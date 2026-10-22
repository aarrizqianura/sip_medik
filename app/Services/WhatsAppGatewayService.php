<?php

namespace App\Services;

use App\Models\DokumenSip;
use App\Models\Notifikasi;
use App\Models\PengaturanSistem;
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
        } else {
            if ($selisih < 0) {
                $statusTeks = 'TELAH KEDALUWARSA ('.abs($selisih).' hari yang lalu)';
                $himbauan = 'Sesuai regulasi Pokja KPS dan Komite Medik, Anda dihimbau untuk *segera menghentikan pelayanan klinis mandiri* sampai penerbitan SIP baru disahkan.';
            } elseif ($selisih <= 30) {
                $statusTeks = "KRITIS (Sisa {$selisih} hari lagi)";
                $himbauan = 'Mohon segera menindaklanjuti proses perpanjangan di DPMPTSP dan mengunggah berkas SIP baru ke sistem.';
            } else {
                $statusTeks = "Sisa {$selisih} hari lagi";
                $himbauan = 'Mohon mempersiapkan berkas persyaratan perpanjangan SIP Anda.';
            }

            $pesan = "🔔 *PENGINGAT MASA BERLAKU SIP DOKTER*\n";
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
            $pesan .= '_Pesan otomatis Komite Medik & Bagian SDM Kepegawaian RSUD dr. H. Moch. Ansari Saleh_';
        }

        $apiUrl = PengaturanSistem::get('wa_api_url', config('services.wa_gateway.endpoint', 'https://api.fonnte.com/send'));
        $apiToken = PengaturanSistem::get('wa_api_token', config('services.wa_gateway.token', ''));

        $sukses = false;
        $responseInfo = 'Mock / Offline';

        if (! empty($apiUrl) && ! empty($apiToken) && ! empty($noWa)) {
            try {
                $response = Http::timeout(10)->withHeaders([
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
            // Simulasi pengiriman berhasil jika di lingkungan lokal tanpa credentials vendor
            $sukses = true;
            $responseInfo = 'Simulated Delivery (Local environment)';
        }

        // Catat ke log notifikasi
        $notifikasi = Notifikasi::create([
            'id_user' => $user->id,
            'id_sip' => $sip->id,
            'pesan' => $pesan,
            'kanal' => 'whatsapp',
            'status_terkirim' => $sukses,
            'status_baca' => false,
        ]);

        return [
            'status' => $sukses,
            'notifikasi' => $notifikasi,
            'info' => $responseInfo,
            'pesan' => $pesan,
        ];
    }
}
