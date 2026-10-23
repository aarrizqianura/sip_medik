<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Http\Requests\SipRequest;
use App\Models\AuditLog;
use App\Models\Dokter;
use App\Models\DokumenSip;
use App\Models\Notifikasi;
use App\Models\User;
use App\Services\WhatsAppGatewayService;
use Illuminate\Http\Request;

class PengajuanSipController extends Controller
{
    public function __construct(
        protected ?WhatsAppGatewayService $waService = null
    ) {
        $this->waService = $waService ?: app(WhatsAppGatewayService::class);
    }

    public function index()
    {
        $user = auth()->user();
        $dokter = $user->dokter;

        if (! $dokter) {
            return redirect()->route('dashboard')->with('error', 'Profil dokter tidak ditemukan untuk akun ini.');
        }

        $isStrValid = $dokter->isStrValidDanTerkunci();
        $sipList = $dokter->dokumenSip()->with(['verifikator', 'dispensasiVerifikator'])->orderByDesc('created_at')->paginate(10);

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

    /**
     * Tahap 6: Formulir Pengajuan Bukti Proses Perizinan DPMPTSP/Dinkes (Masa Tenggang)
     */
    public function createDispensasi(int $id)
    {
        $user = auth()->user();
        $dokter = $user->dokter;

        $sip = DokumenSip::with(['dokter.user', 'dokter.ksm'])->findOrFail($id);

        if ($user->role === 'dokter' && (! $dokter || $sip->id_dokter !== $dokter->id)) {
            abort(403, 'Anda tidak berhak mengakses berkas SIP ini.');
        }

        $targetDokter = $sip->dokter;

        return view('dokter.pengajuan.dispensasi', compact('sip', 'targetDokter'));
    }

    /**
     * Simpan Unggahan Resi Tanda Terima Pengurusan / Suket Dalam Proses
     */
    public function storeDispensasi(Request $request, int $id)
    {
        $user = auth()->user();
        $dokter = $user->dokter;

        $sip = DokumenSip::with(['dokter.user', 'dokter.ksm'])->findOrFail($id);

        if ($user->role === 'dokter' && (! $dokter || $sip->id_dokter !== $dokter->id)) {
            abort(403);
        }

        $targetDokter = $sip->dokter;

        $validated = $request->validate([
            'nomor_resi_proses' => 'required|string|max:150',
            'instansi_penerbit_resi' => 'required|string|max:150',
            'tanggal_pengajuan_resi' => 'required|date',
            'durasi_dispensasi_diminta' => 'nullable|integer|min:14|max:30',
            'file_bukti_proses' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'keterangan_proses' => 'nullable|string',
        ]);

        $file = $request->file('file_bukti_proses');
        $fileName = 'resi_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $filePath = $file->storeAs('bukti_proses', $fileName, 'public');

        $sip->status_dispensasi = 'diajukan';
        $sip->nomor_resi_proses = $validated['nomor_resi_proses'];
        $sip->instansi_penerbit_resi = $validated['instansi_penerbit_resi'];
        $sip->tanggal_pengajuan_resi = $validated['tanggal_pengajuan_resi'];
        $sip->durasi_dispensasi_hari = (int) ($validated['durasi_dispensasi_diminta'] ?? 30);
        $sip->file_bukti_proses = $filePath;
        $sip->keterangan_proses = $validated['keterangan_proses'] ?? null;
        $sip->save();

        AuditLog::record(
            'PENGAJUAN_DISPENSASI',
            'Dispensasi Masa Tenggang',
            "dr. {$targetDokter->user->nama} mengunggah bukti proses perizinan (Resi No. {$sip->nomor_resi_proses} di {$sip->instansi_penerbit_resi}) untuk SIP No. {$sip->nomor_sip}"
        );

        // Notifikasi ke Admin SDM
        $adminSdmList = User::whereIn('role', ['admin_sdm', 'super_admin'])->where('aktif', true)->get();
        foreach ($adminSdmList as $admin) {
            Notifikasi::create([
                'id_user' => $admin->id,
                'id_sip' => $sip->id,
                'tingkat_eskalasi' => 'pengajuan_dispensasi',
                'tipe_penerima' => 'admin_sdm',
                'pesan' => "Permohonan Masa Tenggang (Grace Period): dr. {$targetDokter->user->nama} mengunggah Resi {$sip->instansi_penerbit_resi} No. {$sip->nomor_resi_proses} untuk SIP No. {$sip->nomor_sip}. Menunggu verifikasi SDM.",
                'kanal' => 'sistem',
                'status_terkirim' => true,
                'status_baca' => false,
            ]);
        }

        return redirect()->route('dokter.pengajuan.index')
            ->with('sukses', "Bukti proses perizinan (Resi No. {$sip->nomor_resi_proses}) berhasil diajukan! Bagian Kepegawaian SDM akan memverifikasi untuk pemberian dispensasi masa tenggang (Grace Period).");
    }
}
