@extends('layouts.app')

@section('title', 'Telaah Berkas SIP - Subkomite Kredensial')

@section('content')
<div class="mb-4">
    <a href="{{ route('komite.verifikasi.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Antrean Verifikasi
    </a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Tahap 3: Verifikasi Berkas SIP & Evaluasi Rekredensialing</h4>
            <p class="text-secondary fs-sm mb-0">
                Pemeriksaan pindaian lembar izin DPMPTSP, verifikasi keabsahan fasyankes, serta penetapan keputusan telaah
            </p>
        </div>
        <div>
            @if($sip->status_verifikasi === 'disetujui')
                <span class="badge bg-success fs-sm px-3 py-2"><i class="fa-solid fa-circle-check me-1"></i> Berkas Telah Disahkan</span>
            @elseif($sip->status_verifikasi === 'ditolak')
                <span class="badge bg-danger fs-sm px-3 py-2"><i class="fa-solid fa-circle-xmark me-1"></i> Berkas Ditolak</span>
            @else
                <span class="badge bg-warning text-dark fs-sm px-3 py-2"><i class="fa-solid fa-clock me-1"></i> Menunggu Keputusan Telaah</span>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Internal PDF Engine & Document Preview -->
    <div class="col-lg-7">
        <div class="card shadow-xs mb-3 border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <span class="fw-bold text-dark fs-sm">
                    <i class="fa-solid fa-file-pdf text-danger me-2"></i>Penampil Dokumen Pindaian SIP (Internal PDF Engine)
                </span>
                @if($sip->file_sip)
                    <div class="d-flex gap-2">
                        <a href="{{ route('sip.preview_file', $sip->id) }}" target="_blank" class="btn btn-teal btn-sm fw-bold">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Buka Berkas di Tab Baru
                        </a>
                    </div>
                @endif
            </div>

            <!-- PDF Viewer with fallback against 403 Forbidden -->
            <div class="card-body p-0" style="min-height: 520px; background-color: #f8fafc;">
                @if($sip->file_sip)
                    @php $ext = pathinfo($sip->file_sip, PATHINFO_EXTENSION); @endphp
                    @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                        <div class="text-center p-3">
                            <img src="{{ route('sip.preview_file', $sip->id) }}" class="img-fluid rounded border shadow-sm" alt="Berkas SIP" style="max-height: 560px;">
                        </div>
                    @else
                        <!-- Streamed via preview_file route to guarantee bypass of 403 forbidden / static file restrictions -->
                        <div class="ratio ratio-4x3" style="min-height: 540px;">
                            <iframe src="{{ route('sip.preview_file', $sip->id) }}#toolbar=1&navpanes=0" class="w-100 border-0" title="Pratinjau SIP PDF" allowfullscreen>
                                <p class="p-4 text-center">
                                    Browser Anda tidak mendukung preview inline. Silakan 
                                    <a href="{{ route('sip.preview_file', $sip->id) }}" target="_blank" class="btn btn-sm btn-teal">
                                        Buka Berkas di Tab Baru
                                    </a>
                                </p>
                            </iframe>
                        </div>
                    @endif
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center p-5 text-muted" style="min-height: 400px;">
                        <i class="fa-regular fa-file-excel fs-1 mb-2"></i>
                        <span>Berkas pindaian tidak ditemukan pada server penyimpanan.</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Panel Geser / Collapsible: Parameter Evaluasi Fisik Dokumen (Disembunyikan secara modular) -->
        <div class="accordion mb-4" id="accordionEvaluasiFisik">
            <div class="accordion-item border rounded shadow-2xs overflow-hidden">
                <h2 class="accordion-header" id="headingFisik">
                    <button class="accordion-button collapsed py-2 px-3 bg-light text-secondary fs-xs fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFisik" aria-expanded="false">
                        <i class="fa-solid fa-sliders text-teal me-2"></i> Checklist Uji Fisik Dokumen & Parameter Evaluasi (Klik untuk Buka/Tutup)
                    </button>
                </h2>
                <div id="collapseFisik" class="accordion-collapse collapse" data-bs-parent="#accordionEvaluasiFisik">
                    <div class="accordion-body p-3 bg-white">
                        <div class="row g-2 fs-xs">
                            <div class="col-sm-6">
                                <div class="p-2 border rounded bg-light">
                                    <strong><i class="fa-solid fa-hospital text-teal me-1"></i> Keabsahan Fasyankes:</strong>
                                    <div class="text-muted mt-1">Fasilitas tertuju ke <strong>{{ $sip->fasyankes ?: 'RSUD Ansari Saleh' }}</strong> praktik ke-{{ $sip->tempat_praktik_ke }}.</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-2 border rounded bg-light">
                                    <strong><i class="fa-solid fa-calendar-check text-success me-1"></i> Masa Berlaku 5 Tahun:</strong>
                                    <div class="text-muted mt-1">{{ $sip->tanggal_terbit ? $sip->tanggal_terbit->format('d/m/Y') : '-' }} s/d {{ $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}.</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-2 border rounded bg-light">
                                    <strong><i class="fa-solid fa-eye text-primary me-1"></i> Keterbacaan Pindaian:</strong>
                                    <div class="text-muted mt-1">Stempel basah / barcode perizinan DPMPTSP dapat dibaca dengan jelas.</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-2 border rounded bg-light">
                                    <strong><i class="fa-solid fa-award text-warning me-1"></i> Prasyarat SKP:</strong>
                                    <div class="text-muted mt-1">{{ $sip->status_kecukupan_skp ? 'Terpenuhi via SatuSehat' : ($sip->file_rekomendasi_skp ? 'Surat rekomendasi terlampir' : 'Periksa lampiran') }}.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Metadata & Fast Decision Form -->
    <div class="col-lg-5">
        <!-- 1. Metadata Dokter Ringkas -->
        <div class="card shadow-xs mb-3 border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark fs-sm">
                    <i class="fa-solid fa-id-card-clip text-teal me-2"></i>Metadata Dokter & Izin Praktik
                </span>
                <span class="badge bg-teal-subtle text-teal">{{ $sip->dokter->ksm->kode_ksm ?? '-' }}</span>
            </div>
            <div class="card-body p-3">
                <table class="table table-sm table-borderless fs-sm mb-0">
                    <tr>
                        <td class="text-muted" width="130">Nama Dokter</td>
                        <td class="fw-bold text-dark">: {{ $sip->dokter->user->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">NIP / NRP</td>
                        <td class="font-monospace">: {{ $sip->dokter->nip_nrp ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">KSM / Spesialis</td>
                        <td>: {{ $sip->dokter->ksm->nama_ksm ?? '-' }} ({{ $sip->dokter->spesialisasi ?? 'Umum' }})</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nomor STR</td>
                        <td class="font-monospace">: {{ $sip->dokter->nomor_str ?: '-' }}
                            {!! $sip->dokter->status_verifikasi_str_badge !!}
                        </td>
                    </tr>
                    <tr><td colspan="2"><hr class="my-1"></td></tr>
                    <tr>
                        <td class="text-muted">Nomor Izin SIP</td>
                        <td class="fw-bold font-monospace text-dark">: {{ $sip->nomor_sip }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Fasyankes Tujuan</td>
                        <td>
                            : <span class="fw-bold text-dark">{{ $sip->fasyankes ?: 'RSUD dr. H. Moch. Ansari Saleh' }}</span>
                            <span class="badge bg-success-subtle text-success border border-success ms-1 fs-3xs"><i class="fa-solid fa-circle-check"></i> Sah</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tempat Praktik</td>
                        <td>: SIP Ke-{{ $sip->tempat_praktik_ke }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Prasyarat SKP</td>
                        <td>
                            : {!! $sip->status_kecukupan_skp_badge !!}
                            @if($sip->nomor_rekomendasi_skp)
                                <div class="fs-xs font-monospace text-muted mt-1"><i class="fa-solid fa-file-shield text-primary me-1"></i>No. Rekom: <strong>{{ $sip->nomor_rekomendasi_skp }}</strong></div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Masa Berlaku</td>
                        <td>
                            : {{ $sip->tanggal_terbit ? $sip->tanggal_terbit->translatedFormat('d M Y') : '-' }} s/d 
                            <span class="fw-bold text-danger">{{ $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->translatedFormat('d M Y') : '-' }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- 2. Fast Decision Form: Setujui atau Tolak -->
        <div class="card shadow-xs border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold text-dark mb-0 fs-sm">
                    <i class="fa-solid fa-stamp text-teal me-2"></i>Form Keputusan Telaah Subkomite Kredensial
                </h6>
            </div>
            <div class="card-body p-3">
                <form action="{{ route('komite.verifikasi.proses', $sip->id) }}" method="POST" id="formVerifikasiReview">
                    @csrf

                    <!-- Hidden Auto Checklists -->
                    <input type="hidden" name="checklist_keabsahan_fasyankes" value="1">
                    <input type="hidden" name="checklist_masa_berlaku" value="1">
                    <input type="hidden" name="checklist_keterbacaan_pindaian" id="hiddenChkPindaian" value="1">

                    <!-- Keputusan Cepat: Setujui vs Tolak -->
                    <div class="mb-3">
                        <label class="form-label fs-xs fw-bold text-secondary text-uppercase mb-2">Keputusan Validasi <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="card p-3 border-2 h-100 text-center cursor-pointer transition-all" id="btnRadioSetuju" style="cursor: pointer;">
                                    <input class="form-check-input d-none" type="radio" name="status_verifikasi" id="statusSetuju" value="disetujui" {{ old('status_verifikasi', $sip->status_verifikasi) !== 'ditolak' ? 'checked' : '' }} onchange="handleDecisionChange('disetujui')">
                                    <i class="fa-solid fa-circle-check fs-3 text-success mb-1 d-block"></i>
                                    <span class="fw-bold text-success fs-sm">Setujui</span>
                                    <small class="text-muted d-block fs-3xs">Absah & Sahkan</small>
                                </label>
                            </div>
                            <div class="col-6">
                                <label class="card p-3 border-2 h-100 text-center cursor-pointer transition-all" id="btnRadioTolak" style="cursor: pointer;">
                                    <input class="form-check-input d-none" type="radio" name="status_verifikasi" id="statusTolak" value="ditolak" {{ old('status_verifikasi', $sip->status_verifikasi) === 'ditolak' ? 'checked' : '' }} onchange="handleDecisionChange('ditolak')">
                                    <i class="fa-solid fa-circle-xmark fs-3 text-danger mb-1 d-block"></i>
                                    <span class="fw-bold text-danger fs-sm">Tolak</span>
                                    <small class="text-muted d-block fs-3xs">Perlu Perbaikan</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Geser / Dropdown Templat Cepat Penolakan (Muncul jika ditolak atau saat diinginkan) -->
                    <div id="panelTemplatPenolakan" class="mb-3 d-none">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label fs-3xs text-danger fw-semibold mb-0">Templat Cepat Evaluasi Penolakan:</label>
                            <span class="badge bg-danger-subtle text-danger fs-3xs">Pilih salah satu</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <button type="button" class="btn btn-3xs btn-outline-danger" onclick="setRejectReason('Pindaian dokumen buram, terpotong, atau tidak terbaca dengan jelas. Mohon unggah ulang pindaian dokumen SIP asli beresolusi tinggi.')">
                                Pindaian Buram
                            </button>
                            <button type="button" class="btn btn-3xs btn-outline-danger" onclick="setRejectReason('Masa berlaku SIP tidak sesuai dengan ketentuan regulasi (maksimal 5 tahun) atau tanggal terbit/kadaluarsa tidak cocok.')">
                                Masa Berlaku Salah
                            </button>
                            <button type="button" class="btn btn-3xs btn-outline-danger" onclick="setRejectReason('Nama fasilitas pelayanan kesehatan tidak sesuai dengan fasyankes RSUD dr. H. Moch. Ansari Saleh atau tempat praktik tidak terdaftar.')">
                                Fasyankes Tidak Cocok
                            </button>
                            <button type="button" class="btn btn-3xs btn-outline-danger" onclick="setRejectReason('Nomor SIP atau stempel legalitas perizinan DPMPTSP tidak sah/tidak dapat diverifikasi.')">
                                Nomor SIP Tidak Sah
                            </button>
                        </div>
                    </div>

                    <!-- Catatan Telaah / Evaluasi -->
                    <div class="mb-3">
                        <label class="form-label fs-xs fw-semibold text-dark">
                            Catatan Evaluasi Telaah Klinis <span id="labelWajibTolak" class="text-danger d-none">* (Wajib jika ditolak)</span>
                        </label>
                        <textarea name="catatan_verifikasi" id="txtCatatanVerifikasi" rows="3" class="form-control fs-sm" placeholder="Contoh: Berkas absah sesuai format DPMPTSP. Atau alasan revisi jika ditolak...">{{ old('catatan_verifikasi', $sip->catatan_verifikasi) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-teal text-white btn-sm w-100 py-2 fw-bold shadow-xs">
                        <i class="fa-solid fa-stamp me-1"></i> Simpan & Sahkan Keputusan Telaah
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function handleDecisionChange(status) {
        const btnSetuju = document.getElementById('btnRadioSetuju');
        const btnTolak = document.getElementById('btnRadioTolak');
        const panelTemplat = document.getElementById('panelTemplatPenolakan');
        const labelWajib = document.getElementById('labelWajibTolak');
        const hiddenChkPindaian = document.getElementById('hiddenChkPindaian');

        if (status === 'disetujui') {
            btnSetuju.classList.add('border-success', 'bg-success-subtle');
            btnTolak.classList.remove('border-danger', 'bg-danger-subtle');
            panelTemplat.classList.add('d-none');
            labelWajib.classList.add('d-none');
            hiddenChkPindaian.value = '1';
        } else {
            btnTolak.classList.add('border-danger', 'bg-danger-subtle');
            btnSetuju.classList.remove('border-success', 'bg-success-subtle');
            panelTemplat.classList.remove('d-none');
            labelWajib.classList.remove('d-none');
            hiddenChkPindaian.value = '0';
        }
    }

    function setRejectReason(reason) {
        document.getElementById('txtCatatanVerifikasi').value = reason;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const isTolak = document.getElementById('statusTolak').checked;
        handleDecisionChange(isTolak ? 'ditolak' : 'disetujui');
    });
</script>
@endpush
@endsection
