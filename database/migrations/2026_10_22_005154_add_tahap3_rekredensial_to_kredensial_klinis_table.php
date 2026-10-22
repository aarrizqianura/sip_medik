<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahan pada dokumen_sip untuk validasi pindaian & permohonan kewenangan baru
        Schema::table('dokumen_sip', function (Blueprint $table) {
            $table->boolean('permohonan_kewenangan_baru')->default(false)->after('catatan_verifikasi');
            $table->text('catatan_kewenangan_baru')->nullable()->after('permohonan_kewenangan_baru');
            $table->boolean('checklist_keabsahan_fasyankes')->default(false)->after('catatan_kewenangan_baru');
            $table->boolean('checklist_masa_berlaku')->default(false)->after('checklist_keabsahan_fasyankes');
            $table->boolean('checklist_keterbacaan_pindaian')->default(false)->after('checklist_masa_berlaku');
        });

        // 2. Modifikasi kolom kredensial_klinis agar mendukung status_kredensial baru dan nullability saat inisiasi
        DB::statement("ALTER TABLE kredensial_klinis MODIFY COLUMN status_kredensial VARCHAR(50) NOT NULL DEFAULT 'aktif'");
        DB::statement('ALTER TABLE kredensial_klinis MODIFY COLUMN nomor_spk VARCHAR(100) NULL');
        DB::statement('ALTER TABLE kredensial_klinis MODIFY COLUMN tanggal_terbit_spk DATE NULL');
        DB::statement('ALTER TABLE kredensial_klinis MODIFY COLUMN tanggal_berakhir_spk DATE NULL');

        // 3. Tambahan pada kredensial_klinis untuk alur rekredensialing, sidang Mitra Bestari, & formulir digital RKK
        Schema::table('kredensial_klinis', function (Blueprint $table) {
            $table->string('jenis_pengajuan', 50)->default('rekredensial_berkala')->after('file_rkk');
            $table->text('alasan_rekredensial')->nullable()->after('jenis_pengajuan');
            $table->string('tahap_rekredensial', 50)->default('menunggu_sidang')->after('alasan_rekredensial');
            $table->date('tanggal_sidang')->nullable()->after('tahap_rekredensial');
            $table->string('nama_mitra_bestari', 255)->nullable()->after('tanggal_sidang');
            $table->text('evaluasi_logbook')->nullable()->after('nama_mitra_bestari');
            $table->text('evaluasi_portofolio')->nullable()->after('evaluasi_logbook');
            $table->text('catatan_mitra_bestari')->nullable()->after('evaluasi_portofolio');
            $table->json('daftar_rkk')->nullable()->after('catatan_mitra_bestari');
            $table->string('nomor_surat_rekomendasi', 150)->nullable()->after('daftar_rkk');
            $table->date('tanggal_rekomendasi')->nullable()->after('nomor_surat_rekomendasi');
            $table->string('ketua_komite_medik', 150)->nullable()->after('tanggal_rekomendasi');
            $table->text('catatan_rekomendasi')->nullable()->after('ketua_komite_medik');
            $table->string('status_rekomendasi', 50)->default('draft')->after('catatan_rekomendasi');
            $table->date('tanggal_sah_spk')->nullable()->after('status_rekomendasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumen_sip', function (Blueprint $table) {
            $table->dropColumn([
                'permohonan_kewenangan_baru',
                'catatan_kewenangan_baru',
                'checklist_keabsahan_fasyankes',
                'checklist_masa_berlaku',
                'checklist_keterbacaan_pindaian',
            ]);
        });

        Schema::table('kredensial_klinis', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_pengajuan',
                'alasan_rekredensial',
                'tahap_rekredensial',
                'tanggal_sidang',
                'nama_mitra_bestari',
                'evaluasi_logbook',
                'evaluasi_portofolio',
                'catatan_mitra_bestari',
                'daftar_rkk',
                'nomor_surat_rekomendasi',
                'tanggal_rekomendasi',
                'ketua_komite_medik',
                'catatan_rekomendasi',
                'status_rekomendasi',
                'tanggal_sah_spk',
            ]);
        });

        DB::statement("ALTER TABLE kredensial_klinis MODIFY COLUMN status_kredensial ENUM('aktif', 'evaluasi', 'kedaluwarsa') NOT NULL DEFAULT 'aktif'");
    }
};
