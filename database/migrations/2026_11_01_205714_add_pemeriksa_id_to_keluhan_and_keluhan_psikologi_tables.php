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
        // pegawai yang memeriksa saat keluhan ditambahkan; kosong untuk data lama (tampilan memakai akun pencatat)
        foreach (['keluhan', 'keluhan_psikologi'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->foreignId('pemeriksa_id')->nullable()->after('created_by')->constrained('pegawai')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['keluhan', 'keluhan_psikologi'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->dropConstrainedForeignId('pemeriksa_id');
            });
        }
    }
};
