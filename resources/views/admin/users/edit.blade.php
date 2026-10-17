@extends('layouts.app')

@section('title', 'Ubah Akun Pengguna')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Pengguna
    </a>
    <h4 class="fw-bold text-dark mb-1">Ubah Akun Pengguna: {{ $user->nama }}</h4>
    <p class="text-secondary fs-sm mb-0">Perbarui biodata akun, nomor kontak WhatsApp, atau ubah hak akses</p>
</div>

<div class="card shadow-xs">
    <div class="card-body p-4">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $user->nama) }}" required>
                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Username Login <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Alamat Email Dinas/Pribadi <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                    <input type="text" name="no_wa" class="form-control @error('no_wa') is-invalid @enderror" value="{{ old('no_wa', $user->no_wa) }}" required>
                    @error('no_wa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Role / Hak Akses <span class="text-danger">*</span></label>
                    <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin (IT PDE)</option>
                        <option value="admin_sdm" {{ old('role', $user->role) == 'admin_sdm' ? 'selected' : '' }}>Admin SDM (Kepegawaian)</option>
                        <option value="verifikator_komite" {{ old('role', $user->role) == 'verifikator_komite' ? 'selected' : '' }}>Verifikator (Komite Medik)</option>
                        <option value="dokter" {{ old('role', $user->role) == 'dokter' ? 'selected' : '' }}>Dokter (Staf Medis)</option>
                    </select>
                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Status Akun <span class="text-danger">*</span></label>
                    <select name="aktif" class="form-select @error('aktif') is-invalid @enderror" required>
                        <option value="1" {{ old('aktif', $user->aktif ? '1' : '0') == '1' ? 'selected' : '' }}>Aktif (Dapat Login)</option>
                        <option value="0" {{ old('aktif', $user->aktif ? '1' : '0') == '0' ? 'selected' : '' }}>Non-Aktif (Ditangguhkan)</option>
                    </select>
                    @error('aktif') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Kata Sandi Baru (Kosongkan bila tidak diubah)</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 6 karakter jika ingin mengganti">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ketik ulang kata sandi baru">
                </div>
            </div>

            <div class="border-top mt-4 pt-3 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light border btn-sm">Batal</a>
                <button type="submit" class="btn btn-teal text-white btn-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
