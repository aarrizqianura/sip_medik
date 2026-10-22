<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tahap 5: Early Warning Engine - Scheduler evaluasi masa berlaku SIP & eskalasi berjenjang setiap hari jam 00:00 WITA
Schedule::command('sip:cek-kadaluarsa')
    ->dailyAt('00:00')
    ->timezone('Asia/Makassar') // WITA (UTC+8)
    ->name('early-warning-sip-daily-check')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/sip_scheduler.log'));
