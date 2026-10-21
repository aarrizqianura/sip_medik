@extends('layouts.app')

@section('title', 'Ubah Profil & Data STR Dokter')

@section('content')
<div class="mb-4">
    <a href="{{ route('sdm.dokter.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Dokter
    </a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Ubah Profil Dokter: {{ $dokter->user->nama }}</h4>
            <p class="text-secondary fs-sm mb-0">Perbarui identitas kepegawaian, kualifikasi klinis, deklarasi tempat praktik, dan salinan e-STR</p>
        </div>
        <div>
            {!! $dokter->status_verifikasi_str_badge !!}
            @if($dokter->is_str_locked)
                <span class="badge bg-dark text-white"><i class="fa-solid fa-lock me-1"></i> STR Terkunci (Read-Only)</span>
            @endif
        </div>
    </div>
</div>

@if($dokter->is_str_locked)
    <div class="alert alert-warning border-warning d-flex align-items-center gap-3 mb-4">
        <i class="fa-solid fa-lock text-warning-emphasis fs-3"></i>
        <div class="fs-sm">
            <strong>Nomor Registrasi STR Terkunci (Read-Only):</strong> Nomor STR dokter ini telah diverifikasi dan disahkan oleh Subkomite Kredensial Komite Medik via SatuSehat SDMK. Nomor STR tidak dapat diubah oleh Admin SDM demi integritas hukum. Jika terdapat kesalahan data STR, hubungi Subkomite Kredensial untuk membuka kunci terlebih dahulu.
        </div>
    </div>
@endif

<div class="card shadow-xs">
    <div class="card-body p-4">
        <form action="{{ route('sdm.dokter.update', $dokter->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Section 1: Biodata Staf Medis Dokter -->
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                <i class="fa-solid fa-user-doctor text-teal me-1"></i> 1. Identitas Kepegawaian & Kualifikasi Klinis
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $dokter->user->nama) }}" required>
                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">NIP ASN / NRP BLUD <span class="text-danger">*</span></label>
                    <input type="text" name="nip_nrp" class="form-control font-monospace @error('nip_nrp') is-invalid @enderror" value="{{ old('nip_nrp', $dokter->nip_nrp) }}" required>
                    @error('nip_nrp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Kelompok Staf Medis (KSM) <span class="text-danger">*</span></label>
                    <select name="id_ksm" class="form-select @error('id_ksm') is-invalid @enderror" required>
                        @foreach($ksmList as $ksm)
                            <option value="{{ $ksm->id }}" {{ old('id_ksm', $dokter->id_ksm) == $ksm->id ? 'selected' : '' }}>{{ $ksm->nama_ksm }}</option>
                        @endforeach
                    </select>
                    @error('id_ksm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Kualifikasi Spesialisasi <span class="text-danger">*</span></label>
                    <input type="text" name="spesialisasi" class="form-control @error('spesialisasi') is-invalid @enderror" value="{{ old('spesialisasi', $dokter->spesialisasi) }}" required>
                    @error('spesialisasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Kualifikasi Subspesialis / Peminatan Klinis</label>
                    <input type="text" name="subspesialis" class="form-control @error('subspesialis') is-invalid @enderror" value="{{ old('subspesialis', $dokter->subspesialis) }}">
                    @error('subspesialis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Status Kepegawaian <span class="text-danger">*</span></label>
                    <select name="status_kepegawaian" class="form-select @error('status_kepegawaian') is-invalid @enderror" required>
                        <option value="PNS" {{ old('status_kepegawaian', $dokter->status_kepegawaian) == 'PNS' ? 'selected' : '' }}>PNS</option>
                        <option value="PPPK" {{ old('status_kepegawaian', $dokter->status_kepegawaian) == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                        <option value="BLUD" {{ old('status_kepegawaian', $dokter->status_kepegawaian) == 'BLUD' ? 'selected' : '' }}>BLUD</option>
                        <option value="Mitra" {{ old('status_kepegawaian', $dokter->status_kepegawaian) == 'Mitra' ? 'selected' : '' }}>Mitra</option>
                    </select>
                    @error('status_kepegawaian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Section 2: Perekaman Data STR (Fondasi Hukum) -->
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                <i class="fa-solid fa-certificate text-teal me-1"></i> 2. Fondasi Hukum: Perekaman Master Data STR & Deklarasi Praktik
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">
                        Nomor Registrasi STR (16 Digit) <span class="text-danger">*</span>
                        @if($dokter->is_str_locked)
                            <span class="badge bg-success ms-1"><i class="fa-solid fa-lock me-1"></i> Terkunci</span>
                        @endif
                    </label>
                    <div class="input-group">
                        <span class="input-group-text font-monospace {{ $dokter->is_str_locked ? 'bg-secondary text-white' : 'bg-light text-teal' }}">
                            <i class="fa-solid {{ $dokter->is_str_locked ? 'fa-lock' : 'fa-id-badge' }}"></i>
                        </span>
                        <input type="text" name="nomor_str" id="nomor_str" maxlength="16" minlength="16"
                               class="form-control font-monospace @error('nomor_str') is-invalid @enderror {{ $dokter->is_str_locked ? 'bg-light text-muted' : '' }}"
                               value="{{ old('nomor_str', $dokter->nomor_str) }}"
                               {{ $dokter->is_str_locked ? 'readonly' : 'required' }}>
                        @error('nomor_str') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        @if($dokter->is_str_locked)
                            <small class="text-danger fw-semibold fs-xs"><i class="fa-solid fa-shield-halved me-1"></i>Nomor STR telah tervalidasi SatuSehat SDMK & Terkunci (Read-Only).</small>
                        @else
                            <small class="text-muted fs-xs">Wajib tepat 16 digit angka KKI/Kemenkes.</small>
                            <small class="text-muted fs-xs" id="strCounter">{{ strlen($dokter->nomor_str ?? '') }} / 16 digit</small>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">
                        Deklarasi Tempat Praktik di RSUD Ansari Saleh <span class="text-danger">*</span>
                    </label>
                    <select name="tempat_praktik_ke" class="form-select @error('tempat_praktik_ke') is-invalid @enderror" required>
                        <option value="1" {{ old('tempat_praktik_ke', $dokter->tempat_praktik_ke) == 1 ? 'selected' : '' }}>SIP Ke-1 (Fasilitas Utama)</option>
                        <option value="2" {{ old('tempat_praktik_ke', $dokter->tempat_praktik_ke) == 2 ? 'selected' : '' }}>SIP Ke-2</option>
                        <option value="3" {{ old('tempat_praktik_ke', $dokter->tempat_praktik_ke) == 3 ? 'selected' : '' }}>SIP Ke-3</option>
                    </select>
                    @error('tempat_praktik_ke') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fs-sm fw-semibold">Masa Berlaku STR <span class="text-danger">*</span></label>
                    <select name="status_str" id="status_str" class="form-select @error('status_str') is-invalid @enderror" required onchange="toggleTanggalKadaluarsa()">
                        <option value="seumur_hidup" {{ old('status_str', $dokter->status_str) == 'seumur_hidup' ? 'selected' : '' }}>Seumur Hidup (UU No. 17/2023)</option>
                        <option value="berkala" {{ old('status_str', $dokter->status_str) == 'berkala' ? 'selected' : '' }}>Berkala</option>
                    </select>
                    @error('status_str') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fs-sm fw-semibold">Tanggal Terbit STR <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_terbit_str" class="form-control @error('tanggal_terbit_str') is-invalid @enderror" value="{{ old('tanggal_terbit_str', $dokter->tanggal_terbit_str ? $dokter->tanggal_terbit_str->format('Y-m-d') : '') }}" required>
                    @error('tanggal_terbit_str') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4" id="divTanggalKadaluarsa" style="{{ $dokter->status_str === 'berkala' ? '' : 'display: none;' }}">
                    <label class="form-label fs-sm fw-semibold">Tanggal Kadaluarsa STR <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_kadaluarsa_str" id="tanggal_kadaluarsa_str" class="form-control @error('tanggal_kadaluarsa_str') is-invalid @enderror" value="{{ old('tanggal_kadaluarsa_str', $dokter->tanggal_kadaluarsa_str ? $dokter->tanggal_kadaluarsa_str->format('Y-m-d') : '') }}">
                    @error('tanggal_kadaluarsa_str') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label fs-sm fw-semibold">Salinan Digital e-STR Resmi (KKI / Kemenkes)</label>
                    @if($dokter->file_str)
                        <div class="mb-2 p-2 bg-light border rounded d-flex align-items-center justify-content-between">
                            <span class="fs-xs text-dark"><i class="fa-solid fa-file-pdf text-danger me-1"></i> Berkas e-STR saat ini telah terunggah.</span>
                            <a href="{{ asset('storage/' . $dokter->file_str) }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                <i class="fa-solid fa-eye me-1"></i> Lihat Berkas e-STR
                            </a>
                        </div>
                    @endif
                    <input type="file" name="file_str" class="form-control @error('file_str') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted fs-xs">Unggah berkas baru bila ingin memperbarui salinan e-STR (PDF/JPG/PNG max 5MB). Kosongkan jika tidak ada perubahan.</small>
                    @error('file_str') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Section 3: Akun Login & Kontak -->
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                <i class="fa-solid fa-key text-teal me-1"></i> 3. Akun Login & WhatsApp Reminder
            </h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Username Login <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $dokter->user->username) }}" required>
                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                    <input type="text" name="no_wa" class="form-control @error('no_wa') is-invalid @enderror" value="{{ old('no_wa', $dokter->user->no_wa) }}" required>
                    @error('no_wa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Email Dinas / Pribadi <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $dokter->user->email) }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Ganti Kata Sandi (Kosongkan bila tidak diubah)</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 6 karakter jika ingin mengganti">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="border-top mt-4 pt-3 d-flex justify-content-end gap-2">
                <a href="{{ route('sdm.dokter.index') }}" class="btn btn-light border btn-sm">Batal</a>
                <button type="submit" class="btn btn-teal text-white btn-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan Profil & STR
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const strInput = document.getElementById('nomor_str');
const strCounter = document.getElementById('strCounter');

if (strInput && !strInput.readOnly) {
    strInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 16);
        if (strCounter) {
            strCounter.textContent = this.value.length + ' / 16 digit';
            if (this.value.length === 16) {
                strCounter.className = 'text-success fw-bold fs-xs';
            } else {
                strCounter.className = 'text-muted fs-xs';
            }
        }
    });
}

function toggleTanggalKadaluarsa() {
    const status = document.getElementById('status_str').value;
    const div = document.getElementById('divTanggalKadaluarsa');
    const input = document.getElementById('tanggal_kadaluarsa_str');
    if (status === 'berkala') {
        div.style.display = 'block';
        input.required = true;
    } else {
        div.style.display = 'none';
        input.required = false;
    }
}
</script>
@endpush
@endsection
