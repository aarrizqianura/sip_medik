<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\DokumenSip;
use App\Models\KredensialKlinis;
use App\Models\Ksm;
use App\Models\Notifikasi;
use App\Models\PengaturanSistem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $jenis = $request->input('jenis', '1');
        $ksmList = Ksm::orderBy('nama_ksm')->get();

        $data = $this->ambilDataLaporan($jenis, $request, true);

        // Tahap 7: Daftar Staf Medis Non-Aktif Pelayanan (Audit Akreditasi Pokja KPS)
        $stafNonAktifPelayanan = Dokter::with(['user', 'ksm', 'sipTerbaru', 'spkTerbaru'])
            ->where(function ($q) {
                $q->where('status_praktik', 'DITANGGUHKAN')
                    ->orWhereHas('dokumenSip', function ($sub) {
                        $sub->where('status', 'overdue')
                            ->where(function ($sub2) {
                                $sub2->whereNull('status_dispensasi')
                                    ->orWhere('status_dispensasi', '!=', 'disetujui')
                                    ->orWhere('tanggal_akhir_dispensasi', '<', Carbon::today());
                            });
                    });
            })
            ->get();

        return view('laporan.index', compact('jenis', 'ksmList', 'data', 'stafNonAktifPelayanan'));
    }

    protected function ambilDataLaporan(string $jenis, Request $request, bool $paginate = false)
    {
        switch ($jenis) {
            case '1': // Laporan Data Staf Medis & Status SIP
                $query = Dokter::with(['user', 'ksm', 'sipTerbaru']);

                if ($request->filled('id_ksm')) {
                    $query->where('id_ksm', $request->id_ksm);
                }
                if ($request->filled('status_kepegawaian')) {
                    $query->where('status_kepegawaian', $request->status_kepegawaian);
                }
                if ($request->filled('status_sip')) {
                    $st = $request->status_sip;
                    $query->whereHas('sipTerbaru', function ($q) use ($st) {
                        $q->where('status', $st);
                    });
                }

                return $paginate ? $query->paginate(10)->withQueryString() : $query->get();

            case '2': // Laporan Monitoring Masa Berlaku SIP (Audit Akreditasi KPS)
                $query = DokumenSip::with(['dokter.user', 'dokter.ksm', 'dokter.spkTerbaru'])
                    ->where('status_verifikasi', 'disetujui');

                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                if ($request->filled('id_ksm')) {
                    $idKsm = $request->id_ksm;
                    $query->whereHas('dokter', function ($q) use ($idKsm) {
                        $q->where('id_ksm', $idKsm);
                    });
                }

                $query->orderBy('tanggal_kadaluarsa');

                return $paginate ? $query->paginate(10)->withQueryString() : $query->get();

            case '3': // Laporan Verifikasi Dokumen SIP (Komite Medik)
                $query = DokumenSip::with(['dokter.user', 'dokter.ksm', 'verifikator']);

                if ($request->filled('status_verifikasi')) {
                    $query->where('status_verifikasi', $request->status_verifikasi);
                }
                if ($request->filled('tgl_mulai')) {
                    $query->whereDate('created_at', '>=', $request->tgl_mulai);
                }
                if ($request->filled('tgl_selesai')) {
                    $query->whereDate('created_at', '<=', $request->tgl_selesai);
                }
                if ($request->filled('id_ksm')) {
                    $idKsm = $request->id_ksm;
                    $query->whereHas('dokter', function ($q) use ($idKsm) {
                        $q->where('id_ksm', $idKsm);
                    });
                }

                $query->latest();

                return $paginate ? $query->paginate(10)->withQueryString() : $query->get();

            case '4': // Laporan Masa Berlaku SPK & RKK Dokter
                $query = KredensialKlinis::with(['dokter.user', 'dokter.ksm']);

                if ($request->filled('status_kredensial')) {
                    $query->where('status_kredensial', $request->status_kredensial);
                }
                if ($request->filled('id_ksm')) {
                    $idKsm = $request->id_ksm;
                    $query->whereHas('dokter', function ($q) use ($idKsm) {
                        $q->where('id_ksm', $idKsm);
                    });
                }

                $query->orderBy('tanggal_berakhir_spk');

                return $paginate ? $query->paginate(10)->withQueryString() : $query->get();

            case '5': // Laporan Riwayat Notifikasi Pengingat Jatuh Tempo
                $query = Notifikasi::with(['user', 'dokumenSip']);

                if ($request->filled('status_terkirim')) {
                    $query->where('status_terkirim', $request->status_terkirim === '1');
                }
                if ($request->filled('tgl_mulai')) {
                    $query->whereDate('created_at', '>=', $request->tgl_mulai);
                }
                if ($request->filled('tgl_selesai')) {
                    $query->whereDate('created_at', '<=', $request->tgl_selesai);
                }

                $query->latest();

                return $paginate ? $query->paginate(10)->withQueryString() : $query->get();

            default:
                return collect();
        }
    }

    public function exportPdf(Request $request)
    {
        $jenis = $request->input('jenis', '1');
        $data = $this->ambilDataLaporan($jenis, $request);
        $rsNama = PengaturanSistem::get('nama_fasyankes', 'RSUD dr. H. Moch. Ansari Saleh Banjarmasin');
        $rsAlamat = PengaturanSistem::get('alamat_fasyankes', 'Jl. Brigjend H. Hasan Basry No. 1, Banjarmasin, Kalimantan Selatan');
        $rsTelepon = PengaturanSistem::get('telepon_fasyankes', '(0511) 3300757 / 3300758');

        // Tahap 7: Daftar Staf Medis Non-Aktif Pelayanan untuk Lampiran Akreditasi Pokja KPS
        $stafNonAktifPelayanan = Dokter::with(['user', 'ksm', 'sipTerbaru', 'spkTerbaru'])
            ->where(function ($q) {
                $q->where('status_praktik', 'DITANGGUHKAN')
                    ->orWhereHas('dokumenSip', function ($sub) {
                        $sub->where('status', 'overdue')
                            ->where(function ($sub2) {
                                $sub2->whereNull('status_dispensasi')
                                    ->orWhere('status_dispensasi', '!=', 'disetujui')
                                    ->orWhere('tanggal_akhir_dispensasi', '<', Carbon::today());
                            });
                    });
            })
            ->get();

        $judulLaporan = match ($jenis) {
            '1' => 'Laporan Data Staf Medis & Status SIP',
            '2' => 'Laporan Monitoring Masa Berlaku SIP (Audit Akreditasi KPS)',
            '3' => 'Laporan Verifikasi Dokumen SIP (Komite Medik)',
            '4' => 'Laporan Masa Berlaku SPK & RKK Dokter',
            '5' => 'Laporan Riwayat Notifikasi Pengingat Jatuh Tempo',
            default => 'Laporan Sistem Informasi SIP'
        };

        $pdf = Pdf::loadView('laporan.pdf_template', compact(
            'jenis',
            'data',
            'stafNonAktifPelayanan',
            'judulLaporan',
            'rsNama',
            'rsAlamat',
            'rsTelepon'
        ))->setPaper('a4', 'landscape');

        $filename = 'Laporan_'.str_replace(' ', '_', $judulLaporan).'_'.date('Ymd_His').'.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $jenis = $request->input('jenis', '1');
        $data = $this->ambilDataLaporan($jenis, $request);

        $judulLaporan = match ($jenis) {
            '1' => 'Laporan_Data_Staf_Medis_SIP',
            '2' => 'Laporan_Monitoring_SIP_Audit_KPS',
            '3' => 'Laporan_Verifikasi_SIP_Komite',
            '4' => 'Laporan_Masa_Berlaku_SPK_RKK',
            '5' => 'Laporan_Riwayat_Notifikasi_Pengingat',
            default => 'Laporan_SIM_SIP'
        };

        $filename = $judulLaporan.'_'.date('Ymd_His').'.xls';

        return response()->view('laporan.excel_template', compact('jenis', 'data', 'judulLaporan'))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->header('Cache-Control', 'max-age=0');
    }
}
