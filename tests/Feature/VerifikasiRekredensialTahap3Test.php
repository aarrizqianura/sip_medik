<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\DokumenSip;
use App\Models\KredensialKlinis;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VerifikasiRekredensialTahap3Test extends TestCase
{
    protected User $verifikator;

    protected User $superAdmin;

    protected User $dokterUser;

    protected Dokter $dokter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->verifikator = User::where('role', 'verifikator_komite')->first()
            ?: User::create([
                'nama' => 'Verifikator Komite Medik',
                'username' => 'verifikator_t3',
                'password' => bcrypt('password'),
                'role' => 'verifikator_komite',
                'is_aktif' => true,
            ]);

        $this->superAdmin = User::where('role', 'super_admin')->first();
        $this->dokterUser = User::where('role', 'dokter')->first();
        $this->dokter = $this->dokterUser->dokter;
    }

    /**
     * Test 1: Verifikator dapat menolak pindaian SIP yang buram/salah disertai catatan evaluasi
     */
    public function test_pemeriksaan_dokumen_verifikator_dapat_menolak_sip_dengan_catatan_evaluasi(): void
    {
        Storage::fake('public');

        $sip = DokumenSip::create([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '503/BURAM-TEST/'.rand(1000, 9999),
            'tempat_praktik_ke' => 1,
            'fasyankes' => 'RSUD dr. H. Moch. Ansari Saleh',
            'status_kecukupan_skp' => true,
            'tanggal_terbit' => Carbon::today()->subMonths(2),
            'tanggal_kadaluarsa' => Carbon::today()->addYears(5),
            'status' => 'aktif',
            'status_verifikasi' => 'menunggu',
            'file_sip' => 'sip/test_buram.pdf',
        ]);

        $response = $this->actingAs($this->verifikator)->post(route('komite.verifikasi.proses', $sip->id), [
            'status_verifikasi' => 'ditolak',
            'catatan_verifikasi' => 'Pindaian dokumen buram dan tidak terbaca jelas. Mohon unggah ulang pindaian resolusi tinggi.',
            'checklist_keterbacaan_pindaian' => 0,
            'checklist_keabsahan_fasyankes' => 1,
            'checklist_masa_berlaku' => 1,
        ]);

        $response->assertRedirect(route('komite.verifikasi.index'));
        $response->assertSessionHas('error');

        $sip->refresh();
        $this->assertEquals('ditolak', $sip->status_verifikasi);
        $this->assertStringContainsString('Pindaian dokumen buram', $sip->catatan_verifikasi);
        $this->assertEquals($this->verifikator->id, $sip->verified_by);
        $this->assertNotNull($sip->verified_at);
    }

    /**
     * Test 2: Menolak dokumen SIP tanpa catatan evaluasi wajib dicegah oleh sistem
     */
    public function test_pemeriksaan_dokumen_menolak_tanpa_catatan_evaluasi_menghasilkan_error(): void
    {
        $sip = DokumenSip::create([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '503/TOLAK-NOCATATAN/'.rand(1000, 9999),
            'tempat_praktik_ke' => 1,
            'fasyankes' => 'RSUD dr. H. Moch. Ansari Saleh',
            'status_kecukupan_skp' => true,
            'tanggal_terbit' => Carbon::today()->subMonths(1),
            'tanggal_kadaluarsa' => Carbon::today()->addYears(5),
            'status' => 'aktif',
            'status_verifikasi' => 'menunggu',
            'file_sip' => 'sip/test_reject.pdf',
        ]);

        $response = $this->actingAs($this->verifikator)->post(route('komite.verifikasi.proses', $sip->id), [
            'status_verifikasi' => 'ditolak',
            'catatan_verifikasi' => '',
        ]);

        $response->assertSessionHasErrors('catatan_verifikasi');
        $sip->refresh();
        $this->assertEquals('menunggu', $sip->status_verifikasi);
    }

    /**
     * Test 3: Pengesahan SIP memicu alur rekredensialing otomatis jika SPK/RKK telah melampaui siklus evaluasi (3 tahun)
     */
    public function test_pengesahan_sip_memicu_alur_rekredensial_otomatis_jika_spk_melampaui_3_tahun(): void
    {
        // Set SPK lama dokter yang telah melampaui siklus 3 tahun (misal kadaluarsa 10 hari lalu)
        $spkLama = KredensialKlinis::create([
            'id_dokter' => $this->dokter->id,
            'nomor_spk' => '800/SPK-KADALUARSA/2023',
            'tanggal_terbit_spk' => Carbon::today()->subYears(3)->subDays(10),
            'tanggal_berakhir_spk' => Carbon::today()->subDays(10),
            'status_kredensial' => 'kedaluwarsa',
        ]);

        $sip = DokumenSip::create([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '503/SIP-VALID/'.rand(1000, 9999),
            'tempat_praktik_ke' => 1,
            'fasyankes' => 'RSUD dr. H. Moch. Ansari Saleh',
            'status_kecukupan_skp' => true,
            'tanggal_terbit' => Carbon::today(),
            'tanggal_kadaluarsa' => Carbon::today()->addYears(5),
            'status' => 'aktif',
            'status_verifikasi' => 'menunggu',
            'file_sip' => 'sip/valid_test.pdf',
        ]);

        // Verifikator menyetujui SIP
        $response = $this->actingAs($this->verifikator)->post(route('komite.verifikasi.proses', $sip->id), [
            'status_verifikasi' => 'disetujui',
            'catatan_verifikasi' => 'Berkas pindaian SIP absah, fasyankes sesuai, masa berlaku 5 tahun valid.',
            'checklist_keterbacaan_pindaian' => 1,
            'checklist_keabsahan_fasyankes' => 1,
            'checklist_masa_berlaku' => 1,
        ]);

        $sip->refresh();
        $this->assertEquals('disetujui', $sip->status_verifikasi);

        // Sistem secara otomatis memicu status proses_rekredensial
        $kredensialAktif = KredensialKlinis::where('id_dokter', $this->dokter->id)
            ->where('status_kredensial', 'proses_rekredensial')
            ->latest('id')
            ->first();

        $this->assertNotNull($kredensialAktif);
        $this->assertEquals('proses_rekredensial', $kredensialAktif->status_kredensial);
        $this->assertEquals('menunggu_sidang', $kredensialAktif->tahap_rekredensial);
        $this->assertStringContainsString('3 tahun', $kredensialAktif->alasan_rekredensial);

        // Verifikator otomatis diarahkan ke ruang Sidang Kredensial & Pengisian RKK
        $response->assertRedirect(route('komite.kredensial.sidang', $kredensialAktif->id));
        $response->assertSessionHas('sukses');
    }

    /**
     * Test 4: Pengesahan SIP memicu alur rekredensialing jika ada permohonan penambahan kewenangan klinis baru
     */
    public function test_pengesahan_sip_memicu_rekredensial_jika_ada_permohonan_kewenangan_baru(): void
    {
        // SPK dokter masih aktif (misal sisa 1 tahun)
        KredensialKlinis::create([
            'id_dokter' => $this->dokter->id,
            'nomor_spk' => '800/SPK-MASIH-AKTIF/2025',
            'tanggal_terbit_spk' => Carbon::today()->subYears(1),
            'tanggal_berakhir_spk' => Carbon::today()->addYears(2),
            'status_kredensial' => 'aktif',
        ]);

        $sip = DokumenSip::create([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '503/SIP-NEW-PRIV/'.rand(1000, 9999),
            'tempat_praktik_ke' => 1,
            'fasyankes' => 'RSUD dr. H. Moch. Ansari Saleh',
            'status_kecukupan_skp' => true,
            'tanggal_terbit' => Carbon::today(),
            'tanggal_kadaluarsa' => Carbon::today()->addYears(5),
            'status' => 'aktif',
            'status_verifikasi' => 'menunggu',
            'file_sip' => 'sip/new_priv.pdf',
        ]);

        // Verifikator menyetujui SIP dan mencentang permohonan penambahan kewenangan klinis baru
        $response = $this->actingAs($this->verifikator)->post(route('komite.verifikasi.proses', $sip->id), [
            'status_verifikasi' => 'disetujui',
            'catatan_verifikasi' => 'SIP absah. Mengajukan penambahan tindakan minimal invasif laparoskopi lanjutan.',
            'checklist_keterbacaan_pindaian' => 1,
            'checklist_keabsahan_fasyankes' => 1,
            'checklist_masa_berlaku' => 1,
            'permohonan_kewenangan_baru' => 1,
            'catatan_kewenangan_baru' => 'Sertifikat fellowship laparoskopi lanjutan telah terlampir.',
        ]);

        $kredensialAktif = KredensialKlinis::where('id_dokter', $this->dokter->id)
            ->where('status_kredensial', 'proses_rekredensial')
            ->latest('id')
            ->first();

        $this->assertNotNull($kredensialAktif);
        $this->assertEquals('penambahan_kewenangan', $kredensialAktif->jenis_pengajuan);
        $this->assertStringContainsString('kewenangan klinis baru', $kredensialAktif->alasan_rekredensial);
        $response->assertRedirect(route('komite.kredensial.sidang', $kredensialAktif->id));
    }

    /**
     * Test 5: Sidang Kredensial bersama Mitra Bestari mengevaluasi logbook & menetapkan form digital RKK
     */
    public function test_subkomite_kredensial_dan_mitra_bestari_dapat_mengisi_form_digital_rkk_pada_sidang(): void
    {
        $kredensial = KredensialKlinis::create([
            'id_dokter' => $this->dokter->id,
            'nomor_spk' => 'SPK-SIDANG/'.rand(100, 999),
            'status_kredensial' => 'proses_rekredensial',
            'tahap_rekredensial' => 'menunggu_sidang',
            'jenis_pengajuan' => 'rekredensial_berkala',
        ]);

        $rkkPayload = [
            [
                'kode' => 'BED-01',
                'nama' => 'Laparotomi Eksplorasi',
                'kategori' => 'Bedah Digestif',
                'status' => 'mandiri',
                'catatan' => 'Sesuai kompetensi Sp.B mandiri',
            ],
            [
                'kode' => 'BED-02',
                'nama' => 'Apendiktomi Laparoskopi',
                'kategori' => 'Bedah Minimal Invasif',
                'status' => 'mandiri',
                'catatan' => 'Rutin',
            ],
            [
                'kode' => 'BED-05',
                'nama' => 'Kolesistektomi Laparoskopi Lanjutan',
                'kategori' => 'Bedah Minimal Invasif',
                'status' => 'supervisi',
                'catatan' => 'Supervisi oleh konsultan digestif senior',
            ],
        ];

        $response = $this->actingAs($this->verifikator)->post(route('komite.kredensial.simpan_sidang', $kredensial->id), [
            'tanggal_sidang' => Carbon::today()->format('Y-m-d'),
            'nama_mitra_bestari' => 'dr. H. Fachrul Razi, Sp.B (Mitra Bestari)',
            'evaluasi_logbook' => 'Total 340 tindakan tercatat dalam logbook 3 tahun terakhir tanpa komplikasi berat.',
            'evaluasi_portofolio' => 'Sertifikat kompetensi kolegium aktif, bukti CPD terpenuhi.',
            'catatan_mitra_bestari' => 'Direkomendasikan kewenangan mandiri untuk prosedur rutin dan supervisi untuk tindakan lanjutan.',
            'rkk' => $rkkPayload,
            'aksi' => 'simpan_saja',
        ]);

        $response->assertRedirect(route('komite.kredensial.sidang', $kredensial->id));
        $response->assertSessionHas('sukses');

        $kredensial->refresh();
        $this->assertEquals('sidang_komite', $kredensial->status_kredensial);
        $this->assertEquals('sidang_selesai', $kredensial->tahap_rekredensial);
        $this->assertEquals(2, $kredensial->rkk_mandiri_count);
        $this->assertEquals(1, $kredensial->rkk_supervisi_count);
        $this->assertEquals(3, $kredensial->rkk_total_count);
    }

    /**
     * Test 6: Komite Medik menerbitkan Surat Rekomendasi Kewenangan Klinis Digital kepada Direktur
     */
    public function test_komite_medik_menerbitkan_surat_rekomendasi_kewenangan_klinis_digital_kepada_direktur(): void
    {
        $kredensial = KredensialKlinis::create([
            'id_dokter' => $this->dokter->id,
            'nomor_spk' => 'SPK-TEST/'.rand(100, 999),
            'status_kredensial' => 'sidang_komite',
            'tahap_rekredensial' => 'sidang_selesai',
            'nama_mitra_bestari' => 'dr. Mitra Bestari, Sp.B',
            'daftar_rkk' => [
                ['kode' => 'T-01', 'nama' => 'Tindakan Mandiri', 'kategori' => 'Umum', 'status' => 'mandiri', 'catatan' => ''],
                ['kode' => 'T-02', 'nama' => 'Tindakan Supervisi', 'kategori' => 'Khusus', 'status' => 'supervisi', 'catatan' => ''],
            ],
        ]);

        $nomorSurat = '445/881/KOM-MED/RSAS/'.date('m/Y');

        $response = $this->actingAs($this->verifikator)->post(route('komite.kredensial.terbitkan_rekomendasi', $kredensial->id), [
            'nomor_surat_rekomendasi' => $nomorSurat,
            'tanggal_rekomendasi' => Carbon::today()->format('Y-m-d'),
            'ketua_komite_medik' => 'dr. H. Hendra Sanjaya, Sp.B, FInaCS (Ketua Komite Medik)',
            'catatan_rekomendasi' => 'Rekomendasi disetujui penuh berdasarkan evaluasi logbook dan portofolio.',
        ]);

        $response->assertRedirect(route('komite.kredensial.surat_rekomendasi', $kredensial->id));
        $response->assertSessionHas('sukses');

        $kredensial->refresh();
        $this->assertEquals('rekomendasi_terbit', $kredensial->status_kredensial);
        $this->assertEquals('diterbitkan', $kredensial->status_rekomendasi);
        $this->assertEquals($nomorSurat, $kredensial->nomor_surat_rekomendasi);

        // Halaman surat rekomendasi resmi dapat diakses dan menampilkan nomor surat
        $suratPage = $this->actingAs($this->verifikator)->get(route('komite.kredensial.surat_rekomendasi', $kredensial->id));
        $suratPage->assertStatus(200);
        $suratPage->assertSee($nomorSurat);
        $suratPage->assertSee('Rekomendasi Rincian Kewenangan Klinis (RKK)', false);
        $suratPage->assertSee('Direktur RSUD dr. H. Moch. Ansari Saleh');
    }

    /**
     * Test 7: Direktur mengesahkan Surat Penugasan Klinis (SPK) baru berjangka waktu 3 tahun
     */
    public function test_pengesahan_spk_baru_3_tahun_oleh_direktur(): void
    {
        $kredensial = KredensialKlinis::create([
            'id_dokter' => $this->dokter->id,
            'status_kredensial' => 'rekomendasi_terbit',
            'status_rekomendasi' => 'diterbitkan',
            'nomor_surat_rekomendasi' => '445/999/KOM-MED/RSAS/2026',
            'daftar_rkk' => [
                ['kode' => 'T-01', 'nama' => 'Tindakan Mandiri', 'kategori' => 'Umum', 'status' => 'mandiri', 'catatan' => ''],
            ],
        ]);

        $nomorSpkBaru = '800/777-DIR/RSAS/SPK-MED/'.date('Y');
        $tglTerbit = Carbon::today()->format('Y-m-d');
        $tglBerakhir = Carbon::today()->addYears(3)->format('Y-m-d');

        $response = $this->actingAs($this->superAdmin)->post(route('komite.kredensial.sahkan_spk', $kredensial->id), [
            'nomor_spk' => $nomorSpkBaru,
            'tanggal_terbit_spk' => $tglTerbit,
            'tanggal_berakhir_spk' => $tglBerakhir,
        ]);

        $response->assertRedirect(route('komite.kredensial.surat_rekomendasi', $kredensial->id));
        $response->assertSessionHas('sukses');

        $kredensial->refresh();
        $this->assertEquals('aktif', $kredensial->status_kredensial);
        $this->assertEquals('disahkan_direktur', $kredensial->status_rekomendasi);
        $this->assertEquals($nomorSpkBaru, $kredensial->nomor_spk);
        $this->assertEquals($tglBerakhir, $kredensial->tanggal_berakhir_spk->format('Y-m-d'));
    }
}
