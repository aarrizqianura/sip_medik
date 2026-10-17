<?php

namespace App\Http\Controllers\Komite;

use App\Http\Controllers\Controller;
use App\Models\KredensialKlinis;
use App\Models\Dokter;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KredensialController extends Controller
{
    public function index(Request $request)
    {
        $query = KredensialKlinis::with(['dokter.user', 'dokter.ksm']);

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
                  ->orWhereHas('dokter.user', function ($qu) use ($cari) {
                      $qu->where('nama', 'like', "%{$cari}%");
                  });
            });
        }

        $kredensialList = $query->orderBy('tanggal_berakhir_spk')->paginate(15)->withQueryString();

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
            $fileName = 'rkk_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('rkk', $fileName, 'public');
            $validated['file_rkk'] = 'rkk/' . $fileName;
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
            $fileName = 'rkk_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('rkk', $fileName, 'public');
            $validated['file_rkk'] = 'rkk/' . $fileName;
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
}
