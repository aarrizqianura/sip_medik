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
        // 1. Modifikasi kolom status di dokumen_sip agar mendukung status 'critical'
        DB::statement("ALTER TABLE dokumen_sip MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'aktif'");

        // 2. Tambahan kolom metadata eskalasi pada dokumen_sip
        Schema::table('dokumen_sip', function (Blueprint $table) {
            $table->string('tingkat_eskalasi', 50)->default('aman')->after('status');
            $table->timestamp('terakhir_notifikasi_dokter_at')->nullable()->after('tingkat_eskalasi');
            $table->timestamp('terakhir_eskalasi_sdm_at')->nullable()->after('terakhir_notifikasi_dokter_at');
            $table->text('log_eskalasi_terakhir')->nullable()->after('terakhir_eskalasi_sdm_at');
        });

        // 3. Tambahan kolom metadata eskalasi pada notifikasi
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->string('tingkat_eskalasi', 50)->nullable()->after('id_sip');
            $table->string('tipe_penerima', 50)->default('dokter')->after('tingkat_eskalasi'); // dokter, admin_sdm, staf_sdm
            $table->json('metadata')->nullable()->after('status_baca');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->dropColumn(['tingkat_eskalasi', 'tipe_penerima', 'metadata']);
        });

        Schema::table('dokumen_sip', function (Blueprint $table) {
            $table->dropColumn([
                'tingkat_eskalasi',
                'terakhir_notifikasi_dokter_at',
                'terakhir_eskalasi_sdm_at',
                'log_eskalasi_terakhir',
            ]);
        });

        DB::statement("ALTER TABLE dokumen_sip MODIFY COLUMN status ENUM('aktif', 'due_soon', 'due', 'overdue') NOT NULL DEFAULT 'aktif'");
    }
};
