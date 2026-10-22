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
            if ($selisih < 0) {
                $statusTeks = 'TELAH KEDALUWARSA ('.abs($selisih).' hari yang lalu)';
                $himbauan = "⚠️ *PERINGATAN KEDALUWARSA*\nSesuai regulasi Pokja KPS dan Komite Medik, Anda dihimbau untuk *segera menghentikan pelayanan klinis mandiri* sampai penerbitan SIP baru disahkan.";
                $tingkatEskalasi = 'overdue';
            } elseif ($selisih <= 7) {
                $statusTeks = "🚨 KRITIS (H-7 / Sisa {$selisih} hari lagi)";
                $himbauan = "🚨 *PERINGATAN KRITIS TAHAP AKHIR (H-7)*\nMasa berlaku SIP Anda tersisa *{$selisih} hari lagi* ({$tglKadaluarsa}). Sesuai regulasi Pokja KPS & Komite Medik, praktik kedokteran tanpa SIP aktif berisiko terhadap legalitas pelayanan.\n\n*Notifikasi ini telah dieskalasikan berjenjang langsung ke Staf Kepegawaian / SDM* untuk pendampingan mendesak.";
                $tingkatEskalasi = 'critical';
            } elseif ($selisih <= 30) {
                $statusTeks = "⚠️ MENDESAK (H-30 / Sisa {$selisih} hari lagi)";
                $himbauan = "⚠️ *PERINGATAN MENDESAK (H-30)*\nSisa masa berlaku SIP Anda tersisa *{$selisih} hari lagi*. Mohon segera menindaklanjuti proses penerbitan perpanjangan di DPMPTSP dan mengunggah berkas SIP baru ke sistem SIM-SIP.\n\n*Salinan rekapitulasi pengingat ini telah diteruskan ke Admin SDM.*";
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
}
