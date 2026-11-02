<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Keluhan & kontrol KLB memakai isian yang sama dengan Laporan Kesehatan (tanda vital, pemeriksa, obat, rujukan),
     * tetapi tanpa tingkat keparahan: kolom status jadi opsional (data lama tetap punya nilainya).
     */
    public function up(): void
    {
        Schema::table('keluhan_klb', function (Blueprint $table) {
            $table->foreignId('pemeriksa_id')->nullable()->after('created_by')->constrained('pegawai')->nullOnDelete();
            $table->dateTime('waktu_laporan')->nullable()->after('kamar');
            $table->string('tekanan_darah', 20)->nullable()->after('waktu_laporan');
            $table->decimal('suhu', 4, 1)->nullable()->after('tekanan_darah');
            $table->smallInteger('nadi')->unsigned()->nullable()->after('suhu');
            $table->tinyInteger('saturasi')->unsigned()->nullable()->after('nadi');
            $table->smallInteger('pernapasan')->unsigned()->nullable()->after('saturasi');
            $table->tinyInteger('skala_nyeri')->unsigned()->nullable()->after('pernapasan');
            $table->text('keterangan')->nullable()->after('hasil_pemeriksaan');
            $table->enum('status', ['Ringan', 'Sedang', 'Berat'])->nullable()->change();
        });

        Schema::table('kontrol_klb', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('keluhan_klb_id')->constrained('users')->nullOnDelete();
            $table->foreignId('pemeriksa_id')->nullable()->after('created_by')->constrained('pegawai')->nullOnDelete();
            $table->string('tekanan_darah', 20)->nullable()->after('hasil_kontrol');
            $table->decimal('suhu', 4, 1)->nullable()->after('tekanan_darah');
            $table->smallInteger('nadi')->unsigned()->nullable()->after('suhu');
            $table->tinyInteger('saturasi')->unsigned()->nullable()->after('nadi');
            $table->smallInteger('pernapasan')->unsigned()->nullable()->after('saturasi');
            $table->tinyInteger('skala_nyeri')->unsigned()->nullable()->after('pernapasan');
            $table->text('terapi')->nullable()->after('skala_nyeri');
            $table->boolean('perlu_rujukan')->default(false)->after('terapi');
            $table->string('tempat_rujukan', 150)->nullable()->after('perlu_rujukan');
            $table->date('tanggal_kontrol_selanjutnya')->nullable()->after('tempat_rujukan');
        });
    }

    public function down(): void
    {
        Schema::table('kontrol_klb', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('pemeriksa_id');
            $table->dropColumn(['tekanan_darah', 'suhu', 'nadi', 'saturasi', 'pernapasan', 'skala_nyeri', 'terapi', 'perlu_rujukan', 'tempat_rujukan', 'tanggal_kontrol_selanjutnya']);
        });

        Schema::table('keluhan_klb', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pemeriksa_id');
            $table->dropColumn(['waktu_laporan', 'tekanan_darah', 'suhu', 'nadi', 'saturasi', 'pernapasan', 'skala_nyeri', 'keterangan']);
        });
    }
};
