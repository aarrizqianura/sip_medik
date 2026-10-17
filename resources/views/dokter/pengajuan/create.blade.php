@extends('layouts.app')

@section('title', 'Unggah Berkas Perpanjangan SIP Baru')

@section('content')
<div class="mb-4">
    <a href="{{ route('dokter.pengajuan.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Riwayat SIP
    </a>
    <h4 class="fw-bold text-dark mb-1">Unggah Dokumen Perpanjangan SIP Baru</h4>
    <p class="text-secondary fs-sm mb-0">Masukkan metadata izin praktik kedokteran DPMPTSP dan lampirkan pindaian berkas asli (PDF / JPG / PNG maksimal 2MB)</p>
</div>

<div class="card shadow-xs">
    <div class="card-body p-4">
        <form action="{{ route('dokter.pengajuan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @if(in_array(auth()->user()->role, ['super_admin', 'admin_sdm']) && isset($dokterList))
                <div class="mb-3">
                    <label class="form-label fs-sm fw-semibold">Pilih Dokter Pengaju <span class="text-danger">*</span></label>
                    <select name="id_dokter" class="form-select @error('id_dokter') is-invalid @enderror" required>
                        <option value="">-- Pilih Dokter --</option>
                        @foreach($dokterList as $dok)
                            <option value="{{ $dok->id }}">{{ $dok->user->nama ?? '-' }} (NIP: {{ $dok->nip_nrp }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fs-sm fw-semibold">Nomor Surat Izin Praktik (SIP) <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_sip" class="form-control font-monospace @error('nomor_sip') is-invalid @enderror" placeholder="Contoh: 446/012-SIP.DU/DPMPTSP/2026" value="{{ old('nomor_sip') }}" required>
                    <small class="text-muted fs-xs">Pastikan nomor sesuai dengan lembar fisik surat keputusan perizinan.</small>
                    @error('nomor_sip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fs-sm fw-semibold">SIP Tempat Praktik Ke <span class="text-danger">*</span></label>
                    <select name="tempat_praktik_ke" class="form-select @error('tempat_praktik_ke') is-invalid @enderror" required>
                        <option value="1" {{ old('tempat_praktik_ke', '1') == '1' ? 'selected' : '' }}>Tempat Praktik Ke-1 (Utama)</option>
                        <option value="2" {{ old('tempat_praktik_ke') == '2' ? 'selected' : '' }}>Tempat Praktik Ke-2</option>
                        <option value="3" {{ old('tempat_praktik_ke') == '3' ? 'selected' : '' }}>Tempat Praktik Ke-3</option>
                    </select>
                    @error('tempat_praktik_ke') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Tanggal Berlaku / Terbit <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_terbit" class="form-control @error('tanggal_terbit') is-invalid @enderror" value="{{ old('tanggal_terbit', date('Y-m-d')) }}" required>
                    @error('tanggal_terbit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Tanggal Jatuh Tempo (Kedaluwarsa) <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_kadaluarsa" class="form-control @error('tanggal_kadaluarsa') is-invalid @enderror" value="{{ old('tanggal_kadaluarsa') }}" required>
                    <small class="text-muted fs-xs">Tanggal berakhirnya masa berlaku surat izin praktik resmi.</small>
                    @error('tanggal_kadaluarsa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fs-sm fw-semibold">Pindaian Berkas (Scan) SIP Asli <span class="text-danger">*</span></label>
                    <input type="file" name="file_sip" class="form-control @error('file_sip') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                    <small class="text-muted fs-xs">Format diperbolehkan: PDF, JPG, PNG (Maksimal ukuran 2 MB). Pastikan hasil pindaian terbaca jelas untuk telaah Komite Medik.</small>
                    @error('file_sip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="border-top mt-4 pt-3 d-flex justify-content-end gap-2">
                <a href="{{ route('dokter.pengajuan.index') }}" class="btn btn-light border btn-sm">Batal</a>
                <button type="submit" class="btn btn-teal text-white btn-sm">
                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> Ajukan Berkas SIP untuk Verifikasi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
