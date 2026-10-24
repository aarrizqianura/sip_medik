@extends('layouts.app')

@section('title', 'Repositori Digital Kepegawaian (e-Arsip) - ' . $dokter->user->nama)

@section('content')
<div class="mb-4">
    <a href="{{ route('sdm.pengesahan.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Repositori Pengesahan
    </a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold text-dark mb-0">{{ $dokter->user->nama }}</h4>
                {!! $dokter->status_praktik_badge !!}
                {!! $dokter->arsip_status_badge !!}
            </div>
            <p class="text-secondary fs-sm mb-0">
                NIP/NRP: <strong>{{ $dokter->nip_nrp }}</strong> &bullet; 
                KSM: <strong>{{ $dokter->ksm->nama_ksm }}</strong> &bullet; 
                {{ $dokter->spesialisasi }} &bullet; 
                Status: <span class="badge bg-light text-dark border">{{ $dokter->status_kepegawaian }}</span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sdm.arsip.cetak', $dokter->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-print me-1"></i> Cetak Berita Acara e-Arsip
            </a>
            <a href="{{ route('sdm.dokter.show', $dokter->id) }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-user me-1"></i> Profil Dokter
            </a>
        </div>
    </div>
</div>

<!-- Banner Status Aktivasi Hak Praktik Penuh / Penangguhan -->
@if($dokter->isSuspended())
<div class="card shadow-xs mb-4 border-danger">
    <div class="card-body p-4 bg-danger-subtle rounded text-dark">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <div class="d-flex align-items-start gap-3">
                    <div class="avatar bg-danger text-white rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width: 56px; height: 56px; min-width: 56px;">
                        <i class="fa-solid fa-ban fs-2"></i>
                    </div>
                    <div>
                        <div class="fs-xs fw-bold text-uppercase text-danger tracking-wide">Status Otorisasi Medis (Pokja KPS)</div>
                        <h4 class="fw-bold text-danger mb-1">WEWENANG KLINIS DITANGGUHKAN (AUTO-SUSPEND)</h4>
                        <p class="fs-sm text-dark mb-0">
                            Masa berlaku SIP dokter telah kedaluwarsa tanpa status dispensasi aktif. Sesuai Standar Akreditasi Pokja KPS & regulasi Komite Medik, <strong>seluruh instrumen SPK & RKK ditangguhkan secara otomatis</strong> dan dokter <strong>terblokir secara administratif dari tindakan klinis mandiri</strong> guna melindungi fasyankes dari tuntutan malpraktik.
                        </p>
                        @if($dokter->alasan_penangguhan)
                            <div class="fs-xs text-danger mt-1"><em>Catatan Sistem: {{ $dokter->alasan_penangguhan }}</em></div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end border-start-md ps-md-4">
                <div class="p-2 bg-white rounded border border-danger">
                    <div class="fs-xs text-muted mb-1"><i class="fa-solid fa-lock text-danger me-1"></i>Status Integritas Berkas:</div>
                    <div class="fw-bold text-danger fs-sm">OVERDUE & TERBLOKIR</div>
                    <div class="fs-2xs text-secondary mt-1">
                        Ditangguhkan sejak: {{ $dokter->suspended_at ? $dokter->suspended_at->translatedFormat('d F Y H:i') : date('d F Y H:i') }} WITA
                    </div>
                    <div class="fs-2xs text-danger mt-1">
                        <i class="fa-solid fa-shield-halved me-1"></i>Masuk Staf Medis Non-Aktif Pelayanan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="card shadow-xs mb-4 border-success">
    <div class="card-body p-4 bg-success-subtle rounded text-dark">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <div class="d-flex align-items-start gap-3">
                    <div class="avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width: 56px; height: 56px; min-width: 56px;">
                        <i class="fa-solid fa-shield-check fs-2"></i>
                    </div>
                    <div>
                        <div class="fs-xs fw-bold text-uppercase text-success tracking-wide">Status Otorisasi Resmi Staf Medis</div>
                        <h4 class="fw-bold text-dark mb-1">HAK PRAKTIK PENUH (AKTIF_LEGAL)</h4>
                        <p class="fs-sm text-secondary mb-0">
                            Berdasarkan <strong>SK Direktur No. {{ $dokter->nomor_sk_direktur ?: ($spk?->nomor_sk_direktur ?: ($spk?->nomor_spk ?: '-')) }}</strong>, dokter memiliki wewenang hukum klinis penuh di RSUD dr. H. Moch. Ansari Saleh. Dokumen STR, SIP, SPK, dan RKK terkunci aman di e-Arsip kepegawaian.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end border-start-md ps-md-4">
                <div class="p-2 bg-white rounded border">
                    <div class="fs-xs text-muted mb-1"><i class="fa-solid fa-lock text-success me-1"></i>Integritas Berkas Digital:</div>
                    <div class="fw-bold text-success fs-sm">TERKUNCI & TERARSIP RESMI</div>
                    <div class="fs-2xs text-secondary mt-1">
                        Dikunci pada: {{ $dokter->arsip_locked_at ? $dokter->arsip_locked_at->translatedFormat('d F Y H:i') : date('d F Y H:i') }} WITA
                    </div>
                    @if($dokter->arsipLockedBy)
                        <div class="fs-2xs text-muted">
                            Petugas: <strong>{{ $dokter->arsipLockedBy->nama }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- 4 Pilar Repositori Digital Kepegawaian (e-Arsip) -->
<div class="row g-4 mb-4">
    <!-- Pilar 1: Surat Tanda Registrasi (STR) -->
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-xs h-100 border-teal">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div class="fw-bold text-dark fs-sm">
                    <i class="fa-solid fa-certificate text-teal me-1"></i> 1. Pilar STR (16 Digit)
                </div>
                <span class="badge bg-success-subtle text-success border border-success fs-2xs">
                    <i class="fa-solid fa-lock me-1"></i> Terkunci
                </span>
            </div>
            <div class="card-body p-3 fs-sm">
                <div class="text-muted fs-xs">Nomor STR Registrasi:</div>
                <div class="font-monospace fw-bold fs-6 text-dark mb-2">
                    {{ $dokter->nomor_str ?: 'Belum Direkam' }}
                </div>

                <div class="mb-2">
                    <div class="text-muted fs-xs">Masa Berlaku:</div>
                    <div class="fw-semibold text-dark fs-xs">
                        {{ $dokter->status_str === 'seumur_hidup' ? 'Seumur Hidup (UU No. 17/2023)' : ($dokter->tanggal_kadaluarsa_str ? $dokter->tanggal_kadaluarsa_str->format('d/m/Y') : '-') }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted fs-xs">Validasi SatuSehat SDMK:</div>
                    <div class="fw-semibold text-success fs-xs">
                        <i class="fa-solid fa-circle-check me-1"></i> Terverifikasi KKI / Kemenkes
                    </div>
                </div>

                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                    @if($dokter->file_str)
                        <a href="{{ asset('storage/' . $dokter->file_str) }}" target="_blank" class="btn btn-xs btn-outline-teal">
                            <i class="fa-solid fa-file-pdf me-1"></i> e-STR Digital
                        </a>
                    @else
                        <span class="fs-xs text-muted">File e-STR terarsip</span>
                    @endif
                    <span class="badge bg-light text-secondary border fs-2xs">Read-Only</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pilar 2: Surat Izin Praktik (SIP) -->
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-xs h-100 border-primary">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div class="fw-bold text-dark fs-sm">
                    <i class="fa-solid fa-file-medical text-primary me-1"></i> 2. Pilar Berkas SIP
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary fs-2xs">
                    <i class="fa-solid fa-radar me-1"></i> Dipantau
                </span>
            </div>
            <div class="card-body p-3 fs-sm">
                <div class="text-muted fs-xs">Nomor Surat Izin Praktik:</div>
                <div class="font-monospace fw-bold fs-6 text-dark mb-2 text-truncate" title="{{ $sip?->nomor_sip }}">
                    {{ $sip?->nomor_sip ?: '-' }}
                </div>

                <div class="mb-2">
                    <div class="text-muted fs-xs">Masa Berlaku SIP:</div>
                    <div class="fw-semibold text-dark fs-xs">
                        s.d {{ $sip?->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}
                        @if($sip)
                            <span class="badge status-pill status-{{ $sip->status }} fs-3xs ms-1">
                                {{ $sip->status_label }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted fs-xs">Status Pelacakan & Dispensasi:</div>
                    @if($sip?->isGracePeriodAktif())
                        <div class="fw-semibold text-teal fs-xs">
                            <i class="fa-solid fa-business-time me-1"></i> Grace Period Aktif (sisa {{ $sip->hitungSisaHariDispensasi() }} hr)
                        </div>
                        <div class="fs-3xs text-muted">Resi: {{ $sip->nomor_resi_proses }}</div>
                    @else
                        <div class="fw-semibold text-primary fs-xs">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Jatuh Tempo Aktif Terdaftar
                        </div>
                    @endif
                </div>

                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                    <div class="btn-group btn-group-sm">
                        @if($sip?->file_sip)
                            <a href="{{ asset('storage/' . $sip->file_sip) }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                <i class="fa-solid fa-file-pdf me-1"></i> Pindaian SIP
                            </a>
                        @else
                            <span class="fs-xs text-muted">Berkas SIP Terarsip</span>
                        @endif
                        @if($sip?->file_bukti_proses)
                            <a href="{{ asset('storage/' . $sip->file_bukti_proses) }}" target="_blank" class="btn btn-xs btn-outline-warning text-dark" title="Resi DPMPTSP">
                                <i class="fa-solid fa-receipt text-warning"></i>
                            </a>
                        @endif
                    </div>
                    <span class="badge bg-light text-secondary border fs-2xs">Terkunci</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pilar 3: Surat Penugasan Klinis (SPK) -->
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-xs h-100 border-warning">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div class="fw-bold text-dark fs-sm">
                    <i class="fa-solid fa-file-signature text-warning me-1"></i> 3. Pilar SPK Direktur
                </div>
                <span class="badge bg-success-subtle text-success border border-success fs-2xs">
                    <i class="fa-solid fa-circle-check me-1"></i> 3 Tahun
                </span>
            </div>
            <div class="card-body p-3 fs-sm">
                <div class="text-muted fs-xs">Nomor SK Direktur / SPK:</div>
                <div class="font-monospace fw-bold fs-6 text-dark mb-2 text-truncate" title="{{ $dokter->nomor_sk_direktur ?: ($spk?->nomor_sk_direktur ?: ($spk?->nomor_spk ?: '-')) }}">
                    {{ $dokter->nomor_sk_direktur ?: ($spk?->nomor_sk_direktur ?: ($spk?->nomor_spk ?: '-')) }}
                </div>

                <div class="mb-2">
                    <div class="text-muted fs-xs">Masa Berlaku SPK:</div>
                    <div class="fw-semibold text-dark fs-xs">
                        {{ $spk?->tanggal_terbit_spk ? $spk->tanggal_terbit_spk->format('d/m/Y') : ($dokter->tanggal_sk_direktur ? $dokter->tanggal_sk_direktur->format('d/m/Y') : '-') }} 
                        s.d <strong>{{ $spk?->tanggal_berakhir_spk ? $spk->tanggal_berakhir_spk->format('d/m/Y') : '-' }}</strong>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted fs-xs">Status Otorisasi:</div>
                    <div class="fw-semibold text-success fs-xs">
                        <i class="fa-solid fa-stamp me-1"></i> Ditetapkan Direktur RSUD
                    </div>
                </div>

                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                    @php $fileSk = $dokter->file_sk_direktur ?: ($spk?->file_sk_direktur ?: $spk?->file_rkk); @endphp
                    @if($fileSk)
                        <a href="{{ asset('storage/' . $fileSk) }}" target="_blank" class="btn btn-xs btn-outline-warning">
                            <i class="fa-solid fa-file-pdf me-1"></i> Berkas SK Direktur
                        </a>
                    @else
                        <span class="fs-xs text-muted">SK Resmi Terlampir</span>
                    @endif
                    <span class="badge bg-light text-secondary border fs-2xs">Terkunci</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pilar 4: Rincian Kewenangan Klinis (RKK) -->
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-xs h-100 border-info">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div class="fw-bold text-dark fs-sm">
                    <i class="fa-solid fa-stethoscope text-info me-1"></i> 4. Lampiran RKK
                </div>
                <span class="badge bg-info-subtle text-info border border-info fs-2xs">
                    Tervalidasi
                </span>
            </div>
            <div class="card-body p-3 fs-sm">
                <div class="text-muted fs-xs">Penetapan Kewenangan:</div>
                <div class="fw-bold text-dark fs-6 mb-2">
                    {{ $spk?->rkk_total_count ?: 'Lampiran SK' }} Tindakan Klinis
                </div>

                <div class="mb-2">
                    <div class="d-flex justify-content-between text-muted fs-xs">
                        <span>Mandiri:</span>
                        <strong class="text-success">{{ $spk?->rkk_mandiri_count ?? 0 }} Tindakan</strong>
                    </div>
                    <div class="d-flex justify-content-between text-muted fs-xs">
                        <span>Supervisi:</span>
                        <strong class="text-info">{{ $spk?->rkk_supervisi_count ?? 0 }} Tindakan</strong>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted fs-xs">Mitra Bestari:</div>
                    <div class="fw-semibold text-dark fs-xs text-truncate">
                        {{ $spk?->nama_mitra_bestari ?: 'Peer Group KSM' }}
                    </div>
                </div>

                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                    @if($spk)
                        <a href="{{ route('komite.kredensial.sidang', $spk->id) }}" class="btn btn-xs btn-outline-info">
                            <i class="fa-solid fa-list-check me-1"></i> Formulir RKK
                        </a>
                    @else
                        <span class="fs-xs text-muted">RKK Terlampir</span>
                    @endif
                    <span class="badge bg-light text-secondary border fs-2xs">Terkunci</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lembar Riwayat Lengkap & Detail Berkas Repositori -->
<div class="row g-4">
    <!-- Riwayat Seluruh SPK & SK Direktur -->
    <div class="col-lg-6">
        <div class="card shadow-xs h-100">
            <div class="card-header bg-white py-3 fw-bold text-dark d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-history text-teal me-2"></i>Riwayat Instrumen SPK & SK Direktur</span>
                <span class="badge bg-teal-subtle text-teal">{{ $dokter->kredensialKlinis->count() }} Periode</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-sm">
                    <thead class="table-light">
                        <tr>
                            <th>No. SK Direktur / SPK</th>
                            <th>Masa Berlaku (3 Thn)</th>
                            <th>Status Instrumen</th>
                            <th>Berkas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dokter->kredensialKlinis as $itemSpk)
                            <tr>
                                <td>
                                    <div class="fw-bold font-monospace text-dark fs-xs">
                                        {{ $itemSpk->nomor_sk_direktur ?: $itemSpk->nomor_spk }}
                                    </div>
                                    <small class="text-muted fs-2xs">Sah: {{ $itemSpk->tanggal_sah_spk ? $itemSpk->tanggal_sah_spk->format('d/m/Y') : '-' }}</small>
                                </td>
                                <td>
                                    <div class="fs-xs">{{ $itemSpk->tanggal_terbit_spk ? $itemSpk->tanggal_terbit_spk->format('d/m/Y') : '-' }} s.d</div>
                                    <div class="fw-bold text-dark fs-xs">{{ $itemSpk->tanggal_berakhir_spk ? $itemSpk->tanggal_berakhir_spk->format('d/m/Y') : '-' }}</div>
                                </td>
                                <td>
                                    {!! $itemSpk->status_badge !!}
                                    @if($itemSpk->is_spk_locked)
                                        <div class="fs-3xs text-success mt-1"><i class="fa-solid fa-lock me-1"></i>Terkunci e-Arsip</div>
                                    @endif
                                </td>
                                <td>
                                    @php $filePdf = $itemSpk->file_sk_direktur ?: $itemSpk->file_rkk; @endphp
                                    @if($filePdf)
                                        <a href="{{ asset('storage/' . $filePdf) }}" target="_blank" class="btn btn-xs btn-outline-secondary">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </a>
                                    @else
                                        <span class="text-muted fs-2xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada riwayat instrumen SPK.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Riwayat Berkas SIP Dokter & Status Pelacakan Jatuh Tempo -->
    <div class="col-lg-6">
        <div class="card shadow-xs h-100">
            <div class="card-header bg-white py-3 fw-bold text-dark d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-file-medical text-primary me-2"></i>Riwayat Berkas SIP & Pelacakan Jatuh Tempo</span>
                <span class="badge bg-primary-subtle text-primary">{{ $dokter->dokumenSip->count() }} Berkas</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Nomor SIP</th>
                            <th>Tempat Praktik</th>
                            <th>Masa Kadaluarsa</th>
                            <th>Status Pelacakan</th>
                            <th>Berkas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dokter->dokumenSip as $itemSip)
                            @php $sisa = $itemSip->hitungSisaHari(); @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold font-monospace text-dark fs-xs">{{ $itemSip->nomor_sip }}</div>
                                    <small class="text-muted fs-2xs">{{ $itemSip->fasyankes ?: 'RSUD Ansari Saleh' }}</small>
                                </td>
                                <td>SIP Ke-{{ $itemSip->tempat_praktik_ke }}</td>
                                <td>
                                    <div class="fw-bold fs-xs {{ $sisa <= 30 ? 'text-danger' : 'text-dark' }}">
                                        {{ $itemSip->tanggal_kadaluarsa ? $itemSip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}
                                    </div>
                                    <small class="text-muted fs-2xs">{{ $sisa < 0 ? abs($sisa) . ' hr lalu' : 'sisa ' . $sisa . ' hr' }}</small>
                                </td>
                                <td>
                                    <span class="status-pill status-{{ $itemSip->status }} fs-2xs">
                                        {{ $itemSip->status_label }}
                                    </span>
                                    @if($itemSip->is_sip_locked)
                                        <div class="fs-3xs text-success mt-1"><i class="fa-solid fa-lock me-1"></i>Terkunci e-Arsip</div>
                                    @endif
                                </td>
                                <td>
                                    @if($itemSip->file_sip)
                                        <a href="{{ asset('storage/' . $itemSip->file_sip) }}" target="_blank" class="btn btn-xs btn-outline-secondary">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </a>
                                    @else
                                        <span class="text-muted fs-2xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat berkas SIP.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
