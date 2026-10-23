<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\DokumenSip;
use App\Models\KredensialKlinis;
use App\Models\Notifikasi;
use App\Models\User;
use App\Services\SipMonitoringService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GracePeriodTahap6Test extends TestCase
{
    protected User $adminSdm;

    protected User $dokterUser;

    protected Dokter $dokter;

    protected DokumenSip $sip;

    protected SipMonitoringService $monitoringService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminSdm = User::where('username', 'adminsdm')->first();
        $this->dokterUser = User::where('username', 'dokter1')->first();
        $this->dokter = $this->dokterUser->dokter;
        $this->monitoringService = app(SipMonitoringService::class);

        Storage::fake('public');

        // Pastikan ada satu dokumen SIP untuk pengujian
        $this->sip = DokumenSip::create([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/TEST-GRACE/2026',
            'tempat_praktik_ke' => 1,
            'fasyankes' => 'RSUD dr. H. Moch. Ansari Saleh',
            'penerbit_sip' => 'DPMPTSP Kota Banjarmasin',
            'tanggal_terbit' => Carbon::today()->subYears(5),
            'tanggal_kadaluarsa' => Carbon::today()->addDays(5), // Mendekati jatuh tempo (H-5)
            'status' => 'critical',
            'status_verifikasi' => 'disetujui',
            'file_sip' => 'sip/sip_sample_1.pdf',
        ]);
    }

    /**
     * Test Case 1: Dokter dapat mengunggah bukti proses (Resi DPMPTSP/Dinkes) saat mendekati jatuh tempo
     */
    public function test_dokter_dapat_mengajukan_bukti_proses_resi_tanda_terima_dpmptsp(): void
    {
        $fileResi = UploadedFile::fake()->create('resi_dpmptsp.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->dokterUser)->post(
            route('dokter.dispensasi.store', $this->sip->id),
            [
                'nomor_resi_proses' => 'RESI/DPMPTSP/2026/09988',
                'instansi_penerbit_resi' => 'DPMPTSP Kota Banjarmasin',
                'tanggal_pengajuan_resi' => Carbon::today()->format('Y-m-d'),
                'durasi_dispensasi_diminta' => 30,
                'file_bukti_proses' => $fileResi,
                'keterangan_proses' => 'Berkas perpanjangan telah lengkap di DPMPTSP, menunggu TTE Kepala Dinas.',
            ]
        );

        $response->assertRedirect(route('dokter.pengajuan.index'));
        $response->assertSessionHas('sukses');

        $this->sip->refresh();
        $this->assertEquals('diajukan', $this->sip->status_dispensasi);
        $this->assertEquals('RESI/DPMPTSP/2026/09988', $this->sip->nomor_resi_proses);
        $this->assertEquals('DPMPTSP Kota Banjarmasin', $this->sip->instansi_penerbit_resi);
        $this->assertEquals(30, $this->sip->durasi_dispensasi_hari);
        $this->assertTrue($this->sip->isMenungguVerifikasiDispensasi());

        // Verifikasi berkas tersimpan
        Storage::disk('public')->assertExists($this->sip->file_bukti_proses);

        // Verifikasi audit log & notifikasi internal SDM
        $this->assertDatabaseHas('audit_logs', [
            'aksi' => 'PENGAJUAN_DISPENSASI',
            'modul' => 'Dispensasi Masa Tenggang',
        ]);

        $this->assertDatabaseHas('notifikasi', [
            'id_user' => $this->adminSdm->id,
            'id_sip' => $this->sip->id,
            'tingkat_eskalasi' => 'pengajuan_dispensasi',
        ]);
    }

    /**
     * Test Case 2: Admin SDM memverifikasi resi dan memberikan dispensasi terbatas (Grace Period 14-30 hari)
     * Mengaktifkan status dalam_proses
     */
    public function test_admin_sdm_dapat_memverifikasi_resi_dan_mengaktifkan_status_dalam_proses(): void
    {
        // Set awal pengajuan
        $this->sip->update([
            'status_dispensasi' => 'diajukan',
            'nomor_resi_proses' => 'RESI/DPMPTSP/2026/09988',
            'instansi_penerbit_resi' => 'DPMPTSP Kota Banjarmasin',
            'tanggal_pengajuan_resi' => Carbon::today(),
            'file_bukti_proses' => 'bukti_proses/sample_resi.pdf',
        ]);

        $response = $this->actingAs($this->adminSdm)->post(
            route('sdm.dispensasi.verifikasi', $this->sip->id),
            [
                'keputusan' => 'setujui',
                'durasi_dispensasi_hari' => 21, // 21 hari (14 - 30 hari)
                'tanggal_mulai_dispensasi' => Carbon::today()->format('Y-m-d'),
                'catatan_verifikasi_dispensasi' => 'Resi tanda terima sah terverifikasi dari DPMPTSP. Hak pelayanan klinis dilindungi.',
            ]
        );

        $response->assertRedirect(route('sdm.dispensasi.index', ['tab' => 'aktif']));
        $response->assertSessionHas('sukses');

        $this->sip->refresh();
        $this->assertEquals('disetujui', $this->sip->status_dispensasi);
        $this->assertEquals('dalam_proses', $this->sip->status);
        $this->assertEquals('grace_period', $this->sip->tingkat_eskalasi);
        $this->assertEquals(21, $this->sip->durasi_dispensasi_hari);
        $this->assertEquals(Carbon::today()->addDays(21)->format('Y-m-d'), $this->sip->tanggal_akhir_dispensasi->format('Y-m-d'));
        $this->assertEquals($this->adminSdm->id, $this->sip->dispensasi_verified_by);
        $this->assertNotNull($this->sip->dispensasi_verified_at);

        // Verifikasi audit log
        $this->assertDatabaseHas('audit_logs', [
            'aksi' => 'PEMBERIAN_DISPENSASI_GRACE_PERIOD',
            'modul' => 'Dispensasi Masa Tenggang',
        ]);

        // Verifikasi notifikasi terkirim ke dokter
        $this->assertDatabaseHas('notifikasi', [
            'id_user' => $this->dokterUser->id,
            'id_sip' => $this->sip->id,
            'tingkat_eskalasi' => 'grace_period',
        ]);
    }

    /**
     * Test Case 3: Proteksi Pelayanan - Status wewenang klinis (SPK/RKK) di sistem TIDAK ditangguhkan mendadak
     */
    public function test_proteksi_pelayanan_wewenang_klinis_spk_rkk_tidak_ditangguhkan_selama_grace_period(): void
    {
        // Simulasikan SIP yang telah lewat tanggal jatuh tempo awal (misal H+3 setelah kadaluarsa awal)
        // namun memiliki dispensasi grace period aktif selama 30 hari
        $this->sip->update([
            'tanggal_kadaluarsa' => Carbon::today()->subDays(3),
            'status' => 'dalam_proses',
            'status_dispensasi' => 'disetujui',
            'durasi_dispensasi_hari' => 30,
            'tanggal_mulai_dispensasi' => Carbon::today()->subDays(3),
            'tanggal_akhir_dispensasi' => Carbon::today()->addDays(27), // Masih sisa 27 hari
        ]);

        // 1. Uji method isGracePeriodAktif
        $this->assertTrue($this->sip->isGracePeriodAktif());
        $this->assertEquals(27, $this->sip->hitungSisaHariDispensasi());

        // 2. Uji bahwa updateStatusKadaluarsa() mempertahankan status 'dalam_proses' dan TIDAK menjadikannya 'overdue'
        $statusTerbaru = $this->sip->updateStatusKadaluarsa();
        $this->assertEquals('dalam_proses', $statusTerbaru);
        $this->assertEquals('grace_period', $this->sip->tingkat_eskalasi);

        // 3. Uji proteksi pelayanan pada model Dokter
        $this->dokter->refresh();
        $this->assertTrue($this->dokter->hasActiveDispensasi());
        $this->assertTrue($this->dokter->isPelayananTerproteksi());
        $this->assertTrue($this->dokter->isAktifLegal());

        // 4. Uji proteksi pada instrumen SPK/RKK (KredensialKlinis)
        $spk = KredensialKlinis::create([
            'id_dokter' => $this->dokter->id,
            'nomor_spk' => 'SPK/TEST-PROT/2026',
            'tanggal_terbit_spk' => Carbon::today()->subYear(),
            'tanggal_berakhir_spk' => Carbon::today()->addYears(2),
            'status_kredensial' => 'aktif',
            'is_spk_locked' => true,
            'is_rkk_locked' => true,
        ]);

        $this->assertTrue($spk->isProteksiPelayananAktif());
        $this->assertStringContainsString('SPK Terproteksi (Grace Period)', $spk->status_badge);
    }

    /**
     * Test Case 4: Early Warning Engine menghormati masa dispensasi (tidak mengirim overdue) dan mengirim pengingat H-3
     */
    public function test_early_warning_engine_menghormati_grace_period_dan_kirim_pengingat_h3(): void
    {
        // 1. Kondisi sisa dispensasi 20 hari: tidak boleh dikirimi peringatan overdue
        $this->sip->update([
            'tanggal_kadaluarsa' => Carbon::today()->subDays(2),
            'status' => 'dalam_proses',
            'status_dispensasi' => 'disetujui',
            'durasi_dispensasi_hari' => 30,
            'tanggal_mulai_dispensasi' => Carbon::today()->subDays(2),
            'tanggal_akhir_dispensasi' => Carbon::today()->addDays(20),
        ]);

        $hasil = $this->monitoringService->runDailyCheck(true);
        // Pastikan tidak ada pesan overdue yang terkirim untuk SIP ini
        $notifOverdue = Notifikasi::where('id_sip', $this->sip->id)
            ->where('tingkat_eskalasi', 'overdue')
            ->exists();
        $this->assertFalse($notifOverdue);

        // 2. Kondisi sisa dispensasi H-3 (3 hari sebelum grace period habis): dikirimi pengingat H-3
        $this->sip->update([
            'tanggal_akhir_dispensasi' => Carbon::today()->addDays(3),
        ]);

        $hasilH3 = $this->monitoringService->runDailyCheck(true);

        $notifH3 = Notifikasi::where('id_sip', $this->sip->id)
            ->where('tingkat_eskalasi', 'grace_period_warning')
            ->first();

        $this->assertNotNull($notifH3);
        $this->assertStringContainsString('PENGINGAT MASA TENGGANG SEGERA BERAKHIR', $notifH3->pesan);
    }

    /**
     * Test Case 5: Dispensasi otomatis beralih menjadi 'berakhir' jika melewati tanggal_akhir_dispensasi
     */
    public function test_dispensasi_berakhir_otomatis_saat_melewati_batas_waktu(): void
    {
        // Grace period telah kedaluwarsa 2 hari yang lalu
        $this->sip->update([
            'tanggal_kadaluarsa' => Carbon::today()->subDays(32),
            'status' => 'dalam_proses',
            'status_dispensasi' => 'disetujui',
            'durasi_dispensasi_hari' => 30,
            'tanggal_mulai_dispensasi' => Carbon::today()->subDays(32),
            'tanggal_akhir_dispensasi' => Carbon::today()->subDays(2),
        ]);

        $this->assertFalse($this->sip->isGracePeriodAktif());

        // Jalankan updateStatusKadaluarsa
        $status = $this->sip->updateStatusKadaluarsa();

        $this->sip->refresh();
        $this->assertEquals('berakhir', $this->sip->status_dispensasi);
        $this->assertEquals('overdue', $this->sip->status);
        $this->assertEquals('kedaluwarsa', $this->sip->tingkat_eskalasi);
    }

    /**
     * Test Case 6: Admin SDM dapat menolak resi permohonan yang tidak valid beserta catatan penolakan
     */
    public function test_admin_sdm_dapat_menolak_pengajuan_resi_tidak_valid(): void
    {
        $this->sip->update([
            'status_dispensasi' => 'diajukan',
            'nomor_resi_proses' => 'RESI/PALSU/001',
            'file_bukti_proses' => 'bukti_proses/palsu.pdf',
        ]);

        $response = $this->actingAs($this->adminSdm)->post(
            route('sdm.dispensasi.verifikasi', $this->sip->id),
            [
                'keputusan' => 'tolak',
                'catatan_verifikasi_dispensasi' => 'Berkas pindaian resi buram dan tidak terdapat cap stempel basah loket PTSP.',
            ]
        );

        $response->assertRedirect(route('sdm.dispensasi.index', ['tab' => 'riwayat']));
        $response->assertSessionHas('warning');

        $this->sip->refresh();
        $this->assertEquals('ditolak', $this->sip->status_dispensasi);
        $this->assertEquals($this->adminSdm->id, $this->sip->dispensasi_verified_by);
        $this->assertStringContainsString('cap stempel basah', $this->sip->catatan_verifikasi_dispensasi);

        // Verifikasi audit log
        $this->assertDatabaseHas('audit_logs', [
            'aksi' => 'PENOLAKAN_DISPENSASI_SIP',
            'modul' => 'Dispensasi Masa Tenggang',
        ]);

        // Verifikasi notifikasi penolakan ke dokter
        $this->assertDatabaseHas('notifikasi', [
            'id_user' => $this->dokterUser->id,
            'tingkat_eskalasi' => 'dispensasi_ditolak',
        ]);
    }
}
