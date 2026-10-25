@extends('layouts.app')

@section('title', 'Tahap 4: Pengesahan Direksi & e-Arsip Kepegawaian')

@section('content')
<div class="mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-stamp text-teal me-2"></i> Pengesahan Direksi & e-Arsip Terpadu (SDM)
            </h4>
            <p class="text-secondary fs-sm mb-0">
                Tahap 4: Penerbitan Surat Keputusan (SK) Direktur penetapan SPK & RKK, penguncian berkas STR-SIP-SPK-RKK ke repositori digital kepegawaian, dan aktivasi status <strong>AKTIF_LEGAL</strong>.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sdm.dokter.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-user-doctor me-1"></i> Master Data Dokter
            </a>
            <a href="{{ route('sdm.reminder.index') }}" class="btn btn-outline-success btn-sm">
                <i class="fa-brands fa-whatsapp me-1"></i> Mesin Pelacak SIP
            </a>
        </div>
    </div>
</div>

<!-- Statistik Metrik Kartu -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-xs border-start border-4 border-warning h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-xs fw-semibold text-muted text-uppercase">Siap Disahkan Direktur</div>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalSiapSah }}</h3>
                        <small class="text-warning fw-semibold fs-xs">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i>Rekomendasi Komite Medik
                        </small>
                    </div>
                    <div class="avatar bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-file-signature fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-xs border-start border-4 border-success h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-xs fw-semibold text-muted text-uppercase">Hak Praktik AKTIF_LEGAL</div>
                        <h3 class="fw-bold text-success mb-0 mt-1">{{ $totalAktifLegal }}</h3>
                        <small class="text-muted fs-xs">
                            <i class="fa-solid fa-circle-check text-success me-1"></i>Kewenangan Penuh Absah
                        </small>
                    </div>
                    <div class="avatar bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-shield-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-xs border-start border-4 border-teal h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-xs fw-semibold text-muted text-uppercase">e-Arsip Terkunci Resmi</div>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalArsipTerkunci }}</h3>
                        <small class="text-teal fw-semibold fs-xs">
                            <i class="fa-solid fa-lock me-1"></i>STR, SIP, SPK & RKK
                        </small>
                    </div>
                    <div class="avatar bg-teal-subtle text-teal rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-box-archive fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-xs border-start border-4 border-secondary h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-xs fw-semibold text-muted text-uppercase">Total Staf Medis Dokter</div>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalDokter }}</h3>
                        <small class="text-muted fs-xs">
                            <i class="fa-solid fa-hospital-user me-1"></i>Tercatat di SDM
                        </small>
                    </div>
                    <div class="avatar bg-secondary-subtle text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-user-doctor fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Toolbar -->
<div class="card shadow-xs mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('sdm.pengesahan.index') }}" class="row g-2 align-items-center">
            <input type="hidden" name="tab" value="{{ $statusTab }}">
            <div class="col-md-5">
                <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari nama dokter, NIP, nomor SK Direktur, nomor rekomendasi..." value="{{ request('cari') }}">
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
                    <i class="fa-solid fa-filter me-1"></i> Terapkan Filter
                </button>
                <a href="{{ route('sdm.pengesahan.index', ['tab' => $statusTab]) }}" class="btn btn-light btn-sm border" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Nav Tabs Alur Kerja Tahap 4 -->
<ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ $statusTab === 'menunggu' ? 'active fw-bold' : '' }}" href="{{ route('sdm.pengesahan.index', ['tab' => 'menunggu']) }}">
            <i class="fa-solid fa-hourglass-half text-warning me-2"></i>
            Antrean Pengesahan SK Direktur
            <span class="badge bg-warning text-dark ms-2 rounded-pill">{{ $menungguPengesahan->total() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $statusTab === 'aktif_legal' ? 'active fw-bold' : '' }}" href="{{ route('sdm.pengesahan.index', ['tab' => 'aktif_legal']) }}">
            <i class="fa-solid fa-shield-check text-success me-2"></i>
            Dokter Berstatus AKTIF_LEGAL & e-Arsip
            <span class="badge bg-success text-white ms-2 rounded-pill">{{ $dokterAktifLegal->total() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $statusTab === 'semua' ? 'active fw-bold' : '' }}" href="{{ route('sdm.pengesahan.index', ['tab' => 'semua']) }}">
            <i class="fa-solid fa-boxes-packing text-primary me-2"></i>
            Buku Induk Repositori e-Arsip Kepegawaian
            <span class="badge bg-secondary ms-2 rounded-pill">{{ $semuaArsip->total() }}</span>
        </a>
    </li>
</ul>

<!-- TAB CONTENT 1: Antrean Pengesahan SK Direktur -->
@if($statusTab === 'menunggu')
<div class="card shadow-xs">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-file-signature text-teal me-2"></i>Rekomendasi Komite Medik Menunggu Penerbitan SK Direktur
            </h6>
            <small class="text-muted fs-xs">
                Dokter yang telah selesai dievaluasi Mitra Bestari dan diterbitkan Surat Rekomendasi RKK oleh Komite Medik
            </small>
        </div>
        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-3 py-1 fs-xs">
            Pintu Masuk Tahap 4
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th>Dokter & KSM</th>
                    <th>No. Rekomendasi Komite</th>
                    <th>Tanggal Rekomendasi</th>
                    <th>Penetapan RKK</th>
                    <th>Status SIP & STR</th>
                    <th class="text-end">Aksi SDM</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menungguPengesahan as $kred)
                    @php 
                        $dok = $kred->dokter; 
                        $sip = $dok->sipTerbaru;
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar bg-teal text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-xs">
                                    {{ strtoupper(substr($dok->user->nama ?? 'D', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $dok->user->nama ?? '-' }}</div>
                                    <small class="text-secondary">NIP: {{ $dok->nip_nrp }} &bull; {{ $dok->ksm->nama_ksm ?? '-' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="font-monospace fw-bold fs-xs text-primary">
                                {{ $kred->nomor_surat_rekomendasi ?: 'Draft Rekomendasi' }}
                            </div>
                            <small class="text-muted fs-2xs">Oleh: {{ Str::limit($kred->ketua_komite_medik ?: 'Ketua Komite Medik', 30) }}</small>
                        </td>
                        <td>
                            <div>{{ $kred->tanggal_rekomendasi ? $kred->tanggal_rekomendasi->format('d/m/Y') : '-' }}</div>
                            <small class="text-muted fs-2xs">Sidang: {{ $kred->tanggal_sidang ? $kred->tanggal_sidang->format('d/m/Y') : '-' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-teal-subtle text-teal border border-teal fs-xs">
                                {{ $kred->rkk_mandiri_count }} Mandiri
                            </span>
                            <span class="badge bg-info-subtle text-info border border-info fs-xs">
                                {{ $kred->rkk_supervisi_count }} Supervisi
                            </span>
                            <div class="fs-2xs text-muted mt-1">Total: {{ $kred->rkk_total_count }} Tindakan</div>
                        </td>
                        <td>
                            <div>
                                <span class="badge bg-light text-dark border fs-2xs">STR: {{ $dok->is_str_locked ? 'Terkunci' : 'Belum' }}</span>
                                @if($sip)
                                    <span class="badge bg-light text-dark border fs-2xs">SIP: {{ $sip->nomor_sip }}</span>
                                @endif
                            </div>
                            <small class="text-success fs-2xs fw-semibold"><i class="fa-solid fa-check me-1"></i>Syarat Mutlak Terpenuhi</small>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('komite.kredensial.surat_rekomendasi', $kred->id) }}" target="_blank" class="btn btn-xs btn-outline-secondary" title="Pratinjau Surat Rekomendasi">
                                    <i class="fa-solid fa-eye me-1"></i> Rekomendasi
                                </a>
                                <a href="{{ route('sdm.pengesahan.create', $kred->id) }}" class="btn btn-xs btn-teal text-white fw-semibold shadow-xs">
                                    <i class="fa-solid fa-stamp me-1"></i> Terbitkan SK Direktur & Kunci e-Arsip
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <div class="mb-2"><i class="fa-solid fa-circle-check fs-2 text-success"></i></div>
                            <div class="fw-semibold">Tidak ada antrean rekomendasi yang menunggu pengesahan SK Direktur.</div>
                            <small>Seluruh rekomendasi Komite Medik telah diproses atau belum ada berkas rekomendasi baru yang diterbitkan.</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($menungguPengesahan->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $menungguPengesahan->links() }}
        </div>
    @endif
</div>
@endif

<!-- TAB CONTENT 2: Dokter Berstatus AKTIF_LEGAL & e-Arsip -->
@if($statusTab === 'aktif_legal')
<div class="card shadow-xs">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-shield-check text-success me-2"></i>Daftar Dokter Berstatus AKTIF_LEGAL & Berkas Terkunci
            </h6>
            <small class="text-muted fs-xs">
                Staf medis yang memiliki hak praktik penuh, SK Direktur aktif masa 3 tahun, dan SIP resmi dipantau mesin pelacak otomatis
            </small>
        </div>
        <span class="badge bg-success-subtle text-success border border-success px-3 py-1 fs-xs">
            Hak Praktik Sah Penuh
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th>Dokter & NIP</th>
                    <th>KSM & Spesialisasi</th>
                    <th>Nomor SK Direktur (SPK)</th>
                    <th>Masa Berlaku SPK</th>
                    <th>Jatuh Tempo SIP (Pelacakan)</th>
                    <th>e-Arsip Status</th>
                    <th class="text-end">Aksi Terpadu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dokterAktifLegal as $dok)
                    @php 
                        $spk = $dok->kredensialTerbaru ?: $dok->spkTerbaru;
                        $sip = $dok->sipTerbaru;
                        $sisaSpk = $spk ? $spk->hitungSisaHari() : 0;
                        $sisaSip = $sip ? $sip->hitungSisaHari() : 0;
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-xs">
                                    {{ strtoupper(substr($dok->user->nama ?? 'D', 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('sdm.arsip.show', $dok->id) }}" class="fw-bold text-dark text-decoration-none hover-teal">
                                        {{ $dok->user->nama ?? '-' }}
                                    </a>
                                    <div><small class="text-secondary font-monospace fs-2xs">{{ $dok->nip_nrp }}</small></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $dok->spesialisasi }}</div>
                            <small class="text-teal fw-semibold">{{ $dok->ksm->nama_ksm ?? '-' }}</small>
                        </td>
                        <td>
                            <div class="font-monospace fw-bold fs-xs text-dark">
                                {{ $dok->nomor_sk_direktur ?: ($spk?->nomor_sk_direktur ?: ($spk?->nomor_spk ?: '-')) }}
                            </div>
                            <small class="text-muted fs-2xs">Tgl: {{ $dok->tanggal_sk_direktur ? $dok->tanggal_sk_direktur->format('d/m/Y') : ($spk?->tanggal_terbit_spk ? $spk->tanggal_terbit_spk->format('d/m/Y') : '-') }}</small>
                        </td>
                        <td>
                            <span class="badge bg-success-subtle text-success border border-success fs-xs">
                                <i class="fa-solid fa-circle-check me-1"></i>3 Tahun Aktif
                            </span>
                            <div class="fs-2xs text-muted mt-1">
                                s.d {{ $spk?->tanggal_berakhir_spk ? $spk->tanggal_berakhir_spk->format('d/m/Y') : '-' }}
                                ({{ $sisaSpk }} hari)
                            </div>
                        </td>
                        <td>
                            @if($sip)
                                <span class="status-pill status-{{ $sip->status }} fs-2xs">
                                    {{ $sip->status_label }}
                                </span>
                                <div class="fs-2xs text-muted mt-1">
                                    <i class="fa-solid fa-radar text-primary me-1"></i>Exp: {{ $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}
                                </div>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Belum ada SIP</span>
                            @endif
                        </td>
                        <td>
                            {!! $dok->arsip_status_badge !!}
                            <div class="fs-3xs text-muted font-monospace mt-1">
                                4 Pilar Terkunci
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('sdm.arsip.show', $dok->id) }}" class="btn btn-outline-teal" title="Lihat Repositori e-Arsip">
                                    <i class="fa-solid fa-folder-open me-1"></i> e-Arsip
                                </a>
                                <a href="{{ route('sdm.arsip.cetak', $dok->id) }}" target="_blank" class="btn btn-outline-secondary" title="Cetak Berita Acara & Lembar e-Arsip">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            Tidak ditemukan dokter berstatus AKTIF_LEGAL yang cocok dengan kriteria pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($dokterAktifLegal->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $dokterAktifLegal->links() }}
        </div>
    @endif
</div>
@endif

<!-- TAB CONTENT 3: Buku Induk Seluruh e-Arsip Kepegawaian (Tabel Sederhana & Modal Detail) -->
@if($statusTab === 'semua')
<div class="card shadow-xs">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-boxes-packing text-teal me-2"></i>Buku Induk Repositori Digital Kepegawaian (e-Arsip)
            </h6>
            <small class="text-muted fs-xs">Daftar staf medis dan kepatuhan 4 pilar instrumen legalitas (STR, SIP, SPK, RKK)</small>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th>Nama Dokter</th>
                    <th>Kelompok Staf Medis (KSM)</th>
                    <th>Status Legalitas</th>
                    <th class="text-end" style="width: 220px;">Aksi Terpadu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($semuaArsip as $dok)
                    @php 
                        $spk = $dok->kredensialTerbaru ?: $dok->spkTerbaru;
                        $sip = $dok->sipTerbaru;
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $dok->user->nama ?? '-' }}</div>
                            <small class="text-muted font-monospace fs-3xs">NIP: {{ $dok->nip_nrp }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-teal fs-xs">{{ $dok->ksm->nama_ksm ?? '-' }}</div>
                            <small class="text-secondary fs-3xs">{{ $dok->spesialisasi }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                {!! $dok->status_praktik_badge !!}
                                {!! $dok->arsip_status_badge !!}
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-2xs btn-outline-teal" data-bs-toggle="modal" data-bs-target="#modalLegalitas{{ $dok->id }}">
                                    <i class="fa-solid fa-circle-info me-1"></i> Detail Legalitas
                                </button>
                                <a href="{{ route('sdm.arsip.show', $dok->id) }}" class="btn btn-2xs btn-teal text-white" title="Buka e-Arsip">
                                    <i class="fa-solid fa-folder-open me-1"></i> e-Arsip
                                </a>
                            </div>

                            <!-- Modal Detail 4 Pilar Legalitas -->
                            <div class="modal fade" id="modalLegalitas{{ $dok->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered text-start">
                                    <div class="modal-content">
                                        <div class="modal-header bg-dark-teal text-white py-2">
                                            <h6 class="modal-title fs-sm fw-bold">
                                                <i class="fa-solid fa-landmark me-2"></i>Status 4 Pilar Legalitas: {{ $dok->user->nama ?? '-' }}
                                            </h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body fs-sm">
                                            <div class="p-2 bg-light rounded border mb-3">
                                                <div class="row g-2 fs-xs">
                                                    <div class="col-6">
                                                        <span class="text-muted d-block">NIP / NRP:</span>
                                                        <strong class="font-monospace text-dark">{{ $dok->nip_nrp }}</strong>
                                                    </div>
                                                    <div class="col-6">
                                                        <span class="text-muted d-block">KSM & Spesialis:</span>
                                                        <strong class="text-teal">{{ $dok->ksm->kode_ksm ?? '-' }} ({{ $dok->spesialisasi }})</strong>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="list-group list-group-flush fs-xs">
                                                <!-- Pilar 1: STR -->
                                                <div class="list-group-item px-0 py-2">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <strong class="text-dark"><i class="fa-solid fa-certificate text-primary me-1"></i> Pilar 1: Surat Tanda Registrasi (STR)</strong>
                                                        @if($dok->is_str_locked)
                                                            <span class="badge bg-success text-white fs-3xs"><i class="fa-solid fa-lock me-1"></i> Terkunci</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark fs-3xs">Terbuka</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-muted">
                                                        No. STR: <span class="font-monospace fw-semibold text-dark">{{ $dok->nomor_str ?: 'Belum diisi' }}</span> &bull; 
                                                        Status: {{ $dok->status_str === 'seumur_hidup' ? 'Seumur Hidup' : 'Berkala' }}
                                                    </div>
                                                </div>

                                                <!-- Pilar 2: SIP -->
                                                <div class="list-group-item px-0 py-2">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <strong class="text-dark"><i class="fa-solid fa-file-contract text-teal me-1"></i> Pilar 2: Surat Izin Praktik (SIP)</strong>
                                                        @if($sip)
                                                            <span class="status-pill status-{{ $sip->status }} fs-3xs">{{ $sip->status_label }}</span>
                                                        @else
                                                            <span class="badge bg-secondary fs-3xs">Belum ada</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-muted">
                                                        No. SIP: <span class="font-monospace fw-semibold text-dark">{{ $sip ? $sip->nomor_sip : '-' }}</span> &bull; 
                                                        Exp: {{ $sip && $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}
                                                    </div>
                                                </div>

                                                <!-- Pilar 3: SPK Direktur -->
                                                <div class="list-group-item px-0 py-2">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <strong class="text-dark"><i class="fa-solid fa-stamp text-success me-1"></i> Pilar 3: Surat Penugasan Klinis (SPK)</strong>
                                                        @if($spk && $spk->status_kredensial === 'aktif')
                                                            <span class="badge bg-success text-white fs-3xs">Aktif</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark fs-3xs">{{ $spk ? $spk->status_label : 'Belum Terbit' }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-muted">
                                                        No. SPK / SK: <span class="font-monospace fw-semibold text-dark">{{ $spk ? ($spk->nomor_sk_direktur ?: $spk->nomor_spk) : '-' }}</span> &bull; 
                                                        s.d {{ $spk && $spk->tanggal_berakhir_spk ? $spk->tanggal_berakhir_spk->format('d/m/Y') : '-' }}
                                                    </div>
                                                </div>

                                                <!-- Pilar 4: RKK -->
                                                <div class="list-group-item px-0 py-2">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <strong class="text-dark"><i class="fa-solid fa-list-check text-warning me-1"></i> Pilar 4: Rincian Kewenangan Klinis (RKK)</strong>
                                                        @if($spk && is_array($spk->daftar_rkk))
                                                            <span class="badge bg-teal-subtle text-teal fs-3xs">{{ count($spk->daftar_rkk) }} Kewenangan</span>
                                                        @else
                                                            <span class="badge bg-secondary fs-3xs">Belum diatur</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-muted">
                                                        Tindakan Mandiri: <strong>{{ $spk?->rkk_mandiri_count ?? 0 }}</strong> &bull; 
                                                        Tindakan Supervisi: <strong>{{ $spk?->rkk_supervisi_count ?? 0 }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer py-2">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                                            <a href="{{ route('sdm.arsip.show', $dok->id) }}" class="btn btn-teal text-white btn-sm">
                                                <i class="fa-solid fa-box-archive me-1"></i> Buka e-Arsip Penuh
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Belum ada data dokter yang tersimpan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($semuaArsip->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $semuaArsip->links() }}
        </div>
    @endif
</div>
@endif
@endsection
