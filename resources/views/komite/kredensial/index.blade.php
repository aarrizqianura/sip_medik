@extends('layouts.app')

@section('title', 'Kredensialing Dokter (SPK & RKK)')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Manajemen Kredensialing Dokter (SPK & RKK)</h4>
        <p class="text-secondary fs-sm mb-0">Pengelolaan Surat Penugasan Klinis Direktur dan Rincian Kewenangan Klinis 3-tahunan Subkomite Kredensial</p>
    </div>
    <div>
        <a href="{{ route('komite.kredensial.create') }}" class="btn btn-teal text-white btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Terbitkan SPK / RKK Baru</span>
        </a>
    </div>
</div>

<!-- Filter Toolbar -->
<div class="card shadow-xs mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('komite.kredensial.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari nama dokter atau nomor SPK..." value="{{ request('cari') }}">
            </div>
            <div class="col-md-4">
                <select name="status_kredensial" class="form-select form-select-sm">
                    <option value="">-- Semua Status Kredensial --</option>
                    <option value="aktif" {{ request('status_kredensial') == 'aktif' ? 'selected' : '' }}>Aktif (Kewenangan Berlaku)</option>
                    <option value="evaluasi" {{ request('status_kredensial') == 'evaluasi' ? 'selected' : '' }}>Evaluasi (≤ 90 Hari)</option>
                    <option value="kedaluwarsa" {{ request('status_kredensial') == 'kedaluwarsa' ? 'selected' : '' }}>Kedaluwarsa (Overdue)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('komite.kredensial.index') }}" class="btn btn-light btn-sm border" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table SPK & RKK -->
<div class="card shadow-xs">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th>Dokter</th>
                    <th>KSM</th>
                    <th>No. SK Penugasan (SPK)</th>
                    <th>Tgl Terbit</th>
                    <th>Jatuh Tempo Rekredensial</th>
                    <th>Status Kewenangan</th>
                    <th>Berkas RKK</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kredensialList as $kred)
                    @php $sisa = $kred->hitungSisaHari(); @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold text-dark">{{ $kred->dokter->user->nama ?? '-' }}</div>
                            <small class="text-muted">NIP: {{ $kred->dokter->nip_nrp ?? '-' }}</small>
                        </td>
                        <td><span class="badge bg-teal-subtle text-teal">{{ $kred->dokter->ksm->kode_ksm ?? '-' }}</span></td>
                        <td><span class="font-monospace fs-xs">{{ $kred->nomor_spk }}</span></td>
                        <td>{{ $kred->tanggal_terbit_spk ? $kred->tanggal_terbit_spk->format('d/m/Y') : '-' }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $kred->tanggal_berakhir_spk ? $kred->tanggal_berakhir_spk->format('d/m/Y') : '-' }}</div>
                            <small class="text-muted">{{ $sisa < 0 ? abs($sisa) . ' hr lalu' : 'sisa ' . $sisa . ' hr' }}</small>
                        </td>
                        <td>
                            <span class="badge {{ $kred->status_kredensial === 'aktif' ? 'bg-success' : ($kred->status_kredensial === 'evaluasi' ? 'bg-warning text-dark' : 'bg-danger text-white') }}">
                                {{ $kred->status_label }}
                            </span>
                        </td>
                        <td>
                            @if($kred->file_rkk)
                                <a href="{{ asset('storage/' . $kred->file_rkk) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Lihat Berkas RKK">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                            @else
                                <span class="text-muted fs-xs">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('komite.kredensial.edit', $kred->id) }}" class="btn btn-outline-primary" title="Ubah SPK/RKK">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('komite.kredensial.destroy', $kred->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus instrumen SPK ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            Tidak ditemukan data kredensialing klinis.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($kredensialList->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $kredensialList->links() }}
        </div>
    @endif
</div>
@endsection
