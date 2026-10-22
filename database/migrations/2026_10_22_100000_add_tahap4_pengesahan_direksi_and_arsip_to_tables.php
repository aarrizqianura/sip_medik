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
        // 1. Tambahan pada dokters: status praktik legal, penguncian e-Arsip digital, & SK Direktur
        Schema::table('dokters', function (Blueprint $table) {
            $table->string('status_praktik', 50)->default('BELUM_LENGKAP')->after('satusehat_data');
            $table->boolean('is_arsip_locked')->default(false)->after('status_praktik');
            $table->timestamp('arsip_locked_at')->nullable()->after('is_arsip_locked');
            $table->foreignId('arsip_locked_by')->nullable()->after('arsip_locked_at')->constrained('users')->nullOnDelete();
            $table->string('nomor_sk_direktur', 150)->nullable()->after('arsip_locked_by');
            $table->date('tanggal_sk_direktur')->nullable()->after('nomor_sk_direktur');
            $table->string('file_sk_direktur')->nullable()->after('tanggal_sk_direktur');
            $table->text('catatan_pengesahan_direksi')->nullable()->after('file_sk_direktur');
        });

        // 2. Tambahan pada kredensial_klinis: penguncian SPK & RKK serta metadata SK Direktur
        Schema::table('kredensial_klinis', function (Blueprint $table) {
            $table->string('nomor_sk_direktur', 150)->nullable()->after('nomor_spk');
            $table->date('tanggal_sk_direktur')->nullable()->after('tanggal_terbit_spk');
            $table->string('file_sk_direktur')->nullable()->after('file_rkk');
            $table->boolean('is_spk_locked')->default(false)->after('file_sk_direktur');
            $table->boolean('is_rkk_locked')->default(false)->after('is_spk_locked');
            $table->foreignId('disahkan_oleh_sdm')->nullable()->after('is_rkk_locked')->constrained('users')->nullOnDelete();
        });

        // 3. Tambahan pada dokumen_sip: penguncian SIP ke dalam e-Arsip
        Schema::table('dokumen_sip', function (Blueprint $table) {
            $table->boolean('is_sip_locked')->default(false)->after('status_verifikasi');
            $table->timestamp('arsip_locked_at')->nullable()->after('is_sip_locked');
        });

        // 4. Inisialisasi awal status profil dokter yang telah tervalidasi STR & memiliki SPK aktif
        DB::table('dokters')->where('is_str_locked', true)->update([
            'status_praktik' => 'AKTIF_LEGAL',
            'is_arsip_locked' => true,
            'arsip_locked_at' => now(),
        ]);
        DB::table('dokumen_sip')->where('status_verifikasi', 'disetujui')->update([
            'is_sip_locked' => true,
            'arsip_locked_at' => now(),
        ]);
        DB::table('kredensial_klinis')->where('status_kredensial', 'aktif')->update([
            'is_spk_locked' => true,
            'is_rkk_locked' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumen_sip', function (Blueprint $table) {
            $table->dropColumn(['is_sip_locked', 'arsip_locked_at']);
        });

        Schema::table('kredensial_klinis', function (Blueprint $table) {
            $table->dropForeign(['disahkan_oleh_sdm']);
            $table->dropColumn([
                'nomor_sk_direktur',
                'tanggal_sk_direktur',
                'file_sk_direktur',
                'is_spk_locked',
                'is_rkk_locked',
                'disahkan_oleh_sdm',
            ]);
        });

        Schema::table('dokters', function (Blueprint $table) {
            $table->dropForeign(['arsip_locked_by']);
            $table->dropColumn([
                'status_praktik',
                'is_arsip_locked',
                'arsip_locked_at',
                'arsip_locked_by',
                'nomor_sk_direktur',
                'tanggal_sk_direktur',
                'file_sk_direktur',
                'catatan_pengesahan_direksi',
            ]);
        });
    }
};
