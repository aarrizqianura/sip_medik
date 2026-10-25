@extends('layouts.app')

@section('title', 'Tambah Dokter - 2-Step Wizard Registrasi')

@section('content')
<div class="mb-4">
    <a href="{{ route('sdm.dokter.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Master Data Dokter
    </a>
    <h4 class="fw-bold text-dark mb-1">
        <i class="fa-solid fa-user-plus text-teal me-2"></i>Tambah Dokter: Perekaman Master Data STR (2-Step Wizard)
    </h4>
    <p class="text-secondary fs-sm mb-0">Pencatatan bertahap: Identitas & Penempatan Staf Medis, dilanjutkan Rekonsiliasi STR 16 Digit & Pembuatan Akun</p>
</div>

<!-- Wizard Step Header -->
<div class="card shadow-xs border-0 mb-4">
    <div class="card-body p-3">
        <div class="row text-center position-relative">
            <div class="col-6">
                <div class="p-2 rounded-3 border-2 border-bottom transition-all" id="tabIndicator1" style="border-bottom: 3px solid var(--primary-teal) !important;">
                    <div class="rounded-circle bg-teal text-white fw-bold d-inline-flex align-items-center justify-content-center mb-1" style="width: 32px; height: 32px;" id="bubble1">1</div>
                    <div class="fw-bold fs-xs text-dark" id="label1">Langkah 1: Identitas & Penempatan</div>
                    <small class="text-muted fs-3xs">Biodata, NIP, KSM & Jenjang Spesialisasi</small>
                </div>
            </div>
            <div class="col-6">
                <div class="p-2 rounded-3 border-2 border-bottom transition-all" id="tabIndicator2" style="border-bottom: 3px solid transparent !important;">
                    <div class="rounded-circle bg-light text-muted border fw-bold d-inline-flex align-items-center justify-content-center mb-1" style="width: 32px; height: 32px;" id="bubble2">2</div>
                    <div class="fw-semibold fs-xs text-muted" id="label2">Langkah 2: Data STR & Akun Dokter</div>
                    <small class="text-muted fs-3xs">STR 16 Digit, Berkas e-STR & No. WhatsApp</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ route('sdm.dokter.store') }}" method="POST" enctype="multipart/form-data" id="formTambahDokterWizard">
            @csrf

            <!-- ==================== LANGKAH 1: IDENTITAS & PENEMPATAN ==================== -->
            <div id="stepPane1">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                    <i class="fa-solid fa-id-card text-teal me-1"></i> 1. Identitas Kepegawaian & Jenjang Spesialisasi
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="inputNama" class="form-control @error('nama') is-invalid @enderror" placeholder="Contoh: dr. Bambang Sudarmono, Sp.A" value="{{ old('nama') }}" required>
                        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">NIP ASN / NRP BLUD <span class="text-danger">*</span></label>
                        <input type="text" name="nip_nrp" id="inputNip" class="form-control font-monospace @error('nip_nrp') is-invalid @enderror" placeholder="Contoh: 198205122008121002" value="{{ old('nip_nrp') }}" required>
                        @error('nip_nrp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">Kelompok Staf Medis (KSM) <span class="text-danger">*</span></label>
                        <select name="id_ksm" id="selectKsm" class="form-select @error('id_ksm') is-invalid @enderror" required>
                            <option value="">-- Pilih KSM --</option>
                            @foreach($ksmList as $ksm)
                                <option value="{{ $ksm->id }}" {{ old('id_ksm') == $ksm->id ? 'selected' : '' }}>{{ $ksm->nama_ksm }}</option>
                            @endforeach
                        </select>
                        @error('id_ksm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">Jenjang Spesialisasi <span class="text-danger">*</span></label>
                        <input type="text" name="spesialisasi" id="inputSpesialisasi" class="form-control @error('spesialisasi') is-invalid @enderror" placeholder="Contoh: Spesialis Bedah Anak" value="{{ old('spesialisasi') }}" required>
                        @error('spesialisasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">Subspesialis / Peminatan Klinis</label>
                        <input type="text" name="subspesialis" class="form-control @error('subspesialis') is-invalid @enderror" placeholder="Contoh: Subspesialis Onkologi (opsional)" value="{{ old('subspesialis') }}">
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

                <div class="d-flex justify-content-end pt-3 border-top">
                    <button type="button" class="btn btn-teal text-white btn-sm px-4 fw-bold shadow-sm" onclick="wizardNextStep()">
                        Lanjut ke Langkah 2: Data STR & Akun <i class="fa-solid fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- ==================== LANGKAH 2: DATA STR & AKUN ==================== -->
            <div id="stepPane2" class="d-none">
                <!-- Data STR -->
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                    <i class="fa-solid fa-certificate text-teal me-1"></i> 2. Master Data STR 16 Digit & e-STR Resmi
                </h6>
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
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted fs-3xs">Wajib tepat 16 digit angka sesuai SatuSehat Kemenkes.</small>
                            <small class="text-muted fs-3xs" id="strCounter">0 / 16 digit</small>
                        </div>
                        @error('nomor_str') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">
                            Deklarasi Tempat Praktik di RSUD Ansari Saleh <span class="text-danger">*</span>
                        </label>
                        <select name="tempat_praktik_ke" class="form-select @error('tempat_praktik_ke') is-invalid @enderror" required>
                            <option value="1" {{ old('tempat_praktik_ke', '1') == '1' ? 'selected' : '' }}>SIP Ke-1 (Fasyankes Utama)</option>
                            <option value="2" {{ old('tempat_praktik_ke') == '2' ? 'selected' : '' }}>SIP Ke-2</option>
                            <option value="3" {{ old('tempat_praktik_ke') == '3' ? 'selected' : '' }}>SIP Ke-3</option>
                        </select>
                        @error('tempat_praktik_ke') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-sm fw-semibold">Masa Berlaku STR <span class="text-danger">*</span></label>
                        <select name="status_str" id="status_str" class="form-select @error('status_str') is-invalid @enderror" required onchange="toggleTanggalKadaluarsa()">
                            <option value="seumur_hidup" {{ old('status_str', 'seumur_hidup') == 'seumur_hidup' ? 'selected' : '' }}>Seumur Hidup (UU No. 17/2023)</option>
                            <option value="berkala" {{ old('status_str') == 'berkala' ? 'selected' : '' }}>Berkala (Masa Transisi)</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-sm fw-semibold">Tanggal Terbit STR <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_terbit_str" class="form-control @error('tanggal_terbit_str') is-invalid @enderror" value="{{ old('tanggal_terbit_str') }}" required>
                    </div>

                    <div class="col-md-4" id="divTanggalKadaluarsa" style="display: none;">
                        <label class="form-label fs-sm fw-semibold">Tanggal Kadaluarsa STR <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_kadaluarsa_str" id="tanggal_kadaluarsa_str" class="form-control @error('tanggal_kadaluarsa_str') is-invalid @enderror" value="{{ old('tanggal_kadaluarsa_str') }}">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fs-sm fw-semibold">
                            Unggah Salinan Digital e-STR Resmi (KKI / Kemenkes) <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file_str" class="form-control @error('file_str') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted fs-3xs">Format: PDF, JPG, JPEG, atau PNG (Maks 5 MB).</small>
                        @error('file_str') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Pembuatan Akun Login & WhatsApp -->
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                    <i class="fa-solid fa-key text-teal me-1"></i> 3. Kredensial Akun Login Dokter & Otomatisasi WhatsApp
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-brands fa-whatsapp text-success"></i></span>
                            <input type="text" name="no_wa" class="form-control @error('no_wa') is-invalid @enderror" placeholder="Contoh: 081234567890" value="{{ old('no_wa') }}" required>
                        </div>
                        <small class="text-muted fs-3xs">Menerima blast notifikasi pengingat & update kredensial otomatis.</small>
                        @error('no_wa') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">Username Login Dokter <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" placeholder="Contoh: dr_bambang" value="{{ old('username') }}" required>
                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">Email Dinas / Pribadi <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Contoh: bambang@ansarisaleh.id" value="{{ old('email') }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">Kata Sandi Awal Akun <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 6 karakter" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-between pt-3 border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="wizardPrevStep()">
                        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Langkah 1
                    </button>
                    <button type="submit" class="btn btn-teal text-white btn-sm px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Master Data & e-STR Dokter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function wizardNextStep() {
        const nama = document.getElementById('inputNama').value.trim();
        const nip = document.getElementById('inputNip').value.trim();
        const ksm = document.getElementById('selectKsm').value;
        const spesialisasi = document.getElementById('inputSpesialisasi').value.trim();

        if (!nama || !nip || !ksm || !spesialisasi) {
            alert('Mohon lengkapi seluruh isian wajib pada Langkah 1 terlebih dahulu.');
            return;
        }

        document.getElementById('stepPane1').classList.add('d-none');
        document.getElementById('stepPane2').classList.remove('d-none');

        document.getElementById('tabIndicator1').style.borderBottom = '3px solid transparent';
        document.getElementById('label1').classList.remove('text-dark', 'fw-bold');
        document.getElementById('label1').classList.add('text-muted');
        document.getElementById('bubble1').className = 'rounded-circle bg-success text-white fw-bold d-inline-flex align-items-center justify-content-center mb-1';
        document.getElementById('bubble1').innerHTML = '<i class="fa-solid fa-check"></i>';

        document.getElementById('tabIndicator2').style.borderBottom = '3px solid var(--primary-teal)';
        document.getElementById('label2').classList.remove('text-muted');
        document.getElementById('label2').classList.add('text-dark', 'fw-bold');
        document.getElementById('bubble2').className = 'rounded-circle bg-teal text-white fw-bold d-inline-flex align-items-center justify-content-center mb-1';

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function wizardPrevStep() {
        document.getElementById('stepPane2').classList.add('d-none');
        document.getElementById('stepPane1').classList.remove('d-none');

        document.getElementById('tabIndicator2').style.borderBottom = '3px solid transparent';
        document.getElementById('label2').classList.remove('text-dark', 'fw-bold');
        document.getElementById('label2').classList.add('text-muted');
        document.getElementById('bubble2').className = 'rounded-circle bg-light text-muted border fw-bold d-inline-flex align-items-center justify-content-center mb-1';
        document.getElementById('bubble2').textContent = '2';

        document.getElementById('tabIndicator1').style.borderBottom = '3px solid var(--primary-teal)';
        document.getElementById('label1').classList.remove('text-muted');
        document.getElementById('label1').classList.add('text-dark', 'fw-bold');
        document.getElementById('bubble1').className = 'rounded-circle bg-teal text-white fw-bold d-inline-flex align-items-center justify-content-center mb-1';
        document.getElementById('bubble1').textContent = '1';

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    const strInput = document.getElementById('nomor_str');
    const strCounter = document.getElementById('strCounter');

    strInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 16);
        strCounter.textContent = this.value.length + ' / 16 digit';
        if (this.value.length === 16) {
            strCounter.className = 'text-success fw-bold fs-3xs';
        } else {
            strCounter.className = 'text-muted fs-3xs';
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
</script>
@endpush
@endsection
