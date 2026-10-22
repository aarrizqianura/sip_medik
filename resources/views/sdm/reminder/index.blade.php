@extends('layouts.app')

@section('title', 'Tahap 5: Early Warning Engine & Eskalasi Berjenjang')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-dark text-teal px-2 py-1 fs-xs fw-semibold border border-teal">Tahap 5</span>
            <span class="badge bg-success-subtle text-success border border-success px-2 py-1 fs-xs">
                <i class="fa-solid fa-clock me-1"></i> Cron Job: 00:00 WITA
            </span>
        </div>
        <h4 class="fw-bold text-dark mb-1">Pemantauan Berjalan & Eskalasi Berjenjang (Early Warning Engine)</h4>
        <p class="text-secondary fs-sm mb-0">Mesin penjadwal otomatis mengevaluasi sisa hari masa berlaku SIP dokter ($sisa\_hari = tanggal\_kadaluarsa - hari\_ini$) dan mengirim notifikasi WhatsApp berjenjang ke Dokter & Tim SDM.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <form action="{{ route('sdm.reminder.run_engine') }}" method="POST" onsubmit="return confirm('Jalankan evaluasi Early Warning Engine sekarang? Sistem akan memproses seluruh SIP dan mengirimkan notifikasi eskalasi berjenjang WhatsApp.')">
            @csrf
            <button type="submit" class="btn btn-teal text-white btn-sm d-flex align-items-center gap-2 shadow-sm">
                <i class="fa-solid fa-bolt"></i>
                <span>Jalankan Engine Sekarang</span>
            </button>
        </form>
        <a href="{{ route('sdm.laporan.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-file-pdf"></i>
            <span>Cetak Rekapitulasi</span>
        </a>
    </div>
</div>

<!-- 4 Tingkatan Eskalasi Berjenjang (Early Warning Engine Metrics) -->
<div class="row g-3 mb-4">
    <!-- Tier 1: Aman > 90 Hari -->
    <div class="col-sm-6 col-xl">
        <a href="{{ route('sdm.reminder.index', ['status' => 'aktif']) }}" class="text-decoration-none">
            <div class="card h-100 border-start border-4 border-success shadow-xs {{ request('status') === 'aktif' ? 'ring-2 ring-success bg-light-teal' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary fs-xs fw-semibold text-uppercase">Tier 1: Sisa >90 Hari</span>
                        <span class="status-pill status-aktif fs-xs"><i class="fa-solid fa-circle-check me-1"></i> Aktif</span>
                    </div>
                    <div class="fs-3 fw-bold text-success">{{ $ringkasan['aktif'] ?? 0 }}</div>
                    <div class="text-muted fs-xs mt-1">Status aman; sistem tidak mengirim pesan.</div>
                </div>
            </div>
        </a>
    </div>

    <!-- Tier 2: H-90 s.d. H-60 (due_soon - Kuning) -->
    <div class="col-sm-6 col-xl">
        <a href="{{ route('sdm.reminder.index', ['status' => 'due_soon']) }}" class="text-decoration-none">
            <div class="card h-100 border-start border-4 border-warning shadow-xs {{ request('status') === 'due_soon' ? 'ring-2 ring-warning bg-warning-subtle' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary fs-xs fw-semibold text-uppercase">Tier 2: H-90 s.d H-60</span>
                        <span class="status-pill status-due_soon fs-xs"><i class="fa-solid fa-clock me-1"></i> Due Soon</span>
                    </div>
                    <div class="fs-3 fw-bold text-warning-emphasis">{{ $ringkasan['due_soon'] ?? 0 }}</div>
                    <div class="text-muted fs-xs mt-1">Pengingat pertama SKP SatuSehat ke nomor dokter.</div>
                </div>
            </div>
        </a>
    </div>

    <!-- Tier 3: H-30 (due - Oranye) -->
    <div class="col-sm-6 col-xl">
        <a href="{{ route('sdm.reminder.index', ['status' => 'due']) }}" class="text-decoration-none">
            <div class="card h-100 border-start border-4 shadow-xs {{ request('status') === 'due' ? 'ring-2 ring-orange' : '' }}" style="border-left-color: #f97316 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary fs-xs fw-semibold text-uppercase">Tier 3: H-30 Hari</span>
                        <span class="status-pill status-due fs-xs"><i class="fa-solid fa-triangle-exclamation me-1"></i> Mendesak</span>
                    </div>
                    <div class="fs-3 fw-bold text-orange" style="color: #ea580c !important;">{{ $ringkasan['due'] ?? 0 }}</div>
                    <div class="text-muted fs-xs mt-1">Peringatan mendesak ke dokter & salinan rekap ke SDM.</div>
                </div>
            </div>
        </a>
    </div>

    <!-- Tier 4: H-7 (critical - Oranye Tua) -->
    <div class="col-sm-6 col-xl">
        <a href="{{ route('sdm.reminder.index', ['status' => 'critical']) }}" class="text-decoration-none">
            <div class="card h-100 border-start border-4 shadow-xs {{ request('status') === 'critical' ? 'ring-2 ring-danger bg-orange-subtle' : '' }}" style="border-left-color: #c2410c !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary fs-xs fw-semibold text-uppercase">Tier 4: H-7 Hari</span>
                        <span class="status-pill status-critical fs-xs"><i class="fa-solid fa-radiation me-1"></i> Kritis</span>
                    </div>
                    <div class="fs-3 fw-bold" style="color: #c2410c !important;">{{ $ringkasan['critical'] ?? 0 }}</div>
                    <div class="text-muted fs-xs mt-1">Eskalasi berjenjang kritis ke Dokter & Staf SDM.</div>
                </div>
            </div>
        </a>
    </div>

    <!-- Overdue: Kedaluwarsa -->
    <div class="col-sm-6 col-xl">
        <a href="{{ route('sdm.reminder.index', ['status' => 'overdue']) }}" class="text-decoration-none">
            <div class="card h-100 border-start border-4 border-danger shadow-xs {{ request('status') === 'overdue' ? 'ring-2 ring-danger bg-danger-subtle' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary fs-xs fw-semibold text-uppercase">Overdue</span>
                        <span class="status-pill status-overdue fs-xs"><i class="fa-solid fa-ban me-1"></i> Habis</span>
                    </div>
                    <div class="fs-3 fw-bold text-danger">{{ $ringkasan['overdue'] ?? 0 }}</div>
                    <div class="text-muted fs-xs mt-1">Praktik klinis wajib distop sementara.</div>
                </div>
            </div>
        </a>
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
                    <option value="">-- Semua Status yang Butuh Perhatian (Kritis, Due, Due Soon) --</option>
                    <option value="critical" {{ request('status') == 'critical' ? 'selected' : '' }}>🚨 H-7: Kritis (Oranye Tua / Eskalasi Berjenjang)</option>
                    <option value="due" {{ request('status') == 'due' ? 'selected' : '' }}>⚠️ H-30: Peringatan Mendesak (Oranye)</option>
                    <option value="due_soon" {{ request('status') == 'due_soon' ? 'selected' : '' }}>📌 H-90 s.d H-60: Due Soon (Kuning)</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>⛔ Overdue: Kedaluwarsa</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>✅ Sisa >90 Hari: Aktif & Aman (Hijau)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-teal text-white btn-sm w-100">
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

    <div class="card shadow-xs mb-4">
        <div class="card-header bg-white py-3 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
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
                <span>Kirim Blast WhatsApp Terpilih</span>
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-sm">
                <thead class="table-light">
                    <tr>
                        <th width="40" class="text-center">#</th>
                        <th>Dokter & KSM</th>
                        <th>No. WhatsApp Dokter</th>
                        <th>Nomor SIP & Tempat</th>
                        <th>Jatuh Tempo</th>
                        <th>Selisih Hari</th>
                        <th>Status & Eskalasi</th>
                        <th>Riwayat Notifikasi</th>
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
                                <span class="font-monospace fs-xs fw-semibold">{{ $sip->nomor_sip }}</span>
                                <div class="text-muted fs-xs">Praktik ke-{{ $sip->tempat_praktik_ke }} • {{ $sip->fasyankes ?? 'RSUD Ansari Saleh' }}</div>
                            </td>
                            <td>{{ $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if($sisa < 0)
                                    <span class="countdown-badge bg-danger text-white">
                                        <i class="fa-solid fa-ban me-1"></i> Kedaluwarsa ({{ abs($sisa) }} hr)
                                    </span>
                                @elseif($sisa <= 7)
                                    <span class="countdown-badge bg-critical text-white" style="background-color: #ea580c !important;">
                                        <i class="fa-solid fa-radiation me-1"></i> H-{{ $sisa }} Kritis
                                    </span>
                                @elseif($sisa <= 30)
                                    <span class="countdown-badge bg-due text-white" style="background-color: #f97316 !important;">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> H-{{ $sisa }} Mendesak
                                    </span>
                                @elseif($sisa <= 90)
                                    <span class="countdown-badge bg-due-soon text-dark" style="background-color: #fef08a !important; color: #854d0e !important;">
                                        <i class="fa-solid fa-clock me-1"></i> H-{{ $sisa }} Hari
                                    </span>
                                @else
                                    <span class="countdown-badge bg-safe text-white" style="background-color: #10b981 !important;">
                                        <i class="fa-solid fa-circle-check me-1"></i> {{ $sisa }} Hari
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($sip->status === 'critical')
                                    <span class="status-pill status-critical fs-xs">
                                        <i class="fa-solid fa-radiation me-1"></i> Eskalasi Kritis (H-7)
                                    </span>
                                @elseif($sip->status === 'due')
                                    <span class="status-pill status-due fs-xs">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Peringatan Mendesak (H-30)
                                    </span>
                                @elseif($sip->status === 'due_soon')
                                    <span class="status-pill status-due_soon fs-xs">
                                        <i class="fa-solid fa-clock me-1"></i> Due Soon (H-90 s.d H-60)
                                    </span>
                                @elseif($sip->status === 'overdue')
                                    <span class="status-pill status-overdue fs-xs">
                                        <i class="fa-solid fa-ban me-1"></i> Kedaluwarsa
                                    </span>
                                @else
                                    <span class="status-pill status-aktif fs-xs">
                                        <i class="fa-solid fa-circle-check me-1"></i> Aktif (>90 Hari)
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="fs-xs">
                                    @if($sip->terakhir_notifikasi_dokter_at)
                                        <div class="text-success" title="Waktu kirim WA ke dokter">
                                            <i class="fa-solid fa-user-doctor me-1"></i> {{ $sip->terakhir_notifikasi_dokter_at->format('d/m/Y H:i') }}
                                        </div>
                                    @else
                                        <span class="text-muted">Dokter: Belum</span>
                                    @endif

                                    @if($sip->terakhir_eskalasi_sdm_at)
                                        <div class="text-danger fw-semibold" title="Waktu eskalasi ke SDM">
                                            <i class="fa-solid fa-users me-1"></i> SDM: {{ $sip->terakhir_eskalasi_sdm_at->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalSend{{ $sip->id }}" title="Kirim Pesan WA Personal">
                                    <i class="fa-brands fa-whatsapp me-1"></i> Kirim WA
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-circle-check fs-1 text-success mb-2 d-block"></i>
                                <div>Tidak ada dokumen SIP yang memerlukan tindakan pengingat saat ini.</div>
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

<!-- Feed Riwayat Log Eskalasi & Notifikasi Terakhir -->
@if(isset($riwayatEskalasi) && $riwayatEskalasi->isNotEmpty())
    <div class="card shadow-xs">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-paper-plane text-teal"></i>
                <span>Log Eskalasi Notifikasi WhatsApp Terakhir</span>
            </h6>
            <span class="badge bg-light text-secondary border fs-xs">8 Pengiriman Terbaru</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 fs-xs">
                <thead class="table-light">
                    <tr>
                        <th>Waktu</th>
                        <th>Target Penerima</th>
                        <th>Tipe Penerima</th>
                        <th>Tingkat Eskalasi</th>
                        <th>Nomor SIP & Dokter Terkait</th>
                        <th>Status Gateway</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($riwayatEskalasi as $notif)
                        <tr>
                            <td class="text-muted">{{ $notif->created_at->translatedFormat('d M Y H:i:s') }}</td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $notif->user?->nama ?? '-' }}</span>
                                <span class="text-muted">({{ $notif->user?->no_wa ?? '-' }})</span>
                            </td>
                            <td>
                                @if($notif->tipe_penerima === 'staf_sdm')
                                    <span class="badge bg-danger-subtle text-danger border border-danger">Staf Kepegawaian SDM</span>
                                @elseif($notif->tipe_penerima === 'admin_sdm')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning">Admin SDM (Salinan)</span>
                                @else
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info">Dokter Bersangkutan</span>
                                @endif
                            </td>
                            <td>
                                @if($notif->tingkat_eskalasi === 'critical')
                                    <span class="status-pill status-critical fs-xs">H-7 Kritis</span>
                                @elseif($notif->tingkat_eskalasi === 'due')
                                    <span class="status-pill status-due fs-xs">H-30 Mendesak</span>
                                @elseif($notif->tingkat_eskalasi === 'due_soon')
                                    <span class="status-pill status-due_soon fs-xs">H90-H60 Pengingat 1</span>
                                @else
                                    <span class="badge bg-light border text-dark">{{ ucfirst($notif->tingkat_eskalasi ?? 'Umum') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($notif->dokumenSip)
                                    <span class="font-monospace fw-semibold">{{ $notif->dokumenSip->nomor_sip }}</span>
                                    <span class="text-muted">({{ $notif->dokumenSip->dokter?->user?->nama ?? '-' }})</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($notif->status_terkirim)
                                    <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> Terkirim</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger"><i class="fa-solid fa-xmark me-1"></i> Gagal</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- Modal Individual Dispatch -->
@foreach($dokumenList as $sip)
    <div class="modal fade" id="modalSend{{ $sip->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('sdm.reminder.kirim', $sip->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Kirim Pengingat WhatsApp Manual</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fs-xs fw-semibold">Penerima Pesan:</label>
                            <input type="text" class="form-control form-control-sm" value="{{ $sip->dokter->user->nama ?? '' }} ({{ $sip->dokter->user->no_wa ?? '' }})" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-xs fw-semibold">Tingkat Eskalasi Otomatis Terdeteksi:</label>
                            <input type="text" class="form-control form-control-sm fw-semibold" value="{{ $sip->status_label }} (Sisa {{ $sip->hitungSisaHari() }} Hari)" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-xs fw-semibold">Pesan Tambahan Khusus (Opsional):</label>
                            <textarea name="pesan_khusus" rows="4" class="form-control form-control-sm" placeholder="Kosongkan untuk menggunakan template resmi standar Early Warning Engine (sesuai status jatuh tempo & SKP SatuSehat)..."></textarea>
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
