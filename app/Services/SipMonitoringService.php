<?php

namespace App\Services;

use App\Models\Dokter;
use App\Models\DokumenSip;
use App\Models\KredensialKlinis;
use Illuminate\Support\Facades\Log;

class SipMonitoringService
{
    protected WhatsAppGatewayService $waService;

    public function __construct(WhatsAppGatewayService $waService)
    {
        $this->waService = $waService;
    }

    /**
     * Hitung dan perbarui status seluruh dokumen SIP dan Kredensial
     */
    public function syncSemuaStatus(): array
    {
        $semuaSip = DokumenSip::with(['dokter.user', 'dokter.ksm'])->get();
        $updatedSip = 0;

        foreach ($semuaSip as $sip) {
            $sip->updateStatusKadaluarsa();
            $updatedSip++;
        }

        $semuaKredensial = KredensialKlinis::all();
        $updatedKredensial = 0;
        foreach ($semuaKredensial as $kredensial) {
            $kredensial->updateStatusKredensial();
            $updatedKredensial++;
        }

        return [
            'sip_diperbarui' => $updatedSip,
            'kredensial_diperbarui' => $updatedKredensial,
        ];
    }

    /**
     * Jalankan pemeriksaan terjadwal (Cron Job Harian)
     * Mengirimkan WhatsApp ke dokter jika memasuki H-90, H-60, H-30, atau overdue
     */
    public function runDailyCheck(): array
    {
        $this->syncSemuaStatus();

        $dokumenList = DokumenSip::with(['dokter.user', 'dokter.ksm'])
            ->where('status_verifikasi', 'disetujui')
            ->get();

        $notifDikirim = 0;
        $detailPengiriman = [];

        foreach ($dokumenList as $sip) {
            $selisih = $sip->hitungSisaHari();

            // Pemicu pada H-90, H-60, H-30, H-14, H-7, H-0 atau sudah kadaluarsa
            $harusKirim = false;
            if ($selisih === 90 || $selisih === 60 || $selisih === 30 || $selisih === 14 || $selisih === 7 || $selisih === 0) {
                $harusKirim = true;
            } elseif ($selisih < 0 && $selisih % 7 === 0) { // Pengingat mingguan jika sudah kadaluarsa
                $harusKirim = true;
            }

            if ($harusKirim && $sip->dokter?->user?->no_wa) {
                $res = $this->waService->kirimPeringatan($sip);
                if ($res['status']) {
                    $notifDikirim++;
                    $detailPengiriman[] = [
                        'dokter' => $sip->dokter->user->nama,
                        'nomor_sip' => $sip->nomor_sip,
                        'selisih' => $selisih,
                    ];
                }
            }
        }

        Log::info("SIP Daily Cron Check selesai. {$notifDikirim} pesan WhatsApp diproses.");

        return [
            'total_diperiksa' => $dokumenList->count(),
            'notifikasi_dikirim' => $notifDikirim,
            'detail' => $detailPengiriman,
        ];
    }

    /**
     * Dapatkan ringkasan statistik komprehensif untuk dashboard
     */
    public function getStatistik(): array
    {
        $totalDokter = Dokter::count();
        $totalSip = DokumenSip::count();

        $sipAktif = DokumenSip::where('status', 'aktif')->where('status_verifikasi', 'disetujui')->count();
        $sipDueSoon = DokumenSip::where('status', 'due_soon')->where('status_verifikasi', 'disetujui')->count();
        $sipDue = DokumenSip::where('status', 'due')->where('status_verifikasi', 'disetujui')->count();
        $sipOverdue = DokumenSip::where('status', 'overdue')->where('status_verifikasi', 'disetujui')->count();
        $sipMenunggu = DokumenSip::where('status_verifikasi', 'menunggu')->count();
        $sipDitolak = DokumenSip::where('status_verifikasi', 'ditolak')->count();

        $kredensialAktif = KredensialKlinis::where('status_kredensial', 'aktif')->count();
        $kredensialEvaluasi = KredensialKlinis::where('status_kredensial', 'evaluasi')->count();
        $kredensialKadaluarsa = KredensialKlinis::where('status_kredensial', 'kedaluwarsa')->count();
        $kredensialProsesRekredensial = KredensialKlinis::whereIn('status_kredensial', ['proses_rekredensial', 'sidang_komite'])->count();
        $kredensialRekomendasiTerbit = KredensialKlinis::where('status_kredensial', 'rekomendasi_terbit')->count();

        return compact(
            'totalDokter',
            'totalSip',
            'sipAktif',
            'sipDueSoon',
            'sipDue',
            'sipOverdue',
            'sipMenunggu',
            'sipDitolak',
            'kredensialAktif',
            'kredensialEvaluasi',
            'kredensialKadaluarsa',
            'kredensialProsesRekredensial',
            'kredensialRekomendasiTerbit'
        );
    }
}
