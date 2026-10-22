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
            $table->string('fasyankes', 255)->default('RSUD dr. H. Moch. Ansari Saleh')->after('tempat_praktik_ke');
            $table->string('alamat_fasyankes', 255)->default('Jl. Brigjend H. Hasan Basry No. 1, Banjarmasin')->after('fasyankes');
            $table->string('penerbit_sip', 150)->default('DPMPTSP Kota Banjarmasin')->after('alamat_fasyankes');
            $table->boolean('status_kecukupan_skp')->default(false)->after('penerbit_sip');
            $table->string('nomor_rekomendasi_skp', 150)->nullable()->after('status_kecukupan_skp');
            $table->string('file_rekomendasi_skp')->nullable()->after('nomor_rekomendasi_skp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumen_sip', function (Blueprint $table) {
            $table->dropColumn([
                'fasyankes',
                'alamat_fasyankes',
                'penerbit_sip',
                'status_kecukupan_skp',
                'nomor_rekomendasi_skp',
                'file_rekomendasi_skp',
            ]);
        });
    }
};
