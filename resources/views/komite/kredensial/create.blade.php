@extends('layouts.app')

@section('title', 'Penerbitan SPK & RKK Baru')

@section('content')
<div class="mb-4">
    <a href="{{ route('komite.kredensial.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Kredensial
    </a>
    <h4 class="fw-bold text-dark mb-1">Penerbitan Surat Penugasan Klinis (SPK & RKK)</h4>
    <p class="text-secondary fs-sm mb-0">Input nomor SK penugasan klinis direktur beserta rincian kewenangan klinis staf medis (periode 3 tahun)</p>
</div>

<div class="card shadow-xs">
    <div class="card-body p-4">
        <form action="{{ route('komite.kredensial.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Pilih Dokter Staf Medis <span class="text-danger">*</span></label>
                    <select name="id_dokter" class="form-select @error('id_dokter') is-invalid @enderror" required>
                        <option value="">-- Pilih Dokter --</option>
                        @foreach($dokterList as $dok)
                            <option value="{{ $dok->id }}" {{ old('id_dokter') == $dok->id ? 'selected' : '' }}>
                                {{ $dok->user->nama ?? '-' }} ({{ $dok->ksm->nama_ksm ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_dokter') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Nomor SPK Direktur <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_spk" class="form-control font-monospace @error('nomor_spk') is-invalid @enderror" placeholder="Contoh: 800/102/SPK-DIR/RSAS/2026" value="{{ old('nomor_spk') }}" required>
                    @error('nomor_spk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Tanggal Terbit SK <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_terbit_spk" class="form-control @error('tanggal_terbit_spk') is-invalid @enderror" value="{{ old('tanggal_terbit_spk', date('Y-m-d')) }}" required>
                    @error('tanggal_terbit_spk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Tanggal Berakhir Rekredensial (3 Tahun) <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_berakhir_spk" class="form-control @error('tanggal_berakhir_spk') is-invalid @enderror" value="{{ old('tanggal_berakhir_spk', date('Y-m-d', strtotime('+3 years'))) }}" required>
                    @error('tanggal_berakhir_spk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fs-sm fw-semibold">Unggah Berkas Rincian Kewenangan Klinis (RKK)</label>
                    <input type="file" name="file_rkk" class="form-control @error('file_rkk') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted fs-xs">Format diperbolehkan: PDF, JPG, PNG (Maksimal 2 MB).</small>
                    @error('file_rkk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="border-top mt-4 pt-3 d-flex justify-content-end gap-2">
                <a href="{{ route('komite.kredensial.index') }}" class="btn btn-light border btn-sm">Batal</a>
                <button type="submit" class="btn btn-teal text-white btn-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan SPK/RKK Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
