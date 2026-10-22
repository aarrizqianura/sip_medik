@extends('layouts.app')

@section('title', 'Dashboard Komite Medik (Subkomite Kredensial)')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Dashboard Subkomite Kredensial Komite Medik</h4>
        <p class="text-secondary fs-sm mb-0">Verifikasi berkas SIP dan monitoring masa berlaku Surat Penugasan Klinis (SPK) & RKK Dokter</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('komite.verifikasi.index') }}" class="btn btn-teal btn-sm text-white d-flex align-items-center gap-2">
            <i class="fa-solid fa-stamp"></i>
            <span>Antrean Telaah SIP</span>
        </a>
        <a href="{{ route('komite.kredensial.create') }}" class="btn btn-outline-teal btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah SPK/RKK Baru</span>
        </a>
    </div>
</div>

@if(($stats['kredensialProsesRekredensial'] ?? 0) > 0)
    <div class="alert alert-warning border-warning shadow-xs d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-warning text-dark p-2 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="fa-solid fa-gavel"></i>
            </div>
            <div>
                <strong class="text-dark">Terdapat {{ $stats['kredensialProsesRekredensial'] }} Dokter dalam Status Proses Rekredensial</strong>
                <div class="fs-xs text-secondary">Berkas SIP telah disahkan dan siklus SPK/RKK 3 tahunan dokter memerlukan Sidang Kredensial & penetapan RKK bersama Mitra Bestari (Peer Group).</div>
            </div>
        </div>
        <a href="{{ route('komite.kredensial.index', ['status_kredensial' => 'proses_rekredensial']) }}" class="btn btn-warning text-dark btn-sm fw-semibold shadow-xs">
            <i class="fa-solid fa-list-check me-1"></i> Buka Antrean Sidang Kredensial
        </a>
    </div>
@endif

<!-- Key Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-4 border-warning shadow-xs">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">Antrean Verifikasi SIP</span>
                    <span class="badge bg-warning-subtle text-warning rounded-pill"><i class="fa-solid fa-hourglass-half"></i> Menunggu</span>
                </div>
                <div class="fs-2 fw-bold text-dark">{{ $stats['sipMenunggu'] }}</div>
                <div class="text-muted fs-xs mt-1">Perlu telaah klinis & validasi</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-4 border-emerald shadow-xs">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">SPK / RKK Aktif</span>
                    <span class="badge bg-success-subtle text-success rounded-pill"><i class="fa-solid fa-check"></i> Absah</span>
                </div>
                <div class="fs-2 fw-bold text-dark">{{ $stats['kredensialAktif'] }}</div>
                <div class="text-muted fs-xs mt-1">Kewenangan klinis berlaku</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-4 border-orange shadow-xs" style="border-left-color: #ea580c !important;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">SPK Evaluasi (≤90 Hari)</span>
                    <span class="badge bg-warning-subtle text-warning rounded-pill"><i class="fa-solid fa-clock"></i> Rekredensialing</span>
                </div>
                <div class="fs-2 fw-bold text-warning">{{ $stats['kredensialEvaluasi'] }}</div>
                <div class="text-muted fs-xs mt-1">Jatuh tempo rekredensialing 3 thn</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-4 border-danger shadow-xs">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">SPK Kedaluwarsa</span>
                    <span class="badge bg-danger text-white rounded-pill"><i class="fa-solid fa-ban"></i> Overdue</span>
                </div>
                <div class="fs-2 fw-bold text-danger">{{ $stats['kredensialKadaluarsa'] }}</div>
                <div class="text-muted fs-xs mt-1">Harus segera rekredensialing</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Antrean Verifikasi Berkas SIP -->
    <div class="col-lg-6">
        <div class="card shadow-xs h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark">
                    <i class="fa-solid fa-stamp text-teal me-2"></i>
                    Antrean Validasi Dokumen SIP Terbaru
                </div>
                <a href="{{ route('komite.verifikasi.index') }}" class="btn btn-outline-secondary btn-sm fs-xs">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Dokter</th>
                            <th>No. SIP</th>
                            <th>Tgl Upload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($antreanVerifikasi as $item)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $item->dokter->user->nama ?? '-' }}</div>
                                    <small class="text-muted">{{ $item->dokter->ksm->nama_ksm ?? '-' }}</small>
                                </td>
                                <td><span class="font-monospace fs-xs">{{ $item->nomor_sip }}</span></td>
                                <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('komite.verifikasi.show', $item->id) }}" class="btn btn-sm btn-teal text-white">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i> Telaah
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i> Tidak ada antrean berkas SIP yang menunggu validasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SPK Rekredensialing Mendekati Jatuh Tempo -->
    <div class="col-lg-6">
        <div class="card shadow-xs h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark">
                    <i class="fa-solid fa-id-card-clip text-warning me-2"></i>
                    Monitoring Evaluasi SPK & RKK Dokter (3 Tahunan)
                </div>
                <a href="{{ route('komite.kredensial.index') }}" class="btn btn-outline-secondary btn-sm fs-xs">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Dokter</th>
                            <th>No. SPK</th>
                            <th>Berakhir</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($spkEvaluasi as $spk)
                            @php $sisaSpk = $spk->hitungSisaHari(); @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $spk->dokter->user->nama ?? '-' }}</div>
                                    <small class="text-muted">{{ $spk->dokter->ksm->kode_ksm ?? '-' }}</small>
                                </td>
                                <td><span class="font-monospace fs-xs">{{ $spk->nomor_spk }}</span></td>
                                <td>{{ $spk->tanggal_berakhir_spk ? $spk->tanggal_berakhir_spk->format('d/m/Y') : '-' }}</td>
                                <td>
                                    <span class="badge {{ $spk->status_kredensial === 'kedaluwarsa' ? 'bg-danger text-white' : 'bg-warning text-dark' }}">
                                        {{ $spk->status_kredensial === 'kedaluwarsa' ? 'Kedaluwarsa' : 'Evaluasi (' . $sisaSpk . ' hr)' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i> Tidak ada SPK dalam masa evaluasi atau kedaluwarsa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
