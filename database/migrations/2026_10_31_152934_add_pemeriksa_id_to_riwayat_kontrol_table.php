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
        Schema::table('riwayat_kontrol', function (Blueprint $table) {
            // pegawai yang memeriksa saat kontrol; kosong untuk data lama (tampilan memakai pencatat)
            $table->foreignId('pemeriksa_id')->nullable()->after('created_by')->constrained('pegawai')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_kontrol', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pemeriksa_id');
        });
    }
};
