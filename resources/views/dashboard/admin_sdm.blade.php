@extends('layouts.app')

@section('title', 'Dashboard Kepegawaian SDM')

@section('content')
<!-- Header Area -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Dashboard Kepatuhan & Masa Berlaku SIP (SDM)</h4>
        <p class="text-secondary fs-sm mb-0">Pemantauan kepatuhan legalitas praktik kedokteran untuk audit akreditasi KPS RSUD dr. H. Moch. Ansari Saleh</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('sdm.reminder.index') }}" class="btn btn-outline-success btn-sm text-nowrap d-flex align-items-center gap-2">
            <i class="fa-brands fa-whatsapp"></i>
            <span>Blast Reminder WA</span>
        </a>
        <a href="{{ route('sdm.laporan.index') }}" class="btn btn-teal btn-sm text-white text-nowrap d-flex align-items-center gap-2">
            <i class="fa-solid fa-file-pdf"></i>
            <span>Cetak 5 Jenis Laporan</span>
        </a>
    </div>
</div>

@if(isset($dokterDitangguhkanList) && $dokterDitangguhkanList->isNotEmpty())
    <div class="alert alert-danger border-start border-4 border-danger shadow-xs p-3 mb-4 rounded-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-danger text-white"><i class="fa-solid fa-triangle-exclamation me-1"></i> ESKALASI DARURAT BAGIAN SDM</span>
                <h6 class="fw-bold text-danger mb-0">Mitigasi Otomatis: {{ $dokterDitangguhkanList->count() }} Dokter Masuk Staf Medis Non-Aktif Pelayanan</h6>
            </div>
            <a href="{{ route('sdm.laporan.index', ['jenis' => '2', 'status' => 'overdue']) }}" class="btn btn-danger btn-sm fs-xs text-nowrap">
                <i class="fa-solid fa-file-shield me-1"></i> Buka Monitoring Pokja KPS
            </a>
        </div>
        <p class="fs-xs text-dark mb-2">
            Sistem mendeteksi masa berlaku SIP dokter telah kedaluwarsa tanpa dispensasi aktif. Status instrumen <strong>SPK dan RKK beralih menjadi DITANGGUHKAN</strong> dan dokter <strong>terblokir administratif dari wewenang tindakan klinis mandiri</strong> untuk melindungi rumah sakit dari temuan audit dan risiko malpraktik.
        </p>
        <div class="d-flex flex-wrap gap-2 mt-2">
            @foreach($dokterDitangguhkanList as $dokTangguh)
                <span class="badge bg-white text-danger border border-danger-subtle p-2 fs-xs d-inline-flex align-items-center gap-2 shadow-2xs">
                    <i class="fa-solid fa-user-xmark"></i>
                    <strong>{{ $dokTangguh->user->nama ?? '-' }}</strong> ({{ $dokTangguh->ksm->nama_ksm ?? '-' }}) &bull; Exp: {{ $dokTangguh->sipTerbaru?->tanggal_kadaluarsa ? $dokTangguh->sipTerbaru->tanggal_kadaluarsa->format('d/m/Y') : '-' }} &bull; <em>Wewenang Terblokir</em>
                </span>
            @endforeach
        </div>
    </div>
@endif

<!-- 5 Key Metrics Cards (Early Warning Engine Tiers) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg col-xl">
        <div class="card h-100 border-0 border-start border-4 border-success shadow-xs bg-white hover-shadow">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-bold text-uppercase text-nowrap">Sisa >90 Hari</span>
                    <span class="status-pill status-aktif fs-3xs text-nowrap"><i class="fa-solid fa-check me-1"></i> Aman</span>
                </div>
                <div class="fs-2 fw-bolder text-success">{{ $stats['sipAktif'] ?? 0 }}</div>
                <div class="text-muted fs-3xs mt-1 text-truncate">Status aman tanpa peringatan</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg col-xl">
        <div class="card h-100 border-0 border-start border-4 border-warning shadow-xs bg-white hover-shadow">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-bold text-uppercase text-nowrap">H-90 s.d H-60</span>
                    <span class="status-pill status-due_soon fs-3xs text-nowrap"><i class="fa-solid fa-clock me-1"></i> Due Soon</span>
                </div>
                <div class="fs-2 fw-bolder text-warning-emphasis">{{ $stats['sipDueSoon'] ?? 0 }}</div>
                <div class="text-muted fs-3xs mt-1 text-truncate">Pengingat SKP SatuSehat</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg col-xl">
        <div class="card h-100 border-0 border-start border-4 shadow-xs bg-white hover-shadow" style="border-left-color: #f97316 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-bold text-uppercase text-nowrap">H-30 (Due)</span>
                    <span class="status-pill status-due fs-3xs text-nowrap"><i class="fa-solid fa-bell me-1"></i> Mendesak</span>
                </div>
                <div class="fs-2 fw-bolder" style="color: #ea580c !important;">{{ $stats['sipDue'] ?? 0 }}</div>
                <div class="text-muted fs-3xs mt-1 text-truncate">Peringatan dokter & SDM</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg col-xl">
        <div class="card h-100 border-0 border-start border-4 shadow-xs bg-white hover-shadow" style="border-left-color: #c2410c !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-bold text-uppercase text-nowrap">H-7 (Critical)</span>
                    <span class="status-pill status-critical fs-3xs text-nowrap"><i class="fa-solid fa-triangle-exclamation me-1"></i> Kritis</span>
                </div>
                <div class="fs-2 fw-bolder" style="color: #c2410c !important;">{{ $stats['sipCritical'] ?? 0 }}</div>
                <div class="text-muted fs-3xs mt-1 text-truncate">Eskalasi darurat berjenjang</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg col-xl">
        <div class="card h-100 border-0 border-start border-4 border-danger shadow-xs bg-white hover-shadow">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-bold text-uppercase text-nowrap">Overdue</span>
                    <span class="badge bg-danger text-white rounded-pill fs-3xs text-nowrap"><i class="fa-solid fa-ban me-1"></i> Stop</span>
                </div>
                <div class="fs-2 fw-bolder text-danger">{{ $stats['sipOverdue'] ?? 0 }}</div>
                <div class="text-muted fs-3xs mt-1 text-truncate">Wajib perpanjangan segera</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Operational Grid: Matriks Kritis & Pintasan Laporan -->
<div class="row g-4 mb-4">
    <!-- Matriks Early Warning Kritis (H-30 s.d Overdue) -->
    <div class="col-xl-8">
        <div class="card shadow-xs border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark fs-sm">
                    <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>
                    Matriks SIP Kritis & Mendekati Jatuh Tempo (H-30 s.d Overdue)
                </div>
                <a href="{{ route('sdm.reminder.index') }}" class="btn btn-outline-teal btn-2xs text-nowrap">Buka Matriks Lengkap &raquo;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-sm table-compact">
                    <thead class="table-light">
                        <tr>
                            <th>Dokter & Legalitas SIP</th>
                            <th class="text-nowrap" style="width: 100px;">Jatuh Tempo</th>
                            <th class="text-nowrap" style="width: 145px;">Sisa & Status</th>
                            <th class="text-end text-nowrap" style="width: 70px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dokumenKritis as $sip)
                            @php $sisa = $sip->hitungSisaHari(); @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark fs-xs text-truncate" style="max-width: 240px;" title="{{ $sip->dokter->user->nama ?? '-' }}">
                                        {{ $sip->dokter->user->nama ?? '-' }}
                                    </div>
                                    <div class="d-flex align-items-center gap-1 mt-1">
                                        <span class="badge bg-light text-secondary border fs-3xs flex-shrink-0">{{ $sip->dokter->ksm->kode_ksm ?? '-' }}</span>
                                        <span class="font-monospace fs-3xs text-muted text-truncate" style="max-width: 180px;" title="{{ $sip->nomor_sip }}">{{ $sip->nomor_sip }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fs-3xs text-secondary text-nowrap">
                                        {{ $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column align-items-start gap-1">
                                        @if($sisa < 0)
                                            <span class="badge bg-danger text-white fs-3xs text-nowrap">Kedaluwarsa ({{ abs($sisa) }} hr)</span>
                                        @elseif($sisa <= 7)
                                            <span class="badge bg-danger text-white fs-3xs text-nowrap">H-{{ $sisa }} Kritis</span>
                                        @elseif($sisa <= 30)
                                            <span class="badge text-white fs-3xs text-nowrap" style="background-color: #ea580c !important;">H-{{ $sisa }} Mendesak</span>
                                        @else
                                            <span class="badge bg-warning text-dark fs-3xs text-nowrap">H-{{ $sisa }} Hari</span>
                                        @endif
                                        <span class="status-pill status-{{ $sip->status }} fs-3xs text-nowrap py-0 px-2">
                                            {{ $sip->status_label }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('sdm.reminder.kirim', $sip->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-2xs btn-outline-success text-nowrap" title="Kirim Pengingat WhatsApp Manual">
                                            <i class="fa-brands fa-whatsapp me-1"></i> WA
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted fs-xs">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i> Tidak ada staf medis dalam status kritis saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pintasan Laporan KPS & Grace Period -->
    <div class="col-xl-4">
        <!-- Card Grace Period Quick Info -->
        <div class="card shadow-xs border-0 bg-white mb-3">
            <div class="card-header bg-light-teal py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-1 overflow-hidden me-2">
                    <span class="badge bg-teal text-white fs-3xs flex-shrink-0">TAHAP 6</span>
                    <span class="fw-bold text-teal fs-xs text-truncate">Dispensasi & Grace Period</span>
                </div>
                <a href="{{ route('sdm.dispensasi.index') }}" class="btn btn-2xs btn-teal text-white text-nowrap flex-shrink-0">Kelola &raquo;</a>
            </div>
            <div class="card-body p-3">
                <div class="row text-center g-2">
                    <div class="col-6 border-end">
                        <div class="fs-3 fw-bolder text-warning-emphasis">{{ $stats['dispensasiDiajukan'] ?? 0 }}</div>
                        <small class="text-muted fs-3xs d-block">Menunggu Telaah</small>
                    </div>
                    <div class="col-6">
                        <div class="fs-3 fw-bolder text-teal">{{ $stats['dispensasiAktif'] ?? 0 }}</div>
                        <small class="text-muted fs-3xs d-block">Grace Period Aktif</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5 Format Laporan Akreditasi KPS Links -->
        <div class="card shadow-xs border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <span class="fw-bold text-dark fs-sm text-truncate me-2">
                    <i class="fa-solid fa-file-waveform text-teal me-1"></i>Pintasan 5 Laporan KPS
                </span>
                <a href="{{ route('sdm.laporan.index') }}" class="btn btn-outline-teal btn-2xs text-nowrap flex-shrink-0">Semua &raquo;</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush fs-xs">
                    <a href="{{ route('sdm.laporan.index', ['jenis' => '1']) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-secondary border fs-3xs">1</span>
                            <span class="text-dark">Data Staf Medis & Status SIP</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-muted fs-3xs"></i>
                    </a>
                    <a href="{{ route('sdm.laporan.index', ['jenis' => '2']) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-secondary border fs-3xs">2</span>
                            <span class="text-dark">Monitoring Audit KPS (Masa Berlaku)</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-muted fs-3xs"></i>
                    </a>
                    <a href="{{ route('sdm.laporan.index', ['jenis' => '3']) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-secondary border fs-3xs">3</span>
                            <span class="text-dark">Verifikasi Dokumen Komite</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-muted fs-3xs"></i>
                    </a>
                    <a href="{{ route('sdm.laporan.index', ['jenis' => '4']) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-secondary border fs-3xs">4</span>
                            <span class="text-dark">Masa Berlaku SPK & RKK</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-muted fs-3xs"></i>
                    </a>
                    <a href="{{ route('sdm.laporan.index', ['jenis' => '5']) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-secondary border fs-3xs">5</span>
                            <span class="text-dark">Riwayat Notifikasi Pengingat</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-muted fs-3xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABEL RINGKASAN KEPATUHAN PER KSM -->
<div class="card shadow-xs border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <h6 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-chart-pie text-teal me-2"></i>Tabel Ringkasan Kepatuhan Legalitas per Kelompok Staf Medis (KSM)
            </h6>
            <small class="text-secondary fs-xs">
                Indikator kepatuhan SIP & SPK staf medis untuk pemenuhan standar Pokja Kualifikasi dan Pendidikan Staf (KPS)
            </small>
        </div>
        <a href="{{ route('sdm.ksm.index') }}" class="btn btn-outline-teal btn-sm fs-xs text-nowrap">
            Kelola Master KSM &raquo;
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm table-compact">
            <thead class="table-light">
                <tr>
                    <th style="min-width: 220px;">Kelompok Staf Medis (KSM)</th>
                    <th class="text-center" style="width: 100px;">Total Dokter</th>
                    <th class="text-center" style="width: 110px;">SIP Absah Aktif</th>
                    <th class="text-center" style="width: 110px;">Grace Period</th>
                    <th class="text-center" style="width: 120px;">Kritis / Overdue</th>
                    <th style="min-width: 200px;">Tingkat Kepatuhan KPS</th>
                    <th class="text-end" style="width: 100px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ksmStatistik as $ksm)
                    @php
                        $totalDokterKsm = $ksm->dokters_count;
                        $aktifCount = 0;
                        $graceCount = 0;
                        $kritisCount = 0;

                        foreach ($ksm->dokters as $dok) {
                            $sip = $dok->sipTerbaru;
                            if (!$sip) {
                                $kritisCount++;
                            } elseif ($sip->isGracePeriodAktif()) {
                                $graceCount++;
                                $aktifCount++;
                            } elseif ($sip->status === 'aktif' || $sip->status === 'due_soon') {
                                $aktifCount++;
                            } elseif (in_array($sip->status, ['due', 'critical', 'overdue'])) {
                                $kritisCount++;
                            }
                        }

                        $persentase = $totalDokterKsm > 0 ? round(($aktifCount / $totalDokterKsm) * 100) : 100;
                        $barColor = $persentase >= 80 ? 'bg-success' : ($persentase >= 50 ? 'bg-warning' : 'bg-danger');
                        $badgeKepatuhan = $persentase >= 80 
                            ? 'bg-success-subtle text-success border border-success' 
                            : ($persentase >= 50 ? 'bg-warning-subtle text-warning-emphasis border border-warning' : 'bg-danger-subtle text-danger border border-danger');
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold text-dark fs-xs">{{ $ksm->nama_ksm }}</div>
                            <small class="text-muted font-monospace fs-3xs">Kode: {{ $ksm->kode_ksm }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $totalDokterKsm > 0 ? 'bg-teal-subtle text-teal' : 'bg-light text-muted' }} border fs-3xs px-2">
                                {{ $totalDokterKsm }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="text-success fw-bold fs-xs">{{ $aktifCount }}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-teal fw-bold fs-xs">{{ $graceCount }}</span>
                        </td>
                        <td class="text-center">
                            <span class="{{ $kritisCount > 0 ? 'text-danger fw-bold' : 'text-muted' }} fs-xs">
                                {{ $kritisCount }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ $persentase }}%;" aria-valuenow="{{ $persentase }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="fs-xs fw-bold text-dark text-nowrap" style="min-width: 36px;">{{ $persentase }}%</span>
                            </div>
                        </td>
                        <td class="text-end">
                            <span class="badge {{ $badgeKepatuhan }} fs-3xs text-nowrap">
                                {{ $persentase >= 80 ? 'Tertib' : ($persentase >= 50 ? 'Perhatian' : 'Kritis') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted fs-xs">Belum ada master KSM terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
