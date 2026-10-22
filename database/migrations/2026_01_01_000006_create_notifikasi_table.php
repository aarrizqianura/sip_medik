<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_sip')->nullable()->constrained('dokumen_sip')->nullOnDelete();
            $table->text('pesan');
            $table->enum('kanal', ['whatsapp', 'email', 'sistem'])->default('whatsapp');
            $table->boolean('status_terkirim')->default(false);
            $table->boolean('status_baca')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
