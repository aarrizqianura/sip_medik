<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Http\Requests\SipRequest;
use App\Models\DokumenSip;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengajuanSipController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $dokter = $user->dokter;

        if (!$dokter) {
            return redirect()->route('dashboard')->with('error', 'Profil dokter tidak ditemukan untuk akun ini.');
        }

        $sipList = $dokter->dokumenSip()->with('verifikator')->orderByDesc('created_at')->paginate(10);

        return view('dokter.pengajuan.index', compact('dokter', 'sipList'));
    }

    public function create()
    {
        $user = auth()->user();
        $dokter = $user->dokter;

        return view('dokter.pengajuan.create', compact('dokter'));
    }

    public function store(SipRequest $request)
    {
        $user = auth()->user();
        $dokter = $user->dokter;

        if (!$dokter && !in_array($user->role, ['super_admin', 'admin_sdm'])) {
            return back()->with('error', 'Profil dokter tidak ditemukan.');
        }

        $idDokter = $dokter ? $dokter->id : $request->id_dokter;

        $file = $request->file('file_sip');
        $fileName = 'sip_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('sip', $fileName, 'public');

        $sip = DokumenSip::create([
            'id_dokter' => $idDokter,
            'nomor_sip' => $request->nomor_sip,
            'tempat_praktik_ke' => $request->tempat_praktik_ke,
            'tanggal_terbit' => $request->tanggal_terbit,
            'tanggal_kadaluarsa' => $request->tanggal_kadaluarsa,
            'status' => 'aktif',
            'status_verifikasi' => 'menunggu',
            'file_sip' => 'sip/' . $fileName,
            'catatan_verifikasi' => null,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        $sip->updateStatusKadaluarsa();

        AuditLog::record('UNGGAH_SIP', 'Pengajuan SIP', "Mengunggah berkas SIP baru No. {$sip->nomor_sip} untuk verifikasi Komite Medik");

        return redirect()->route('dokter.pengajuan.index')
            ->with('sukses', "Berkas perpanjangan SIP No. {$sip->nomor_sip} berhasil diajukan! Menunggu telaah Subkomite Kredensial.");
    }

    public function show($id)
    {
        $sip = DokumenSip::with(['dokter.user', 'dokter.ksm', 'verifikator'])->findOrFail($id);

        // Dokter hanya boleh melihat miliknya sendiri (kecuali admin)
        if (auth()->user()->role === 'dokter' && $sip->dokter->id_user !== auth()->id()) {
            abort(403);
        }

        return view('sip.show', compact('sip'));
    }
}
