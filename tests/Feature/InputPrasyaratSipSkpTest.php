<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\DokumenSip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InputPrasyaratSipSkpTest extends TestCase
{
    protected User $dokterUser;

    protected Dokter $dokter;

    protected User $verifikator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dokterUser = User::where('username', 'dokter1')->first();
        $this->dokter = $this->dokterUser->dokter;
        $this->verifikator = User::where('role', 'verifikator_komite')->first();

        // Pastikan dokter1 memiliki STR tervalidasi dan terkunci
        $this->dokter->update([
            'status_verifikasi_str' => 'tervalidasi',
            'is_str_locked' => true,
            'nomor_str' => '3311801223120001',
        ]);
    }

    /**
     * Dokter dapat mengakses portal mandiri dan melihat form input prasyarat SIP & kecukupan SKP
     */
    public function test_dokter_dapat_mengakses_portal_mandiri_input_prasyarat_sip_dan_skp(): void
    {
        $response = $this->actingAs($this->dokterUser)->get(route('dokter.pengajuan.create'));

        $response->assertStatus(200);
        $response->assertSee('Tahap 2: Input Prasyarat SIP & Status Kecukupan SKP');
        $response->assertSee('Cek Prasyarat SKP (Satuan Kredit Profesi)');
        $response->assertSee('Platform SatuSehat Kemenkes');
        $response->assertSee('Tandai Status Kecukupan SKP Terpenuhi');
        $response->assertSee('Surat Rekomendasi Kecukupan SKP');
        $response->assertSee('Nomor Izin SIP dari DPMPTSP');
        $response->assertSee('RSUD dr. H. Moch. Ansari Saleh');
        $response->assertSee('Tertuju Khusus ke RSUD dr. H. Moch. Ansari Saleh');
        $response->assertSee('Wajib Format PDF');
        $response->assertSee('Maks 2 MB');
        $response->assertSee('Menunggu Verifikasi');
    }

    /**
     * Dokter dapat mengajukan SIP dengan menandai status kecukupan SKP SatuSehat
     * Data tersimpan di antrean sistem dengan status awal 'menunggu'
     */
    public function test_dokter_dapat_mengajukan_sip_dengan_menandai_kecukupan_skp_dan_status_awal_antrean(): void
    {
        Storage::fake('public');

        $fileSip = UploadedFile::fake()->create('lembar_sip_resmi.pdf', 1200, 'application/pdf');
        $nomorSip = '503/099-SIP.SpPD/DPMPTSP-BJM/'.rand(1000, 9999);
        $tglTerbit = Carbon::now()->toDateString();
        $tglKadaluarsa = Carbon::now()->addYears(5)->toDateString();

        $response = $this->actingAs($this->dokterUser)->post(route('dokter.pengajuan.store'), [
            'nomor_sip' => $nomorSip,
            'penerbit_sip' => 'DPMPTSP Kota Banjarmasin',
            'fasyankes' => 'RSUD dr. H. Moch. Ansari Saleh',
            'alamat_fasyankes' => 'Jl. Brigjend H. Hasan Basry No. 1, Banjarmasin',
            'tempat_praktik_ke' => 1,
            'status_kecukupan_skp' => true,
            'tanggal_terbit' => $tglTerbit,
            'tanggal_kadaluarsa' => $tglKadaluarsa,
            'file_sip' => $fileSip,
        ]);

        $response->assertRedirect(route('dokter.pengajuan.index'));
        $response->assertSessionHas('sukses');

        $this->assertDatabaseHas('dokumen_sip', [
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => $nomorSip,
            'fasyankes' => 'RSUD dr. H. Moch. Ansari Saleh',
            'status_kecukupan_skp' => true,
            'status_verifikasi' => 'menunggu', // Status awal di antrean sistem
            'tempat_praktik_ke' => 1,
        ]);

        $sip = DokumenSip::where('nomor_sip', $nomorSip)->first();
        $this->assertNotNull($sip);
        $this->assertTrue($sip->isSkpTerpenuhi());
        $this->assertEquals('Menunggu Verifikasi (Antrean Sistem)', $sip->status_verifikasi_antrean_label);
        Storage::disk('public')->assertExists($sip->file_sip);
    }

    /**
     * Dokter dapat mengunggah Surat Rekomendasi Kecukupan SKP dari platform SatuSehat Kemenkes
     */
    public function test_dokter_dapat_mengajukan_sip_dengan_unggah_rekomendasi_skp_satusehat(): void
    {
        Storage::fake('public');

        $fileSip = UploadedFile::fake()->create('scan_lembar_sip.pdf', 900, 'application/pdf');
        $fileSkp = UploadedFile::fake()->create('rekomendasi_skp_satusehat.pdf', 850, 'application/pdf');
        $nomorSip = '503/045-SIP.SpPD/DPMPTSP-BJM/'.rand(1000, 9999);
        $nomorRekom = 'REKOM-SKP/SATUSEHAT/'.rand(1000, 9999);

        $response = $this->actingAs($this->dokterUser)->post(route('dokter.pengajuan.store'), [
            'nomor_sip' => $nomorSip,
            'fasyankes' => 'RSUD dr. H. Moch. Ansari Saleh',
            'tempat_praktik_ke' => 1,
            'status_kecukupan_skp' => false, // Tidak menandai checkbox, tapi melampirkan berkas rekomendasi
            'nomor_rekomendasi_skp' => $nomorRekom,
            'file_rekomendasi_skp' => $fileSkp,
            'tanggal_terbit' => Carbon::now()->toDateString(),
            'tanggal_kadaluarsa' => Carbon::now()->addYears(5)->toDateString(),
            'file_sip' => $fileSip,
        ]);

        $response->assertRedirect(route('dokter.pengajuan.index'));
        $response->assertSessionHas('sukses');

        $sip = DokumenSip::where('nomor_sip', $nomorSip)->first();
        $this->assertNotNull($sip);
        $this->assertNotNull($sip->file_rekomendasi_skp);
        $this->assertEquals($nomorRekom, $sip->nomor_rekomendasi_skp);
        $this->assertEquals('menunggu', $sip->status_verifikasi);
        Storage::disk('public')->assertExists($sip->file_rekomendasi_skp);
        Storage::disk('public')->assertExists($sip->file_sip);
    }

    /**
     * Permohonan ditolak jika dokter tidak menandai kecukupan SKP dan tidak melampirkan berkas rekomendasi SKP SatuSehat
     */
    public function test_pengajuan_sip_ditolak_jika_skp_tidak_terpenuhi_atau_tanpa_rekomendasi(): void
    {
        Storage::fake('public');
        $fileSip = UploadedFile::fake()->create('lembar_sip.pdf', 800, 'application/pdf');

        $response = $this->actingAs($this->dokterUser)->post(route('dokter.pengajuan.store'), [
            'nomor_sip' => '503/FAIL-SKP/2026',
            'tempat_praktik_ke' => 1,
            'status_kecukupan_skp' => false,
            // file_rekomendasi_skp tidak diunggah
            'tanggal_terbit' => Carbon::now()->toDateString(),
            'tanggal_kadaluarsa' => Carbon::now()->addYears(5)->toDateString(),
            'file_sip' => $fileSip,
        ]);

        $response->assertSessionHasErrors('status_kecukupan_skp');
    }

    /**
     * Permohonan ditolak jika alamat fasyankes bukan tertuju khusus ke RSUD dr. H. Moch. Ansari Saleh
     */
    public function test_pengajuan_sip_ditolak_jika_fasyankes_bukan_rsud_ansari_saleh(): void
    {
        Storage::fake('public');
        $fileSip = UploadedFile::fake()->create('lembar_sip.pdf', 800, 'application/pdf');

        $response = $this->actingAs($this->dokterUser)->post(route('dokter.pengajuan.store'), [
            'nomor_sip' => '503/FAIL-FASYANKES/2026',
            'fasyankes' => 'Rumah Sakit Swasta Lain',
            'status_kecukupan_skp' => true,
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->toDateString(),
            'tanggal_kadaluarsa' => Carbon::now()->addYears(5)->toDateString(),
            'file_sip' => $fileSip,
        ]);

        $response->assertSessionHasErrors('fasyankes');
    }

    /**
     * Permohonan ditolak jika berkas pindaian lembar SIP bukan format PDF
     */
    public function test_pengajuan_sip_ditolak_jika_file_sip_bukan_pdf(): void
    {
        Storage::fake('public');
        $fileDocx = UploadedFile::fake()->create('lembar_sip.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($this->dokterUser)->post(route('dokter.pengajuan.store'), [
            'nomor_sip' => '503/NON-PDF/2026',
            'status_kecukupan_skp' => true,
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->toDateString(),
            'tanggal_kadaluarsa' => Carbon::now()->addYears(5)->toDateString(),
            'file_sip' => $fileDocx,
        ]);

        $response->assertSessionHasErrors('file_sip');
    }

    /**
     * Permohonan ditolak jika berkas pindaian lembar SIP melebihi batas 2 MB
     */
    public function test_pengajuan_sip_ditolak_jika_file_sip_melebihi_2mb(): void
    {
        Storage::fake('public');
        $fileBig = UploadedFile::fake()->create('lembar_sip_besar.pdf', 2500, 'application/pdf'); // 2.5 MB

        $response = $this->actingAs($this->dokterUser)->post(route('dokter.pengajuan.store'), [
            'nomor_sip' => '503/BIG-FILE/2026',
            'status_kecukupan_skp' => true,
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->toDateString(),
            'tanggal_kadaluarsa' => Carbon::now()->addYears(5)->toDateString(),
            'file_sip' => $fileBig,
        ]);

        $response->assertSessionHasErrors('file_sip');
    }

    /**
     * Subkomite Kredensial dapat melihat metadata SKP SatuSehat dan Fasyankes RSUD Ansari Saleh di layar telaah
     */
    public function test_verifikator_melihat_metadata_skp_dan_fasyankes_di_antrean_telaah(): void
    {
        $sip = DokumenSip::create([
            'id_dokter' => $this->dokter->id,
            'nomor_sip' => '503/TELAAH-SKP/DPMPTSP-BJM/2026',
            'fasyankes' => 'RSUD dr. H. Moch. Ansari Saleh',
            'status_kecukupan_skp' => true,
            'nomor_rekomendasi_skp' => 'REKOM-SKP-SATUSEHAT-001',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => Carbon::now()->toDateString(),
            'tanggal_kadaluarsa' => Carbon::now()->addYears(5)->toDateString(),
            'status' => 'aktif',
            'status_verifikasi' => 'menunggu',
            'file_sip' => 'sip/sample.pdf',
        ]);

        $response = $this->actingAs($this->verifikator)->get(route('komite.verifikasi.show', $sip->id));

        $response->assertStatus(200);
        $response->assertSee('503/TELAAH-SKP/DPMPTSP-BJM/2026');
        $response->assertSee('RSUD dr. H. Moch. Ansari Saleh');
        $response->assertSee('SKP Terpenuhi (SatuSehat)');
        $response->assertSee('REKOM-SKP-SATUSEHAT-001');
    }
}
