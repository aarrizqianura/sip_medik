@extends('layouts.app')

@section('title', 'Tahap 2: Input Prasyarat SIP & Status Kecukupan SKP (Dokter)')

@section('content')
<div class="mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
                </a>
                <h4 class="fw-bold text-dark mb-0">Pengajuan Perpanjangan SIP</h4>
            </div>
            <p class="text-secondary fs-sm mb-0">
                Pola kartu bertahap (3-Step Wizard) verifikasi berkas perizinan terpadu RSUD dr. H. Moch. Ansari Saleh
            </p>
        </div>
        <div class="text-md-end">
            <span class="badge bg-light text-dark border px-3 py-2 fs-xs">
                <i class="fa-solid fa-user-doctor text-teal me-1"></i> {{ $dokter->user->nama ?? auth()->user()->nama }} &bull; KSM {{ $dokter->ksm->nama_ksm ?? '-' }}
            </span>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-xs mb-4" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation fs-5 mt-1 text-danger"></i>
            <div>
                <strong class="d-block mb-1">Terdapat kesalahan pada formulir pengajuan:</strong>
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

<!-- Wizard Progress Indicator -->
<div class="card shadow-xs border-0 mb-4">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between position-relative">
            <!-- Progress Line -->
            <div class="position-absolute top-50 start-0 translate-middle-y w-100 bg-light-subtle border" style="height: 4px; z-index: 1;"></div>
            <div class="position-absolute top-50 start-0 translate-middle-y bg-teal transition-all" id="wizardProgressBar" style="height: 4px; width: 0%; z-index: 2; transition: width 0.3s ease;"></div>

            <!-- Step 1 Indicator -->
            <div class="text-center position-relative" style="z-index: 3;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto fw-bold fs-sm shadow-xs transition-all step-bubble bg-teal text-white" id="bubbleStep1" style="width: 38px; height: 38px;">
                    1
                </div>
                <div class="fw-bold fs-xs mt-2 text-dark" id="labelStep1">Prasyarat SKP SatuSehat</div>
            </div>

            <!-- Step 2 Indicator -->
            <div class="text-center position-relative" style="z-index: 3;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto fw-bold fs-sm shadow-xs transition-all step-bubble bg-light text-muted border" id="bubbleStep2" style="width: 38px; height: 38px;">
                    2
                </div>
                <div class="fw-semibold fs-xs mt-2 text-muted" id="labelStep2">Metadata Izin SIP</div>
            </div>

            <!-- Step 3 Indicator -->
            <div class="text-center position-relative" style="z-index: 3;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto fw-bold fs-sm shadow-xs transition-all step-bubble bg-light text-muted border" id="bubbleStep3" style="width: 38px; height: 38px;">
                    3
                </div>
                <div class="fw-semibold fs-xs mt-2 text-muted" id="labelStep3">Unggah & Kirim Berkas</div>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('dokter.pengajuan.store') }}" method="POST" enctype="multipart/form-data" id="formPengajuanWizard">
    @csrf

    @if(in_array(auth()->user()->role, ['super_admin', 'admin_sdm']) && isset($dokterList))
        <div class="card shadow-xs mb-3 border">
            <div class="card-body p-3">
                <label class="form-label fs-sm fw-semibold">Pilih Dokter Pengaju (Mode Admin) <span class="text-danger">*</span></label>
                <select name="id_dokter" class="form-select @error('id_dokter') is-invalid @enderror">
                    <option value="">-- Pilih Dokter --</option>
                    @foreach($dokterList as $dok)
                        <option value="{{ $dok->id }}">{{ $dok->user->nama ?? '-' }} (NIP: {{ $dok->nip_nrp }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    <!-- Hidden Default Inputs for Fasyankes -->
    <input type="hidden" name="fasyankes" value="RSUD dr. H. Moch. Ansari Saleh">
    <input type="hidden" name="alamat_fasyankes" value="Jl. Brigjend H. Hasan Basry No. 1, Banjarmasin">
    <input type="hidden" name="penerbit_sip" value="DPMPTSP Kota Banjarmasin">

    <!-- ==================== TAHAP 1: PRASYARAT SKP SATUSEHAT ==================== -->
    <div class="wizard-pane active" id="paneStep1">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark fs-6">
                    <i class="fa-solid fa-certificate text-teal me-2"></i>Tahap 1: Cek Prasyarat SKP (Satuan Kredit Profesi)
                </div>
                <span class="badge bg-teal-subtle text-teal">Tahap 2: Input Prasyarat SIP & Status Kecukupan SKP</span>
            </div>
            <div class="card-body p-4">
                <p class="text-secondary fs-sm mb-4">
                    Sesuai ketentuan UU Kesehatan No. 17/2023 dan regulasi Kemenkes RI, perpanjangan SIP mensyaratkan pemenuhan Satuan Kredit Profesi (SKP) melalui Platform SatuSehat Kemenkes atau rekomendasi resmi organisasi profesi.
                </p>

                <div class="row g-3 mb-4">
                    <!-- Option A: SKP Terpenuhi -->
                    <div class="col-md-6">
                        <label class="card h-100 p-3 border-2 cursor-pointer transition-all" id="cardSkpTerpenuhi" style="cursor: pointer;">
                            <div class="d-flex align-items-start gap-3">
                                <input class="form-check-input mt-1" type="radio" name="skp_mode" id="radioSkpTerpenuhi" value="terpenuhi" checked onchange="handleSkpModeChange()">
                                <div>
                                    <div class="fw-bold text-dark fs-sm mb-1">
                                        <i class="fa-solid fa-circle-check text-success me-1"></i> Tandai Status Kecukupan SKP Terpenuhi
                                    </div>
                                    <p class="fs-xs text-muted mb-0">
                                        Total SKP telah mencapai batas kecukupan minimal (≥ 250 SKP) dan terdata sinkron di Platform SatuSehat Kemenkes.
                                    </p>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Option B: Memiliki Surat Rekomendasi -->
                    <div class="col-md-6">
                        <label class="card h-100 p-3 border-2 cursor-pointer transition-all" id="cardSkpRekomendasi" style="cursor: pointer;">
                            <div class="d-flex align-items-start gap-3">
                                <input class="form-check-input mt-1" type="radio" name="skp_mode" id="radioSkpRekomendasi" value="rekomendasi" onchange="handleSkpModeChange()">
                                <div>
                                    <div class="fw-bold text-dark fs-sm mb-1">
                                        <i class="fa-solid fa-file-shield text-teal me-1"></i> Surat Rekomendasi Kecukupan SKP
                                    </div>
                                    <p class="fs-xs text-muted mb-0">
                                        Memiliki berkas Surat Rekomendasi Kecukupan SKP yang diterbitkan oleh Kolegium / IDI / Kemenkes RI.
                                    </p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Hidden Real Input for Controller -->
                <input type="hidden" name="status_kecukupan_skp" id="hiddenStatusSkp" value="1">

                <!-- Conditional Upload Area for Surat Rekomendasi -->
                <div id="boxUploadRekomendasi" class="p-3 bg-light rounded-3 border d-none mb-3">
                    <h6 class="fw-bold text-dark fs-sm mb-2">
                        <i class="fa-solid fa-paperclip text-teal me-1"></i> Berkas Bukti Surat Rekomendasi SKP
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fs-xs fw-semibold">Nomor Surat Rekomendasi</label>
                            <input type="text" name="nomor_rekomendasi_skp" id="nomor_rekomendasi_skp" class="form-control form-control-sm font-monospace" placeholder="Contoh: 124/IDI-BJM/REK-SKP/2026">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-xs fw-semibold">Unggah File PDF Rekomendasi (Maks 2 MB)</label>
                            <input type="file" name="file_rekomendasi_skp" id="file_rekomendasi_skp" class="form-control form-control-sm" accept=".pdf">
                            <small class="text-muted fs-3xs">Format dokumen wajib PDF, ukuran maksimal 2 MB.</small>
                        </div>
                    </div>
                </div>

                <!-- Step 1 Navigation -->
                <div class="d-flex justify-content-end pt-3 border-top">
                    <button type="button" class="btn btn-teal text-white btn-sm px-4 fw-bold shadow-sm" onclick="goToStep(2)">
                        Lanjut ke Tahap 2: Metadata SIP <i class="fa-solid fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== TAHAP 2: METADATA SIP ==================== -->
    <div class="wizard-pane d-none" id="paneStep2">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark fs-6">
                    <i class="fa-solid fa-file-invoice text-teal me-2"></i>Tahap 2: Metadata Izin Praktik SIP & Wewenang Klinis
                </div>
                <span class="badge bg-teal-subtle text-teal">Langkah 2 dari 3</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <!-- Kolom 1: Nomor SIP -->
                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">
                            Nomor Izin SIP dari DPMPTSP <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary font-monospace"><i class="fa-solid fa-hashtag"></i></span>
                            <input type="text" name="nomor_sip" id="inputNomorSip" class="form-control font-monospace fw-semibold @error('nomor_sip') is-invalid @enderror" placeholder="Contoh: 503/446-SIP.D/DPMPTSP/2026" value="{{ old('nomor_sip') }}" required>
                        </div>
                        <small class="text-muted fs-3xs">Masukkan nomor resmi dokumen perizinan dari DPMPTSP.</small>
                    </div>

                    <!-- Tempat Praktik Ke -->
                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">Tempat Praktik Keberapa di RSUD? <span class="text-danger">*</span></label>
                        <select name="tempat_praktik_ke" class="form-select @error('tempat_praktik_ke') is-invalid @enderror" required>
                            <option value="1" {{ old('tempat_praktik_ke', $dokter->tempat_praktik_ke ?? '1') == 1 ? 'selected' : '' }}>SIP Ke-1 (Fasilitas Pelayanan Utama)</option>
                            <option value="2" {{ old('tempat_praktik_ke', $dokter->tempat_praktik_ke) == 2 ? 'selected' : '' }}>SIP Ke-2</option>
                            <option value="3" {{ old('tempat_praktik_ke', $dokter->tempat_praktik_ke) == 3 ? 'selected' : '' }}>SIP Ke-3</option>
                        </select>
                        <div class="form-text fs-3xs text-muted">Tertuju Khusus ke RSUD dr. H. Moch. Ansari Saleh</div>
                    </div>

                    <!-- Kolom 2: Tanggal Terbit & Auto-Calc Tanggal Kedaluwarsa -->
                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">Tanggal Terbit SIP <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_terbit" id="inputTanggalTerbit" class="form-control @error('tanggal_terbit') is-invalid @enderror" value="{{ old('tanggal_terbit', date('Y-m-d')) }}" required onchange="hitungKadaluarsaOtomatis()">
                        <small class="text-muted fs-3xs">Tanggal pengesahan oleh dinas perizinan.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">
                            Tanggal Kedaluwarsa (Masa Berlaku 5 Tahun) <span class="badge bg-teal-subtle text-teal ms-1 fs-3xs">Otomatis Terhitung</span>
                        </label>
                        <input type="date" name="tanggal_kadaluarsa" id="inputTanggalKadaluarsa" class="form-control fw-bold text-teal @error('tanggal_kadaluarsa') is-invalid @enderror" value="{{ old('tanggal_kadaluarsa') }}" required>
                        <small class="text-teal fs-3xs" id="textKeterangan5Tahun">Dihitung otomatis 5 tahun dari tanggal terbit SIP.</small>
                    </div>
                </div>

                <!-- Toggle Permohonan Penambahan Wewenang Klinis RKK Baru -->
                <div class="p-3 rounded-3 border bg-light mb-3">
                    <div class="form-check form-switch d-flex align-items-center justify-content-between p-0 mb-0">
                        <div>
                            <label class="form-check-label fw-bold text-dark fs-sm d-block mb-1" for="toggleWewenangBaru">
                                <i class="fa-solid fa-code-pull-request text-primary me-1"></i> Permohonan Penambahan Kewenangan Klinis RKK Baru
                            </label>
                            <small class="text-muted fs-xs">
                                Aktifkan bila Anda mengajukan penambahan prosedur / tindakan medis baru (misal: setelah menyelesaikan fellowship atau pelatihan subspesialis).
                            </small>
                        </div>
                        <input class="form-check-input ms-3" type="checkbox" role="switch" name="permohonan_kewenangan_baru" id="toggleWewenangBaru" value="1" style="width: 2.5rem; height: 1.3rem;" onchange="handleToggleWewenang(this)">
                    </div>

                    <!-- Catatan Wewenang Baru (Collapsible) -->
                    <div id="boxCatatanWewenang" class="mt-3 pt-3 border-top d-none">
                        <label class="form-label fs-xs fw-semibold text-dark">Rincian Tindakan / Kewenangan Klinis Baru yang Diajukan:</label>
                        <textarea name="catatan_kewenangan_baru" id="catatan_kewenangan_baru" rows="3" class="form-control fs-sm" placeholder="Contoh: Pengajuan kewenangan klinis Laparoskopi Lanjutan dan Endoskopi Digestif berdasarkan sertifikat pelatihan fellowship..."></textarea>
                        <small class="text-muted fs-3xs">Rincian ini akan otomatis dimasukkan ke agenda sidang evaluasi Mitra Bestari Subkomite Kredensial.</small>
                    </div>
                </div>

                <!-- Step 2 Navigation -->
                <div class="d-flex justify-content-between pt-3 border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="goToStep(1)">
                        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Tahap 1
                    </button>
                    <button type="button" class="btn btn-teal text-white btn-sm px-4 fw-bold shadow-sm" onclick="goToStep(3)">
                        Lanjut ke Tahap 3: Unggah Dokumen <i class="fa-solid fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== TAHAP 3: UNGGAH & KIRIM ==================== -->
    <div class="wizard-pane d-none" id="paneStep3">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark fs-6">
                    <i class="fa-solid fa-cloud-arrow-up text-teal me-2"></i>Tahap 3: Unggah Pindaian PDF & Kirim Pengajuan
                </div>
                <span class="badge bg-teal-subtle text-teal">Langkah 3 dari 3</span>
            </div>
            <div class="card-body p-4">
                <p class="text-secondary fs-sm mb-3">
                    Unggah lembar pindaian (scan) Surat Izin Praktik (SIP) asli yang diterbitkan oleh DPMPTSP. Pastikan seluruh lembar, tanda tangan, stempel basah, atau QR code legalitas terbaca jelas.
                </p>

                <!-- Drag-and-Drop Upload Box -->
                <div class="border-2 border-dashed rounded-4 p-5 text-center transition-all bg-light mb-4" id="dropZoneContainer" style="border-color: #cbd5e1; cursor: pointer;">
                    <input type="file" name="file_sip" id="file_sip" class="d-none" accept=".pdf" required onchange="handleFileSelected(this)">
                    <div id="dropZoneContent">
                        <div class="rounded-circle bg-teal-subtle text-teal d-inline-flex align-items-center justify-content-center mb-3" style="width: 68px; height: 68px;">
                            <i class="fa-solid fa-file-pdf fs-1"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Tarik & Lepas File Pindaian PDF SIP di Sini</h6>
                        <p class="text-secondary fs-xs mb-3">atau klik untuk memilih file dari komputer / ponsel Anda</p>
                        <span class="badge bg-white text-secondary border px-3 py-1 fs-xs">
                            Wajib Format PDF &bull; Maks 2 MB
                        </span>
                    </div>

                    <!-- File Preview Card if Selected -->
                    <div id="dropZonePreview" class="d-none">
                        <div class="card p-3 border border-success bg-white d-inline-block text-start shadow-xs" style="max-width: 420px;">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fa-solid fa-file-pdf fs-2 text-danger"></i>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-dark fs-sm text-truncate" id="fileNamePreview">-</div>
                                    <small class="text-success fw-semibold fs-3xs" id="fileSizePreview"><i class="fa-solid fa-check me-1"></i> Siap Dikirim</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="resetSelectedFile(event)" title="Ganti File">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Konfirmasi Akhir Checklist -->
                <div class="alert alert-light border p-3 fs-xs text-secondary mb-4">
                    <div class="fw-bold text-dark mb-1"><i class="fa-solid fa-shield-check text-teal me-1"></i> Pemeriksaan Akhir:</div>
                    <div>&bull; Nomor SIP dan tanggal masa berlaku sudah terverifikasi cocok dengan lembar asli.</div>
                    <div>&bull; Berkas akan langsung masuk antrean telaah Subkomite Kredensial Komite Medik dengan status <strong>Menunggu Verifikasi</strong>.</div>
                </div>

                <!-- Step 3 Navigation -->
                <div class="d-flex justify-content-between pt-3 border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="goToStep(2)">
                        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Tahap 2
                    </button>
                    <button type="submit" class="btn btn-teal text-white btn-sm px-4 py-2 fw-bold shadow-sm" id="btnSubmitFinal">
                        <i class="fa-solid fa-paper-plane me-2"></i> Kirim ke Komite Medik
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    let currentStep = 1;

    function goToStep(step) {
        // Simple client validation before advancing
        if (step === 2) {
            const skpMode = document.querySelector('input[name="skp_mode"]:checked').value;
            if (skpMode === 'rekomendasi') {
                const fileSkp = document.getElementById('file_rekomendasi_skp');
                // if file needed
            }
        } else if (step === 3) {
            const nomorSip = document.getElementById('inputNomorSip').value.trim();
            const tglTerbit = document.getElementById('inputTanggalTerbit').value;
            const tglKadaluarsa = document.getElementById('inputTanggalKadaluarsa').value;

            if (!nomorSip) {
                alert('Silakan isi Nomor SIP DPMPTSP terlebih dahulu.');
                document.getElementById('inputNomorSip').focus();
                return;
            }
            if (!tglTerbit || !tglKadaluarsa) {
                alert('Silakan tentukan Tanggal Terbit dan Tanggal Kedaluwarsa SIP.');
                return;
            }
        }

        currentStep = step;

        // Hide all panes
        document.getElementById('paneStep1').classList.add('d-none');
        document.getElementById('paneStep2').classList.add('d-none');
        document.getElementById('paneStep3').classList.add('d-none');

        // Show active pane
        document.getElementById('paneStep' + step).classList.remove('d-none');

        // Update progress bar
        const progressPercent = step === 1 ? '0%' : (step === 2 ? '50%' : '100%');
        document.getElementById('wizardProgressBar').style.width = progressPercent;

        // Update bubbles
        for (let i = 1; i <= 3; i++) {
            const bubble = document.getElementById('bubbleStep' + i);
            const label = document.getElementById('labelStep' + i);
            if (i < step) {
                bubble.className = 'rounded-circle d-flex align-items-center justify-content-center mx-auto fw-bold fs-sm shadow-xs transition-all step-bubble bg-teal text-white';
                bubble.innerHTML = '<i class="fa-solid fa-check"></i>';
                label.className = 'fw-semibold fs-xs mt-2 text-teal';
            } else if (i === step) {
                bubble.className = 'rounded-circle d-flex align-items-center justify-content-center mx-auto fw-bold fs-sm shadow-xs transition-all step-bubble bg-teal text-white ring-4';
                bubble.textContent = i;
                label.className = 'fw-bold fs-xs mt-2 text-dark';
            } else {
                bubble.className = 'rounded-circle d-flex align-items-center justify-content-center mx-auto fw-bold fs-sm shadow-xs transition-all step-bubble bg-light text-muted border';
                bubble.textContent = i;
                label.className = 'fw-semibold fs-xs mt-2 text-muted';
            }
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function handleSkpModeChange() {
        const isRekomendasi = document.getElementById('radioSkpRekomendasi').checked;
        const box = document.getElementById('boxUploadRekomendasi');
        const hiddenStatus = document.getElementById('hiddenStatusSkp');
        const cardTerpenuhi = document.getElementById('cardSkpTerpenuhi');
        const cardRekomendasi = document.getElementById('cardSkpRekomendasi');

        if (isRekomendasi) {
            box.classList.remove('d-none');
            hiddenStatus.value = '0';
            cardRekomendasi.classList.add('border-teal', 'bg-light-teal');
            cardTerpenuhi.classList.remove('border-teal', 'bg-light-teal');
        } else {
            box.classList.add('d-none');
            hiddenStatus.value = '1';
            cardTerpenuhi.classList.add('border-teal', 'bg-light-teal');
            cardRekomendasi.classList.remove('border-teal', 'bg-light-teal');
        }
    }

    function hitungKadaluarsaOtomatis() {
        const tglTerbitVal = document.getElementById('inputTanggalTerbit').value;
        if (!tglTerbitVal) return;

        const parts = tglTerbitVal.split('-');
        if (parts.length === 3) {
            const tahun = parseInt(parts[0], 10) + 5;
            const bulan = parts[1];
            const hari = parts[2];
            const kadaluarsaVal = `${tahun}-${bulan}-${hari}`;
            document.getElementById('inputTanggalKadaluarsa').value = kadaluarsaVal;
            document.getElementById('textKeterangan5Tahun').textContent = `Otomatis berlaku 5 tahun sampai: ${hari}/${bulan}/${tahun}`;
        }
    }

    function handleToggleWewenang(toggle) {
        const box = document.getElementById('boxCatatanWewenang');
        if (toggle.checked) {
            box.classList.remove('d-none');
            document.getElementById('catatan_kewenangan_baru').focus();
        } else {
            box.classList.add('d-none');
        }
    }

    // Drag and drop zone handling
    const dropZone = document.getElementById('dropZoneContainer');
    const fileInput = document.getElementById('file_sip');

    dropZone.addEventListener('click', () => {
        fileInput.click();
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('border-teal', 'bg-light-teal');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('border-teal', 'bg-light-teal');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelected(fileInput);
        }
    });

    function handleFileSelected(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (!file.name.toLowerCase().endsWith('.pdf')) {
                alert('Peringatan: File harus berformat PDF!');
                input.value = '';
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                alert('Peringatan: Ukuran file melebihi 2 MB!');
                input.value = '';
                return;
            }

            document.getElementById('dropZoneContent').classList.add('d-none');
            document.getElementById('dropZonePreview').classList.remove('d-none');
            document.getElementById('fileNamePreview').textContent = file.name;
            document.getElementById('fileSizePreview').innerHTML = `<i class="fa-solid fa-check me-1"></i> Ukuran: ${(file.size / 1024).toFixed(1)} KB (PDF Absah)`;
        }
    }

    function resetSelectedFile(e) {
        e.stopPropagation();
        fileInput.value = '';
        document.getElementById('dropZonePreview').classList.add('d-none');
        document.getElementById('dropZoneContent').classList.remove('d-none');
    }

    // Initialize auto-calc on load
    document.addEventListener('DOMContentLoaded', function() {
        handleSkpModeChange();
        hitungKadaluarsaOtomatis();
    });
</script>
@endpush
@endsection
