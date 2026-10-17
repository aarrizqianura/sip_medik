@extends('layouts.app')

@section('title', 'Pengajuan SIP Saya')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Riwayat Pengajuan Surat Izin Praktik (SIP)</h4>
        <p class="text-secondary fs-sm mb-0">Pantau status telaah dan verifikasi berkas perpanjangan SIP Anda oleh Subkomite Kredensial Komite Medik</p>
    </div>
    <div>
        <a href="{{ route('dokter.pengajuan.create') }}" class="btn btn-teal text-white btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <span>Unggah Perpanjangan SIP Baru</span>
        </a>
    </div>
</div>

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
                                <a href="{{ asset('storage/' . $item->file_sip) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Lihat Berkas">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                            @else
                                <span class="text-muted fs-xs">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            Anda belum pernah mengajukan atau mengunggah berkas SIP.
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
