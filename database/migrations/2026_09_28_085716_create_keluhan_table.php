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
        Schema::create('keluhan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taruna_id')->constrained('taruna')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_awal');
            $table->text('keluhan');
            $table->text('terapi');
            $table->text('hasil_pemeriksaan')->nullable();
            $table->enum('status', ['Ringan', 'Sedang', 'Berat'])->index();
            $table->string('tekanan_darah', 20)->nullable();
            $table->decimal('suhu', 4, 1)->nullable();
            $table->smallInteger('nadi')->unsigned()->nullable();
            $table->tinyInteger('saturasi')->unsigned()->nullable();
            $table->smallInteger('pernapasan')->unsigned()->nullable();
            $table->tinyInteger('skala_nyeri')->unsigned()->nullable();
            $table->string('ruang_kelas', 50)->nullable();
            $table->string('ruang_kamar', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keluhan');
    }
};
