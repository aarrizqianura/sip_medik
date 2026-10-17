@extends('layouts.app')

@section('title', 'Konfigurasi Sistem & Gateway')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold text-dark mb-1">Konfigurasi Sistem & API Gateway</h4>
    <p class="text-secondary fs-sm mb-0">Pengaturan WhatsApp Gateway, integrasi scheduler cron job, dan identitas fasyankes RSUD dr. H. Moch. Ansari Saleh</p>
</div>

<div class="row g-4">
    <!-- Form Pengaturan Sistem -->
    <div class="col-lg-7">
        <div class="card shadow-xs mb-4">
            <div class="card-header fw-bold text-dark">
                <i class="fa-solid fa-sliders text-teal me-2"></i>
                Parameter WhatsApp Gateway & Identitas RS
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.pengaturan.update') }}" method="POST">
                    @csrf

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="fa-brands fa-whatsapp text-success me-1"></i> WhatsApp Gateway API (Fonnte / Wablas)
                    </h6>
                    <div class="mb-3">
                        <label class="form-label fs-sm fw-semibold">Endpoint API URL</label>
                        <input type="url" name="wa_api_url" class="form-control" value="{{ $pengaturan['wa_api_url'] ?? 'https://api.fonnte.com/send' }}" required>
                        <small class="text-muted fs-xs">URL Endpoint vendor gateway (contoh: https://api.fonnte.com/send).</small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fs-sm fw-semibold">Authorization Token / API Key</label>
                        <input type="text" name="wa_api_token" class="form-control font-monospace" value="{{ $pengaturan['wa_api_token'] ?? '' }}" placeholder="Masukkan token API gateway Anda">
                        <small class="text-muted fs-xs">Token autentikasi dari dashboard vendor WhatsApp.</small>
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="fa-solid fa-hospital text-teal me-1"></i> Identitas Resmi Fasyankes (Kop Dokumen)
                    </h6>
                    <div class="mb-3">
                        <label class="form-label fs-sm fw-semibold">Nama Resmi Rumah Sakit</label>
                        <input type="text" name="nama_fasyankes" class="form-control" value="{{ $pengaturan['nama_fasyankes'] ?? 'RSUD dr. H. Moch. Ansari Saleh Banjarmasin' }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-sm fw-semibold">Alamat Lengkap</label>
                        <textarea name="alamat_fasyankes" rows="2" class="form-control" required>{{ $pengaturan['alamat_fasyankes'] ?? 'Jl. Brigjend H. Hasan Basry No. 1, Banjarmasin Utara, Kota Banjarmasin, Kalimantan Selatan 70125' }}</textarea>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fs-sm fw-semibold">Nomor Telepon / Humas</label>
                            <input type="text" name="telepon_fasyankes" class="form-control" value="{{ $pengaturan['telepon_fasyankes'] ?? '(0511) 3300757 / 3300758' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-sm fw-semibold">Email Resmi Komite Medik</label>
                            <input type="email" name="email_komite" class="form-control" value="{{ $pengaturan['email_komite'] ?? 'komite.medik@ansarisaleh.id' }}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-teal text-white btn-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Konfigurasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Testing & Scheduler Side Panel -->
    <div class="col-lg-5">
        <!-- Pengujian WhatsApp Gateway -->
        <div class="card shadow-xs mb-4">
            <div class="card-header fw-bold text-dark">
                <i class="fa-solid fa-paper-plane text-success me-2"></i>
                Uji Coba Kirim WhatsApp Gateway
            </div>
            <div class="card-body p-4">
                <p class="text-secondary fs-xs mb-3">
                    Gunakan formulir ini untuk memverifikasi sambungan API Gateway ke nomor telepon tujuan.
                </p>
                <form action="{{ route('admin.pengaturan.test_wa') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fs-sm fw-semibold">Nomor WhatsApp Tujuan</label>
                        <input type="text" name="no_wa" class="form-control form-control-sm" placeholder="Contoh: 081234567890" value="{{ auth()->user()->no_wa }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-sm fw-semibold">Isi Pesan Uji Coba</label>
                        <textarea name="pesan" rows="3" class="form-control form-control-sm" required>[SIM-SIP RSUD Ansari Saleh] Ini adalah pesan pengujian koneksi WhatsApp Gateway API.</textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-success btn-sm w-100">
                        <i class="fa-solid fa-paper-plane me-1"></i> Kirim Pesan Uji Coba
                    </button>
                </form>
            </div>
        </div>

        <!-- Cron Job Scheduler Info -->
        <div class="card shadow-xs">
            <div class="card-header fw-bold text-dark">
                <i class="fa-solid fa-clock-rotate-left text-teal me-2"></i>
                Status Scheduler Cron Job
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info border-0 p-3 mb-3 fs-xs">
                    <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-info me-1"></i> Konfigurasi Server Crontab:</div>
                    <code>* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code>
                </div>

                <p class="text-secondary fs-xs mb-3">
                    Scheduler diatur berjalan otomatis setiap hari pada pukul <strong>00:00 WITA</strong> untuk memeriksa SIP yang memasuki masa H-90, H-60, H-30, atau Overdue.
                </p>

                <form action="{{ route('admin.pengaturan.trigger_cron') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-teal btn-sm w-100">
                        <i class="fa-solid fa-bolt me-1"></i> Eksekusi Evaluasi Manual Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
