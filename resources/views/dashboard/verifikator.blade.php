@extends('layouts.app')

@section('title', 'Dashboard Komite Medik (Subkomite Kredensial)')

@section('content')
<!-- Header Area -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Dashboard Subkomite Kredensial Komite Medik</h4>
        <p class="text-secondary fs-sm mb-0">Verifikasi berkas SIP dan monitoring masa berlaku Surat Penugasan Klinis (SPK) & RKK Dokter RSUD dr. H. Moch. Ansari Saleh</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('komite.verifikasi.index') }}" class="btn btn-teal btn-sm text-white text-nowrap d-flex align-items-center gap-2">
            <i class="fa-solid fa-stamp"></i>
            <span>Antrean Telaah SIP</span>
        </a>
        <a href="{{ route('komite.kredensial.create') }}" class="btn btn-outline-teal btn-sm text-nowrap d-flex align-items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah SPK/RKK Baru</span>
        </a>
    </div>
</div>

<!-- Ribbon Notifikasi Mitigasi Darurat -->
@if(isset($dokterDitangguhkanList) && $dokterDitangguhkanList->isNotEmpty())
    <div class="alert alert-danger border-0 border-start border-4 border-danger shadow-xs p-2 px-3 mb-4 rounded-3">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                <span class="badge bg-danger text-white flex-shrink-0"><i class="fa-solid fa-triangle-exclamation me-1"></i> MITIGASI DARURAT</span>
                <span class="fw-bold text-danger fs-sm text-truncate">
                    {{ $dokterDitangguhkanList->count() }} Dokter Berstatus Overdue & SPK/RKK Ditangguhkan Otomatis
                </span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <button class="btn btn-2xs btn-outline-danger text-nowrap" type="button" data-bs-toggle="collapse" data-bs-target="#ribbonDetailOverdue" aria-expanded="false">
                    <i class="fa-solid fa-chevron-down me-1"></i> Rincian Dokter
                </button>
                <a href="{{ route('komite.kredensial.index', ['status_kredensial' => 'ditangguhkan']) }}" class="btn btn-danger btn-2xs text-white text-nowrap">
                    Instrumen SPK &raquo;
                </a>
            </div>
        </div>
        <div class="collapse mt-2 pt-2 border-top border-danger-subtle" id="ribbonDetailOverdue">
            <p class="fs-xs text-dark mb-2">
                Masa berlaku SIP dokter berikut kedaluwarsa (&le; 0 hari) tanpa dispensasi aktif. Seluruh wewenang klinis (SPK & RKK) <strong>otomatis ditangguhkan</strong> guna melindungi fasyankes dan dokter dari risiko malpraktik:
            </p>
            <div class="d-flex flex-wrap gap-2">
                @foreach($dokterDitangguhkanList as $dokTangguh)
                    <span class="badge bg-white text-danger border border-danger-subtle p-2 fs-xs d-inline-flex align-items-center gap-1 shadow-2xs">
                        <i class="fa-solid fa-user-slash"></i>
                        <strong>{{ $dokTangguh->user->nama ?? '-' }}</strong> ({{ $dokTangguh->ksm->nama_ksm ?? '-' }}) &bull; SIP: {{ $dokTangguh->sipTerbaru?->nomor_sip ?? '-' }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- 3 KARTU METRIK ANTREAN KERJA RINGKAS SECARA HORIZONTAL -->
<div class="row g-3 mb-4">
    <!-- 1. Verifikasi STR Baru -->
    <div class="col-md-4">
        <a href="{{ route('komite.verifikasi_str.index') }}" class="text-decoration-none">
            <div class="card h-100 border-0 border-start border-4 border-primary shadow-xs hover-shadow transition-all bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary fs-xs fw-bold text-uppercase text-nowrap">1. Verifikasi STR Baru</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill fs-3xs"><i class="fa-solid fa-cloud me-1"></i> SatuSehat</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-2 fw-bolder text-primary">{{ $stats['strMenunggu'] ?? 0 }}</span>
                        <span class="text-muted fs-xs">Dokter Menunggu</span>
                    </div>
                    <div class="text-secondary fs-3xs mt-1 text-truncate">Validasi STR 16 digit & kunci master data</div>
                </div>
            </div>
        </a>
    </div>

    <!-- 2. Verifikasi Berkas SIP -->
    <div class="col-md-4">
        <a href="{{ route('komite.verifikasi.index') }}" class="text-decoration-none">
            <div class="card h-100 border-0 border-start border-4 border-teal shadow-xs hover-shadow transition-all bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary fs-xs fw-bold text-uppercase text-nowrap">2. Verifikasi Berkas SIP</span>
                        <span class="badge bg-teal-subtle text-teal rounded-pill fs-3xs"><i class="fa-solid fa-hourglass-half me-1"></i> Menunggu</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-2 fw-bolder text-teal">{{ $stats['sipMenunggu'] ?? 0 }}</span>
                        <span class="text-muted fs-xs">Berkas Perpanjangan</span>
                    </div>
                    <div class="text-secondary fs-3xs mt-1 text-truncate">Pemeriksaan fisik PDF & inisiasi alur rekredensialing</div>
                </div>
            </div>
        </a>
    </div>

    <!-- 3. Rekredensialing SPK/RKK -->
    <div class="col-md-4">
        <a href="{{ route('komite.kredensial.index', ['status_kredensial' => 'evaluasi']) }}" class="text-decoration-none">
            <div class="card h-100 border-0 border-start border-4 border-warning shadow-xs hover-shadow transition-all bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary fs-xs fw-bold text-uppercase text-nowrap">3. Rekredensialing SPK/RKK</span>
                        <span class="badge bg-warning-subtle text-warning rounded-pill fs-3xs"><i class="fa-solid fa-gavel me-1"></i> Siklus 3 Thn</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-2 fw-bolder text-warning-emphasis">{{ ($stats['kredensialEvaluasi'] ?? 0) + ($stats['kredensialProsesRekredensial'] ?? 0) }}</span>
                        <span class="text-muted fs-xs">Dokter Perlu Sidang</span>
                    </div>
                    <div class="text-secondary fs-3xs mt-1 text-truncate">Sidang evaluasi logbook Mitra Bestari & form RKK</div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Antrean Verifikasi Berkas SIP -->
    <div class="col-xl-6">
        <div class="card shadow-xs h-100 border-0 bg-white">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark fs-sm">
                    <i class="fa-solid fa-stamp text-teal me-2"></i>Antrean Verifikasi Berkas SIP Terbaru
                </div>
                <a href="{{ route('komite.verifikasi.index') }}" class="btn btn-outline-teal btn-2xs text-nowrap">Buka Antrean &raquo;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-sm table-compact">
                    <thead class="table-light">
                        <tr>
                            <th>Dokter & KSM</th>
                            <th>No. SIP</th>
                            <th class="text-nowrap" style="width: 85px;">Tgl Upload</th>
                            <th class="text-end text-nowrap" style="width: 70px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($antreanVerifikasi as $item)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark fs-xs text-truncate" style="max-width: 140px;" title="{{ $item->dokter->user->nama ?? '-' }}">
                                        {{ $item->dokter->user->nama ?? '-' }}
                                    </div>
                                    <small class="text-muted fs-3xs d-block text-truncate" style="max-width: 140px;">{{ $item->dokter->ksm->nama_ksm ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="font-monospace fs-3xs text-dark d-inline-block text-truncate" style="max-width: 110px;" title="{{ $item->nomor_sip }}">
                                        {{ $item->nomor_sip }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fs-3xs text-secondary text-nowrap">{{ $item->created_at->format('d/m/Y') }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('komite.verifikasi.show', $item->id) }}" class="btn btn-2xs btn-teal text-white text-nowrap">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i> Telaah
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted fs-xs">
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
    <div class="col-xl-6">
        <div class="card shadow-xs h-100 border-0 bg-white">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark fs-sm">
                    <i class="fa-solid fa-id-card-clip text-warning me-2"></i>Monitoring Evaluasi SPK & RKK (Siklus 3 Tahunan)
                </div>
                <a href="{{ route('komite.kredensial.index') }}" class="btn btn-outline-teal btn-2xs text-nowrap">Lihat Semua &raquo;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-sm table-compact">
                    <thead class="table-light">
                        <tr>
                            <th>Dokter</th>
                            <th>No. SPK</th>
                            <th class="text-nowrap" style="width: 85px;">Berakhir</th>
                            <th class="text-end text-nowrap" style="width: 95px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($spkEvaluasi as $spk)
                            @php $sisaSpk = $spk->hitungSisaHari(); @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark fs-xs text-truncate" style="max-width: 135px;" title="{{ $spk->dokter->user->nama ?? '-' }}">
                                        {{ $spk->dokter->user->nama ?? '-' }}
                                    </div>
                                    <small class="text-muted fs-3xs d-block text-truncate" style="max-width: 135px;">{{ $spk->dokter->ksm->kode_ksm ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="font-monospace fs-3xs text-dark d-inline-block text-truncate" style="max-width: 110px;" title="{{ $spk->nomor_spk }}">
                                        {{ $spk->nomor_spk }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fs-3xs text-secondary text-nowrap">
                                        {{ $spk->tanggal_berakhir_spk ? $spk->tanggal_berakhir_spk->format('d/m/Y') : '-' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="badge {{ $spk->status_kredensial === 'kedaluwarsa' ? 'bg-danger text-white' : 'bg-warning text-dark' }} fs-3xs text-nowrap">
                                        {{ $spk->status_kredensial === 'kedaluwarsa' ? 'Kedaluwarsa' : 'Evaluasi (' . $sisaSpk . ' hr)' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted fs-xs">
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
