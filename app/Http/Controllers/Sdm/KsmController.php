<?php

namespace App\Http\Controllers\Sdm;

use App\Http\Controllers\Controller;
use App\Models\Ksm;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KsmController extends Controller
{
    public function index(Request $request)
    {
        $query = Ksm::withCount('dokters');

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('kode_ksm', 'like', "%{$cari}%")
                  ->orWhere('nama_ksm', 'like', "%{$cari}%");
            });
        }

        $ksmList = $query->orderBy('nama_ksm')->paginate(15)->withQueryString();

        return view('sdm.ksm.index', compact('ksmList'));
    }

    public function create()
    {
        return view('sdm.ksm.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_ksm' => 'required|string|max:20|unique:ksm,kode_ksm',
            'nama_ksm' => 'required|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $ksm = Ksm::create($validated);

        AuditLog::record('CREATE_KSM', 'Master KSM', "Menambahkan KSM baru: {$ksm->nama_ksm} ({$ksm->kode_ksm})");

        return redirect()->route('sdm.ksm.index')
            ->with('sukses', "Data KSM {$ksm->nama_ksm} berhasil ditambahkan.");
    }

    public function edit(Ksm $ksm)
    {
        return view('sdm.ksm.edit', compact('ksm'));
    }

    public function update(Request $request, Ksm $ksm)
    {
        $validated = $request->validate([
            'kode_ksm' => ['required', 'string', 'max:20', Rule::unique('ksm', 'kode_ksm')->ignore($ksm->id)],
            'nama_ksm' => 'required|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $ksm->update($validated);

        AuditLog::record('UPDATE_KSM', 'Master KSM', "Memperbarui data KSM: {$ksm->nama_ksm}");

        return redirect()->route('sdm.ksm.index')
            ->with('sukses', "Data KSM {$ksm->nama_ksm} berhasil diperbarui.");
    }

    public function destroy(Ksm $ksm)
    {
        if ($ksm->dokters()->count() > 0) {
            return back()->with('error', "KSM {$ksm->nama_ksm} tidak dapat dihapus karena masih menaungi {$ksm->dokters()->count()} dokter.");
        }

        $nama = $ksm->nama_ksm;
        $ksm->delete();

        AuditLog::record('DELETE_KSM', 'Master KSM', "Menghapus KSM: {$nama}");

        return redirect()->route('sdm.ksm.index')
            ->with('sukses', "KSM {$nama} berhasil dihapus.");
    }
}
