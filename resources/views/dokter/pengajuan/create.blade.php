@extends('layouts.app')

@section('title', 'Tahap 2: Input Prasyarat SIP & Status Kecukupan SKP (Dokter)')

@section('content')
<div class="mb-4">
    <a href="{{ route('dokter.pengajuan.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Riwayat SIP
    </a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-teal-subtle text-teal border border-teal-subtle px-2 py-1 fs-xs fw-bold">TAHAP 2</span>
                <h4 class="fw-bold text-dark mb-0">Input Prasyarat SIP & Status Kecukupan SKP</h4>
            </div>
            <p class="text-secondary fs-sm mb-0">
                Portal Mandiri Dokter: Periksa kecukupan SKP SatuSehat, input metadata izin DPMPTSP tertuju ke RSUD dr. H. Moch. Ansari Saleh, dan unggah pindaian lembar SIP format PDF.
            </p>
        </div>
        <div class="text-md-end">
            <span class="badge bg-light text-dark border px-3 py-2 fs-xs">
                <i class="fa-solid fa-hospital-user text-teal me-1"></i> {{ $dokter->user->nama ?? auth()->user()->nama }}
            </span>
        </div>
    </div>
</div>

<!-- Informasi Status Fondasi Hukum STR (Tahap 1) -->
<div class="alert alert-success border-success bg-light-teal d-flex align-items-center justify-content-between p-3 mb-4 shadow-2xs">
    <div class="d-flex align-items-center gap-3">
        <i class="fa-solid fa-shield-check text-success fs-3"></i>
        <div>
            <div class="fw-bold text-dark fs-sm">Fondasi Hukum STR Tervalidasi & Terkunci (Tahap 1 Terpenuhi)</div>
            <div class="fs-xs text-muted">
                No. Registrasi STR: <span class="font-monospace fw-bold text-dark">{{ chunk_split($dokter->nomor_str, 4, ' ') }}</span> &bullet;
                KSM: <strong>{{ $dokter->ksm->nama_ksm ?? '-' }}</strong> &bullet;
                Status Praktik: <strong class="text-teal">{{ $dokter->tempat_praktik_label }}</strong>
            </div>
        </div>
    </div>
    <span class="badge bg-success text-white"><i class="fa-solid fa-lock me-1"></i> STR Absah</span>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-xs mb-4" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation fs-5 mt-1 text-danger"></i>
            <div>
                <strong class="d-block mb-1">Terdapat kesalahan pada isian formulir pengajuan:</strong>
                <ul class="mb-0 ps-3 fs-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('dokter.pengajuan.store') }}" method="POST" enctype="multipart/form-data" id="formPengajuanSip">
    @csrf

    @if(in_array(auth()->user()->role, ['super_admin', 'admin_sdm']) && isset($dokterList))
        <div class="card shadow-xs mb-4 border">
            <div class="card-body p-3">
                <label class="form-label fs-sm fw-semibold">Pilih Dokter Pengaju <span class="text-danger">*</span></label>
                <select name="id_dokter" class="form-select @error('id_dokter') is-invalid @enderror" required>
                    <option value="">-- Pilih Dokter --</option>
                    @foreach($dokterList as $dok)
                        <option value="{{ $dok->id }}">{{ $dok->user->nama ?? '-' }} (NIP: {{ $dok->nip_nrp }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    <!-- BAGIAN 1: Cek Prasyarat SKP -->
    <div class="card shadow-xs mb-4 border">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 13px;">1</div>
                <h6 class="fw-bold text-dark mb-0">Cek Prasyarat SKP (Satuan Kredit Profesi)</h6>
            </div>
            <span class="badge bg-teal-subtle text-teal border border-teal-subtle fs-xs">
                <i class="fa-solid fa-cloud me-1"></i> Platform SatuSehat Kemenkes
            </span>
        </div>
        <div class="card-body p-4">
            <div class="p-3 bg-light rounded-3 border mb-3">
                <div class="d-flex align-items-start gap-2 fs-sm text-secondary">
                    <i class="fa-solid fa-circle-info text-teal mt-1"></i>
                    <div>
                        Sesuai ketentuan perpanjangan izin praktik kedokteran Kemenkes RI, dokter wajib menandai status kecukupan <strong>Satuan Kredit Profesi (SKP)</strong> atau melampirkan berkas <strong>Surat Rekomendasi Kecukupan SKP</strong> yang diterbitkan melalui platform SatuSehat Kemenkes.
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Opsi 1: Menandai Status Kecukupan SKP -->
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100 bg-white hover-shadow transition">
                        <div class="form-check">
                            <input class="form-check-input @error('status_kecukupan_skp') is-invalid @enderror" type="checkbox" name="status_kecukupan_skp" id="statusKecukupanSkp" value="1" {{ old('status_kecukupan_skp', '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark fs-sm" for="statusKecukupanSkp">
                                <i class="fa-solid fa-square-check text-success me-1"></i>
                                Tandai Status Kecukupan SKP Terpenuhi
                            </label>
                        </div>
                        <p class="fs-xs text-muted mt-2 mb-2 ps-4">
                            Saya menyatakan bahwa perolehan Satuan Kredit Profesi (SKP) saya telah mencukupi batas kuota minimal pada pangkalan data <strong>SatuSehat SDMK Kemenkes RI</strong> untuk syarat perpanjangan izin ini.
                        </p>
                        <div class="ps-4">
                            <span class="badge bg-success-subtle text-success border border-success fs-xs">
                                <i class="fa-solid fa-check-double me-1"></i> Mandiri: Terpenuhi via SatuSehat
                            </span>
                        </div>
                        @error('status_kecukupan_skp') <div class="invalid-feedback d-block ps-4 fs-xs">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Opsi 2: Unggah Surat Rekomendasi Kecukupan SKP -->
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100 bg-white hover-shadow transition">
                        <label class="form-label fw-bold text-dark fs-sm mb-1 d-flex align-items-center justify-content-between">
                            <span><i class="fa-solid fa-file-arrow-up text-teal me-1"></i> Surat Rekomendasi Kecukupan SKP (Opsional / Lampiran)</span>
                            <span class="badge bg-light text-secondary border fs-2xs">PDF Maks 2MB</span>
                        </label>
                        <p class="fs-xs text-muted mb-2">
                            Unggah salinan pindaian Surat Rekomendasi Kecukupan SKP resmi dari platform SatuSehat Kemenkes (jika tersedia):
                        </p>
                        <input type="file" name="file_rekomendasi_skp" id="fileRekomendasiSkp" class="form-control form-control-sm @error('file_rekomendasi_skp') is-invalid @enderror" accept=".pdf">
                        <small class="text-muted fs-xs d-block mt-1">Format dokumen: PDF (Maksimal 2 MB)</small>
                        @error('file_rekomendasi_skp') <div class="invalid-feedback">{{ $message }}</div> @enderror

                        <div class="mt-2">
                            <label class="form-label fs-xs text-secondary mb-1">Nomor Surat Rekomendasi SatuSehat (Jika ada):</label>
                            <input type="text" name="nomor_rekomendasi_skp" class="form-control form-control-sm font-monospace" placeholder="Contoh: REKOM-SKP/SATUSEHAT/2026/0491" value="{{ old('nomor_rekomendasi_skp') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BAGIAN 2: Input Metadata SIP -->
    <div class="card shadow-xs mb-4 border">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 13px;">2</div>
                <h6 class="fw-bold text-dark mb-0">Input Metadata Surat Izin Praktik (SIP)</h6>
            </div>
            <span class="badge bg-secondary-subtle text-secondary border fs-xs">
                <i class="fa-solid fa-building-columns me-1"></i> Perizinan DPMPTSP
            </span>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <!-- Nomor SIP DPMPTSP -->
                <div class="col-md-7">
                    <label class="form-label fs-sm fw-semibold">
                        Nomor Izin SIP dari DPMPTSP <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nomor_sip" id="nomorSip" class="form-control font-monospace @error('nomor_sip') is-invalid @enderror" placeholder="Contoh: 503/012-SIP.SpPD/DPMPTSP-BJM/2026" value="{{ old('nomor_sip') }}" required>
                    <small class="text-muted fs-xs">Masukkan nomor surat izin sesuai SK resmi yang diterbitkan oleh DPMPTSP.</small>
                    @error('nomor_sip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Instansi Penerbit -->
                <div class="col-md-5">
                    <label class="form-label fs-sm fw-semibold">Instansi Penerbit Izin</label>
                    <input type="text" name="penerbit_sip" class="form-control bg-light" value="{{ old('penerbit_sip', 'DPMPTSP Kota Banjarmasin') }}" readonly>
                    <small class="text-muted fs-xs">Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu.</small>
                </div>

                <!-- Memastikan Alamat Fasyankes Khusus RSUD Dr. H. Moch. Ansari Saleh -->
                <div class="col-12">
                    <div class="p-3 rounded-3 border bg-light-subtle">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-2">
                            <label class="form-label fs-sm fw-bold text-dark mb-0">
                                <i class="fa-solid fa-hospital text-teal me-1"></i>
                                Alamat Fasilitas Pelayanan Kesehatan (Fasyankes) Tertuju Khusus:
                            </label>
                            <span class="badge bg-success text-white px-2 py-1 fs-xs">
                                <i class="fa-solid fa-circle-check me-1"></i> Tertuju Khusus ke RSUD dr. H. Moch. Ansari Saleh
                            </span>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="text" name="fasyankes" class="form-control form-control-sm fw-bold text-dark bg-white @error('fasyankes') is-invalid @enderror" value="RSUD dr. H. Moch. Ansari Saleh" readonly>
                                @error('fasyankes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-7">
                                <input type="text" name="alamat_fasyankes" class="form-control form-control-sm text-secondary bg-white" value="Jl. Brigjend H. Hasan Basry No. 1, Alalak Utara, Banjarmasin Utara, Kota Banjarmasin, Kalimantan Selatan 70125" readonly>
                            </div>
                        </div>
                        <small class="text-muted fs-xs d-block mt-2">
                            <i class="fa-solid fa-lock me-1 text-teal"></i> Ketentuan Mutlak: Dokumen SIP pada sistem ini tertuju khusus untuk operasional pelayanan medis di lingkungan RSUD dr. H. Moch. Ansari Saleh.
                        </small>
                    </div>
                </div>

                <!-- Tempat Praktik Ke -->
                <div class="col-md-4">
                    <label class="form-label fs-sm fw-semibold">SIP Tempat Praktik Ke <span class="text-danger">*</span></label>
                    <select name="tempat_praktik_ke" class="form-select @error('tempat_praktik_ke') is-invalid @enderror" required>
                        <option value="1" {{ old('tempat_praktik_ke', (string) $dokter->tempat_praktik_ke) == '1' ? 'selected' : '' }}>Tempat Praktik Ke-1 (Fasilitas Utama)</option>
                        <option value="2" {{ old('tempat_praktik_ke', (string) $dokter->tempat_praktik_ke) == '2' ? 'selected' : '' }}>Tempat Praktik Ke-2</option>
                        <option value="3" {{ old('tempat_praktik_ke', (string) $dokter->tempat_praktik_ke) == '3' ? 'selected' : '' }}>Tempat Praktik Ke-3</option>
                    </select>
                    @error('tempat_praktik_ke') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Tanggal Terbit -->
                <div class="col-md-4">
                    <label class="form-label fs-sm fw-semibold">Tanggal Terbit SIP <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_terbit" id="tanggalTerbit" class="form-control @error('tanggal_terbit') is-invalid @enderror" value="{{ old('tanggal_terbit', date('Y-m-d')) }}" required>
                    <small class="text-muted fs-xs">Tanggal pengesahan izin oleh DPMPTSP.</small>
                    @error('tanggal_terbit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Tanggal Jatuh Tempo (Masa 5 Tahun) -->
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fs-sm fw-semibold mb-0">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                        <button type="button" id="btnSet5Tahun" class="btn btn-outline-teal btn-xs py-0 px-2" style="font-size: 11px;">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto 5 Thn
                        </button>
                    </div>
                    <input type="date" name="tanggal_kadaluarsa" id="tanggalKadaluarsa" class="form-control @error('tanggal_kadaluarsa') is-invalid @enderror" value="{{ old('tanggal_kadaluarsa', date('Y-m-d', strtotime('+5 years'))) }}" required>
                    <small class="text-muted fs-xs d-block mt-1">Masa berlaku 5 tahun terhitung sejak tanggal terbit.</small>
                    @error('tanggal_kadaluarsa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Permohonan Penambahan Kewenangan Klinis Baru (Tahap 3 Pemicu Rekredensialing) -->
            <div class="mt-4 p-3 bg-light rounded-3 border">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="permohonan_kewenangan_baru" id="permohonanKewenanganBaru" value="1" {{ old('permohonan_kewenangan_baru') ? 'checked' : '' }} onchange="document.getElementById('boxCatatanKewenanganDokter').classList.toggle('d-none', !this.checked)">
                    <label class="form-check-label fw-bold text-dark fs-sm" for="permohonanKewenanganBaru">
                        <i class="fa-solid fa-plus-circle text-teal me-1"></i> Saya mengajukan permohonan penambahan Rincian Kewenangan Klinis (RKK) baru
                    </label>
                </div>
                <p class="fs-xs text-muted mb-0 ps-4 mt-1">
                    Centang opsi ini apabila Anda telah menyelesaikan pelatihan / fellowship tambahan dan mengajukan tindakan medis baru untuk disidangkan oleh Subkomite Kredensial bersama Mitra Bestari.
                </p>
                <div id="boxCatatanKewenanganDokter" class="ps-4 mt-2 {{ old('permohonan_kewenangan_baru') ? '' : 'd-none' }}">
                    <textarea name="catatan_kewenangan_baru" rows="2" class="form-control fs-xs" placeholder="Tuliskan daftar prosedur / kewenangan klinis baru yang diajukan beserta sertifikat pendukung...">{{ old('catatan_kewenangan_baru') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- BAGIAN 3: Unggah Berkas Pindaian SIP Format PDF -->
    <div class="card shadow-xs mb-4 border">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 13px;">3</div>
                <h6 class="fw-bold text-dark mb-0">Unggah Berkas Pindaian (Scan) SIP Asli</h6>
            </div>
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-xs">
                <i class="fa-solid fa-file-pdf me-1"></i> Wajib Format PDF &bull; Maks 2 MB
            </span>
        </div>
        <div class="card-body p-4">
            <div class="mb-3">
                <label class="form-label fs-sm fw-semibold">
                    Lampirkan Berkas Pindaian Lembar SIP Asli <span class="text-danger">*</span>
                </label>
                <input type="file" name="file_sip" id="fileSip" class="form-control @error('file_sip') is-invalid @enderror" accept=".pdf" required>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted fs-xs">
                        <i class="fa-solid fa-circle-check text-success me-1"></i> Hanya format <strong>PDF</strong> asli yang diperbolehkan (ukuran maksimal <strong>2 MB</strong>).
                    </small>
                    <span id="fileSizeIndicator" class="fs-xs text-muted"></span>
                </div>
                @error('file_sip') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <!-- Notice Status Antrean Sistem -->
            <div class="p-3 bg-light-subtle rounded-3 border border-warning-subtle d-flex align-items-start gap-3">
                <div class="text-warning-emphasis fs-4 mt-1">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div class="fs-sm">
                    <strong class="text-dark d-block mb-1">Status Awal Antrean Sistem:</strong>
                    <p class="text-secondary mb-0 fs-xs">
                        Setelah berkas diajukan, data akan tersimpan langsung di antrean sistem dengan status awal <span class="badge bg-warning-subtle text-warning-emphasis border border-warning"><i class="fa-solid fa-hourglass-half me-1"></i> Menunggu Verifikasi</span> untuk ditelaah dan diverifikasi kesahannya oleh Subkomite Kredensial Komite Medik RSUD dr. H. Moch. Ansari Saleh.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Tindakan -->
    <div class="card shadow-xs border">
        <div class="card-body p-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
            <a href="{{ route('dokter.pengajuan.index') }}" class="btn btn-light border btn-sm order-2 order-sm-1">
                <i class="fa-solid fa-arrow-left me-1"></i> Batal & Kembali
            </a>
            <button type="submit" class="btn btn-teal text-white btn-sm px-4 py-2 order-1 order-sm-2 d-flex align-items-center gap-2">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <span>Simpan ke Antrean Verifikasi SIP</span>
            </button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tanggalTerbitInput = document.getElementById('tanggalTerbit');
    const tanggalKadaluarsaInput = document.getElementById('tanggalKadaluarsa');
    const btnSet5Tahun = document.getElementById('btnSet5Tahun');
    const fileSipInput = document.getElementById('fileSip');
    const fileSizeIndicator = document.getElementById('fileSizeIndicator');
    const fileSkpInput = document.getElementById('fileRekomendasiSkp');
    const statusKecukupanSkp = document.getElementById('statusKecukupanSkp');

    // Fungsi otomatis hitung masa 5 tahun
    function hitung5Tahun() {
        if (!tanggalTerbitInput.value) return;
        const terbitDate = new Date(tanggalTerbitInput.value);
        if (isNaN(terbitDate.getTime())) return;

        const kadaluarsaDate = new Date(terbitDate);
        kadaluarsaDate.setFullYear(kadaluarsaDate.getFullYear() + 5);

        const year = kadaluarsaDate.getFullYear();
        const month = String(kadaluarsaDate.getMonth() + 1).padStart(2, '0');
        const day = String(kadaluarsaDate.getDate()).padStart(2, '0');

        tanggalKadaluarsaInput.value = `${year}-${month}-${day}`;
    }

    if (tanggalTerbitInput && tanggalKadaluarsaInput) {
        tanggalTerbitInput.addEventListener('change', hitung5Tahun);
    }

    if (btnSet5Tahun) {
        btnSet5Tahun.addEventListener('click', function (e) {
            e.preventDefault();
            hitung5Tahun();
        });
    }

    // Validasi ukuran dan ekstensi file SIP di sisi klien
    if (fileSipInput) {
        fileSipInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
                const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');

                if (!isPdf) {
                    alert('Peringatan: Berkas pindaian SIP harus berformat PDF asli.');
                    this.value = '';
                    fileSizeIndicator.textContent = '';
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    alert(`Peringatan: Ukuran berkas ${sizeMb} MB melebihi batas maksimal 2 MB.`);
                    this.value = '';
                    fileSizeIndicator.textContent = '';
                    return;
                }

                fileSizeIndicator.innerHTML = `<span class="badge bg-light text-dark border"><i class="fa-solid fa-file-pdf text-danger me-1"></i> ${sizeMb} MB (Valid)</span>`;
            }
        });
    }

    // Validasi file SKP jika diunggah
    if (fileSkpInput) {
        fileSkpInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
                const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');

                if (!isPdf) {
                    alert('Peringatan: Berkas Surat Rekomendasi SKP harus berformat PDF.');
                    this.value = '';
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    alert(`Peringatan: Ukuran berkas ${sizeMb} MB melebihi batas maksimal 2 MB.`);
                    this.value = '';
                    return;
                }
            }
        });
    }
});
</script>
@endsection
