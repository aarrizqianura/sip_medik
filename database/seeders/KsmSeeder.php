<?php

namespace Database\Seeders;

use App\Models\Ksm;
use Illuminate\Database\Seeder;

class KsmSeeder extends Seeder
{
    public function run(): void
    {
        $ksmData = [
            ['kode_ksm' => 'KSM-BED', 'nama_ksm' => 'KSM Bedah & Subspesialis', 'keterangan' => 'Pelayanan bedah umum, ortopedi, urologi, dan bedah anak.'],
            ['kode_ksm' => 'KSM-INT', 'nama_ksm' => 'KSM Penyakit Dalam', 'keterangan' => 'Pelayanan penyakit dalam, ginjal hipertensi, endokrin, dan tropik infeksi.'],
            ['kode_ksm' => 'KSM-ANA', 'nama_ksm' => 'KSM Kesehatan Anak', 'keterangan' => 'Pelayanan pediatrik umum dan neonatologi.'],
            ['kode_ksm' => 'KSM-OBG', 'nama_ksm' => 'KSM Obstetri & Ginekologi', 'keterangan' => 'Pelayanan kebidanan, fertilitas, dan onkologi ginekologi.'],
            ['kode_ksm' => 'KSM-ANE', 'nama_ksm' => 'KSM Anestesiologi & Terapi Intensif', 'keterangan' => 'Pelayanan anestesi, ICU, ICCU, dan manajemen nyeri.'],
            ['kode_ksm' => 'KSM-JAN', 'nama_ksm' => 'KSM Jantung & Pembuluh Darah', 'keterangan' => 'Pelayanan kardiovaskular diagnostik dan intervensi.'],
            ['kode_ksm' => 'KSM-SAR', 'nama_ksm' => 'KSM Neurologi / Saraf', 'keterangan' => 'Pelayanan saraf, penanganan stroke, dan neurovaskular.'],
            ['kode_ksm' => 'KSM-MAT', 'nama_ksm' => 'KSM Kesehatan Mata', 'keterangan' => 'Pelayanan oftalmologi dan bedah katarak.'],
            ['kode_ksm' => 'KSM-THT', 'nama_ksm' => 'KSM Telinga Hidung Tenggorok - KL', 'keterangan' => 'Pelayanan THT dan bedah kepala leher.'],
            ['kode_ksm' => 'KSM-RAD', 'nama_ksm' => 'KSM Radiologi & Pencitraan Medis', 'keterangan' => 'Pelayanan CT-Scan, MRI, USG, dan radiografi konvensional.'],
        ];

        foreach ($ksmData as $item) {
            Ksm::updateOrCreate(['kode_ksm' => $item['kode_ksm']], $item);
        }
    }
}
