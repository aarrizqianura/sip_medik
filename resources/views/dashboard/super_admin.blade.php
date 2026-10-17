@extends('layouts.app')

@section('title', 'Dashboard Super Admin (IT PDE)')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Dashboard Eksekutif & Administrator IT PDE</h4>
        <p class="text-secondary fs-sm mb-0">Pemantauan menyeluruh sistem, gateway notifikasi, dan kepatuhan SIP dokter RSUD Ansari Saleh</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form action="{{ route('admin.pengaturan.trigger_cron') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-teal btn-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-arrows-rotate"></i>
                <span>Jalankan Scheduler Harian</span>
            </button>
        </form>
        <a href="{{ route('admin.pengaturan.index') }}" class="btn btn-teal btn-sm text-white d-flex align-items-center gap-2">
            <i class="fa-solid fa-sliders"></i>
            <span>Konfigurasi Sistem</span>
        </a>
    </div>
</div>

<!-- 4 Key Metrics Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-4 border-emerald shadow-xs">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">SIP Aktif (>90 Hari)</span>
                    <span class="badge bg-success-subtle text-success rounded-pill"><i class="fa-solid fa-check"></i> Absah</span>
                </div>
                <div class="fs-2 fw-bold text-dark">{{ $stats['sipAktif'] }}</div>
                <div class="text-muted fs-xs mt-1">Dokumen izin praktik aman</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-4 border-warning shadow-xs">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">Due Soon (31-90 Hari)</span>
                    <span class="badge bg-warning-subtle text-warning rounded-pill"><i class="fa-solid fa-clock"></i> Warning</span>
                </div>
                <div class="fs-2 fw-bold text-warning">{{ $stats['sipDueSoon'] }}</div>
                <div class="text-muted fs-xs mt-1">Perlu persiapan perpanjangan</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-4 border-orange shadow-xs" style="border-left-color: #f97316 !important;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">Kritis / Due (≤30 Hari)</span>
                    <span class="badge bg-danger-subtle text-danger rounded-pill"><i class="fa-solid fa-bell"></i> Kritis</span>
                </div>
                <div class="fs-2 fw-bold text-danger">{{ $stats['sipDue'] }}</div>
                <div class="text-muted fs-xs mt-1">Peringatan jatuh tempo intensif</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-4 border-danger shadow-xs">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary fs-xs fw-semibold text-uppercase">Kadaluarsa (Overdue)</span>
                    <span class="badge bg-danger text-white rounded-pill"><i class="fa-solid fa-ban"></i> Stop Praktik</span>
                </div>
                <div class="fs-2 fw-bold text-danger">{{ $stats['sipOverdue'] }}</div>
                <div class="text-muted fs-xs mt-1">Pelanggaran administratif KPS</div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Status Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-white p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                    <i class="fa-solid fa-user-doctor fs-4"></i>
                </div>
                <div>
                    <div class="text-muted fs-xs">Total Dokter Terdaftar</div>
                    <div class="fs-4 fw-bold text-dark">{{ $stats['totalDokter'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-white p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-info-subtle text-info rounded-circle">
                    <i class="fa-solid fa-users fs-4"></i>
                </div>
                <div>
                    <div class="text-muted fs-xs">Akun Pengguna Sistem</div>
                    <div class="fs-4 fw-bold text-dark">{{ $totalUser }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-white p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-warning-subtle text-warning rounded-circle">
                    <i class="fa-solid fa-hourglass-half fs-4"></i>
                </div>
                <div>
                    <div class="text-muted fs-xs">Antrean Verifikasi Komite</div>
                    <div class="fs-4 fw-bold text-dark">{{ $stats['sipMenunggu'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-white p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-secondary-subtle text-secondary rounded-circle">
                    <i class="fa-solid fa-certificate fs-4"></i>
                </div>
                <div>
                    <div class="text-muted fs-xs">SPK / Kredensial Kedaluwarsa</div>
                    <div class="fs-4 fw-bold text-dark">{{ $stats['kredensialKadaluarsa'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Dokumen Kritis & Overdue -->
    <div class="col-lg-8">
        <div class="card shadow-xs">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark">
                    <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>
                    Matriks SIP Kritis & Mendekati Jatuh Tempo (H-30 s.d Overdue)
                </div>
                <a href="{{ route('sdm.reminder.index') }}" class="btn btn-outline-secondary btn-sm fs-xs">Lihat Semua</a>
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
                            <th>Aksi</th>
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
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Kirim Pengingat WhatsApp">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i> Tidak ada dokumen SIP dalam status kritis saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Antrean Pengajuan Verifikasi Komite -->
        <div class="card shadow-xs mt-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark">
                    <i class="fa-solid fa-hourglass-start text-warning me-2"></i>
                    Antrean Verifikasi Berkas Masuk (Subkomite Kredensial)
                </div>
                <a href="{{ route('komite.verifikasi.index') }}" class="btn btn-outline-secondary btn-sm fs-xs">Ke Halaman Telaah</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Tgl Upload</th>
                            <th>Nama Dokter</th>
                            <th>KSM</th>
                            <th>No. SIP Baru</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($antreanVerifikasi as $verif)
                            <tr>
                                <td>{{ $verif->created_at->format('d/m/Y H:i') }}</td>
                                <td class="fw-semibold">{{ $verif->dokter->user->nama ?? '-' }}</td>
                                <td>{{ $verif->dokter->ksm->nama_ksm ?? '-' }}</td>
                                <td><span class="font-monospace fs-xs">{{ $verif->nomor_sip }}</span></td>
                                <td>
                                    <a href="{{ route('komite.verifikasi.show', $verif->id) }}" class="btn btn-teal btn-sm text-white">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i> Telaah
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">
                                    Tidak ada antrean verifikasi yang menunggu.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Audit Log & Quick Info -->
    <div class="col-lg-4">
        <!-- Audit Log Widget -->
        <div class="card shadow-xs mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark">
                    <i class="fa-solid fa-shield-halved text-secondary me-2"></i>
                    Aktivitas Sistem Terbaru
                </div>
                <a href="{{ route('admin.audit.index') }}" class="btn btn-link btn-sm text-teal p-0 fs-xs">Selengkapnya</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush fs-sm">
                    @forelse($auditTerbaru as $log)
                        <li class="list-group-item py-2 px-3">
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-light text-dark border fs-xs">{{ $log->aksi }}</span>
                                <small class="text-muted fs-xs">{{ $log->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="text-dark fs-xs mt-1">{{ Str::limit($log->deskripsi, 70) }}</div>
                            <small class="text-secondary fs-xs"><i class="fa-regular fa-user me-1"></i>{{ $log->user->nama ?? 'Sistem' }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-3 text-muted">Belum ada riwayat audit log.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Sebaran Dokter Per KSM -->
        <div class="card shadow-xs">
            <div class="card-header fw-bold text-dark">
                <i class="fa-solid fa-sitemap text-teal me-2"></i>
                Sebaran Staf Medis per KSM
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush fs-sm">
                    @foreach($ksmStatistik->take(6) as $ksm)
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
