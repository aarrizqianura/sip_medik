<?php

namespace App\Http\Controllers\Sdm;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DokumenSip;
use App\Models\Ksm;
use App\Models\Notifikasi;
use App\Services\WhatsAppGatewayService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DispensasiController extends Controller
{
    public function __construct(
        protected WhatsAppGatewayService $waService
    ) {}

    /**
     * Halaman Utama Pengelolaan Masa Tenggang Administratif (Grace Period / On-Process)
     */
    public function index(Request $request): View
    {
        $statusTab = $request->query('tab', 'menunggu'); // 'menunggu', 'aktif', 'riwayat', 'semua'
        $ksmList = Ksm::orderBy('nama_ksm')->get();

        $queryMenunggu = DokumenSip::with(['dokter.user', 'dokter.ksm', 'dispensasiVerifikator'])
            ->where('status_dispensasi', 'diajukan');

        $queryAktif = DokumenSip::with(['dokter.user', 'dokter.ksm', 'dispensasiVerifikator'])
            ->where('status_dispensasi', 'disetujui')
            ->where('tanggal_akhir_dispensasi', '>=', Carbon::today());

        $queryRiwayat = DokumenSip::with(['dokter.user', 'dokter.ksm', 'dispensasiVerifikator'])
            ->whereIn('status_dispensasi', ['berakhir', 'ditolak']);

        $querySemua = DokumenSip::with(['dokter.user', 'dokter.ksm', 'dispensasiVerifikator'])
            ->where('status_dispensasi', '!=', 'none');

        // Filter KSM
        if ($request->filled('id_ksm')) {
            $idKsm = $request->id_ksm;
            $queryMenunggu->whereHas('dokter', fn ($q) => $q->where('id_ksm', $idKsm));
            $queryAktif->whereHas('dokter', fn ($q) => $q->where('id_ksm', $idKsm));
            $queryRiwayat->whereHas('dokter', fn ($q) => $q->where('id_ksm', $idKsm));
            $querySemua->whereHas('dokter', fn ($q) => $q->where('id_ksm', $idKsm));
        }

        // Pencarian Dokter / SIP / Resi
        if ($request->filled('cari')) {
            $cari = $request->cari;
            $filterCari = function ($q) use ($cari) {
                $q->where('nomor_sip', 'like', "%{$cari}%")
                    ->orWhere('nomor_resi_proses', 'like', "%{$cari}%")
                    ->orWhere('instansi_penerbit_resi', 'like', "%{$cari}%")
                    ->orWhereHas('dokter.user', fn ($qu) => $qu->where('nama', 'like', "%{$cari}%")->orWhere('nip_nrp', 'like', "%{$cari}%"));
            };

            $queryMenunggu->where($filterCari);
            $queryAktif->where($filterCari);
            $queryRiwayat->where($filterCari);
            $querySemua->where($filterCari);
        }

        $menungguList = $queryMenunggu->latest('tanggal_pengajuan_resi')->paginate(10, ['*'], 'page_menunggu')->withQueryString();
        $aktifList = $queryAktif->orderBy('tanggal_akhir_dispensasi')->paginate(10, ['*'], 'page_aktif')->withQueryString();
        $riwayatList = $queryRiwayat->latest('dispensasi_verified_at')->paginate(10, ['*'], 'page_riwayat')->withQueryString();
        $semuaList = $querySemua->latest('updated_at')->paginate(10, ['*'], 'page_semua')->withQueryString();

        // Statistik Metrik Dispensasi
        $totalMenunggu = DokumenSip::where('status_dispensasi', 'diajukan')->count();
        $totalAktif = DokumenSip::where('status_dispensasi', 'disetujui')->where('tanggal_akhir_dispensasi', '>=', Carbon::today())->count();
        $totalRiwayat = DokumenSip::whereIn('status_dispensasi', ['berakhir', 'ditolak'])->count();
        $totalDokterDalamProses = DokumenSip::where('status', 'dalam_proses')->distinct('id_dokter')->count('id_dokter');

        return view('sdm.dispensasi.index', compact(
            'menungguList',
            'aktifList',
            'riwayatList',
            'semuaList',
            'ksmList',
            'statusTab',
            'totalMenunggu',
            'totalAktif',
            'totalRiwayat',
            'totalDokterDalamProses'
        ));
    }

    /**
     * Telaah Resi Pengurusan & Form Verifikasi Pemberian Dispensasi Masa Tenggang
     */
    public function show(int $id): View
    {
        $sip = DokumenSip::with(['dokter.user', 'dokter.ksm', 'dokter.kredensialTerbaru', 'dispensasiVerifikator'])->findOrFail($id);
        $dokter = $sip->dokter;
        $spk = $dokter->kredensialTerbaru ?: $dokter->spkTerbaru;

        return view('sdm.dispensasi.show', compact('sip', 'dokter', 'spk'));
    }

    /**
     * Eksekusi Verifikasi Pemberian Dispensasi Terbatas (Maksimal 14 - 30 Hari) & Proteksi Pelayanan
     */
    public function verifikasi(Request $request, int $id): RedirectResponse
    {
        $sip = DokumenSip::with(['dokter.user', 'dokter.ksm'])->findOrFail($id);
        $dokter = $sip->dokter;

        $validated = $request->validate([
            'keputusan' => 'required|in:setujui,tolak',
            'durasi_dispensasi_hari' => 'required_if:keputusan,setujui|nullable|integer|min:14|max:30',
            'tanggal_mulai_dispensasi' => 'required_if:keputusan,setujui|nullable|date',
            'catatan_verifikasi_dispensasi' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            if ($validated['keputusan'] === 'setujui') {
                $durasi = (int) ($validated['durasi_dispensasi_hari'] ?? 30);
                $tglMulai = $validated['tanggal_mulai_dispensasi']
                    ? Carbon::parse($validated['tanggal_mulai_dispensasi'])
                    : ($sip->tanggal_kadaluarsa && Carbon::parse($sip->tanggal_kadaluarsa)->isFuture() ? Carbon::parse($sip->tanggal_kadaluarsa) : Carbon::today());
                $tglAkhir = $tglMulai->copy()->addDays($durasi);

                // 1. Perbarui status dokumen SIP menjadi dalam_proses
                $sip->status_dispensasi = 'disetujui';
                $sip->status = 'dalam_proses';
                $sip->tingkat_eskalasi = 'grace_period';
                $sip->durasi_dispensasi_hari = $durasi;
                $sip->tanggal_mulai_dispensasi = $tglMulai;
                $sip->tanggal_akhir_dispensasi = $tglAkhir;
                $sip->catatan_verifikasi_dispensasi = $validated['catatan_verifikasi_dispensasi'] ?? 'Resi tanda terima pengurusan DPMPTSP/Dinkes sah dan terverifikasi.';
                $sip->dispensasi_verified_by = auth()->id();
                $sip->dispensasi_verified_at = Carbon::now();
                $sip->save();

                // 2. Proteksi Pelayanan: Pastikan wewenang klinis (SPK/RKK) & status praktik terlindungi
                if ($dokter->status_praktik !== 'AKTIF_LEGAL' && $dokter->isArsipLengkapDanTerkunci()) {
                    $dokter->status_praktik = 'AKTIF_LEGAL';
                    $dokter->save();
                }

                // 3. Rekam jejak audit
                $tglAkhirFormatted = $tglAkhir->translatedFormat('d F Y');
                AuditLog::record(
                    'PEMBERIAN_DISPENSASI_GRACE_PERIOD',
                    'Dispensasi Masa Tenggang',
                    "Admin SDM menyetujui masa tenggang {$durasi} hari (berlaku s.d. {$tglAkhirFormatted}) untuk dr. {$dokter->user->nama} (SIP No. {$sip->nomor_sip}) berdasarkan Resi No. {$sip->nomor_resi_proses}. Status diaktifkan ke dalam_proses dengan proteksi pelayanan penuh."
                );

                // 4. Notifikasi WhatsApp & Sistem Internal ke Dokter
                $this->waService->kirimPemberitahuanDispensasi(
                    $sip,
                    $durasi,
                    $tglAkhirFormatted,
                    $validated['catatan_verifikasi_dispensasi'] ?? null
                );

                DB::commit();

                return redirect()->route('sdm.dispensasi.index', ['tab' => 'aktif'])
                    ->with('sukses', "Dispensasi masa tenggang ({$durasi} hari) berhasil diaktifkan untuk dr. {$dokter->user->nama}! Status SIP resmi menjadi 'Dalam Proses' dan wewenang klinis (SPK/RKK) terproteksi penuh hingga {$tglAkhirFormatted}.");
            } else {
                // Keputusan: TOLAK
                $sip->status_dispensasi = 'ditolak';
                $sip->catatan_verifikasi_dispensasi = $validated['catatan_verifikasi_dispensasi'] ?? 'Bukti resi pengurusan perizinan tidak memenuhi standar keabsahan DPMPTSP/Dinkes.';
                $sip->dispensasi_verified_by = auth()->id();
                $sip->dispensasi_verified_at = Carbon::now();
                $sip->updateStatusKadaluarsa();
                $sip->save();

                // Rekam jejak audit
                AuditLog::record(
                    'PENOLAKAN_DISPENSASI_SIP',
                    'Dispensasi Masa Tenggang',
                    "Admin SDM menolak permohonan masa tenggang dr. {$dokter->user->nama} untuk SIP No. {$sip->nomor_sip}. Alasan: {$sip->catatan_verifikasi_dispensasi}"
                );

                // Notifikasi ke dokter
                $this->waService->kirimPenolakanDispensasi($sip, $sip->catatan_verifikasi_dispensasi);

                DB::commit();

                return redirect()->route('sdm.dispensasi.index', ['tab' => 'riwayat'])
                    ->with('warning', "Pengajuan dispensasi masa tenggang untuk dr. {$dokter->user->nama} telah ditolak. Notifikasi penolakan telah dikirimkan.");
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Gagal memproses verifikasi dispensasi: '.$e->getMessage());
        }
    }

    /**
     * Akhiri Masa Tenggang Administratif (Manual Termination saat SIP Definitif Terbit / Selesai)
     */
    public function akhiri(Request $request, int $id): RedirectResponse
    {
        $sip = DokumenSip::with(['dokter.user'])->findOrFail($id);

        $sip->status_dispensasi = 'berakhir';
        $sip->updateStatusKadaluarsa();
        $sip->save();

        AuditLog::record(
            'AKHIRI_DISPENSASI_SIP',
            'Dispensasi Masa Tenggang',
            "Admin SDM mengakhiri status masa tenggang untuk SIP No. {$sip->nomor_sip} dr. {$sip->dokter->user->nama}"
        );

        return back()->with('sukses', "Masa tenggang administratif untuk SIP No. {$sip->nomor_sip} telah diakhiri.");
    }
}
