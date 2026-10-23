@extends('layouts.app')

@section('title', 'Tahap 6: Telaah & Verifikasi Dispensasi Masa Tenggang (SDM)')

@section('content')
<div class="mb-4">
    <a href="{{ route('sdm.dispensasi.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Antrean Dispensasi
    </a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-teal-subtle text-teal border border-teal-subtle px-2 py-1 fs-xs fw-bold">TAHAP 6</span>
                <h4 class="fw-bold text-dark mb-0">Verifikasi Bukti Proses & Pemberian Dispensasi Terbatas</h4>
            </div>
            <p class="text-secondary fs-sm mb-0">
                Pemeriksaan keabsahan resi pengurusan perizinan DPMPTSP/Dinkes untuk mengaktifkan status <code>dalam_proses</code> (Grace Period maksimal 14–30 hari) dan melindungi wewenang klinis (SPK/RKK).
            </p>
        </div>
        <div>
            {!! $sip->status_dispensasi_badge !!}
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-xs mb-4" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation fs-5 mt-1 text-danger"></i>
            <div>
                <strong class="d-block mb-1">Terdapat kesalahan pada input verifikasi:</strong>
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
    <!-- Kolom Kiri: Dokumen Bukti Resi & Informasi Dokter -->
    <div class="col-lg-7">
        <!-- Rincian Dokter & KSM -->
        <div class="card shadow-xs mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="fa-solid fa-user-doctor text-teal me-2"></i>Profil Dokter & Integritas Legalitas
                </h6>
                <span class="badge bg-light text-secondary border fs-xs">NIP: {{ $dokter->nip_nrp }}</span>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="text-muted fs-xs d-block">Nama Lengkap Dokter:</span>
                        <div class="fw-bold text-dark fs-sm">{{ $dokter->user->nama ?? '-' }}</div>
                        <small class="text-muted"><i class="fa-brands fa-whatsapp text-success me-1"></i>{{ $dokter->user->no_wa ?? '-' }}</small>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted fs-xs d-block">Kelompok Staf Medis (KSM):</span>
                        <div class="fw-semibold text-teal fs-sm">{{ $dokter->ksm->nama_ksm ?? '-' }}</div>
                        <small class="text-muted">{{ $dokter->spesialisasi }}</small>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted fs-xs d-block">Status Validasi STR (Tahap 1):</span>
                        {!! $dokter->status_verifikasi_str_badge !!}
                        <span class="font-monospace fs-xs ms-1">{{ $dokter->nomor_str ?: '-' }}</span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted fs-xs d-block">Status Praktik Saat Ini:</span>
                        {!! $dokter->status_praktik_badge !!}
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted fs-xs d-block">Instrumen SPK & RKK (Kredensial):</span>
                        @if($spk)
                            <span class="badge bg-success-subtle text-success border border-success fs-xs">
                                <i class="fa-solid fa-file-signature me-1"></i> SPK No. {{ $spk->nomor_spk ?: $spk->nomor_sk_direktur }}
                            </span>
                            <div class="fs-3xs text-muted mt-1">Masa Berlaku SPK: s.d {{ $spk->tanggal_berakhir_spk ? $spk->tanggal_berakhir_spk->format('d/m/Y') : '-' }}</div>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary fs-xs">Belum Ada SPK</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted fs-xs d-block">SIP Yang Dimintakan Dispensasi:</span>
                        <span class="font-monospace fw-bold text-dark fs-xs">{{ $sip->nomor_sip }}</span>
                        <div>
                            <span class="badge bg-teal-subtle text-teal border border-teal-subtle fs-3xs">SIP Ke-{{ $sip->tempat_praktik_ke }}</span>
                            <span class="text-danger fw-semibold fs-3xs ms-1">Kadaluarsa: {{ $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pratinjau Dokumen Bukti Resi Tanda Terima Pengurusan -->
        <div class="card shadow-xs">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="fa-solid fa-file-lines text-teal me-2"></i>Salinan Digital Resi / Surat Keterangan Proses
                </h6>
                @if($sip->file_bukti_proses)
                    <a href="{{ asset('storage/' . $sip->file_bukti_proses) }}" target="_blank" class="btn btn-2xs btn-outline-teal d-inline-flex align-items-center gap-1">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        <span>Buka Layar Penuh</span>
                    </a>
                @endif
            </div>
            <div class="card-body p-3">
                <div class="mb-3 p-3 bg-light rounded border">
                    <div class="row g-2 fs-sm">
                        <div class="col-sm-6">
                            <span class="text-muted fs-xs d-block">Instansi Perizinan:</span>
                            <strong class="text-dark">{{ $sip->instansi_penerbit_resi ?: 'DPMPTSP Kota Banjarmasin' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted fs-xs d-block">Nomor Resi / Tanda Terima:</span>
                            <strong class="font-monospace text-teal">{{ $sip->nomor_resi_proses ?: '-' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted fs-xs d-block">Tanggal Tanda Terima:</span>
                            <strong class="text-dark">{{ $sip->tanggal_pengajuan_resi ? $sip->tanggal_pengajuan_resi->format('d F Y') : '-' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted fs-xs d-block">Durasi Diajukan Dokter:</span>
                            <span class="badge bg-secondary">{{ $sip->durasi_dispensasi_hari ?: 30 }} Hari Kalender</span>
                        </div>
                        @if($sip->keterangan_proses)
                            <div class="col-12 mt-2 pt-2 border-top">
                                <span class="text-muted fs-xs d-block">Catatan Kronologi Dokter:</span>
                                <div class="text-dark fs-xs fst-italic">{{ $sip->keterangan_proses }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Preview File -->
                @if($sip->file_bukti_proses)
                    @php $ext = pathinfo($sip->file_bukti_proses, PATHINFO_EXTENSION); @endphp
                    @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                        <div class="text-center p-2 border rounded bg-white">
                            <img src="{{ asset('storage/' . $sip->file_bukti_proses) }}" alt="Resi DPMPTSP" class="img-fluid rounded shadow-2xs" style="max-height: 480px;">
                        </div>
                    @else
                        <div class="ratio ratio-16x9 border rounded shadow-2xs overflow-hidden" style="min-height: 400px;">
                            <iframe src="{{ asset('storage/' . $sip->file_bukti_proses) }}" title="Resi DPMPTSP PDF"></iframe>
                        </div>
                    @endif
                @else
                    <div class="alert alert-warning mb-0 fs-sm">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Dokter belum melampirkan berkas tanda terima digital.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Formulir Keputusan Admin SDM -->
    <div class="col-lg-5">
        <!-- Kartu Jaminan Proteksi Pelayanan Rumah Sakit -->
        <div class="card shadow-xs border-teal bg-teal-subtle mb-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-shield-halved text-teal fs-4"></i>
                    <h6 class="fw-bold text-teal mb-0">Proteksi Pelayanan (Tata Kelola KPS)</h6>
                </div>
                <p class="fs-xs text-dark mb-0">
                    Dengan menyetujui dispensasi ini, status SIP akan bertransisi menjadi <strong>dalam_proses</strong>. Status wewenang klinis (SPK/RKK) di sistem <strong>tidak ditangguhkan mendadak</strong> selama masa dispensasi berlangsung, menjaga kelangsungan pelayanan medis rumah sakit tetap tertib dan terlindungi secara administratif.
                </p>
            </div>
        </div>

        <!-- Formulir Verifikasi & Keputusan SDM -->
        <div class="card shadow-xs">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="fa-solid fa-stamp text-teal me-2"></i>Formulir Verifikasi Bagian Kepegawaian SDM
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('sdm.dispensasi.verifikasi', $sip->id) }}" method="POST" id="formVerifikasiDispensasi">
                    @csrf

                    <!-- Pilihan Keputusan -->
                    <div class="mb-3">
                        <label class="form-label fs-sm fw-semibold text-dark">
                            Keputusan Verifikasi Bukti Proses <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-3">
                            <div class="form-check card p-3 flex-fill border {{ old('keputusan', 'setujui') == 'setujui' ? 'border-teal bg-teal-subtle' : '' }}" style="cursor: pointer;">
                                <input class="form-check-input" type="radio" name="keputusan" id="keputusanSetujui" value="setujui" {{ old('keputusan', 'setujui') == 'setujui' ? 'checked' : '' }} onchange="toggleFormKeputusan()">
                                <label class="form-check-label fw-bold text-teal fs-sm" for="keputusanSetujui">
                                    <i class="fa-solid fa-circle-check me-1"></i> Setujui Dispensasi
                                </label>
                                <small class="text-muted fs-3xs d-block mt-1">Aktifkan status dalam_proses (Grace Period)</small>
                            </div>

                            <div class="form-check card p-3 flex-fill border {{ old('keputusan') == 'tolak' ? 'border-danger bg-danger-subtle' : '' }}" style="cursor: pointer;">
                                <input class="form-check-input" type="radio" name="keputusan" id="keputusanTolak" value="tolak" {{ old('keputusan') == 'tolak' ? 'checked' : '' }} onchange="toggleFormKeputusan()">
                                <label class="form-check-label fw-bold text-danger fs-sm" for="keputusanTolak">
                                    <i class="fa-solid fa-circle-xmark me-1"></i> Tolak Bukti Resi
                                </label>
                                <small class="text-muted fs-3xs d-block mt-1">Resi tidak sah / tidak terbaca</small>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Konfigurasi Grace Period (Jika Disetujui) -->
                    <div id="panelSetujui" class="mb-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fs-sm fw-semibold text-dark">
                                    Durasi Dispensasi (Hari) <span class="text-danger">*</span>
                                </label>
                                <select name="durasi_dispensasi_hari" id="durasiDispensasi" class="form-select @error('durasi_dispensasi_hari') is-invalid @enderror" onchange="hitungTanggalAkhir()">
                                    <option value="30" {{ old('durasi_dispensasi_hari', $sip->durasi_dispensasi_hari ?: 30) == 30 ? 'selected' : '' }}>30 Hari (Maksimal)</option>
                                    <option value="21" {{ old('durasi_dispensasi_hari', $sip->durasi_dispensasi_hari) == 21 ? 'selected' : '' }}>21 Hari (3 Pekan)</option>
                                    <option value="14" {{ old('durasi_dispensasi_hari', $sip->durasi_dispensasi_hari) == 14 ? 'selected' : '' }}>14 Hari (2 Pekan)</option>
                                </select>
                                @error('durasi_dispensasi_hari')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text fs-3xs">SOP Rumah Sakit: Maksimal 14–30 hari kalender.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fs-sm fw-semibold text-dark">
                                    Tanggal Mulai Berlaku <span class="text-danger">*</span>
                                </label>
                                @php
                                    $defaultMulai = $sip->tanggal_kadaluarsa && \Carbon\Carbon::parse($sip->tanggal_kadaluarsa)->isFuture()
                                        ? $sip->tanggal_kadaluarsa->format('Y-m-d')
                                        : date('Y-m-d');
                                @endphp
                                <input type="date" name="tanggal_mulai_dispensasi" id="tanggalMulai" class="form-control @error('tanggal_mulai_dispensasi') is-invalid @enderror" value="{{ old('tanggal_mulai_dispensasi', $sip->tanggal_mulai_dispensasi ? $sip->tanggal_mulai_dispensasi->format('Y-m-d') : $defaultMulai) }}" onchange="hitungTanggalAkhir()">
                                @error('tanggal_mulai_dispensasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Kalkulasi Tanggal Akhir Otomatis -->
                        <div class="mt-3 p-3 bg-light rounded border">
                            <span class="text-muted fs-xs d-block">Batas Akhir Dispensasi (Grace Period):</span>
                            <div class="fs-5 fw-bold text-teal" id="tampilanTanggalAkhir">
                                {{ $sip->tanggal_akhir_dispensasi ? $sip->tanggal_akhir_dispensasi->translatedFormat('d F Y') : '-' }}
                            </div>
                            <small class="text-muted fs-3xs">
                                <i class="fa-solid fa-circle-info me-1 text-teal"></i>Sistem akan mengirimkan notifikasi H-3 sebelum tanggal ini tiba.
                            </small>
                        </div>
                    </div>

                    <!-- Catatan Verifikasi SDM -->
                    <div class="mb-4">
                        <label class="form-label fs-sm fw-semibold text-dark">
                            Catatan Verifikasi / Alasan Keputusan
                        </label>
                        <textarea name="catatan_verifikasi_dispensasi" rows="3" class="form-control @error('catatan_verifikasi_dispensasi') is-invalid @enderror" placeholder="Tuliskan catatan verifikasi bukti proses perizinan atau alasan jika menolak...">{{ old('catatan_verifikasi_dispensasi', $sip->catatan_verifikasi_dispensasi) }}</textarea>
                        @error('catatan_verifikasi_dispensasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tombol Aksi Verifikasi -->
                    <div class="d-flex flex-column gap-2">
                        <button type="submit" id="btnSubmitVerifikasi" class="btn btn-teal text-white w-100 py-2 fw-semibold shadow-xs d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-shield-check"></i>
                            <span id="labelTombolSubmit">Setujui Dispensasi & Aktifkan Status dalam_proses</span>
                        </button>
                    </div>
                </form>

                <!-- Tombol Akhiri Masa Tenggang Jika Sedang Aktif -->
                @if($sip->isGracePeriodAktif())
                    <hr class="my-4">
                    <form action="{{ route('sdm.dispensasi.akhiri', $sip->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengakhiri masa tenggang administratif ini? Gunakan aksi ini jika dokter telah menerima SIP definitif dari DPMPTSP.')">
                        @csrf
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded border">
                            <div>
                                <strong class="fs-xs text-dark d-block">SIP Definitif Telah Diterbitkan?</strong>
                                <span class="fs-3xs text-muted">Akhiri dispensasi untuk memperbarui masa berlaku ke SIP baru.</span>
                            </div>
                            <button type="submit" class="btn btn-xs btn-outline-danger">
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
