<?php

namespace App\Services;

use App\Models\Dokter;
use App\Models\DokumenSip;
use App\Models\KredensialKlinis;
use App\Models\User;
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
     * Tahap 5: Early Warning Engine - Pemantauan Berjalan & Eskalasi Berjenjang
     * Berjalan via background cron job setiap hari pukul 00.00 WITA.
     *
     * Rules:
     * 1. Sisa > 90 Hari (aktif - Hijau): Status aman; sistem tidak mengirimkan pesan.
     * 2. H-90 s.d. H-60 (due_soon - Kuning): Notifikasi pengingat pertama melalui WhatsApp Gateway langsung ke nomor dokter.
     * 3. H-30 (due - Oranye): Pesan peringatan mendesak ke nomor dokter dan salinan rekapitulasi ke Admin SDM.
     * 4. H-7 (critical - Oranye Tua): Eskalasi berjenjang notifikasi peringatan kritis ke:
     *    - Dokter bersangkutan.
     *    - Staf Kepegawaian / SDM.
     */
    public function runDailyCheck(bool $force = false): array
    {
        $this->syncSemuaStatus();

        $dokumenList = DokumenSip::with(['dokter.user', 'dokter.ksm'])
            ->where('status_verifikasi', 'disetujui')
            ->get();

        $adminSdmList = User::where('role', 'admin_sdm')->where('aktif', true)->get();
        if ($adminSdmList->isEmpty()) {
            $adminSdmList = User::where('role', 'super_admin')->where('aktif', true)->get();
        }

        $notifDikirim = 0;
        $detailPengiriman = [];

        foreach ($dokumenList as $sip) {
            $selisih = $sip->hitungSisaHari();
            $userDokter = $sip->dokter?->user;
            $noWaDokter = $userDokter?->no_wa;

            // Tier 1: Sisa > 90 Hari (aktif - Hijau) -> Status aman; sistem TIDAK mengirimkan pesan
            if ($selisih > 90) {
                continue;
            }

            // Tier 2: H-90 s.d. H-60 (due_soon - Kuning)
            // Notifikasi pengingat pertama langsung ke nomor dokter agar segera persiapan SKP SatuSehat & berkas
            if ($selisih >= 60 && $selisih <= 90) {
                $harusKirim = $force || $selisih === 90 || $selisih === 60 || ! $sip->pernahDikirimNotifikasi('due_soon', 'dokter');
                if ($harusKirim && $noWaDokter) {
                    $res = $this->waService->kirimPeringatan($sip);
                    if ($res['status']) {
                        $notifDikirim++;
                        $detailPengiriman[] = [
                            'dokter' => $userDokter->nama,
                            'nomor_sip' => $sip->nomor_sip,
                            'selisih' => $selisih,
                            'eskalasi' => 'Dokter (Pengingat Pertama H90-H60)',
                            'penerima' => $userDokter->nama,
                        ];
                    }
                }

                continue;
            }

            // Tier 3: H-30 (due - Oranye)
            // Pesan peringatan mendesak ke nomor dokter dan kirim salinan rekapitulasi ke Admin SDM
            if ($selisih === 30 || ($selisih <= 30 && $selisih > 7 && ($force || ! $sip->pernahDikirimNotifikasi('due', 'dokter')))) {
                // 1. Pesan peringatan mendesak ke dokter
                if ($noWaDokter) {
                    $resDokter = $this->waService->kirimPeringatan($sip);
                    if ($resDokter['status']) {
                        $notifDikirim++;
                        $detailPengiriman[] = [
                            'dokter' => $userDokter->nama,
                            'nomor_sip' => $sip->nomor_sip,
                            'selisih' => $selisih,
                            'eskalasi' => 'Dokter (Peringatan Mendesak H-30)',
                            'penerima' => $userDokter->nama,
                        ];
                    }
                }

                // 2. Salinan rekapitulasi ke Admin SDM
                foreach ($adminSdmList as $adminSdm) {
                    $harusKirimSdm = $force || $selisih === 30 || ! $sip->pernahDikirimNotifikasi('due', 'admin_sdm');
                    if ($harusKirimSdm && $adminSdm->no_wa) {
                        $resSdm = $this->waService->kirimSalinanRekapitulasiSdm($sip, $adminSdm);
                        if ($resSdm['status']) {
                            $notifDikirim++;
                            $detailPengiriman[] = [
                                'dokter' => $userDokter?->nama ?? '-',
                                'nomor_sip' => $sip->nomor_sip,
                                'selisih' => $selisih,
                                'eskalasi' => 'Admin SDM (Salinan Rekapitulasi H-30)',
                                'penerima' => $adminSdm->nama,
                            ];
                        }
                    }
                }

                continue;
            }

            // Tier 4: H-7 (critical - Oranye Tua)
            // Eskalasi berjenjang peringatan kritis ke Dokter bersangkutan DAN Staf Kepegawaian / SDM
            if ($selisih === 7 || ($selisih <= 7 && $selisih >= 0 && ($force || ! $sip->pernahDikirimNotifikasi('critical', 'dokter')))) {
                // 1. Notifikasi peringatan kritis ke dokter bersangkutan
                if ($noWaDokter) {
                    $resDokter = $this->waService->kirimPeringatan($sip);
                    if ($resDokter['status']) {
                        $notifDikirim++;
                        $detailPengiriman[] = [
                            'dokter' => $userDokter->nama,
                            'nomor_sip' => $sip->nomor_sip,
                            'selisih' => $selisih,
                            'eskalasi' => 'Dokter (Eskalasi Kritis H-7)',
                            'penerima' => $userDokter->nama,
                        ];
                    }
                }

                // 2. Eskalasi berjenjang ke Staf Kepegawaian / SDM
                foreach ($adminSdmList as $stafSdm) {
                    $harusKirimStaf = $force || $selisih === 7 || ! $sip->pernahDikirimNotifikasi('critical', 'staf_sdm');
                    if ($harusKirimStaf && $stafSdm->no_wa) {
                        $resStaf = $this->waService->kirimEskalasiKritisSdm($sip, $stafSdm);
                        if ($resStaf['status']) {
                            $notifDikirim++;
                            $detailPengiriman[] = [
                                'dokter' => $userDokter?->nama ?? '-',
                                'nomor_sip' => $sip->nomor_sip,
                                'selisih' => $selisih,
                                'eskalasi' => 'Staf SDM (Eskalasi Berjenjang Kritis H-7)',
                                'penerima' => $stafSdm->nama,
                            ];
                        }
                    }
                }

                continue;
            }

            // Pengingat mingguan jika sudah kadaluarsa
            if ($selisih < 0 && $selisih % 7 === 0 && $noWaDokter) {
                $res = $this->waService->kirimPeringatan($sip);
                if ($res['status']) {
                    $notifDikirim++;
                    $detailPengiriman[] = [
                        'dokter' => $userDokter->nama,
                        'nomor_sip' => $sip->nomor_sip,
                        'selisih' => $selisih,
                        'eskalasi' => 'Dokter (Peringatan Kedaluwarsa)',
                        'penerima' => $userDokter->nama,
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
        $sipCritical = DokumenSip::where('status', 'critical')->where('status_verifikasi', 'disetujui')->count();
        $sipOverdue = DokumenSip::where('status', 'overdue')->where('status_verifikasi', 'disetujui')->count();
        $sipMenunggu = DokumenSip::where('status_verifikasi', 'menunggu')->count();
        $sipDitolak = DokumenSip::where('status_verifikasi', 'ditolak')->count();

        $kredensialAktif = KredensialKlinis::where('status_kredensial', 'aktif')->count();
        $kredensialEvaluasi = KredensialKlinis::where('status_kredensial', 'evaluasi')->count();
        $kredensialKadaluarsa = KredensialKlinis::where('status_kredensial', 'kedaluwarsa')->count();
        $kredensialProsesRekredensial = KredensialKlinis::whereIn('status_kredensial', ['proses_rekredensial', 'sidang_komite'])->count();
        $kredensialRekomendasiTerbit = KredensialKlinis::where('status_kredensial', 'rekomendasi_terbit')->count();

        // Statistik Tahap 4: Pengesahan Direksi & e-Arsip Kepegawaian
        $dokterAktifLegal = Dokter::where('status_praktik', 'AKTIF_LEGAL')->count();
        $dokterMenungguPengesahan = KredensialKlinis::where('status_rekomendasi', 'diterbitkan')
            ->where('status_kredensial', 'rekomendasi_terbit')
            ->count();
        $arsipTerkunci = Dokter::where('is_arsip_locked', true)->count();

        return compact(
            'totalDokter',
            'totalSip',
            'sipAktif',
            'sipDueSoon',
            'sipDue',
            'sipCritical',
            'sipOverdue',
            'sipMenunggu',
            'sipDitolak',
            'kredensialAktif',
            'kredensialEvaluasi',
            'kredensialKadaluarsa',
            'kredensialProsesRekredensial',
            'kredensialRekomendasiTerbit',
            'dokterAktifLegal',
            'dokterMenungguPengesahan',
            'arsipTerkunci'
        );
    }
}
