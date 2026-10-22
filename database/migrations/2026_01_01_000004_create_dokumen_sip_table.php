<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_sip', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dokter')->constrained('dokters')->onDelete('cascade');
            $table->string('nomor_sip', 100);
            $table->tinyInteger('tempat_praktik_ke')->default(1);
            $table->date('tanggal_terbit');
            $table->date('tanggal_kadaluarsa')->index();
            $table->enum('status', ['aktif', 'due_soon', 'due', 'overdue'])->default('aktif');
            $table->enum('status_verifikasi', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->string('file_sip');
            $table->text('catatan_verifikasi')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_sip');
    }
};
