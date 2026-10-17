@extends('layouts.app')

@section('title', 'Kelompok Staf Medis (KSM)')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Master Kelompok Staf Medis (KSM)</h4>
        <p class="text-secondary fs-sm mb-0">Pengorganisasian kelompok dokter spesialis dan umum di lingkungan RSUD dr. H. Moch. Ansari Saleh</p>
    </div>
    <div>
        <a href="{{ route('sdm.ksm.create') }}" class="btn btn-teal text-white btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah KSM Baru</span>
        </a>
    </div>
</div>

<!-- Search Bar -->
<div class="card shadow-xs mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('sdm.ksm.index') }}" class="row g-2 align-items-center">
            <div class="col-md-9">
                <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari nama KSM atau kode KSM..." value="{{ request('cari') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Cari
                </button>
                <a href="{{ route('sdm.ksm.index') }}" class="btn btn-light btn-sm border" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table KSM -->
<div class="card shadow-xs">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th>Kode KSM</th>
                    <th>Nama Kelompok Staf Medis</th>
                    <th>Keterangan Pelayanan</th>
                    <th>Jumlah Staf Medis</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ksmList as $ksm)
                    <tr>
                        <td><span class="badge bg-teal-subtle text-teal font-monospace">{{ $ksm->kode_ksm }}</span></td>
                        <td class="fw-semibold text-dark">{{ $ksm->nama_ksm }}</td>
                        <td class="text-secondary fs-xs">{{ $ksm->keterangan ?? '-' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $ksm->dokters_count }} Dokter</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('sdm.ksm.edit', $ksm->id) }}" class="btn btn-outline-primary" title="Ubah KSM">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('sdm.ksm.destroy', $ksm->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus KSM {{ $ksm->nama_ksm }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Hapus KSM">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            Belum ada data Kelompok Staf Medis.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($ksmList->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $ksmList->links() }}
        </div>
    @endif
</div>
@endsection
