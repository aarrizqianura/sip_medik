@extends('layouts.app')

@section('title', 'Riwayat Dokumen & Berkas SIP Saya')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
            </a>
            <h4 class="fw-bold text-dark mb-0">Riwayat & Arsip Berkas SIP Saya</h4>
        </div>
        <p class="text-secondary fs-sm mb-0">Log arsip dokumen perizinan, status verifikasi Subkomite Kredensial, dan unduhan pindaian PDF</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('dokter.pengajuan.create') }}" class="btn btn-teal text-white btn-sm shadow-sm d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>+ Ajukan Perpanjangan SIP</span>
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="fw-bold text-dark fs-sm">
            <i class="fa-solid fa-folder-closed text-teal me-2"></i>Daftar Berkas Perpanjangan & Riwayat Pengajuan
        </div>
        <span class="badge bg-light text-secondary border">Total {{ $sipList->total() }} Berkas</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th style="width: 130px;">Tanggal</th>
                    <th>Nomor SIP</th>
                    <th>Masa Berlaku</th>
                    <th>Status Verifikasi</th>
                    <th class="text-end" style="width: 160px;">Aksi Unduh PDF</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sipList as $item)
                    @php
                        $sisaHari = $item->hitungSisaHari();
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold text-dark">{{ $item->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted fs-xs">{{ $item->created_at->format('H:i') }} WITA</small>
                        </td>
                        <td>
                            <div class="font-monospace fw-bold text-dark fs-sm">{{ $item->nomor_sip }}</div>
                            <div class="text-secondary fs-xs">
                                <span>{{ $item->fasyankes ?: 'RSUD Ansari Saleh' }}</span> &bullet;
                                <span class="badge bg-light text-dark border fs-3xs">Praktik ke-{{ $item->tempat_praktik_ke }}</span>
                            </div>
                        </td>
                        <td>
                            @if($item->tanggal_kadaluarsa)
                                <div class="text-dark fs-xs">
                                    {{ $item->tanggal_terbit ? $item->tanggal_terbit->format('d/m/Y') : '-' }} s/d 
                                    <strong>{{ $item->tanggal_kadaluarsa->format('d/m/Y') }}</strong>
                                </div>
                                @if($sisaHari < 0)
                                    <span class="badge bg-danger text-white fs-3xs">Kedaluwarsa ({{ abs($sisaHari) }} hari)</span>
                                @elseif($sisaHari <= 30)
                                    <span class="badge bg-danger text-white fs-3xs">Tersisa {{ $sisaHari }} hari</span>
                                @elseif($sisaHari <= 90)
                                    <span class="badge bg-warning text-dark fs-3xs">Tersisa {{ $sisaHari }} hari</span>
                                @else
                                    <span class="badge bg-success-subtle text-success fs-3xs">Berlaku Aktif</span>
                                @endif
                            @else
                                <span class="text-muted fs-xs">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->status_verifikasi === 'disetujui')
                                <span class="badge bg-success text-white py-1 px-2">
                                    <i class="fa-solid fa-circle-check me-1"></i> Disetujui
                                </span>
                            @elseif($item->status_verifikasi === 'ditolak')
                                <span class="badge bg-danger text-white py-1 px-2">
                                    <i class="fa-solid fa-circle-xmark me-1"></i> Ditolak
                                </span>
                                @if($item->catatan_verifikasi)
                                    <div class="mt-1">
                                        <button type="button" class="btn btn-2xs btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalCatatan{{ $item->id }}">
                                            <i class="fa-solid fa-info-circle me-1"></i> Catatan Penolakan
                                        </button>

                                        <!-- Modal Catatan -->
                                        <div class="modal fade" id="modalCatatan{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white py-2">
                                                        <h6 class="modal-title fs-sm fw-bold">
                                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Catatan Evaluasi Penolakan SIP
                                                        </h6>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body fs-sm">
                                                        <p class="mb-2 text-muted">Nomor SIP: <strong>{{ $item->nomor_sip }}</strong></p>
                                                        <div class="alert alert-danger mb-0">
                                                            {{ $item->catatan_verifikasi }}
                                                        </div>
                                                        <small class="text-muted d-block mt-2">Ditinjau oleh: {{ $item->verifikator->nama ?? 'Subkomite Kredensial' }}</small>
                                                    </div>
                                                    <div class="modal-footer py-2">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                                                        <a href="{{ route('dokter.pengajuan.create') }}" class="btn btn-teal text-white btn-sm">
                                                            Unggah Revisi Baru
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <span class="badge bg-warning text-dark py-1 px-2">
                                    <i class="fa-solid fa-clock me-1"></i> Menunggu Telaah
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                @if($item->file_sip)
                                    <a href="{{ route('sip.preview_file', $item->id) }}" target="_blank" class="btn btn-teal text-white btn-sm" title="Pratinjau / Unduh Berkas PDF">
                                        <i class="fa-solid fa-file-pdf me-1"></i> Unduh PDF
                                    </a>
                                @else
                                    <button class="btn btn-secondary btn-sm" disabled>
                                        <i class="fa-solid fa-file-excel me-1"></i> Nihil
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fa-regular fa-folder-open fs-2 mb-2"></i>
                            <div>Belum ada riwayat dokumen pengajuan SIP.</div>
                            <div class="mt-2">
                                <a href="{{ route('dokter.pengajuan.create') }}" class="btn btn-teal text-white btn-sm">
                                    <i class="fa-solid fa-plus me-1"></i> Ajukan Permohonan SIP
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sipList->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $sipList->links() }}
        </div>
    @endif
</div>
@endsection
