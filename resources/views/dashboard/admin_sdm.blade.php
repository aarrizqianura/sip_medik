@extends('layouts.app')

@section('title', 'Dashboard Kepegawaian SDM')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Dashboard Kepatuhan & Masa Berlaku SIP (SDM)</h4>
        <p class="text-secondary fs-sm mb-0">Pemantauan kepatuhan legalitas praktik kedokteran untuk audit akreditasi KPS RSUD dr. H. Moch. Ansari Saleh</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('sdm.reminder.index') }}" class="btn btn-outline-success btn-sm d-flex align-items-center gap-2">
            <i class="fa-brands fa-whatsapp"></i>
            <span>Blast Reminder WA</span>
        </a>
        <a href="{{ route('sdm.laporan.index') }}" class="btn btn-teal btn-sm text-white d-flex align-items-center gap-2">
            <i class="fa-solid fa-file-pdf"></i>
            <span>Cetak 5 Jenis Laporan</span>
        </a>
    </div>
</div>

<!-- 4 Key Metrics Cards -->
<!-- 5 Key Metrics Cards (Tahap 5: Early Warning Engine Tiers) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl">
        <div class="card h-100 border-start border-4 border-emerald shadow-xs">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">Sisa >90 Hari</span>
                    <span class="status-pill status-aktif fs-xs"><i class="fa-solid fa-check me-1"></i> Aktif</span>
                </div>
                <div class="fs-2 fw-bold text-success">{{ $stats['sipAktif'] ?? 0 }}</div>
                <div class="text-muted fs-xs mt-1">Status aman, tanpa pesan</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl">
        <div class="card h-100 border-start border-4 border-warning shadow-xs">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">H-90 s.d H-60</span>
                    <span class="status-pill status-due_soon fs-xs"><i class="fa-solid fa-clock me-1"></i> Due Soon</span>
                </div>
                <div class="fs-2 fw-bold text-warning-emphasis">{{ $stats['sipDueSoon'] ?? 0 }}</div>
                <div class="text-muted fs-xs mt-1">Pengingat SKP SatuSehat dokter</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl">
        <div class="card h-100 border-start border-4 shadow-xs" style="border-left-color: #f97316 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">H-30 (Due)</span>
                    <span class="status-pill status-due fs-xs"><i class="fa-solid fa-bell me-1"></i> Mendesak</span>
                </div>
                <div class="fs-2 fw-bold text-orange" style="color: #ea580c !important;">{{ $stats['sipDue'] ?? 0 }}</div>
                <div class="text-muted fs-xs mt-1">Peringatan dokter & salinan SDM</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl">
        <div class="card h-100 border-start border-4 shadow-xs" style="border-left-color: #c2410c !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">H-7 (Critical)</span>
                    <span class="status-pill status-critical fs-xs"><i class="fa-solid fa-radiation me-1"></i> Kritis</span>
                </div>
                <div class="fs-2 fw-bold" style="color: #c2410c !important;">{{ $stats['sipCritical'] ?? 0 }}</div>
                <div class="text-muted fs-xs mt-1">Eskalasi berjenjang Dokter & SDM</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl">
        <div class="card h-100 border-start border-4 border-danger shadow-xs">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">Overdue</span>
                    <span class="badge bg-danger text-white rounded-pill"><i class="fa-solid fa-ban"></i> Stop</span>
                </div>
                <div class="fs-2 fw-bold text-danger">{{ $stats['sipOverdue'] ?? 0 }}</div>
                <div class="text-muted fs-xs mt-1">Wajib perpanjangan segera</div>
            </div>
        </div>
    </div>
</div>

<!-- Widget Tahap 4: Pengesahan Direksi & e-Arsip Kepegawaian -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-xs border-success h-100 bg-success-subtle">
            <div class="card-body p-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="fs-xs fw-bold text-success text-uppercase">Hak Praktik AKTIF_LEGAL</span>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['dokterAktifLegal'] ?? 0 }}</h3>
                    <div class="fs-xs text-secondary mt-1">Kewenangan penuh & SPK aktif 3 tahun</div>
                </div>
                <div class="avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                    <i class="fa-solid fa-shield-check fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-xs border-warning h-100 bg-warning-subtle">
            <div class="card-body p-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="fs-xs fw-bold text-warning-emphasis text-uppercase">Siap Disahkan Direktur</span>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['dokterMenungguPengesahan'] ?? 0 }}</h3>
                    <div class="fs-xs text-secondary mt-1">
                        <a href="{{ route('sdm.pengesahan.index') }}" class="text-dark fw-bold text-decoration-none">
                            Terbitkan SK Direktur &raquo;
                        </a>
                    </div>
                </div>
                <div class="avatar bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                    <i class="fa-solid fa-stamp fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-xs border-teal h-100 bg-teal-subtle">
            <div class="card-body p-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="fs-xs fw-bold text-teal text-uppercase">e-Arsip Kepegawaian Terkunci</span>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['arsipTerkunci'] ?? 0 }}</h3>
                    <div class="fs-xs text-secondary mt-1">
                        <a href="{{ route('sdm.pengesahan.index', ['tab' => 'semua']) }}" class="text-teal fw-bold text-decoration-none">
                            Buka Repositori e-Arsip &raquo;
                        </a>
                    </div>
                </div>
                <div class="avatar bg-teal text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                    <i class="fa-solid fa-box-archive fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Widget Tahap 6: Alur Masa Tenggang Administratif (Grace Period / On-Process) -->
<div class="card shadow-xs border-teal mb-4 overflow-hidden">
    <div class="card-body p-3 bg-light-teal d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar bg-teal text-white rounded-circle d-flex align-items-center justify-content-center shadow-2xs" style="width: 44px; height: 44px;">
                <i class="fa-solid fa-business-time fs-4"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-teal text-white fs-3xs">TAHAP 6: GRACE PERIOD</span>
                    <strong class="text-dark fs-sm">Alur Masa Tenggang Administratif & Proteksi Pelayanan</strong>
                </div>
                <div class="fs-xs text-secondary mt-1">
                    <span class="badge bg-warning text-dark me-1">{{ $stats['dispensasiDiajukan'] ?? 0 }} Menunggu Telaah</span>
                    <span class="badge bg-teal text-white me-1">{{ $stats['dispensasiAktif'] ?? 0 }} Grace Period Aktif</span>
                    Pelayanan medis dan wewenang klinis (SPK/RKK) terproteksi penuh selama masa proses perizinan di DPMPTSP/Dinkes.
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('sdm.dispensasi.index') }}" class="btn btn-teal text-white btn-sm shadow-2xs d-inline-flex align-items-center gap-1 text-nowrap">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Kelola Masa Tenggang</span>
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Matriks Early Warning -->
    <div class="col-lg-8">
        <div class="card shadow-xs">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark">
                    <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>
                    Matriks SIP Kritis & Mendekati Jatuh Tempo (H-30 s.d Overdue)
                </div>
                <a href="{{ route('sdm.reminder.index') }}" class="btn btn-outline-secondary btn-sm fs-xs">Ke Matriks Lengkap</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Dokter</th>
                            <th>KSM</th>
                            <th>No. SIP</th>
                            <th>Jatuh Tempo</th>
                            <th>Sisa Hari</th>
                            <th>Status</th>
                            <th>Kirim WA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dokumenKritis as $sip)
                            @php $sisa = $sip->hitungSisaHari(); @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $sip->dokter->user->nama ?? '-' }}</div>
                                    <small class="text-muted">NIP: {{ $sip->dokter->nip_nrp ?? '-' }}</small>
                                </td>
                                <td>{{ $sip->dokter->ksm->kode_ksm ?? '-' }}</td>
                                <td><span class="font-monospace fs-xs">{{ $sip->nomor_sip }}</span></td>
                                <td>{{ $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</td>
                                <td>
                                    @if($sisa < 0)
                                        <span class="countdown-badge bg-danger text-white">
                                            <i class="fa-solid fa-ban me-1"></i> Kedaluwarsa ({{ abs($sisa) }} hr)
                                        </span>
                                    @elseif($sisa <= 7)
                                        <span class="countdown-badge bg-critical text-white" style="background-color: #ea580c !important;">
                                            <i class="fa-solid fa-radiation me-1"></i> H-{{ $sisa }} Kritis
                                        </span>
                                    @elseif($sisa <= 30)
                                        <span class="countdown-badge bg-due text-white" style="background-color: #f97316 !important;">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> H-{{ $sisa }} Mendesak
                                        </span>
                                    @elseif($sisa <= 90)
                                        <span class="countdown-badge bg-due-soon text-dark" style="background-color: #fef08a !important; color: #854d0e !important;">
                                            <i class="fa-solid fa-clock me-1"></i> H-{{ $sisa }} Hari
                                        </span>
                                    @else
                                        <span class="countdown-badge bg-safe text-white" style="background-color: #10b981 !important;">
                                            <i class="fa-solid fa-circle-check me-1"></i> {{ $sisa }} Hari
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($sip->status === 'critical')
                                        <span class="status-pill status-critical fs-xs">
                                            <i class="fa-solid fa-radiation me-1"></i> Eskalasi Kritis (H-7)
                                        </span>
                                    @elseif($sip->status === 'due')
                                        <span class="status-pill status-due fs-xs">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Peringatan Mendesak (H-30)
                                        </span>
                                    @elseif($sip->status === 'due_soon')
                                        <span class="status-pill status-due_soon fs-xs">
                                            <i class="fa-solid fa-clock me-1"></i> Due Soon (H-90 s.d H-60)
                                        </span>
                                    @elseif($sip->status === 'overdue')
                                        <span class="status-pill status-overdue fs-xs">
                                            <i class="fa-solid fa-ban me-1"></i> Kedaluwarsa
                                        </span>
                                    @else
                                        <span class="status-pill status-aktif fs-xs">
                                            <i class="fa-solid fa-circle-check me-1"></i> Aktif (>90 Hari)
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('sdm.reminder.kirim', $sip->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Kirim Pengingat WhatsApp Manual">
                                            <i class="fa-brands fa-whatsapp me-1"></i> Kirim
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i> Tidak ada staf medis dalam status kritis saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Links to Reports & KSM Distribution -->
    <div class="col-lg-4">
        <div class="card shadow-xs mb-4">
            <div class="card-header fw-bold text-dark">
                <i class="fa-solid fa-file-waveform text-teal me-2"></i>
                Pintasan Laporan KPS Akreditasi
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush fs-sm">
                    <a href="{{ route('sdm.laporan.index', ['jenis' => '1']) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between">
                        <span>1. Data Staf Medis & Status SIP</span>
                        <i class="fa-solid fa-chevron-right text-muted fs-xs"></i>
                    </a>
                    <a href="{{ route('sdm.laporan.index', ['jenis' => '2']) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between">
                        <span>2. Monitoring Audit KPS (Masa Berlaku)</span>
                        <i class="fa-solid fa-chevron-right text-muted fs-xs"></i>
                    </a>
                    <a href="{{ route('sdm.laporan.index', ['jenis' => '3']) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between">
                        <span>3. Verifikasi Dokumen Komite</span>
                        <i class="fa-solid fa-chevron-right text-muted fs-xs"></i>
                    </a>
                    <a href="{{ route('sdm.laporan.index', ['jenis' => '4']) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between">
                        <span>4. Masa Berlaku SPK & RKK</span>
                        <i class="fa-solid fa-chevron-right text-muted fs-xs"></i>
                    </a>
                    <a href="{{ route('sdm.laporan.index', ['jenis' => '5']) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between">
                        <span>5. Riwayat Notifikasi Pengingat</span>
                        <i class="fa-solid fa-chevron-right text-muted fs-xs"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow-xs">
            <div class="card-header fw-bold text-dark">
                <i class="fa-solid fa-sitemap text-teal me-2"></i>
                Rekapitulasi KSM
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush fs-sm">
                    @foreach($ksmStatistik->take(5) as $ksm)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                            <span>{{ $ksm->nama_ksm }}</span>
                            <span class="badge bg-teal-subtle text-teal rounded-pill">{{ $ksm->dokters_count }} Dokter</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
