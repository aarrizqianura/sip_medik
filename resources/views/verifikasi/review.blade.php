@extends('layouts.app')

@section('title', 'Telaah Berkas SIP - Komite Medik')

@section('content')
<div class="mb-4">
    <a href="{{ route('komite.verifikasi.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Antrean Verifikasi
    </a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Telaah Berkas SIP: {{ $sip->dokter->user->nama }}</h4>
            <p class="text-secondary fs-sm mb-0">
                Pemeriksaan keabsahan dokumen perizinan praktik kedokteran DPMPTSP Kota/Provinsi
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
    <!-- Left Column: Document Viewer -->
    <div class="col-lg-7">
        <div class="card shadow-xs h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="fw-bold text-dark"><i class="fa-solid fa-file-pdf text-danger me-2"></i> Pindaian Berkas SIP Fisik</span>
                @if($sip->file_sip)
                    <a href="{{ asset('storage/' . $sip->file_sip) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Buka Layar Penuh
                    </a>
                @endif
            </div>
            <div class="card-body p-0" style="min-height: 550px;">
                @if($sip->file_sip)
                    @php $ext = pathinfo($sip->file_sip, PATHINFO_EXTENSION); @endphp
                    @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                        <div class="text-center p-3">
                            <img src="{{ asset('storage/' . $sip->file_sip) }}" class="img-fluid rounded border shadow-sm" alt="Berkas SIP">
                        </div>
                    @else
                        <iframe src="{{ asset('storage/' . $sip->file_sip) }}" class="w-100 border-0" style="height: 600px;"></iframe>
                    @endif
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 p-5 text-muted">
                        <i class="fa-regular fa-file-excel fs-1 mb-2"></i>
                        <span>Berkas pindaian tidak ditemukan pada server penyimpanan.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Verification Action & Metadata -->
    <div class="col-lg-5">
        <!-- Metadata Dokter & SIP -->
        <div class="card shadow-xs mb-4">
            <div class="card-header fw-bold text-dark">
                <i class="fa-solid fa-id-card-clip text-teal me-2"></i>
                Metadata SIP & Dokter Pengaju
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
                        <td class="text-muted">KSM</td>
                        <td>: {{ $sip->dokter->ksm->nama_ksm ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Spesialisasi</td>
                        <td>: {{ $sip->dokter->spesialisasi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status STR</td>
                        <td>
                            : <span class="badge {{ $sip->dokter->status_str === 'seumur_hidup' ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $sip->dokter->status_str === 'seumur_hidup' ? 'STR Seumur Hidup' : 'STR Berkala' }}
                            </span>
                        </td>
                    </tr>
                    <tr><td colspan="2"><hr class="my-1"></td></tr>
                    <tr>
                        <td class="text-muted">Nomor Izin SIP</td>
                        <td class="fw-bold text-dark font-monospace">: {{ $sip->nomor_sip }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Penerbit Izin</td>
                        <td>: {{ $sip->penerbit_sip ?: 'DPMPTSP Kota Banjarmasin' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Fasyankes Tujuan</td>
                        <td>
                            : <span class="fw-bold text-dark">{{ $sip->fasyankes ?: 'RSUD dr. H. Moch. Ansari Saleh' }}</span>
                            <span class="badge bg-success-subtle text-success border border-success ms-1 fs-2xs"><i class="fa-solid fa-circle-check"></i> Sesuai Alamat</span>
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
                                <div class="fs-xs font-monospace text-secondary mt-1">No: {{ $sip->nomor_rekomendasi_skp }}</div>
                            @endif
                            @if($sip->file_rekomendasi_skp)
                                <div class="mt-1">
                                    <a href="{{ asset('storage/' . $sip->file_rekomendasi_skp) }}" target="_blank" class="btn btn-2xs btn-outline-teal text-teal">
                                        <i class="fa-solid fa-file-pdf me-1"></i> Surat Rekomendasi SKP SatuSehat
                                    </a>
                                </div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Terbit</td>
                        <td>: {{ $sip->tanggal_terbit ? $sip->tanggal_terbit->translatedFormat('d F Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jatuh Tempo (5 Thn)</td>
                        <td class="fw-bold text-danger">: {{ $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->translatedFormat('d F Y') : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Form Keputusan Telaah Klinis -->
        <div class="card shadow-xs">
            <div class="card-header fw-bold text-dark">
                <i class="fa-solid fa-signature text-teal me-2"></i>
                Keputusan Telaah Subkomite Kredensial
            </div>
            <div class="card-body p-4">
                <form action="{{ route('komite.verifikasi.proses', $sip->id) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fs-sm fw-semibold">Keputusan Validasi <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status_verifikasi" id="statusSetuju" value="disetujui" {{ $sip->status_verifikasi === 'disetujui' ? 'checked' : '' }} required>
                                <label class="form-check-label fw-semibold text-success fs-sm" for="statusSetuju">
                                    <i class="fa-solid fa-circle-check me-1"></i> Setujui (Absah & Aktif)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status_verifikasi" id="statusTolak" value="ditolak" {{ $sip->status_verifikasi === 'ditolak' ? 'checked' : '' }} required>
                                <label class="form-check-label fw-semibold text-danger fs-sm" for="statusTolak">
                                    <i class="fa-solid fa-circle-xmark me-1"></i> Tolak (Perlu Perbaikan)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fs-sm fw-semibold">Catatan Telaah Klinis / Evaluasi</label>
                        <textarea name="catatan_verifikasi" rows="4" class="form-control fs-sm" placeholder="Contoh: Berkas absah dan sesuai STR seumur hidup. Atau tuliskan alasan jika ditolak (misal: pindaian buram / masa berlaku tidak cocok)...">{{ old('catatan_verifikasi', $sip->catatan_verifikasi) }}</textarea>
                        <small class="text-muted fs-xs">Wajib diisi apabila Anda menolak pengajuan berkas.</small>
                    </div>

                    @if($sip->verifikator)
                        <div class="p-3 bg-light rounded-3 mb-3 fs-xs text-secondary">
                            <div>Ditinjau oleh: <strong>{{ $sip->verifikator->nama }}</strong></div>
                            <div>Waktu Pengesahan: <strong>{{ $sip->verified_at ? $sip->verified_at->format('d/m/Y H:i:s') : '-' }} WITA</strong></div>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-teal text-white btn-sm w-100 py-2">
                        <i class="fa-solid fa-stamp me-1"></i> Simpan & Sahkan Keputusan Telaah
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
