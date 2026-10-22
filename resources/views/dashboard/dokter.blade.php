@extends('layouts.app')

@section('title', 'Portal Personal Dokter')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Selamat Datang, {{ $user->nama }}</h4>
        <p class="text-secondary fs-sm mb-0">
            NIP/NRP: <strong>{{ $dokter->nip_nrp ?? '-' }}</strong> &bullet; 
            KSM: <strong>{{ $dokter->ksm->nama_ksm ?? '-' }}</strong> &bullet; 
            Spesialisasi: <strong>{{ $dokter->spesialisasi ?? '-' }}</strong>
        </p>
    </div>
    <div>
        <a href="{{ route('dokter.arsip.saya') }}" class="btn btn-outline-teal btn-sm me-1">
            <i class="fa-solid fa-box-archive me-1"></i> e-Arsip Saya
        </a>
        <a href="{{ route('dokter.pengajuan.create') }}" class="btn btn-teal text-white btn-sm d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-file-arrow-up"></i>
            <span>Unggah Perpanjangan SIP Baru</span>
        </a>
    </div>
</div>

@if($dokter && $dokter->isAktifLegal())
<div class="alert alert-success border-success shadow-xs d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <i class="fa-solid fa-shield-check fs-2 text-success"></i>
        <div>
            <div class="fw-bold text-dark fs-6">Status Profil: HAK PRAKTIK PENUH (AKTIF_LEGAL)</div>
            <div class="fs-xs text-secondary">
                SK Direktur No. <strong>{{ $dokter->nomor_sk_direktur ?: ($kredensialAktif?->nomor_sk_direktur ?: ($kredensialAktif?->nomor_spk ?: '-')) }}</strong> &bull; Berkas STR, SIP, SPK, dan RKK Anda telah terkunci aman di Repositori e-Arsip Kepegawaian. Tanggal jatuh tempo SIP terpantau otomatis.
            </div>
        </div>
    </div>
    <a href="{{ route('dokter.arsip.saya') }}" class="btn btn-sm btn-success fw-semibold text-nowrap">
        <i class="fa-solid fa-folder-open me-1"></i> Buka e-Arsip Saya
    </a>
</div>
@endif

<!-- Primary Countdown Card & Status Legalitas -->
<div class="row g-4 mb-4">
    <!-- Countdown SIP -->
    <div class="col-lg-6">
        @if($sipAktif)
            @php
                $sisaHari = $sipAktif->hitungSisaHari();
                if ($sisaHari < 0) {
                    $headerBg = 'bg-danger text-white';
                    $alertIcon = 'fa-triangle-exclamation';
                    $statusTitle = 'SIP TELAH KEDALUWARSA';
                    $statusDesc = 'Masa berlaku izin praktik telah berakhir ' . abs($sisaHari) . ' hari yang lalu. Mohon segera mengunggah perpanjangan SIP Anda.';
                } elseif ($sisaHari <= 30) {
                    $headerBg = 'bg-danger-subtle text-danger border-danger';
                    $alertIcon = 'fa-bell';
                    $statusTitle = 'STATUS KRITIS (≤ 30 HARI)';
                    $statusDesc = 'Sisa masa berlaku SIP Anda tinggal ' . $sisaHari . ' hari lagi. Segera tindak lanjuti berkas perpanjangan di DPMPTSP.';
                } elseif ($sisaHari <= 90) {
                    $headerBg = 'bg-warning-subtle text-warning border-warning';
                    $alertIcon = 'fa-clock';
                    $statusTitle = 'DUE SOON (31 - 90 HARI)';
                    $statusDesc = 'Memasuki periode persiapan perpanjangan izin praktik dokter.';
                } else {
                    $headerBg = 'bg-success-subtle text-success border-success';
                    $alertIcon = 'fa-circle-check';
                    $statusTitle = 'SIP AKTIF & ABSAH';
                    $statusDesc = 'Izin praktik resmi DPMPTSP berlaku dan legal untuk pelayanan klinis.';
                }
            @endphp
            <div class="card shadow-sm h-100 border">
                <div class="card-header p-3 {{ $headerBg }} d-flex align-items-center justify-content-between">
                    <div class="fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid {{ $alertIcon }}"></i>
                        <span>{{ $statusTitle }}</span>
                    </div>
                    <span class="badge bg-white text-dark shadow-xs">Praktik ke-{{ $sipAktif->tempat_praktik_ke }}</span>
                </div>
                <div class="card-body p-4 text-center">
                    <div class="text-muted fs-xs text-uppercase fw-semibold mb-1">Sisa Masa Berlaku Dokumen SIP</div>
                    <div class="display-3 fw-bolder {{ $sisaHari < 0 ? 'text-danger' : ($sisaHari <= 30 ? 'text-danger' : ($sisaHari <= 90 ? 'text-warning' : 'text-teal')) }}">
                        {{ $sisaHari < 0 ? abs($sisaHari) : $sisaHari }}
                    </div>
                    <div class="fs-6 fw-semibold text-secondary mb-3">
                        {{ $sisaHari < 0 ? 'Hari Terlewat' : 'Hari Tersisa' }}
                    </div>

                    <div class="p-3 bg-light rounded-3 text-start mb-3">
                        <div class="row g-2 fs-sm">
                            <div class="col-6">
                                <span class="text-muted d-block fs-xs">Nomor SIP:</span>
                                <strong>{{ $sipAktif->nomor_sip }}</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block fs-xs">Tanggal Kedaluwarsa:</span>
                                <strong>{{ $sipAktif->tanggal_kadaluarsa ? $sipAktif->tanggal_kadaluarsa->translatedFormat('d F Y') : '-' }}</strong>
                            </div>
                        </div>
                    </div>

                    <p class="text-secondary fs-sm mb-0">{{ $statusDesc }}</p>
                </div>
            </div>
        @else
            <div class="card shadow-sm h-100 border border-dashed text-center p-4 d-flex flex-column justify-content-center">
                <i class="fa-regular fa-folder-open fs-1 text-muted mb-3"></i>
                <h5 class="fw-bold text-dark">Belum Ada Dokumen SIP Aktif</h5>
                <p class="text-secondary fs-sm mb-3">Anda belum memiliki dokumen SIP yang telah disahkan oleh Komite Medik.</p>
                <div>
                    <a href="{{ route('dokter.pengajuan.create') }}" class="btn btn-teal text-white btn-sm">
                        <i class="fa-solid fa-plus me-1"></i> Unggah SIP Pertama
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Status STR & Kredensial Klinis -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-bold text-dark">
                <i class="fa-solid fa-id-card-clip text-teal me-2"></i>
                Instrumen Legalitas & Kredensial Klinis
            </div>
            <div class="card-body">
                <!-- Status STR -->
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-semibold text-dark fs-sm"><i class="fa-solid fa-certificate text-primary me-2"></i> Surat Tanda Registrasi (STR)</span>
                        <span class="badge {{ $dokter->status_str === 'seumur_hidup' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $dokter->status_str === 'seumur_hidup' ? 'STR Seumur Hidup (UU No. 17/2023)' : 'STR Berkala' }}
                        </span>
                    </div>
                    <small class="text-muted">Status registrasi dokter konsil kedokteran KKI / Kemenkes RI.</small>
                </div>

                <!-- Status SPK & RKK -->
                <div class="p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-semibold text-dark fs-sm"><i class="fa-solid fa-stamp text-teal me-2"></i> Surat Penugasan Klinis (SPK & RKK)</span>
                        @if($kredensialAktif)
                            <span class="badge {{ $kredensialAktif->status_kredensial === 'aktif' ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $kredensialAktif->status_label }}
                            </span>
                        @else
                            <span class="badge bg-secondary">Belum Diterbitkan</span>
                        @endif
                    </div>
                    @if($kredensialAktif)
                        <div class="fs-xs text-secondary">
                            <div>No. SPK Direktur: <strong>{{ $kredensialAktif->nomor_spk }}</strong></div>
                            <div>Masa Berlaku s.d: <strong>{{ $kredensialAktif->tanggal_berakhir_spk ? $kredensialAktif->tanggal_berakhir_spk->translatedFormat('d F Y') : '-' }}</strong></div>
                        </div>
                    @else
                        <small class="text-muted">Hubungi Subkomite Kredensial untuk pengesahan rincian kewenangan klinis Anda.</small>
                    @endif
                </div>

                <!-- Status Kepegawaian -->
                <div class="mt-3 px-2 d-flex align-items-center justify-content-between text-secondary fs-sm">
                    <span>Status Kepegawaian:</span>
                    <strong class="text-dark">{{ $dokter->status_kepegawaian ?? '-' }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Riwayat Pengajuan & Berkas SIP Dokter -->
<div class="card shadow-xs">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div class="fw-bold text-dark">
            <i class="fa-solid fa-clock-rotate-left text-teal me-2"></i>
            Riwayat Pengajuan & Arsip Berkas SIP Saya
        </div>
        <a href="{{ route('dokter.pengajuan.create') }}" class="btn btn-outline-teal btn-sm fs-xs">
            <i class="fa-solid fa-plus me-1"></i> Unggah Baru
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th>Tgl Pengajuan</th>
                    <th>Nomor Izin SIP</th>
                    <th>Fasyankes & Praktik</th>
                    <th>Prasyarat SKP</th>
                    <th>Masa Berlaku</th>
                    <th>Status Antrean</th>
                    <th>Catatan Telaah</th>
                    <th>Berkas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sipList as $item)
                    <tr>
                        <td>{{ $item->created_at->format('d/m/Y') }}</td>
                        <td>
                            <span class="font-monospace fw-semibold fs-xs">{{ $item->nomor_sip }}</span>
                            <div class="text-muted fs-2xs">{{ $item->penerbit_sip ?: 'DPMPTSP' }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark fs-xs">{{ $item->fasyankes ?: 'RSUD dr. H. Moch. Ansari Saleh' }}</div>
                            <span class="badge bg-light text-secondary border fs-2xs">SIP Ke-{{ $item->tempat_praktik_ke }}</span>
                        </td>
                        <td>
                            {!! $item->status_kecukupan_skp_badge !!}
                            @if($item->file_rekomendasi_skp)
                                <div class="mt-1">
                                    <a href="{{ asset('storage/' . $item->file_rekomendasi_skp) }}" target="_blank" class="btn btn-2xs btn-outline-teal text-teal" title="Rekomendasi SKP SatuSehat">
                                        <i class="fa-solid fa-file-pdf"></i> Rekomendasi
                                    </a>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $item->tanggal_terbit ? $item->tanggal_terbit->format('d/m/Y') : '-' }} s.d</div>
                            <small class="fw-bold text-dark">{{ $item->tanggal_kadaluarsa ? $item->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</small>
                        </td>
                        <td>
                            @if($item->status_verifikasi === 'disetujui')
                                <span class="badge bg-success-subtle text-success border border-success"><i class="fa-solid fa-circle-check me-1"></i> Disetujui</span>
                            @elseif($item->status_verifikasi === 'ditolak')
                                <span class="badge bg-danger-subtle text-danger border border-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Ditolak</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning"><i class="fa-solid fa-hourglass-half me-1"></i> Menunggu Verifikasi</span>
                            @endif
                        </td>
                        <td>
                            @if($item->catatan_verifikasi)
                                <span class="text-secondary fs-xs fst-italic">{{ $item->catatan_verifikasi }}</span>
                            @else
                                <span class="text-muted fs-xs">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->file_sip)
                                <a href="{{ asset('storage/' . $item->file_sip) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Lihat Berkas Lembar SIP">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                            @else
                                <span class="text-muted fs-xs">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            Belum ada dokumen SIP yang diunggah.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
