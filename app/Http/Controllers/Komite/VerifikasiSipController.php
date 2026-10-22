<?php

namespace App\Http\Controllers\Komite;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DokumenSip;
use App\Models\KredensialKlinis;
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
        $sip = DokumenSip::with(['dokter.user', 'dokter.ksm', 'dokter.kredensialTerbaru', 'dokter.spkTerbaru', 'verifikator'])->findOrFail($id);
        $dokter = $sip->dokter;
        $evaluasiRekredensial = $dokter->evaluasiStatusRekredensial((bool) $sip->permohonan_kewenangan_baru);

        return view('verifikasi.review', compact('sip', 'evaluasiRekredensial'));
    }

    public function proses(Request $request, $id)
    {
        $sip = DokumenSip::with(['dokter.user', 'dokter.ksm', 'dokter.kredensialTerbaru', 'dokter.spkTerbaru'])->findOrFail($id);

        $validated = $request->validate([
            'status_verifikasi' => 'required|in:disetujui,ditolak',
            'catatan_verifikasi' => 'nullable|string|max:1000',
            'checklist_keabsahan_fasyankes' => 'nullable|boolean',
            'checklist_masa_berlaku' => 'nullable|boolean',
            'checklist_keterbacaan_pindaian' => 'nullable|boolean',
            'permohonan_kewenangan_baru' => 'nullable|boolean',
            'catatan_kewenangan_baru' => 'nullable|string|max:1000',
        ]);

        if ($validated['status_verifikasi'] === 'ditolak' && empty($validated['catatan_verifikasi'])) {
            return back()->withErrors(['catatan_verifikasi' => 'Catatan evaluasi telaah klinis/alasan penolakan wajib diisi jika berkas ditolak (misal: pindaian buram, masa berlaku salah, fasyankes tidak cocok).']);
        }

        $dokter = $sip->dokter;
        $dokterUser = $dokter->user;

        $sip->status_verifikasi = $validated['status_verifikasi'];
        $sip->catatan_verifikasi = $validated['catatan_verifikasi'];
        $sip->checklist_keabsahan_fasyankes = $request->boolean('checklist_keabsahan_fasyankes');
        $sip->checklist_masa_berlaku = $request->boolean('checklist_masa_berlaku');
        $sip->checklist_keterbacaan_pindaian = $request->boolean('checklist_keterbacaan_pindaian');
        $sip->permohonan_kewenangan_baru = $request->boolean('permohonan_kewenangan_baru');
        $sip->catatan_kewenangan_baru = $request->catatan_kewenangan_baru;
        $sip->verified_by = auth()->id();
        $sip->verified_at = Carbon::now();

        // JIKA DITOLAK
        if ($validated['status_verifikasi'] === 'ditolak') {
            $sip->save();

            $pesanNotif = "Halo dr. {$dokterUser->nama}, berkas pengajuan SIP No. {$sip->nomor_sip} Anda DITOLAK oleh Subkomite Kredensial dengan catatan evaluasi: {$validated['catatan_verifikasi']}. Silakan perbaiki dan unggah ulang berkas fisik pindaian yang absah.";

            Notifikasi::create([
                'id_user' => $dokterUser->id,
                'id_sip' => $sip->id,
                'pesan' => $pesanNotif,
                'kanal' => 'sistem',
                'status_terkirim' => true,
            ]);

            AuditLog::record(
                'TOLAK_SIP',
                'Komite Medik',
                "Menolak berkas SIP No. {$sip->nomor_sip} dokter {$dokterUser->nama}. Catatan evaluasi: {$validated['catatan_verifikasi']}"
            );

            return redirect()->route('komite.verifikasi.index')
                ->with('error', "Pemeriksaan selesai: Berkas SIP No. {$sip->nomor_sip} ({$dokterUser->nama}) DITOLAK dengan catatan evaluasi tersimpan.");
        }

        // JIKA DISETUJUI & DINYATAKAN VALID
        $sip->updateStatusKadaluarsa();
        $sip->save();

        // 1. Notifikasi Pengesahan SIP ke Dokter
        Notifikasi::create([
            'id_user' => $dokterUser->id,
            'id_sip' => $sip->id,
            'pesan' => "Halo dr. {$dokterUser->nama}, berkas SIP No. {$sip->nomor_sip} Anda telah DISETUJUI dan dinyatakan sah oleh Subkomite Kredensial Komite Medik.",
            'kanal' => 'sistem',
            'status_terkirim' => true,
        ]);

        AuditLog::record(
            'VERIFIKASI_SIP',
            'Komite Medik',
            "Mengesahkan berkas SIP No. {$sip->nomor_sip} dokter {$dokterUser->nama} (Fasyankes & Masa Berlaku valid)."
        );

        // 2. PEMICU ALUR REKREDENSIALING (Re-credentialing Workflow Trigger)
        // Mengecek status instrumen SPK & RKK dokter (siklus 3 tahun atau permohonan baru)
        $evaluasi = $dokter->evaluasiStatusRekredensial((bool) $sip->permohonan_kewenangan_baru);
        $rekredensialId = null;

        if ($evaluasi['perlu']) {
            // Cek apakah sudah ada rekredensial aktif berstatus proses_rekredensial
            $kredensial = KredensialKlinis::where('id_dokter', $dokter->id)
                ->where('status_kredensial', 'proses_rekredensial')
                ->latest('id')
                ->first();

            $spkTerakhir = $dokter->kredensialTerbaru ?: $dokter->spkTerbaru;

            if (! $kredensial) {
                $kredensial = KredensialKlinis::create([
                    'id_dokter' => $dokter->id,
                    'nomor_spk' => $spkTerakhir?->nomor_spk ?: ('SPK-REKRED/'.date('Y').'/'.$dokter->id),
                    'tanggal_terbit_spk' => $spkTerakhir?->tanggal_terbit_spk,
                    'tanggal_berakhir_spk' => $spkTerakhir?->tanggal_berakhir_spk,
                    'status_kredensial' => 'proses_rekredensial',
                    'tahap_rekredensial' => 'menunggu_sidang',
                    'jenis_pengajuan' => $evaluasi['jenis'],
                    'alasan_rekredensial' => $evaluasi['alasan'],
                ]);
            } else {
                $kredensial->update([
                    'jenis_pengajuan' => $evaluasi['jenis'],
                    'alasan_rekredensial' => $evaluasi['alasan'],
                    'tahap_rekredensial' => 'menunggu_sidang',
                ]);
            }

            $rekredensialId = $kredensial->id;

            // Notifikasi ke Dokter terkait Inisiasi Rekredensial
            Notifikasi::create([
                'id_user' => $dokterUser->id,
                'id_sip' => $sip->id,
                'pesan' => "SIP Anda sah. Sistem secara otomatis MEMICU STATUS PROSES REKREDENSIAL: {$evaluasi['alasan']}. Mohon siapkan logbook tindakan klinis dan portofolio kompetensi untuk Sidang Kredensial bersama Mitra Bestari.",
                'kanal' => 'sistem',
                'status_terkirim' => true,
            ]);

            // Notifikasi ke Tim Komite Medik / Verifikator
            Notifikasi::create([
                'id_user' => auth()->id(),
                'id_sip' => $sip->id,
                'pesan' => "Alur Rekredensialing Dipicu: Berkas SIP dr. {$dokterUser->nama} sah. Status instrumen SPK/RKK dialihkan ke 'proses_rekredensial' ({$evaluasi['alasan']}). Jadwalkan sidang kredensial segera.",
                'kanal' => 'sistem',
                'status_terkirim' => true,
            ]);

            AuditLog::record(
                'PEMICU_REKREDENSIAL',
                'Komite Medik',
                "Sistem secara otomatis memicu status 'proses_rekredensial' untuk dr. {$dokterUser->nama}. Alasan: {$evaluasi['alasan']}"
            );

            return redirect()->route('komite.kredensial.sidang', $rekredensialId)
                ->with('sukses', "Pengesahan berkas SIP No. {$sip->nomor_sip} ({$dokterUser->nama}) berhasil! Sistem secara otomatis mendeteksi perlunya evaluasi SPK/RKK dan memicu status 'proses_rekredensial'. Silakan lanjutkan ke Sidang Kredensial & Pengisian RKK bersama Mitra Bestari di bawah ini.");
        }

        return redirect()->route('komite.verifikasi.index')
            ->with('sukses', "Pengesahan berkas SIP No. {$sip->nomor_sip} ({$dokterUser->nama}) berhasil disimpan. Instrumen SPK & RKK dokter masih aktif dalam siklus 3 tahun.");
    }
}
