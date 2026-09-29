<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['taruna_id', 'jenis', 'periode', 'sesi', 'tekanan_darah', 'nadi', 'suhu', 'pernapasan', 'keluhan', 'terapi', 'keterangan'])]
class Pemeriksaan extends Model
{
    protected $table = 'pemeriksaan';

    /** Kolom yang diisi perawat di tabel input. */
    public const HASIL = ['tekanan_darah', 'nadi', 'suhu', 'pernapasan', 'keluhan', 'terapi', 'keterangan'];

    /** Baris dianggap "sudah diperiksa" kalau tanda vital + keluhan terisi (sama dengan status di tabel input). */
    public const WAJIB = ['tekanan_darah', 'nadi', 'suhu', 'pernapasan', 'keluhan'];

    protected function casts(): array
    {
        return [
            'nadi' => 'integer',
            'suhu' => 'decimal:1',
            'pernapasan' => 'integer',
        ];
    }

    /** '2026-ganjil' → 'Ganjil 2026/2027' */
    public static function labelSemester(string $semester): string
    {
        [$tahun, $jenis] = explode('-', $semester);

        return ucfirst($jenis).' '.$tahun.'/'.($tahun + 1);
    }

    /** Semester berjalan: Ganjil Agustus–Januari, Genap Februari–Juli. */
    public static function semesterBerjalan(): string
    {
        $bulan = today()->month;
        $tahun = $bulan >= 8 ? today()->year : today()->year - 1;

        return $tahun.'-'.($bulan >= 8 || $bulan === 1 ? 'ganjil' : 'genap');
    }

    public function labelPeriode(): string
    {
        return $this->jenis === 'MPTB'
            ? Carbon::parse($this->periode)->locale('id')->translatedFormat('l, d F Y').' · Sesi '.strtolower($this->sesi)
            : 'Semester '.self::labelSemester($this->periode);
    }

    public function url(): string
    {
        return $this->jenis === 'MPTB'
            ? route('pemeriksaan-kesehatan.mptb.index', ['tanggal' => $this->periode, 'sesi' => $this->sesi])
            : route('pemeriksaan-kesehatan.samapta.index', ['semester' => $this->periode]);
    }

    /** @return BelongsTo<Taruna, $this> */
    public function taruna(): BelongsTo
    {
        return $this->belongsTo(Taruna::class);
    }
}
