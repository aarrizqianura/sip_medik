@extends('layouts.app')

@section('title', 'Matriks Monitoring & Blast WhatsApp')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Matriks Monitoring & Blast WhatsApp Reminder</h4>
        <p class="text-secondary fs-sm mb-0">Engine early warning masa berlaku SIP dokter dengan fasilitas pengiriman notifikasi pengingat WhatsApp manual dan masal (blast)</p>
    </div>
</div>

<!-- Filter Box -->
<div class="card shadow-xs mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('sdm.reminder.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari nama dokter atau nomor SIP..." value="{{ request('cari') }}">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status Kritis & Due Soon --</option>
                    <option value="due_soon" {{ request('status') == 'due_soon' ? 'selected' : '' }}>Due Soon (31 - 90 Hari)</option>
                    <option value="due" {{ request('status') == 'due' ? 'selected' : '' }}>Due / Kritis (≤ 30 Hari)</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue (Kedaluwarsa)</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif (> 90 Hari)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100">
                    <i class="fa-solid fa-filter me-1"></i> Filter Matriks
                </button>
                <a href="{{ route('sdm.reminder.index') }}" class="btn btn-light btn-sm border" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Blast Form & Table -->
<form action="{{ route('sdm.reminder.blast') }}" method="POST" id="blastForm">
    @csrf

    <div class="card shadow-xs">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="selectAllCheckbox">
                    <label class="form-check-label fs-sm fw-semibold text-dark" for="selectAllCheckbox">
                        Pilih Semua Dokter Terpilih
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-success btn-sm d-flex align-items-center gap-2" onclick="return confirm('Kirimkan pesan pengingat WhatsApp ke seluruh dokter yang dicentang?')">
                <i class="fa-brands fa-whatsapp fs-6"></i>
                <span>Kirim Blast WhatsApp</span>
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-sm">
                <thead class="table-light">
                    <tr>
                        <th width="40" class="text-center">#</th>
                        <th>Dokter & KSM</th>
                        <th>No. WhatsApp</th>
                        <th>Nomor SIP</th>
                        <th>Jatuh Tempo</th>
                        <th>Selisih Hari</th>
                        <th>Status</th>
                        <th class="text-end">Aksi Mandiri</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dokumenList as $sip)
                        @php $sisa = $sip->hitungSisaHari(); @endphp
                        <tr>
                            <td class="text-center">
                                <input class="form-check-input row-checkbox" type="checkbox" name="sip_ids[]" value="{{ $sip->id }}">
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $sip->dokter->user->nama ?? '-' }}</div>
                                <small class="text-muted">KSM: {{ $sip->dokter->ksm->nama_ksm ?? '-' }}</small>
                            </td>
                            <td>
                                @if($sip->dokter?->user?->no_wa)
                                    <span class="text-success font-monospace fs-xs"><i class="fa-brands fa-whatsapp me-1"></i>{{ $sip->dokter->user->no_wa }}</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Belum ada no WA</span>
                                @endif
                            </td>
                            <td>
                                <span class="font-monospace fs-xs">{{ $sip->nomor_sip }}</span>
                                <div class="text-muted fs-xs">Praktik ke-{{ $sip->tempat_praktik_ke }}</div>
                            </td>
                            <td>{{ $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</td>
                            <td>
                                <span class="countdown-badge {{ $sisa < 0 ? 'bg-danger text-white' : ($sisa <= 30 ? 'bg-warning text-dark' : 'bg-info text-white') }}">
                                    {{ $sisa < 0 ? 'Kedaluwarsa (' . abs($sisa) . ' hr)' : $sisa . ' Hari' }}
                                </span>
                            </td>
                            <td>
                                <span class="status-pill status-{{ $sip->status }} fs-xs">
                                    {{ $sip->status_label }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalSend{{ $sip->id }}" title="Kirim Pesan WA Personal">
                                    <i class="fa-brands fa-whatsapp me-1"></i> Kirim
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Tidak ada dokumen SIP yang perlu tindakan pengingat saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($dokumenList->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $dokumenList->links() }}
            </div>
        @endif
    </div>
</form>

<!-- Modal Individual Dispatch -->
@foreach($dokumenList as $sip)
    <div class="modal fade" id="modalSend{{ $sip->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('sdm.reminder.kirim', $sip->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Kirim Pengingat WhatsApp</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fs-xs fw-semibold">Penerima:</label>
                            <input type="text" class="form-control form-control-sm" value="{{ $sip->dokter->user->nama ?? '' }} ({{ $sip->dokter->user->no_wa ?? '' }})" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-xs fw-semibold">Pesan Tambahan Khusus (Opsional):</label>
                            <textarea name="pesan_khusus" rows="4" class="form-control form-control-sm" placeholder="Kosongkan untuk menggunakan template resmi SIM-SIP..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fa-brands fa-whatsapp me-1"></i> Kirim WhatsApp Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@push('scripts')
<script>
    document.getElementById('selectAllCheckbox')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>
@endpush
@endsection
