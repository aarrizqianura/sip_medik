@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Pengguna
    </a>
    <h4 class="fw-bold text-dark mb-1">Tambah Pengguna Baru</h4>
    <p class="text-secondary fs-sm mb-0">Daftarkan akun administrator, staf kepegawaian, verifikator komite medik, atau dokter baru</p>
</div>

<div class="card shadow-xs">
    <div class="card-body p-4">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" placeholder="Contoh: dr. Ahmad Spesialis, Sp.B" value="{{ old('nama') }}" required>
                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Username Login <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" placeholder="Contoh: ahmad_spb" value="{{ old('username') }}" required>
                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Alamat Email Dinas/Pribadi <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Contoh: ahmad@ansarisaleh.id" value="{{ old('email') }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                    <input type="text" name="no_wa" class="form-control @error('no_wa') is-invalid @enderror" placeholder="Format: 081234567890" value="{{ old('no_wa') }}" required>
                    <small class="text-muted fs-xs">Wajib valid untuk penerimaan pengingat WhatsApp otomatis.</small>
                    @error('no_wa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Role / Hak Akses <span class="text-danger">*</span></label>
                    <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="">-- Pilih Role Akses --</option>
                        <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin (IT PDE)</option>
                        <option value="admin_sdm" {{ old('role') == 'admin_sdm' ? 'selected' : '' }}>Admin SDM (Kepegawaian)</option>
                        <option value="verifikator_komite" {{ old('role') == 'verifikator_komite' ? 'selected' : '' }}>Verifikator (Komite Medik)</option>
                        <option value="dokter" {{ old('role') == 'dokter' ? 'selected' : '' }}>Dokter (Staf Medis)</option>
                    </select>
                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Status Akun <span class="text-danger">*</span></label>
                    <select name="aktif" class="form-select @error('aktif') is-invalid @enderror" required>
                        <option value="1" {{ old('aktif', '1') == '1' ? 'selected' : '' }}>Aktif (Dapat Login)</option>
                        <option value="0" {{ old('aktif') == '0' ? 'selected' : '' }}>Non-Aktif (Ditangguhkan)</option>
                    </select>
                    @error('aktif') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Kata Sandi <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 6 karakter" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ketik ulang kata sandi" required>
                </div>
            </div>

            <div class="border-top mt-4 pt-3 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light border btn-sm">Batal</a>
                <button type="submit" class="btn btn-teal text-white btn-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Pengguna Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
