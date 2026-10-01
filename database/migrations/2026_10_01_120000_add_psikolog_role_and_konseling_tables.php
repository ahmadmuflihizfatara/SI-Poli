<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Role Psikolog + laporan keluhan psikologi (terpisah dari keluhan medis perawat). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'perawat', 'psikolog'])->default('perawat')->change();
        });

        Schema::create('keluhan_psikologi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taruna_id')->constrained('taruna')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_awal');
            $table->text('keluhan');
            $table->text('terapi');
            $table->boolean('lanjut_konseling')->default(true)->index();
            $table->date('tanggal_konseling_selanjutnya')->nullable();
            // Kapan dinyatakan tidak melanjutkan konseling (untuk kartu "Selesai Konseling" per periode)
            $table->timestamp('selesai_at')->nullable();
            $table->timestamps();
        });

        Schema::create('riwayat_konseling', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keluhan_psikologi_id')->constrained('keluhan_psikologi')->cascadeOnDelete();
            $table->date('tanggal_konseling');
            $table->text('hasil_konseling');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_konseling');
        Schema::dropIfExists('keluhan_psikologi');
        DB::table('users')->where('role', 'psikolog')->update(['role' => 'perawat']);
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'perawat'])->default('perawat')->change();
        });
    }
};
