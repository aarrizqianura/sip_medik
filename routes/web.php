<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dokter\PengajuanSipController;
use App\Http\Controllers\Komite\KredensialController;
use App\Http\Controllers\Komite\VerifikasiSipController;
use App\Http\Controllers\Komite\VerifikasiStrController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\Sdm\DokterController;
use App\Http\Controllers\Sdm\KsmController;
use App\Http\Controllers\Sdm\PengesahanDireksiController;
use App\Http\Controllers\Sdm\ReminderController;
use App\Http\Controllers\SuperAdmin\AuditLogController;
use App\Http\Controllers\SuperAdmin\PengaturanController;
use App\Http\Controllers\SuperAdmin\UserController;
use Illuminate\Support\Facades\Route;

// Autentikasi
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifikasi
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/{id}/baca', [NotifikasiController::class, 'markAsRead'])->name('notifikasi.baca');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'markAllAsRead'])->name('notifikasi.baca_semua');

    // Repositori berkas SIP publik/terverifikasi
    Route::get('/sip/arsip/{id}', [PengajuanSipController::class, 'show'])->name('sip.show');

    // 1. Role Super Admin
    Route::middleware(['role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-aktif', [UserController::class, 'toggleAktif'])->name('users.toggle');
        Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::post('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
        Route::post('pengaturan/test-wa', [PengaturanController::class, 'testWa'])->name('pengaturan.test_wa');
        Route::post('pengaturan/trigger-cron', [PengaturanController::class, 'triggerCron'])->name('pengaturan.trigger_cron');
        Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit.index');
    });

    // 2. Role Admin SDM (dan Super Admin)
    Route::middleware(['role:admin_sdm,super_admin'])->prefix('sdm')->name('sdm.')->group(function () {
        Route::resource('dokter', DokterController::class);
        Route::post('reminder/{id}/kirim', [DokterController::class, 'kirimManual'])->name('reminder.kirim');
        Route::resource('ksm', KsmController::class);
        Route::get('reminder', [ReminderController::class, 'index'])->name('reminder.index');
        Route::post('reminder/blast', [ReminderController::class, 'blast'])->name('reminder.blast');
        Route::post('reminder/run-engine', [ReminderController::class, 'runEngine'])->name('reminder.run_engine');
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/export-pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
        Route::get('laporan/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.excel');

        // Tahap 4: Pengesahan Direksi & Repositori e-Arsip Digital Kepegawaian
        Route::get('pengesahan-direksi', [PengesahanDireksiController::class, 'index'])->name('pengesahan.index');
        Route::get('pengesahan-direksi/{kredensial}/proses', [PengesahanDireksiController::class, 'create'])->name('pengesahan.create');
        Route::post('pengesahan-direksi/{kredensial}/proses', [PengesahanDireksiController::class, 'store'])->name('pengesahan.store');
        Route::get('e-arsip/{dokter}', [PengesahanDireksiController::class, 'show'])->name('arsip.show');
        Route::get('e-arsip/{dokter}/cetak', [PengesahanDireksiController::class, 'cetak'])->name('arsip.cetak');
    });

    // 3. Role Verifikator Komite Medik (dan Super Admin)
    Route::middleware(['role:verifikator_komite,super_admin'])->prefix('komite')->name('komite.')->group(function () {
        // Tahap 1: Verifikasi STR & SatuSehat SDMK
        Route::get('verifikasi-str', [VerifikasiStrController::class, 'index'])->name('verifikasi_str.index');
        Route::get('verifikasi-str/{dokter}', [VerifikasiStrController::class, 'show'])->name('verifikasi_str.show');
        Route::post('verifikasi-str/{dokter}/periksa-satusehat', [VerifikasiStrController::class, 'periksaSatuSehat'])->name('verifikasi_str.satusehat');
        Route::post('verifikasi-str/{dokter}/validasi', [VerifikasiStrController::class, 'validasi'])->name('verifikasi_str.validasi');
        Route::post('verifikasi-str/{dokter}/unlock', [VerifikasiStrController::class, 'unlock'])->name('verifikasi_str.unlock');

        // Verifikasi SIP (Tahap 3 Pemeriksaan Dokumen)
        Route::get('verifikasi', [VerifikasiSipController::class, 'index'])->name('verifikasi.index');
        Route::get('verifikasi/{id}', [VerifikasiSipController::class, 'show'])->name('verifikasi.show');
        Route::post('verifikasi/{id}/proses', [VerifikasiSipController::class, 'proses'])->name('verifikasi.proses');

        // Rekredensialing & Sidang Mitra Bestari & RKK (Tahap 3)
        Route::get('kredensial/{kredensial}/sidang', [KredensialController::class, 'sidang'])->name('kredensial.sidang');
        Route::post('kredensial/{kredensial}/sidang', [KredensialController::class, 'simpanSidang'])->name('kredensial.simpan_sidang');
        Route::post('kredensial/{kredensial}/terbitkan-rekomendasi', [KredensialController::class, 'terbitkanRekomendasi'])->name('kredensial.terbitkan_rekomendasi');
        Route::get('kredensial/{kredensial}/surat-rekomendasi', [KredensialController::class, 'suratRekomendasi'])->name('kredensial.surat_rekomendasi');
        Route::post('kredensial/{kredensial}/sahkan-spk', [KredensialController::class, 'sahkanSpk'])->name('kredensial.sahkan_spk');
        Route::resource('kredensial', KredensialController::class);
    });

    // 4. Role Dokter (dan Super Admin)
    Route::middleware(['role:dokter,super_admin'])->prefix('dokter')->name('dokter.')->group(function () {
        Route::get('pengajuan-sip', [PengajuanSipController::class, 'index'])->name('pengajuan.index');
        Route::get('pengajuan-sip/tambah', [PengajuanSipController::class, 'create'])->name('pengajuan.create');
        Route::post('pengajuan-sip', [PengajuanSipController::class, 'store'])->name('pengajuan.store');
        Route::get('e-arsip', function () {
            $dokter = auth()->user()->dokter;
            if (! $dokter) {
                return redirect()->route('dashboard')->with('error', 'Profil dokter belum terdaftar.');
            }

            return app(PengesahanDireksiController::class)->show($dokter->id);
        })->name('arsip.saya');
    });
});
