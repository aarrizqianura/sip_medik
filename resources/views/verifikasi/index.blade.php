@extends('layouts.app')

@section('title', 'Verifikasi Berkas SIP Dokter')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Verifikasi & Pengesahan Berkas SIP</h4>
        <p class="text-secondary fs-sm mb-0">Antrean kerja bersih Subkomite Kredensial: memvalidasi pindaian lembar perizinan DPMPTSP</p>
    </div>
</div>

<!-- Navigation Tabs: Menunggu Telaah vs Arsip Telaah -->
<div class="card shadow-xs border-0 mb-3">
    <div class="card-header bg-white p-0 border-bottom">
        <ul class="nav nav-pills p-2 gap-1">
            <li class="nav-item">
                <a class="nav-link py-2 px-3 fs-sm fw-semibold {{ $tab === 'menunggu' ? 'active bg-teal' : 'text-dark' }}" href="{{ route('komite.verifikasi.index', ['tab' => 'menunggu']) }}">
                    <i class="fa-solid fa-hourglass-half me-1"></i> Menunggu Telaah
                    <span class="badge {{ $tab === 'menunggu' ? 'bg-white text-teal' : 'bg-warning text-dark' }} ms-1">{{ $countMenunggu }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 px-3 fs-sm fw-semibold {{ $tab === 'arsip' ? 'active bg-teal' : 'text-dark' }}" href="{{ route('komite.verifikasi.index', ['tab' => 'arsip']) }}">
                    <i class="fa-solid fa-box-archive me-1"></i> Arsip Telaah
                    <span class="badge {{ $tab === 'arsip' ? 'bg-white text-teal' : 'bg-light text-secondary border' }} ms-1">{{ $countArsip }}</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Filter Toolbar -->
    <div class="card-body p-3 bg-light border-bottom">
        <form method="GET" action="{{ route('komite.verifikasi.index') }}" class="row g-2 align-items-center">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="col-md-{{ $tab === 'arsip' ? '5' : '8' }}">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="cari" class="form-control form-control-sm border-start-0" placeholder="Cari nama dokter atau nomor izin SIP..." value="{{ request('cari') }}">
                </div>
            </div>

            @if($tab === 'arsip')
                <div class="col-md-4">
                    <select name="status_verifikasi" class="form-select form-select-sm">
                        <option value="">-- Semua Status Arsip --</option>
                        <option value="disetujui" {{ request('status_verifikasi') == 'disetujui' ? 'selected' : '' }}>Disetujui / Disahkan</option>
                        <option value="ditolak" {{ request('status_verifikasi') == 'ditolak' ? 'selected' : '' }}>Ditolak (Perlu Perbaikan)</option>
                    </select>
                </div>
            @endif

            <div class="col-md-{{ $tab === 'arsip' ? '3' : '4' }} d-flex gap-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100">
                    <i class="fa-solid fa-filter me-1"></i> Filter Antrean
                </button>
                <a href="{{ route('komite.verifikasi.index', ['tab' => $tab]) }}" class="btn btn-light btn-sm border" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table Antrean Bersih (10 Baris per Halaman) -->
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th style="width: 140px;">Tgl Pengajuan</th>
                    <th>Nama Dokter</th>
                    <th>KSM</th>
                    <th>Nomor Izin SIP</th>
                    <th>Tempat Praktik</th>
                    <th>Status Telaah</th>
                    <th>Verifikator</th>
                    <th class="text-end" style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sipList as $item)
                    <tr>
                        <td>
                            <div class="fw-semibold text-dark">{{ $item->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted fs-3xs">{{ $item->created_at->format('H:i') }} WITA</small>
                        </td>
                        <td>
                            <div class="fw-bold text-dark fs-xs">{{ $item->dokter->user->nama ?? '-' }}</div>
                            <small class="text-muted font-monospace fs-3xs">NIP: {{ $item->dokter->nip_nrp ?? '-' }}</small>
                        </td>
                        <td><span class="badge bg-teal-subtle text-teal">{{ $item->dokter->ksm->kode_ksm ?? '-' }}</span></td>
                        <td>
                            <span class="font-monospace fw-semibold text-dark fs-xs">{{ $item->nomor_sip }}</span>
                            <div class="text-muted fs-3xs">{{ $item->penerbit_sip ?: 'DPMPTSP' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border fs-3xs">Praktik Ke-{{ $item->tempat_praktik_ke }}</span>
                        </td>
                        <td>
                            @if($item->status_verifikasi === 'menunggu')
                                <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Menunggu Telaah</span>
                            @elseif($item->status_verifikasi === 'disetujui')
                                <span class="badge bg-success-subtle text-success border border-success"><i class="fa-solid fa-circle-check me-1"></i> Disetujui</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Ditolak</span>
                            @endif

                            @if($item->permohonan_kewenangan_baru)
                                <div class="mt-1">
                                    <span class="badge bg-primary-subtle text-primary border border-primary fs-3xs"><i class="fa-solid fa-plus-circle me-1"></i> RKK Baru</span>
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($item->verifikator)
                                <div class="fs-xs fw-semibold">{{ $item->verifikator->nama }}</div>
                                <small class="text-muted fs-3xs">{{ $item->verified_at ? $item->verified_at->format('d/m/y') : '' }}</small>
                            @else
                                <span class="text-muted fs-xs">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('komite.verifikasi.show', $item->id) }}" class="btn btn-sm {{ $item->status_verifikasi === 'menunggu' ? 'btn-teal text-white fw-semibold' : 'btn-outline-secondary' }}">
                                <i class="fa-solid fa-clipboard-check me-1"></i>
                                {{ $item->status_verifikasi === 'menunggu' ? 'Telaah' : 'Lihat' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-circle-check fs-2 text-success mb-2"></i>
                            <div>Tidak ada berkas yang {{ $tab === 'menunggu' ? 'menunggu telaah saat ini' : 'terdapat di arsip telaah' }}.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sipList->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $sipList->links() }}
        </div>
    @endif
</div>
@endsection
