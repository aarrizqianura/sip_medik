@extends('layouts.app')

@section('title', 'Detail Profil Dokter')

@section('content')
<div class="mb-4">
    <a href="{{ route('sdm.dokter.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Dokter
    </a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">{{ $dokter->user->nama }}</h4>
            <p class="text-secondary fs-sm mb-0">
                NIP/NRP: <strong>{{ $dokter->nip_nrp }}</strong> &bullet; 
                KSM: <strong>{{ $dokter->ksm->nama_ksm }}</strong> &bullet; 
                {{ $dokter->spesialisasi }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sdm.dokter.edit', $dokter->id) }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-pen-to-square me-1"></i> Edit Profil
            </a>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Doctor Biodata Card -->
    <div class="col-lg-4">
        <div class="card shadow-xs h-100">
            <div class="card-header fw-bold text-dark">
                <i class="fa-solid fa-id-card text-teal me-2"></i>
                Informasi Kepegawaian & Kontak
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush fs-sm">
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Username:</span>
                        <strong class="font-monospace text-dark">{{ $dokter->user->username }}</strong>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Status Kepegawaian:</span>
                        <span class="badge bg-light text-dark border">{{ $dokter->status_kepegawaian }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Status STR:</span>
                        <span class="badge {{ $dokter->status_str === 'seumur_hidup' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $dokter->status_str === 'seumur_hidup' ? 'Seumur Hidup' : 'Berkala' }}
                        </span>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Email:</span>
                        <span class="text-dark">{{ $dokter->user->email }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">No. WhatsApp:</span>
                        <span class="text-success fw-bold"><i class="fa-brands fa-whatsapp me-1"></i>{{ $dokter->user->no_wa }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Status Akun:</span>
                        <span class="badge {{ $dokter->user->aktif ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                            {{ $dokter->user->aktif ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Riwayat Dokumen SIP & Fast Reminder -->
    <div class="col-lg-8">
        <div class="card shadow-xs h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark">
                    <i class="fa-solid fa-file-medical text-teal me-2"></i>
                    Riwayat Berkas Surat Izin Praktik (SIP)
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-sm">
                    <thead class="table-light">
                        <tr>
                            <th>No. SIP</th>
                            <th>Praktik Ke</th>
                            <th>Masa Berlaku</th>
                            <th>Status Masa</th>
                            <th>Status Verifikasi</th>
                            <th>Kirim WA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dokter->dokumenSip as $sip)
                            @php $sisa = $sip->hitungSisaHari(); @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark"><span class="font-monospace fs-xs">{{ $sip->nomor_sip }}</span></div>
                                    @if($sip->file_sip)
                                        <a href="{{ asset('storage/' . $sip->file_sip) }}" target="_blank" class="fs-xs text-primary text-decoration-none">
                                            <i class="fa-solid fa-file-pdf me-1"></i> Unduh File
                                        </a>
                                    @endif
                                </td>
                                <td>Ke-{{ $sip->tempat_praktik_ke }}</td>
                                <td>
                                    <div>{{ $sip->tanggal_terbit ? $sip->tanggal_terbit->format('d/m/Y') : '-' }} s.d</div>
                                    <small class="fw-bold text-dark">{{ $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</small>
                                </td>
                                <td>
                                    <span class="status-pill status-{{ $sip->status }} fs-xs">
                                        {{ $sip->status_label }}
                                    </span>
                                    <div class="fs-xs text-muted mt-1">
                                        {{ $sisa < 0 ? abs($sisa) . ' hari lalu' : 'sisa ' . $sisa . ' hari' }}
                                    </div>
                                </td>
                                <td>
                                    @if($sip->status_verifikasi === 'disetujui')
                                        <span class="badge bg-success-subtle text-success border border-success"><i class="fa-solid fa-check"></i> Disahkan</span>
                                    @elseif($sip->status_verifikasi === 'ditolak')
                                        <span class="badge bg-danger-subtle text-danger border border-danger"><i class="fa-solid fa-xmark"></i> Ditolak</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning"><i class="fa-solid fa-clock"></i> Menunggu</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalKirimWa{{ $sip->id }}" title="Kirim Pengingat WhatsApp">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </button>

                                    <!-- Modal Kirim WA Khusus -->
                                    <div class="modal fade" id="modalKirimWa{{ $sip->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('sdm.reminder.kirim', $sip->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h6 class="modal-title fw-bold">Kirim Pengingat WhatsApp ke {{ $dokter->user->nama }}</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label fs-xs fw-semibold">Nomor WhatsApp Tujuan:</label>
                                                            <input type="text" class="form-control form-control-sm" value="{{ $dokter->user->no_wa }}" readonly>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fs-xs fw-semibold">Pesan Khusus (Opsional, kosongkan untuk menggunakan template resmi):</label>
                                                            <textarea name="pesan_khusus" rows="4" class="form-control form-control-sm" placeholder="Ketik pesan khusus jika ada instruksi tambahan..."></textarea>
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
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Dokter belum memiliki riwayat dokumen SIP.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Kredensial Klinis & Riwayat Notifikasi -->
<div class="row g-4">
    <!-- SPK & RKK -->
    <div class="col-lg-6">
        <div class="card shadow-xs h-100">
            <div class="card-header fw-bold text-dark">
                <i class="fa-solid fa-stamp text-teal me-2"></i>
                Instrumen Kredensial Klinis (SPK & RKK)
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-sm">
                    <thead class="table-light">
                        <tr>
                            <th>No. SPK Direktur</th>
                            <th>Masa Berlaku</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dokter->kredensialKlinis as $spk)
                            <tr>
                                <td><span class="font-monospace fs-xs">{{ $spk->nomor_spk }}</span></td>
                                <td>
                                    <div>{{ $spk->tanggal_terbit_spk ? $spk->tanggal_terbit_spk->format('d/m/Y') : '-' }} s.d</div>
                                    <small class="fw-bold text-dark">{{ $spk->tanggal_berakhir_spk ? $spk->tanggal_berakhir_spk->format('d/m/Y') : '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $spk->status_kredensial === 'aktif' ? 'bg-success' : ($spk->status_kredensial === 'evaluasi' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ $spk->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">
                                    Belum ada data kewenangan klinis (SPK/RKK).
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Riwayat Notifikasi Terkirim -->
    <div class="col-lg-6">
        <div class="card shadow-xs h-100">
            <div class="card-header fw-bold text-dark">
                <i class="fa-solid fa-bell text-secondary me-2"></i>
                Riwayat Pengingat Terkirim ke Dokter Ini
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush fs-sm">
                    @forelse($notifikasiList as $notif)
                        <li class="list-group-item py-2 px-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge {{ $notif->status_terkirim ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} fs-xs">
                                    <i class="fa-solid {{ $notif->status_terkirim ? 'fa-check' : 'fa-xmark' }}"></i>
                                    {{ $notif->status_terkirim ? 'Terkirim' : 'Gagal' }}
                                </span>
                                <small class="text-muted fs-xs">{{ $notif->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="text-secondary fs-xs">{{ Str::limit($notif->pesan, 90) }}</div>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-3 text-muted">Belum ada notifikasi terkirim.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
