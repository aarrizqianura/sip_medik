@extends('layouts.app')

@section('title', 'Verifikasi Dispensasi Masa Tenggang (SDM)')

@section('content')
<!-- Header Kompak Tunggal -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('sdm.dispensasi.index') }}" class="btn btn-light btn-sm border" title="Kembali ke antrean">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-teal-subtle text-teal border border-teal-subtle px-2 py-0 fs-3xs fw-bold">TAHAP 6</span>
                <h5 class="fw-bold text-dark mb-0">Verifikasi Resi & Grace Period (Single-Screen)</h5>
                {!! $sip->status_dispensasi_badge !!}
            </div>
            <p class="text-secondary fs-xs mb-0">Periksa bukti resi DPMPTSP dan tetapkan durasi dispensasi (14–30 hari) secara sejajar tanpa scroll.</p>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-xs py-2 px-3 mb-3 fs-xs" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-danger"></i>
            <div>
                <strong>Terdapat kesalahan input:</strong>
                @foreach ($errors->all() as $error)
                    <span>{{ $error }} </span>
                @endforeach
            </div>
        </div>
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Cockpit Sejajar (Single-Screen View) -->
<div class="row g-3">
    <!-- Kolom Kiri: Profil Dokter, Metadata Resi, & Pratinjau Dokumen -->
    <div class="col-lg-6">
        <div class="card shadow-xs h-100 d-flex flex-column">
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar bg-teal text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-xs" style="width: 32px; height: 32px;">
                        {{ strtoupper(substr($dokter->user->nama ?? 'D', 0, 1)) }}
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0 fs-sm">{{ $dokter->user->nama ?? '-' }}</h6>
                        <span class="fs-3xs text-muted">NIP: {{ $dokter->nip_nrp ?? '-' }} &bull; {{ $dokter->ksm->nama_ksm ?? '-' }}</span>
                    </div>
                </div>
                <span class="badge bg-light text-secondary border fs-3xs">{{ $dokter->spesialisasi }}</span>
            </div>

            <div class="card-body p-3 d-flex flex-column gap-2 flex-grow-1">
                <!-- Metadata Legalitas Ringkas Grid 4 -->
                <div class="p-2 bg-light rounded border">
                    <div class="row g-2 fs-xs">
                        <div class="col-6">
                            <span class="text-muted fs-3xs d-block">Status Validasi STR:</span>
                            {!! $dokter->status_verifikasi_str_badge !!}
                            <span class="font-monospace fs-3xs ms-1">{{ $dokter->nomor_str ?: '-' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted fs-3xs d-block">Status Praktik:</span>
                            {!! $dokter->status_praktik_badge !!}
                        </div>
                        <div class="col-6">
                            <span class="text-muted fs-3xs d-block">SIP Dimintakan Dispensasi:</span>
                            <span class="font-monospace fw-bold text-dark fs-3xs">{{ $sip->nomor_sip }}</span>
                            <span class="badge bg-teal-subtle text-teal border border-teal-subtle fs-3xs">Ke-{{ $sip->tempat_praktik_ke }}</span>
                            <div class="fs-3xs text-danger fw-semibold">Jatuh Tempo: {{ $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</div>
                        </div>
                        <div class="col-6">
                            <span class="text-muted fs-3xs d-block">Instrumen SPK & RKK:</span>
                            @if($spk)
                                <span class="badge bg-success-subtle text-success border border-success fs-3xs">
                                    <i class="fa-solid fa-file-signature me-1"></i> SPK Berlaku
                                </span>
                                <div class="fs-3xs text-muted">s.d {{ $spk->tanggal_berakhir_spk ? $spk->tanggal_berakhir_spk->format('d/m/Y') : '-' }}</div>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary fs-3xs">Belum Ada SPK</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Informasi Bukti Resi Tanda Terima -->
                <div class="p-2 bg-teal-subtle border border-teal rounded">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fs-3xs fw-bold text-teal text-uppercase"><i class="fa-solid fa-receipt me-1"></i> Bukti Resi DPMPTSP</span>
                        <span class="badge bg-secondary fs-3xs">Diminta: {{ $sip->durasi_dispensasi_hari ?: 30 }} Hari</span>
                    </div>
                    <div class="row g-1 fs-xs">
                        <div class="col-7">
                            <span class="text-muted fs-3xs d-block">Nomor Resi / Instansi:</span>
                            <span class="font-monospace fw-bold text-dark">{{ $sip->nomor_resi_proses ?: '-' }}</span>
                            <small class="d-block text-secondary fs-3xs">{{ $sip->instansi_penerbit_resi ?: 'DPMPTSP Kota Banjarmasin' }}</small>
                        </div>
                        <div class="col-5">
                            <span class="text-muted fs-3xs d-block">Tanggal Pengajuan:</span>
                            <span class="fw-semibold text-dark">{{ $sip->tanggal_pengajuan_resi ? $sip->tanggal_pengajuan_resi->format('d/m/Y') : '-' }}</span>
                        </div>
                        @if($sip->keterangan_proses)
                            <div class="col-12 mt-1 pt-1 border-top border-teal-subtle">
                                <span class="text-muted fs-3xs d-block">Catatan Dokter:</span>
                                <span class="fst-italic fs-3xs text-dark">{{ $sip->keterangan_proses }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Pratinjau Dokumen (Fixed Height Compact Box) -->
                <div class="flex-grow-1 border rounded overflow-hidden position-relative bg-light" style="min-height: 250px; max-height: 320px;">
                    <div class="d-flex justify-content-between align-items-center bg-white border-bottom px-2 py-1">
                        <span class="fs-3xs fw-bold text-muted"><i class="fa-solid fa-file-pdf me-1 text-danger"></i> Pratinjau Lembar Resi</span>
                        @if($sip->file_bukti_proses)
                            <a href="{{ asset('storage/' . $sip->file_bukti_proses) }}" target="_blank" class="btn btn-2xs btn-outline-teal d-inline-flex align-items-center gap-1">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                <span>Buka Tab Baru</span>
                            </a>
                        @endif
                    </div>

                    @if($sip->file_bukti_proses)
                        @php $ext = pathinfo($sip->file_bukti_proses, PATHINFO_EXTENSION); @endphp
                        @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                            <div class="text-center p-2 h-100 overflow-auto">
                                <img src="{{ asset('storage/' . $sip->file_bukti_proses) }}" alt="Resi DPMPTSP" class="img-fluid rounded shadow-2xs" style="max-height: 270px;">
                            </div>
                        @else
                            <iframe src="{{ asset('storage/' . $sip->file_bukti_proses) }}" title="Resi DPMPTSP PDF" class="w-100 h-100 border-0" style="min-height: 280px;"></iframe>
                        @endif
                    @else
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted p-4">
                            <i class="fa-solid fa-triangle-exclamation fs-3 text-warning mb-2"></i>
                            <span class="fs-xs">Dokter belum melampirkan berkas digital resi.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Formulir Penetapan Durasi & Keputusan SDM -->
    <div class="col-lg-6">
        <div class="card shadow-xs h-100 d-flex flex-column">
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0 fs-sm">
                    <i class="fa-solid fa-stamp text-teal me-1"></i>Formulir Penetapan Durasi & Keputusan SDM
                </h6>
                <span class="badge bg-teal-subtle text-teal fs-3xs">SOP: 14–30 Hari</span>
            </div>

            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <!-- Proteksi Banner Mini -->
                <div class="alert alert-info border-teal bg-teal-subtle py-2 px-3 mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-teal fs-4 flex-shrink-0"></i>
                    <div class="fs-xs text-dark mb-0">
                        <strong>Proteksi Pelayanan (Tata Kelola KPS):</strong> Menyetujui dispensasi mengaktifkan status <code>dalam_proses</code>. Wewenang klinis (SPK/RKK) tidak ditangguhkan mendadak selama dispensasi aktif.
                    </div>
                </div>

                <form action="{{ route('sdm.dispensasi.verifikasi', $sip->id) }}" method="POST" id="formVerifikasiDispensasi">
                    @csrf

                    <!-- Pilihan Keputusan -->
                    <div class="mb-3">
                        <label class="form-label fs-xs fw-bold text-dark mb-1">
                            Keputusan Verifikasi Bukti Resi <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-2">
                            <div class="form-check card p-2 flex-fill border mb-0 {{ old('keputusan', 'setujui') == 'setujui' ? 'border-teal bg-teal-subtle' : '' }}" style="cursor: pointer;">
                                <input class="form-check-input" type="radio" name="keputusan" id="keputusanSetujui" value="setujui" {{ old('keputusan', 'setujui') == 'setujui' ? 'checked' : '' }} onchange="toggleFormKeputusan()">
                                <label class="form-check-label fw-bold text-teal fs-xs" for="keputusanSetujui">
                                    <i class="fa-solid fa-circle-check me-1"></i> Setujui Dispensasi
                                </label>
                                <span class="text-muted fs-3xs d-block">Aktifkan status dalam_proses</span>
                            </div>

                            <div class="form-check card p-2 flex-fill border mb-0 {{ old('keputusan') == 'tolak' ? 'border-danger bg-danger-subtle' : '' }}" style="cursor: pointer;">
                                <input class="form-check-input" type="radio" name="keputusan" id="keputusanTolak" value="tolak" {{ old('keputusan') == 'tolak' ? 'checked' : '' }} onchange="toggleFormKeputusan()">
                                <label class="form-check-label fw-bold text-danger fs-xs" for="keputusanTolak">
                                    <i class="fa-solid fa-circle-xmark me-1"></i> Tolak Bukti Resi
                                </label>
                                <span class="text-muted fs-3xs d-block">Resi tidak sah / buram</span>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Konfigurasi Grace Period (Jika Disetujui) -->
                    <div id="panelSetujui" class="mb-3">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <label class="form-label fs-xs fw-bold text-dark mb-1">
                                    Durasi Dispensasi <span class="text-danger">*</span>
                                </label>
                                <select name="durasi_dispensasi_hari" id="durasiDispensasi" class="form-select form-select-sm @error('durasi_dispensasi_hari') is-invalid @enderror" onchange="hitungTanggalAkhir()">
                                    <option value="30" {{ old('durasi_dispensasi_hari', $sip->durasi_dispensasi_hari ?: 30) == 30 ? 'selected' : '' }}>30 Hari (Maksimal)</option>
                                    <option value="21" {{ old('durasi_dispensasi_hari', $sip->durasi_dispensasi_hari) == 21 ? 'selected' : '' }}>21 Hari (3 Pekan)</option>
                                    <option value="14" {{ old('durasi_dispensasi_hari', $sip->durasi_dispensasi_hari) == 14 ? 'selected' : '' }}>14 Hari (2 Pekan)</option>
                                </select>
                                @error('durasi_dispensasi_hari')
                                    <div class="invalid-feedback fs-3xs">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label fs-xs fw-bold text-dark mb-1">
                                    Tanggal Mulai <span class="text-danger">*</span>
                                </label>
                                @php
                                    $defaultMulai = $sip->tanggal_kadaluarsa && \Carbon\Carbon::parse($sip->tanggal_kadaluarsa)->isFuture()
                                        ? $sip->tanggal_kadaluarsa->format('Y-m-d')
                                        : date('Y-m-d');
                                @endphp
                                <input type="date" name="tanggal_mulai_dispensasi" id="tanggalMulai" class="form-control form-control-sm @error('tanggal_mulai_dispensasi') is-invalid @enderror" value="{{ old('tanggal_mulai_dispensasi', $sip->tanggal_mulai_dispensasi ? $sip->tanggal_mulai_dispensasi->format('Y-m-d') : $defaultMulai) }}" onchange="hitungTanggalAkhir()">
                                @error('tanggal_mulai_dispensasi')
                                    <div class="invalid-feedback fs-3xs">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Kalkulasi Tanggal Akhir Otomatis -->
                        <div class="mt-2 p-2 bg-light rounded border d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted fs-3xs d-block">Batas Akhir Dispensasi:</span>
                                <span class="fs-6 fw-bold text-teal" id="tampilanTanggalAkhir">
                                    {{ $sip->tanggal_akhir_dispensasi ? $sip->tanggal_akhir_dispensasi->translatedFormat('d F Y') : '-' }}
                                </span>
                            </div>
                            <span class="badge bg-teal-subtle text-teal border border-teal-subtle fs-3xs">
                                <i class="fa-solid fa-bell me-1"></i> Notifikasi H-3
                            </span>
                        </div>
                    </div>

                    <!-- Catatan Verifikasi SDM -->
                    <div class="mb-3">
                        <label class="form-label fs-xs fw-bold text-dark mb-1">
                            Catatan Verifikasi / Alasan Penolakan
                        </label>
                        <textarea name="catatan_verifikasi_dispensasi" rows="2" class="form-control form-control-sm @error('catatan_verifikasi_dispensasi') is-invalid @enderror" placeholder="Tuliskan catatan verifikasi bukti proses perizinan atau alasan jika menolak...">{{ old('catatan_verifikasi_dispensasi', $sip->catatan_verifikasi_dispensasi) }}</textarea>
                        @error('catatan_verifikasi_dispensasi')
                            <div class="invalid-feedback fs-3xs">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tombol Aksi Verifikasi -->
                    <button type="submit" id="btnSubmitVerifikasi" class="btn btn-teal text-white w-100 py-2 fw-semibold shadow-xs d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-shield-check"></i>
                        <span id="labelTombolSubmit">Setujui Dispensasi & Aktifkan Status dalam_proses</span>
                    </button>
                </form>

                <!-- Tombol Akhiri Masa Tenggang Jika Sedang Aktif -->
                @if($sip->isGracePeriodAktif())
                    <hr class="my-2">
                    <form action="{{ route('sdm.dispensasi.akhiri', $sip->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengakhiri masa tenggang administratif ini? Gunakan aksi ini jika dokter telah menerima SIP definitif dari DPMPTSP.')">
                        @csrf
                        <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded border">
                            <div>
                                <strong class="fs-3xs text-dark d-block">SIP Definitif Terbit?</strong>
                                <span class="fs-3xs text-muted">Perbarui masa berlaku ke SIP baru.</span>
                            </div>
                            <button type="submit" class="btn btn-2xs btn-outline-danger">
                                <i class="fa-solid fa-flag-checkered me-1"></i> Akhiri Grace Period
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function toggleFormKeputusan() {
        const isSetujui = document.getElementById('keputusanSetujui').checked;
        const panelSetujui = document.getElementById('panelSetujui');
        const btnSubmit = document.getElementById('btnSubmitVerifikasi');
        const labelTombol = document.getElementById('labelTombolSubmit');

        if (isSetujui) {
            panelSetujui.style.display = 'block';
            btnSubmit.className = 'btn btn-teal text-white w-100 py-2 fw-semibold shadow-xs d-flex align-items-center justify-content-center gap-2';
            labelTombol.textContent = 'Setujui Dispensasi & Aktifkan Status dalam_proses';
        } else {
            panelSetujui.style.display = 'none';
            btnSubmit.className = 'btn btn-danger text-white w-100 py-2 fw-semibold shadow-xs d-flex align-items-center justify-content-center gap-2';
            labelTombol.textContent = 'Tolak Permohonan Bukti Resi';
        }
    }

    function hitungTanggalAkhir() {
        const tglMulaiVal = document.getElementById('tanggalMulai').value;
        const durasiVal = parseInt(document.getElementById('durasiDispensasi').value) || 30;
        const display = document.getElementById('tampilanTanggalAkhir');

        if (tglMulaiVal) {
            const date = new Date(tglMulaiVal);
            date.setDate(date.getDate() + durasiVal);

            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            display.textContent = date.toLocaleDateString('id-ID', options);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        hitungTanggalAkhir();
    });
</script>
@endsection
