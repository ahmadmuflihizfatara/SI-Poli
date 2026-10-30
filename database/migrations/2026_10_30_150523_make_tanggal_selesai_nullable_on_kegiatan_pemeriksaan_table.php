<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kegiatan pemeriksaan tidak lagi punya rentang tanggal di depan: tanggal_selesai kosong = masih berjalan,
 * diisi saat perawat menekan "Selesaikan pemeriksaan".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan_pemeriksaan', function (Blueprint $table) {
            $table->date('tanggal_selesai')->nullable()->change();
        });

        // Kegiatan yang rentangnya belum lewat dianggap masih berjalan
        DB::table('kegiatan_pemeriksaan')->where('tanggal_selesai', '>=', today()->toDateString())->update(['tanggal_selesai' => null]);
    }

    public function down(): void
    {
        DB::table('kegiatan_pemeriksaan')->whereNull('tanggal_selesai')->update(['tanggal_selesai' => DB::raw('tanggal_mulai')]);

        Schema::table('kegiatan_pemeriksaan', function (Blueprint $table) {
            $table->date('tanggal_selesai')->nullable(false)->change();
        });
    }
};
