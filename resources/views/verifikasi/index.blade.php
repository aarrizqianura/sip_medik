@extends('layouts.app')

@section('title', 'Verifikasi Berkas SIP Dokter')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Verifikasi & Pengesahan Berkas SIP Dokter</h4>
        <p class="text-secondary fs-sm mb-0">Subkomite Kredensial Komite Medik RSUD dr. H. Moch. Ansari Saleh memvalidasi keabsahan pindaian SIP sebelum dokumen aktif di sistem</p>
    </div>
</div>

<!-- Filter Toolbar -->
<div class="card shadow-xs mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('komite.verifikasi.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari nama dokter atau nomor SIP..." value="{{ request('cari') }}">
            </div>
            <div class="col-md-4">
                <select name="status_verifikasi" class="form-select form-select-sm">
                    <option value="">-- Semua Status Verifikasi --</option>
                    <option value="menunggu" {{ request('status_verifikasi') == 'menunggu' ? 'selected' : '' }}>Menunggu Telaah (Antrean)</option>
                    <option value="disetujui" {{ request('status_verifikasi') == 'disetujui' ? 'selected' : '' }}>Disetujui / Disahkan</option>
                    <option value="ditolak" {{ request('status_verifikasi') == 'ditolak' ? 'selected' : '' }}>Ditolak (Perlu Perbaikan)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('komite.verifikasi.index') }}" class="btn btn-light btn-sm border" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table Antrean Verifikasi -->
<div class="card shadow-xs">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th>Tgl Pengajuan</th>
                    <th>Nama Dokter</th>
                    <th>KSM</th>
                    <th>No. SIP Baru</th>
                    <th>Tempat Ke</th>
                    <th>Status Validasi</th>
                    <th>Verifikator</th>
                    <th class="text-end">Aksi Telaah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sipList as $item)
                    <tr>
                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $item->dokter->user->nama ?? '-' }}</div>
                            <small class="text-muted">NIP: {{ $item->dokter->nip_nrp ?? '-' }}</small>
                        </td>
                        <td><span class="badge bg-teal-subtle text-teal">{{ $item->dokter->ksm->kode_ksm ?? '-' }}</span></td>
                        <td><span class="font-monospace fs-xs">{{ $item->nomor_sip }}</span></td>
                        <td>Praktik Ke-{{ $item->tempat_praktik_ke }}</td>
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
                                    <span class="badge bg-primary-subtle text-primary border border-primary fs-3xs"><i class="fa-solid fa-plus-circle me-1"></i> Usul Kewenangan Baru</span>
                                </div>
                            @endif

                            @if($item->status_verifikasi === 'ditolak' && $item->catatan_verifikasi)
                                <div class="fs-3xs text-danger text-truncate mt-1" style="max-width: 180px;" title="{{ $item->catatan_verifikasi }}">
                                    <i class="fa-solid fa-note-sticky"></i> {{ $item->catatan_verifikasi }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($item->verifikator)
                                <div class="fs-xs fw-semibold">{{ $item->verifikator->nama }}</div>
                                <small class="text-muted fs-xs">{{ $item->verified_at ? $item->verified_at->format('d/m/y') : '' }}</small>
                            @else
                                <span class="text-muted fs-xs">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('komite.verifikasi.show', $item->id) }}" class="btn btn-sm {{ $item->status_verifikasi === 'menunggu' ? 'btn-teal text-white fw-semibold' : 'btn-outline-secondary' }}">
                                <i class="fa-solid fa-clipboard-check me-1"></i>
                                {{ $item->status_verifikasi === 'menunggu' ? 'Telaah Berkas' : 'Lihat Telaah' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            Tidak ditemukan berkas permohonan SIP dalam kriteria ini.
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
