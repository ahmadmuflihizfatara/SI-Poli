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
        Schema::table('keluhan', function (Blueprint $table) {
            // kosong untuk data lama: tampilan memakai tanggal_awal saja
            $table->dateTime('waktu_laporan')->nullable()->after('tanggal_awal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keluhan', function (Blueprint $table) {
            $table->dropColumn('waktu_laporan');
        });
    }
};
