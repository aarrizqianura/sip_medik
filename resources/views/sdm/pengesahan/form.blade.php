@extends('layouts.app')

@section('title', 'Penerbitan SK Direktur & Penguncian e-Arsip')

@section('content')
<div class="mb-4">
    <a href="{{ route('sdm.pengesahan.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Antrean Pengesahan
    </a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-file-signature text-teal me-2"></i> Penerbitan SK Direktur & Penguncian e-Arsip
            </h4>
            <p class="text-secondary fs-sm mb-0">
                Tahap 4: Pengesahan Surat Keputusan (SK) Direktur mengenai penetapan SPK dan lampiran RKK terbaru untuk dr. {{ $dokter->user->nama }}
            </p>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 fs-sm">
            <i class="fa-solid fa-stamp me-1"></i> Otoritas Direksi & Kepegawaian (SDM)
        </span>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Rujukan Rekomendasi Komite Medik & Berkas 4 Pilar -->
    <div class="col-lg-5">
        <!-- 1. Ringkasan Rekomendasi Komite Medik -->
        <div class="card shadow-xs mb-4 border-teal">
            <div class="card-header bg-teal-subtle text-teal fw-bold d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-file-invoice text-teal me-2"></i>Dasar Hukum Surat Rekomendasi</span>
                <span class="badge bg-teal text-white fs-2xs">Tahap 3 Selesai</span>
            </div>
            <div class="card-body p-3 fs-sm">
                <div class="p-3 bg-light rounded border mb-3">
                    <div class="text-muted fs-xs">Nomor Surat Rekomendasi Komite Medik:</div>
                    <div class="fw-bold text-dark font-monospace fs-6">
                        {{ $kredensial->nomor_surat_rekomendasi ?: '445/KOM-MED/RSAS/' . date('Y') }}
                    </div>
                    <div class="fs-xs text-muted mt-1">
                        Tanggal: <strong>{{ $kredensial->tanggal_rekomendasi ? $kredensial->tanggal_rekomendasi->translatedFormat('d F Y') : date('d F Y') }}</strong>
                    </div>
                    <div class="fs-xs text-muted">
                        Penandatangan: <strong>{{ $kredensial->ketua_komite_medik ?: 'Ketua Komite Medik' }}</strong>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="fw-semibold text-dark mb-1">Hasil Sidang Mitra Bestari:</div>
                    <ul class="list-unstyled mb-0 fs-xs">
                        <li class="mb-1"><i class="fa-solid fa-check text-success me-1"></i> Mitra Bestari: <strong>{{ $kredensial->nama_mitra_bestari ?: 'Tim Mitra Bestari KSM' }}</strong></li>
                        <li class="mb-1"><i class="fa-solid fa-check text-success me-1"></i> Tanggal Sidang: <strong>{{ $kredensial->tanggal_sidang ? $kredensial->tanggal_sidang->translatedFormat('d F Y') : '-' }}</strong></li>
                        <li><i class="fa-solid fa-check text-success me-1"></i> Evaluasi Logbook & Portofolio: <span class="badge bg-success-subtle text-success">Layak Penuh</span></li>
                    </ul>
                </div>

                <div class="p-3 bg-white rounded border">
                    <div class="fw-semibold text-dark mb-2">Penetapan Rincian Kewenangan Klinis (RKK):</div>
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="p-2 bg-success-subtle rounded border border-success">
                                <div class="fs-4 fw-bold text-success">{{ $kredensial->rkk_mandiri_count }}</div>
                                <div class="fs-2xs fw-semibold text-success text-uppercase">Kewenangan Mandiri</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-info-subtle rounded border border-info">
                                <div class="fs-4 fw-bold text-info">{{ $kredensial->rkk_supervisi_count }}</div>
                                <div class="fs-2xs fw-semibold text-info text-uppercase">Dengan Supervisi</div>
                            </div>
                        </div>
                    </div>

                    @if(is_array($kredensial->daftar_rkk) && count($kredensial->daftar_rkk) > 0)
                        <div class="mt-3 pt-2 border-top">
                            <div class="fw-semibold text-dark fs-xs mb-1">Daftar Tindakan Medis RKK Disetujui:</div>
                            <div class="overflow-auto border rounded bg-light p-1" style="max-height: 140px;">
                                <ul class="list-group list-group-flush fs-2xs bg-transparent">
                                    @foreach($kredensial->daftar_rkk as $rkkItem)
                                        <li class="list-group-item px-2 py-1 d-flex justify-content-between align-items-center bg-transparent border-bottom">
                                            <span class="text-truncate me-2">{{ $rkkItem['nama'] ?? '-' }}</span>
                                            <span class="badge {{ ($rkkItem['status'] ?? '') === 'mandiri' ? 'bg-success text-white' : 'bg-info text-white' }} fs-3xs">
                                                {{ ucfirst($rkkItem['status'] ?? '-') }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-3 text-end">
                    <a href="{{ route('komite.kredensial.surat_rekomendasi', $kredensial->id) }}" target="_blank" class="btn btn-xs btn-outline-primary">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Buka Lembar Rekomendasi Resmi
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Pemeriksaan Integritas 4 Pilar Berkas -->
        <div class="card shadow-xs">
            <div class="card-header bg-white fw-bold text-dark">
                <i class="fa-solid fa-lock text-teal me-2"></i>Status Berkas yang Akan Dikunci ke e-Arsip
            </div>
            <div class="card-body p-3 fs-sm">
                <div class="d-flex align-items-center gap-3 p-2 border-bottom">
                    <i class="fa-solid fa-certificate fs-4 text-teal"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">1. Berkas STR 16 Digit</div>
                        <div class="fs-xs font-monospace text-muted">{{ $dokter->nomor_str ?: '-' }}</div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success fs-xs">
                        <i class="fa-solid fa-circle-check me-1"></i> Valid
                    </span>
                </div>

                <div class="d-flex align-items-center gap-3 p-2 border-bottom">
                    <i class="fa-solid fa-file-medical fs-4 text-primary"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">2. Berkas Surat Izin Praktik (SIP)</div>
                        <div class="fs-xs font-monospace text-muted">{{ $sip?->nomor_sip ?: '-' }}</div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success fs-xs">
                        <i class="fa-solid fa-circle-check me-1"></i> Disetujui
                    </span>
                </div>

                <div class="d-flex align-items-center gap-3 p-2 border-bottom">
                    <i class="fa-solid fa-file-signature fs-4 text-warning"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">3. Instrumen SPK Direktur</div>
                        <div class="fs-xs text-muted">Akan disahkan untuk masa 3 tahun</div>
                    </div>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning fs-xs">
                        Siap Terbit
                    </span>
                </div>

                <div class="d-flex align-items-center gap-3 p-2">
                    <i class="fa-solid fa-stethoscope fs-4 text-info"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">4. Lampiran RKK Terbaru</div>
                        <div class="fs-xs text-muted">{{ $kredensial->rkk_total_count }} Tindakan Klinis Disetujui</div>
                    </div>
                    <span class="badge bg-info-subtle text-info border border-info fs-xs">
                        Siap Terkunci
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Formulir Pengesahan SK Direktur & Penguncian e-Arsip -->
    <div class="col-lg-7">
        <div class="card shadow-xs border-primary">
            <div class="card-header bg-primary text-white py-3">
                <h6 class="modal-title fw-bold mb-0">
                    <i class="fa-solid fa-stamp me-2"></i> Formulir Pengesahan SK Direktur & Aktivasi Hak Praktik
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('sdm.pengesahan.store', $kredensial->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Profil Singkat Dokter -->
                    <div class="p-3 bg-light rounded border mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar bg-teal text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 52px; height: 52px;">
                                {{ strtoupper(substr($dokter->user->nama ?? 'D', 0, 1)) }}
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">{{ $dokter->user->nama }}</h5>
                                <div class="text-secondary fs-sm">
                                    NIP: <strong>{{ $dokter->nip_nrp }}</strong> &bull; 
                                    KSM: <strong>{{ $dokter->ksm->nama_ksm }}</strong> &bull; 
                                    {{ $dokter->spesialisasi }}
                                </div>
                                <div class="fs-xs text-teal fw-semibold mt-1">
                                    <i class="fa-solid fa-hospital me-1"></i> Deklarasi Tempat Praktik: {{ $dokter->tempat_praktik_label }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input 1: Nomor SK Direktur -->
                    <div class="mb-3">
                        <label class="form-label fs-sm fw-bold text-dark">
                            Nomor Surat Keputusan (SK) Direktur <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-hashtag"></i></span>
                            <input type="text" name="nomor_sk_direktur" class="form-control font-monospace fw-semibold" value="{{ old('nomor_sk_direktur', $nomorSkOtomatis) }}" required placeholder="Contoh: 800/123-DIR/RSAS/SK-MED/2026">
                        </div>
                        <small class="text-muted fs-xs">Nomor resmi penetapan SPK & RKK oleh Direktur RSUD dr. H. Moch. Ansari Saleh.</small>
                    </div>

                    <!-- Input 2 & 3: Tanggal Penetapan & Masa Berlaku 3 Tahun -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-sm fw-bold text-dark">
                                Tanggal Penetapan SK Direktur <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_sk_direktur" id="tglSkDirektur" class="form-control" value="{{ old('tanggal_sk_direktur', date('Y-m-d')) }}" required onchange="hitungMasaTigaTahun(this.value)">
                            <small class="text-muted fs-2xs">Tanggal terbit SK penetapan SPK.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-sm fw-bold text-dark">
                                Tanggal Berakhir SPK (Siklus 3 Tahun) <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_berakhir_spk" id="tglBerakhirSpk" class="form-control fw-bold text-primary bg-light" value="{{ old('tanggal_berakhir_spk', date('Y-m-d', strtotime('+3 years'))) }}" required>
                            <small class="text-success fs-2xs fw-semibold"><i class="fa-solid fa-lock me-1"></i>Sesuai siklus evaluasi berkala 3 tahun.</small>
                        </div>
                    </div>

                    <!-- Input 4: Upload Berkas SK Direktur & Lampiran RKK -->
                    <div class="mb-3">
                        <label class="form-label fs-sm fw-bold text-dark">
                            Unggah Salinan Digital SK Direktur & Lampiran RKK Terbaru (PDF)
                        </label>
                        <div class="input-group">
                            <input type="file" name="file_sk_direktur" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-file-pdf"></i></span>
                        </div>
                        <small class="text-muted fs-xs">Berkas pindaian resmi SK Direktur lengkap dengan tanda tangan, cap dinas, dan lampiran RKK terbaru (Maks. 5 MB).</small>
                    </div>

                    <!-- Input 5: Catatan Pengesahan Direksi & SDM -->
                    <div class="mb-4">
                        <label class="form-label fs-sm fw-bold text-dark">
                            Catatan Pengesahan Direksi & Bagian Kepegawaian (SDM)
                        </label>
                        <textarea name="catatan_pengesahan_direksi" rows="3" class="form-control fs-sm" placeholder="Contoh: Berdasarkan rekomendasi Komite Medik No. {{ $kredensial->nomor_surat_rekomendasi }}, Direktur RSUD menetapkan SPK dan lampiran RKK terbaru masa berlaku 3 tahun.">{{ old('catatan_pengesahan_direksi', 'Berdasarkan surat rekomendasi Komite Medik No. ' . ($kredensial->nomor_surat_rekomendasi ?: '-') . ', Direktur RSUD dr. H. Moch. Ansari Saleh menetapkan Surat Penugasan Klinis (SPK) dan Rincian Kewenangan Klinis (RKK) terbaru untuk masa berlaku 3 tahun.') }}</textarea>
                    </div>

                    <!-- Checklist Pengarsipan Terpadu & Aktivasi Status Mutlak -->
                    <div class="p-3 bg-light rounded border mb-4">
                        <div class="fw-bold text-dark fs-sm mb-2 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-success"></i>
                            Pemberlakuan Otomatis Sistem (Aktivasi Hak Praktik Penuh):
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" checked disabled id="chkAktifLegal">
                            <label class="form-check-label fs-xs fw-semibold text-success" for="chkAktifLegal">
                                <i class="fa-solid fa-circle-check me-1"></i> Perbarui status profil dokter menjadi <strong>AKTIF_LEGAL</strong>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" checked disabled id="chkKunciArsip">
                            <label class="form-check-label fs-xs fw-semibold text-teal" for="chkKunciArsip">
                                <i class="fa-solid fa-lock me-1"></i> Kunci berkas STR, SIP, SPK, dan RKK ke repositori digital kepegawaian (e-Arsip)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" checked disabled id="chkMasaSpk">
                            <label class="form-check-label fs-xs fw-semibold text-primary" for="chkMasaSpk">
                                <i class="fa-solid fa-calendar-check me-1"></i> Setel masa berlaku SPK aktif selama 3 tahun penuh
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" checked disabled id="chkMesinPelacak">
                            <label class="form-check-label fs-xs fw-semibold text-dark" for="chkMesinPelacak">
                                <i class="fa-solid fa-radar me-1"></i> Daftarkan tanggal jatuh tempo SIP resmi ke mesin pelacakan & reminder otomatis
                            </label>
                        </div>
                    </div>

                    <!-- Tombol Aksi Submit -->
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <a href="{{ route('sdm.pengesahan.index') }}" class="btn btn-light btn-sm border">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm">
                            <i class="fa-solid fa-stamp me-1"></i> Sahkan SK Direktur & Kunci e-Arsip (AKTIF_LEGAL)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function hitungMasaTigaTahun(tglMulai) {
        if (!tglMulai) return;
        const date = new Date(tglMulai);
        date.setFullYear(date.getFullYear() + 3);
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');
        document.getElementById('tglBerakhirSpk').value = `${yyyy}-${mm}-${dd}`;
    }
</script>
@endsection
