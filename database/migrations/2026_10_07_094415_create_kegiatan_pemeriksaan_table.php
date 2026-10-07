<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kegiatan pemeriksaan: satu MPTB/Samapta yang dibuat perawat (nama + rentang tanggal).
 * Hasil pemeriksaan per taruna kini dikelompokkan per kegiatan, bukan lagi per tanggal/semester saja.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan_pemeriksaan', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis', ['MPTB', 'Samapta']);
            $table->string('nama', 150);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('pemeriksaan', function (Blueprint $table) {
            $table->foreignId('kegiatan_pemeriksaan_id')->nullable()->after('taruna_id')->constrained('kegiatan_pemeriksaan')->cascadeOnDelete();
        });

        // Data lama: MPTB dikelompokkan per tahun, Samapta per semester (periode '2026-ganjil')
        $lama = DB::table('pemeriksaan')->select('jenis', 'periode')->distinct()->get()
            ->groupBy(fn ($p) => $p->jenis === 'MPTB' ? 'MPTB '.substr($p->periode, 0, 4) : $p->periode);
        foreach ($lama as $kunci => $grup) {
            $jenis = $grup->first()->jenis;
            if ($jenis === 'MPTB') {
                [$nama, $mulai, $selesai] = [$kunci, $grup->min('periode'), $grup->max('periode')];
            } else {
                [$tahun, $semester] = explode('-', $kunci);
                $nama = 'Samapta Semester '.ucfirst($semester).' '.$tahun.'/'.($tahun + 1);
                [$mulai, $selesai] = $semester === 'ganjil' ? ["$tahun-08-01", ($tahun + 1).'-01-31'] : [($tahun + 1).'-02-01', ($tahun + 1).'-07-31'];
            }
            $id = DB::table('kegiatan_pemeriksaan')->insertGetId([
                'jenis' => $jenis, 'nama' => $nama, 'tanggal_mulai' => $mulai, 'tanggal_selesai' => $selesai, 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('pemeriksaan')->where('jenis', $jenis)->whereIn('periode', $grup->pluck('periode'))->update(['kegiatan_pemeriksaan_id' => $id]);
        }

        Schema::table('pemeriksaan', function (Blueprint $table) {
            $table->dropUnique(['jenis', 'periode', 'sesi', 'taruna_id']);
            $table->unique(['kegiatan_pemeriksaan_id', 'periode', 'sesi', 'taruna_id'], 'pemeriksaan_kegiatan_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pemeriksaan', function (Blueprint $table) {
            // FK dulu: MySQL memakai index unik ini sebagai index foreign key
            $table->dropForeign(['kegiatan_pemeriksaan_id']);
            $table->dropUnique('pemeriksaan_kegiatan_unique');
            $table->dropColumn('kegiatan_pemeriksaan_id');
            $table->unique(['jenis', 'periode', 'sesi', 'taruna_id']);
        });
        Schema::dropIfExists('kegiatan_pemeriksaan');
    }
};
