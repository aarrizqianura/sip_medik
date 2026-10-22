@extends('layouts.app')

@section('title', 'Telaah Berkas SIP - Komite Medik')

@section('content')
<div class="mb-4">
    <a href="{{ route('komite.verifikasi.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Antrean Verifikasi
    </a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Tahap 3: Verifikasi Berkas & Inisiasi Rekredensialing</h4>
            <p class="text-secondary fs-sm mb-0">
                Pemeriksaan pindaian SIP dokter: validasi masa berlaku, keabsahan fasyankes, serta inisiasi otomatis alur rekredensialing SPK & RKK
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
    <!-- Left Column: Document Viewer & Checklist Verifikasi -->
    <div class="col-lg-7">
        <!-- Card Pindaian Dokumen Fisik -->
        <div class="card shadow-xs mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="fw-bold text-dark"><i class="fa-solid fa-file-pdf text-danger me-2"></i> Pindaian Berkas SIP Fisik</span>
                @if($sip->file_sip)
                    <a href="{{ asset('storage/' . $sip->file_sip) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Buka Layar Penuh
                    </a>
                @endif
            </div>
            <div class="card-body p-0" style="min-height: 520px; background-color: #f8fafc;">
                @if($sip->file_sip)
                    @php $ext = pathinfo($sip->file_sip, PATHINFO_EXTENSION); @endphp
                    @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                        <div class="text-center p-3">
                            <img src="{{ asset('storage/' . $sip->file_sip) }}" class="img-fluid rounded border shadow-sm" alt="Berkas SIP" style="max-height: 560px;">
                        </div>
                    @else
                        <iframe src="{{ asset('storage/' . $sip->file_sip) }}" class="w-100 border-0" style="height: 580px;"></iframe>
                    @endif
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 p-5 text-muted">
                        <i class="fa-regular fa-file-excel fs-1 mb-2"></i>
                        <span>Berkas pindaian tidak ditemukan pada server penyimpanan.</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Card Checklist Pemeriksaan Dokumen Fisik -->
        <div class="card shadow-xs border-primary-subtle">
            <div class="card-header bg-primary-subtle text-primary fw-bold">
                <i class="fa-solid fa-list-check me-2"></i> Parameter Evaluasi Fisik Dokumen (Subkomite Kredensial)
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-2 border rounded bg-white h-100">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-hospital text-teal"></i>
                                <span class="fw-semibold text-dark fs-sm">Keabsahan Fasyankes</span>
                            </div>
                            <p class="fs-xs text-muted mb-0">
                                Mencocokkan nama fasilitas (<strong>{{ $sip->fasyankes ?: 'RSUD dr. H. Moch. Ansari Saleh' }}</strong>) dan alamat praktik ke-{{ $sip->tempat_praktik_ke }}.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-2 border rounded bg-white h-100">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-calendar-check text-success"></i>
                                <span class="fw-semibold text-dark fs-sm">Validitas Masa Berlaku</span>
                            </div>
                            <p class="fs-xs text-muted mb-0">
                                Masa berlaku 5 tahun terhitung {{ $sip->tanggal_terbit ? $sip->tanggal_terbit->format('d/m/Y') : '-' }} s/d {{ $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-2 border rounded bg-white h-100">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-eye text-primary"></i>
                                <span class="fw-semibold text-dark fs-sm">Keterbacaan Pindaian</span>
                            </div>
                            <p class="fs-xs text-muted mb-0">
                                Berkas tidak buram, stempel basah/QR code legalitas DPMPTSP dapat diverifikasi jelas.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-2 border rounded bg-white h-100">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-award text-warning"></i>
                                <span class="fw-semibold text-dark fs-sm">Prasyarat SKP SatuSehat</span>
                            </div>
                            <p class="fs-xs text-muted mb-0">
                                {{ $sip->status_kecukupan_skp ? 'Kecukupan SKP terkonfirmasi SatuSehat' : ($sip->file_rekomendasi_skp ? 'Surat rekomendasi SKP terlampir' : 'Periksa lampiran SKP') }}.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Metadata, Rekredensialing Trigger, and Action Form -->
    <div class="col-lg-5">
        <!-- 1. Metadata Dokter & SIP -->
        <div class="card shadow-xs mb-4">
            <div class="card-header fw-bold text-dark d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-id-card-clip text-teal me-2"></i> Metadata Dokter & Izin Praktik</span>
                <span class="badge bg-teal-subtle text-teal">{{ $sip->dokter->ksm->kode_ksm ?? '-' }}</span>
            </div>
            <div class="card-body p-3">
                <table class="table table-sm table-borderless fs-sm mb-0">
                    <tr>
                        <td class="text-muted" width="140">Nama Dokter</td>
                        <td class="fw-bold text-dark">: {{ $sip->dokter->user->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">NIP / NRP</td>
                        <td>: {{ $sip->dokter->nip_nrp ?? '-' }}</td>
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
                        <td class="fw-bold text-dark font-monospace">: {{ $sip->nomor_sip }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Fasyankes Tujuan</td>
                        <td>
                            : <span class="fw-bold text-dark">{{ $sip->fasyankes ?: 'RSUD dr. H. Moch. Ansari Saleh' }}</span>
                            <span class="badge bg-success-subtle text-success border border-success ms-1 fs-2xs"><i class="fa-solid fa-circle-check"></i> Sah</span>
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

        <!-- 2. Status Instrumen SPK & RKK & Pemicu Rekredensialing -->
        <div class="card shadow-xs mb-4 {{ $evaluasiRekredensial['perlu'] ? 'border-warning' : 'border-success' }}">
            <div class="card-header fw-bold {{ $evaluasiRekredensial['perlu'] ? 'bg-warning-subtle text-warning-emphasis' : 'bg-success-subtle text-success' }} d-flex justify-content-between align-items-center">
                <span>
                    <i class="fa-solid fa-shield-halved me-2"></i> Pemicu Alur Rekredensialing (SPK & RKK)
                </span>
                <span class="badge {{ $evaluasiRekredensial['perlu'] ? 'bg-warning text-dark' : 'bg-success' }}">
                    {{ $evaluasiRekredensial['perlu'] ? 'Pemicu Aktif' : 'SPK Sah' }}
                </span>
            </div>
            <div class="card-body p-3">
                @php
                    $spkAktif = $sip->dokter->kredensialTerbaru ?: $sip->dokter->spkTerbaru;
                @endphp
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fs-xs text-muted">SPK Terakhir Dokter:</span>
                    <span class="font-monospace fs-xs fw-bold">{{ $spkAktif ? $spkAktif->nomor_spk : 'Belum Ada SPK' }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fs-xs text-muted">Masa Berlaku Siklus (3 Thn):</span>
                    <span class="fs-xs fw-semibold {{ $spkAktif && $spkAktif->hitungSisaHari() < 0 ? 'text-danger' : 'text-dark' }}">
                        {{ $spkAktif && $spkAktif->tanggal_berakhir_spk ? $spkAktif->tanggal_berakhir_spk->format('d/m/Y') . ' (' . $spkAktif->hitungSisaHari() . ' hari)' : 'Tidak Tercatat' }}
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fs-xs text-muted">Status Instrumen Klinis:</span>
                    <div>{!! $spkAktif ? $spkAktif->status_badge : '<span class="badge bg-secondary">Belum Terbit</span>' !!}</div>
                </div>

                <hr class="my-2">

                @if($evaluasiRekredensial['perlu'])
                    <div class="p-2 rounded bg-warning-subtle border border-warning fs-xs text-warning-emphasis">
                        <div class="fw-bold mb-1"><i class="fa-solid fa-bell me-1"></i> Pemicu Otomatis Alur Rekredensialing:</div>
                        <div>{{ $evaluasiRekredensial['alasan'] }}</div>
                        <div class="mt-2 text-dark">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i>
                            Ketika berkas SIP ini disetujui, sistem secara otomatis mengubah status dokter menjadi <strong>proses_rekredensial</strong> dan mengarahkan Anda ke <strong>Sidang Kredensial & Penetapan RKK bersama Mitra Bestari</strong>.
                        </div>
                    </div>
                @else
                    <div class="p-2 rounded bg-success-subtle border border-success fs-xs text-success">
                        <i class="fa-solid fa-circle-check me-1"></i>
                        Instrumen SPK & RKK dokter masih berlaku absah dalam siklus evaluasi 3 tahun. Alur rekredensialing tidak dipicu kecuali terdapat permohonan kewenangan klinis baru.
                    </div>
                @endif
            </div>
        </div>

        <!-- 3. Form Keputusan Telaah Klinis & Validasi -->
        <div class="card shadow-xs">
            <div class="card-header fw-bold text-dark">
                <i class="fa-solid fa-signature text-teal me-2"></i> Keputusan Telaah Subkomite Kredensial
            </div>
            <div class="card-body p-4">
                <form action="{{ route('komite.verifikasi.proses', $sip->id) }}" method="POST" id="formVerifikasiSip">
                    @csrf

                    <!-- Checklist Verifikasi Langsung -->
                    <div class="mb-3 p-3 bg-light rounded border">
                        <label class="form-label fs-xs fw-bold text-uppercase text-secondary mb-2">Hasil Uji Fisik Dokumen</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="checklist_keterbacaan_pindaian" id="chkPindaian" value="1" {{ old('checklist_keterbacaan_pindaian', $sip->checklist_keterbacaan_pindaian) ? 'checked' : 'checked' }}>
                            <label class="form-check-label fs-xs" for="chkPindaian">
                                <i class="fa-solid fa-check text-success me-1"></i> Pindaian dokumen jelas, tajam & tidak buram
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="checklist_keabsahan_fasyankes" id="chkFasyankes" value="1" {{ old('checklist_keabsahan_fasyankes', $sip->checklist_keabsahan_fasyankes) ? 'checked' : 'checked' }}>
                            <label class="form-check-label fs-xs" for="chkFasyankes">
                                <i class="fa-solid fa-check text-success me-1"></i> Keabsahan nama fasyankes & alamat RSUD Ansari Saleh cocok
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="checklist_masa_berlaku" id="chkMasaBerlaku" value="1" {{ old('checklist_masa_berlaku', $sip->checklist_masa_berlaku) ? 'checked' : 'checked' }}>
                            <label class="form-check-label fs-xs" for="chkMasaBerlaku">
                                <i class="fa-solid fa-check text-success me-1"></i> Masa berlaku sah & tanggal kadaluarsa 5 tahun sesuai
                            </label>
                        </div>
                    </div>

                    <!-- Pilihan Penambahan Kewenangan Klinis Baru -->
                    <div class="mb-3 p-3 bg-light rounded border">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permohonan_kewenangan_baru" id="chkKewenanganBaru" value="1" {{ old('permohonan_kewenangan_baru', $sip->permohonan_kewenangan_baru) ? 'checked' : '' }} onchange="toggleKewenanganBaru(this)">
                            <label class="form-check-label fs-xs fw-semibold text-dark" for="chkKewenanganBaru">
                                <i class="fa-solid fa-plus-circle text-primary me-1"></i> Dokter mengajukan permohonan penambahan kewenangan klinis baru
                            </label>
                        </div>
                        <div id="boxCatatanKewenangan" class="mt-2 {{ old('permohonan_kewenangan_baru', $sip->permohonan_kewenangan_baru) ? '' : 'd-none' }}">
                            <textarea name="catatan_kewenangan_baru" rows="2" class="form-control fs-xs" placeholder="Tuliskan tindakan/kewenangan baru yang diajukan (misal: Laparoskopi Lanjutan / Tindakan Intervensi)...">{{ old('catatan_kewenangan_baru', $sip->catatan_kewenangan_baru) }}</textarea>
                            <small class="text-muted fs-2xs">Catatan ini otomatis diteruskan sebagai agenda pembahasan Sidang Mitra Bestari.</small>
                        </div>
                    </div>

                    <!-- Keputusan Validasi -->
                    <div class="mb-3">
                        <label class="form-label fs-sm fw-semibold">Keputusan Validasi <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status_verifikasi" id="statusSetuju" value="disetujui" {{ old('status_verifikasi', $sip->status_verifikasi) === 'disetujui' ? 'checked' : ($sip->status_verifikasi !== 'ditolak' ? 'checked' : '') }} required onchange="handleStatusChange('disetujui')">
                                <label class="form-check-label fw-semibold text-success fs-sm" for="statusSetuju">
                                    <i class="fa-solid fa-circle-check me-1"></i> Setujui (Absah & Sahkan)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status_verifikasi" id="statusTolak" value="ditolak" {{ old('status_verifikasi', $sip->status_verifikasi) === 'ditolak' ? 'checked' : '' }} required onchange="handleStatusChange('ditolak')">
                                <label class="form-check-label fw-semibold text-danger fs-sm" for="statusTolak">
                                    <i class="fa-solid fa-circle-xmark me-1"></i> Tolak (Perlu Perbaikan)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Reject Buttons jika ingin tolak cepat -->
                    <div class="mb-2" id="quickRejectOptions">
                        <label class="form-label fs-2xs text-muted mb-1">Templat Evaluasi Cepat Penolakan:</label>
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-2xs btn-outline-danger" onclick="setRejectReason('Pindaian dokumen buram, terpotong, atau tidak terbaca dengan jelas. Mohon unggah ulang pindaian dokumen SIP asli beresolusi tinggi.')">
                                Pindaian Buram
                            </button>
                            <button type="button" class="btn btn-2xs btn-outline-danger" onclick="setRejectReason('Masa berlaku SIP tidak sesuai dengan ketentuan regulasi (maksimal 5 tahun) atau tanggal terbit/kadaluarsa tidak cocok.')">
                                Masa Berlaku Salah
                            </button>
                            <button type="button" class="btn btn-2xs btn-outline-danger" onclick="setRejectReason('Nama fasilitas pelayanan kesehatan tidak sesuai dengan fasyankes RSUD dr. H. Moch. Ansari Saleh atau tempat praktik tidak terdaftar.')">
                                Fasyankes Tidak Cocok
                            </button>
                            <button type="button" class="btn btn-2xs btn-outline-danger" onclick="setRejectReason('Nomor SIP atau stempel legalitas perizinan DPMPTSP tidak sah/tidak dapat diverifikasi.')">
                                Nomor SIP Tidak Sah
                            </button>
                        </div>
                    </div>

                    <!-- Textarea Catatan Telaah / Evaluasi -->
                    <div class="mb-4">
                        <label class="form-label fs-sm fw-semibold">Catatan Evaluasi / Telaah Klinis <span id="labelWajibTolak" class="text-danger d-none">* (Wajib jika ditolak)</span></label>
                        <textarea name="catatan_verifikasi" id="txtCatatanVerifikasi" rows="4" class="form-control fs-sm" placeholder="Contoh: Berkas absah dan sesuai STR. Atau tuliskan alasan jika ditolak (misal: pindaian buram / masa berlaku salah)...">{{ old('catatan_verifikasi', $sip->catatan_verifikasi) }}</textarea>
                    </div>

                    @if($sip->verifikator)
                        <div class="p-3 bg-light rounded-3 mb-3 fs-xs text-secondary">
                            <div>Ditinjau terakhir oleh: <strong>{{ $sip->verifikator->nama }}</strong></div>
                            <div>Waktu Pengesahan: <strong>{{ $sip->verified_at ? $sip->verified_at->format('d/m/Y H:i:s') : '-' }} WITA</strong></div>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-teal text-white btn-sm w-100 py-2 fw-semibold shadow-xs">
                        <i class="fa-solid fa-stamp me-1"></i> Simpan & Sahkan Keputusan Telaah
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleKewenanganBaru(chk) {
        const box = document.getElementById('boxCatatanKewenangan');
        if (chk.checked) {
            box.classList.remove('d-none');
        } else {
            box.classList.add('d-none');
        }
    }

    function setRejectReason(reason) {
        document.getElementById('statusTolak').checked = true;
        document.getElementById('txtCatatanVerifikasi').value = reason;
        handleStatusChange('ditolak');
    }

    function handleStatusChange(status) {
        const labelWajib = document.getElementById('labelWajibTolak');
        const chkPindaian = document.getElementById('chkPindaian');
        if (status === 'ditolak') {
            labelWajib.classList.remove('d-none');
            chkPindaian.checked = false;
        } else {
            labelWajib.classList.add('d-none');
            chkPindaian.checked = true;
        }
    }
</script>
@endsection
