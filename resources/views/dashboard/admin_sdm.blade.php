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
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-4 border-emerald shadow-xs">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">SIP Absah (>90 Hari)</span>
                    <span class="badge bg-success-subtle text-success rounded-pill"><i class="fa-solid fa-check"></i> Aktif</span>
                </div>
                <div class="fs-2 fw-bold text-dark">{{ $stats['sipAktif'] }}</div>
                <div class="text-muted fs-xs mt-1">Dokter siap praktik mandiri</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-4 border-warning shadow-xs">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">Due Soon (31-90 Hari)</span>
                    <span class="badge bg-warning-subtle text-warning rounded-pill"><i class="fa-solid fa-clock"></i> Peringatan</span>
                </div>
                <div class="fs-2 fw-bold text-warning">{{ $stats['sipDueSoon'] }}</div>
                <div class="text-muted fs-xs mt-1">Pemberitahuan perpanjangan DPMPTSP</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-4 border-orange shadow-xs" style="border-left-color: #ea580c !important;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">Kritis / Due (≤30 Hari)</span>
                    <span class="badge bg-danger-subtle text-danger rounded-pill"><i class="fa-solid fa-bell"></i> Kritis</span>
                </div>
                <div class="fs-2 fw-bold text-danger">{{ $stats['sipDue'] }}</div>
                <div class="text-muted fs-xs mt-1">Peringatan jatuh tempo segera</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-4 border-danger shadow-xs">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">Kedaluwarsa (Overdue)</span>
                    <span class="badge bg-danger text-white rounded-pill"><i class="fa-solid fa-ban"></i> Stop Layanan</span>
                </div>
                <div class="fs-2 fw-bold text-danger">{{ $stats['sipOverdue'] }}</div>
                <div class="text-muted fs-xs mt-1">Wajib proses perpanjangan segera</div>
            </div>
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
                                    <span class="countdown-badge {{ $sisa < 0 ? 'bg-danger text-white' : ($sisa <= 30 ? 'bg-warning text-dark' : 'bg-info text-white') }}">
                                        {{ $sisa < 0 ? 'Kedaluwarsa (' . abs($sisa) . ' hr)' : $sisa . ' Hari' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-pill status-{{ $sip->status }}">{{ $sip->status_label }}</span>
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
