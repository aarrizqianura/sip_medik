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
        Schema::table('dokumen_sip', function (Blueprint $table) {
            $table->string('status_dispensasi', 50)->default('none')->after('status_verifikasi'); // none, diajukan, disetujui, ditolak, berakhir
            $table->string('nomor_resi_proses', 150)->nullable()->after('status_dispensasi');
            $table->string('instansi_penerbit_resi', 150)->nullable()->after('nomor_resi_proses'); // DPMPTSP Kota Banjarmasin, Dinkes, dll
            $table->date('tanggal_pengajuan_resi')->nullable()->after('instansi_penerbit_resi');
            $table->string('file_bukti_proses')->nullable()->after('tanggal_pengajuan_resi');
            $table->text('keterangan_proses')->nullable()->after('file_bukti_proses');
            $table->integer('durasi_dispensasi_hari')->default(0)->after('keterangan_proses'); // 14-30 hari
            $table->date('tanggal_mulai_dispensasi')->nullable()->after('durasi_dispensasi_hari');
            $table->date('tanggal_akhir_dispensasi')->nullable()->after('tanggal_mulai_dispensasi');
            $table->text('catatan_verifikasi_dispensasi')->nullable()->after('tanggal_akhir_dispensasi');
            $table->foreignId('dispensasi_verified_by')->nullable()->after('catatan_verifikasi_dispensasi')->constrained('users')->nullOnDelete();
            $table->timestamp('dispensasi_verified_at')->nullable()->after('dispensasi_verified_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumen_sip', function (Blueprint $table) {
            $table->dropForeign(['dispensasi_verified_by']);
            $table->dropColumn([
                'status_dispensasi',
                'nomor_resi_proses',
                'instansi_penerbit_resi',
                'tanggal_pengajuan_resi',
                'file_bukti_proses',
                'keterangan_proses',
                'durasi_dispensasi_hari',
                'tanggal_mulai_dispensasi',
                'tanggal_akhir_dispensasi',
                'catatan_verifikasi_dispensasi',
                'dispensasi_verified_by',
                'dispensasi_verified_at',
            ]);
        });
    }
};
