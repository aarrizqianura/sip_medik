@extends('layouts.app')

@section('title', 'Tambah Dokter Baru')

@section('content')
<div class="mb-4">
    <a href="{{ route('sdm.dokter.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Dokter
    </a>
    <h4 class="fw-bold text-dark mb-1">Pendaftaran Dokter Baru</h4>
    <p class="text-secondary fs-sm mb-0">Input data identitas kepegawaian, KSM, spesialisasi, dan buat akun akses sistem untuk dokter</p>
</div>

<div class="card shadow-xs">
    <div class="card-body p-4">
        <form action="{{ route('sdm.dokter.store') }}" method="POST">
            @csrf

            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                <i class="fa-solid fa-user-doctor text-teal me-1"></i> Biodata Staf Medis Dokter
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" placeholder="Contoh: dr. Bambang Sudarmono, Sp.A" value="{{ old('nama') }}" required>
                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">NIP ASN / NRP BLUD <span class="text-danger">*</span></label>
                    <input type="text" name="nip_nrp" class="form-control font-monospace @error('nip_nrp') is-invalid @enderror" placeholder="Contoh: 198205122008121002" value="{{ old('nip_nrp') }}" required>
                    @error('nip_nrp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Kelompok Staf Medis (KSM) <span class="text-danger">*</span></label>
                    <select name="id_ksm" class="form-select @error('id_ksm') is-invalid @enderror" required>
                        <option value="">-- Pilih KSM --</option>
                        @foreach($ksmList as $ksm)
                            <option value="{{ $ksm->id }}" {{ old('id_ksm') == $ksm->id ? 'selected' : '' }}>{{ $ksm->nama_ksm }}</option>
                        @endforeach
                    </select>
                    @error('id_ksm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Spesialisasi / Kualifikasi Klinis <span class="text-danger">*</span></label>
                    <input type="text" name="spesialisasi" class="form-control @error('spesialisasi') is-invalid @enderror" placeholder="Contoh: Spesialis Bedah Umum / Subspesialis Onkologi" value="{{ old('spesialisasi') }}" required>
                    @error('spesialisasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Status Kepegawaian <span class="text-danger">*</span></label>
                    <select name="status_kepegawaian" class="form-select @error('status_kepegawaian') is-invalid @enderror" required>
                        <option value="PNS" {{ old('status_kepegawaian') == 'PNS' ? 'selected' : '' }}>PNS (Pegawai Negeri Sipil)</option>
                        <option value="PPPK" {{ old('status_kepegawaian') == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                        <option value="BLUD" {{ old('status_kepegawaian') == 'BLUD' ? 'selected' : '' }}>Non-ASN / Kontrak BLUD</option>
                        <option value="Mitra" {{ old('status_kepegawaian') == 'Mitra' ? 'selected' : '' }}>Dokter Tamu / Mitra</option>
                    </select>
                    @error('status_kepegawaian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Status STR (Surat Tanda Registrasi) <span class="text-danger">*</span></label>
                    <select name="status_str" class="form-select @error('status_str') is-invalid @enderror" required>
                        <option value="seumur_hidup" {{ old('status_str') == 'seumur_hidup' ? 'selected' : '' }}>Seumur Hidup (UU Kesehatan No. 17/2023)</option>
                        <option value="berkala" {{ old('status_str') == 'berkala' ? 'selected' : '' }}>Berkala (Masih Masa Transisi)</option>
                    </select>
                    @error('status_str') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                <i class="fa-solid fa-key text-teal me-1"></i> Akun Login & WhatsApp Reminder
            </h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Username Login <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" placeholder="Contoh: dr_bambang" value="{{ old('username') }}" required>
                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                    <input type="text" name="no_wa" class="form-control @error('no_wa') is-invalid @enderror" placeholder="Contoh: 081234567890" value="{{ old('no_wa') }}" required>
                    <small class="text-muted fs-xs">Nomor ini yang akan menerima notifikasi pengingat SIP harian otomatis.</small>
                    @error('no_wa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Email Dinas / Pribadi <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Contoh: bambang@ansarisaleh.id" value="{{ old('email') }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Kata Sandi Awal <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 6 karakter" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="border-top mt-4 pt-3 d-flex justify-content-end gap-2">
                <a href="{{ route('sdm.dokter.index') }}" class="btn btn-light border btn-sm">Batal</a>
                <button type="submit" class="btn btn-teal text-white btn-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Daftarkan Dokter Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
