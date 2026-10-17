<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SipMonitoringService;

class CekKadaluarsaSipCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sip:cek-kadaluarsa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pemeriksaan harian masa berlaku SIP & kredensial dokter serta kirim notifikasi WhatsApp otomatis';

    /**
     * Execute the console command.
     */
    public function handle(SipMonitoringService $monitoringService): int
    {
        $this->info('Memulai evaluasi masa berlaku SIP dokter RSUD dr. H. Moch. Ansari Saleh...');
        
        $hasil = $monitoringService->runDailyCheck();

        $this->info("Pemeriksaan selesai!");
        $this->info("Total berkas diperiksa : {$hasil['total_diperiksa']}");
        $this->info("Pesan WA terkirim      : {$hasil['notifikasi_dikirim']}");

        if (!empty($hasil['detail'])) {
            $this->table(['Dokter', 'Nomor SIP', 'Sisa Hari'], $hasil['detail']);
        }

        return Command::SUCCESS;
    }
}
