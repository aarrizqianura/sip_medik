<?php

namespace Database\Seeders;

use App\Models\DokumenSip;
use App\Models\KredensialKlinis;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SampleFileSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('sip');
        Storage::disk('public')->makeDirectory('rkk');
        Storage::disk('public')->makeDirectory('str');

        $samplePdf = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >>\nendobj\n4 0 obj\n<< /Length 64 >>\nstream\nBT /F1 16 Tf 50 720 Td (SIM-SIP RSUD dr. H. Moch. Ansari Saleh - e-STR Kemenkes) ET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000216 00000 n \ntrailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n330\n%%EOF";

        // Buat file sample di root dan di subdirektori
        for ($i = 1; $i <= 5; $i++) {
            Storage::disk('public')->put("sip/sip_sample_{$i}.pdf", $samplePdf);
            Storage::disk('public')->put("sip_sample_{$i}.pdf", $samplePdf);
        }

        $strFiles = [
            'str_sample_ahmad.pdf',
            'str_sample_siti.pdf',
            'str_sample_bambang.pdf',
            'str_sample_ratna.pdf',
            'str_sample_hendra.pdf',
        ];

        foreach ($strFiles as $str) {
            Storage::disk('public')->put("str/{$str}", $samplePdf);
            Storage::disk('public')->put($str, $samplePdf);
        }

        $rkkFiles = [
            'rkk_bedah_ahmad.pdf',
            'rkk_internal_siti.pdf',
            'rkk_anak_bambang.pdf',
            'rkk_obgyn_ratna.pdf',
            'rkk_anestesi_hendra.pdf',
        ];

        foreach ($rkkFiles as $rkk) {
            Storage::disk('public')->put("rkk/{$rkk}", $samplePdf);
            Storage::disk('public')->put($rkk, $samplePdf);
        }

        // Pastikan path di DB menggunakan format sip/ atau langsung
        foreach (DokumenSip::all() as $sip) {
            if (! str_starts_with($sip->file_sip, 'sip/')) {
                $sip->file_sip = 'sip/'.$sip->file_sip;
                $sip->save();
            }
        }

        foreach (KredensialKlinis::all() as $kred) {
            if ($kred->file_rkk && ! str_starts_with($kred->file_rkk, 'rkk/')) {
                $kred->file_rkk = 'rkk/'.$kred->file_rkk;
                $kred->save();
            }
        }
    }
}
