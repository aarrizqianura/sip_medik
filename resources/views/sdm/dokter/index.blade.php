@extends('layouts.app')

@section('title', 'Master Data Staf Medis Dokter')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold text-dark mb-1">
        <i class="fa-solid fa-user-doctor text-teal me-2"></i>Master Data Staf Medis Dokter
    </h4>
    <p class="text-secondary fs-sm mb-0">Pengelolaan profil biodata dokter, NIP/NRP, KSM naungan, kualifikasi klinis, dan perekaman data STR (16 digit) sebagai fondasi hukum</p>
</div>

<!-- Aksi Tambah Dokter (Diatas Kanan Posisi Filter) -->
<div class="d-flex justify-content-end align-items-center mb-3">
    <a href="{{ route('sdm.dokter.create') }}" id="btnTambahDokter" class="btn btn-teal text-white d-inline-flex align-items-center gap-2 px-3 py-2 shadow-sm fw-semibold">
        <i class="fa-solid fa-user-plus"></i>
        <span>Tambah Dokter</span>
    </a>
</div>

<!-- Filter Toolbar -->
<div class="card shadow-xs mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('sdm.dokter.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari nama, NIP, spesialisasi..." value="{{ request('cari') }}">
            </div>
            <div class="col-md-3">
                <select name="id_ksm" class="form-select form-select-sm">
                    <option value="">-- Semua KSM --</option>
                    @foreach($ksmList as $ksm)
                        <option value="{{ $ksm->id }}" {{ request('id_ksm') == $ksm->id ? 'selected' : '' }}>{{ $ksm->nama_ksm }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status_kepegawaian" class="form-select form-select-sm">
                    <option value="">-- Kepegawaian --</option>
                    <option value="PNS" {{ request('status_kepegawaian') == 'PNS' ? 'selected' : '' }}>PNS</option>
                    <option value="PPPK" {{ request('status_kepegawaian') == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                    <option value="BLUD" {{ request('status_kepegawaian') == 'BLUD' ? 'selected' : '' }}>BLUD</option>
                    <option value="Mitra" {{ request('status_kepegawaian') == 'Mitra' ? 'selected' : '' }}>Mitra</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status_sip" class="form-select form-select-sm">
                    <option value="">-- Status SIP --</option>
                    <option value="aktif" {{ request('status_sip') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="due_soon" {{ request('status_sip') == 'due_soon' ? 'selected' : '' }}>Due Soon</option>
                    <option value="due" {{ request('status_sip') == 'due' ? 'selected' : '' }}>Due (Kritis)</option>
                    <option value="overdue" {{ request('status_sip') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('sdm.dokter.index') }}" class="btn btn-light btn-sm border" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table Dokters -->
<div class="card shadow-xs">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th>Dokter</th>
                    <th>NIP / NRP</th>
                    <th>KSM & Spesialisasi</th>
                    <th>Nomor STR (16 Digit)</th>
                    <th>Status Validasi STR</th>
                    <th>Praktik RSUD</th>
                    <th>SIP Aktif</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dokters as $dok)
                    @php $sip = $dok->sipTerbaru; @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar bg-teal text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-xs">
                                    {{ strtoupper(substr($dok->user->nama ?? 'D', 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('sdm.dokter.show', $dok->id) }}" class="fw-semibold text-dark text-decoration-none hover-teal">
                                        {{ $dok->user->nama ?? '-' }}
                                    </a>
                                    <div><small class="text-muted"><i class="fa-brands fa-whatsapp text-success me-1"></i>{{ $dok->user->no_wa ?? '-' }}</small></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="font-monospace fs-xs">{{ $dok->nip_nrp }}</span>
                            <div><span class="badge bg-light text-dark border fs-2xs">{{ $dok->status_kepegawaian }}</span></div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $dok->spesialisasi }}</div>
                            <small class="text-teal fw-semibold">{{ $dok->ksm->nama_ksm ?? '-' }}</small>
                            @if($dok->subspesialis)
                                <div class="fs-xs text-muted">Sub: {{ $dok->subspesialis }}</div>
                            @endif
                        </td>
                        <td>
                            @if($dok->nomor_str)
                                <div class="font-monospace fw-bold fs-xs text-dark">
                                    {{ $dok->nomor_str }}
                                    @if($dok->is_str_locked)
                                        <i class="fa-solid fa-lock text-success ms-1" title="Terkunci (Read-Only)"></i>
                                    @endif
                                </div>
                                <small class="text-muted fs-xs">
                                    {{ $dok->status_str === 'seumur_hidup' ? 'Seumur Hidup' : 's.d ' . ($dok->tanggal_kadaluarsa_str ? $dok->tanggal_kadaluarsa_str->format('d/m/Y') : '-') }}
                                </small>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Belum Diinput</span>
                            @endif
                        </td>
                        <td>
                            {!! $dok->status_verifikasi_str_badge !!}
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary fs-xs">
                                {{ $dok->tempat_praktik_label }}
                            </span>
                        </td>
                        <td>
                            @if($sip)
                                @php $sisa = $sip->hitungSisaHari(); @endphp
                                <span class="status-pill status-{{ $sip->status }} fs-xs">
                                    {{ $sip->status_label }}
                                </span>
                                <div class="fs-xs text-muted mt-1">
                                    {{ $sisa < 0 ? abs($sisa) . ' hr lalu' : 'sisa ' . $sisa . ' hr' }}
                                </div>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Belum ada SIP</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('sdm.dokter.show', $dok->id) }}" class="btn btn-outline-secondary" title="Detail Dokter">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('sdm.dokter.edit', $dok->id) }}" class="btn btn-outline-primary" title="Ubah Data">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('sdm.dokter.destroy', $dok->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus dokter {{ $dok->user->nama ?? '' }}?')">
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
                        <td colspan="7" class="text-center py-4 text-muted">
                            Tidak ditemukan data dokter yang sesuai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($dokters->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $dokters->links() }}
        </div>
    @endif
</div>
@endsection
