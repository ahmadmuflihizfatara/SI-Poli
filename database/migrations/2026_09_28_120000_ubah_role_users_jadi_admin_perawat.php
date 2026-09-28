<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Aplikasi hanya punya dua role: admin dan perawat (dulu petugas_kesehatan). */
return new class extends Migration
{
    public function up(): void
    {
        // Lebarkan enum dulu supaya data lama bisa dipindah sebelum nilai lama dibuang.
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'petugas_kesehatan', 'perawat'])->default('perawat')->change();
        });
        DB::table('users')->where('role', 'petugas_kesehatan')->update(['role' => 'perawat']);
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'perawat'])->default('perawat')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'petugas_kesehatan', 'perawat'])->default('petugas_kesehatan')->change();
        });
        DB::table('users')->where('role', 'perawat')->update(['role' => 'petugas_kesehatan']);
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'petugas_kesehatan'])->default('petugas_kesehatan')->change();
        });
    }
};
