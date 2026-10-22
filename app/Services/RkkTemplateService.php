<?php

namespace App\Services;

class RkkTemplateService
{
    /**
     * Dapatkan daftar template rincian kewenangan klinis (RKK) default berdasarkan kode KSM.
     *
     * @return array<int, array{kode: string, nama: string, kategori: string, status: string, catatan: string}>
     */
    public static function getTemplateByKsm(?string $kodeKsm): array
    {
        $kodeKsm = strtoupper($kodeKsm ?? '');

        return match ($kodeKsm) {
            'KSM-BED' => [
                ['kode' => 'BED-01', 'nama' => 'Laparotomi Eksplorasi Darurat & Elektif', 'kategori' => 'Bedah Digestif / Akut', 'status' => 'mandiri', 'catatan' => 'Sesuai kompetensi Sp.B'],
                ['kode' => 'BED-02', 'nama' => 'Apendiktomi Terbuka & Laparoskopi', 'kategori' => 'Bedah Digestif', 'status' => 'mandiri', 'catatan' => 'Prosedur rutin'],
                ['kode' => 'BED-03', 'nama' => 'Herniorafi & Hernioplasti dengan Mesh', 'kategori' => 'Bedah Dinding Abdomen', 'status' => 'mandiri', 'catatan' => 'Kasus inguinal/femoral'],
                ['kode' => 'BED-04', 'nama' => 'Kolesistektomi Terbuka', 'kategori' => 'Bedah Hepatobilier', 'status' => 'mandiri', 'catatan' => 'Batu empedu / kolesistitis'],
                ['kode' => 'BED-05', 'nama' => 'Kolesistektomi Laparoskopi Lanjutan', 'kategori' => 'Bedah Minimal Invasif', 'status' => 'supervisi', 'catatan' => 'Perlu supervisi konsultan digestif'],
                ['kode' => 'BED-06', 'nama' => 'Reseksi & Anastomosis Usus Halus/Kolon', 'kategori' => 'Bedah Digestif', 'status' => 'mandiri', 'catatan' => 'Obstruksi / perforasi'],
                ['kode' => 'BED-07', 'nama' => 'Eksisi Tumor Jaringan Lunak & Lipoma', 'kategori' => 'Bedah Onkologi Minor', 'status' => 'mandiri', 'catatan' => 'Eksisi luas'],
                ['kode' => 'BED-08', 'nama' => 'Mastektomi Radikal Modifikasi (MRM)', 'kategori' => 'Bedah Onkologi', 'status' => 'supervisi', 'catatan' => 'Didampingi konsultan onkologi'],
                ['kode' => 'BED-09', 'nama' => 'Trakeostomi Darurat & Elektif', 'kategori' => 'Airway Management Bedah', 'status' => 'mandiri', 'catatan' => 'Gawat darurat jalan napas'],
            ],
            'KSM-INT' => [
                ['kode' => 'INT-01', 'nama' => 'Endoskopi Saluran Cerna Atas (EGD Diagnostik)', 'kategori' => 'Gastroenterologi', 'status' => 'mandiri', 'catatan' => 'Hematemesis / melena'],
                ['kode' => 'INT-02', 'nama' => 'Kolonoskopi Diagnostik & Biopsi Mukosa', 'kategori' => 'Gastroenterologi', 'status' => 'supervisi', 'catatan' => 'Supervisi dokter spesialis senior'],
                ['kode' => 'INT-03', 'nama' => 'Parasentesis Abdomen Diagnostik & Terapeutik', 'kategori' => 'Hepatologi', 'status' => 'mandiri', 'catatan' => 'Asites masif'],
                ['kode' => 'INT-04', 'nama' => 'Punksi Pleura (Torakosentesis) & WSD', 'kategori' => 'Pulmonologi Medik', 'status' => 'mandiri', 'catatan' => 'Efusi pleura masif'],
                ['kode' => 'INT-05', 'nama' => 'Pemasangan Akses Hemodialisis Vaskular CDL', 'kategori' => 'Nefrologi', 'status' => 'supervisi', 'catatan' => 'Double lumen kateter'],
                ['kode' => 'INT-06', 'nama' => 'Biopsi Aspirasi Jarum Halus (FNAB) Tiroid', 'kategori' => 'Endokrinologi', 'status' => 'mandiri', 'catatan' => 'Nodul tiroid'],
                ['kode' => 'INT-07', 'nama' => 'Interpretasi Pemeriksaan EKG 12 Lead & Treadmill', 'kategori' => 'Kardiologi Medik', 'status' => 'mandiri', 'catatan' => 'Iskemia / aritmia'],
            ],
            'KSM-ANA' => [
                ['kode' => 'ANA-01', 'nama' => 'Resusitasi Neonatus Lanjut & NRP', 'kategori' => 'Perinatologi', 'status' => 'mandiri', 'catatan' => 'Asfiksia neonatorum'],
                ['kode' => 'ANA-02', 'nama' => 'Punksi Lumbal Pediatrik', 'kategori' => 'Neurologi Anak', 'status' => 'mandiri', 'catatan' => 'Suspek meningitis/ensefalitis'],
                ['kode' => 'ANA-03', 'nama' => 'Pemasangan Kateterisasi Vena Umbilikal (UVC)', 'kategori' => 'Neonatologi', 'status' => 'mandiri', 'catatan' => 'Akses emergensi BBLR'],
                ['kode' => 'ANA-04', 'nama' => 'Manajemen Ventilasi Mekanik Neonatus & PICU', 'kategori' => 'Pediatric Intensive Care', 'status' => 'supervisi', 'catatan' => 'Supervisi dokter konsultan ERIA'],
                ['kode' => 'ANA-05', 'nama' => 'Tata Laksana Syok Sepsis & Terapi Inotropik Anak', 'kategori' => 'Emergensi Pediatrik', 'status' => 'mandiri', 'catatan' => 'Protokol sepsis RSAS'],
                ['kode' => 'ANA-06', 'nama' => 'Intubasi Endotrakeal Neonatus & Pediatrik', 'kategori' => 'Airway Anak', 'status' => 'mandiri', 'catatan' => 'Gagal napas akut'],
            ],
            'KSM-OBG' => [
                ['kode' => 'OBG-01', 'nama' => 'Seksio Sesarea Elektif & Cito Darurat', 'kategori' => 'Obstetri Operatif', 'status' => 'mandiri', 'catatan' => 'Gawat janin / distosia'],
                ['kode' => 'OBG-02', 'nama' => 'Histerektomi Subtotal & Total Perabdominam', 'kategori' => 'Ginekologi Operatif', 'status' => 'mandiri', 'catatan' => 'Mioma uteri / adenomiosis'],
                ['kode' => 'OBG-03', 'nama' => 'Kuretase Diagnostik & Terapeutik (Blighted Ovum/AUB)', 'kategori' => 'Ginekologi Minor', 'status' => 'mandiri', 'catatan' => 'Perdarahan uterus abnormal'],
                ['kode' => 'OBG-04', 'nama' => 'Laparoskopi Ginekologi Operatif (Kistektomi)', 'kategori' => 'Minimal Invasif Ginekologi', 'status' => 'supervisi', 'catatan' => 'Didampingi konsultan fertilitas'],
                ['kode' => 'OBG-05', 'nama' => 'Manajemen Komprehensif Perdarahan Pascapersalinan (PPH)', 'kategori' => 'Emergensi Obstetri', 'status' => 'mandiri', 'catatan' => 'B-Lynch & tamponade balon'],
                ['kode' => 'OBG-06', 'nama' => 'Repair Ruptur Perineum Total (Grade 3-4)', 'kategori' => 'Rekonstruksi Obstetri', 'status' => 'mandiri', 'catatan' => 'Sphincter ani eksternus'],
            ],
            'KSM-ANE' => [
                ['kode' => 'ANE-01', 'nama' => 'Anestesi Umum Inhalasi & Intravena dengan ETT', 'kategori' => 'Anestesi Umum', 'status' => 'mandiri', 'catatan' => 'Operasi mayor/khusus'],
                ['kode' => 'ANE-02', 'nama' => 'Anestesi Regional Spinal & Epidural Lumbal', 'kategori' => 'Anestesi Regional', 'status' => 'mandiri', 'catatan' => 'Seksio & bedah ekstremitas'],
                ['kode' => 'ANE-03', 'nama' => 'Pemasangan Central Venous Catheter (CVC)', 'kategori' => 'Vascular Access', 'status' => 'mandiri', 'catatan' => 'Vena jugularis / subklavia'],
                ['kode' => 'ANE-04', 'nama' => 'Pemasangan Kanulasi Arteri (Arterial Line)', 'kategori' => 'Hemodynamic Monitoring', 'status' => 'mandiri', 'catatan' => 'Pemantauan tekanan darah kontinu'],
                ['kode' => 'ANE-05', 'nama' => 'Manajemen Pasien Kritis di ICU & Ventilator Mekanik', 'kategori' => 'Terapi Intensif', 'status' => 'mandiri', 'catatan' => 'Syok septik & ARDS'],
                ['kode' => 'ANE-06', 'nama' => 'Resusitasi Jantung Paru Lanjut (ACLS) & Defibrilasi', 'kategori' => 'Resusitasi Kritis', 'status' => 'mandiri', 'catatan' => 'Cardiac arrest'],
            ],
            'KSM-JAN' => [
                ['kode' => 'JAN-01', 'nama' => 'Ekokardiografi Transtorakal Dewasa (TTE)', 'kategori' => 'Non-Invasif Kardiologi', 'status' => 'mandiri', 'catatan' => 'Evaluasi fungsi sistolik/diastolik'],
                ['kode' => 'JAN-02', 'nama' => 'Uji Latih Beban Jantung (Treadmill Test)', 'kategori' => 'Non-Invasif Kardiologi', 'status' => 'mandiri', 'catatan' => 'Deteksi PJK'],
                ['kode' => 'JAN-03', 'nama' => 'Kardioversi Tersinkronisasi Emergensi & Elektif', 'kategori' => 'Aritmia & Resusitasi', 'status' => 'mandiri', 'catatan' => 'AF / VT dengan nadi'],
                ['kode' => 'JAN-04', 'nama' => 'Kateterisasi Jantung Koroner Diagnostik (CAG)', 'kategori' => 'Kardiologi Intervensi', 'status' => 'supervisi', 'catatan' => 'Supervisi intervensi senior'],
                ['kode' => 'JAN-05', 'nama' => 'Pemasangan Pacemaker Transvena Sementara (TPM)', 'kategori' => 'Aritmia Emergensi', 'status' => 'mandiri', 'catatan' => 'Total AV block simtomatik'],
            ],
            'KSM-SAR' => [
                ['kode' => 'SAR-01', 'nama' => 'Trombolisis Intravena Stroke Iskemik Hiperakut (r-tPA)', 'kategori' => 'Neuro-Vaskular Akut', 'status' => 'mandiri', 'catatan' => 'Golden period 4.5 jam'],
                ['kode' => 'SAR-02', 'nama' => 'Punksi Lumbal Diagnostik Neurologis', 'kategori' => 'Prosedur Invasif Saraf', 'status' => 'mandiri', 'catatan' => 'Meningitis / Guillain-Barre'],
                ['kode' => 'SAR-03', 'nama' => 'Interpretasi Elektroensefalografi (EEG Rutin)', 'kategori' => 'Neurofisiologi Klinis', 'status' => 'mandiri', 'catatan' => 'Epilepsi & ensefalopati'],
                ['kode' => 'SAR-04', 'nama' => 'Elektromiografi & Kecepatan Hantar Saraf (EMG/NCV)', 'kategori' => 'Neurofisiologi Klinis', 'status' => 'supervisi', 'catatan' => 'Didampingi dokter spesialis senior'],
                ['kode' => 'SAR-05', 'nama' => 'Manajemen Status Epileptikus Refrakter di ICU', 'kategori' => 'Neuro-Intensif', 'status' => 'mandiri', 'catatan' => 'Protokol ICU'],
            ],
            default => [
                ['kode' => 'UMUM-01', 'nama' => 'Triase Gawat Darurat & Initial Assessment', 'kategori' => 'Gawat Darurat', 'status' => 'mandiri', 'catatan' => 'Instalasi Gawat Darurat'],
                ['kode' => 'UMUM-02', 'nama' => 'Bantuan Hidup Dasar (BHD) & Lanjut (ACLS)', 'kategori' => 'Gawat Darurat', 'status' => 'mandiri', 'catatan' => 'Henti jantung / henti napas'],
                ['kode' => 'UMUM-03', 'nama' => 'Penjahitan Luka Robek & Eksisi Luka Sederhana', 'kategori' => 'Bedah Minor', 'status' => 'mandiri', 'catatan' => 'Luka robek kutis/subkutis'],
                ['kode' => 'UMUM-04', 'nama' => 'Pemasangan Pipa Nasogastrik (NGT) & Kateter Urine', 'kategori' => 'Prosedur Medis Dasar', 'status' => 'mandiri', 'catatan' => 'Dekompresi & eliminasi'],
                ['kode' => 'UMUM-05', 'nama' => 'Sirkumsisi Medis Sederhana', 'kategori' => 'Bedah Minor', 'status' => 'mandiri', 'catatan' => 'Phimosis / higienis'],
                ['kode' => 'UMUM-06', 'nama' => 'Tata Laksana Medikamentosa Primer Rawat Jalan', 'kategori' => 'Pelayanan Primer', 'status' => 'mandiri', 'catatan' => 'Penyakit umum non-spesialistik'],
            ],
        };
    }
}
