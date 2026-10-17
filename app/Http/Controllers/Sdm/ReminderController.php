<?php

namespace App\Http\Controllers\Sdm;

use App\Http\Controllers\Controller;
use App\Models\DokumenSip;
use App\Models\AuditLog;
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
            // Default filter dokumen yang butuh perhatian
            $query->whereIn('status', ['due_soon', 'due', 'overdue']);
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

        return view('sdm.reminder.index', compact('dokumenList'));
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
}
