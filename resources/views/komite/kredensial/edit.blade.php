@extends('layouts.app')

@section('title', 'Ubah SPK & RKK')

@section('content')
<div class="mb-4">
    <a href="{{ route('komite.kredensial.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Kredensial
    </a>
    <h4 class="fw-bold text-dark mb-1">Ubah SPK & RKK: {{ $kredensial->dokter->user->nama }}</h4>
    <p class="text-secondary fs-sm mb-0">Perbarui nomor SK penugasan klinis atau masa berlaku rekredensialing</p>
</div>

<div class="card shadow-xs">
    <div class="card-body p-4">
        <form action="{{ route('komite.kredensial.update', $kredensial->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Dokter Staf Medis</label>
                    <input type="text" class="form-control" value="{{ $kredensial->dokter->user->nama }} ({{ $kredensial->dokter->ksm->nama_ksm ?? '' }})" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Nomor SPK Direktur <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_spk" class="form-control font-monospace @error('nomor_spk') is-invalid @enderror" value="{{ old('nomor_spk', $kredensial->nomor_spk) }}" required>
                    @error('nomor_spk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Tanggal Terbit SK <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_terbit_spk" class="form-control @error('tanggal_terbit_spk') is-invalid @enderror" value="{{ old('tanggal_terbit_spk', $kredensial->tanggal_terbit_spk ? $kredensial->tanggal_terbit_spk->format('Y-m-d') : '') }}" required>
                    @error('tanggal_terbit_spk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Tanggal Berakhir Rekredensial <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_berakhir_spk" class="form-control @error('tanggal_berakhir_spk') is-invalid @enderror" value="{{ old('tanggal_berakhir_spk', $kredensial->tanggal_berakhir_spk ? $kredensial->tanggal_berakhir_spk->format('Y-m-d') : '') }}" required>
                    @error('tanggal_berakhir_spk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fs-sm fw-semibold">Ganti Berkas RKK (Biarkan kosong jika tidak diubah)</label>
                    <input type="file" name="file_rkk" class="form-control @error('file_rkk') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                    @if($kredensial->file_rkk)
                        <small class="text-success fs-xs d-block mt-1">
                            <i class="fa-solid fa-file-check me-1"></i> Berkas tersimpan saat ini: <a href="{{ asset('storage/' . $kredensial->file_rkk) }}" target="_blank">Lihat Berkas RKK</a>
                        </small>
                    @endif
                    @error('file_rkk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="border-top mt-4 pt-3 d-flex justify-content-end gap-2">
                <a href="{{ route('komite.kredensial.index') }}" class="btn btn-light border btn-sm">Batal</a>
                <button type="submit" class="btn btn-teal text-white btn-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan SPK
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
