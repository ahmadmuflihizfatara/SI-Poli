<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data Taruna: hanya nama yang wajib. Keluhan KLB menyalin identitas taruna, jadi kolom salinannya ikut opsional.
     */
    public function up(): void
    {
        foreach (['taruna', 'keluhan_klb'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->string('npm', 20)->nullable()->change();
                $table->enum('tingkat', ['I', 'II', 'III', 'IV'])->nullable()->change();
                $table->string('kelas', 20)->nullable()->change();
                $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable()->change();
                $table->string('kamar', 20)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['taruna', 'keluhan_klb'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->string('npm', 20)->nullable(false)->change();
                $table->enum('tingkat', ['I', 'II', 'III', 'IV'])->nullable(false)->change();
                $table->string('kelas', 20)->nullable(false)->change();
                $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable(false)->change();
                $table->string('kamar', 20)->nullable(false)->change();
            });
        }
    }
};
