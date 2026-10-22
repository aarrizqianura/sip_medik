<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kredensial_klinis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dokter')->constrained('dokters')->onDelete('cascade');
            $table->string('nomor_spk', 100);
            $table->date('tanggal_terbit_spk');
            $table->date('tanggal_berakhir_spk');
            $table->enum('status_kredensial', ['aktif', 'evaluasi', 'kedaluwarsa'])->default('aktif');
            $table->string('file_rkk')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kredensial_klinis');
    }
};
