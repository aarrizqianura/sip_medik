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

class MitigasiOverdueTahap7Test extends TestCase
{
    protected User $adminSdm;

    protected User $direktur;

    protected User $komiteMedik;

    protected User $dokterUser;

    protected Dokter $dokter;

    protected DokumenSip $sip;

    protected KredensialKlinis $kredensial;

    protected SipMonitoringService $monitoringService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminSdm = User::where('username', 'adminsdm')->first();
        $this->direktur = User::where('username', 'superadmin')->first();
        $this->komiteMedik = User::where('username', 'verifikatorkomite')->first();
        $this->dokterUser = User::where('username', 'dokter1')->first();
        $this->dokter = $this->dokterUser->dokter;
        $this->monitoringService = app(SipMonitoringService::class);

        // Reset data dokter dari tes sebelumnya agar terisolasi bersih
        DokumenSip::where('id_dokter', $this->dokter->id)->update([
            'status_dispensasi' => 'none',
            'tanggal_mulai_dispensasi' => null,
            'tanggal_akhir_dispensasi' => null,
            'is_overdue_locked' => false,
            'overdue_locked_at' => null,
        ]);
        DokumenSip::where('id_dokter', $this->dokter->id)
            ->whereIn('nomor_sip', ['446/TEST-TAHAP7/2026', '446/DEFINITIF-BARU/DPMPTSP/2026'])
            ->delete();

        $this->dokter->update([
            'status_praktik' => 'AKTIF_LEGAL',
            'suspended_at' => null,
            'alasan_penangguhan' => null,
        ]);

        Storage::fake('public');

        // Pastikan ada SPK & RKK aktif awal untuk dokter
        $this->kredensial = KredensialKlinis::updateOrCreate(
            ['nomor_spk' => 'SPK/TEST-TAHAP7/2026'],
            [
                'id_dokter' => $this->dokter->id,
                'tanggal_terbit_spk' => Carbon::today()->subYear(),
                'tanggal_berakhir_spk' => Carbon::today()->addYears(2),
                'status_kredensial' => 'aktif',
                'is_suspended' => false,
                'is_spk_locked' => true,
                'is_rkk_locked' => true,
                'daftar_rkk' => [
                    ['tindakan' => 'Laparotomi Eksplorasi', 'status' => 'mandiri'],
                    ['tindakan' => 'Apendektomi Terbuka', 'status' => 'mandiri'],
                    ['tindakan' => 'Kolesistektomi Laparoskopi', 'status' => 'supervisi'],
                ],
            ]
        );

        // Buat satu dokumen SIP aktif yang akan diuji jatuh temponya
        $this->sip = DokumenSip::create([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/TEST-TAHAP7/2026',
            'tempat_praktik_ke' => 1,
            'fasyankes' => 'RSUD dr. H. Moch. Ansari Saleh',
            'penerbit_sip' => 'DPMPTSP Kota Banjarmasin',
            'tanggal_terbit' => Carbon::today()->subYears(5),
            'tanggal_kadaluarsa' => Carbon::today()->subDays(1), // Kedaluwarsa kemarin (sisa_hari <= 0)
            'status' => 'critical',
            'status_verifikasi' => 'disetujui',
            'status_dispensasi' => 'none',
            'file_sip' => 'sip/sip_sample_tahap7.pdf',
        ]);
    }

    /**
     * Test Case 1: Kunci Status Kedaluwarsa Otomatis
     * Jika sisa_hari <= 0 dan dokter tidak memiliki status dispensasi aktif,
     * sistem otomatis mengunci status SIP menjadi overdue (Merah).
     */
    public function test_kunci_status_kedaluwarsa_overdue_jika_sisa_hari_nol_atau_negatif_tanpa_dispensasi(): void
    {
        // Kondisi 1: Sisa hari = 0 (tepat jatuh tempo hari ini)
        $this->sip->update([
            'tanggal_kadaluarsa' => Carbon::today(),
            'status' => 'critical',
            'status_dispensasi' => 'none',
        ]);

        $this->assertEquals(0, $this->sip->hitungSisaHari());
        $status = $this->sip->updateStatusKadaluarsa();

        $this->assertEquals('overdue', $status);
        $this->sip->refresh();
        $this->assertEquals('overdue', $this->sip->status);
        $this->assertTrue($this->sip->is_overdue_locked);
        $this->assertNotNull($this->sip->overdue_locked_at);
        $this->assertEquals('kedaluwarsa', $this->sip->tingkat_eskalasi);
        $this->assertTrue($this->sip->isOverdue());

        // Kondisi 2: Sisa hari negatif (lewat 5 hari)
        $this->sip->update([
            'tanggal_kadaluarsa' => Carbon::today()->subDays(5),
        ]);
        $this->assertEquals(-5, $this->sip->hitungSisaHari());
        $status2 = $this->sip->updateStatusKadaluarsa();

        $this->assertEquals('overdue', $status2);
        $this->assertTrue($this->sip->fresh()->is_overdue_locked);
    }

    /**
     * Test Case 2: Penangguhan Otomatis (Auto-Suspend Wewenang Klinis)
     * Status instrumen SPK dan RKK secara otomatis beralih menjadi DITANGGUHKAN (Suspended).
     * Dokter terblokir secara administratif dari wewenang tindakan klinis mandiri.
     */
    public function test_auto_suspend_wewenang_klinis_spk_rkk_dan_blokir_tindakan_mandiri(): void
    {
        // Pastikan dokter berstatus awal AKTIF_LEGAL
        $this->dokter->update(['status_praktik' => 'AKTIF_LEGAL']);
        $this->kredensial->update(['status_kredensial' => 'aktif', 'is_suspended' => false]);

        // Picu mitigasi kedaluwarsa SIP
        $this->sip->update([
            'tanggal_kadaluarsa' => Carbon::today()->subDays(2),
            'status_dispensasi' => 'none',
        ]);
        $this->sip->updateStatusKadaluarsa();

        // 1. Periksa model Dokter
        $this->dokter->refresh();
        $this->assertEquals('DITANGGUHKAN', $this->dokter->status_praktik);
        $this->assertTrue($this->dokter->isSuspended());
        $this->assertTrue($this->dokter->isWewenangMandiriDiblokir());
        $this->assertFalse($this->dokter->hasWewenangKlinisMandiri());
        $this->assertFalse($this->dokter->isPelayananTerproteksi());
        $this->assertNotNull($this->dokter->suspended_at);
        $this->assertStringContainsString('Auto-Suspend', $this->dokter->alasan_penangguhan);

        // 2. Periksa instrumen SPK & RKK
        $this->kredensial->refresh();
        $this->assertEquals('ditangguhkan', $this->kredensial->status_kredensial);
        $this->assertTrue($this->kredensial->is_suspended);
        $this->assertTrue($this->kredensial->isSuspended());
        $this->assertTrue($this->kredensial->isWewenangMandiriDiblokir());
        $this->assertStringContainsString('SPK & RKK DITANGGUHKAN', $this->kredensial->status_badge);
    }

    /**
     * Test Case 3: Eskalasi Insiden & Notifikasi Peringatan Darurat
     * Terkirim langsung ke dashboard Direktur, Ketua Komite Medik, Bagian SDM, dan Dokter bersangkutan.
     */
    public function test_eskalasi_insiden_darurat_ke_direktur_komite_medik_dan_sdm(): void
    {
        $this->sip->update([
            'tanggal_kadaluarsa' => Carbon::today()->subDays(3),
            'status_dispensasi' => 'none',
            'is_overdue_locked' => false,
        ]);

        // Jalankan mesin pemantauan harian
        $res = $this->monitoringService->runDailyCheck(true);

        $this->assertGreaterThan(0, $res['notifikasi_dikirim']);

        // 1. Notifikasi darurat ke Direktur (super_admin)
        $this->assertDatabaseHas('notifikasi', [
            'id_user' => $this->direktur->id,
            'id_sip' => $this->sip->id,
            'tingkat_eskalasi' => 'darurat_overdue',
            'tipe_penerima' => 'direktur',
        ]);

        // 2. Notifikasi darurat ke Ketua Komite Medik (verifikator_komite)
        $this->assertDatabaseHas('notifikasi', [
            'id_user' => $this->komiteMedik->id,
            'id_sip' => $this->sip->id,
            'tingkat_eskalasi' => 'darurat_overdue',
            'tipe_penerima' => 'komite_medik',
        ]);

        // 3. Notifikasi darurat ke Bagian SDM (admin_sdm)
        $this->assertDatabaseHas('notifikasi', [
            'id_user' => $this->adminSdm->id,
            'id_sip' => $this->sip->id,
            'tingkat_eskalasi' => 'darurat_overdue',
            'tipe_penerima' => 'admin_sdm',
        ]);

        // 4. Notifikasi darurat ke Dokter bersangkutan
        $this->assertDatabaseHas('notifikasi', [
            'id_user' => $this->dokterUser->id,
            'id_sip' => $this->sip->id,
            'tingkat_eskalasi' => 'darurat_overdue',
            'tipe_penerima' => 'dokter',
        ]);

        // 5. Rekam jejak audit mitigasi otomatis
        $this->assertDatabaseHas('audit_logs', [
            'aksi' => 'AUTO_SUSPEND_OVERDUE',
            'modul' => 'Mitigasi Kedaluwarsa & Auto-Suspend',
        ]);
    }

    /**
     * Test Case 4: Laporan Monitoring Akreditasi Pokja KPS
     * Nama dokter otomatis masuk ke daftar Staf Medis Non-Aktif Pelayanan guna mencegah temuan audit eksternal.
     */
    public function test_nama_dokter_masuk_daftar_staf_medis_non_aktif_pelayanan_laporan_akreditasi_kps(): void
    {
        // Pastikan dokter dalam kondisi suspended
        $this->dokter->autoSuspendWewenangKlinis($this->sip);

        $response = $this->actingAs($this->adminSdm)
            ->get(route('sdm.laporan.index', ['jenis' => '2']));

        $response->assertStatus(200);
        $response->assertViewHas('stafNonAktifPelayanan');
        $response->assertSee('Daftar Staf Medis Non-Aktif Pelayanan');
        $response->assertSee($this->dokterUser->nama);
        $response->assertSee('Terblokir Administratif');
        $response->assertSee('DITANGGUHKAN');

        // Uji unduh PDF Laporan 2
        $pdfResponse = $this->actingAs($this->adminSdm)
            ->get(route('sdm.laporan.pdf', ['jenis' => '2']));

        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    /**
     * Test Case 5: Pemulihan Hak Praktik (Re-activation)
     * Penangguhan wewenang klinis hanya dapat dicabut setelah dokter mengunggah fisik SIP definitif
     * yang disahkan kembali melalui alur Tahap 2, 3, dan 4 (SK Direktur).
     */
    public function test_pemulihan_hak_praktik_setelah_pengesahan_sip_definitif_tahap_2_3_4(): void
    {
        // 1. Kondisi awal dokter dan SPK ditangguhkan (Auto-Suspend)
        $this->dokter->autoSuspendWewenangKlinis($this->sip);
        $this->assertTrue($this->dokter->fresh()->isSuspended());
        $this->assertTrue($this->kredensial->fresh()->is_suspended);

        // 2. Tahap 2: Dokter mengunggah fisik SIP definitif baru
        $fileSipBaru = UploadedFile::fake()->create('sip_definitif_baru.pdf', 500, 'application/pdf');
        $sipBaru = DokumenSip::create([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/DEFINITIF-BARU/DPMPTSP/2026',
            'tempat_praktik_ke' => 1,
            'fasyankes' => 'RSUD dr. H. Moch. Ansari Saleh',
            'penerbit_sip' => 'DPMPTSP Kota Banjarmasin',
            'tanggal_terbit' => Carbon::today(),
            'tanggal_kadaluarsa' => Carbon::today()->addYears(5),
            'status' => 'aktif',
            'status_verifikasi' => 'menunggu',
            'status_dispensasi' => 'none',
            'file_sip' => 'sip/sip_definitif_baru.pdf',
        ]);

        // Saat masih menunggu verifikasi, hak praktik MASIH ditangguhkan
        $this->assertTrue($this->dokter->fresh()->isSuspended());

        // 3. Tahap 3: Komite Medik memverifikasi & menyetujui fisik SIP definitif
        $this->actingAs($this->komiteMedik)
            ->post(route('komite.verifikasi.proses', $sipBaru->id), [
                'status_verifikasi' => 'disetujui',
                'checklist_keabsahan_fasyankes' => 1,
                'checklist_masa_berlaku' => 1,
                'checklist_keterbacaan_pindaian' => 1,
                'catatan_verifikasi' => 'Fisik SIP definitif absah terverifikasi.',
            ]);

        // 4. Tahap 4: Direktur & SDM menerbitkan SK Direktur & mengunci e-Arsip
        $fileSk = UploadedFile::fake()->create('sk_direktur_pemulihan.pdf', 300, 'application/pdf');
        $responseSah = $this->actingAs($this->adminSdm)
            ->post(route('sdm.pengesahan.store', $this->kredensial->id), [
                'nomor_sk_direktur' => '800/999-DIR/RSAS/SK-PULIH/2026',
                'tanggal_sk_direktur' => Carbon::today()->format('Y-m-d'),
                'tanggal_berakhir_spk' => Carbon::today()->addYears(3)->format('Y-m-d'),
                'file_sk_direktur' => $fileSk,
                'catatan_pengesahan_direksi' => 'Pemulihan hak praktik resmi setelah pengesahan SIP definitif baru.',
            ]);

        $responseSah->assertRedirect(route('sdm.arsip.show', $this->dokter->id));

        // 5. Verifikasi Hak Praktik Resmi Pulih Penuh
        $this->dokter->refresh();
        $this->kredensial->refresh();
        $sipBaru->refresh();

        $this->assertEquals('AKTIF_LEGAL', $this->dokter->status_praktik);
        $this->assertFalse($this->dokter->isSuspended());
        $this->assertTrue($this->dokter->hasWewenangKlinisMandiri());
        $this->assertFalse($this->dokter->isWewenangMandiriDiblokir());
        $this->assertNull($this->dokter->suspended_at);
        $this->assertTrue($this->dokter->is_arsip_locked);

        // SPK aktif kembali
        $this->assertEquals('aktif', $this->kredensial->status_kredensial);
        $this->assertFalse($this->kredensial->is_suspended);
        $this->assertNull($this->kredensial->suspended_at);

        // SIP definitif terkunci
        $this->assertTrue($sipBaru->is_sip_locked);
        $this->assertFalse($sipBaru->is_overdue_locked);

        // Audit Log Pemulihan Hak Praktik tercatat
        $this->assertDatabaseHas('audit_logs', [
            'aksi' => 'PEMULIHAN_HAK_PRAKTIK',
            'modul' => 'SDM & Direksi',
        ]);
    }

    /**
     * Test Case 6: Proteksi Dispensasi Aktif Mencegah Auto-Suspend
     * Selama masa dispensasi aktif (Grace Period), status tidak boleh dialihkan menjadi overdue
     * dan wewenang klinis tidak ditangguhkan.
     */
    public function test_proteksi_dispensasi_tetap_menjaga_tidak_terjadi_auto_suspend_selama_grace_period(): void
    {
        $this->dokter->update(['status_praktik' => 'AKTIF_LEGAL']);
        $this->kredensial->update(['status_kredensial' => 'aktif', 'is_suspended' => false]);

        // Simulasikan SIP lewat tanggal awal tetapi memiliki dispensasi aktif 20 hari lagi
        $this->sip->update([
            'tanggal_kadaluarsa' => Carbon::today()->subDays(5),
            'status' => 'dalam_proses',
            'status_dispensasi' => 'disetujui',
            'durasi_dispensasi_hari' => 25,
            'tanggal_mulai_dispensasi' => Carbon::today()->subDays(5),
            'tanggal_akhir_dispensasi' => Carbon::today()->addDays(20),
        ]);

        $this->assertTrue($this->sip->isGracePeriodAktif());

        // Jalankan pengecekan harian
        $this->monitoringService->runDailyCheck(true);

        // Verifikasi tidak terjadi penangguhan
        $this->sip->refresh();
        $this->dokter->refresh();
        $this->kredensial->refresh();

        $this->assertEquals('dalam_proses', $this->sip->status);
        $this->assertFalse($this->sip->is_overdue_locked);
        $this->assertNotEquals('DITANGGUHKAN', $this->dokter->status_praktik);
        $this->assertFalse($this->dokter->isSuspended());
        $this->assertEquals('aktif', $this->kredensial->status_kredensial);
        $this->assertFalse($this->kredensial->is_suspended);
    }
}
