@extends('layouts.app')

@section('title', 'Audit Log & Aktivitas Sistem')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold text-dark mb-1">Audit Log & Rekam Jejak Sistem</h4>
    <p class="text-secondary fs-sm mb-0">Catatan kronologis seluruh interaksi pengguna, modifikasi data kredensial, verifikasi SIP, dan pengiriman notifikasi</p>
</div>

<!-- Filter Box -->
<div class="card shadow-xs mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.audit.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari deskripsi aktivitas, nama user, atau IP..." value="{{ request('cari') }}">
            </div>
            <div class="col-md-4">
                <select name="modul" class="form-select form-select-sm">
                    <option value="">-- Semua Modul --</option>
                    @foreach($modulList as $m)
                        <option value="{{ $m }}" {{ request('modul') == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('admin.audit.index') }}" class="btn btn-light btn-sm border" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table Audit Logs -->
<div class="card shadow-xs">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th>Waktu (WITA)</th>
                    <th>Pengguna</th>
                    <th>Aksi</th>
                    <th>Modul</th>
                    <th>Deskripsi Aktivitas</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditLogs as $log)
                    <tr>
                        <td class="text-nowrap text-secondary fs-xs">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $log->user->nama ?? 'Sistem / Anonim' }}</div>
                            <small class="text-muted">{{ $log->user->role_label ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border fs-xs">{{ $log->aksi }}</span>
                        </td>
                        <td>
                            <span class="badge bg-teal-subtle text-teal">{{ $log->modul }}</span>
                        </td>
                        <td>{{ $log->deskripsi }}</td>
                        <td><span class="font-monospace fs-xs text-muted">{{ $log->ip_address ?? '127.0.0.1' }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Tidak ditemukan log aktivitas sistem yang sesuai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($auditLogs->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $auditLogs->links() }}
        </div>
    @endif
</div>
@endsection
