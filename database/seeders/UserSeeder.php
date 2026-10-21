<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\DokumenSip;
use App\Models\KredensialKlinis;
use App\Models\Ksm;
use App\Models\PengaturanSistem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Super Admin
        $superAdmin = User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'nama' => 'IT PDE Super Admin',
                'email' => 'itpde@ansarisaleh.id',
                'no_wa' => '081234567890',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'aktif' => true,
            ]
        );

        // 2. Akun Admin SDM
        $adminSdm = User::updateOrCreate(
            ['username' => 'adminsdm'],
            [
                'nama' => 'Staf Kepegawaian SDM',
                'email' => 'sdm@ansarisaleh.id',
                'no_wa' => '081234567891',
                'password' => Hash::make('password123'),
                'role' => 'admin_sdm',
                'aktif' => true,
            ]
        );

        // 3. Akun Verifikator Komite Medik
        $verifikator = User::updateOrCreate(
            ['username' => 'verifikatorkomite'],
            [
                'nama' => 'dr. Verifikator Komite Medik',
                'email' => 'komitemedik@ansarisaleh.id',
                'no_wa' => '081234567892',
                'password' => Hash::make('password123'),
                'role' => 'verifikator_komite',
                'aktif' => true,
            ]
        );

        // Dapatkan data KSM
        $ksmBedah = Ksm::where('kode_ksm', 'KSM-BED')->first();
        $ksmPenyakitDalam = Ksm::where('kode_ksm', 'KSM-INT')->first();
        $ksmAnak = Ksm::where('kode_ksm', 'KSM-ANA')->first();
        $ksmObgyn = Ksm::where('kode_ksm', 'KSM-OBG')->first();
        $ksmAnestesi = Ksm::where('kode_ksm', 'KSM-ANE')->first();

        // 4. Dokter 1 (SIP Aktif > 90 hari)
        $userDokter1 = User::updateOrCreate(
            ['username' => 'dokter1'],
            [
                'nama' => 'dr. Ahmad Spesialis, Sp.B',
                'email' => 'dokter1@ansarisaleh.id',
                'no_wa' => '081234567893',
                'password' => Hash::make('password123'),
                'role' => 'dokter',
                'aktif' => true,
            ]
        );
        $dokter1 = Dokter::updateOrCreate(
            ['id_user' => $userDokter1->id],
            [
                'nip_nrp' => '198205122008121002',
                'id_ksm' => $ksmBedah?->id,
                'spesialisasi' => 'Spesialis Bedah Umum',
                'subspesialis' => 'Subspesialis Bedah Digestif',
                'tempat_praktik_ke' => 1,
                'status_kepegawaian' => 'PNS',
                'status_str' => 'seumur_hidup',
                'nomor_str' => '3311801223123451',
                'tanggal_terbit_str' => Carbon::now()->subYears(2)->toDateString(),
                'file_str' => 'str/str_sample_ahmad.pdf',
                'status_verifikasi_str' => 'tervalidasi',
                'is_str_locked' => true,
                'verified_str_by' => $verifikator->id,
                'verified_str_at' => Carbon::now()->subMonths(6),
                'catatan_verifikasi_str' => 'Tervalidasi sesuai pangkalan data SatuSehat SDMK KKI.',
                'satusehat_verified_at' => Carbon::now()->subMonths(6),
            ]
        );
        DokumenSip::updateOrCreate(
            ['nomor_sip' => '446/012-SIP.DU/DPMPTSP/2024'],
            [
                'id_dokter' => $dokter1->id,
                'tempat_praktik_ke' => 1,
                'tanggal_terbit' => Carbon::now()->subMonths(6)->toDateString(),
                'tanggal_kadaluarsa' => Carbon::now()->addDays(240)->toDateString(),
                'status' => 'aktif',
                'status_verifikasi' => 'disetujui',
                'file_sip' => 'sip/sip_sample_1.pdf',
                'catatan_verifikasi' => 'Berkas SIP absah dan sesuai STR seumur hidup.',
                'verified_by' => $verifikator->id,
                'verified_at' => Carbon::now()->subMonths(6),
            ]
        );
        KredensialKlinis::updateOrCreate(
            ['nomor_spk' => '800/102/SPK-DIR/RSAS/2024'],
            [
                'id_dokter' => $dokter1->id,
                'tanggal_terbit_spk' => Carbon::now()->subMonths(6)->toDateString(),
                'tanggal_berakhir_spk' => Carbon::now()->addYears(2)->toDateString(),
                'status_kredensial' => 'aktif',
                'file_rkk' => 'rkk/rkk_bedah_ahmad.pdf',
            ]
        );

        // 5. Dokter 2 (SIP Due Soon: H-45)
        $userDokter2 = User::updateOrCreate(
            ['username' => 'dokter2'],
            [
                'nama' => 'dr. Siti Khadijah, Sp.PD',
                'email' => 'dokter2@ansarisaleh.id',
                'no_wa' => '081234567894',
                'password' => Hash::make('password123'),
                'role' => 'dokter',
                'aktif' => true,
            ]
        );
        $dokter2 = Dokter::updateOrCreate(
            ['id_user' => $userDokter2->id],
            [
                'nip_nrp' => '198503142010012008',
                'id_ksm' => $ksmPenyakitDalam?->id,
                'spesialisasi' => 'Spesialis Penyakit Dalam',
                'subspesialis' => 'Subspesialis Gastroenterohepatologi',
                'tempat_praktik_ke' => 1,
                'status_kepegawaian' => 'PNS',
                'status_str' => 'seumur_hidup',
                'nomor_str' => '3311801223123452',
                'tanggal_terbit_str' => Carbon::now()->subYears(3)->toDateString(),
                'file_str' => 'str/str_sample_siti.pdf',
                'status_verifikasi_str' => 'tervalidasi',
                'is_str_locked' => true,
                'verified_str_by' => $verifikator->id,
                'verified_str_at' => Carbon::now()->subMonths(3),
                'catatan_verifikasi_str' => 'Tervalidasi sesuai pangkalan data SatuSehat SDMK KKI.',
                'satusehat_verified_at' => Carbon::now()->subMonths(3),
            ]
        );
        DokumenSip::updateOrCreate(
            ['nomor_sip' => '446/045-SIP.SP/DPMPTSP/2021'],
            [
                'id_dokter' => $dokter2->id,
                'tempat_praktik_ke' => 1,
                'tanggal_terbit' => Carbon::now()->subYears(3)->toDateString(),
                'tanggal_kadaluarsa' => Carbon::now()->addDays(45)->toDateString(), // H-45
                'status' => 'due_soon',
                'status_verifikasi' => 'disetujui',
                'file_sip' => 'sip/sip_sample_2.pdf',
                'catatan_verifikasi' => 'Disetujui. Memasuki masa due soon perpanjangan.',
                'verified_by' => $verifikator->id,
                'verified_at' => Carbon::now()->subYears(3),
            ]
        );
        KredensialKlinis::updateOrCreate(
            ['nomor_spk' => '800/115/SPK-DIR/RSAS/2023'],
            [
                'id_dokter' => $dokter2->id,
                'tanggal_terbit_spk' => Carbon::now()->subYears(2)->toDateString(),
                'tanggal_berakhir_spk' => Carbon::now()->addMonths(6)->toDateString(),
                'status_kredensial' => 'aktif',
                'file_rkk' => 'rkk/rkk_internal_siti.pdf',
            ]
        );

        // 6. Dokter 3 (SIP Due / Kritis: H-15)
        $userDokter3 = User::updateOrCreate(
            ['username' => 'dokter3'],
            [
                'nama' => 'dr. Bambang Sudarmono, Sp.A',
                'email' => 'dokter3@ansarisaleh.id',
                'no_wa' => '081234567895',
                'password' => Hash::make('password123'),
                'role' => 'dokter',
                'aktif' => true,
            ]
        );
        $dokter3 = Dokter::updateOrCreate(
            ['id_user' => $userDokter3->id],
            [
                'nip_nrp' => '197908222005011004',
                'id_ksm' => $ksmAnak?->id,
                'spesialisasi' => 'Spesialis Anak',
                'subspesialis' => 'Subspesialis Perinatologi',
                'tempat_praktik_ke' => 1,
                'status_kepegawaian' => 'PNS',
                'status_str' => 'seumur_hidup',
                'nomor_str' => '3311801223123453',
                'tanggal_terbit_str' => Carbon::now()->subYears(3)->toDateString(),
                'file_str' => 'str/str_sample_bambang.pdf',
                'status_verifikasi_str' => 'tervalidasi',
                'is_str_locked' => true,
                'verified_str_by' => $verifikator->id,
                'verified_str_at' => Carbon::now()->subMonths(4),
                'catatan_verifikasi_str' => 'Tervalidasi sesuai pangkalan data SatuSehat SDMK KKI.',
                'satusehat_verified_at' => Carbon::now()->subMonths(4),
            ]
        );
        DokumenSip::updateOrCreate(
            ['nomor_sip' => '446/089-SIP.SP/DPMPTSP/2021'],
            [
                'id_dokter' => $dokter3->id,
                'tempat_praktik_ke' => 1,
                'tanggal_terbit' => Carbon::now()->subYears(3)->toDateString(),
                'tanggal_kadaluarsa' => Carbon::now()->addDays(15)->toDateString(), // H-15 (kritis)
                'status' => 'due',
                'status_verifikasi' => 'disetujui',
                'file_sip' => 'sip/sip_sample_3.pdf',
                'catatan_verifikasi' => 'Disetujui. Perlu reminder intensif karena sisa 15 hari.',
                'verified_by' => $verifikator->id,
                'verified_at' => Carbon::now()->subYears(3),
            ]
        );
        KredensialKlinis::updateOrCreate(
            ['nomor_spk' => '800/088/SPK-DIR/RSAS/2023'],
            [
                'id_dokter' => $dokter3->id,
                'tanggal_terbit_spk' => Carbon::now()->subYears(3)->toDateString(),
                'tanggal_berakhir_spk' => Carbon::now()->addDays(20)->toDateString(),
                'status_kredensial' => 'evaluasi',
                'file_rkk' => 'rkk/rkk_anak_bambang.pdf',
            ]
        );

        // 7. Dokter 4 (SIP Overdue / Kedaluwarsa: -10 hari)
        $userDokter4 = User::updateOrCreate(
            ['username' => 'dokter4'],
            [
                'nama' => 'dr. Ratna Juwita, Sp.OG',
                'email' => 'dokter4@ansarisaleh.id',
                'no_wa' => '081234567896',
                'password' => Hash::make('password123'),
                'role' => 'dokter',
                'aktif' => true,
            ]
        );
        $dokter4 = Dokter::updateOrCreate(
            ['id_user' => $userDokter4->id],
            [
                'nip_nrp' => '198711052014022003',
                'id_ksm' => $ksmObgyn?->id,
                'spesialisasi' => 'Spesialis Obstetri & Ginekologi',
                'subspesialis' => 'Subspesialis Fertilitas Endokrinologi',
                'tempat_praktik_ke' => 2,
                'status_kepegawaian' => 'BLUD',
                'status_str' => 'berkala',
                'nomor_str' => '3311801223123454',
                'tanggal_terbit_str' => Carbon::now()->subYears(4)->toDateString(),
                'tanggal_kadaluarsa_str' => Carbon::now()->addMonths(6)->toDateString(),
                'file_str' => 'str/str_sample_ratna.pdf',
                'status_verifikasi_str' => 'tervalidasi',
                'is_str_locked' => true,
                'verified_str_by' => $verifikator->id,
                'verified_str_at' => Carbon::now()->subMonths(2),
                'catatan_verifikasi_str' => 'Tervalidasi sesuai pangkalan data SatuSehat SDMK KKI.',
                'satusehat_verified_at' => Carbon::now()->subMonths(2),
            ]
        );
        DokumenSip::updateOrCreate(
            ['nomor_sip' => '446/102-SIP.SP/DPMPTSP/2021'],
            [
                'id_dokter' => $dokter4->id,
                'tempat_praktik_ke' => 2,
                'tanggal_terbit' => Carbon::now()->subYears(3)->toDateString(),
                'tanggal_kadaluarsa' => Carbon::now()->subDays(10)->toDateString(), // Overdue
                'status' => 'overdue',
                'status_verifikasi' => 'disetujui',
                'file_sip' => 'sip/sip_sample_4.pdf',
                'catatan_verifikasi' => 'Telah kedaluwarsa. Telah dikirimkan instruksi penundaan tindakan.',
                'verified_by' => $verifikator->id,
                'verified_at' => Carbon::now()->subYears(3),
            ]
        );
        KredensialKlinis::updateOrCreate(
            ['nomor_spk' => '800/041/SPK-DIR/RSAS/2021'],
            [
                'id_dokter' => $dokter4->id,
                'tanggal_terbit_spk' => Carbon::now()->subYears(3)->toDateString(),
                'tanggal_berakhir_spk' => Carbon::now()->subDays(5)->toDateString(),
                'status_kredensial' => 'kedaluwarsa',
                'file_rkk' => 'rkk/rkk_obgyn_ratna.pdf',
            ]
        );

        // 8. Dokter 5 (STR Menunggu Verifikasi & Belum Dikunci - Untuk Testing Verifikasi SatuSehat & Hard Constraint)
        $userDokter5 = User::updateOrCreate(
            ['username' => 'dokter5'],
            [
                'nama' => 'dr. Hendra Wijaya, Sp.An',
                'email' => 'dokter5@ansarisaleh.id',
                'no_wa' => '081234567897',
                'password' => Hash::make('password123'),
                'role' => 'dokter',
                'aktif' => true,
            ]
        );
        $dokter5 = Dokter::updateOrCreate(
            ['id_user' => $userDokter5->id],
            [
                'nip_nrp' => '199002182019031005',
                'id_ksm' => $ksmAnestesi?->id,
                'spesialisasi' => 'Spesialis Anestesiologi',
                'subspesialis' => 'Subspesialis Intensive Care (KIC)',
                'tempat_praktik_ke' => 1,
                'status_kepegawaian' => 'PPPK',
                'status_str' => 'seumur_hidup',
                'nomor_str' => '3311801223123455',
                'tanggal_terbit_str' => Carbon::now()->subMonths(1)->toDateString(),
                'file_str' => 'str/str_sample_hendra.pdf',
                'status_verifikasi_str' => 'menunggu_verifikasi',
                'is_str_locked' => false,
            ]
        );
        DokumenSip::updateOrCreate(
            ['nomor_sip' => '446/215-SIP.SP/DPMPTSP/2026'],
            [
                'id_dokter' => $dokter5->id,
                'tempat_praktik_ke' => 1,
                'tanggal_terbit' => Carbon::now()->subDays(2)->toDateString(),
                'tanggal_kadaluarsa' => Carbon::now()->addYears(3)->toDateString(),
                'status' => 'aktif',
                'status_verifikasi' => 'menunggu',
                'file_sip' => 'sip_sample_5.pdf',
                'catatan_verifikasi' => null,
                'verified_by' => null,
                'verified_at' => null,
            ]
        );
        KredensialKlinis::updateOrCreate(
            ['nomor_spk' => '800/220/SPK-DIR/RSAS/2025'],
            [
                'id_dokter' => $dokter5->id,
                'tanggal_terbit_spk' => Carbon::now()->subYear()->toDateString(),
                'tanggal_berakhir_spk' => Carbon::now()->addYears(2)->toDateString(),
                'status_kredensial' => 'aktif',
                'file_rkk' => 'rkk_anestesi_hendra.pdf',
            ]
        );

        // Seed Pengaturan Sistem Awal
        $pengaturan = [
            'wa_api_url' => ['nilai' => 'https://api.fonnte.com/send', 'deskripsi' => 'Endpoint API WhatsApp Gateway'],
            'wa_api_token' => ['nilai' => 'TOKEN_FONNTE_DEMO_ANSARISALEH', 'deskripsi' => 'Authorization Token WA Gateway'],
            'nama_fasyankes' => ['nilai' => 'RSUD dr. H. Moch. Ansari Saleh Banjarmasin', 'deskripsi' => 'Nama Resmi Rumah Sakit'],
            'telepon_fasyankes' => ['nilai' => '(0511) 3300757 / 3300758', 'deskripsi' => 'Nomor Kontak Sekretariat Komite Medik / SDM'],
            'alamat_fasyankes' => ['nilai' => 'Jl. Brigjend H. Hasan Basry No. 1, Banjarmasin Utara, Kota Banjarmasin, Kalimantan Selatan 70125', 'deskripsi' => 'Alamat Resmi Fasilitas Pelayanan Kesehatan'],
            'email_komite' => ['nilai' => 'komite.medik@ansarisaleh.id', 'deskripsi' => 'Email Resmi Komite Medik'],
        ];

        foreach ($pengaturan as $kunci => $data) {
            PengaturanSistem::updateOrCreate(['kunci' => $kunci], $data);
        }
    }
}
