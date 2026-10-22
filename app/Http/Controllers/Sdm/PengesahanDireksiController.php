<?php

namespace App\Http\Controllers\Sdm;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Dokter;
use App\Models\KredensialKlinis;
use App\Models\Ksm;
use App\Models\Notifikasi;
use App\Services\WhatsAppGatewayService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PengesahanDireksiController extends Controller
{
    public function __construct(
        public WhatsAppGatewayService $waService
    ) {}

    /**
     * Halaman Utama Pengesahan Direksi & Repositori e-Arsip Digital Kepegawaian (SDM)
     */
    public function index(Request $request): View
    {
        $statusTab = $request->query('tab', 'menunggu'); // 'menunggu', 'aktif_legal', 'semua'

        $ksmList = Ksm::orderBy('nama_ksm')->get();

        // 1. Antrean Rekomendasi Siap Disahkan Direktur (Tahap 4 Pintu Masuk)
        $queryMenunggu = KredensialKlinis::with(['dokter.user', 'dokter.ksm', 'dokter.sipTerbaru'])
            ->where(function ($q) {
                $q->where('status_rekomendasi', 'diterbitkan')
                    ->orWhere('status_kredensial', 'rekomendasi_terbit')
                    ->orWhere(function ($sub) {
                        $sub->where('tahap_rekredensial', 'rekomendasi_terbit')
                            ->where('status_rekomendasi', '!=', 'disahkan_direktur');
                    });
            });

        // 2. Daftar Dokter Berstatus AKTIF_LEGAL & e-Arsip Terkunci
        $queryAktif = Dokter::with(['user', 'ksm', 'sipTerbaru', 'kredensialTerbaru', 'spkTerbaru'])
            ->where('status_praktik', 'AKTIF_LEGAL');

        // 3. Seluruh Repositori e-Arsip
        $querySemua = Dokter::with(['user', 'ksm', 'sipTerbaru', 'kredensialTerbaru', 'spkTerbaru']);

        if ($request->filled('id_ksm')) {
            $idKsm = $request->id_ksm;
            $queryMenunggu->whereHas('dokter', fn ($q) => $q->where('id_ksm', $idKsm));
            $queryAktif->where('id_ksm', $idKsm);
            $querySemua->where('id_ksm', $idKsm);
        }

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $queryMenunggu->where(function ($q) use ($cari) {
                $q->where('nomor_surat_rekomendasi', 'like', "%{$cari}%")
                    ->orWhere('nomor_spk', 'like', "%{$cari}%")
                    ->orWhereHas('dokter.user', fn ($qu) => $qu->where('nama', 'like', "%{$cari}%"));
            });

            $queryAktif->where(function ($q) use ($cari) {
                $q->where('nip_nrp', 'like', "%{$cari}%")
                    ->orWhere('nomor_str', 'like', "%{$cari}%")
                    ->orWhere('nomor_sk_direktur', 'like', "%{$cari}%")
                    ->orWhereHas('user', fn ($qu) => $qu->where('nama', 'like', "%{$cari}%"));
            });

            $querySemua->where(function ($q) use ($cari) {
                $q->where('nip_nrp', 'like', "%{$cari}%")
                    ->orWhere('nomor_str', 'like', "%{$cari}%")
                    ->orWhere('nomor_sk_direktur', 'like', "%{$cari}%")
                    ->orWhereHas('user', fn ($qu) => $qu->where('nama', 'like', "%{$cari}%"));
            });
        }

        $menungguPengesahan = $queryMenunggu->latest('tanggal_rekomendasi')->paginate(10, ['*'], 'page_menunggu')->withQueryString();
        $dokterAktifLegal = $queryAktif->latest('arsip_locked_at')->paginate(10, ['*'], 'page_aktif')->withQueryString();
        $semuaArsip = $querySemua->latest('updated_at')->paginate(10, ['*'], 'page_semua')->withQueryString();

        // Rekapitulasi Metrik
        $totalSiapSah = KredensialKlinis::where('status_rekomendasi', 'diterbitkan')->count();
        $totalAktifLegal = Dokter::where('status_praktik', 'AKTIF_LEGAL')->count();
        $totalArsipTerkunci = Dokter::where('is_arsip_locked', true)->count();
        $totalDokter = Dokter::count();

        return view('sdm.pengesahan.index', compact(
            'menungguPengesahan',
            'dokterAktifLegal',
            'semuaArsip',
            'ksmList',
            'statusTab',
            'totalSiapSah',
            'totalAktifLegal',
            'totalArsipTerkunci',
            'totalDokter'
        ));
    }

    /**
     * Halaman Formulir Penerbitan SK Direktur & Penguncian e-Arsip Terpadu
     */
    public function create(int $kredensialId): View
    {
        $kredensial = KredensialKlinis::with(['dokter.user', 'dokter.ksm', 'dokter.sipTerbaru'])->findOrFail($kredensialId);
        $dokter = $kredensial->dokter;
        $sip = $dokter->sipTerbaru;

        // Nomor SK Direktur Otomatis (Rekomendasi Format Resmi RSUD Ansari Saleh)
        $kodeKsm = $dokter->ksm?->kode_ksm ?: 'MED';
        $nomorSkOtomatis = '800/'.rand(100, 999).'-DIR/RSAS/SK-'.$kodeKsm.'/'.date('Y');

        return view('sdm.pengesahan.form', compact('kredensial', 'dokter', 'sip', 'nomorSkOtomatis'));
    }

    /**
     * Eksekusi Pengesahan Direksi, Penguncian e-Arsip, dan Aktivasi Hak Praktik Penuh (AKTIF_LEGAL)
     */
    public function store(Request $request, int $kredensialId): RedirectResponse
    {
        $kredensial = KredensialKlinis::with(['dokter.user', 'dokter.ksm', 'dokter.sipTerbaru'])->findOrFail($kredensialId);
        $dokter = $kredensial->dokter;
        $sip = $dokter->sipTerbaru;

        $validated = $request->validate([
            'nomor_sk_direktur' => 'required|string|max:150',
            'tanggal_sk_direktur' => 'required|date',
            'tanggal_berakhir_spk' => 'required|date|after:tanggal_sk_direktur',
            'file_sk_direktur' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'catatan_pengesahan_direksi' => 'nullable|string',
            'kunci_arsip' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // 1. Simpan berkas salinan SK Direktur & Lampiran RKK jika diunggah
            $fileSkPath = $kredensial->file_sk_direktur;
            if ($request->hasFile('file_sk_direktur')) {
                $file = $request->file('file_sk_direktur');
                $filename = 'sk_dir_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $fileSkPath = $file->storeAs('sk_direktur', $filename, 'public');
            }

            // 2. Perbarui Instrumen Kredensial Klinis (SPK & RKK Masa 3 Tahun)
            $kredensial->nomor_spk = $validated['nomor_sk_direktur'];
            $kredensial->nomor_sk_direktur = $validated['nomor_sk_direktur'];
            $kredensial->tanggal_sk_direktur = $validated['tanggal_sk_direktur'];
            $kredensial->tanggal_terbit_spk = $validated['tanggal_sk_direktur'];
            $kredensial->tanggal_berakhir_spk = $validated['tanggal_berakhir_spk'];
            $kredensial->status_kredensial = 'aktif';
            $kredensial->tahap_rekredensial = 'spk_diterbitkan';
            $kredensial->status_rekomendasi = 'disahkan_direktur';
            $kredensial->tanggal_sah_spk = Carbon::today();
            if ($fileSkPath) {
                $kredensial->file_sk_direktur = $fileSkPath;
                if (! $kredensial->file_rkk) {
                    $kredensial->file_rkk = $fileSkPath;
                }
            }
            $kredensial->is_spk_locked = true;
            $kredensial->is_rkk_locked = true;
            $kredensial->disahkan_oleh_sdm = auth()->id();
            $kredensial->save();

            // 3. Kunci Berkas STR & Repositori e-Arsip Kepegawaian, Perbarui Status Profil menjadi AKTIF_LEGAL
            $dokter->status_praktik = 'AKTIF_LEGAL';
            $dokter->is_arsip_locked = true;
            $dokter->is_str_locked = true;
            $dokter->arsip_locked_at = Carbon::now();
            $dokter->arsip_locked_by = auth()->id();
            $dokter->nomor_sk_direktur = $validated['nomor_sk_direktur'];
            $dokter->tanggal_sk_direktur = $validated['tanggal_sk_direktur'];
            if ($fileSkPath) {
                $dokter->file_sk_direktur = $fileSkPath;
            }
            $dokter->catatan_pengesahan_direksi = $validated['catatan_pengesahan_direksi']
                ?? 'Telah disahkan oleh Direktur RSUD dr. H. Moch. Ansari Saleh dan diarsipkan secara digital oleh Bagian SDM.';
            $dokter->save();

            // 4. Kunci Berkas SIP dan Daftarkan Tanggal Jatuh Tempo SIP ke Mesin Pelacakan Otomatis
            if ($sip) {
                $sip->is_sip_locked = true;
                $sip->arsip_locked_at = Carbon::now();
                $sip->status_verifikasi = 'disetujui';
                $sip->updateStatusKadaluarsa();
                $sip->save();
            }

            // 5. Rekam Jejak Audit Sistem (Audit Log)
            AuditLog::record(
                'PENGESAHAN_SK_DIREKTUR',
                'SDM & Direksi',
                "Menerbitkan SK Direktur No. {$validated['nomor_sk_direktur']} mengenai penetapan SPK dan lampiran RKK terbaru untuk dr. {$dokter->user->nama} masa berlaku 3 tahun."
            );

            AuditLog::record(
                'PENGARSIPAN_DIGITAL_E_ARSIP',
                'SDM e-Arsip',
                "Mengunci berkas STR, SIP, SPK, dan RKK dr. {$dokter->user->nama} ke dalam Repositori Digital Kepegawaian. Status profil diperbarui menjadi AKTIF_LEGAL."
            );

            // 6. Terbitkan Notifikasi Internal Sistem ke Dokter
            $tglBerakhirFormatted = Carbon::parse($validated['tanggal_berakhir_spk'])->translatedFormat('d F Y');
            $pesanNotif = "Selamat dr. {$dokter->user->nama}, Surat Keputusan (SK) Direktur No. {$validated['nomor_sk_direktur']} mengenai penetapan SPK dan lampiran RKK terbaru telah resmi disahkan. Status profil Anda kini AKTIF_LEGAL dengan hak praktik penuh di RSUD dr. H. Moch. Ansari Saleh (Masa berlaku SPK aktif 3 tahun s.d. {$tglBerakhirFormatted}). Berkas STR, SIP, SPK, dan RKK Anda telah terkunci aman dalam e-Arsip Kepegawaian.";

            Notifikasi::create([
                'id_user' => $dokter->user->id,
                'id_sip' => $sip?->id,
                'pesan' => $pesanNotif,
                'kanal' => 'sistem',
                'status_terkirim' => true,
            ]);

            // 7. Pengiriman Notifikasi WhatsApp Gateway ke Dokter
            if ($dokter->user->no_wa) {
                $pesanWa = "Yth. dr. {$dokter->user->nama},\n\n"
                    ."Pemberitahuan Resmi Kepegawaian RSUD dr. H. Moch. Ansari Saleh:\n"
                    ."Surat Keputusan (SK) Direktur No. {$validated['nomor_sk_direktur']} mengenai penetapan SPK dan lampiran RKK terbaru Anda telah RESMI DISAHKAN.\n\n"
                    ."Status Profil: *AKTIF_LEGAL (Hak Praktik Penuh)*\n"
                    ."Masa Berlaku SPK: 3 Tahun (s.d. {$tglBerakhirFormatted})\n"
                    ."e-Arsip: STR, SIP, SPK, dan RKK telah terkunci di repositori digital.\n\n"
                    .'Tanggal jatuh tempo SIP Anda telah resmi terdaftar dalam sistem pemantauan berkala rumah sakit. Terima kasih atas dedikasi pelayanan Anda.';

                $this->waService->kirimPesanTeks($dokter->user->no_wa, $pesanWa);
            }

            DB::commit();

            return redirect()->route('sdm.arsip.show', $dokter->id)
                ->with('sukses', "SK Direktur No. {$validated['nomor_sk_direktur']} berhasil diterbitkan! Berkas STR, SIP, SPK, dan RKK dr. {$dokter->user->nama} telah terkunci ke dalam e-Arsip dan status profil resmi menjadi AKTIF_LEGAL.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Gagal memproses pengesahan Direksi: '.$e->getMessage());
        }
    }

    /**
     * Halaman Detail Repositori Digital Kepegawaian (e-Arsip Terpadu Dokter)
     */
    public function show(int $dokterId): View
    {
        $dokter = Dokter::with([
            'user',
            'ksm',
            'dokumenSip' => fn ($q) => $q->orderByDesc('tanggal_kadaluarsa'),
            'kredensialKlinis' => fn ($q) => $q->orderByDesc('tanggal_berakhir_spk'),
            'arsipLockedBy',
            'verifikatorStr',
        ])->findOrFail($dokterId);

        $spk = $dokter->kredensialTerbaru ?: $dokter->spkTerbaru;
        $sip = $dokter->sipTerbaru;

        // Kelayakan & Integritas Dokumen 4 Pilar
        $integritas = [
            'str' => [
                'nama' => 'Surat Tanda Registrasi (STR)',
                'nomor' => $dokter->nomor_str ?: '-',
                'status' => $dokter->status_verifikasi_str === 'tervalidasi' ? 'Tervalidasi (SatuSehat)' : 'Belum Valid',
                'locked' => (bool) $dokter->is_str_locked,
                'file' => $dokter->file_str,
                'berlaku' => $dokter->status_str === 'seumur_hidup' ? 'Seumur Hidup' : ($dokter->tanggal_kadaluarsa_str ? $dokter->tanggal_kadaluarsa_str->format('d/m/Y') : '-'),
                'icon' => 'fa-certificate',
            ],
            'sip' => [
                'nama' => 'Surat Izin Praktik (SIP)',
                'nomor' => $sip?->nomor_sip ?: '-',
                'status' => $sip?->status_verifikasi === 'disetujui' ? 'Disetujui & Tervalidasi' : 'Belum Sah',
                'locked' => (bool) ($sip?->is_sip_locked || $dokter->is_arsip_locked),
                'file' => $sip?->file_sip,
                'berlaku' => $sip?->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-',
                'sisa_hari' => $sip?->hitungSisaHari() ?? 0,
                'dalam_pelacakan' => true,
                'icon' => 'fa-file-medical',
            ],
            'spk' => [
                'nama' => 'Surat Penugasan Klinis (SPK Direktur)',
                'nomor' => $spk?->nomor_sk_direktur ?: ($spk?->nomor_spk ?: '-'),
                'status' => $spk?->status_kredensial === 'aktif' ? 'Aktif Absah (3 Tahun)' : ucfirst($spk?->status_kredensial ?? '-'),
                'locked' => (bool) ($spk?->is_spk_locked || $dokter->is_arsip_locked),
                'file' => $spk?->file_sk_direktur ?: $spk?->file_rkk,
                'berlaku' => $spk?->tanggal_berakhir_spk ? $spk->tanggal_berakhir_spk->format('d/m/Y') : '-',
                'sisa_hari' => $spk?->hitungSisaHari() ?? 0,
                'icon' => 'fa-file-signature',
            ],
            'rkk' => [
                'nama' => 'Rincian Kewenangan Klinis (RKK)',
                'nomor' => 'Lampiran SK Direktur',
                'status' => is_array($spk?->daftar_rkk) ? count($spk->daftar_rkk).' Tindakan Medis Disetujui' : 'Terlampir dalam SK',
                'locked' => (bool) ($spk?->is_rkk_locked || $dokter->is_arsip_locked),
                'file' => $spk?->file_sk_direktur ?: $spk?->file_rkk,
                'mandiri' => $spk?->rkk_mandiri_count ?? 0,
                'supervisi' => $spk?->rkk_supervisi_count ?? 0,
                'icon' => 'fa-stethoscope',
            ],
        ];

        return view('sdm.pengesahan.show', compact('dokter', 'spk', 'sip', 'integritas'));
    }

    /**
     * Lembar Cetak Pengesahan Hak Praktik & e-Arsip Terpadu (Print-Ready Document)
     */
    public function cetak(int $dokterId): View
    {
        $dokter = Dokter::with([
            'user',
            'ksm',
            'sipTerbaru',
            'kredensialTerbaru',
            'arsipLockedBy',
        ])->findOrFail($dokterId);

        $spk = $dokter->kredensialTerbaru ?: $dokter->spkTerbaru;
        $sip = $dokter->sipTerbaru;

        return view('sdm.pengesahan.cetak', compact('dokter', 'spk', 'sip'));
    }
}
