<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Http\Requests\SipRequest;
use App\Models\AuditLog;
use App\Models\Dokter;
use App\Models\DokumenSip;

class PengajuanSipController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $dokter = $user->dokter;

        if (! $dokter) {
            return redirect()->route('dashboard')->with('error', 'Profil dokter tidak ditemukan untuk akun ini.');
        }

        $isStrValid = $dokter->isStrValidDanTerkunci();
        $sipList = $dokter->dokumenSip()->with('verifikator')->orderByDesc('created_at')->paginate(10);

        return view('dokter.pengajuan.index', compact('dokter', 'sipList', 'isStrValid'));
    }

    public function create()
    {
        $user = auth()->user();
        $dokter = $user->dokter;

        if (! $dokter) {
            return redirect()->route('dashboard')->with('error', 'Profil dokter tidak ditemukan.');
        }

        // Syarat Mutlak (Hard Constraint)
        if (! $dokter->isStrValidDanTerkunci()) {
            return redirect()->route('dokter.pengajuan.index')->with(
                'error',
                'Modul pengurusan SIP tidak aktif. Syarat mutlak: Nomor STR 16 digit dan profil dokter harus telah tervalidasi dan dikunci oleh Subkomite Kredensial Komite Medik via SatuSehat SDMK.'
            );
        }

        return view('dokter.pengajuan.create', compact('dokter'));
    }

    public function store(SipRequest $request)
    {
        $user = auth()->user();
        $dokter = $user->dokter;

        if (! $dokter && ! in_array($user->role, ['super_admin', 'admin_sdm'])) {
            return back()->with('error', 'Profil dokter tidak ditemukan.');
        }

        $idDokter = $dokter ? $dokter->id : $request->id_dokter;
        $targetDokter = $dokter ?: Dokter::findOrFail($idDokter);

        // Syarat Mutlak (Hard Constraint)
        if (! $targetDokter->isStrValidDanTerkunci()) {
            return redirect()->route('dokter.pengajuan.index')->with(
                'error',
                'Gagal mengajukan SIP! Syarat mutlak belum terpenuhi: Data STR dokter bersangkutan belum tervalidasi dan terkunci oleh Subkomite Kredensial Komite Medik.'
            );
        }

        $file = $request->file('file_sip');
        $fileName = 'sip_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->storeAs('sip', $fileName, 'public');

        $fileSkpPath = null;
        if ($request->hasFile('file_rekomendasi_skp')) {
            $fileSkp = $request->file('file_rekomendasi_skp');
            $fileSkpName = 'skp_'.time().'_'.uniqid().'.'.$fileSkp->getClientOriginalExtension();
            $fileSkp->storeAs('skp', $fileSkpName, 'public');
            $fileSkpPath = 'skp/'.$fileSkpName;
        }

        $sip = DokumenSip::create([
            'id_dokter' => $idDokter,
            'nomor_sip' => $request->nomor_sip,
            'tempat_praktik_ke' => $request->tempat_praktik_ke,
            'fasyankes' => $request->input('fasyankes', 'RSUD dr. H. Moch. Ansari Saleh'),
            'alamat_fasyankes' => $request->input('alamat_fasyankes', 'Jl. Brigjend H. Hasan Basry No. 1, Banjarmasin'),
            'penerbit_sip' => $request->input('penerbit_sip', 'DPMPTSP Kota Banjarmasin'),
            'status_kecukupan_skp' => $request->boolean('status_kecukupan_skp'),
            'nomor_rekomendasi_skp' => $request->input('nomor_rekomendasi_skp'),
            'file_rekomendasi_skp' => $fileSkpPath,
            'permohonan_kewenangan_baru' => $request->boolean('permohonan_kewenangan_baru'),
            'catatan_kewenangan_baru' => $request->input('catatan_kewenangan_baru'),
            'tanggal_terbit' => $request->tanggal_terbit,
            'tanggal_kadaluarsa' => $request->tanggal_kadaluarsa,
            'status' => 'aktif',
            'status_verifikasi' => 'menunggu', // Status awal di antrean sistem
            'file_sip' => 'sip/'.$fileName,
            'catatan_verifikasi' => null,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        $sip->updateStatusKadaluarsa();

        AuditLog::record(
            'UNGGAH_SIP',
            'Pengajuan SIP',
            "Mengunggah berkas SIP DPMPTSP No. {$sip->nomor_sip} untuk RSUD dr. H. Moch. Ansari Saleh (Prasyarat SKP: ".($sip->status_kecukupan_skp ? 'Terpenuhi' : 'Rekomendasi SatuSehat').') ke antrean verifikasi Komite Medik'
        );

        return redirect()->route('dokter.pengajuan.index')
            ->with('sukses', "Berkas perpanjangan SIP No. {$sip->nomor_sip} berhasil disimpan di antrean sistem dengan status Menunggu Verifikasi Komite Medik!");
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
