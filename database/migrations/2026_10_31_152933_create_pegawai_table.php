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
        // tenaga kesehatan poliklinik (dokter, perawat) yang bisa dipilih sebagai pemeriksa; terpisah dari akun pengguna
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('jabatan', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
