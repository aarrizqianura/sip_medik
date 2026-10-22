<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\DokumenSip;
use App\Models\KredensialKlinis;
use App\Models\Ksm;
use App\Models\Notifikasi;
use App\Models\User;
use App\Services\SipMonitoringService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengesahanDireksiTahap4Test extends TestCase
{
    use DatabaseTransactions;

    protected User $adminSdm;

    protected User $superAdmin;

    protected User $dokterUser;

    protected Dokter $dokter;

    protected KredensialKlinis $kredensial;

    protected DokumenSip $sip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminSdm = User::where('role', 'admin_sdm')->first()
            ?: User::create([
                'nama' => 'Staf SDM RSAS',
                'username' => 'admin_sdm_t4',
                'email' => 'sdm_t4@ansarisaleh.id',
                'no_wa' => '081234567899',
                'password' => bcrypt('password'),
                'role' => 'admin_sdm',
                'aktif' => true,
            ]);

        $this->superAdmin = User::where('role', 'super_admin')->first();

        // Siapkan dokter dengan KSM dan STR terkunci (hasil Tahap 1)
        $ksm = Ksm::first() ?: Ksm::create(['nama_ksm' => 'KSM Bedah', 'kode_ksm' => 'KSM-BED']);

        $uniqueId = rand(1000, 9999);
        $this->dokterUser = User::create([
            'nama' => 'dr. Budi Santoso, Sp.B',
            'username' => 'dr_budi_t4_'.$uniqueId,
            'email' => 'budi_t4_'.$uniqueId.'@ansarisaleh.id',
            'no_wa' => '081299887766',
            'password' => bcrypt('password'),
            'role' => 'dokter',
            'aktif' => true,
        ]);

        $this->dokter = Dokter::create([
            'id_user' => $this->dokterUser->id,
            'nip_nrp' => '19840101'.rand(1000000000, 9999999999),
            'id_ksm' => $ksm->id,
            'spesialisasi' => 'Spesialis Bedah Umum',
            'subspesialis' => 'Bedah Digestif',
            'tempat_praktik_ke' => 1,
            'status_kepegawaian' => 'PNS',
            'status_str' => 'seumur_hidup',
            'nomor_str' => '33118012'.rand(10000000, 99999999),
            'tanggal_terbit_str' => Carbon::now()->subYears(2)->toDateString(),
            'file_str' => 'str/str_sample_budi.pdf',
            'status_verifikasi_str' => 'tervalidasi',
            'is_str_locked' => true,
            'status_praktik' => 'MENUNGGU_PENGESAHAN',
            'is_arsip_locked' => false,
        ]);

        // Dokumen SIP (Tahap 2 & 3)
        $this->sip = DokumenSip::create([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '503/099-SIP/DPMPTSP/'.rand(100, 999),
            'tempat_praktik_ke' => 1,
            'fasyankes' => 'RSUD dr. H. Moch. Ansari Saleh',
            'status_kecukupan_skp' => true,
            'tanggal_terbit' => Carbon::now()->subMonths(1)->toDateString(),
            'tanggal_kadaluarsa' => Carbon::now()->addYears(5)->toDateString(),
            'status' => 'aktif',
            'status_verifikasi' => 'disetujui',
            'file_sip' => 'sip/sip_budi.pdf',
        ]);

        // Rekomendasi Komite Medik yang telah diterbitkan (Tahap 3 Selesai)
        $this->kredensial = KredensialKlinis::create([
            'id_dokter' => $this->dokter->id,
            'status_kredensial' => 'rekomendasi_terbit',
            'tahap_rekredensial' => 'rekomendasi_terbit',
            'status_rekomendasi' => 'diterbitkan',
            'nomor_surat_rekomendasi' => '445/789/KOM-MED/RSAS/2026',
            'tanggal_rekomendasi' => Carbon::today(),
            'ketua_komite_medik' => 'dr. H. Hendra Sanjaya, Sp.B (Ketua Komite Medik)',
            'nama_mitra_bestari' => 'dr. Spesialis Konsultan Mitra Bestari',
            'tanggal_sidang' => Carbon::yesterday(),
            'evaluasi_logbook' => 'Logbook tindakan bedah digestif memenuhi standar volume 3 tahun.',
            'evaluasi_portofolio' => 'Sertifikat kompetensi dan pelatihan terkini terverifikasi.',
            'daftar_rkk' => [
                ['kode' => 'BD-01', 'nama' => 'Apendektomi Laparoskopi', 'kategori' => 'Bedah', 'status' => 'mandiri'],
                ['kode' => 'BD-02', 'nama' => 'Kolesistektomi Laparoskopi', 'kategori' => 'Bedah', 'status' => 'mandiri'],
                ['kode' => 'BD-03', 'nama' => 'Reseksi Hepar Lanjutan', 'kategori' => 'Bedah', 'status' => 'supervisi'],
            ],
        ]);
    }

    /**
     * Test 1: Bagian SDM dapat mengakses antrean rekomendasi yang siap diterbitkan SK Direktur
     */
    public function test_sdm_dapat_melihat_antrean_rekomendasi_siap_disahkan_direktur(): void
    {
        $response = $this->actingAs($this->adminSdm)->get(route('sdm.pengesahan.index', ['tab' => 'menunggu']));

        $response->assertStatus(200);
        $response->assertSee('Pengesahan Direksi', false);
        $response->assertSee('e-Arsip Terpadu (SDM)', false);
        $response->assertSee('dr. Budi Santoso, Sp.B');
        $response->assertSee('445/789/KOM-MED/RSAS/2026');
        $response->assertSee('Terbitkan SK Direktur', false);
    }

    /**
     * Test 2: Halaman formulir penerbitan SK Direktur memuat metadata rujukan rekomendasi dan RKK
     */
    public function test_sdm_dapat_membuka_formulir_pengesahan_sk_direktur(): void
    {
        $response = $this->actingAs($this->adminSdm)->get(route('sdm.pengesahan.create', $this->kredensial->id));

        $response->assertStatus(200);
        $response->assertSee('dr. Budi Santoso, Sp.B');
        $response->assertSee('445/789/KOM-MED/RSAS/2026');
        $response->assertSee('Apendektomi Laparoskopi');
        $response->assertSee('Sahkan SK Direktur', false);
        $response->assertSee('AKTIF_LEGAL');
    }

    /**
     * Test 3: Pengesahan Direksi: Bagian SDM mengunggah SK Direktur, sistem mengunci STR, SIP, SPK, RKK ke e-Arsip,
     * status dokter AKTIF_LEGAL, dan masa SPK aktif 3 tahun.
     */
    public function test_penerbitan_sk_direktur_mengaktifkan_status_aktif_legal_dan_mengunci_e_arsip(): void
    {
        Storage::fake('public');

        $fileSk = UploadedFile::fake()->create('sk_direktur_spk_rkk.pdf', 1024, 'application/pdf');

        $tglSk = Carbon::today()->toDateString();
        $tglBerakhirSpk = Carbon::today()->addYears(3)->toDateString();
        $noSk = '800/999-DIR/RSAS/SK-BED/2026';

        $response = $this->actingAs($this->adminSdm)->post(route('sdm.pengesahan.store', $this->kredensial->id), [
            'nomor_sk_direktur' => $noSk,
            'tanggal_sk_direktur' => $tglSk,
            'tanggal_berakhir_spk' => $tglBerakhirSpk,
            'file_sk_direktur' => $fileSk,
            'catatan_pengesahan_direksi' => 'SK Direktur resmi menetapkan SPK & RKK masa 3 tahun.',
            'kunci_arsip' => 1,
        ]);

        $response->assertRedirect(route('sdm.arsip.show', $this->dokter->id));
        $response->assertSessionHas('sukses');

        // 1. Verifikasi Status Profil Dokter diperbarui menjadi AKTIF_LEGAL
        $this->dokter->refresh();
        $this->assertEquals('AKTIF_LEGAL', $this->dokter->status_praktik);
        $this->assertTrue((bool) $this->dokter->is_arsip_locked);
        $this->assertTrue((bool) $this->dokter->is_str_locked);
        $this->assertEquals($noSk, $this->dokter->nomor_sk_direktur);
        $this->assertNotNull($this->dokter->arsip_locked_at);
        $this->assertEquals($this->adminSdm->id, $this->dokter->arsip_locked_by);
        $this->assertNotNull($this->dokter->file_sk_direktur);

        // 2. Verifikasi Masa Berlaku SPK disetel aktif (3 tahun) & terkunci
        $this->kredensial->refresh();
        $this->assertEquals('aktif', $this->kredensial->status_kredensial);
        $this->assertEquals('spk_diterbitkan', $this->kredensial->tahap_rekredensial);
        $this->assertEquals('disahkan_direktur', $this->kredensial->status_rekomendasi);
        $this->assertEquals($noSk, $this->kredensial->nomor_sk_direktur);
        $this->assertEquals($noSk, $this->kredensial->nomor_spk);
        $this->assertEquals($tglBerakhirSpk, $this->kredensial->tanggal_berakhir_spk->toDateString());
        $this->assertTrue((bool) $this->kredensial->is_spk_locked);
        $this->assertTrue((bool) $this->kredensial->is_rkk_locked);
        $this->assertEquals($this->adminSdm->id, $this->kredensial->disahkan_oleh_sdm);

        // 3. Verifikasi Berkas SIP terkunci ke e-Arsip
        $this->sip->refresh();
        $this->assertTrue((bool) $this->sip->is_sip_locked);
        $this->assertEquals('disetujui', $this->sip->status_verifikasi);
        $this->assertNotNull($this->sip->arsip_locked_at);

        // 4. Verifikasi Jejak Audit Tercipta
        $this->assertDatabaseHas('audit_logs', [
            'aksi' => 'PENGESAHAN_SK_DIREKTUR',
            'modul' => 'SDM & Direksi',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'aksi' => 'PENGARSIPAN_DIGITAL_E_ARSIP',
            'modul' => 'SDM e-Arsip',
        ]);

        // 5. Verifikasi Notifikasi Terkirim ke Dokter
        $this->assertDatabaseHas('notifikasi', [
            'id_user' => $this->dokterUser->id,
        ]);
    }

    /**
     * Test 4: Tanggal jatuh tempo SIP resmi masuk ke dalam mesin pelacakan otomatis
     */
    public function test_tanggal_jatuh_tempo_sip_masuk_ke_mesin_pelacakan_otomatis(): void
    {
        // Berikan status AKTIF_LEGAL pada dokter
        $this->dokter->update([
            'status_praktik' => 'AKTIF_LEGAL',
            'is_arsip_locked' => true,
        ]);

        $this->sip->update([
            'status_verifikasi' => 'disetujui',
            'is_sip_locked' => true,
            'tanggal_kadaluarsa' => Carbon::today()->addDays(30), // Tepat H-30 (Due / Kritis)
        ]);

        $monitoringService = app(SipMonitoringService::class);
        $stats = $monitoringService->getStatistik();

        $this->assertGreaterThan(0, $stats['dokterAktifLegal']);
        $this->assertGreaterThan(0, $stats['arsipTerkunci']);

        // Jalankan pengecekan harian mesin pelacak
        $hasilCheck = $monitoringService->runDailyCheck();

        $this->assertGreaterThan(0, $hasilCheck['total_diperiksa']);
        $this->assertGreaterThanOrEqual(0, $hasilCheck['notifikasi_dikirim']);
    }

    /**
     * Test 5: Repositori Digital Kepegawaian (e-Arsip) menampilkan 4 pilar lengkap (STR, SIP, SPK, RKK)
     */
    public function test_detail_repositori_e_arsip_menampilkan_4_pilar_terkunci(): void
    {
        $this->dokter->update([
            'status_praktik' => 'AKTIF_LEGAL',
            'is_arsip_locked' => true,
            'nomor_sk_direktur' => '800/100-DIR/RSAS/SK-MED/2026',
            'tanggal_sk_direktur' => Carbon::today(),
            'file_sk_direktur' => 'sk_direktur/sample.pdf',
        ]);

        $this->kredensial->update([
            'status_kredensial' => 'aktif',
            'nomor_sk_direktur' => '800/100-DIR/RSAS/SK-MED/2026',
            'tanggal_terbit_spk' => Carbon::today(),
            'tanggal_berakhir_spk' => Carbon::today()->addYears(3),
            'is_spk_locked' => true,
            'is_rkk_locked' => true,
        ]);

        $this->sip->update([
            'status_verifikasi' => 'disetujui',
            'is_sip_locked' => true,
        ]);

        $response = $this->actingAs($this->adminSdm)->get(route('sdm.arsip.show', $this->dokter->id));

        $response->assertStatus(200);
        $response->assertSee('HAK PRAKTIK PENUH (AKTIF_LEGAL)');
        $response->assertSee('TERKUNCI & TERARSIP RESMI', false);
        $response->assertSee('1. Pilar STR (16 Digit)');
        $response->assertSee('2. Pilar Berkas SIP');
        $response->assertSee('3. Pilar SPK Direktur');
        $response->assertSee('4. Lampiran RKK');
        $response->assertSee($this->dokter->nomor_str);
        $response->assertSee('800/100-DIR/RSAS/SK-MED/2026');
    }

    /**
     * Test 6: Cetak Berita Acara Pengesahan Direksi & e-Arsip dapat diakses untuk arsip fisik/PDF
     */
    public function test_cetak_berita_acara_e_arsip_dapat_diakses(): void
    {
        $this->dokter->update([
            'status_praktik' => 'AKTIF_LEGAL',
            'is_arsip_locked' => true,
            'nomor_sk_direktur' => '800/100-DIR/RSAS/SK-MED/2026',
        ]);

        $response = $this->actingAs($this->adminSdm)->get(route('sdm.arsip.cetak', $this->dokter->id));

        $response->assertStatus(200);
        $response->assertSee('BERITA ACARA PENGESAHAN DIREKSI', false);
        $response->assertSee('PENGARSIPAN DIGITAL TERPADU (e-Arsip)', false);
        $response->assertSee('dr. Budi Santoso, Sp.B');
        $response->assertSee('AKTIF_LEGAL (Hak Praktik Penuh)');
        $response->assertSee('Hj. Rusmawati, S.Kep, Ns, MM');
        $response->assertSee('dr. Among Wibowo, M.Kes, Sp.S');
    }

    /**
     * Test 7: Dokter yang bersangkutan dapat mengakses halaman e-Arsip miliknya melalui Portal Dokter
     */
    public function test_dokter_dapat_melihat_e_arsip_pribadi_di_portal_dokter(): void
    {
        $this->dokter->update([
            'status_praktik' => 'AKTIF_LEGAL',
            'is_arsip_locked' => true,
        ]);

        $response = $this->actingAs($this->dokterUser)->get(route('dokter.arsip.saya'));

        $response->assertStatus(200);
        $response->assertSee('dr. Budi Santoso, Sp.B');
        $response->assertSee('HAK PRAKTIK PENUH (AKTIF_LEGAL)');
    }

    /**
     * Test 8: Validasi formulir pengesahan: nomor SK dan tanggal wajib diisi dengan benar
     */
    public function test_pengesahan_sk_direktur_memerlukan_data_wajib(): void
    {
        $response = $this->actingAs($this->adminSdm)->post(route('sdm.pengesahan.store', $this->kredensial->id), [
            'nomor_sk_direktur' => '',
            'tanggal_sk_direktur' => '',
            'tanggal_berakhir_spk' => '',
        ]);

        $response->assertSessionHasErrors(['nomor_sk_direktur', 'tanggal_sk_direktur', 'tanggal_berakhir_spk']);
    }
}
