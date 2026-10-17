<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SipMonitoringService;
use App\Models\DokumenSip;
use App\Models\KredensialKlinis;
use App\Models\Dokter;
use App\Models\User;
use App\Models\Ksm;
use App\Models\AuditLog;
use App\Models\Notifikasi;

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
            $sipList = $dokter ? $dokter->dokumenSip()->with('verifikator')->get() : collect();
            $sipAktif = $dokter ? $dokter->dokumenSip()->where('status_verifikasi', 'disetujui')->orderByDesc('tanggal_kadaluarsa')->first() : null;
            $kredensialList = $dokter ? $dokter->kredensialKlinis()->get() : collect();
            $kredensialAktif = $dokter ? $dokter->kredensialKlinis()->orderByDesc('tanggal_berakhir_spk')->first() : null;
            $notifikasiList = $user->notifikasi()->latest()->take(5)->get();

            return view('dashboard.dokter', compact(
                'user',
                'dokter',
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

        $dokumenKritis = DokumenSip::with(['dokter.user', 'dokter.ksm'])
            ->where('status_verifikasi', 'disetujui')
            ->whereIn('status', ['due', 'overdue'])
            ->orderBy('tanggal_kadaluarsa')
            ->take(10)
            ->get();

        $auditTerbaru = AuditLog::with('user')->latest()->take(8)->get();
        $notifikasiTerbaru = Notifikasi::with('user')->latest()->take(8)->get();

        // Rekap KSM
        $ksmStatistik = Ksm::withCount('dokters')->get();

        if ($user->role === 'super_admin') {
            $totalUser = User::count();
            return view('dashboard.super_admin', compact(
                'stats',
                'totalUser',
                'antreanVerifikasi',
                'dokumenKritis',
                'auditTerbaru',
                'ksmStatistik',
                'notifikasiTerbaru'
            ));
        }

        if ($user->role === 'admin_sdm') {
            return view('dashboard.admin_sdm', compact(
                'stats',
                'antreanVerifikasi',
                'dokumenKritis',
                'ksmStatistik',
                'notifikasiTerbaru'
            ));
        }

        if ($user->role === 'verifikator_komite') {
            $spkEvaluasi = KredensialKlinis::with(['dokter.user', 'dokter.ksm'])
                ->whereIn('status_kredensial', ['evaluasi', 'kedaluwarsa'])
                ->orderBy('tanggal_berakhir_spk')
                ->take(10)
                ->get();

            return view('dashboard.verifikator', compact(
                'stats',
                'antreanVerifikasi',
                'dokumenKritis',
                'spkEvaluasi'
            ));
        }

        abort(403);
    }
}
