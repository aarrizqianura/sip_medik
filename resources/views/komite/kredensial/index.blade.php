@extends('layouts.app')

@section('title', 'Kredensialing & Rekredensialing Dokter (SPK & RKK)')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Manajemen Kredensialing & Rekredensialing (SPK & RKK)</h4>
        <p class="text-secondary fs-sm mb-0">Evaluasi siklus 3-tahunan Subkomite Kredensial bersama Mitra Bestari (Peer Group) serta penerbitan Surat Rekomendasi Kewenangan Klinis kepada Direktur</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('komite.kredensial.create') }}" class="btn btn-teal text-white btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Pencatatan SPK Manual</span>
        </a>
    </div>
</div>

<!-- Filter Toolbar -->
<div class="card shadow-xs mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('komite.kredensial.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari nama dokter, no. SPK, atau no. rekomendasi..." value="{{ request('cari') }}">
            </div>
            <div class="col-md-4">
                <select name="status_kredensial" class="form-select form-select-sm">
                    <option value="">-- Semua Status Kredensial --</option>
                    <option value="proses_rekredensial" {{ request('status_kredensial') == 'proses_rekredensial' ? 'selected' : '' }}>Proses Rekredensial (Menunggu Sidang)</option>
                    <option value="sidang_komite" {{ request('status_kredensial') == 'sidang_komite' ? 'selected' : '' }}>Sidang Kredensial / RKK Ditetapkan</option>
                    <option value="rekomendasi_terbit" {{ request('status_kredensial') == 'rekomendasi_terbit' ? 'selected' : '' }}>Rekomendasi Digital Terbit (Ke Direktur)</option>
                    <option value="aktif" {{ request('status_kredensial') == 'aktif' ? 'selected' : '' }}>SPK Aktif (Kewenangan Berlaku)</option>
                    <option value="evaluasi" {{ request('status_kredensial') == 'evaluasi' ? 'selected' : '' }}>Evaluasi (≤ 90 Hari)</option>
                    <option value="kedaluwarsa" {{ request('status_kredensial') == 'kedaluwarsa' ? 'selected' : '' }}>Kedaluwarsa (> 3 Tahun)</option>
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

<!-- Table SPK & Rekredensial -->
<div class="card shadow-xs">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th>Dokter</th>
                    <th>KSM</th>
                    <th>Status Kredensial</th>
                    <th>Penetapan RKK</th>
                    <th>Mitra Bestari / Sidang</th>
                    <th>SPK / Surat Rekomendasi</th>
                    <th>Jatuh Tempo</th>
                    <th class="text-end">Aksi Alur</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kredensialList as $kred)
                    @php $sisa = $kred->hitungSisaHari(); @endphp
                    <tr class="{{ $kred->status_kredensial === 'proses_rekredensial' ? 'table-warning bg-opacity-10' : '' }}">
                        <td>
                            <div class="fw-semibold text-dark">{{ $kred->dokter->user->nama ?? '-' }}</div>
                            <small class="text-muted">NIP: {{ $kred->dokter->nip_nrp ?? '-' }}</small>
                            @if($kred->alasan_rekredensial)
                                <div class="fs-2xs text-secondary text-truncate" style="max-width: 200px;" title="{{ $kred->alasan_rekredensial }}">
                                    <i class="fa-solid fa-circle-info text-teal"></i> {{ $kred->alasan_rekredensial }}
                                </div>
                            @endif
                        </td>
                        <td><span class="badge bg-teal-subtle text-teal">{{ $kred->dokter->ksm->kode_ksm ?? '-' }}</span></td>
                        <td>
                            {!! $kred->status_badge !!}
                            <div class="fs-2xs text-muted mt-1">{{ $kred->tahap_label }}</div>
                        </td>
                        <td>
                            @if($kred->rkk_total_count > 0)
                                <span class="badge bg-success-subtle text-success border border-success fs-2xs" title="Kewenangan Mandiri">
                                    <i class="fa-solid fa-user-check"></i> {{ $kred->rkk_mandiri_count }} M
                                </span>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning fs-2xs ms-1" title="Kewenangan Supervisi">
                                    <i class="fa-solid fa-user-clock"></i> {{ $kred->rkk_supervisi_count }} S
                                </span>
                            @else
                                <span class="badge bg-light text-secondary border fs-2xs">Belum Ada RKK</span>
                            @endif
                        </td>
                        <td>
                            @if($kred->nama_mitra_bestari)
                                <div class="fw-semibold fs-xs text-dark">{{ $kred->nama_mitra_bestari }}</div>
                                <small class="text-muted fs-2xs">{{ $kred->tanggal_sidang ? $kred->tanggal_sidang->format('d/m/Y') : '-' }}</small>
                            @else
                                <span class="text-muted fs-xs">Belum Disidangkan</span>
                            @endif
                        </td>
                        <td>
                            @if($kred->nomor_surat_rekomendasi)
                                <div class="fs-xs fw-bold text-primary font-monospace">{{ $kred->nomor_surat_rekomendasi }}</div>
                                <small class="text-muted fs-2xs"><i class="fa-solid fa-paper-plane text-teal"></i> Rekomendasi Direktur</small>
                            @elseif($kred->nomor_spk)
                                <div class="fs-xs font-monospace">{{ $kred->nomor_spk }}</div>
                                <small class="text-muted fs-2xs">SPK Direktur</small>
                            @else
                                <span class="text-muted fs-xs">-</span>
                            @endif
                        </td>
                        <td>
                            @if($kred->tanggal_berakhir_spk)
                                <div class="fw-semibold text-dark">{{ $kred->tanggal_berakhir_spk->format('d/m/Y') }}</div>
                                <small class="{{ $sisa < 0 ? 'text-danger fw-bold' : ($sisa <= 90 ? 'text-warning-emphasis fw-bold' : 'text-muted') }}">
                                    {{ $sisa < 0 ? abs($sisa) . ' hr lalu' : 'sisa ' . $sisa . ' hr' }}
                                </small>
                            @else
                                <span class="text-muted fs-xs">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <!-- Tombol Sidang Kredensial & Pengisian RKK -->
                                <a href="{{ route('komite.kredensial.sidang', $kred->id) }}" class="btn btn-sm {{ in_array($kred->status_kredensial, ['proses_rekredensial', 'sidang_komite']) ? 'btn-teal text-white fw-semibold' : 'btn-outline-secondary' }}" title="Evaluasi Logbook, Portofolio & Form Digital RKK">
                                    <i class="fa-solid fa-gavel me-1"></i>
                                    {{ in_array($kred->status_kredensial, ['proses_rekredensial', 'sidang_komite']) ? 'Sidang RKK' : 'RKK' }}
                                </a>

                                <!-- Tombol Surat Rekomendasi Digital jika sudah pernah disidangkan -->
                                @if($kred->nomor_surat_rekomendasi || $kred->status_kredensial === 'rekomendasi_terbit' || $kred->rkk_total_count > 0)
                                    <a href="{{ route('komite.kredensial.surat_rekomendasi', $kred->id) }}" class="btn btn-sm btn-outline-primary" title="Lihat / Cetak Surat Rekomendasi Digital ke Direktur">
                                        <i class="fa-solid fa-file-signature"></i>
                                    </a>
                                @endif

                                <!-- Opsi Edit & Hapus -->
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-sm">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('komite.kredensial.edit', $kred->id) }}">
                                                <i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Ubah Data SPK
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('komite.kredensial.destroy', $kred->id) }}" method="POST" onsubmit="return confirm('Hapus instrumen kredensial ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fa-solid fa-trash me-2"></i> Hapus
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
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
