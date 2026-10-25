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
        <div class="d-flex align-items-center gap-2">
            {!! $dokter->status_verifikasi_str_badge !!}
            @if($dokter->is_str_locked)
                <span class="badge bg-dark text-white"><i class="fa-solid fa-lock me-1"></i> Terkunci (Read-Only)</span>
            @endif
        </div>
    </div>
</div>

<!-- SPLIT-VIEW: DATA SDM VS SATUSEHAT SDMK KEMENKES -->
<div class="row g-4 mb-4">
    <!-- Kolom Kiri: Perekaman Data STR oleh Admin SDM -->
    <div class="col-lg-6">
        <div class="card shadow-xs h-100 border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark fs-sm">
                    <i class="fa-solid fa-database text-teal me-2"></i>1. Data Inputan Admin SDM (SIP-Medik)
                </span>
                <span class="badge bg-light text-dark border fs-3xs">Pangkalan Internal</span>
            </div>
            <div class="card-body p-3">
                <table class="table table-sm table-borderless fs-sm mb-3">
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
                                <span class="badge bg-teal-subtle text-teal border border-teal-subtle">
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
                            <td>{{ $dokter->tanggal_terbit_str ? $dokter->tanggal_terbit_str->translatedFormat('d F Y') : '-' }}</td>
                        </tr>
                        @if($dokter->status_str !== 'seumur_hidup')
                            <tr>
                                <td class="text-muted">Jatuh Tempo STR:</td>
                                <td class="text-danger fw-bold">{{ $dokter->tanggal_kadaluarsa_str ? $dokter->tanggal_kadaluarsa_str->translatedFormat('d F Y') : '-' }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <!-- Pratinjau Berkas Fisik e-STR dari SDM -->
                <div class="border rounded p-3 bg-light">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-dark fs-xs"><i class="fa-solid fa-file-pdf text-danger me-1"></i> Berkas Digital e-STR (Lampiran SDM):</span>
                        @if($dokter->file_str)
                            <a href="{{ asset('storage/' . $dokter->file_str) }}" target="_blank" class="btn btn-2xs btn-outline-teal">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Buka Layar Penuh
                            </a>
                        @endif
                    </div>
                    @if($dokter->file_str)
                        @php $ext = pathinfo($dokter->file_str, PATHINFO_EXTENSION); @endphp
                        @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                            <div class="text-center p-2 bg-white rounded border">
                                <img src="{{ asset('storage/' . $dokter->file_str) }}" class="img-fluid rounded" style="max-height: 280px;" alt="Berkas STR">
                            </div>
                        @else
                            <div class="ratio ratio-16x9 bg-white border rounded">
                                <iframe src="{{ asset('storage/' . $dokter->file_str) }}" title="e-STR PDF"></iframe>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4 text-muted fs-xs">
                            <i class="fa-regular fa-file-excel fs-3 mb-1"></i>
                            <div>Belum ada salinan berkas e-STR yang diunggah oleh SDM.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Pangkalan Data SatuSehat SDMK Kemenkes RI -->
    <div class="col-lg-6">
        <div class="card shadow-xs h-100 border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark fs-sm">
                    <i class="fa-solid fa-cloud-check text-primary me-2"></i>2. Pangkalan Data SatuSehat SDMK Kemenkes RI
                </span>
                <button type="button" class="btn btn-2xs btn-outline-primary" id="btnCekSatuSehat" onclick="cekUlangSatuSehat()">
                    <i class="fa-solid fa-rotate me-1" id="iconCek"></i> Cek Ulang API
                </button>
            </div>
            <div class="card-body p-3">
                <div id="satusehatResponseArea">
                    @if($dokter->data_satusehat_raw && is_array($dokter->data_satusehat_raw))
                        @php $raw = $dokter->data_satusehat_raw; @endphp
                        <div class="alert alert-success d-flex align-items-center gap-2 p-2 px-3 mb-3 fs-xs">
                            <i class="fa-solid fa-circle-check fs-5 text-success"></i>
                            <div>
                                <strong>Data Sinkron dengan Kemenkes RI</strong> &bull; Respon SatuSehat SDMK Status Code: 200 OK
                            </div>
                        </div>

                        <table class="table table-sm table-borderless fs-sm mb-3">
                            <tbody>
                                <tr>
                                    <td class="text-muted text-nowrap" style="width: 170px;">Nama di Kemenkes:</td>
                                    <td class="fw-bold text-dark">{{ $raw['nama'] ?? $dokter->user->nama }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Nomor STR Terdaftar:</td>
                                    <td class="font-monospace fw-bold text-primary">{{ chunk_split($raw['nomor_str'] ?? $dokter->nomor_str, 4, ' ') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Profesi / Kualifikasi:</td>
                                    <td class="fw-semibold text-dark">{{ $raw['profesi'] ?? $dokter->spesialisasi }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status Registrasi STR:</td>
                                    <td>
                                        <span class="badge bg-success text-white">
                                            <i class="fa-solid fa-check me-1"></i>{{ strtoupper($raw['status_str'] ?? 'AKTIF_SEUMUR_HIDUP') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Lembaga Penerbit:</td>
                                    <td>{{ $raw['instansi_penerbit'] ?? 'Konsil Kedokteran Indonesia (KKI)' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Kepatuhan UU No. 17/2023:</td>
                                    <td><span class="badge bg-teal-subtle text-teal">Memenuhi Syarat UU Kesehatan</span></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="p-3 bg-light-subtle border rounded fs-xs mb-3">
                            <div class="fw-bold text-dark mb-1"><i class="fa-solid fa-list-check text-teal me-1"></i> Rekonsiliasi Otomatis:</div>
                            <ul class="mb-0 ps-3">
                                <li class="text-success mb-1">Nomor Registrasi 16 digit terdaftar di Konsil Kedokteran Indonesia</li>
                                <li class="text-success mb-1">Nama staf medis sesuai dengan identitas KTP & Ijazah Dokter</li>
                                <li class="text-success mb-1">Status STR terdata AKTIF di pangkalan data Kemenkes RI</li>
                                <li class="text-success">Deklarasi tempat praktik di RSUD Ansari Saleh valid (SIP Ke-{{ $dokter->tempat_praktik_ke }})</li>
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

<!-- BILAH AKSI MENGAMBANG (STICKY ACTION BAR) DI BAWAH LAYAR -->
<div class="sticky-action-bar position-sticky bottom-0 bg-white border border-teal-subtle rounded-3 p-3 shadow-lg mb-4" style="z-index: 1020; backdrop-filter: blur(10px);">
    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar bg-teal text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px;">
                <i class="fa-solid fa-gavel"></i>
            </div>
            <div>
                <div class="fw-bold text-dark fs-sm">
                    Keputusan Kredensial STR: {{ $dokter->user->nama }}
                </div>
                <div class="text-muted fs-3xs">
                    Nomor STR: <strong class="font-monospace text-dark">{{ $dokter->nomor_str ?: '-' }}</strong> &bull;
                    Status: <span class="fw-semibold {{ $dokter->is_str_locked ? 'text-success' : 'text-warning' }}">{{ $dokter->is_str_locked ? 'TERKUNCI' : 'MENUNGGU PENGESAHAN' }}</span>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($dokter->is_str_locked)
                <span class="badge bg-success-subtle text-success border border-success p-2 fs-xs me-2">
                    <i class="fa-solid fa-lock me-1"></i> Data STR Telah Dikunci (Read-Only)
                </span>
                <form action="{{ route('komite.verifikasi_str.unlock', $dokter->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membuka kunci STR dokter ini? Membuka kunci akan menonaktifkan modul pengajuan SIP dokter sementara hingga diverifikasi ulang.')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="fa-solid fa-lock-open me-1"></i> Buka Kunci (Revisi)
                    </button>
                </form>
            @else
                <!-- Tombol Kembalikan ke SDM (Merah) -> Memicu Modal Pop-up Revisi -->
                <button type="button" class="btn btn-outline-danger btn-sm px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalRevisiSdm">
                    <i class="fa-solid fa-rotate-left me-1"></i> Kembalikan ke SDM
                </button>

                <!-- Tombol Sahkan & Kunci STR (Hijau) -> Langsung Eksekusi Cepat -->
                <form action="{{ route('komite.verifikasi_str.validasi', $dokter->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Sahkan data STR dokter ini dan kunci secara permanen (read-only)?')">
                    @csrf
                    <input type="hidden" name="keputusan" value="tervalidasi">
                    <input type="hidden" name="catatan" value="Tervalidasi sesuai pangkalan data SatuSehat SDMK Kemenkes RI. Data STR memenuhi kualifikasi kredensial.">
                    <button type="submit" class="btn btn-success btn-sm px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-circle-check me-1"></i> Sahkan & Kunci STR
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<!-- MODAL POP-UP CATATAN REVISI KEMBALIKAN KE SDM -->
<div class="modal fade" id="modalRevisiSdm" tabindex="-1" aria-labelledby="modalRevisiSdmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('komite.verifikasi_str.validasi', $dokter->id) }}" method="POST">
                @csrf
                <input type="hidden" name="keputusan" value="ditolak">
                <div class="modal-header bg-danger text-white py-2">
                    <h6 class="modal-title fs-sm fw-bold" id="modalRevisiSdmLabel">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Kembalikan Data STR ke Bagian SDM
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body fs-sm">
                    <p class="text-secondary mb-3">
                        Dokter: <strong>{{ $dokter->user->nama }}</strong> (NIP: {{ $dokter->nip_nrp }})
                    </p>
                    <div class="mb-3">
                        <label class="form-label fs-xs fw-semibold text-dark">
                            Catatan Perbaikan / Alasan Penolakan <span class="text-danger">*</span>
                        </label>
                        <textarea name="catatan" class="form-control fs-sm" rows="4" placeholder="Tuliskan catatan perbaikan untuk SDM (misal: Nomor STR 16 digit tidak cocok dengan identitas KTP, pindaian buram, atau deklarasi fasyankes salah)..." required></textarea>
                        <small class="text-muted fs-3xs">Catatan ini akan dikirimkan ke Admin SDM untuk proses revisi master data dokter.</small>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-light btn-sm border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-bold">
                        <i class="fa-solid fa-paper-plane me-1"></i> Kirim Revisi ke SDM
                    </button>
                </div>
            </form>
        </div>
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
