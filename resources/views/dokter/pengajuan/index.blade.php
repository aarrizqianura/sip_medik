@extends('layouts.app')

@section('title', 'Pengajuan SIP Saya')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Riwayat Pengajuan Surat Izin Praktik (SIP)</h4>
        <p class="text-secondary fs-sm mb-0">Pantau status telaah dan verifikasi berkas perpanjangan SIP Anda oleh Subkomite Kredensial Komite Medik</p>
    </div>
    <div>
        @if($isStrValid)
            <a href="{{ route('dokter.pengajuan.create') }}" class="btn btn-teal text-white btn-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <span>Unggah Perpanjangan SIP Baru</span>
            </a>
        @else
            <button class="btn btn-secondary btn-sm d-flex align-items-center gap-2" disabled title="Modul pengurusan SIP terkunci (Syarat mutlak STR belum tervalidasi)">
                <i class="fa-solid fa-lock"></i>
                <span>Unggah SIP Baru (Terkunci)</span>
            </button>
        @endif
    </div>
</div>

<!-- Fondasi Hukum & Hard Constraint Alert -->
@if(!$isStrValid)
    <div class="card border-warning bg-warning-subtle mb-4 shadow-xs">
        <div class="card-body p-4 d-flex align-items-start gap-3">
            <div class="text-warning-emphasis fs-2">
                <i class="fa-solid fa-lock"></i>
            </div>
            <div>
                <h5 class="fw-bold text-warning-emphasis mb-1">Modul Pengurusan SIP Terkunci (Syarat Mutlak Fondasi Hukum)</h5>
                <p class="fs-sm text-dark mb-2">
                    Berdasarkan tata kelola kredensial klinis RSUD dr. H. Moch. Ansari Saleh, pengurusan dan perpanjangan SIP <strong>hanya dapat aktif</strong> setelah data Master STR (16 digit) tervalidasi dengan pangkalan data <strong>SatuSehat SDMK Kemenkes RI</strong> dan berstatus <strong>terkunci (read-only)</strong> oleh Subkomite Kredensial.
                </p>
                <div class="p-2 bg-white rounded border d-inline-block fs-xs">
                    <span class="text-muted">Status STR Anda saat ini:</span>
                    {!! $dokter->status_verifikasi_str_badge !!}
                    @if($dokter->nomor_str)
                        <span class="ms-2 font-monospace fw-semibold text-dark">No. STR: {{ $dokter->nomor_str }}</span>
                    @else
                        <span class="ms-2 text-danger fw-semibold">Nomor STR belum diinput oleh Admin SDM</span>
                    @endif
                </div>
                <div class="mt-2 text-muted fs-xs">
                    <i class="fa-solid fa-circle-info me-1"></i> Jika Anda baru menyerahkan berkas e-STR, silakan menunggu Subkomite Kredensial Komite Medik memvalidasi data Anda.
                </div>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-success border-success bg-light-teal d-flex align-items-center justify-content-between p-3 mb-4 shadow-2xs">
        <div class="d-flex align-items-center gap-3">
            <i class="fa-solid fa-shield-check text-success fs-3"></i>
            <div>
                <div class="fw-bold text-dark fs-sm">Fondasi Hukum Terpenuhi & STR Terkunci (Read-Only)</div>
                <div class="fs-xs text-muted">
                    No. Registrasi STR: <span class="font-monospace fw-bold text-dark">{{ chunk_split($dokter->nomor_str, 4, ' ') }}</span> &bullet;
                    Tempat Praktik Terdaftar: <strong class="text-teal">{{ $dokter->tempat_praktik_label }}</strong> &bullet;
                    Terverifikasi via SatuSehat SDMK
                </div>
            </div>
        </div>
        <span class="badge bg-success text-white"><i class="fa-solid fa-lock me-1"></i> STR Tervalidasi</span>
    </div>
@endif

<div class="card shadow-xs">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th>Tgl Pengajuan</th>
                    <th>Nomor SIP</th>
                    <th>Tempat Praktik</th>
                    <th>Masa Berlaku</th>
                    <th>Status Verifikasi</th>
                    <th>Catatan Telaah Komite</th>
                    <th>Berkas Pindaian</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sipList as $item)
                    <tr>
                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        <td class="fw-semibold"><span class="font-monospace fs-xs">{{ $item->nomor_sip }}</span></td>
                        <td>SIP Ke-{{ $item->tempat_praktik_ke }}</td>
                        <td>
                            <div>{{ $item->tanggal_terbit ? $item->tanggal_terbit->format('d/m/Y') : '-' }} s.d</div>
                            <small class="fw-bold text-dark">{{ $item->tanggal_kadaluarsa ? $item->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</small>
                        </td>
                        <td>
                            @if($item->status_verifikasi === 'disetujui')
                                <span class="badge bg-success-subtle text-success border border-success"><i class="fa-solid fa-circle-check me-1"></i> Disetujui</span>
                            @elseif($item->status_verifikasi === 'ditolak')
                                <span class="badge bg-danger-subtle text-danger border border-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Ditolak</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning"><i class="fa-solid fa-clock me-1"></i> Menunggu Telaah</span>
                            @endif
                        </td>
                        <td>
                            @if($item->catatan_verifikasi)
                                <span class="text-secondary fs-xs fst-italic">{{ $item->catatan_verifikasi }}</span>
                            @else
                                <span class="text-muted fs-xs">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->file_sip)
                                <a href="{{ asset('storage/' . $item->file_sip) }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                    <i class="fa-solid fa-file-pdf me-1"></i> Unduh File
                                </a>
                            @else
                                <span class="text-muted fs-xs">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-folder-open fs-2 mb-2 d-block"></i>
                            Belum ada riwayat dokumen SIP yang diajukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sipList->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $sipList->links() }}
        </div>
    @endif
</div>
@endsection
