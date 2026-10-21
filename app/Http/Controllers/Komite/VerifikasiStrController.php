<?php

namespace App\Http\Controllers\Komite;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Dokter;
use App\Models\Notifikasi;
use App\Services\SatuSehatSdmkService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VerifikasiStrController extends Controller
{
    public function __construct(
        protected SatuSehatSdmkService $satuSehatService
    ) {}

    public function index(Request $request)
    {
        $query = Dokter::with(['user', 'ksm', 'verifikatorStr']);

        if ($request->filled('status_verifikasi')) {
            $query->where('status_verifikasi_str', $request->status_verifikasi);
        } else {
            // Prioritaskan dokter yang sudah mengisi STR dan menunggu verifikasi
            $query->orderByRaw("CASE 
                WHEN status_verifikasi_str = 'menunggu_verifikasi' THEN 1 
                WHEN status_verifikasi_str = 'belum_lengkap' THEN 2 
                WHEN status_verifikasi_str = 'ditolak' THEN 3 
                ELSE 4 END");
        }

        if ($request->filled('id_ksm')) {
            $query->where('id_ksm', $request->id_ksm);
        }

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('nip_nrp', 'like', "%{$cari}%")
                    ->orWhere('nomor_str', 'like', "%{$cari}%")
                    ->orWhere('spesialisasi', 'like', "%{$cari}%")
                    ->orWhere('subspesialis', 'like', "%{$cari}%")
                    ->orWhereHas('user', function ($qu) use ($cari) {
                        $qu->where('nama', 'like', "%{$cari}%");
                    });
            });
        }

        $dokterList = $query->latest('updated_at')->paginate(15)->withQueryString();

        $statistik = [
            'total' => Dokter::count(),
            'menunggu' => Dokter::where('status_verifikasi_str', 'menunggu_verifikasi')->count(),
            'tervalidasi' => Dokter::where('status_verifikasi_str', 'tervalidasi')->count(),
            'ditolak' => Dokter::where('status_verifikasi_str', 'ditolak')->count(),
        ];

        return view('komite.verifikasi_str.index', compact('dokterList', 'statistik'));
    }

    public function show(Dokter $dokter)
    {
        $dokter->load(['user', 'ksm', 'verifikatorStr']);

        // Jika belum ada snapshot SatuSehat dan ada nomor STR 16 digit, lakukan preview
        $previewSatuSehat = null;
        if ($dokter->nomor_str) {
            $previewSatuSehat = $this->satuSehatService->periksaStr(
                $dokter->nomor_str,
                $dokter->user->nama,
                $dokter->spesialisasi.($dokter->subspesialis ? ' / '.$dokter->subspesialis : '')
            );
        }

        return view('komite.verifikasi_str.show', compact('dokter', 'previewSatuSehat'));
    }

    public function periksaSatuSehat(Dokter $dokter)
    {
        if (empty($dokter->nomor_str)) {
            return response()->json([
                'success' => false,
                'message' => 'Dokter belum memiliki nomor STR yang tercatat.',
            ], 422);
        }

        $hasil = $this->satuSehatService->periksaStr(
            $dokter->nomor_str,
            $dokter->user->nama,
            $dokter->spesialisasi.($dokter->subspesialis ? ' / '.$dokter->subspesialis : '')
        );

        return response()->json([
            'success' => $hasil['is_valid'],
            'message' => $hasil['pesan'],
            'data' => $hasil['data'],
        ]);
    }

    public function validasi(Request $request, Dokter $dokter)
    {
        $validated = $request->validate([
            'keputusan' => 'required|in:tervalidasi,ditolak',
            'catatan' => 'nullable|string|max:1000',
        ]);

        if ($validated['keputusan'] === 'ditolak' && empty($validated['catatan'])) {
            return back()->withErrors(['catatan' => 'Wajib memberikan catatan/alasan penolakan hasil telaah kredensial STR.']);
        }

        if (empty($dokter->nomor_str) || strlen($dokter->nomor_str) !== 16) {
            return back()->with('error', 'Nomor registrasi STR tidak memenuhi kriteria 16 digit angka KKI/Kemenkes.');
        }

        if ($validated['keputusan'] === 'tervalidasi') {
            // Jalankan sinkronisasi / snapshot pangkalan data SatuSehat SDMK
            $satuSehat = $this->satuSehatService->periksaStr(
                $dokter->nomor_str,
                $dokter->user->nama,
                $dokter->spesialisasi.($dokter->subspesialis ? ' / '.$dokter->subspesialis : '')
            );

            $dokter->status_verifikasi_str = 'tervalidasi';
            $dokter->is_str_locked = true; // KUNCI DATA NOMOR STR (HARD CONSTRAINT)
            $dokter->verified_str_by = auth()->id();
            $dokter->verified_str_at = Carbon::now();
            $dokter->catatan_verifikasi_str = $validated['catatan'] ?: 'STR tervalidasi sesuai dengan pangkalan data SatuSehat SDMK Kemenkes RI.';
            $dokter->satusehat_verified_at = Carbon::now();
            $dokter->satusehat_data = $satuSehat['data'] ?? null;
            $dokter->save();

            AuditLog::record(
                'VERIFIKASI_STR_VALID',
                'Subkomite Kredensial',
                "Memvalidasi & MENGUNCI Master Data STR No. {$dokter->nomor_str} untuk {$dokter->user->nama}. Modul Pengurusan SIP dokter kini aktif."
            );

            // Notifikasi ke dokter
            Notifikasi::create([
                'id_user' => $dokter->user->id,
                'id_sip' => null,
                'pesan' => "Selamat dr. {$dokter->user->nama}, Data Master STR No. {$dokter->nomor_str} telah disahkan dan tervalidasi oleh Subkomite Kredensial Komite Medik via SatuSehat SDMK. Nomor STR terkunci dan modul pengurusan SIP Anda kini telah aktif.",
                'kanal' => 'sistem',
                'status_terkirim' => true,
            ]);

            return redirect()->route('komite.verifikasi_str.index')
                ->with('sukses', "Data STR dr. {$dokter->user->nama} berhasil divalidasi dan NOMOR STR RESMI TERKUNCI (Read-Only). Modul pengurusan SIP dokter telah dibuka.");
        } else {
            $dokter->status_verifikasi_str = 'ditolak';
            $dokter->is_str_locked = false;
            $dokter->verified_str_by = auth()->id();
            $dokter->verified_str_at = Carbon::now();
            $dokter->catatan_verifikasi_str = $validated['catatan'];
            $dokter->save();

            AuditLog::record(
                'VERIFIKASI_STR_REJECT',
                'Subkomite Kredensial',
                "Menolak verifikasi STR No. {$dokter->nomor_str} kepunyaan {$dokter->user->nama} dengan catatan: {$validated['catatan']}"
            );

            // Notifikasi penolakan ke dokter
            Notifikasi::create([
                'id_user' => $dokter->user->id,
                'id_sip' => null,
                'pesan' => "Pemberitahuan: Data STR No. {$dokter->nomor_str} Anda belum dapat disahkan oleh Subkomite Kredensial dengan catatan: {$validated['catatan']}. Silakan hubungi Bagian SDM untuk perbaikan berkas.",
                'kanal' => 'sistem',
                'status_terkirim' => true,
            ]);

            return redirect()->route('komite.verifikasi_str.index')
                ->with('sukses', "Verifikasi STR dr. {$dokter->user->nama} ditolak dengan catatan. Dokter belum dapat mengajukan perpanjangan SIP.");
        }
    }

    public function unlock(Dokter $dokter)
    {
        // Hanya super_admin atau ketua komite yang dapat membuka kunci jika ada revisi
        $user = auth()->user();
        if (! in_array($user->role, ['super_admin', 'verifikator_komite'])) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $dokter->is_str_locked = false;
        $dokter->status_verifikasi_str = 'menunggu_verifikasi';
        $dokter->save();

        AuditLog::record(
            'UNLOCK_STR',
            'Subkomite Kredensial',
            "Membuka kunci data STR dokter {$dokter->user->nama} (No. STR: {$dokter->nomor_str}) untuk pembaruan data."
        );

        return back()->with('sukses', "Kunci nomor STR dokter {$dokter->user->nama} berhasil dibuka untuk penyesuaian.");
    }
}
