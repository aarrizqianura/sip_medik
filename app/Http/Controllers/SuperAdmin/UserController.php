<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('dokter');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('aktif', $request->status === '1');
        }

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('nama', 'like', "%{$cari}%")
                  ->orWhere('username', 'like', "%{$cari}%")
                  ->orWhere('email', 'like', "%{$cari}%");
            });
        }

        $users = $query->orderBy('nama')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'no_wa' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:super_admin,admin_sdm,verifikator_komite,dokter',
            'aktif' => 'required|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        AuditLog::record('CREATE_USER', 'Manajemen User', "Membuat akun pengguna baru: {$user->nama} ({$user->username}) dengan peran {$user->role}");

        return redirect()->route('admin.users.index')
            ->with('sukses', "Pengguna {$user->nama} berhasil didaftarkan.");
    }

    public function show(User $user)
    {
        $user->load(['dokter.ksm', 'dokter.dokumenSip', 'notifikasi', 'auditLogs']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'no_wa' => 'required|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:super_admin,admin_sdm,verifikator_komite,dokter',
            'aktif' => 'required|boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        AuditLog::record('UPDATE_USER', 'Manajemen User', "Memperbarui akun pengguna: {$user->nama} ({$user->username})");

        return redirect()->route('admin.users.index')
            ->with('sukses', "Data akun {$user->nama} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $nama = $user->nama;
        $user->delete();

        AuditLog::record('DELETE_USER', 'Manajemen User', "Menghapus akun pengguna: {$nama}");

        return redirect()->route('admin.users.index')
            ->with('sukses', "Pengguna {$nama} telah dihapus dari sistem.");
    }

    public function toggleAktif(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat mengubah status akun Anda sendiri.');
        }

        $user->aktif = !$user->aktif;
        $user->save();

        $statusTeks = $user->aktif ? 'diaktifkan' : 'dinonaktifkan';
        AuditLog::record('TOGGLE_STATUS_USER', 'Manajemen User', "Akun {$user->nama} telah {$statusTeks}.");

        return back()->with('sukses', "Akun {$user->nama} berhasil {$statusTeks}.");
    }
}
