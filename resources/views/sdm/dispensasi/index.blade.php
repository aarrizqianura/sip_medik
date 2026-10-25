@extends('layouts.app')

@section('title', 'Tahap 6: Manajemen Masa Tenggang Administratif (Grace Period)')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-teal-subtle text-teal border border-teal-subtle px-2 py-1 fs-xs fw-bold">TAHAP 6</span>
            <h4 class="fw-bold text-dark mb-0">Alur Masa Tenggang Administratif (Grace Period / On-Process)</h4>
        </div>
        <p class="text-secondary fs-sm mb-0">
            Verifikasi tanda terima pengurusan perizinan DPMPTSP/Dinkes, pemberian dispensasi terbatas (14–30 hari), dan proteksi wewenang klinis (SPK/RKK) agar pelayanan tidak terhenti.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('sdm.dokter.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-user-doctor"></i>
            <span>Master Data Dokter</span>
        </a>
    </div>
</div>

<!-- Metrik Indikator Kunci (Hanya menampilkan yang memiliki data/non-nol) -->
@php
    $indicatorCards = [
        [
            'count' => $totalMenunggu,
            'border' => 'border-warning',
            'label' => 'Antrean Menunggu',
            'badge' => 'bg-warning text-dark',
            'icon' => 'fa-hourglass-half',
            'badge_text' => 'Menunggu',
            'text_class' => 'text-warning-emphasis',
            'desc' => 'Resi DPMPTSP perlu diverifikasi'
        ],
        [
            'count' => $totalAktif,
            'border' => 'border-teal',
            'label' => 'Grace Period Aktif',
            'badge' => 'bg-teal text-white',
            'icon' => 'fa-business-time',
            'badge_text' => 'On-Process',
            'text_class' => 'text-teal',
            'desc' => 'Dispensasi aktif (14–30 hari)'
        ],
        [
            'count' => $totalDokterDalamProses,
            'border' => 'border-success',
            'label' => 'Pelayanan Terproteksi',
            'badge' => 'bg-success text-white',
            'icon' => 'fa-shield-check',
            'badge_text' => 'Aman',
            'text_class' => 'text-success',
            'desc' => 'Dokter berstatus dalam_proses'
        ],
        [
            'count' => $totalRiwayat,
            'border' => 'border-secondary',
            'label' => 'Riwayat Berakhir',
            'badge' => 'bg-secondary text-white',
            'icon' => 'fa-clock-rotate-left',
            'badge_text' => 'Arsip',
            'text_class' => 'text-secondary',
            'desc' => 'Dispensasi selesai / ditolak'
        ]
    ];
    $visibleCards = array_filter($indicatorCards, fn($item) => $item['count'] > 0);
    $cardCols = match(count($visibleCards)) {
        1 => 'col-12',
        2 => 'col-sm-6',
        3 => 'col-sm-6 col-lg-4',
        default => 'col-sm-6 col-xl-3'
    };
@endphp

@if(count($visibleCards) > 0)
<div class="row g-3 mb-4">
    @foreach($visibleCards as $card)
    <div class="{{ $cardCols }}">
        <div class="card h-100 border-start border-4 {{ $card['border'] }} shadow-xs">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">{{ $card['label'] }}</span>
                    <span class="badge {{ $card['badge'] }}"><i class="fa-solid {{ $card['icon'] }}"></i> {{ $card['badge_text'] }}</span>
                </div>
                <div class="fs-2 fw-bold {{ $card['text_class'] }}">{{ $card['count'] }}</div>
                <div class="text-muted fs-xs mt-1">{{ $card['desc'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<!-- Filter Toolbar -->
<div class="card shadow-xs mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('sdm.dispensasi.index') }}" class="row g-2 align-items-center">
            <input type="hidden" name="tab" value="{{ $statusTab }}">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="cari" class="form-control form-control-sm border-start-0" placeholder="Cari nama dokter, NIP, no. SIP, nomor resi DPMPTSP..." value="{{ request('cari') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="id_ksm" class="form-select form-select-sm">
                    <option value="">-- Semua Kelompok Staf Medis (KSM) --</option>
                    @foreach($ksmList as $ksm)
                        <option value="{{ $ksm->id }}" {{ request('id_ksm') == $ksm->id ? 'selected' : '' }}>{{ $ksm->nama_ksm }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100">
                    <i class="fa-solid fa-filter me-1"></i> Filter Data
                </button>
                <a href="{{ route('sdm.dispensasi.index', ['tab' => $statusTab]) }}" class="btn btn-light btn-sm border" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tab Navigasi Dispensasi -->
<div class="card shadow-xs">
    <div class="card-header bg-white border-bottom p-0">
        <ul class="nav nav-tabs card-header-tabs m-0 px-3 border-0">
            <li class="nav-item">
                <a class="nav-link py-3 px-3 fs-sm {{ $statusTab === 'menunggu' ? 'active fw-bold text-teal border-teal' : 'text-secondary' }}" href="{{ route('sdm.dispensasi.index', array_merge(request()->query(), ['tab' => 'menunggu'])) }}">
                    <i class="fa-solid fa-hourglass-half me-1 text-warning"></i>
                    Antrean Menunggu Verifikasi
                    @if($totalMenunggu > 0)
                        <span class="badge bg-warning text-dark ms-1">{{ $totalMenunggu }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 px-3 fs-sm {{ $statusTab === 'aktif' ? 'active fw-bold text-teal border-teal' : 'text-secondary' }}" href="{{ route('sdm.dispensasi.index', array_merge(request()->query(), ['tab' => 'aktif'])) }}">
                    <i class="fa-solid fa-shield-check me-1 text-teal"></i>
                    Grace Period Aktif (14–30 Hari)
                    @if($totalAktif > 0)
                        <span class="badge bg-teal text-white ms-1">{{ $totalAktif }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 px-3 fs-sm {{ $statusTab === 'riwayat' ? 'active fw-bold text-teal border-teal' : 'text-secondary' }}" href="{{ route('sdm.dispensasi.index', array_merge(request()->query(), ['tab' => 'riwayat'])) }}">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i>
                    Riwayat Selesai / Ditolak
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 px-3 fs-sm {{ $statusTab === 'semua' ? 'active fw-bold text-teal border-teal' : 'text-secondary' }}" href="{{ route('sdm.dispensasi.index', array_merge(request()->query(), ['tab' => 'semua'])) }}">
                    <i class="fa-solid fa-list-check me-1"></i>
                    Semua Berkas
                </a>
            </li>
        </ul>
    </div>

    <!-- Tampilan Data Berdasarkan Tab Aktif -->
    @php
        $daftarTampil = match($statusTab) {
            'aktif' => $aktifList,
            'riwayat' => $riwayatList,
            'semua' => $semuaList,
            default => $menungguList,
        };
    @endphp

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th>Dokter & KSM</th>
                    <th>Nomor SIP & Praktik</th>
                    <th>Bukti Resi DPMPTSP/Dinkes</th>
                    <th>Tgl Resi / Pengajuan</th>
                    <th>Batas Waktu Grace Period</th>
                    <th>Status Dispensasi & SIP</th>
                    <th>Proteksi Pelayanan</th>
                    <th class="text-end">Aksi Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftarTampil as $item)
                    @php $dok = $item->dokter; @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar bg-teal text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-xs" style="width: 36px; height: 36px;">
                                    {{ strtoupper(substr($dok->user->nama ?? 'D', 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('sdm.dokter.show', $dok->id) }}" class="fw-semibold text-dark text-decoration-none hover-teal">
                                        {{ $dok->user->nama ?? '-' }}
                                    </a>
                                    <div class="fs-xs text-muted">
                                        NIP: {{ $dok->nip_nrp ?? '-' }} &bullet; <span class="text-teal fw-semibold">{{ $dok->ksm->nama_ksm ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="font-monospace fw-semibold fs-xs text-dark">{{ $item->nomor_sip }}</div>
                            <span class="badge bg-teal-subtle text-teal border border-teal-subtle fs-3xs">SIP Ke-{{ $item->tempat_praktik_ke }}</span>
                            <div class="fs-3xs text-muted mt-1">
                                Jatuh Tempo Awal: {{ $item->tanggal_kadaluarsa ? $item->tanggal_kadaluarsa->format('d/m/Y') : '-' }}
                            </div>
                        </td>
                        <td>
                            <div class="font-monospace fw-bold text-dark fs-xs">{{ $item->nomor_resi_proses ?: '-' }}</div>
                            <small class="text-secondary fs-3xs d-block">{{ $item->instansi_penerbit_resi ?: 'DPMPTSP' }}</small>
                            @if($item->file_bukti_proses)
                                <a href="{{ asset('storage/' . $item->file_bukti_proses) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none mt-1">
                                    <i class="fa-solid fa-file-pdf me-1 text-danger"></i> Unduh Lembar Resi
                                </a>
                            @endif
                        </td>
                        <td>
                            <div>{{ $item->tanggal_pengajuan_resi ? $item->tanggal_pengajuan_resi->format('d/m/Y') : '-' }}</div>
                            <small class="text-muted fs-3xs">Durasi: {{ $item->durasi_dispensasi_hari ?: 30 }} hari</small>
                        </td>
                        <td>
                            @if($item->isGracePeriodAktif())
                                @php $sisaDisp = $item->hitungSisaHariDispensasi(); @endphp
                                <div class="fw-bold text-teal">{{ $item->tanggal_akhir_dispensasi ? $item->tanggal_akhir_dispensasi->format('d/m/Y') : '-' }}</div>
                                <span class="badge bg-teal-subtle text-teal border border-teal-subtle fs-3xs">
                                    <i class="fa-solid fa-clock me-1"></i> Sisa {{ $sisaDisp }} Hari Lagi
                                </span>
                            @elseif($item->status_dispensasi === 'diajukan')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning fs-xs">
                                    <i class="fa-solid fa-hourglass-start me-1"></i> Menunggu Telaah SDM
                                </span>
                            @elseif($item->status_dispensasi === 'berakhir')
                                <div class="text-muted">{{ $item->tanggal_akhir_dispensasi ? $item->tanggal_akhir_dispensasi->format('d/m/Y') : '-' }}</div>
                                <span class="badge bg-secondary-subtle text-secondary fs-3xs">Dispensasi Berakhir</span>
                            @elseif($item->status_dispensasi === 'ditolak')
                                <span class="badge bg-danger-subtle text-danger fs-3xs">Ditolak</span>
                            @else
                                <span class="text-muted fs-xs">-</span>
                            @endif
                        </td>
                        <td>
                            <div>{!! $item->status_dispensasi_badge !!}</div>
                            <div class="mt-1">
                                <span class="status-pill status-{{ $item->status }} fs-3xs">
                                    {{ $item->status_label }}
                                </span>
                            </div>
                        </td>
                        <td>
                            @if($item->isGracePeriodAktif())
                                <span class="badge bg-success-subtle text-success border border-success fs-xs">
                                    <i class="fa-solid fa-shield-check me-1"></i> SPK/RKK Terproteksi
                                </span>
                                <div class="fs-3xs text-muted mt-1">Pelayanan medis berjalan normal</div>
                            @elseif($item->status_dispensasi === 'diajukan')
                                <span class="badge bg-light text-secondary border fs-3xs">Dalam Telaah Dokumen</span>
                            @else
                                <span class="text-muted fs-3xs">Sesuai Regulasi Standar</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('sdm.dispensasi.show', $item->id) }}" class="btn btn-sm {{ $item->status_dispensasi === 'diajukan' ? 'btn-teal text-white shadow-2xs' : 'btn-outline-secondary' }}">
                                <i class="fa-solid {{ $item->status_dispensasi === 'diajukan' ? 'fa-stamp' : 'fa-eye' }} me-1"></i>
                                <span>{{ $item->status_dispensasi === 'diajukan' ? 'Verifikasi Resi' : 'Detail Dispensasi' }}</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fs-2 mb-2 d-block text-secondary"></i>
                            Tidak ada berkas permohonan masa tenggang administratif pada tab ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($daftarTampil->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $daftarTampil->links() }}
        </div>
    @endif
</div>
@endsection
