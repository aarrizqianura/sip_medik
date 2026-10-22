<?php

namespace App\Http\Controllers\Sdm;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DokumenSip;
use App\Models\Notifikasi;
use App\Services\SipMonitoringService;
use App\Services\WhatsAppGatewayService;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    protected WhatsAppGatewayService $waService;

    public function __construct(WhatsAppGatewayService $waService)
    {
        $this->waService = $waService;
    }

    public function index(Request $request)
    {
        $query = DokumenSip::with(['dokter.user', 'dokter.ksm'])
            ->where('status_verifikasi', 'disetujui');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default filter dokumen yang butuh perhatian (H-7 Kritis, H-30 Mendesak, H-90..60 Due Soon, Overdue)
            $query->whereIn('status', ['critical', 'due', 'due_soon', 'overdue']);
        }

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('nomor_sip', 'like', "%{$cari}%")
                    ->orWhereHas('dokter.user', function ($qu) use ($cari) {
                        $qu->where('nama', 'like', "%{$cari}%")
                            ->orWhere('no_wa', 'like', "%{$cari}%");
                    });
            });
        }

        $dokumenList = $query->orderBy('tanggal_kadaluarsa')->paginate(20)->withQueryString();

        // Hitung statistik 4 tingkatan early warning engine
        $ringkasan = [
            'aktif' => DokumenSip::where('status_verifikasi', 'disetujui')->where('status', 'aktif')->count(),
            'due_soon' => DokumenSip::where('status_verifikasi', 'disetujui')->where('status', 'due_soon')->count(),
            'due' => DokumenSip::where('status_verifikasi', 'disetujui')->where('status', 'due')->count(),
            'critical' => DokumenSip::where('status_verifikasi', 'disetujui')->where('status', 'critical')->count(),
            'overdue' => DokumenSip::where('status_verifikasi', 'disetujui')->where('status', 'overdue')->count(),
        ];

        // 8 Notifikasi Eskalasi Terakhir
        $riwayatEskalasi = Notifikasi::with(['user', 'dokumenSip.dokter.user'])
            ->latest()
            ->take(8)
            ->get();

        return view('sdm.reminder.index', compact('dokumenList', 'ringkasan', 'riwayatEskalasi'));
    }

    public function blast(Request $request)
    {
        $sipIds = $request->input('sip_ids', []);

        if (empty($sipIds)) {
            return back()->with('error', 'Pilih minimal satu dokter untuk dikirimi pesan pengingat WhatsApp.');
        }

        $dokumenList = DokumenSip::with(['dokter.user', 'dokter.ksm'])
            ->whereIn('id', $sipIds)
            ->get();

        $berhasil = 0;
        $gagal = 0;

        foreach ($dokumenList as $sip) {
            $res = $this->waService->kirimPeringatan($sip);
            if ($res['status']) {
                $berhasil++;
            } else {
                $gagal++;
            }
        }

        AuditLog::record('BLAST_WA_REMINDER', 'WhatsApp Gateway', "Kirim blast pengingat WhatsApp ke {$berhasil} dokter berhasil, {$gagal} gagal.");

        return back()->with('sukses', "Blast WhatsApp selesai: {$berhasil} pesan terkirim, {$gagal} gagal/simulasi.");
    }

    /**
     * Jalankan simulasi atau eksekusi manual Early Warning Engine
     */
    public function runEngine(Request $request, SipMonitoringService $monitoringService)
    {
        $force = (bool) $request->boolean('force', true);
        $hasil = $monitoringService->runDailyCheck($force);

        AuditLog::record(
            'TRIGGER_EARLY_WARNING',
            'Early Warning Engine',
            "SDM memicu evaluasi berjenjang. {$hasil['total_diperiksa']} berkas diperiksa, {$hasil['notifikasi_dikirim']} pesan terkirim."
        );

        return back()->with('sukses', "Early Warning Engine selesai dijalankan: {$hasil['total_diperiksa']} berkas diperiksa, {$hasil['notifikasi_dikirim']} notifikasi terkirim (Dokter & SDM).");
    }
}
