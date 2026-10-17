@extends('layouts.app')

@section('title', 'Kotak Notifikasi & Log Pengingat')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Kotak Masuk Notifikasi & Log Pengingat</h4>
        <p class="text-secondary fs-sm mb-0">Riwayat pemberitahuan otomatis pengingat masa berlaku SIP, SPK/RKK, dan pengesahan berkas</p>
    </div>
    <div>
        <form action="{{ route('notifikasi.baca_semua') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-check-double"></i>
                <span>Tandai Semua Sudah Dibaca</span>
            </button>
        </form>
    </div>
</div>

<div class="card shadow-xs">
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse($notifikasi as $notif)
                <div class="list-group-item p-3 {{ !$notif->status_baca ? 'bg-teal-subtle bg-opacity-25' : '' }}">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="p-2 rounded-circle {{ $notif->kanal === 'whatsapp' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }}">
                                <i class="fa-{{ $notif->kanal === 'whatsapp' ? 'brands fa-whatsapp' : 'solid fa-bell' }} fs-5"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge {{ $notif->kanal === 'whatsapp' ? 'bg-success text-white' : 'bg-primary text-white' }} fs-xs">
                                        {{ strtoupper($notif->kanal) }}
                                    </span>
                                    @if(in_array(auth()->user()->role, ['super_admin', 'admin_sdm']))
                                        <span class="fw-bold text-dark fs-sm">Ke: {{ $notif->user->nama ?? '-' }}</span>
                                    @endif
                                    <span class="text-muted fs-xs">&bullet; {{ $notif->created_at->diffForHumans() }} ({{ $notif->created_at->format('d/m/Y H:i') }})</span>
                                </div>
                                <div class="text-dark fs-sm" style="white-space: pre-line;">{{ $notif->pesan }}</div>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-1">
                            @if(!$notif->status_baca)
                                <form action="{{ route('notifikasi.baca', $notif->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-link btn-sm text-teal p-0 fs-xs text-decoration-none">
                                        <i class="fa-solid fa-check me-1"></i> Tandai Dibaca
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-light text-muted border fs-xs">Sudah Dibaca</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fa-regular fa-bell-slash fs-2 mb-2"></i>
                    <div>Belum ada riwayat notifikasi di kotak masuk.</div>
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
