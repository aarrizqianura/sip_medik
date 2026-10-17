@extends('layouts.app')

@section('title', 'Repositori Arsip Dokumen SIP')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Repositori e-Arsip Dokumen SIP</h4>
        <p class="text-secondary fs-sm mb-0">Arsip digital seluruh berkas Surat Izin Praktik staf medis RSUD dr. H. Moch. Ansari Saleh</p>
    </div>
    @if(in_array(auth()->user()->role, ['dokter', 'super_admin']))
        <div>
            <a href="{{ route('dokter.pengajuan.create') }}" class="btn btn-teal text-white btn-sm">
                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Unggah SIP Baru
            </a>
        </div>
    @endif
</div>

<div class="card shadow-xs">
    <div class="card-body p-4 text-center">
        <p class="text-secondary mb-3">Untuk pengelolaan pengajuan berkas SIP, silakan akses modul pengajuan atau verifikasi.</p>
        <div class="d-flex justify-content-center gap-2">
            @if(auth()->user()->role === 'dokter')
                <a href="{{ route('dokter.pengajuan.index') }}" class="btn btn-teal text-white btn-sm">Ke Pengajuan SIP Saya</a>
            @else
                <a href="{{ route('sdm.dokter.index') }}" class="btn btn-primary btn-sm">Ke Master Data Dokter</a>
                <a href="{{ route('komite.verifikasi.index') }}" class="btn btn-teal text-white btn-sm">Ke Antrean Verifikasi</a>
            @endif
        </div>
    </div>
</div>
@endsection
