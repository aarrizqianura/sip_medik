<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dokters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->string('nip_nrp', 30)->unique();
            $table->foreignId('id_ksm')->constrained('ksm')->onDelete('restrict');
            $table->string('spesialisasi', 100);
            $table->enum('status_kepegawaian', ['PNS', 'PPPK', 'BLUD', 'Mitra'])->default('PNS');
            $table->enum('status_str', ['seumur_hidup', 'berkala'])->default('seumur_hidup');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('dokters');
    }
};
