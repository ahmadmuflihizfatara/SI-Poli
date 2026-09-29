<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeriksaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taruna_id')->constrained('taruna')->cascadeOnDelete();
            $table->enum('jenis', ['MPTB', 'Samapta']);
            // MPTB: tanggal (2026-09-28), Samapta: semester (2026-ganjil)
            $table->string('periode', 20);
            // Pagi/Malam untuk MPTB, kosong untuk Samapta (bukan NULL supaya unique tetap berlaku)
            $table->string('sesi', 10)->default('');
            $table->string('tekanan_darah', 20)->nullable();
            $table->smallInteger('nadi')->unsigned()->nullable();
            $table->decimal('suhu', 4, 1)->nullable();
            $table->smallInteger('pernapasan')->unsigned()->nullable();
            $table->string('keluhan')->nullable();
            $table->string('terapi')->nullable();
            $table->enum('keterangan', ['Sudah membaik', 'Dalam perawatan'])->nullable();
            $table->timestamps();

            $table->unique(['jenis', 'periode', 'sesi', 'taruna_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan');
    }
};
