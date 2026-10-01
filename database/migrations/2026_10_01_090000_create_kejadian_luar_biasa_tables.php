<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kejadian Luar Biasa (KLB): laporan wabah sederhana, terpisah dari laporan kesehatan utama.
 * Data taruna diketik langsung di keluhan KLB (tidak terhubung ke tabel taruna/keluhan).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kejadian_luar_biasa', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->text('deskripsi');
            $table->enum('status', ['Berlangsung', 'Selesai'])->default('Berlangsung')->index();
            $table->timestamp('selesai_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('keluhan_klb', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kejadian_luar_biasa_id')->constrained('kejadian_luar_biasa')->cascadeOnDelete();
            $table->string('nama', 150);
            $table->string('npm', 20);
            $table->string('kelas', 20);
            $table->enum('tingkat', ['I', 'II', 'III', 'IV']);
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('kamar', 20);
            $table->text('keluhan');
            $table->text('terapi');
            $table->text('hasil_pemeriksaan')->nullable();
            $table->enum('status', ['Ringan', 'Sedang', 'Berat']);
            $table->date('tanggal_kontrol_selanjutnya');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('kontrol_klb', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keluhan_klb_id')->constrained('keluhan_klb')->cascadeOnDelete();
            $table->date('tanggal_kontrol');
            $table->text('hasil_kontrol');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kontrol_klb');
        Schema::dropIfExists('keluhan_klb');
        Schema::dropIfExists('kejadian_luar_biasa');
    }
};
