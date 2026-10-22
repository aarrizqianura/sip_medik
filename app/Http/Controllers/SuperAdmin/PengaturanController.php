<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PengaturanSistem;
use App\Services\SipMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PengaturanController extends Controller
{
    public function index()
    {
        $pengaturan = PengaturanSistem::all()->pluck('nilai', 'kunci')->toArray();

        return view('admin.pengaturan.index', compact('pengaturan'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', 'action']);

        foreach ($data as $key => $value) {
            PengaturanSistem::set($key, $value);
        }

        AuditLog::record('UPDATE_PENGATURAN', 'Pengaturan Sistem', 'Memperbarui konfigurasi sistem dan parameter gateway.');

        return back()->with('sukses', 'Konfigurasi sistem berhasil disimpan.');
    }

    public function testWa(Request $request)
    {
        $request->validate([
            'no_wa' => 'required|string',
            'pesan' => 'required|string',
        ]);

        $apiUrl = PengaturanSistem::get('wa_api_url', 'https://api.fonnte.com/send');
        $apiToken = PengaturanSistem::get('wa_api_token', '');

        if (empty($apiToken)) {
            return back()->with('warning', 'Token API WhatsApp belum diatur. Pesan simulasi berhasil dicatat.');
        }

        try {
            $response = Http::timeout(10)->withHeaders([
                'Authorization' => $apiToken,
            ])->post($apiUrl, [
                'target' => $request->no_wa,
                'message' => $request->pesan,
            ]);

            if ($response->successful()) {
                AuditLog::record('TEST_WA_GATEWAY', 'WhatsApp Gateway', "Kirim tes WA berhasil ke nomor {$request->no_wa}");

                return back()->with('sukses', "Pengujian kirim pesan WhatsApp ke {$request->no_wa} berhasil!");
            } else {
                return back()->with('error', 'Respon Vendor WA Gateway: '.$response->body());
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal terhubung ke gateway WhatsApp: '.$e->getMessage());
        }
    }

    public function triggerCron(SipMonitoringService $monitoringService)
    {
        $hasil = $monitoringService->runDailyCheck();

        AuditLog::record('TRIGGER_CRON', 'Scheduler Sistem', "Menjalankan manual evaluasi kedaluwarsa SIP. {$hasil['notifikasi_dikirim']} pesan terkirim.");

        return back()->with('sukses', "Evaluasi selesai dijalankan! {$hasil['total_diperiksa']} berkas diperiksa, {$hasil['notifikasi_dikirim']} pesan WhatsApp terpicu.");
    }
}
