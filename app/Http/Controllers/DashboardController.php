<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dokter;
use App\Models\DokumenSip;
use App\Models\KredensialKlinis;
use App\Models\Ksm;
use App\Models\Notifikasi;
use App\Models\User;
use App\Services\SipMonitoringService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected SipMonitoringService $monitoringService;

    public function __construct(SipMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    public function index()
    {
        $user = auth()->user();
        $stats = $this->monitoringService->getStatistik();

        if ($user->role === 'dokter') {
            $dokter = $user->dokter;
            $isStrValid = $dokter ? $dokter->isStrValidDanTerkunci() : false;
            $isSuspended = $dokter ? $dokter->isSuspended() : false;
            $alasanSuspended = $dokter?->alasan_penangguhan;
            $sipList = $dokter ? $dokter->dokumenSip()->with('verifikator')->get() : collect();
            $sipAktif = $dokter ? $dokter->dokumenSip()->where('status_verifikasi', 'disetujui')->orderByDesc('tanggal_kadaluarsa')->first() : null;
            $kredensialList = $dokter ? $dokter->kredensialKlinis()->get() : collect();
            $kredensialAktif = $dokter ? $dokter->kredensialKlinis()->orderByDesc('tanggal_berakhir_spk')->first() : null;
            $notifikasiList = $user->notifikasi()->latest()->take(5)->get();

            return view('dashboard.dokter', compact(
                'user',
                'dokter',
                'isStrValid',
                'isSuspended',
                'alasanSuspended',
                'sipList',
                'sipAktif',
                'kredensialList',
                'kredensialAktif',
                'notifikasiList'
            ));
        }

        // Shared / Executive Data
        $antreanVerifikasi = DokumenSip::with(['dokter.user', 'dokter.ksm'])
            ->where('status_verifikasi', 'menunggu')
            ->orderBy('created_at')
            ->take(5)
            ->get();

        $antreanStr = Dokter::with(['user', 'ksm'])
            ->where('status_verifikasi_str', 'menunggu_verifikasi')
            ->latest('updated_at')
            ->take(5)
            ->get();

        $stats['strMenunggu'] = Dokter::where('status_verifikasi_str', 'menunggu_verifikasi')->count();
        $stats['strTervalidasi'] = Dokter::where('status_verifikasi_str', 'tervalidasi')->count();

        // Tahap 7: Staf Medis yang Ditangguhkan (Auto-Suspend Overdue)
        $dokterDitangguhkanList = Dokter::with(['user', 'ksm', 'sipTerbaru', 'spkTerbaru'])
            ->where(function ($q) {
                $q->where('status_praktik', 'DITANGGUHKAN')
                    ->orWhereHas('dokumenSip', function ($sub) {
                        $sub->where('status', 'overdue')
                            ->where(function ($sub2) {
                                $sub2->whereNull('status_dispensasi')
                                    ->orWhere('status_dispensasi', '!=', 'disetujui')
                                    ->orWhere('tanggal_akhir_dispensasi', '<', Carbon::today());
                            });
                    });
            })
            ->get();

        $dokumenKritis = DokumenSip::with(['dokter.user', 'dokter.ksm'])
            ->where('status_verifikasi', 'disetujui')
            ->whereIn('status', ['critical', 'due', 'overdue'])
            ->orderBy('tanggal_kadaluarsa')
            ->take(10)
            ->get();

        $auditTerbaru = AuditLog::with('user')->latest()->take(8)->get();
        $notifikasiTerbaru = Notifikasi::with('user')->latest()->take(8)->get();

        // Rekap KSM & Kepatuhan Legalitas
        $ksmStatistik = Ksm::with(['dokters.sipTerbaru', 'dokters.spkTerbaru'])->withCount('dokters')->get();

        if ($user->role === 'super_admin') {
            $totalUser = User::count();

            return view('dashboard.super_admin', compact(
                'stats',
                'totalUser',
                'antreanVerifikasi',
                'antreanStr',
                'dokumenKritis',
                'dokterDitangguhkanList',
                'auditTerbaru',
                'ksmStatistik',
                'notifikasiTerbaru'
            ));
        }

        if ($user->role === 'admin_sdm') {
            return view('dashboard.admin_sdm', compact(
                'stats',
                'antreanVerifikasi',
                'antreanStr',
                'dokumenKritis',
                'dokterDitangguhkanList',
                'ksmStatistik',
                'notifikasiTerbaru'
            ));
        }

        if ($user->role === 'verifikator_komite') {
            $spkEvaluasi = KredensialKlinis::with(['dokter.user', 'dokter.ksm'])
                ->whereIn('status_kredensial', ['evaluasi', 'kedaluwarsa', 'ditangguhkan'])
                ->orderBy('tanggal_berakhir_spk')
                ->take(10)
                ->get();

            return view('dashboard.verifikator', compact(
                'stats',
                'antreanVerifikasi',
                'antreanStr',
                'dokumenKritis',
                'dokterDitangguhkanList',
                'spkEvaluasi'
            ));
        }

        abort(403);
    }
}
