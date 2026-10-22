<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\DokumenSip;
use App\Models\Notifikasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SipMedikWaterfallTest extends TestCase
{
    protected User $superAdmin;

    protected User $adminSdm;

    protected User $verifikator;

    protected User $dokterUser;

    protected Dokter $dokter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::where('username', 'superadmin')->first();
        $this->adminSdm = User::where('username', 'adminsdm')->first();
        $this->verifikator = User::where('username', 'verifikatorkomite')->first();
        $this->dokterUser = User::where('username', 'dokter1')->first();
        $this->dokter = $this->dokterUser->dokter;
    }

    /**
     * Test Case 1: Login Super Admin
     */
    public function test_case_1_login_super_admin(): void
    {
        $response = $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->superAdmin);

        $dashboardResponse = $this->actingAs($this->superAdmin)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Dashboard Eksekutif', false);
    }

    /**
     * Test Case 2: Proteksi Role Hak Akses (Dokter mencoba akses URL Super Admin)
     */
    public function test_case_2_proteksi_role_hak_akses(): void
    {
        $response = $this->actingAs($this->dokterUser)->get('/admin/users');
        $response->assertStatus(403);
    }

    /**
     * Test Case 3: Unggah Dokumen SIP Baru
     */
    public function test_case_3_unggah_dokumen_sip_baru(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('sip_perpanjangan_baru.pdf', 1500, 'application/pdf');

        $response = $this->actingAs($this->dokterUser)->post('/dokter/pengajuan-sip', [
            'nomor_sip' => '446/TEST-SIP/DPMPTSP/2026',
            'tempat_praktik_ke' => 1,
            'fasyankes' => 'RSUD dr. H. Moch. Ansari Saleh',
            'status_kecukupan_skp' => true,
            'tanggal_terbit' => Carbon::now()->toDateString(),
            'tanggal_kadaluarsa' => Carbon::now()->addYears(5)->toDateString(),
            'file_sip' => $file,
        ]);

        $response->assertRedirect(route('dokter.pengajuan.index'));
        $this->assertDatabaseHas('dokumen_sip', [
            'nomor_sip' => '446/TEST-SIP/DPMPTSP/2026',
            'fasyankes' => 'RSUD dr. H. Moch. Ansari Saleh',
            'status_kecukupan_skp' => true,
            'status_verifikasi' => 'menunggu',
        ]);
    }

    /**
     * Test Case 4: Validasi Jenis Berkas (Non-PDF atau melebihi 2MB ditolak)
     */
    public function test_case_4_validasi_jenis_berkas(): void
    {
        Storage::fake('public');

        $fileExe = UploadedFile::fake()->create('malware.exe', 1000, 'application/octet-stream');

        $response = $this->actingAs($this->dokterUser)->post('/dokter/pengajuan-sip', [
            'nomor_sip' => '446/INVALID/DPMPTSP/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->toDateString(),
            'tanggal_kadaluarsa' => Carbon::now()->addYears(3)->toDateString(),
            'file_sip' => $fileExe,
        ]);

        $response->assertSessionHasErrors('file_sip');
    }

    /**
     * Test Case 5: Telaah Berkas Komite - Ditolak disertai catatan
     */
    public function test_case_5_telaah_berkas_komite_tolak(): void
    {
        $sipPending = DokumenSip::where('status_verifikasi', 'menunggu')->first();
        if (! $sipPending) {
            $sipPending = DokumenSip::create([
                'id_dokter' => $this->dokter->id,
                'nomor_sip' => '446/TEST-TOLAK/2026',
                'tempat_praktik_ke' => 1,
                'tanggal_terbit' => Carbon::now()->toDateString(),
                'tanggal_kadaluarsa' => Carbon::now()->addYear()->toDateString(),
                'status' => 'aktif',
                'status_verifikasi' => 'menunggu',
                'file_sip' => 'sip/sip_sample_1.pdf',
            ]);
        }

        $response = $this->actingAs($this->verifikator)->post("/komite/verifikasi/{$sipPending->id}/proses", [
            'status_verifikasi' => 'ditolak',
            'catatan_verifikasi' => 'Pindaian SIP buram dan masa berlaku tidak sesuai dengan STR.',
        ]);

        $response->assertRedirect(route('komite.verifikasi.index'));

        $this->assertDatabaseHas('dokumen_sip', [
            'id' => $sipPending->id,
            'status_verifikasi' => 'ditolak',
            'catatan_verifikasi' => 'Pindaian SIP buram dan masa berlaku tidak sesuai dengan STR.',
        ]);
    }

    /**
     * Test Case 6: Pengesahan Berkas Komite - Disetujui
     */
    public function test_case_6_pengesahan_berkas_komite_setuju(): void
    {
        $sipSetuju = DokumenSip::create([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/TEST-SETUJU/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->toDateString(),
            'tanggal_kadaluarsa' => Carbon::now()->addYear()->toDateString(),
            'status' => 'aktif',
            'status_verifikasi' => 'menunggu',
            'file_sip' => 'sip/sip_sample_1.pdf',
        ]);

        $response = $this->actingAs($this->verifikator)->post("/komite/verifikasi/{$sipSetuju->id}/proses", [
            'status_verifikasi' => 'disetujui',
            'catatan_verifikasi' => 'Dokumen sah dan diverifikasi sesuai STR seumur hidup.',
        ]);

        $response->assertRedirect(route('komite.verifikasi.index'));

        $this->assertDatabaseHas('dokumen_sip', [
            'id' => $sipSetuju->id,
            'status_verifikasi' => 'disetujui',
            'verified_by' => $this->verifikator->id,
        ]);
    }

    /**
     * Test Case 7: Perhitungan Countdown & Penentuan Status Otomatis
     */
    public function test_case_7_perhitungan_countdown_dan_status(): void
    {
        // Dokumen H-45 harus due_soon
        $sipH45 = new DokumenSip([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/H45-TEST/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->subYears(2),
            'tanggal_kadaluarsa' => Carbon::now()->addDays(45),
            'file_sip' => 'sip/sip_sample_1.pdf',
        ]);

        $this->assertEquals(45, $sipH45->hitungSisaHari());
        $status = $sipH45->updateStatusKadaluarsa();
        $this->assertEquals('due_soon', $status);

        // Dokumen H-10 harus due
        $sipH10 = new DokumenSip([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/H10-TEST/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->subYears(2),
            'tanggal_kadaluarsa' => Carbon::now()->addDays(10),
            'file_sip' => 'sip/sip_sample_1.pdf',
        ]);
        $this->assertEquals('due', $sipH10->updateStatusKadaluarsa());

        // Dokumen H minus 5 harus overdue
        $sipMinus5 = new DokumenSip([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/MINUS5-TEST/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->subYears(2),
            'tanggal_kadaluarsa' => Carbon::now()->subDays(5),
            'file_sip' => 'sip/sip_sample_1.pdf',
        ]);
        $this->assertEquals('overdue', $sipMinus5->updateStatusKadaluarsa());
    }

    /**
     * Test Case 8: Trigger Cron Job Reminder
     */
    public function test_case_8_trigger_cron_job_reminder(): void
    {
        // Buat SIP pas H-30
        $sipH30 = DokumenSip::create([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '446/CRON-H30/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->subYears(2)->toDateString(),
            'tanggal_kadaluarsa' => Carbon::now()->addDays(30)->toDateString(),
            'status' => 'due',
            'status_verifikasi' => 'disetujui',
            'file_sip' => 'sip/sip_sample_1.pdf',
        ]);

        $this->artisan('sip:cek-kadaluarsa')
            ->assertExitCode(0);

        // Pastikan record notifikasi tersimpan di tabel notifikasi
        $this->assertDatabaseHas('notifikasi', [
            'id_user' => $this->dokterUser->id,
            'id_sip' => $sipH30->id,
            'kanal' => 'whatsapp',
        ]);
    }

    /**
     * Test Case 9: Ekspor Rekapitulasi PDF
     */
    public function test_case_9_ekspor_rekapitulasi_pdf(): void
    {
        $response = $this->actingAs($this->adminSdm)->get('/sdm/laporan/export-pdf?jenis=2');
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'application/pdf'));
    }

    /**
     * Test Case 10: Ekspor Rekapitulasi Excel
     */
    public function test_case_10_ekspor_rekapitulasi_excel(): void
    {
        $response = $this->actingAs($this->adminSdm)->get('/sdm/laporan/export-excel?jenis=1');
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'application/vnd.ms-excel'));
    }
}
