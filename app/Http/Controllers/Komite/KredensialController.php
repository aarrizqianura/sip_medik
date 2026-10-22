<?php

namespace App\Http\Controllers\Komite;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Dokter;
use App\Models\KredensialKlinis;
use App\Models\Notifikasi;
use App\Services\RkkTemplateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KredensialController extends Controller
{
    public function index(Request $request)
    {
        $query = KredensialKlinis::with(['dokter.user', 'dokter.ksm', 'dokter.sipTerbaru']);

        if ($request->filled('status_kredensial')) {
            $query->where('status_kredensial', $request->status_kredensial);
        }

        if ($request->filled('id_ksm')) {
            $idKsm = $request->id_ksm;
            $query->whereHas('dokter', function ($q) use ($idKsm) {
                $q->where('id_ksm', $idKsm);
            });
        }

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('nomor_spk', 'like', "%{$cari}%")
                    ->orWhere('nomor_surat_rekomendasi', 'like', "%{$cari}%")
                    ->orWhereHas('dokter.user', function ($qu) use ($cari) {
                        $qu->where('nama', 'like', "%{$cari}%");
                    });
            });
        }

        // Urutkan: proses_rekredensial dan sidang pertama kali, lalu tanggal berakhir
        $kredensialList = $query->orderByRaw("
            CASE 
                WHEN status_kredensial = 'proses_rekredensial' THEN 1
                WHEN status_kredensial = 'sidang_komite' THEN 2
                WHEN status_kredensial = 'rekomendasi_terbit' THEN 3
                WHEN status_kredensial = 'kedaluwarsa' THEN 4
                WHEN status_kredensial = 'evaluasi' THEN 5
                ELSE 6
            END
        ")->latest('id')->paginate(15)->withQueryString();

        return view('komite.kredensial.index', compact('kredensialList'));
    }

    public function create()
    {
        $dokterList = Dokter::with('user', 'ksm')->get();

        return view('komite.kredensial.create', compact('dokterList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_dokter' => 'required|exists:dokters,id',
            'nomor_spk' => 'required|string|max:100',
            'tanggal_terbit_spk' => 'required|date',
            'tanggal_berakhir_spk' => 'required|date|after:tanggal_terbit_spk',
            'file_rkk' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('file_rkk')) {
            $file = $request->file('file_rkk');
            $fileName = 'rkk_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->storeAs('rkk', $fileName, 'public');
            $validated['file_rkk'] = 'rkk/'.$fileName;
        }

        $kredensial = new KredensialKlinis($validated);
        $kredensial->updateStatusKredensial();
        $kredensial->save();

        $dokter = Dokter::with('user')->find($validated['id_dokter']);
        AuditLog::record('CREATE_KREDENSIAL', 'Komite Medik', "Menerbitkan SPK No. {$kredensial->nomor_spk} untuk dokter {$dokter->user->nama}");

        return redirect()->route('komite.kredensial.index')
            ->with('sukses', "Data SPK & RKK untuk dr. {$dokter->user->nama} berhasil dicatat.");
    }

    public function edit(KredensialKlinis $kredensial)
    {
        $kredensial->load('dokter.user');
        $dokterList = Dokter::with('user')->get();

        return view('komite.kredensial.edit', compact('kredensial', 'dokterList'));
    }

    public function update(Request $request, KredensialKlinis $kredensial)
    {
        $validated = $request->validate([
            'nomor_spk' => 'required|string|max:100',
            'tanggal_terbit_spk' => 'required|date',
            'tanggal_berakhir_spk' => 'required|date|after:tanggal_terbit_spk',
            'file_rkk' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('file_rkk')) {
            if ($kredensial->file_rkk && Storage::disk('public')->exists($kredensial->file_rkk)) {
                Storage::disk('public')->delete($kredensial->file_rkk);
            }
            $file = $request->file('file_rkk');
            $fileName = 'rkk_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->storeAs('rkk', $fileName, 'public');
            $validated['file_rkk'] = 'rkk/'.$fileName;
        }

        $kredensial->fill($validated);
        $kredensial->updateStatusKredensial();
        $kredensial->save();

        AuditLog::record('UPDATE_KREDENSIAL', 'Komite Medik', "Memperbarui instrumen SPK No. {$kredensial->nomor_spk}");

        return redirect()->route('komite.kredensial.index')
            ->with('sukses', "Data SPK No. {$kredensial->nomor_spk} berhasil diperbarui.");
    }

    public function destroy(KredensialKlinis $kredensial)
    {
        $noSpk = $kredensial->nomor_spk;
        if ($kredensial->file_rkk && Storage::disk('public')->exists($kredensial->file_rkk)) {
            Storage::disk('public')->delete($kredensial->file_rkk);
        }
        $kredensial->delete();

        AuditLog::record('DELETE_KREDENSIAL', 'Komite Medik', "Menghapus SPK No. {$noSpk}");

        return redirect()->route('komite.kredensial.index')
            ->with('sukses', "Data SPK No. {$noSpk} berhasil dihapus.");
    }

    /**
     * Tahap 3: Halaman Sidang Kredensial & Pengisian Formulir Digital RKK
     */
    public function sidang($id)
    {
        $kredensial = KredensialKlinis::with(['dokter.user', 'dokter.ksm', 'dokter.sipTerbaru'])->findOrFail($id);
        $dokter = $kredensial->dokter;

        // Jika rincian RKK belum pernah diisi, gunakan template default berdasarkan KSM dokter
        $rkkItems = $kredensial->daftar_rkk;
        if (empty($rkkItems) || ! is_array($rkkItems)) {
            $rkkItems = RkkTemplateService::getTemplateByKsm($dokter->ksm?->kode_ksm);
        }

        return view('komite.kredensial.sidang', compact('kredensial', 'dokter', 'rkkItems'));
    }

    /**
     * Tahap 3: Simpan Hasil Sidang Kredensial & Form Digital RKK
     */
    public function simpanSidang(Request $request, $id)
    {
        $kredensial = KredensialKlinis::with(['dokter.user', 'dokter.ksm'])->findOrFail($id);

        $validated = $request->validate([
            'tanggal_sidang' => 'required|date',
            'nama_mitra_bestari' => 'required|string|max:255',
            'evaluasi_logbook' => 'required|string',
            'evaluasi_portofolio' => 'required|string',
            'catatan_mitra_bestari' => 'nullable|string',
            'rkk' => 'required|array|min:1',
            'rkk.*.kode' => 'required|string',
            'rkk.*.nama' => 'required|string',
            'rkk.*.kategori' => 'nullable|string',
            'rkk.*.status' => 'required|in:mandiri,supervisi,tidak_disetujui',
            'rkk.*.catatan' => 'nullable|string',
            'aksi' => 'nullable|string',
        ]);

        $daftarRkk = [];
        foreach ($validated['rkk'] as $item) {
            $daftarRkk[] = [
                'kode' => $item['kode'],
                'nama' => $item['nama'],
                'kategori' => $item['kategori'] ?? 'Kewenangan Klinis',
                'status' => $item['status'],
                'catatan' => $item['catatan'] ?? '',
            ];
        }

        $kredensial->tanggal_sidang = $validated['tanggal_sidang'];
        $kredensial->nama_mitra_bestari = $validated['nama_mitra_bestari'];
        $kredensial->evaluasi_logbook = $validated['evaluasi_logbook'];
        $kredensial->evaluasi_portofolio = $validated['evaluasi_portofolio'];
        $kredensial->catatan_mitra_bestari = $validated['catatan_mitra_bestari'];
        $kredensial->daftar_rkk = $daftarRkk;
        $kredensial->tahap_rekredensial = 'sidang_selesai';
        $kredensial->status_kredensial = 'sidang_komite';

        // Jika langsung pilih aksi terbitkan rekomendasi
        if ($request->input('aksi') === 'terbitkan_rekomendasi') {
            $nomorSurat = '445/'.rand(100, 999).'/KOM-MED/RSAS/'.Carbon::now()->format('m/Y');
            $kredensial->nomor_surat_rekomendasi = $nomorSurat;
            $kredensial->tanggal_rekomendasi = Carbon::today();
            $kredensial->ketua_komite_medik = 'dr. H. Hendra Sanjaya, Sp.B, FInaCS (Ketua Komite Medik)';
            $kredensial->catatan_rekomendasi = 'Rekomendasi RKK disetujui penuh oleh Tim Mitra Bestari dan Subkomite Kredensial berdasarkan hasil evaluasi logbook dan portofolio klinis.';
            $kredensial->status_rekomendasi = 'diterbitkan';
            $kredensial->status_kredensial = 'rekomendasi_terbit';
            $kredensial->tahap_rekredensial = 'rekomendasi_terbit';
        }

        $kredensial->save();

        $dokterUser = $kredensial->dokter->user;

        // Notifikasi ke dokter
        $pesanNotif = "Sidang Kredensial telah selesai dievaluasi oleh Subkomite Kredensial & Mitra Bestari ({$validated['nama_mitra_bestari']}). RKK Anda telah ditetapkan (".$kredensial->rkk_mandiri_count.' Mandiri, '.$kredensial->rkk_supervisi_count.' Supervisi).';
        if ($kredensial->status_rekomendasi === 'diterbitkan') {
            $pesanNotif .= " Surat Rekomendasi Kewenangan Klinis Digital No. {$kredensial->nomor_surat_rekomendasi} telah diterbitkan kepada Direktur.";
        }

        Notifikasi::create([
            'id_user' => $dokterUser->id,
            'id_sip' => $kredensial->dokter->sipTerbaru?->id,
            'pesan' => $pesanNotif,
            'kanal' => 'sistem',
            'status_terkirim' => true,
        ]);

        AuditLog::record(
            'SIDANG_KREDENSIAL',
            'Komite Medik',
            "Menyelesaikan Sidang Kredensial dokter {$dokterUser->nama} bersama Mitra Bestari {$validated['nama_mitra_bestari']}. Penetapan RKK: {$kredensial->rkk_mandiri_count} Mandiri, {$kredensial->rkk_supervisi_count} Supervisi."
        );

        if ($kredensial->status_rekomendasi === 'diterbitkan') {
            return redirect()->route('komite.kredensial.surat_rekomendasi', $kredensial->id)
                ->with('sukses', "Sidang Kredensial berhasil disimpan dan Surat Rekomendasi Kewenangan Klinis Digital No. {$kredensial->nomor_surat_rekomendasi} resmi diterbitkan kepada Direktur!");
        }

        return redirect()->route('komite.kredensial.sidang', $kredensial->id)
            ->with('sukses', "Hasil Sidang Kredensial & penetapan daftar RKK dr. {$dokterUser->nama} berhasil disimpan.");
    }

    /**
     * Tahap 3: Menerbitkan Surat Rekomendasi Kewenangan Klinis Digital kepada Direktur
     */
    public function terbitkanRekomendasi(Request $request, $id)
    {
        $kredensial = KredensialKlinis::with(['dokter.user', 'dokter.ksm'])->findOrFail($id);

        $validated = $request->validate([
            'nomor_surat_rekomendasi' => 'required|string|max:150',
            'tanggal_rekomendasi' => 'required|date',
            'ketua_komite_medik' => 'required|string|max:150',
            'catatan_rekomendasi' => 'nullable|string',
        ]);

        $kredensial->nomor_surat_rekomendasi = $validated['nomor_surat_rekomendasi'];
        $kredensial->tanggal_rekomendasi = $validated['tanggal_rekomendasi'];
        $kredensial->ketua_komite_medik = $validated['ketua_komite_medik'];
        $kredensial->catatan_rekomendasi = $validated['catatan_rekomendasi'] ?? 'Berdasarkan telaah Subkomite Kredensial dan Tim Mitra Bestari, dokter yang bersangkutan direkomendasikan untuk diberikan Kewenangan Klinis sesuai rincian terlampir.';
        $kredensial->status_rekomendasi = 'diterbitkan';
        $kredensial->status_kredensial = 'rekomendasi_terbit';
        $kredensial->tahap_rekredensial = 'rekomendasi_terbit';
        $kredensial->save();

        $dokterUser = $kredensial->dokter->user;

        Notifikasi::create([
            'id_user' => $dokterUser->id,
            'id_sip' => $kredensial->dokter->sipTerbaru?->id,
            'pesan' => "Komite Medik telah menerbitkan Surat Rekomendasi Kewenangan Klinis Digital No. {$kredensial->nomor_surat_rekomendasi} kepada Direktur RSUD dr. H. Moch. Ansari Saleh.",
            'kanal' => 'sistem',
            'status_terkirim' => true,
        ]);

        AuditLog::record(
            'TERBIT_REKOMENDASI',
            'Komite Medik',
            "Menerbitkan Surat Rekomendasi Kewenangan Klinis No. {$kredensial->nomor_surat_rekomendasi} untuk dr. {$dokterUser->nama} kepada Direktur RSUD Ansari Saleh"
        );

        return redirect()->route('komite.kredensial.surat_rekomendasi', $kredensial->id)
            ->with('sukses', "Surat Rekomendasi Kewenangan Klinis Digital No. {$kredensial->nomor_surat_rekomendasi} berhasil diterbitkan dan siap diteruskan kepada Direktur.");
    }

    /**
     * Tahap 3: Halaman Lihat / Cetak Surat Rekomendasi Digital Resmi Komite Medik
     */
    public function suratRekomendasi($id)
    {
        $kredensial = KredensialKlinis::with(['dokter.user', 'dokter.ksm', 'dokter.sipTerbaru'])->findOrFail($id);
        $dokter = $kredensial->dokter;

        return view('komite.kredensial.surat_rekomendasi', compact('kredensial', 'dokter'));
    }

    /**
     * Pengesahan & Penerbitan SPK Baru oleh Direktur (Siklus 3 Tahun Baru)
     */
    public function sahkanSpk(Request $request, $id)
    {
        $kredensial = KredensialKlinis::with(['dokter.user', 'dokter.ksm'])->findOrFail($id);

        $validated = $request->validate([
            'nomor_spk' => 'required|string|max:100',
            'tanggal_terbit_spk' => 'required|date',
            'tanggal_berakhir_spk' => 'required|date|after:tanggal_terbit_spk',
        ]);

        $kredensial->nomor_spk = $validated['nomor_spk'];
        $kredensial->tanggal_terbit_spk = $validated['tanggal_terbit_spk'];
        $kredensial->tanggal_berakhir_spk = $validated['tanggal_berakhir_spk'];
        $kredensial->status_kredensial = 'aktif';
        $kredensial->tahap_rekredensial = 'spk_diterbitkan';
        $kredensial->status_rekomendasi = 'disahkan_direktur';
        $kredensial->tanggal_sah_spk = Carbon::today();
        $kredensial->save();

        $dokterUser = $kredensial->dokter->user;

        Notifikasi::create([
            'id_user' => $dokterUser->id,
            'id_sip' => $kredensial->dokter->sipTerbaru?->id,
            'pesan' => "Selamat dr. {$dokterUser->nama}, Surat Penugasan Klinis (SPK) & RKK baru No. {$kredensial->nomor_spk} telah resmi disahkan Direktur untuk periode 3 tahun ke depan (berlaku s.d. ".Carbon::parse($kredensial->tanggal_berakhir_spk)->format('d F Y').').',
            'kanal' => 'sistem',
            'status_terkirim' => true,
        ]);

        AuditLog::record(
            'SAHKAN_SPK',
            'Direktur RSUD',
            "Mengesahkan Surat Penugasan Klinis (SPK) baru No. {$kredensial->nomor_spk} untuk dr. {$dokterUser->nama} masa berlaku 3 tahun."
        );

        return redirect()->route('komite.kredensial.surat_rekomendasi', $kredensial->id)
            ->with('sukses', "Surat Penugasan Klinis (SPK) No. {$kredensial->nomor_spk} resmi disahkan untuk masa berlaku 3 tahun!");
    }
}
