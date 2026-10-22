@extends('layouts.app')

@section('title', 'Arsip Digital SIP - ' . $sip->nomor_sip)

@section('content')
<div class="mb-4">
    <a href="{{ url()->previous() }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali
    </a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">e-Arsip Surat Izin Praktik (SIP)</h4>
            <p class="text-secondary fs-sm mb-0">Nomor Registrasi: <span class="font-monospace fw-bold">{{ $sip->nomor_sip }}</span></p>
        </div>
        <div>
            <span class="status-pill status-{{ $sip->status }} fs-sm px-3 py-2">
                {{ $sip->status_label }}
            </span>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-xs">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="fw-bold text-dark"><i class="fa-solid fa-file-pdf text-danger me-2"></i> Berkas Digital Resmi</span>
                @if($sip->file_sip)
                    <a href="{{ asset('storage/' . $sip->file_sip) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-download me-1"></i> Unduh Berkas
                    </a>
                @endif
            </div>
            <div class="card-body p-0" style="min-height: 550px;">
                @if($sip->file_sip)
                    @php $ext = pathinfo($sip->file_sip, PATHINFO_EXTENSION); @endphp
                    @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                        <div class="text-center p-3">
                            <img src="{{ asset('storage/' . $sip->file_sip) }}" class="img-fluid rounded border" alt="SIP">
                        </div>
                    @else
                        <iframe src="{{ asset('storage/' . $sip->file_sip) }}" class="w-100 border-0" style="height: 600px;"></iframe>
                    @endif
                @else
                    <div class="text-center py-5 text-muted">
                        Berkas tidak ditemukan.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-xs mb-4">
            <div class="card-header fw-bold text-dark">
                <i class="fa-solid fa-circle-info text-teal me-2"></i>
                Metadata Berkas
            </div>
            <div class="card-body p-3">
                <ul class="list-group list-group-flush fs-sm">
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Dokter:</span>
                        <strong class="text-dark">{{ $sip->dokter->user->nama ?? '-' }}</strong>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">KSM:</span>
                        <span>{{ $sip->dokter->ksm->nama_ksm ?? '-' }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Fasyankes Tujuan:</span>
                        <strong class="text-dark">{{ $sip->fasyankes ?: 'RSUD dr. H. Moch. Ansari Saleh' }}</strong>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Tempat Praktik:</span>
                        <span>SIP Ke-{{ $sip->tempat_praktik_ke }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Prasyarat SKP:</span>
                        <span>{!! $sip->status_kecukupan_skp_badge !!}</span>
                    </li>
                    @if($sip->file_rekomendasi_skp)
                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Rekomendasi SKP:</span>
                            <a href="{{ asset('storage/' . $sip->file_rekomendasi_skp) }}" target="_blank" class="btn btn-2xs btn-outline-teal text-teal">
                                <i class="fa-solid fa-file-pdf me-1"></i> Unduh SKP SatuSehat
                            </a>
                        </li>
                    @endif
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Tanggal Terbit:</span>
                        <span>{{ $sip->tanggal_terbit ? $sip->tanggal_terbit->translatedFormat('d F Y') : '-' }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Jatuh Tempo:</span>
                        <span class="fw-bold text-danger">{{ $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->translatedFormat('d F Y') : '-' }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Sisa Masa Berlaku:</span>
                        @php $sisa = $sip->hitungSisaHari(); @endphp
                        <span class="badge {{ $sisa < 0 ? 'bg-danger text-white' : ($sisa <= 30 ? 'bg-warning text-dark' : 'bg-success text-white') }}">
                            {{ $sisa < 0 ? 'Kedaluwarsa ' . abs($sisa) . ' hr' : $sisa . ' Hari' }}
                        </span>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Status Validasi:</span>
                        <span class="badge {{ $sip->status_verifikasi === 'disetujui' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ ucfirst($sip->status_verifikasi) }}
                        </span>
                    </li>
                    @if($sip->verifikator)
                        <li class="list-group-item px-0 py-2">
                            <span class="text-muted d-block fs-xs">Disahkan Oleh:</span>
                            <strong class="text-dark">{{ $sip->verifikator->nama }}</strong>
                            <div class="text-muted fs-xs">{{ $sip->verified_at ? $sip->verified_at->format('d/m/Y H:i') : '' }} WITA</div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        @if($sip->catatan_verifikasi)
            <div class="card shadow-xs">
                <div class="card-header fw-bold text-dark">
                    <i class="fa-solid fa-notes-medical text-teal me-2"></i>
                    Catatan Telaah Subkomite
                </div>
                <div class="card-body p-3 fs-sm text-secondary">
                    {{ $sip->catatan_verifikasi }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
