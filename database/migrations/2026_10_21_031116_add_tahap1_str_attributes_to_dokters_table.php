<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dokters', function (Blueprint $table) {
            $table->string('subspesialis', 150)->nullable()->after('spesialisasi');
            $table->tinyInteger('tempat_praktik_ke')->default(1)->after('subspesialis')->comment('Deklarasi praktik ke-1, 2, atau 3 di RSUD Dr. H. Moch. Ansari Saleh');
            $table->enum('status_verifikasi_str', ['belum_lengkap', 'menunggu_verifikasi', 'tervalidasi', 'ditolak'])
                ->default('belum_lengkap')
                ->after('file_str');
            $table->boolean('is_str_locked')->default(false)->after('status_verifikasi_str');
            $table->foreignId('verified_str_by')->nullable()->after('is_str_locked')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_str_at')->nullable()->after('verified_str_by');
            $table->text('catatan_verifikasi_str')->nullable()->after('verified_str_at');
            $table->timestamp('satusehat_verified_at')->nullable()->after('catatan_verifikasi_str');
            $table->json('satusehat_data')->nullable()->after('satusehat_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokters', function (Blueprint $table) {
            $table->dropForeign(['verified_str_by']);
            $table->dropColumn([
                'subspesialis',
                'tempat_praktik_ke',
                'status_verifikasi_str',
                'is_str_locked',
                'verified_str_by',
                'verified_str_at',
                'catatan_verifikasi_str',
                'satusehat_verified_at',
                'satusehat_data',
            ]);
        });
    }
};
