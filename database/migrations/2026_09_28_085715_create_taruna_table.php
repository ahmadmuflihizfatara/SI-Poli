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
        Schema::create('taruna', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->string('npm', 20)->unique();
            $table->enum('tingkat', ['I', 'II', 'III', 'IV']);
            $table->string('kelas', 20);
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('kamar', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taruna');
    }
};
