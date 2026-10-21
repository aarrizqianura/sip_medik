<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\Ksm;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManajemenStrDokterTest extends TestCase
{
    protected User $adminSdm;

    protected User $dokterUser;

    protected Dokter $dokter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminSdm = User::where('role', 'admin_sdm')->first();
        $this->dokterUser = User::where('role', 'dokter')->first();
        $this->dokter = $this->dokterUser->dokter;
    }

    /**
     * Admin SDM dapat mendaftarkan dokter baru lengkap dengan nomor STR 16 digit, subspesialis, deklarasi praktik, dan salinan e-STR
     */
    public function test_sdm_dapat_mendaftarkan_dokter_baru_dengan_data_dan_berkas_str(): void
    {
        Storage::fake('public');

        $ksm = Ksm::first();
        $fileStr = UploadedFile::fake()->create('scan_estr_resmi.pdf', 800, 'application/pdf');
        $uniqueNum = rand(1000, 9999);
        $nomorStr16 = '331180122312'.$uniqueNum;

        $response = $this->actingAs($this->adminSdm)->post(route('sdm.dokter.store'), [
            'nama' => 'dr. Dokter Uji STR, Sp.PD',
            'nip_nrp' => '19890101'.$uniqueNum,
            'id_ksm' => $ksm->id,
            'spesialisasi' => 'Spesialis Penyakit Dalam',
            'subspesialis' => 'Subspesialis Gastroenterohepatologi',
            'tempat_praktik_ke' => 1,
            'status_kepegawaian' => 'PNS',
            'status_str' => 'seumur_hidup',
            'nomor_str' => $nomorStr16,
            'tanggal_terbit_str' => '2023-05-10',
            'file_str' => $fileStr,
            'username' => 'dr_uji_str_'.$uniqueNum,
            'no_wa' => '08129999'.$uniqueNum,
            'email' => 'uji_str_'.$uniqueNum.'@rsud.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('sdm.dokter.index'));
        $response->assertSessionHas('sukses');

        $this->assertDatabaseHas('dokters', [
            'nomor_str' => $nomorStr16,
            'status_str' => 'seumur_hidup',
            'subspesialis' => 'Subspesialis Gastroenterohepatologi',
            'tempat_praktik_ke' => 1,
            'status_verifikasi_str' => 'menunggu_verifikasi',
            'is_str_locked' => false,
        ]);

        $dokterBaru = Dokter::where('nomor_str', $nomorStr16)->first();
        $this->assertNotNull($dokterBaru->file_str);
        Storage::disk('public')->assertExists($dokterBaru->file_str);
    }

    /**
     * Validasi menolak jika nomor STR bukan 16 digit angka
     */
    public function test_perekaman_str_menolak_jika_bukan_16_digit(): void
    {
        Storage::fake('public');

        $ksm = Ksm::first();
        $fileStr = UploadedFile::fake()->create('scan_str.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->adminSdm)->post(route('sdm.dokter.store'), [
            'nama' => 'dr. Format Salah, Sp.A',
            'nip_nrp' => '199501019999',
            'id_ksm' => $ksm->id,
            'spesialisasi' => 'Spesialis Anak',
            'tempat_praktik_ke' => 1,
            'status_kepegawaian' => 'PNS',
            'status_str' => 'seumur_hidup',
            'nomor_str' => '12345', // Kurang dari 16 digit
            'tanggal_terbit_str' => '2023-01-01',
            'file_str' => $fileStr,
            'username' => 'dr_formatsalah',
            'no_wa' => '081234567800',
            'email' => 'formatsalah@rsud.id',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('nomor_str');
    }

    /**
     * Subkomite Kredensial dapat memeriksa kesesuaian SatuSehat SDMK dan mengesahkan/mengunci STR
     */
    public function test_subkomite_kredensial_dapat_memvalidasi_dan_mengunci_str(): void
    {
        $verifikator = User::where('role', 'verifikator_komite')->first();

        // Pastikan dokter5 dalam status menunggu verifikasi
        $dokter5 = User::where('username', 'dokter5')->first()->dokter;
        $dokter5->status_verifikasi_str = 'menunggu_verifikasi';
        $dokter5->is_str_locked = false;
        $dokter5->save();

        $response = $this->actingAs($verifikator)->post(route('komite.verifikasi_str.validasi', $dokter5->id), [
            'keputusan' => 'tervalidasi',
            'catatan' => 'Kesesuaian STR 16 digit terkonfirmasi pada pangkalan data SatuSehat SDMK Kemenkes RI.',
        ]);

        $response->assertRedirect(route('komite.verifikasi_str.index'));
        $response->assertSessionHas('sukses');

        $dokter5->refresh();
        $this->assertEquals('tervalidasi', $dokter5->status_verifikasi_str);
        $this->assertTrue($dokter5->is_str_locked);
        $this->assertEquals($verifikator->id, $dokter5->verified_str_by);
        $this->assertNotNull($dokter5->satusehat_verified_at);
        $this->assertTrue($dokter5->isStrValidDanTerkunci());
    }

    /**
     * Nomor STR yang sudah terkunci (read-only) dicegah diubah oleh Admin SDM
     */
    public function test_nomor_str_terkunci_tidak_dapat_diubah_oleh_sdm(): void
    {
        // Dokter1 berstatus tervalidasi & terkunci
        $dokter1 = User::where('username', 'dokter1')->first()->dokter;
        $dokter1->is_str_locked = true;
        $dokter1->status_verifikasi_str = 'tervalidasi';
        $originalStr = $dokter1->nomor_str;
        $dokter1->save();

        $response = $this->actingAs($this->adminSdm)->put(route('sdm.dokter.update', $dokter1->id), [
            'nama' => $dokter1->user->nama,
            'nip_nrp' => $dokter1->nip_nrp,
            'id_ksm' => $dokter1->id_ksm,
            'spesialisasi' => $dokter1->spesialisasi,
            'subspesialis' => 'Subspesialis Baru',
            'tempat_praktik_ke' => 1,
            'status_kepegawaian' => $dokter1->status_kepegawaian,
            'status_str' => $dokter1->status_str,
            'nomor_str' => '9999999999999999', // Coba ubah nomor STR yang terkunci
            'tanggal_terbit_str' => '2022-01-01',
            'username' => $dokter1->user->username,
            'no_wa' => $dokter1->user->no_wa,
            'email' => $dokter1->user->email,
        ]);

        $response->assertSessionHasErrors('nomor_str');
        $dokter1->refresh();
        $this->assertEquals($originalStr, $dokter1->nomor_str);
    }

    /**
     * Syarat Mutlak (Hard Constraint): Modul pengurusan SIP hanya aktif jika STR tervalidasi dan terkunci
     */
    public function test_modul_pengurusan_sip_ditolak_jika_str_belum_tervalidasi(): void
    {
        $userDokter5 = User::where('username', 'dokter5')->first();
        $dokter5 = $userDokter5->dokter;

        // Set status belum tervalidasi
        $dokter5->status_verifikasi_str = 'menunggu_verifikasi';
        $dokter5->is_str_locked = false;
        $dokter5->save();

        // 1. Dokter tidak dapat membuka form tambah SIP
        $responseCreate = $this->actingAs($userDokter5)->get(route('dokter.pengajuan.create'));
        $responseCreate->assertRedirect(route('dokter.pengajuan.index'));
        $responseCreate->assertSessionHas('error');

        // 2. Dokter tidak dapat mengirimkan permohonan SIP baru
        Storage::fake('public');
        $fileSip = UploadedFile::fake()->create('sip_baru.pdf', 500, 'application/pdf');

        $responseStore = $this->actingAs($userDokter5)->post(route('dokter.pengajuan.store'), [
            'nomor_sip' => '446/TEST-SIP/2026',
            'tempat_praktik_ke' => 1,
            'tanggal_terbit' => '2026-01-01',
            'tanggal_kadaluarsa' => '2029-01-01',
            'file_sip' => $fileSip,
        ]);

        $responseStore->assertRedirect(route('dokter.pengajuan.index'));
        $responseStore->assertSessionHas('error');
    }
}
