@extends('layouts.app')

@section('title', 'Portal Personal Dokter')

@section('content')
<!-- 2. Ucapan Selamat Datang (Nama Dokter beserta Gelar) -->
<div class="mb-3">
    <h4 class="fw-bold text-dark mb-1">Selamat Datang, {{ $user->nama }}</h4>

    <!-- 3. NIP, KSM, dan Spesialisasi -->
    <div class="d-flex align-items-center gap-2 flex-wrap text-secondary fs-sm mt-2">
        <span class="badge bg-light text-secondary border fs-xs py-2 px-3">
            <i class="fa-solid fa-id-card me-1 text-teal"></i>NIP/NRP: <strong class="text-dark">{{ $dokter->nip_nrp ?? '-' }}</strong>
        </span>
        <span class="badge bg-light text-secondary border fs-xs py-2 px-3">
            <i class="fa-solid fa-hospital me-1 text-teal"></i>KSM: <strong class="text-dark">{{ $dokter->ksm->nama_ksm ?? '-' }}</strong>
        </span>
        <span class="badge bg-light text-secondary border fs-xs py-2 px-3">
            <i class="fa-solid fa-user-doctor me-1 text-teal"></i>Spesialisasi: <strong class="text-dark">{{ $dokter->spesialisasi ?? '-' }}</strong>
        </span>
    </div>
</div>

<!-- 4. Kartu Status Legalitas Terpadu Dokter -->
<div class="card shadow-xs border-0 overflow-hidden mb-4 bg-white">
    <div class="card-header bg-dark-teal text-white py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-shield-halved text-teal-300 fs-5"></i>
            <h5 class="fw-bold mb-0 text-white fs-6">Kartu Status Legalitas Terpadu Dokter</h5>
        </div>
        <span class="badge bg-teal-subtle text-teal fw-bold fs-xs px-3 py-1">
            RSUD dr. H. Moch. Ansari Saleh
        </span>
    </div>

    <div class="card-body p-4 bg-white">
        @if($sipAktif)
            @php
                $sisaHari = $sipAktif->hitungSisaHari();
                if ($sisaHari < 0) {
                    $accentColor = 'text-danger';
                    $badgeClass = 'bg-danger text-white';
                    $statusTitle = 'SIP TELAH KEDALUWARSA';
                    $statusDesc = 'Masa berlaku izin praktik berakhir ' . abs($sisaHari) . ' hari yang lalu. Segera perbarui berkas izin praktik definitif.';
                } elseif ($sisaHari <= 30) {
                    $accentColor = 'text-danger';
                    $badgeClass = 'bg-danger text-white';
                    $statusTitle = 'STATUS KRITIS (≤ 30 HARI)';
                    $statusDesc = 'Sisa masa berlaku SIP tinggal ' . $sisaHari . ' hari lagi. Segera koordinasikan perizinan di DPMPTSP.';
                } elseif ($sisaHari <= 90) {
                    $accentColor = 'text-warning-emphasis';
                    $badgeClass = 'bg-warning text-dark';
                    $statusTitle = 'DUE SOON (31 - 90 HARI)';
                    $statusDesc = 'Memasuki periode persiapan perpanjangan izin praktik DPMPTSP dan kecukupan SKP SatuSehat.';
                } else {
                    $accentColor = 'text-teal';
                    $badgeClass = 'bg-success text-white';
                    $statusTitle = 'SIP AKTIF & ABSAH';
                    $statusDesc = 'Izin praktik resmi DPMPTSP berlaku aktif untuk pelayanan klinis di fasyankes RSUD Ansari Saleh.';
                }
            @endphp

            @if($dokter && $dokter->isSuspended())
                <div class="alert alert-danger border-start border-4 border-danger shadow-xs p-3 mb-4 rounded-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-danger text-white p-2 rounded-circle fs-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="fa-solid fa-ban"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <span class="badge bg-danger text-white"><i class="fa-solid fa-lock me-1"></i> STATUS: OVERDUE & DITANGGUHKAN</span>
                                <span class="badge bg-white text-danger border border-danger-subtle"><i class="fa-solid fa-triangle-exclamation me-1"></i> Tindakan Mandiri Terblokir</span>
                            </div>
                            <h6 class="fw-bold text-danger mb-1">Wewenang Tindakan Klinis Mandiri Anda Ditangguhkan Secara Administratif</h6>
                            <p class="fs-xs text-dark mb-0">
                                Masa berlaku SIP Anda telah kedaluwarsa. Sesuai Standar Akreditasi <strong>Pokja KPS</strong>, instrumen <strong>Surat Penugasan Klinis (SPK) & RKK Anda DITANGGUHKAN</strong> guna melindungi fasyankes dan dokter dari risiko medikolegal.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="p-4 rounded-3 border" style="background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%);">
                <div class="row align-items-center g-4">
                    <!-- Countdown Display -->
                    <div class="col-lg-5 text-center text-lg-start pe-lg-4 border-end-lg pb-3 pb-lg-0" style="border-color: #ccfbf1 !important;">
                        <span class="badge {{ $badgeClass }} px-3 py-1 mb-2 fs-xs fw-semibold">
                            <i class="fa-solid fa-circle-dot me-1"></i> {{ $statusTitle }}
                        </span>
                        <div class="d-flex align-items-baseline justify-content-center justify-content-lg-start gap-2 my-1">
                            <span class="display-3 fw-bolder {{ $accentColor }} lh-1">
                                {{ $sisaHari < 0 ? abs($sisaHari) : $sisaHari }}
                            </span>
                            <span class="fs-5 fw-bold text-secondary">
                                {{ $sisaHari < 0 ? 'Hari Terlewat' : 'Hari Tersisa' }}
                            </span>
                        </div>
                        <p class="text-secondary fs-sm mt-2 mb-0">{{ $statusDesc }}</p>
                    </div>

                    <!-- Metadata Dokumen SIP Definitif -->
                    <div class="col-lg-7 ps-lg-4">
                        <div class="row g-3 fs-sm">
                            <div class="col-sm-6">
                                <div class="p-2 rounded border bg-white h-100">
                                    <span class="text-muted d-block fs-3xs text-uppercase fw-semibold">Nomor SIP DPMPTSP</span>
                                    <strong class="font-monospace text-dark fs-xs text-break">{{ $sipAktif->nomor_sip }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-2 rounded border bg-white h-100">
                                    <span class="text-muted d-block fs-3xs text-uppercase fw-semibold">Tempat Praktik</span>
                                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle fs-3xs">
                                        SIP Ke-{{ $sipAktif->tempat_praktik_ke }} ({{ $sipAktif->fasyankes ?: 'RSUD Ansari Saleh' }})
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-2 rounded border bg-white h-100">
                                    <span class="text-muted d-block fs-3xs text-uppercase fw-semibold">Tanggal Terbit</span>
                                    <strong class="text-dark fs-xs">{{ $sipAktif->tanggal_terbit ? $sipAktif->tanggal_terbit->translatedFormat('d F Y') : '-' }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-2 rounded border bg-white h-100">
                                    <span class="text-muted d-block fs-3xs text-uppercase fw-semibold">Tanggal Kedaluwarsa</span>
                                    <strong class="text-danger fs-xs">{{ $sipAktif->tanggal_kadaluarsa ? $sipAktif->tanggal_kadaluarsa->translatedFormat('d F Y') : '-' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="p-4 rounded-3 border border-dashed text-center bg-light">
                <i class="fa-regular fa-folder-open fs-2 text-muted mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">Belum Ada Dokumen SIP Aktif Terverifikasi</h6>
                <p class="text-secondary fs-sm mb-0">Anda belum memiliki dokumen SIP aktif yang telah divalidasi oleh Subkomite Kredensial.</p>
            </div>
        @endif
    </div>
</div>

<!-- 5. 3 Pilar Instrumen Legalitas Staf Medis -->
<div class="card shadow-xs border-0 overflow-hidden mb-4 bg-white">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-landmark text-teal fs-5"></i>
            <h5 class="fw-bold mb-0 text-dark fs-6">3 Pilar Instrumen Legalitas Staf Medis</h5>
        </div>
        <span class="badge bg-light text-secondary border fs-xs">
            Standar Akreditasi KPS
        </span>
    </div>

    <div class="card-body p-4 bg-white">
        <div class="row g-3">
            <!-- Pilar 1: STR Seumur Hidup -->
            <div class="col-md-4">
                <div class="p-3 rounded-3 border bg-light h-100 d-flex flex-column justify-content-between hover-shadow">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fs-xs fw-bold text-uppercase text-secondary text-nowrap">
                                <i class="fa-solid fa-certificate text-primary me-1"></i> Pilar 1
                            </span>
                            <span class="badge {{ $dokter->status_str === 'seumur_hidup' ? 'bg-success text-white' : 'bg-warning text-dark' }} fs-3xs text-nowrap">
                                {{ $dokter->status_str === 'seumur_hidup' ? 'Seumur Hidup' : 'Berkala' }}
                            </span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1 fs-sm">Surat Tanda Registrasi (STR)</h6>
                        <div class="font-monospace fs-xs fw-bold text-teal mb-1">
                            {{ $dokter->nomor_str ? chunk_split($dokter->nomor_str, 4, ' ') : 'Belum diisi' }}
                        </div>
                        <small class="text-muted fs-3xs d-block">
                            UU Kesehatan No. 17/2023 &bull; Terverifikasi SatuSehat
                        </small>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        {!! $dokter->status_verifikasi_str_badge !!}
                    </div>
                </div>
            </div>

            <!-- Pilar 2: SIP Aktif DPMPTSP -->
            <div class="col-md-4">
                <div class="p-3 rounded-3 border bg-light h-100 d-flex flex-column justify-content-between hover-shadow">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fs-xs fw-bold text-uppercase text-secondary text-nowrap">
                                <i class="fa-solid fa-file-contract text-teal me-1"></i> Pilar 2
                            </span>
                            @if($sipAktif)
                                <span class="badge {{ $sipAktif->status === 'aktif' ? 'bg-success' : 'bg-warning text-dark' }} fs-3xs text-nowrap">
                                    {{ $sipAktif->status_label }}
                                </span>
                            @else
                                <span class="badge bg-secondary fs-3xs text-nowrap">Nihil</span>
                            @endif
                        </div>
                        <h6 class="fw-bold text-dark mb-1 fs-sm">Surat Izin Praktik (SIP)</h6>
                        <div class="font-monospace fs-xs fw-bold text-dark mb-1 text-truncate">
                            {{ $sipAktif ? $sipAktif->nomor_sip : 'Belum ada SIP disahkan' }}
                        </div>
                        <small class="text-muted fs-3xs d-block text-truncate">
                            Penerbit: {{ $sipAktif?->penerbit_sip ?: 'DPMPTSP Kota Banjarmasin' }}
                        </small>
                    </div>
                    <div class="mt-3 pt-2 border-top fs-3xs text-secondary d-flex justify-content-between align-items-center">
                        <span>Jatuh Tempo:</span>
                        <strong class="text-dark">{{ $sipAktif?->tanggal_kadaluarsa ? $sipAktif->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</strong>
                    </div>
                </div>
            </div>

            <!-- Pilar 3: SPK & RKK Berlaku -->
            <div class="col-md-4">
                <div class="p-3 rounded-3 border bg-light h-100 d-flex flex-column justify-content-between hover-shadow">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fs-xs fw-bold text-uppercase text-secondary text-nowrap">
                                <i class="fa-solid fa-stamp text-success me-1"></i> Pilar 3
                            </span>
                            @if($kredensialAktif)
                                <span class="badge {{ $kredensialAktif->status_kredensial === 'aktif' ? 'bg-success' : 'bg-warning text-dark' }} fs-3xs text-nowrap">
                                    {{ $kredensialAktif->status_label }}
                                </span>
                            @else
                                <span class="badge bg-secondary fs-3xs text-nowrap">Belum Ada</span>
                            @endif
                        </div>
                        <h6 class="fw-bold text-dark mb-1 fs-sm">Surat Penugasan Klinis (SPK/RKK)</h6>
                        <div class="font-monospace fs-xs fw-bold text-dark mb-1 text-truncate">
                            {{ $kredensialAktif ? ($kredensialAktif->nomor_spk ?: $kredensialAktif->nomor_sk_direktur) : 'Proses Kredensialing' }}
                        </div>
                        <small class="text-muted fs-3xs d-block">
                            Siklus Evaluasi 3 Tahunan Subkomite Kredensial
                        </small>
                    </div>
                    <div class="mt-3 pt-2 border-top fs-3xs text-secondary d-flex justify-content-between align-items-center">
                        <span>Masa Berlaku s.d:</span>
                        <strong class="text-dark">{{ $kredensialAktif?->tanggal_berakhir_spk ? $kredensialAktif->tanggal_berakhir_spk->format('d/m/Y') : '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 6. Tombol "Riwayat dan Berkas Saya" dan Tombol "Ajukan Perpanjangan SIP" -->
<div class="d-flex align-items-center gap-3 flex-wrap mb-4">
    <a href="{{ route('dokter.riwayat') }}" class="btn btn-outline-teal px-4 py-2 fw-semibold shadow-xs d-inline-flex align-items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>Riwayat dan Berkas Saya</span>
    </a>
    <a href="{{ route('dokter.pengajuan.create') }}" class="btn btn-teal text-white px-4 py-2 fw-bold shadow-xs d-inline-flex align-items-center gap-2">
        <i class="fa-solid fa-file-circle-plus"></i>
        <span>Ajukan Perpanjangan SIP</span>
    </a>
</div>
@endsection
