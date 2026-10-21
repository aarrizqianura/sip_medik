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
            $table->string('nomor_str', 50)->nullable()->after('status_str');
            $table->date('tanggal_terbit_str')->nullable()->after('nomor_str');
            $table->date('tanggal_kadaluarsa_str')->nullable()->after('tanggal_terbit_str');
            $table->string('file_str')->nullable()->after('tanggal_kadaluarsa_str');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokters', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_str',
                'tanggal_terbit_str',
                'tanggal_kadaluarsa_str',
                'file_str',
            ]);
        });
    }
};
