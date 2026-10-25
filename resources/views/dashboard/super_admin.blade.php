@extends('layouts.app')

@section('title', 'Dashboard Super Admin (IT PDE)')

@section('content')
<!-- Header Area -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Dashboard Eksekutif & Administrator IT PDE</h4>
        <p class="text-secondary fs-sm mb-0">Pemantauan menyeluruh sistem, gateway notifikasi, dan kepatuhan SIP dokter RSUD dr. H. Moch. Ansari Saleh</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <form action="{{ route('admin.pengaturan.trigger_cron') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-outline-teal btn-sm text-nowrap d-flex align-items-center gap-2">
                <i class="fa-solid fa-arrows-rotate"></i>
                <span>Jalankan Scheduler Harian</span>
            </button>
        </form>
        <a href="{{ route('admin.pengaturan.index') }}" class="btn btn-teal btn-sm text-white text-nowrap d-flex align-items-center gap-2">
            <i class="fa-solid fa-sliders"></i>
            <span>Konfigurasi Sistem</span>
        </a>
    </div>
</div>

@if(isset($dokterDitangguhkanList) && $dokterDitangguhkanList->isNotEmpty())
<div class="alert alert-danger border-start border-4 border-danger shadow-xs p-3 mb-4 rounded-3">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-danger text-white"><i class="fa-solid fa-bell me-1"></i> ESKALASI DARURAT DIREKSI</span>
            <h6 class="fw-bold text-danger mb-0">Mitigasi Otomatis (Tahap 7): {{ $dokterDitangguhkanList->count() }} Staf Medis Ditangguhkan Wewenang Klinis</h6>
        </div>
        <a href="{{ route('sdm.laporan.index', ['jenis' => '2']) }}" class="btn btn-danger btn-sm fs-xs text-nowrap">
            <i class="fa-solid fa-file-shield me-1"></i> Buka Laporan Akreditasi KPS
        </a>
    </div>
    <p class="fs-sm text-dark mb-2">
        Sistem mendeteksi staf medis yang masa berlaku SIP-nya telah kedaluwarsa (&le; 0 hari) tanpa dispensasi aktif. Sesuai standar tata kelola medis rumah sakit & standar Pokja KPS, <strong>status instrumen SPK dan RKK secara otomatis beralih menjadi DITANGGUHKAN</strong> dan dokter <strong>terblokir secara administratif dari wewenang tindakan klinis mandiri</strong> guna melindungi fasyankes dari tuntutan hukum/malpraktik.
    </p>
    <div class="d-flex flex-wrap gap-2 mt-2">
        @foreach($dokterDitangguhkanList as $dokTangguh)
            <span class="badge bg-white text-danger border border-danger-subtle p-2 fs-xs d-inline-flex align-items-center gap-2 shadow-2xs">
                <i class="fa-solid fa-user-xmark"></i>
                <strong>{{ $dokTangguh->user->nama ?? '-' }}</strong> ({{ $dokTangguh->ksm->nama_ksm ?? '-' }}) &bull; SIP Exp: {{ $dokTangguh->sipTerbaru?->tanggal_kadaluarsa ? $dokTangguh->sipTerbaru->tanggal_kadaluarsa->format('d/m/Y') : '-' }} &bull; <em>Wewenang Mandiri Terblokir</em>
            </span>
        @endforeach
    </div>
</div>
@endif

<!-- 4 Key Metrics Cards (Early Warning Engine) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 border-start border-4 border-success shadow-xs bg-white hover-shadow">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-bold text-uppercase">SIP Aktif (>90 Hari)</span>
                    <span class="status-pill status-aktif fs-3xs"><i class="fa-solid fa-check me-1"></i> Absah</span>
                </div>
                <div class="fs-2 fw-bolder text-success">{{ $stats['sipAktif'] ?? 0 }}</div>
                <div class="text-muted fs-3xs mt-1">Dokumen izin praktik aman tanpa peringatan</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 border-start border-4 border-warning shadow-xs bg-white hover-shadow">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-bold text-uppercase">Due Soon (31-90 Hari)</span>
                    <span class="status-pill status-due_soon fs-3xs"><i class="fa-solid fa-clock me-1"></i> Warning</span>
                </div>
                <div class="fs-2 fw-bolder text-warning-emphasis">{{ $stats['sipDueSoon'] ?? 0 }}</div>
                <div class="text-muted fs-3xs mt-1">Perlu persiapan berkas perpanjangan</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 border-start border-4 shadow-xs bg-white hover-shadow" style="border-left-color: #f97316 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-bold text-uppercase">Kritis / Due (≤30 Hari)</span>
                    <span class="status-pill status-due fs-3xs"><i class="fa-solid fa-bell me-1"></i> Mendesak</span>
                </div>
                <div class="fs-2 fw-bolder" style="color: #ea580c !important;">{{ $stats['sipDue'] ?? 0 }}</div>
                <div class="text-muted fs-3xs mt-1">Peringatan intensif dokter & bagian SDM</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 border-start border-4 border-danger shadow-xs bg-white hover-shadow">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-bold text-uppercase">Kadaluarsa (Overdue)</span>
                    <span class="badge bg-danger text-white rounded-pill fs-3xs"><i class="fa-solid fa-ban me-1"></i> Stop Praktik</span>
                </div>
                <div class="fs-2 fw-bolder text-danger">{{ $stats['sipOverdue'] ?? 0 }}</div>
                <div class="text-muted fs-3xs mt-1">Pelanggaran administratif Pokja KPS</div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Operational Status Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card bg-white shadow-xs border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-3 bg-primary-subtle text-primary rounded-3 flex-shrink-0">
                    <i class="fa-solid fa-user-doctor fs-4"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-secondary fs-xs fw-semibold text-uppercase">Total Dokter</div>
                    <div class="fs-4 fw-bolder text-dark">{{ $stats['totalDokter'] ?? 0 }}</div>
                    <small class="text-muted fs-3xs">Terdaftar aktif di SIM-SIP</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card bg-white shadow-xs border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-3 bg-info-subtle text-info rounded-3 flex-shrink-0">
                    <i class="fa-solid fa-users fs-4"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-secondary fs-xs fw-semibold text-uppercase">Akun Pengguna</div>
                    <div class="fs-4 fw-bolder text-dark">{{ $totalUser ?? 0 }}</div>
                    <small class="text-muted fs-3xs">Hak akses RBAC aktif</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card bg-white shadow-xs border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-3 bg-warning-subtle text-warning-emphasis rounded-3 flex-shrink-0">
                    <i class="fa-solid fa-hourglass-half fs-4"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-secondary fs-xs fw-semibold text-uppercase">Antrean Verifikasi</div>
                    <div class="fs-4 fw-bolder text-dark">{{ $stats['sipMenunggu'] ?? 0 }}</div>
                    <small class="text-muted fs-3xs">Menunggu telaah komite</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card bg-white shadow-xs border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-3 bg-danger-subtle text-danger rounded-3 flex-shrink-0">
                    <i class="fa-solid fa-certificate fs-4"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-secondary fs-xs fw-semibold text-uppercase">SPK Kadaluarsa</div>
                    <div class="fs-4 fw-bolder text-dark">{{ $stats['kredensialKadaluarsa'] ?? 0 }}</div>
                    <small class="text-muted fs-3xs">Siklus 3 tahunan berakhir</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Desktop 2-Column Content Layout -->
<div class="row g-4">
    <!-- Left Column: Tables & Workflows -->
    <div class="col-xl-8">
        <!-- Matriks SIP Kritis & Mendekati Jatuh Tempo -->
        <div class="card shadow-xs border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark fs-sm">
                    <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>
                    Matriks SIP Kritis & Mendekati Jatuh Tempo (H-30 s.d Overdue)
                </div>
                <a href="{{ route('sdm.reminder.index') }}" class="btn btn-outline-teal btn-2xs text-nowrap">Lihat Matriks Lengkap &raquo;</a>
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
                                    <i class="fa-solid fa-circle-check text-success me-1"></i> Tidak ada dokumen SIP dalam status kritis saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Antrean Pengajuan Verifikasi Komite -->
        <div class="card shadow-xs border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark fs-sm">
                    <i class="fa-solid fa-hourglass-start text-teal me-2"></i>
                    Antrean Verifikasi Berkas Masuk (Subkomite Kredensial)
                </div>
                <a href="{{ route('komite.verifikasi.index') }}" class="btn btn-outline-teal btn-2xs text-nowrap">Buka Halaman Telaah &raquo;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-sm table-compact">
                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap" style="width: 105px;">Tgl Upload</th>
                            <th>Nama Dokter</th>
                            <th>KSM</th>
                            <th>No. SIP Baru</th>
                            <th class="text-end text-nowrap" style="width: 70px;">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($antreanVerifikasi as $verif)
                            <tr>
                                <td>
                                    <span class="fs-3xs text-secondary text-nowrap">{{ $verif->created_at->format('d/m/Y H:i') }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark fs-xs">{{ $verif->dokter->user->nama ?? '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border fs-3xs">{{ $verif->dokter->ksm->nama_ksm ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="font-monospace fs-3xs text-dark">{{ $verif->nomor_sip }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('komite.verifikasi.show', $verif->id) }}" class="btn btn-teal btn-2xs text-white text-nowrap">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i> Telaah
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted fs-xs">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i> Tidak ada antrean verifikasi yang menunggu.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Audit Log & Quick Info -->
    <div class="col-xl-4">
        <!-- Audit Log Widget -->
        <div class="card shadow-xs border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark fs-sm">
                    <i class="fa-solid fa-shield-halved text-secondary me-2"></i>
                    Aktivitas Sistem Terbaru
                </div>
                <a href="{{ route('admin.audit.index') }}" class="btn btn-link btn-sm text-teal p-0 fs-xs text-decoration-none">Selengkapnya &raquo;</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush fs-sm">
                    @forelse($auditTerbaru as $log)
                        <li class="list-group-item py-2 px-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-light text-dark border fs-3xs">{{ $log->aksi }}</span>
                                <small class="text-muted fs-3xs">{{ $log->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="text-dark fs-xs mb-1">{{ Str::limit($log->deskripsi, 65) }}</div>
                            <small class="text-secondary fs-3xs"><i class="fa-regular fa-user me-1"></i>{{ $log->user->nama ?? 'Sistem' }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-4 text-muted fs-xs">Belum ada riwayat audit log.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Sebaran Dokter Per KSM -->
        <div class="card shadow-xs border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark fs-sm">
                    <i class="fa-solid fa-sitemap text-teal me-2"></i>
                    Sebaran Staf Medis per KSM
                </div>
                <a href="{{ route('sdm.ksm.index') }}" class="btn btn-outline-teal btn-2xs text-nowrap">Master KSM &raquo;</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush fs-sm">
                    @foreach($ksmStatistik->take(6) as $ksm)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                            <div class="overflow-hidden me-2">
                                <div class="text-dark fs-xs fw-semibold text-truncate">{{ $ksm->nama_ksm }}</div>
                                <small class="text-muted fs-3xs">{{ $ksm->kode_ksm }}</small>
                            </div>
                            <span class="badge bg-teal-subtle text-teal rounded-pill fs-3xs flex-shrink-0">{{ $ksm->dokters_count }} Dokter</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
