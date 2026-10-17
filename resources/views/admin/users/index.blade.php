@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Hak Akses')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Manajemen Pengguna & Hak Akses</h4>
        <p class="text-secondary fs-sm mb-0">Kelola akun, hak akses role (Super Admin, Admin SDM, Verifikator Komite, Dokter), dan status aktifasi pengguna</p>
    </div>
    <div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-teal text-white btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-user-plus"></i>
            <span>Tambah Pengguna Baru</span>
        </a>
    </div>
</div>

<!-- Filter Toolbar -->
<div class="card shadow-xs mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari nama, username, email..." value="{{ request('cari') }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select form-select-sm">
                    <option value="">-- Semua Role --</option>
                    <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="admin_sdm" {{ request('role') == 'admin_sdm' ? 'selected' : '' }}>Admin SDM</option>
                    <option value="verifikator_komite" {{ request('role') == 'verifikator_komite' ? 'selected' : '' }}>Verifikator Komite</option>
                    <option value="dokter" {{ request('role') == 'dokter' ? 'selected' : '' }}>Dokter</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm border" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table Users -->
<div class="card shadow-xs">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th>Pengguna</th>
                    <th>Username</th>
                    <th>Email & WhatsApp</th>
                    <th>Role Akses</th>
                    <th>Status</th>
                    <th>Terdaftar</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar bg-teal text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-xs">
                                    {{ strtoupper(substr($u->nama, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $u->nama }}</div>
                                    @if($u->dokter)
                                        <small class="text-muted">NIP: {{ $u->dokter->nip_nrp }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><span class="font-monospace text-secondary fs-xs">{{ $u->username }}</span></td>
                        <td>
                            <div>{{ $u->email }}</div>
                            <small class="text-muted"><i class="fa-brands fa-whatsapp text-success me-1"></i>{{ $u->no_wa ?? '-' }}</small>
                        </td>
                        <td>
                            @php
                                $badgeClass = match($u->role) {
                                    'super_admin' => 'bg-danger text-white',
                                    'admin_sdm' => 'bg-primary text-white',
                                    'verifikator_komite' => 'bg-success text-white',
                                    'dokter' => 'bg-info text-dark',
                                    default => 'bg-secondary text-white'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $u->role_label }}</span>
                        </td>
                        <td>
                            @if($u->aktif)
                                <span class="badge bg-success-subtle text-success border border-success">Aktif</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger">Non-Aktif</span>
                            @endif
                        </td>
                        <td>{{ $u->created_at->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <form action="{{ route('admin.users.toggle', $u->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary" title="{{ $u->aktif ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                                        <i class="fa-solid {{ $u->aktif ? 'fa-toggle-on text-success' : 'fa-toggle-off text-danger' }}"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-outline-primary" title="Ubah Akun">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                @if($u->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus akun {{ $u->nama }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus Akun">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            Tidak ditemukan data pengguna yang cocok.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
