<?php

namespace App\Console\Commands;

use App\Services\SipMonitoringService;
use Illuminate\Console\Command;

class CekKadaluarsaSipCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sip:cek-kadaluarsa {--force : Paksa pengiriman notifikasi ke seluruh dokter & SDM}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tahap 5: Early Warning Engine harian (00.00 WITA) evaluasi masa berlaku SIP & eskalasi berjenjang WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle(SipMonitoringService $monitoringService): int
    {
        $this->info('================================================================');
        $this->info('  EARLY WARNING ENGINE - RSUD DR. H. MOCH. ANSARI SALEH (WITA)  ');
        $this->info('================================================================');
        $this->info('Memulai evaluasi masa berlaku SIP & eskalasi berjenjang...');

        $force = (bool) $this->option('force');
        $hasil = $monitoringService->runDailyCheck($force);

        $this->newLine();
        $this->info('Pemeriksaan selesai!');
        $this->info("Total berkas diperiksa : {$hasil['total_diperiksa']}");
        $this->info("Pesan WA terkirim      : {$hasil['notifikasi_dikirim']}");

        if (! empty($hasil['detail'])) {
            $rows = array_map(function ($item) {
                return [
                    $item['dokter'] ?? '-',
                    $item['nomor_sip'] ?? '-',
                    ($item['selisih'] ?? 0).' Hari',
                    $item['eskalasi'] ?? '-',
                    $item['penerima'] ?? '-',
                ];
            }, $hasil['detail']);

            $this->table(['Dokter', 'Nomor SIP', 'Sisa Hari', 'Eskalasi', 'Penerima'], $rows);
        } else {
            $this->line('Semua dokumen berstatus aman (>90 Hari) atau belum memerlukan pengiriman pesan baru.');
        }

        return Command::SUCCESS;
    }
}
