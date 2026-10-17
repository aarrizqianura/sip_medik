<?php

namespace App\Http\Controllers\Komite;

use App\Http\Controllers\Controller;
use App\Models\DokumenSip;
use App\Models\AuditLog;
use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VerifikasiSipController extends Controller
{
    public function index(Request $request)
    {
        $query = DokumenSip::with(['dokter.user', 'dokter.ksm', 'verifikator']);

        if ($request->filled('status_verifikasi')) {
            $query->where('status_verifikasi', $request->status_verifikasi);
        } else {
            // Default tunjukkan yang menunggu verifikasi terlebih dahulu
            $query->orderByRaw("CASE WHEN status_verifikasi = 'menunggu' THEN 1 ELSE 2 END");
        }

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('nomor_sip', 'like', "%{$cari}%")
                  ->orWhereHas('dokter.user', function ($qu) use ($cari) {
                      $qu->where('nama', 'like', "%{$cari}%");
                  });
            });
        }

        $sipList = $query->latest()->paginate(15)->withQueryString();

        return view('verifikasi.index', compact('sipList'));
    }

    public function show($id)
    {
        $sip = DokumenSip::with(['dokter.user', 'dokter.ksm', 'verifikator'])->findOrFail($id);
        return view('verifikasi.review', compact('sip'));
    }

    public function proses(Request $request, $id)
    {
        $sip = DokumenSip::with('dokter.user')->findOrFail($id);

        $validated = $request->validate([
            'status_verifikasi' => 'required|in:disetujui,ditolak',
            'catatan_verifikasi' => 'nullable|string|max:1000',
        ]);

        if ($validated['status_verifikasi'] === 'ditolak' && empty($validated['catatan_verifikasi'])) {
            return back()->withErrors(['catatan_verifikasi' => 'Catatan telaah klinis/alasan penolakan wajib diisi jika berkas ditolak.']);
        }

        $sip->status_verifikasi = $validated['status_verifikasi'];
        $sip->catatan_verifikasi = $validated['catatan_verifikasi'];
        $sip->verified_by = auth()->id();
        $sip->verified_at = Carbon::now();

        if ($validated['status_verifikasi'] === 'disetujui') {
            $sip->updateStatusKadaluarsa();
            $statusMsg = "disetujui dan dinyatakan sah";
        } else {
            $statusMsg = "ditolak dengan catatan: " . $validated['catatan_verifikasi'];
        }

        $sip->save();

        // Notifikasi ke dokter
        $dokterUser = $sip->dokter->user;
        $pesanNotif = "Halo dr. {$dokterUser->nama}, berkas SIP No. {$sip->nomor_sip} Anda telah {$statusMsg} oleh Komite Medik.";

        Notifikasi::create([
            'id_user' => $dokterUser->id,
            'id_sip' => $sip->id,
            'pesan' => $pesanNotif,
            'kanal' => 'sistem',
            'status_terkirim' => true,
        ]);

        AuditLog::record(
            'VERIFIKASI_SIP',
            'Komite Medik',
            "Memverifikasi SIP No. {$sip->nomor_sip} kepunyaan {$dokterUser->nama} dengan keputusan: {$validated['status_verifikasi']}"
        );

        return redirect()->route('komite.verifikasi.index')
            ->with('sukses', "Pengesahan berkas SIP No. {$sip->nomor_sip} ({$dokterUser->nama}) berhasil disimpan.");
    }
}
