@extends('layouts.app')

@section('title', 'Ubah Kelompok Staf Medis (KSM)')

@section('content')
<div class="mb-4">
    <a href="{{ route('sdm.ksm.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar KSM
    </a>
    <h4 class="fw-bold text-dark mb-1">Ubah KSM: {{ $ksm->nama_ksm }}</h4>
    <p class="text-secondary fs-sm mb-0">Perbarui informasi nama atau deskripsi kelompok staf medis</p>
</div>

<div class="card shadow-xs">
    <div class="card-body p-4">
        <form action="{{ route('sdm.ksm.update', $ksm->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Kode KSM <span class="text-danger">*</span></label>
                    <input type="text" name="kode_ksm" class="form-control font-monospace @error('kode_ksm') is-invalid @enderror" value="{{ old('kode_ksm', $ksm->kode_ksm) }}" required>
                    @error('kode_ksm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Nama Kelompok Staf Medis <span class="text-danger">*</span></label>
                    <input type="text" name="nama_ksm" class="form-control @error('nama_ksm') is-invalid @enderror" value="{{ old('nama_ksm', $ksm->nama_ksm) }}" required>
                    @error('nama_ksm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fs-sm fw-semibold">Keterangan / Deskripsi Pelayanan Klinis</label>
                    <textarea name="keterangan" rows="3" class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan', $ksm->keterangan) }}</textarea>
                    @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="border-top mt-4 pt-3 d-flex justify-content-end gap-2">
                <a href="{{ route('sdm.ksm.index') }}" class="btn btn-light border btn-sm">Batal</a>
                <button type="submit" class="btn btn-teal text-white btn-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan KSM
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
