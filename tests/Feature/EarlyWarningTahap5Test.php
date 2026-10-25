<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\DokumenSip;
use App\Models\Notifikasi;
use App\Models\User;
use App\Services\SipMonitoringService;
use Carbon\Carbon;
use Tests\TestCase;

class EarlyWarningTahap5Test extends TestCase
{
    protected User $adminSdm;

    protected User $dokterUser;

    protected Dokter $dokter;

    protected SipMonitoringService $monitoringService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminSdm = User::where('username', 'adminsdm')->first();
        $this->dokterUser = User::where('username', 'dokter1')->first();
        $this->dokter = $this->dokterUser->dokter;
        $this->monitoringService = app(SipMonitoringService::class);
    }

    /**
     * Test Case 1: Perhitungan sisa hari dan kategorisasi 4 tingkatan status (Tahap 5)
     */
    public function test_perhitungan_sisa_hari_dan_kategorisasi_status_tahap_5(): void
    {
        // 1. Sisa > 90 Hari (aktif - Hijau)
        $sipAman = new DokumenSip([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/TEST-AMAN/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->subMonths(6),
            'tanggal_kadaluarsa' => Carbon::now()->addDays(120),
            'file_sip' => 'sip/sip_sample_1.pdf',
        ]);
        $this->assertEquals(120, $sipAman->hitungSisaHari());
        $this->assertEquals('aktif', $sipAman->updateStatusKadaluarsa());
        $this->assertEquals('aman', $sipAman->tingkat_eskalasi);

        // 2. H-90 s.d. H-60 (due_soon - Kuning)
        $sipDueSoon = new DokumenSip([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/TEST-DUESOON/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->subMonths(6),
            'tanggal_kadaluarsa' => Carbon::now()->addDays(75),
            'file_sip' => 'sip/sip_sample_1.pdf',
        ]);
        $this->assertEquals(75, $sipDueSoon->hitungSisaHari());
        $this->assertEquals('due_soon', $sipDueSoon->updateStatusKadaluarsa());
        $this->assertEquals('pengingat_pertama', $sipDueSoon->tingkat_eskalasi);

        // 3. H-30 (due - Oranye)
        $sipDue = new DokumenSip([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/TEST-DUE/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->subMonths(6),
            'tanggal_kadaluarsa' => Carbon::now()->addDays(20),
            'file_sip' => 'sip/sip_sample_1.pdf',
        ]);
        $this->assertEquals(20, $sipDue->hitungSisaHari());
        $this->assertEquals('due', $sipDue->updateStatusKadaluarsa());
        $this->assertEquals('peringatan_mendesak', $sipDue->tingkat_eskalasi);

        // 4. H-7 (critical - Oranye Tua)
        $sipCritical = new DokumenSip([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/TEST-CRITICAL/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->subMonths(6),
            'tanggal_kadaluarsa' => Carbon::now()->addDays(7),
            'file_sip' => 'sip/sip_sample_1.pdf',
        ]);
        $this->assertEquals(7, $sipCritical->hitungSisaHari());
        $this->assertEquals('critical', $sipCritical->updateStatusKadaluarsa());
        $this->assertEquals('eskalasi_kritis', $sipCritical->tingkat_eskalasi);

        // 5. Overdue (Kedaluwarsa - Merah)
        $sipOverdue = new DokumenSip([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/TEST-OVERDUE/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->subMonths(6),
            'tanggal_kadaluarsa' => Carbon::now()->subDays(4),
            'file_sip' => 'sip/sip_sample_1.pdf',
        ]);
        $this->assertEquals(-4, $sipOverdue->hitungSisaHari());
        $this->assertEquals('overdue', $sipOverdue->updateStatusKadaluarsa());
        $this->assertEquals('kedaluwarsa', $sipOverdue->tingkat_eskalasi);
    }

    /**
     * Test Case 2: Tier 1 - Sisa > 90 Hari (aktif - Hijau): Status aman; sistem tidak mengirimkan pesan
     */
    public function test_tier_1_sisa_lebih_dari_90_hari_status_aman_tanpa_pesan(): void
    {
        $sipAman = DokumenSip::create([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/SIP-TIER1-SAFE/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->subMonths(3)->toDateString(),
            'tanggal_kadaluarsa' => Carbon::now()->addDays(150)->toDateString(),
            'status' => 'aktif',
            'status_verifikasi' => 'disetujui',
            'file_sip' => 'sip/sip_sample_1.pdf',
        ]);

        $awalCount = Notifikasi::where('id_sip', $sipAman->id)->count();

        // Jalankan evaluasi cron
        $this->monitoringService->runDailyCheck(force: false);

        // Tidak boleh ada notifikasi yang terkirim untuk dokumen aman ini
        $akhirCount = Notifikasi::where('id_sip', $sipAman->id)->count();
        $this->assertEquals($awalCount, $akhirCount);
    }

    /**
     * Test Case 3: Tier 2 - H-90 s.d. H-60 (due_soon - Kuning): Pengingat pertama SKP SatuSehat langsung ke nomor dokter
     */
    public function test_tier_2_h90_sampai_h60_pengingat_pertama_skp_satusehat_ke_dokter(): void
    {
        $sipDueSoon = DokumenSip::create([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/SIP-TIER2-DUE-SOON/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->subYears(2)->toDateString(),
            'tanggal_kadaluarsa' => Carbon::now()->addDays(90)->toDateString(),
            'status' => 'due_soon',
            'status_verifikasi' => 'disetujui',
            'file_sip' => 'sip/sip_sample_1.pdf',
        ]);

        // Jalankan evaluasi cron
        $this->monitoringService->runDailyCheck(force: false);

        // Harus ada notifikasi ke dokter bersangkutan
        $notifDokter = Notifikasi::where('id_sip', $sipDueSoon->id)
            ->where('id_user', $this->dokterUser->id)
            ->where('tipe_penerima', 'dokter')
            ->first();

        $this->assertNotNull($notifDokter);
        $this->assertEquals('due_soon', $notifDokter->tingkat_eskalasi);
        $this->assertStringContainsString('PENGINGAT PERTAMA', $notifDokter->pesan);
        $this->assertStringContainsString('SKP SatuSehat', $notifDokter->pesan);

        // Pastikan TIDAK dikirimkan ke Admin SDM pada tier ini
        $notifSdm = Notifikasi::where('id_sip', $sipDueSoon->id)
            ->where('tipe_penerima', 'admin_sdm')
            ->first();
        $this->assertNull($notifSdm);
    }

    /**
     * Test Case 4: Tier 3 - H-30 (due - Oranye): Pesan peringatan mendesak ke dokter dan salinan rekapitulasi ke Admin SDM
     */
    public function test_tier_3_h30_peringatan_mendesak_ke_dokter_dan_salinan_rekapitulasi_ke_admin_sdm(): void
    {
        $sipDue = DokumenSip::create([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/SIP-TIER3-H30/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->subYears(2)->toDateString(),
            'tanggal_kadaluarsa' => Carbon::now()->addDays(30)->toDateString(),
            'status' => 'due',
            'status_verifikasi' => 'disetujui',
            'file_sip' => 'sip/sip_sample_1.pdf',
        ]);

        // Jalankan evaluasi cron
        $this->monitoringService->runDailyCheck(force: false);

        // 1. Pesan peringatan mendesak terkirim ke dokter
        $notifDokter = Notifikasi::where('id_sip', $sipDue->id)
            ->where('id_user', $this->dokterUser->id)
            ->where('tipe_penerima', 'dokter')
            ->first();

        $this->assertNotNull($notifDokter);
        $this->assertEquals('due', $notifDokter->tingkat_eskalasi);
        $this->assertStringContainsString('MENDESAK', $notifDokter->pesan);

        // 2. Salinan rekapitulasi terkirim ke Admin SDM
        $notifSdm = Notifikasi::where('id_sip', $sipDue->id)
            ->where('id_user', $this->adminSdm->id)
            ->where('tipe_penerima', 'admin_sdm')
            ->first();

        $this->assertNotNull($notifSdm);
        $this->assertEquals('due', $notifSdm->tingkat_eskalasi);
        $this->assertStringContainsString('SALINAN REKAPITULASI JATUH TEMPO (H-30)', $notifSdm->pesan);
        $this->assertStringContainsString($this->dokterUser->nama, $notifSdm->pesan);
    }

    /**
     * Test Case 5: Tier 4 - H-7 (critical - Oranye Tua): Eskalasi berjenjang notifikasi peringatan kritis ke Dokter & Staf SDM
     */
    public function test_tier_4_h7_eskalasi_berjenjang_peringatan_kritis_ke_dokter_dan_staf_sdm(): void
    {
        $sipCritical = DokumenSip::create([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/SIP-TIER4-H7/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->subYears(2)->toDateString(),
            'tanggal_kadaluarsa' => Carbon::now()->addDays(7)->toDateString(),
            'status' => 'critical',
            'status_verifikasi' => 'disetujui',
            'file_sip' => 'sip/sip_sample_1.pdf',
        ]);

        // Jalankan evaluasi cron
        $this->monitoringService->runDailyCheck(force: false);

        // 1. Notifikasi peringatan kritis terkirim ke Dokter bersangkutan
        $notifDokter = Notifikasi::where('id_sip', $sipCritical->id)
            ->where('id_user', $this->dokterUser->id)
            ->where('tipe_penerima', 'dokter')
            ->first();

        $this->assertNotNull($notifDokter);
        $this->assertEquals('critical', $notifDokter->tingkat_eskalasi);
        $this->assertStringContainsString('PERINGATAN KRITIS TAHAP AKHIR (H-7)', $notifDokter->pesan);
        $this->assertStringContainsString('dieskalasikan berjenjang langsung ke Staf Kepegawaian / SDM', $notifDokter->pesan);

        // 2. Notifikasi eskalasi berjenjang terkirim ke Staf Kepegawaian / SDM
        $notifStafSdm = Notifikasi::where('id_sip', $sipCritical->id)
            ->where('id_user', $this->adminSdm->id)
            ->where('tipe_penerima', 'staf_sdm')
            ->first();

        $this->assertNotNull($notifStafSdm);
        $this->assertEquals('critical', $notifStafSdm->tingkat_eskalasi);
        $this->assertStringContainsString('ESKALASI BERJENJANG TINGKAT KRITIS (H-7)', $notifStafSdm->pesan);
        $this->assertStringContainsString($this->dokterUser->nama, $notifStafSdm->pesan);
    }

    /**
     * Test Case 6: Eksekusi Artisan Command sip:cek-kadaluarsa
     */
    public function test_artisan_command_sip_cek_kadaluarsa(): void
    {
        $this->artisan('sip:cek-kadaluarsa --force')
            ->expectsOutputToContain('EARLY WARNING ENGINE')
            ->expectsOutputToContain('Pemeriksaan selesai!')
            ->assertExitCode(0);
    }

    /**
     * Test Case 7: Halaman Matriks Monitoring SDM & Filter Status Critical (Oranye Tua)
     */
    public function test_halaman_matriks_monitoring_sdm_dan_filter_critical(): void
    {
        // Pastikan ada satu dokumen berstatus critical
        DokumenSip::updateOrCreate(
            ['nomor_sip' => '446/SIP-UI-CRITICAL/2026'],
            [
                'id_dokter' => $this->dokter->id,
                'tempat_praktik_ke' => 1,
                'tanggal_terbit' => Carbon::now()->subYears(2)->toDateString(),
                'tanggal_kadaluarsa' => Carbon::now()->addDays(1)->toDateString(),
                'status' => 'critical',
                'status_verifikasi' => 'disetujui',
                'file_sip' => 'sip/sip_sample_1.pdf',
            ]
        );

        $response = $this->actingAs($this->adminSdm)->get(route('sdm.reminder.index', ['status' => 'critical']));

        $response->assertStatus(200);
        $response->assertSee('Early Warning Engine');
        $response->assertSee('Tier 4: H-7 Hari');
        $response->assertSee('Eskalasi Kritis');
        $response->assertSee('446/SIP-UI-CRITICAL/2026');
    }

    /**
     * Test Case 8: Manual Trigger Early Warning Engine via Tombol Web SDM
     */
    public function test_manual_trigger_early_warning_engine_sdm(): void
    {
        $response = $this->actingAs($this->adminSdm)->post(route('sdm.reminder.run_engine'), [
            'force' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('sukses');

        $this->assertDatabaseHas('audit_logs', [
            'id_user' => $this->adminSdm->id,
            'aksi' => 'TRIGGER_EARLY_WARNING',
            'modul' => 'Early Warning Engine',
        ]);
    }
}
