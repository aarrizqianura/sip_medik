<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tahap 7: Mitigasi Otomatis saat Kedaluwarsa (Overdue & Auto-Suspend)
     */
    public function up(): void
    {
        // 1. Dokumen SIP: Penguncian status kedaluwarsa otomatis (Overdue Lock)
        Schema::table('dokumen_sip', function (Blueprint $table) {
            $table->boolean('is_overdue_locked')->default(false)->after('is_sip_locked');
            $table->timestamp('overdue_locked_at')->nullable()->after('is_overdue_locked');
        });

        // 2. Dokter: Status penangguhan administratif dan pembatasan wewenang klinis mandiri
        Schema::table('dokters', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable()->after('status_praktik');
            $table->text('alasan_penangguhan')->nullable()->after('suspended_at');
        });

        // 3. Kredensial Klinis (SPK & RKK): Penangguhan otomatis instrumen wewenang klinis
        Schema::table('kredensial_klinis', function (Blueprint $table) {
            $table->boolean('is_suspended')->default(false)->after('status_kredensial');
            $table->timestamp('suspended_at')->nullable()->after('is_suspended');
            $table->text('alasan_penangguhan')->nullable()->after('suspended_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kredensial_klinis', function (Blueprint $table) {
            $table->dropColumn(['is_suspended', 'suspended_at', 'alasan_penangguhan']);
        });

        Schema::table('dokters', function (Blueprint $table) {
            $table->dropColumn(['suspended_at', 'alasan_penangguhan']);
        });

        Schema::table('dokumen_sip', function (Blueprint $table) {
            $table->dropColumn(['is_overdue_locked', 'overdue_locked_at']);
        });
    }
};
