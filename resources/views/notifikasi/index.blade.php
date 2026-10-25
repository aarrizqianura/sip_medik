@extends('layouts.app')

@section('title', 'Kotak Masuk Notifikasi & Log Pengingat')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Kotak Masuk Notifikasi & Pengingat</h4>
        <p class="text-secondary fs-sm mb-0">Daftar ramping pemberitahuan otomatis masa berlaku SIP, SPK/RKK, dan log pesan WhatsApp</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form action="{{ route('notifikasi.baca_semua') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-check-double"></i>
                <span>Tandai Semua Sudah Dibaca</span>
            </button>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="fw-bold text-dark fs-sm">
            <i class="fa-solid fa-bell text-teal me-2"></i>Feed Notifikasi Resmi
        </div>
        <small class="text-muted fs-xs">Klik baris pesan untuk membuka isi lengkap WhatsApp / Sistem</small>
    </div>

    <div class="card-body p-0">
        <div class="accordion accordion-flush" id="notifAccordion">
            @forelse($notifikasi as $idx => $notif)
                @php
                    $isWa = $notif->kanal === 'whatsapp';
                    $pesan = $notif->pesan;
                    
                    // Ekstraksi judul ringkas dari pesan
                    $judul = 'Pemberitahuan Sistem';
                    if (str_contains($pesan, 'H-7') || str_contains($pesan, 'KRITIS')) {
                        $judul = 'Peringatan SIP H-7: Masa Kritis (Segera Perpanjang)';
                        $badgeClass = 'bg-danger text-white';
                    } elseif (str_contains($pesan, 'H-30') || str_contains($pesan, 'MENDESAK')) {
                        $judul = 'Peringatan SIP H-30: Masa Berlaku Segera Berakhir';
                        $badgeClass = 'bg-orange text-white';
                    } elseif (str_contains($pesan, 'H-90') || str_contains($pesan, 'DUE SOON')) {
                        $judul = 'Pengingat SIP (H-90): Persiapan Prasyarat SKP SatuSehat';
                        $badgeClass = 'bg-warning text-dark';
                    } elseif (str_contains($pesan, 'OVERDUE') || str_contains($pesan, 'DITANGGUHKAN') || str_contains($pesan, 'Kedaluwarsa')) {
                        $judul = 'Eskalasi Overdue: SIP Kedaluwarsa & Wewenang Klinis Ditangguhkan';
                        $badgeClass = 'bg-danger text-white';
                    } elseif (str_contains($pesan, 'DISETUJUI') || str_contains($pesan, 'Disahkan')) {
                        $judul = 'Verifikasi SIP: Berkas Berhasil Disahkan Komite Medik';
                        $badgeClass = 'bg-success text-white';
                    } elseif (str_contains($pesan, 'DITOLAK')) {
                        $judul = 'Verifikasi SIP: Berkas Ditolak / Perlu Perbaikan';
                        $badgeClass = 'bg-danger text-white';
                    } elseif (str_contains($pesan, 'STR')) {
                        $judul = 'Validasi STR: Status Perekaman SatuSehat SDMK';
                        $badgeClass = 'bg-primary text-white';
                    } else {
                        // Ambil baris pertama atau 50 karakter pertama
                        $firstLine = strtok($pesan, "\n");
                        $cleanLine = trim(str_replace(['*', '#', '_'], '', $firstLine));
                        $judul = Str::limit($cleanLine ?: 'Pemberitahuan Perizinan Medis', 65);
                        $badgeClass = 'bg-teal text-white';
                    }
                @endphp

                <div class="accordion-item {{ !$notif->status_baca ? 'bg-light-teal' : '' }}">
                    <h2 class="accordion-header" id="heading{{ $notif->id }}">
                        <button class="accordion-button collapsed py-3 px-4 d-flex align-items-center gap-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $notif->id }}" aria-expanded="false" aria-controls="collapse{{ $notif->id }}">
                            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-white {{ $isWa ? 'bg-success' : 'bg-primary' }}" style="width: 32px; height: 32px;">
                                <i class="fa-{{ $isWa ? 'brands fa-whatsapp' : 'solid fa-bell' }} fs-xs"></i>
                            </div>

                            <div class="flex-grow-1 overflow-hidden pe-3">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <span class="badge {{ $badgeClass }} fs-3xs px-2 py-0.5">
                                        {{ $judul }}
                                    </span>
                                    <span class="badge {{ $isWa ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }} fs-3xs">
                                        {{ strtoupper($notif->kanal) }}
                                    </span>
                                    @if(in_array(auth()->user()->role, ['super_admin', 'admin_sdm']) && $notif->user)
                                        <span class="text-secondary fs-3xs fw-semibold">&bull; Ke: {{ $notif->user->nama }}</span>
                                    @endif
                                </div>
                                <div class="text-dark fs-sm text-truncate fw-semibold">
                                    {{ Str::limit(strip_tags(str_replace(['*', '_'], '', $pesan)), 90) }}
                                </div>
                            </div>

                            <div class="text-end text-nowrap d-none d-sm-block me-3">
                                <small class="text-muted fs-3xs d-block">{{ $notif->created_at->diffForHumans() }}</small>
                                <span class="fs-3xs text-secondary font-monospace">{{ $notif->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse{{ $notif->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $notif->id }}" data-bs-parent="#notifAccordion">
                        <div class="accordion-body px-4 py-3 bg-white border-top">
                            <div class="p-3 rounded-3 border {{ $isWa ? 'bg-light-subtle' : 'bg-light' }}">
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-{{ $isWa ? 'brands fa-whatsapp text-success' : 'solid fa-bell text-primary' }}"></i>
                                        <strong class="fs-xs text-dark">{{ $isWa ? 'Transkrip Pesan WhatsApp Otomatis' : 'Detail Notifikasi Sistem' }}</strong>
                                    </div>
                                    @if(!$notif->status_baca)
                                        <form action="{{ route('notifikasi.baca', $notif->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-2xs btn-outline-teal">
                                                <i class="fa-solid fa-check me-1"></i> Tandai Sudah Dibaca
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-light text-muted border fs-3xs">Telah Dibaca</span>
                                    @endif
                                </div>
                                <div class="text-dark fs-sm font-monospace" style="white-space: pre-wrap; line-height: 1.6;">{{ $pesan }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fa-regular fa-bell-slash fs-2 mb-2"></i>
                    <div>Kotak masuk notifikasi kosong.</div>
                </div>
            @endforelse
        </div>
    </div>

    @if($notifikasi->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $notifikasi->links() }}
        </div>
    @endif
</div>
@endsection
