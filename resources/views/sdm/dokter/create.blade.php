@extends('layouts.app')

@section('title', 'Tambah Dokter & Perekaman Data STR')

@section('content')
<div class="mb-4">
    <a href="{{ route('sdm.dokter.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Dokter
    </a>
    <h4 class="fw-bold text-dark mb-1">Perekaman Master Data STR & Profil Dokter</h4>
    <p class="text-secondary fs-sm mb-0">Tahap 1: Pencatatan identitas, kualifikasi klinis, nomor registrasi STR 16 digit, deklarasi tempat praktik di RSUD Ansari Saleh, dan salinan e-STR</p>
</div>

<div class="card shadow-xs">
    <div class="card-body p-4">
        <form action="{{ route('sdm.dokter.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Section 1: Biodata Staf Medis Dokter -->
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                <i class="fa-solid fa-user-doctor text-teal me-1"></i> 1. Identitas Kepegawaian & Kualifikasi Klinis
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
                    <label class="form-label fs-sm fw-semibold">Kualifikasi Spesialisasi <span class="text-danger">*</span></label>
                    <input type="text" name="spesialisasi" class="form-control @error('spesialisasi') is-invalid @enderror" placeholder="Contoh: Spesialis Bedah Anak" value="{{ old('spesialisasi') }}" required>
                    @error('spesialisasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">Kualifikasi Subspesialis / Peminatan Klinis</label>
                    <input type="text" name="subspesialis" class="form-control @error('subspesialis') is-invalid @enderror" placeholder="Contoh: Subspesialis Digestif / Onkologi (bila ada)" value="{{ old('subspesialis') }}">
                    @error('subspesialis') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
            </div>

            <!-- Section 2: Perekaman Data STR (Fondasi Hukum) -->
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                <i class="fa-solid fa-certificate text-teal me-1"></i> 2. Fondasi Hukum: Perekaman Master Data STR & Deklarasi Tempat Praktik
            </h6>
            <div class="alert alert-light border d-flex align-items-center gap-2 py-2 px-3 mb-3 fs-xs text-secondary">
                <i class="fa-solid fa-circle-info text-teal fs-5"></i>
                <span>Data STR yang diinput akan diverifikasi mandiri oleh <strong>Subkomite Kredensial Komite Medik</strong> terhadap pangkalan data <strong>SatuSehat SDMK Kemenkes RI</strong> sebelum dikunci permanen.</span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">
                        Nomor Registrasi STR (16 Digit) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text font-monospace bg-light"><i class="fa-solid fa-id-badge text-teal"></i></span>
                        <input type="text" name="nomor_str" id="nomor_str" maxlength="16" minlength="16"
                               class="form-control font-monospace @error('nomor_str') is-invalid @enderror"
                               placeholder="16 digit angka (contoh: 3311801223123456)"
                               value="{{ old('nomor_str') }}" required>
                        @error('nomor_str') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted fs-xs">Wajib tepat 16 digit angka sesuai standar KKI/Kemenkes.</small>
                        <small class="text-muted fs-xs" id="strCounter">0 / 16 digit</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fs-sm fw-semibold">
                        Deklarasi Tempat Praktik di RSUD Ansari Saleh <span class="text-danger">*</span>
                    </label>
                    <select name="tempat_praktik_ke" class="form-select @error('tempat_praktik_ke') is-invalid @enderror" required>
                        <option value="1" {{ old('tempat_praktik_ke', '1') == '1' ? 'selected' : '' }}>SIP Ke-1 (Fasilitas Pelayanan Kesehatan Utama)</option>
                        <option value="2" {{ old('tempat_praktik_ke') == '2' ? 'selected' : '' }}>SIP Ke-2</option>
                        <option value="3" {{ old('tempat_praktik_ke') == '3' ? 'selected' : '' }}>SIP Ke-3</option>
                    </select>
                    <small class="text-muted fs-xs">Deklarasi tempat praktik keberapa RSUD dr. H. Moch. Ansari Saleh difungsikan.</small>
                    @error('tempat_praktik_ke') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fs-sm fw-semibold">Masa Berlaku STR <span class="text-danger">*</span></label>
                    <select name="status_str" id="status_str" class="form-select @error('status_str') is-invalid @enderror" required onchange="toggleTanggalKadaluarsa()">
                        <option value="seumur_hidup" {{ old('status_str', 'seumur_hidup') == 'seumur_hidup' ? 'selected' : '' }}>Seumur Hidup (UU No. 17/2023)</option>
                        <option value="berkala" {{ old('status_str') == 'berkala' ? 'selected' : '' }}>Berkala (Masa Transisi)</option>
                    </select>
                    @error('status_str') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fs-sm fw-semibold">Tanggal Terbit STR <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_terbit_str" class="form-control @error('tanggal_terbit_str') is-invalid @enderror" value="{{ old('tanggal_terbit_str') }}" required>
                    @error('tanggal_terbit_str') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4" id="divTanggalKadaluarsa" style="display: none;">
                    <label class="form-label fs-sm fw-semibold">Tanggal Kadaluarsa STR <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_kadaluarsa_str" id="tanggal_kadaluarsa_str" class="form-control @error('tanggal_kadaluarsa_str') is-invalid @enderror" value="{{ old('tanggal_kadaluarsa_str') }}">
                    @error('tanggal_kadaluarsa_str') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label fs-sm fw-semibold">
                        Unggah Salinan Digital e-STR Resmi (KKI / Kemenkes) <span class="text-danger">*</span>
                    </label>
                    <input type="file" name="file_str" class="form-control @error('file_str') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                    <small class="text-muted fs-xs">Format: PDF, JPG, JPEG, atau PNG (Maksimal 5 MB). Pastikan barcode/QR e-STR terbaca jelas.</small>
                    @error('file_str') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Section 3: Akun Login & WhatsApp Reminder -->
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                <i class="fa-solid fa-key text-teal me-1"></i> 3. Kredensial Akun Login Dokter & WhatsApp Pengingat
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
                    <small class="text-muted fs-xs">Nomor ini yang akan menerima notifikasi pengingat SIP & update kredensial otomatis.</small>
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
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Master Data & e-STR Dokter
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const strInput = document.getElementById('nomor_str');
const strCounter = document.getElementById('strCounter');

strInput.addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 16);
    strCounter.textContent = this.value.length + ' / 16 digit';
    if (this.value.length === 16) {
        strCounter.className = 'text-success fw-bold fs-xs';
    } else {
        strCounter.className = 'text-muted fs-xs';
    }
});

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
        input.value = '';
    }
}
toggleTanggalKadaluarsa();
</script>
@endpush
@endsection
