@extends('layouts.app')

@section('title', 'Telaah Kredensial STR & SatuSehat SDMK: ' . $dokter->user->nama)

@section('content')
<div class="mb-4">
    <a href="{{ route('komite.verifikasi_str.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Verifikasi STR
    </a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                Telaah Master Data STR & Verifikasi SatuSehat SDMK
            </h4>
            <p class="text-secondary fs-sm mb-0">
                Pemeriksaan kesesuaian data STR dokter sebelum penguncian nomor STR (hard constraint) untuk aktivasi pengurusan SIP
            </p>
        </div>
        <div>
            {!! $dokter->status_verifikasi_str_badge !!}
            @if($dokter->is_str_locked)
                <span class="badge bg-dark text-white"><i class="fa-solid fa-lock me-1"></i> Terkunci (Read-Only)</span>
            @endif
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Kolom Kiri: Perekaman Data STR oleh Admin SDM -->
    <div class="col-lg-6">
        <div class="card shadow-xs h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">
                    <i class="fa-solid fa-database text-teal me-2"></i>Data Inputan Admin SDM (Fondasi Hukum)
                </span>
                <span class="badge bg-light text-dark border">Sumber: SIP-Medik SDM</span>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless fs-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted text-nowrap" style="width: 170px;">Nama Lengkap:</td>
                            <td class="fw-bold text-dark">{{ $dokter->user->nama }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">NIP ASN / NRP BLUD:</td>
                            <td class="font-monospace text-dark">{{ $dokter->nip_nrp }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kelompok Staf Medis (KSM):</td>
                            <td class="fw-semibold text-teal">{{ $dokter->ksm->nama_ksm ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kualifikasi Spesialisasi:</td>
                            <td class="fw-bold text-dark">{{ $dokter->spesialisasi }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Subspesialis / Peminatan:</td>
                            <td>{{ $dokter->subspesialis ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Deklarasi Praktik di RSUD:</td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary">
                                    <i class="fa-solid fa-hospital me-1"></i>{{ $dokter->tempat_praktik_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status Kepegawaian:</td>
                            <td><span class="badge bg-light text-dark border">{{ $dokter->status_kepegawaian }}</span></td>
                        </tr>
                        <tr class="border-top">
                            <td class="text-muted pt-3">Nomor Registrasi STR:</td>
                            <td class="pt-3">
                                @if($dokter->nomor_str)
                                    <span class="font-monospace fw-bold fs-6 text-dark px-2 py-1 bg-light rounded border">
                                        {{ chunk_split($dokter->nomor_str, 4, ' ') }}
                                    </span>
                                    <span class="badge {{ strlen($dokter->nomor_str) === 16 ? 'bg-success' : 'bg-danger' }} ms-1">
                                        {{ strlen($dokter->nomor_str) }} Digit
                                    </span>
                                @else
                                    <span class="text-danger fw-semibold">Belum diinput oleh SDM</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Masa Berlaku STR:</td>
                            <td>
                                <span class="badge {{ $dokter->status_str === 'seumur_hidup' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $dokter->status_str === 'seumur_hidup' ? 'Seumur Hidup (UU No. 17/2023)' : 'Berkala' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Terbit STR:</td>
                            <td>{{ $dokter->tanggal_terbit_str ? $dokter->tanggal_terbit_str->format('d F Y') : '-' }}</td>
                        </tr>
                        @if($dokter->status_str !== 'seumur_hidup')
                            <tr>
                                <td class="text-muted">Tanggal Kadaluarsa STR:</td>
                                <td class="fw-bold text-danger">{{ $dokter->tanggal_kadaluarsa_str ? $dokter->tanggal_kadaluarsa_str->format('d F Y') : '-' }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <div class="mt-4 pt-3 border-top">
                    <label class="form-label fw-bold fs-xs text-uppercase text-muted mb-2">
                        <i class="fa-solid fa-file-pdf text-danger me-1"></i>Salinan Digital e-STR Resmi (KKI / Kemenkes):
                    </label>
                    @if($dokter->file_str)
                        <div class="p-3 bg-light rounded border d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-file-invoice text-teal fs-3"></i>
                                <div>
                                    <div class="fw-semibold text-dark fs-sm">Berkas e-STR Dokter</div>
                                    <small class="text-muted fs-xs">Format resmi diterbitkan KKI / Kemenkes RI</small>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ asset('storage/' . $dokter->file_str) }}" target="_blank" class="btn btn-sm btn-teal text-white">
                                    <i class="fa-solid fa-eye me-1"></i> Lihat Dokumen
                                </a>
                                <a href="{{ asset('storage/' . $dokter->file_str) }}" download class="btn btn-sm btn-outline-secondary">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning py-2 fs-xs mb-0">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>Salinan digital e-STR belum diunggah oleh Admin SDM.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Integrasi Pangkalan Data SatuSehat SDMK Kemenkes RI -->
    <div class="col-lg-6">
        <div class="card shadow-xs h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">
                    <i class="fa-solid fa-network-wired text-primary me-2"></i>Pangkalan Data SatuSehat SDMK Kemenkes RI
                </span>
                <span class="badge bg-success-subtle text-success border border-success">
                    <i class="fa-solid fa-shield-check me-1"></i>Konsil Kedokteran Indonesia
                </span>
            </div>
            <div class="card-body">
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <p class="text-secondary fs-xs mb-0">
                        Hasil validasi real-time dari SISDMK Kemenkes untuk memastikan keabsahan nomor registrasi STR 16 digit.
                    </p>
                    <button type="button" class="btn btn-outline-teal btn-sm" id="btnCekSatuSehat" onclick="cekUlangSatuSehat()">
                        <i class="fa-solid fa-arrows-rotate me-1" id="iconCek"></i> Cek Real-time
                    </button>
                </div>

                <div id="satusehatResultArea">
                    @php
                        $ssData = $dokter->satusehat_data ?: ($previewSatuSehat['data'] ?? null);
                    @endphp

                    @if($ssData)
                        <div class="p-3 rounded border bg-light-teal mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-success text-white">
                                    <i class="fa-solid fa-check-circle me-1"></i> STR TERVERIFIKASI AKTIF
                                </span>
                                <small class="text-muted fs-xs">
                                    Ref: {{ $ssData['kode_registrasi_sdmk'] ?? 'SDMK-VALID' }}
                                </small>
                            </div>

                            <div class="row g-2 fs-sm mt-1">
                                <div class="col-4 text-muted">No. STR SDMK:</div>
                                <div class="col-8 font-monospace fw-bold text-dark">{{ $ssData['nomor_str'] ?? $dokter->nomor_str }}</div>

                                <div class="col-4 text-muted">Nama Terdaftar:</div>
                                <div class="col-8 fw-bold text-dark">{{ $ssData['nama_terdaftar'] ?? strtoupper($dokter->user->nama) }}</div>

                                <div class="col-4 text-muted">Kualifikasi KKI:</div>
                                <div class="col-8 text-dark">{{ $ssData['spesialisasi'] ?? $dokter->spesialisasi }}</div>

                                <div class="col-4 text-muted">Faskes Terdaftar:</div>
                                <div class="col-8 text-dark">{{ $ssData['terdaftar_faskes'] ?? 'RSUD dr. H. Moch. Ansari Saleh Kota Banjarmasin' }}</div>

                                <div class="col-4 text-muted">Penerbit:</div>
                                <div class="col-8 text-dark">{{ $ssData['instansi_penerbit'] ?? 'Konsil Kedokteran Indonesia (KKI)' }}</div>
                            </div>
                        </div>

                        <!-- Check Status Checklist -->
                        <div class="border rounded p-3 bg-white mb-3">
                            <h6 class="fw-bold fs-xs text-uppercase text-muted mb-2">Checklist Kesesuaian Kredensial:</h6>
                            <ul class="list-unstyled mb-0 fs-sm">
                                <li class="text-success mb-1">
                                    <i class="fa-solid fa-circle-check me-2"></i>Nomor Registrasi 16 digit terdaftar di Konsil Kedokteran Indonesia
                                </li>
                                <li class="text-success mb-1">
                                    <i class="fa-solid fa-circle-check me-2"></i>Nama staf medis sesuai dengan identitas KTP & Ijazah Dokter
                                </li>
                                <li class="text-success mb-1">
                                    <i class="fa-solid fa-circle-check me-2"></i>Status STR terdata AKTIF di pangkalan data Kemenkes RI
                                </li>
                                <li class="text-success">
                                    <i class="fa-solid fa-circle-check me-2"></i>Deklarasi tempat praktik di RSUD Ansari Saleh valid (SIP Ke-{{ $dokter->tempat_praktik_ke }})
                                </li>
                            </ul>
                        </div>
                    @else
                        <div class="alert alert-warning fs-sm mb-3">
                            <i class="fa-solid fa-circle-info me-1"></i> Data STR belum dapat dipetakan ke SatuSehat SDMK. Pastikan nomor STR 16 digit telah terisi.
                        </div>
                    @endif
                </div>

                @if($dokter->verified_str_at)
                    <div class="p-3 bg-light rounded border fs-xs">
                        <div class="fw-bold text-dark mb-1"><i class="fa-solid fa-signature text-teal me-1"></i> Log Verifikasi Komite Medik:</div>
                        <div>Diverifikasi oleh: <strong>{{ $dokter->verifikatorStr->nama ?? 'Subkomite Kredensial' }}</strong></div>
                        <div>Tanggal Telaah: <strong>{{ $dokter->verified_str_at->format('d F Y, H:i') }} WITA</strong></div>
                        @if($dokter->catatan_verifikasi_str)
                            <div class="mt-1 text-secondary">Catatan: "{{ $dokter->catatan_verifikasi_str }}"</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Form Keputusan & Penguncian Data STR -->
<div class="card shadow-xs border-top border-4 {{ $dokter->is_str_locked ? 'border-success' : 'border-teal' }}">
    <div class="card-body p-4">
        <h5 class="fw-bold text-dark mb-1">
            <i class="fa-solid fa-lock text-teal me-2"></i>Keputusan Subkomite Kredensial & Penguncian Data STR
        </h5>
        <p class="text-secondary fs-sm mb-4">
            Pengesahan ini merupakan <strong>syarat mutlak (hard constraint)</strong>. Setelah divalidasi, nomor STR terkunci (read-only) dan modul pengurusan SIP dokter akan otomatis dibuka.
        </p>

        @if($dokter->is_str_locked)
            <div class="alert alert-success d-flex align-items-center justify-content-between p-3 mb-0">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-solid fa-lock fs-3 text-success"></i>
                    <div>
                        <div class="fw-bold">DATA STR TELAH TERVALIDASI DAN TERKUNCI (READ-ONLY)</div>
                        <small class="text-success-emphasis">
                            Nomor registrasi STR {{ $dokter->nomor_str }} telah dikunci. Modul pengurusan SIP dokter saat ini dalam status AKTIF.
                        </small>
                    </div>
                </div>

                @if(in_array(auth()->user()->role, ['super_admin', 'verifikator_komite']))
                    <form action="{{ route('komite.verifikasi_str.unlock', $dokter->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membuka kunci STR dokter ini? Membuka kunci akan menonaktifkan modul pengajuan SIP dokter sementara hingga diverifikasi ulang.')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fa-solid fa-lock-open me-1"></i> Buka Kunci STR (Revisi)
                        </button>
                    </form>
                @endif
            </div>
        @else
            <form action="{{ route('komite.verifikasi_str.validasi', $dokter->id) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">Keputusan Kredensial Komite Medik <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check p-3 border rounded bg-light">
                                <input class="form-check-input ms-0 me-2" type="radio" name="keputusan" id="keputusanValid" value="tervalidasi" checked>
                                <label class="form-check-label fw-bold text-success" for="keputusanValid">
                                    <i class="fa-solid fa-circle-check me-1"></i> Sahkan & Kunci Nomor STR (Tervalidasi SatuSehat SDMK)
                                </label>
                                <small class="text-muted d-block ms-4 fs-xs">
                                    Mengunci nomor STR (read-only) dan membuka hak akses dokter untuk pengurusan SIP di RSUD Ansari Saleh.
                                </small>
                            </div>

                            <div class="form-check p-3 border rounded bg-light">
                                <input class="form-check-input ms-0 me-2" type="radio" name="keputusan" id="keputusanTolak" value="ditolak">
                                <label class="form-check-label fw-bold text-danger" for="keputusanTolak">
                                    <i class="fa-solid fa-circle-xmark me-1"></i> Tolak / Kembalikan ke Admin SDM untuk Perbaikan
                                </label>
                                <small class="text-muted d-block ms-4 fs-xs">
                                    Nomor STR tidak dikunci dan modul SIP dokter tetap non-aktif.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-sm fw-semibold">Catatan Telaah Subkomite Kredensial</label>
                        <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="5" placeholder="Contoh: Telah diperiksa kesesuaian dengan pangkalan data SatuSehat SDMK Kemenkes RI per {{ date('d/m/Y') }}. Data STR dinyatakan sah dan memenuhi kualifikasi kewenangan klinis."></textarea>
                        @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted fs-xs">Wajib diisi bila keputusan menolak atau memerlukan perbaikan berkas dari SDM.</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('komite.verifikasi_str.index') }}" class="btn btn-light border btn-sm">Batal</a>
                    <button type="submit" class="btn btn-teal text-white btn-sm" onclick="return confirm('Simpan keputusan telaah kredensial STR dokter ini?')">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Keputusan & Eksekusi Kunci Data
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

@push('scripts')
<script>
function cekUlangSatuSehat() {
    const btn = document.getElementById('btnCekSatuSehat');
    const icon = document.getElementById('iconCek');
    icon.classList.add('fa-spin');
    btn.disabled = true;

    fetch('{{ route("komite.verifikasi_str.satusehat", $dokter->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        icon.classList.remove('fa-spin');
        btn.disabled = false;
        if(data.success) {
            alert('Sukses Sinkronisasi SatuSehat SDMK: ' + data.message);
            location.reload();
        } else {
            alert('Peringatan: ' + data.message);
        }
    })
    .catch(err => {
        icon.classList.remove('fa-spin');
        btn.disabled = false;
        alert('Terjadi kendala koneksi ke server SatuSehat SDMK.');
    });
}
</script>
@endpush
@endsection
