@extends('layouts.app')

@section('title', 'Tahap 6: Pengajuan Masa Tenggang Administratif (Grace Period)')

@section('content')
<div class="mb-4">
    <a href="{{ route('dokter.pengajuan.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Riwayat SIP
    </a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-teal-subtle text-teal border border-teal-subtle px-2 py-1 fs-xs fw-bold">TAHAP 6</span>
                <h4 class="fw-bold text-dark mb-0">Pengajuan Bukti Proses & Masa Tenggang Administratif</h4>
            </div>
            <p class="text-secondary fs-sm mb-0">
                Alur Dispensasi Terbatas (Grace Period / On-Process) untuk melindungi kelangsungan pelayanan medis rumah sakit saat perizinan definitif masih diproses di DPMPTSP/Dinas Kesehatan.
            </p>
        </div>
        <div class="text-md-end">
            <span class="badge bg-light text-dark border px-3 py-2 fs-xs">
                <i class="fa-solid fa-user-doctor text-teal me-1"></i> {{ $targetDokter->user->nama ?? auth()->user()->nama }}
            </span>
        </div>
    </div>
</div>

<!-- Informasi Proteksi Pelayanan & Regulasi Pokja KPS -->
<div class="card border-0 bg-dark-teal text-white shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-start gap-3">
            <div class="avatar bg-white text-teal rounded-circle d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 48px; height: 48px;">
                <i class="fa-solid fa-shield-halved fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold text-white mb-1">Proteksi Pelayanan Medis & Kelangsungan Tata Kelola Klinis</h5>
                <p class="fs-sm text-teal-100 mb-0">
                    Berdasarkan tata kelola kredensial klinis RSUD dr. H. Moch. Ansari Saleh, jika perizinan di DPMPTSP/Dinkes belum rampung saat mendekati tanggal jatuh tempo, dokter dapat mengunggah <strong>Resi Tanda Terima Pengurusan</strong> atau <strong>Surat Keterangan dalam Proses</strong>. Setelah diverifikasi oleh Admin SDM, status akan diaktifkan menjadi <strong>Dalam Proses (Grace Period 14–30 Hari)</strong> dan wewenang klinis (SPK/RKK) di sistem <strong>tidak akan ditangguhkan mendadak</strong>.
                </p>
            </div>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-xs mb-4" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation fs-5 mt-1 text-danger"></i>
            <div>
                <strong class="d-block mb-1">Terdapat kesalahan pada isian formulir:</strong>
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

<div class="row g-4">
    <!-- Kolom Kiri: Formulir Pengajuan Bukti Proses -->
    <div class="col-lg-8">
        <div class="card shadow-xs">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="fa-solid fa-file-invoice text-teal me-2"></i>Formulir Bukti Tanda Terima Pengurusan (Resi DPMPTSP/Dinkes)
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('dokter.dispensasi.store', $sip->id) }}" method="POST" enctype="multipart/form-data" id="formDispensasi">
                    @csrf

                    <!-- Instansi Penerbit & Nomor Resi -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-sm fw-semibold text-dark">
                                Instansi Perizinan Pemroses <span class="text-danger">*</span>
                            </label>
                            <select name="instansi_penerbit_resi" class="form-select @error('instansi_penerbit_resi') is-invalid @enderror" required>
                                <option value="DPMPTSP Kota Banjarmasin" {{ old('instansi_penerbit_resi', $sip->instansi_penerbit_resi) == 'DPMPTSP Kota Banjarmasin' ? 'selected' : '' }}>DPMPTSP Kota Banjarmasin</option>
                                <option value="Dinas Kesehatan Kota Banjarmasin" {{ old('instansi_penerbit_resi', $sip->instansi_penerbit_resi) == 'Dinas Kesehatan Kota Banjarmasin' ? 'selected' : '' }}>Dinas Kesehatan Kota Banjarmasin</option>
                                <option value="DPMPTSP Provinsi Kalimantan Selatan" {{ old('instansi_penerbit_resi', $sip->instansi_penerbit_resi) == 'DPMPTSP Provinsi Kalimantan Selatan' ? 'selected' : '' }}>DPMPTSP Provinsi Kalimantan Selatan</option>
                                <option value="Dinas Kesehatan Provinsi Kalimantan Selatan" {{ old('instansi_penerbit_resi', $sip->instansi_penerbit_resi) == 'Dinas Kesehatan Provinsi Kalimantan Selatan' ? 'selected' : '' }}>Dinas Kesehatan Provinsi Kalimantan Selatan</option>
                                <option value="Kementerian Kesehatan RI (MPP)" {{ old('instansi_penerbit_resi', $sip->instansi_penerbit_resi) == 'Kementerian Kesehatan RI (MPP)' ? 'selected' : '' }}>Kementerian Kesehatan RI (MPP)</option>
                            </select>
                            @error('instansi_penerbit_resi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text fs-xs">Pilih instansi tempat pengajuan izin perpanjangan SIP diajukan.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-sm fw-semibold text-dark">
                                Nomor Resi Tanda Terima / Suket <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nomor_resi_proses" class="form-control font-monospace @error('nomor_resi_proses') is-invalid @enderror" placeholder="Contoh: RESI-DPMPTSP/2026/0981" value="{{ old('nomor_resi_proses', $sip->nomor_resi_proses) }}" required>
                            @error('nomor_resi_proses')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text fs-xs">Nomor pendaftaran pada lembar tanda terima permohonan.</div>
                        </div>
                    </div>

                    <!-- Tanggal Pengajuan Resi & Durasi Diminta -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-sm fw-semibold text-dark">
                                Tanggal Tanda Terima / Suket <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_pengajuan_resi" class="form-control @error('tanggal_pengajuan_resi') is-invalid @enderror" value="{{ old('tanggal_pengajuan_resi', $sip->tanggal_pengajuan_resi ? $sip->tanggal_pengajuan_resi->format('Y-m-d') : date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                            @error('tanggal_pengajuan_resi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text fs-xs">Tanggal berkas diterima oleh petugas loket perizinan DPMPTSP/Dinkes.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-sm fw-semibold text-dark">
                                Permintaan Durasi Masa Tenggang <span class="text-danger">*</span>
                            </label>
                            <select name="durasi_dispensasi_diminta" class="form-select @error('durasi_dispensasi_diminta') is-invalid @enderror" required>
                                <option value="30" {{ old('durasi_dispensasi_diminta', $sip->durasi_dispensasi_hari ?: 30) == 30 ? 'selected' : '' }}>30 Hari (Maksimal Standar Regulasi)</option>
                                <option value="21" {{ old('durasi_dispensasi_diminta', $sip->durasi_dispensasi_hari) == 21 ? 'selected' : '' }}>21 Hari (3 Pekan)</option>
                                <option value="14" {{ old('durasi_dispensasi_diminta', $sip->durasi_dispensasi_hari) == 14 ? 'selected' : '' }}>14 Hari (2 Pekan)</option>
                            </select>
                            @error('durasi_dispensasi_diminta')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text fs-xs">Sesuai SOP Kepegawaian, masa tenggang administratif maksimal 14–30 hari kalender.</div>
                        </div>
                    </div>

                    <!-- Unggah File Bukti Proses -->
                    <div class="mb-3">
                        <label class="form-label fs-sm fw-semibold text-dark">
                            Unggah Salinan Digital Resi / Surat Keterangan Proses <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file_bukti_proses" class="form-control @error('file_bukti_proses') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                        @error('file_bukti_proses')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text fs-xs">
                            Format yang didukung: <strong>PDF, JPG, PNG</strong> (Ukuran maksimal: 5MB). Pastikan stempel, nomor resi, dan tanggal tanda terima terbaca jelas.
                        </div>
                        @if($sip->file_bukti_proses)
                            <div class="mt-2 p-2 bg-light rounded border d-flex align-items-center justify-content-between">
                                <span class="fs-xs text-muted">
                                    <i class="fa-solid fa-paperclip me-1 text-teal"></i> Bukti proses sebelumnya:
                                    <a href="{{ asset('storage/' . $sip->file_bukti_proses) }}" target="_blank" class="fw-semibold text-teal">Lihat Berkas Terunggah</a>
                                </span>
                                <span class="badge bg-secondary fs-3xs">Tersimpan</span>
                            </div>
                        @endif
                    </div>

                    <!-- Keterangan Proses / Kendala Lapangan -->
                    <div class="mb-4">
                        <label class="form-label fs-sm fw-semibold text-dark">
                            Catatan Kronologi / Keterangan Proses
                        </label>
                        <textarea name="keterangan_proses" rows="3" class="form-control @error('keterangan_proses') is-invalid @enderror" placeholder="Jelaskan tahapan proses perizinan saat ini atau catatan tambahan untuk Admin SDM...">{{ old('keterangan_proses', $sip->keterangan_proses) }}</textarea>
                        @error('keterangan_proses')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text fs-xs">Contoh: Berkas telah diverifikasi loket DPMPTSP, saat ini menunggu tanda tangan elektronik Kepala Dinas Penanaman Modal.</div>
                    </div>

                    <!-- Pakta Komitmen Dokter -->
                    <div class="card bg-light border p-3 mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="komitmenDokter" required>
                            <label class="form-check-label fs-sm text-dark" for="komitmenDokter">
                                <strong>Pernyataan Integritas Profesi:</strong> Saya menyatakan bahwa berkas perpanjangan SIP tersebut benar-benar dalam proses legal di instansi DPMPTSP/Dinkes. Saya berkomitmen segera mengunggah lembar SIP definitif ke sistem SIM-SIP begitu diterbitkan sebelum batas waktu masa tenggang berakhir.
                            </label>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2">
                        <a href="{{ route('dokter.pengajuan.index') }}" class="btn btn-outline-secondary btn-sm">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-teal text-white d-inline-flex align-items-center gap-2 px-4 shadow-sm">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Kirim Bukti Resi & Ajukan Masa Tenggang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Rincian SIP & Perlindungan Wewenang Klinis -->
    <div class="col-lg-4">
        <!-- Kartu Rincian Dokumen SIP Terkait -->
        <div class="card shadow-xs mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="fa-solid fa-id-card text-teal me-2"></i>Dokumen SIP Dalam Perpanjangan
                </h6>
            </div>
            <div class="card-body p-3 fs-sm">
                <div class="mb-2">
                    <span class="text-muted fs-xs d-block">Nomor SIP:</span>
                    <span class="fw-bold font-monospace text-dark">{{ $sip->nomor_sip }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted fs-xs d-block">Fasilitas Praktik:</span>
                    <span class="fw-semibold text-dark">{{ $sip->fasyankes ?: 'RSUD dr. H. Moch. Ansari Saleh' }}</span>
                    <div><span class="badge bg-teal-subtle text-teal border border-teal-subtle fs-2xs">SIP Ke-{{ $sip->tempat_praktik_ke }}</span></div>
                </div>
                <div class="mb-2">
                    <span class="text-muted fs-xs d-block">Jatuh Tempo Awal:</span>
                    <span class="fw-bold text-danger">{{ $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</span>
                    @php $sisa = $sip->hitungSisaHari(); @endphp
                    <span class="text-muted fs-xs">({{ $sisa < 0 ? abs($sisa) . ' hari lalu' : 'sisa ' . $sisa . ' hari' }})</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted fs-xs d-block">Status Saat Ini:</span>
                    <span class="status-pill status-{{ $sip->status }} fs-xs">
                        {{ $sip->status_label }}
                    </span>
                </div>
                <div class="mb-0">
                    <span class="text-muted fs-xs d-block">Status Dispensasi:</span>
                    {!! $sip->status_dispensasi_badge !!}
                </div>
            </div>
        </div>

        <!-- Kartu Jaminan Proteksi Pelayanan SPK/RKK -->
        <div class="card shadow-xs border-success bg-success-subtle">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-circle-check text-success fs-5"></i>
                    <h6 class="fw-bold text-success mb-0">Jaminan Tata Kelola Rumah Sakit</h6>
                </div>
                <p class="fs-xs text-dark mb-2">
                    Setelah Admin SDM menyetujui resi ini:
                </p>
                <ul class="fs-xs text-dark ps-3 mb-0">
                    <li class="mb-1">Status wewenang klinis (SPK/RKK) <strong>tidak ditangguhkan</strong>.</li>
                    <li class="mb-1">Hak pelayanan medis kepada pasien tetap <strong>sah dan terlindungi</strong> secara administratif.</li>
                    <li class="mb-1">Sistem memberikan rentang dispensasi <strong>14–30 hari</strong> kalender.</li>
                    <li>Notifikasi pengingat otomatis dikirim H-3 sebelum masa dispensasi berakhir.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
