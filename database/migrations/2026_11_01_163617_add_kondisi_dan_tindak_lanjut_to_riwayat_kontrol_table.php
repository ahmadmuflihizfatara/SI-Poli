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
        Schema::table('riwayat_kontrol', function (Blueprint $table) {
            // tanda vital saat kontrol (sama dengan kolom keluhan); kosong untuk kontrol lama
            $table->string('tekanan_darah', 20)->nullable()->after('hasil_kontrol');
            $table->decimal('suhu', 4, 1)->nullable()->after('tekanan_darah');
            $table->smallInteger('nadi')->unsigned()->nullable()->after('suhu');
            $table->tinyInteger('saturasi')->unsigned()->nullable()->after('nadi');
            $table->smallInteger('pernapasan')->unsigned()->nullable()->after('saturasi');
            $table->tinyInteger('skala_nyeri')->unsigned()->nullable()->after('pernapasan');
            // salinan terapi & jadwal yang dicatat saat kontrol ini (kolom keluhan selalu ditimpa kontrol terbaru)
            $table->text('terapi')->nullable()->after('skala_nyeri');
            $table->date('tanggal_kontrol_selanjutnya')->nullable()->after('tempat_rujukan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_kontrol', function (Blueprint $table) {
            $table->dropColumn(['tekanan_darah', 'suhu', 'nadi', 'saturasi', 'pernapasan', 'skala_nyeri', 'terapi', 'tanggal_kontrol_selanjutnya']);
        });
    }
};
