<?php

namespace App\Services;

use App\Models\DokumenSip;
use App\Models\Notifikasi;
use App\Models\PengaturanSistem;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppGatewayService
{
    /**
     * Kirim pesan WhatsApp pengingat jatuh tempo SIP
     */
    public function kirimPeringatan(DokumenSip $sip, ?string $customPesan = null): array
    {
        $dokter = $sip->dokter;
        if (! $dokter || ! $dokter->user) {
            return ['status' => false, 'message' => 'Dokter atau akun user tidak ditemukan.'];
        }

        $user = $dokter->user;
        $noWa = $user->no_wa;
        $selisih = $sip->hitungSisaHari();
        $tglKadaluarsa = $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->translatedFormat('d F Y') : '-';

        if ($customPesan) {
            $pesan = $customPesan;
            $tingkatEskalasi = $sip->status ?? 'kustom';
        } else {
            if ($sip->isGracePeriodAktif()) {
                $sisaDispensasi = $sip->hitungSisaHariDispensasi();
                $tglAkhirDispensasi = $sip->tanggal_akhir_dispensasi ? $sip->tanggal_akhir_dispensasi->translatedFormat('d F Y') : '-';
                $statusTeks = "DALAM PROSES / GRACE PERIOD (Sisa {$sisaDispensasi} hari lagi)";
                $himbauan = "🛡️ *PROTEKSI PELAYANAN MEDIS AKTIF (GRACE PERIOD)*\nResi tanda terima pengurusan SIP Anda (No. Resi: {$sip->nomor_resi_proses}) telah diverifikasi oleh Admin SDM.\nDispensasi berlaku s.d. *{$tglAkhirDispensasi}* ({$sip->durasi_dispensasi_hari} hari).\n\n*Wewenang klinis (SPK/RKK) Anda tetap sah dan aktif*. Mohon pantau terbitnya SIP definitif di DPMPTSP sebelum masa tenggang berakhir.";
                $tingkatEskalasi = 'grace_period';
            } elseif ($selisih < 0) {
                $statusTeks = 'TELAH KEDALUWARSA ('.abs($selisih).' hari yang lalu)';
                $himbauan = "⚠️ *PERINGATAN KEDALUWARSA*\nSesuai regulasi Pokja KPS dan Komite Medik, Anda dihimbau untuk *segera menghentikan pelayanan klinis mandiri* sampai penerbitan SIP baru disahkan atau mengajukan masa tenggang administratif dengan bukti resi pengurusan DPMPTSP.";
                $tingkatEskalasi = 'overdue';
            } elseif ($selisih <= 7) {
                $statusTeks = "🚨 KRITIS (H-7 / Sisa {$selisih} hari lagi)";
                $himbauan = "🚨 *PERINGATAN KRITIS TAHAP AKHIR (H-7)*\nMasa berlaku SIP Anda tersisa *{$selisih} hari lagi* ({$tglKadaluarsa}). Sesuai regulasi Pokja KPS & Komite Medik, praktik kedokteran tanpa SIP aktif berisiko terhadap legalitas pelayanan.\n\n*Notifikasi ini telah dieskalasikan berjenjang langsung ke Staf Kepegawaian / SDM* untuk pendampingan mendesak. Jika izin sedang diproses di DPMPTSP/Dinkes, silakan segera mengunggah resi bukti proses untuk permohonan masa tenggang administratif.";
                $tingkatEskalasi = 'critical';
            } elseif ($selisih <= 30) {
                $statusTeks = "⚠️ MENDESAK (H-30 / Sisa {$selisih} hari lagi)";
                $himbauan = "⚠️ *PERINGATAN MENDESAK (H-30)*\nSisa masa berlaku SIP Anda tersisa *{$selisih} hari lagi*. Mohon segera menindaklanjuti proses penerbitan perpanjangan di DPMPTSP dan mengunggah berkas SIP baru atau Resi tanda terima ke sistem SIM-SIP.\n\n*Salinan rekapitulasi pengingat ini telah diteruskan ke Admin SDM.*";
                $tingkatEskalasi = 'due';
            } elseif ($selisih <= 90) {
                $statusTeks = "PENGINGAT PERTAMA (H-90 s.d. H-60 / Sisa {$selisih} hari lagi)";
                $himbauan = "📌 *PENGINGAT PERTAMA MASA BERLAKU SIP (H-90 s.d. H-60)*\nMohon segera mempersiapkan:\n1. *Kecukupan SKP SatuSehat* (Kemenkes RI) untuk rekomendasi profesi.\n2. Berkas persyaratan perpanjangan SIP ke DPMPTSP agar kesinambungan pelayanan klinis tetap terjamin.";
                $tingkatEskalasi = 'due_soon';
            } else {
                $statusTeks = "Aman (Sisa {$selisih} hari lagi)";
                $himbauan = 'Masa berlaku SIP Anda terpantau aman.';
                $tingkatEskalasi = 'aktif';
            }

            $pesan = "🔔 *EARLY WARNING ENGINE - SIM-SIP DOKTER*\n";
            $pesan .= "*RSUD dr. H. Moch. Ansari Saleh*\n";
            $pesan .= "----------------------------------------\n\n";
            $pesan .= "Yth. *{$user->nama}*\n";
            $pesan .= "KSM: *{$dokter->ksm?->nama_ksm}*\n\n";
            $pesan .= "Kami menginformasikan status Surat Izin Praktik (SIP) Anda:\n";
            $pesan .= "• No. SIP: *{$sip->nomor_sip}* (Praktik ke-{$sip->tempat_praktik_ke})\n";
            $pesan .= "• Jatuh Tempo: *{$tglKadaluarsa}*\n";
            $pesan .= "• Status: *{$statusTeks}*\n\n";
            $pesan .= "{$himbauan}\n\n";
            $pesan .= "Akses Portal SIM-SIP:\n".config('app.url')."\n\n";
            $pesan .= '_Pesan otomatis Early Warning Engine & Bagian SDM RSUD dr. H. Moch. Ansari Saleh_';
        }

        $dispatch = $this->dispatchWaMessage($noWa ?? '', $pesan);

        // Catat ke log notifikasi
        $notifikasi = Notifikasi::create([
            'id_user' => $user->id,
            'id_sip' => $sip->id,
            'tingkat_eskalasi' => $tingkatEskalasi,
            'tipe_penerima' => 'dokter',
            'pesan' => $pesan,
            'kanal' => 'whatsapp',
            'status_terkirim' => $dispatch['status'],
            'status_baca' => false,
            'metadata' => [
                'sisa_hari' => $selisih,
                'status_sip' => $sip->status,
                'tempat_praktik_ke' => $sip->tempat_praktik_ke,
            ],
        ]);

        $sip->updateQuietly([
            'terakhir_notifikasi_dokter_at' => now(),
            'log_eskalasi_terakhir' => "Peringatan dokter ({$tingkatEskalasi}) dikirim pada ".now()->translatedFormat('d/m/Y H:i'),
        ]);

        return [
            'status' => $dispatch['status'],
            'notifikasi' => $notifikasi,
            'info' => $dispatch['info'],
            'pesan' => $pesan,
        ];
    }

    /**
     * Kirim Salinan Rekapitulasi Jatuh Tempo H-30 ke Admin SDM
     */
    public function kirimSalinanRekapitulasiSdm(DokumenSip $sip, User $adminSdm): array
    {
        $noWa = $adminSdm->no_wa;
        $dokter = $sip->dokter;
        $userDokter = $dokter?->user;
        $selisih = $sip->hitungSisaHari();
        $tglKadaluarsa = $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->translatedFormat('d F Y') : '-';
        $statusSkp = $sip->status_kecukupan_skp_label;

        $pesan = "📋 *SALINAN REKAPITULASI JATUH TEMPO (H-30) - SDM KEPEGAWAIAN*\n";
        $pesan .= "*RSUD dr. H. Moch. Ansari Saleh*\n";
        $pesan .= "----------------------------------------\n\n";
        $pesan .= "Yth. *{$adminSdm->nama}* (Admin SDM),\n\n";
        $pesan .= "Sistem Early Warning Engine menginformasikan dokter yang memasuki ambang *H-30 (Peringatan Mendesak)*:\n\n";
        $pesan .= "• Nama Dokter : *{$userDokter?->nama}*\n";
        $pesan .= "• KSM : *{$dokter?->ksm?->nama_ksm}*\n";
        $pesan .= "• No. SIP : *{$sip->nomor_sip}* (Praktik ke-{$sip->tempat_praktik_ke})\n";
        $pesan .= "• Tanggal Kedaluwarsa : *{$tglKadaluarsa}*\n";
        $pesan .= "• Sisa Waktu : *{$selisih} Hari lagi*\n";
        $pesan .= "• Status SKP SatuSehat : *{$statusSkp}*\n";
        $pesan .= '• Kontak WA Dokter : *'.($userDokter?->no_wa ?? '-')."*\n\n";
        $pesan .= "📌 *Tindakan SDM*:\n";
        $pesan .= "Pesan peringatan mendesak telah otomatis dikirimkan ke nomor dokter. Mohon Admin SDM memonitor kelengkapan berkas perpanjangan di DPMPTSP.\n\n";
        $pesan .= "Akses Matriks & Monitoring:\n".config('app.url')."/sdm/reminder\n\n";
        $pesan .= '_Early Warning Engine SIM-SIP RSUD dr. H. Moch. Ansari Saleh_';

        $dispatch = $this->dispatchWaMessage($noWa ?? '', $pesan);

        $notifikasi = Notifikasi::create([
            'id_user' => $adminSdm->id,
            'id_sip' => $sip->id,
            'tingkat_eskalasi' => 'due',
            'tipe_penerima' => 'admin_sdm',
            'pesan' => $pesan,
            'kanal' => 'whatsapp',
            'status_terkirim' => $dispatch['status'],
            'status_baca' => false,
            'metadata' => [
                'jenis' => 'salinan_rekapitulasi_h30',
                'id_dokter' => $sip->id_dokter,
                'nama_dokter' => $userDokter?->nama,
                'sisa_hari' => $selisih,
            ],
        ]);

        $sip->updateQuietly([
            'terakhir_eskalasi_sdm_at' => now(),
            'log_eskalasi_terakhir' => "Salinan H-30 diteruskan ke Admin SDM ({$adminSdm->nama}) pada ".now()->translatedFormat('d/m/Y H:i'),
        ]);

        return [
            'status' => $dispatch['status'],
            'notifikasi' => $notifikasi,
            'info' => $dispatch['info'],
            'pesan' => $pesan,
        ];
    }

    /**
     * Kirim Notifikasi Eskalasi Kritis Berjenjang H-7 ke Staf Kepegawaian / SDM
     */
    public function kirimEskalasiKritisSdm(DokumenSip $sip, User $stafSdm): array
    {
        $noWa = $stafSdm->no_wa;
        $dokter = $sip->dokter;
        $userDokter = $dokter?->user;
        $selisih = $sip->hitungSisaHari();
        $tglKadaluarsa = $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->translatedFormat('d F Y') : '-';
        $statusSkp = $sip->status_kecukupan_skp_label;

        $pesan = "🚨 *ESKALASI BERJENJANG TINGKAT KRITIS (H-7) - KEPEGAWAIAN SDM*\n";
        $pesan .= "*RSUD dr. H. Moch. Ansari Saleh*\n";
        $pesan .= "----------------------------------------\n\n";
        $pesan .= "PERHATIAN: *STAF KEPEGAWAIAN / SDM*\n";
        $pesan .= "Penerima: *{$stafSdm->nama}*\n\n";
        $pesan .= "Sistem Early Warning Engine melakukan *Eskalasi Berjenjang Kritis* atas dokumen SIP dokter yang berada pada ambang batas *KRITIS (H-7)*:\n\n";
        $pesan .= "• Nama Dokter : *{$userDokter?->nama}*\n";
        $pesan .= "• KSM : *{$dokter?->ksm?->nama_ksm}*\n";
        $pesan .= "• No. SIP : *{$sip->nomor_sip}* (Praktik ke-{$sip->tempat_praktik_ke})\n";
        $pesan .= "• Jatuh Tempo : *{$tglKadaluarsa}*\n";
        $pesan .= "• Sisa Masa Berlaku : *{$selisih} Hari lagi*\n";
        $pesan .= "• Status SKP SatuSehat : *{$statusSkp}*\n";
        $pesan .= '• Kontak WA Dokter : *'.($userDokter?->no_wa ?? '-')."*\n\n";
        $pesan .= "⚡ *TINDAKAN MENDESAK KEPEGAWAIAN SDM*:\n";
        $pesan .= "1. Segera lakukan kontak langsung / konfirmasi fisik status pengurusan perpanjangan SIP dokter.\n";
        $pesan .= "2. Pastikan kecukupan SKP SatuSehat dan surat rekomendasi IDI/Kemenkes telah terbit.\n";
        $pesan .= "3. Laporkan kepada manajemen jika berpotensi menyebabkan kekosongan legalitas pelayanan klinis mandiri.\n\n";
        $pesan .= "Akses Matriks & Monitoring:\n".config('app.url')."/sdm/reminder\n\n";
        $pesan .= '_Early Warning Engine SIM-SIP RSUD dr. H. Moch. Ansari Saleh_';

        $dispatch = $this->dispatchWaMessage($noWa ?? '', $pesan);

        $notifikasi = Notifikasi::create([
            'id_user' => $stafSdm->id,
            'id_sip' => $sip->id,
            'tingkat_eskalasi' => 'critical',
            'tipe_penerima' => 'staf_sdm',
            'pesan' => $pesan,
            'kanal' => 'whatsapp',
            'status_terkirim' => $dispatch['status'],
            'status_baca' => false,
            'metadata' => [
                'jenis' => 'eskalasi_kritis_h7',
                'id_dokter' => $sip->id_dokter,
                'nama_dokter' => $userDokter?->nama,
                'sisa_hari' => $selisih,
            ],
        ]);

        $sip->updateQuietly([
            'terakhir_eskalasi_sdm_at' => now(),
            'log_eskalasi_terakhir' => "Eskalasi Kritis H-7 diteruskan ke Staf SDM ({$stafSdm->nama}) pada ".now()->translatedFormat('d/m/Y H:i'),
        ]);

        return [
            'status' => $dispatch['status'],
            'notifikasi' => $notifikasi,
            'info' => $dispatch['info'],
            'pesan' => $pesan,
        ];
    }

    /**
     * Helper internal pengiriman HTTP ke vendor WA Gateway atau simulasi lokal
     */
    protected function dispatchWaMessage(string $noWa, string $pesan): array
    {
        $apiUrl = PengaturanSistem::get('wa_api_url', config('services.wa_gateway.endpoint', 'https://api.fonnte.com/send'));
        $apiToken = PengaturanSistem::get('wa_api_token', config('services.wa_gateway.token', ''));

        $sukses = false;
        $responseInfo = 'Mock / Offline';

        $isMockToken = empty($apiToken) || str_contains($apiToken, 'DEMO') || str_starts_with($apiToken, 'TOKEN_') || app()->environment('testing');

        if (! empty($apiUrl) && ! empty($noWa) && ! $isMockToken) {
            try {
                $response = Http::timeout(5)->withHeaders([
                    'Authorization' => $apiToken,
                ])->post($apiUrl, [
                    'target' => $noWa,
                    'message' => $pesan,
                ]);

                $sukses = $response->successful();
                $responseInfo = $response->body();
            } catch (\Exception $e) {
                Log::error("Gagal kirim WA ke {$noWa}: ".$e->getMessage());
                $responseInfo = $e->getMessage();
            }
        } else {
            // Simulasi pengiriman berhasil jika di lingkungan lokal tanpa credentials vendor / testing / demo
            $sukses = true;
            $responseInfo = 'Simulated Delivery (Local/Demo environment)';
        }

        return [
            'status' => $sukses,
            'info' => $responseInfo,
        ];
    }

    /**
     * Kirim pesan teks WhatsApp umum (misal: notifikasi pengesahan SK Direktur)
     */
    public function kirimPesanTeks(string $noWa, string $pesan, ?int $idUser = null): array
    {
        $dispatch = $this->dispatchWaMessage($noWa, $pesan);

        if ($idUser) {
            Notifikasi::create([
                'id_user' => $idUser,
                'pesan' => $pesan,
                'kanal' => 'whatsapp',
                'status_terkirim' => $dispatch['status'],
                'status_baca' => false,
            ]);
        }

        return [
            'status' => $dispatch['status'],
            'info' => $dispatch['info'],
            'pesan' => $pesan,
        ];
    }

    /**
     * Kirim notifikasi WhatsApp bahwa dispensasi masa tenggang telah disetujui Admin SDM
     */
    public function kirimPemberitahuanDispensasi(DokumenSip $sip, int $durasiHari, string $tanggalAkhir, ?string $catatan = null): array
    {
        $dokter = $sip->dokter;
        $user = $dokter?->user;
        $noWa = $user?->no_wa;

        $pesan = "🛡️ *DISPENSASI MASA TENGGANG (GRACE PERIOD) DISETUJUI*\n";
        $pesan .= "*RSUD dr. H. Moch. Ansari Saleh*\n";
        $pesan .= "----------------------------------------\n\n";
        $pesan .= "Yth. *{$user?->nama}*\n";
        $pesan .= "KSM: *{$dokter?->ksm?->nama_ksm}*\n\n";
        $pesan .= "Permohonan dispensasi masa tenggang administratif perizinan Anda telah *DIVERIFIKASI & DISETUJUI* oleh Admin SDM:\n";
        $pesan .= "• No. SIP: *{$sip->nomor_sip}*\n";
        $pesan .= "• No. Resi DPMPTSP/Dinkes: *{$sip->nomor_resi_proses}*\n";
        $pesan .= "• Durasi Dispensasi: *{$durasiHari} Hari*\n";
        $pesan .= "• Batas Akhir Grace Period: *{$tanggalAkhir}*\n";
        $pesan .= "• Status SIP: *DALAM PROSES*\n\n";
        if ($catatan) {
            $pesan .= "Catatan SDM: {$catatan}\n\n";
        }
        $pesan .= "✅ *PROTEKSI PELAYANAN*: Status kewenangan klinis (SPK/RKK) Anda tetap *AKTIF* dan tidak ditangguhkan selama masa dispensasi berlangsung.\n\n";
        $pesan .= "Mohon segera mengunggah berkas SIP definitif setelah terbit sebelum batas waktu di atas.\n\n";
        $pesan .= '_Pesan resmi Bagian Kepegawaian SDM RSUD dr. H. Moch. Ansari Saleh_';

        $dispatch = $this->dispatchWaMessage($noWa ?? '', $pesan);

        if ($user) {
            Notifikasi::create([
                'id_user' => $user->id,
                'id_sip' => $sip->id,
                'tingkat_eskalasi' => 'grace_period',
                'tipe_penerima' => 'dokter',
                'pesan' => $pesan,
                'kanal' => 'whatsapp',
                'status_terkirim' => $dispatch['status'],
                'status_baca' => false,
                'metadata' => [
                    'durasi_hari' => $durasiHari,
                    'tanggal_akhir' => $tanggalAkhir,
                    'status_dispensasi' => 'disetujui',
                ],
            ]);
        }

        return [
            'status' => $dispatch['status'],
            'info' => $dispatch['info'],
            'pesan' => $pesan,
        ];
    }

    /**
     * Pengingat H-3 sebelum masa tenggang administratif berakhir
     */
    public function kirimPengingatDispensasiH3(DokumenSip $sip, int $sisaHari): array
    {
        $dokter = $sip->dokter;
        $user = $dokter?->user;
        $noWa = $user?->no_wa;
        $tglAkhir = $sip->tanggal_akhir_dispensasi ? $sip->tanggal_akhir_dispensasi->translatedFormat('d F Y') : '-';

        $pesan = "⚠️ *PENGINGAT MASA TENGGANG SEGERA BERAKHIR*\n";
        $pesan .= "*RSUD dr. H. Moch. Ansari Saleh*\n";
        $pesan .= "----------------------------------------\n\n";
        $pesan .= "Yth. *{$user?->nama}*\n\n";
        $pesan .= "Masa dispensasi administratif (Grace Period) untuk SIP No. *{$sip->nomor_sip}* tersisa *{$sisaHari} hari lagi* (jatuh tempo pada *{$tglAkhir}*).\n\n";
        $pesan .= "Jika izin definitif dari DPMPTSP telah terbit, segera unggah salinan berkas ke sistem SIM-SIP untuk memperbarui legalitas hak praktik penuh Anda.\n\n";
        $pesan .= "Sesuai tata kelola, bila masa tenggang berakhir tanpa SIP definitif, kewenangan klinis akan ditinjau kembali.\n\n";
        $pesan .= '_Bagian Kepegawaian SDM RSUD dr. H. Moch. Ansari Saleh_';

        $dispatch = $this->dispatchWaMessage($noWa ?? '', $pesan);

        if ($user) {
            Notifikasi::create([
                'id_user' => $user->id,
                'id_sip' => $sip->id,
                'tingkat_eskalasi' => 'grace_period_warning',
                'tipe_penerima' => 'dokter',
                'pesan' => $pesan,
                'kanal' => 'whatsapp',
                'status_terkirim' => $dispatch['status'],
                'status_baca' => false,
            ]);
        }

        return [
            'status' => $dispatch['status'],
            'info' => $dispatch['info'],
            'pesan' => $pesan,
        ];
    }

    /**
     * Notifikasi penolakan resi bukti proses dispensasi
     */
    public function kirimPenolakanDispensasi(DokumenSip $sip, string $alasan): array
    {
        $dokter = $sip->dokter;
        $user = $dokter?->user;
        $noWa = $user?->no_wa;

        $pesan = "❌ *PEMBERITAHUAN VERIFIKASI DISPENSASI SIP*\n";
        $pesan .= "*RSUD dr. H. Moch. Ansari Saleh*\n";
        $pesan .= "----------------------------------------\n\n";
        $pesan .= "Yth. *{$user?->nama}*\n\n";
        $pesan .= "Pengajuan dispensasi masa tenggang administratif untuk SIP No. *{$sip->nomor_sip}* belum dapat disetujui oleh Bagian SDM.\n\n";
        $pesan .= "Alasan Penolakan: *{$alasan}*\n\n";
        $pesan .= "Silakan koordinasikan dengan Bagian SDM atau perbaiki bukti tanda terima proses perizinan Anda.\n\n";
        $pesan .= '_Bagian Kepegawaian SDM RSUD dr. H. Moch. Ansari Saleh_';

        $dispatch = $this->dispatchWaMessage($noWa ?? '', $pesan);

        if ($user) {
            Notifikasi::create([
                'id_user' => $user->id,
                'id_sip' => $sip->id,
                'tingkat_eskalasi' => 'dispensasi_ditolak',
                'tipe_penerima' => 'dokter',
                'pesan' => $pesan,
                'kanal' => 'whatsapp',
                'status_terkirim' => $dispatch['status'],
                'status_baca' => false,
            ]);
        }

        return [
            'status' => $dispatch['status'],
            'info' => $dispatch['info'],
            'pesan' => $pesan,
        ];
    }

    /**
     * Tahap 7: Eskalasi Insiden Darurat Kedaluwarsa & Auto-Suspend
     * Dikirim langsung ke Direktur, Ketua Komite Medik, Bagian SDM, dan Dokter bersangkutan
     */
    public function kirimPeringatanDaruratOverdue(DokumenSip $sip, User $penerima, string $rolePenerima = 'direktur'): array
    {
        $dokter = $sip->dokter;
        $userDokter = $dokter?->user;
        $noWa = $penerima->no_wa;
        $tglKadaluarsa = $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->translatedFormat('d F Y') : '-';
        $selisih = abs($sip->hitungSisaHari());

        $headerPenerima = match ($rolePenerima) {
            'direktur' => 'Yth. Direktur RSUD dr. H. Moch. Ansari Saleh',
            'komite_medik' => 'Yth. Ketua Komite Medik',
            'admin_sdm' => 'Yth. Kepala Bagian Kepegawaian & SDM',
            default => "Yth. {$penerima->nama}",
        };

        $pesan = "🚨 *ESKALASI INSIDEN DARURAT: AUTO-SUSPEND SIP OVERDUE*\n";
        $pesan .= "*RSUD dr. H. Moch. Ansari Saleh*\n";
        $pesan .= "----------------------------------------\n\n";
        $pesan .= "{$headerPenerima},\n\n";
        $pesan .= "Sistem mendeteksi bahwa masa berlaku SIP staf medis berikut telah *KEDALUWARSA (OVERDUE)* dan tidak memiliki status dispensasi aktif:\n\n";
        $pesan .= "• *Nama Dokter*: {$userDokter?->nama}\n";
        $pesan .= '• *NIP / NRP*: '.($dokter?->nip_nrp ?? '-')."\n";
        $pesan .= '• *Spesialisasi*: '.($dokter?->spesialisasi ?? '-')."\n";
        $pesan .= "• *Nomor SIP*: {$sip->nomor_sip}\n";
        $pesan .= "• *Tanggal Jatuh Tempo*: {$tglKadaluarsa} ({$selisih} hari yang lalu)\n\n";
        $pesan .= "⛔ *TINDAKAN MITIGASI OTOMATIS (TAHAP 7)*:\n";
        $pesan .= "1. Status SIP resmi *DIKUNCI OVERDUE* (Merah).\n";
        $pesan .= "2. Status instrumen SPK & RKK otomatis beralih menjadi *DITANGGUHKAN (SUSPENDED)*.\n";
        $pesan .= "3. Dokter *TERBLOKIR SECARA ADMINISTRATIF* dari wewenang tindakan klinis mandiri.\n";
        $pesan .= "4. Nama dokter otomatis dimasukkan ke *Daftar Staf Medis Non-Aktif Pelayanan* pada Laporan Monitoring Akreditasi Pokja KPS guna mencegah temuan audit eksternal serta melindungi fasyankes dari tuntutan malpraktik.\n\n";
        $pesan .= "💡 *PEMULIHAN HAK PRAKTIK*:\n";
        $pesan .= "Penangguhan wewenang klinis hanya dapat dicabut (re-activated) setelah dokter mengunggah fisik SIP definitif yang disahkan kembali melalui alur Tahap 2, 3, dan 4.\n\n";
        $pesan .= '_Sistem Informasi Manajemen SIP & Kredensial Medis RSUD Ansari Saleh_';

        $dispatch = $this->dispatchWaMessage($noWa ?? '', $pesan);

        Notifikasi::create([
            'id_user' => $penerima->id,
            'id_sip' => $sip->id,
            'tingkat_eskalasi' => 'darurat_overdue',
            'tipe_penerima' => $rolePenerima,
            'pesan' => $pesan,
            'kanal' => 'whatsapp',
            'status_terkirim' => $dispatch['status'],
            'status_baca' => false,
        ]);

        return [
            'status' => $dispatch['status'],
            'info' => $dispatch['info'],
            'pesan' => $pesan,
        ];
    }
}
