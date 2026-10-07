<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['taruna_id', 'kegiatan_pemeriksaan_id', 'jenis', 'periode', 'sesi', 'tekanan_darah', 'nadi', 'suhu', 'pernapasan', 'keluhan', 'terapi', 'keterangan'])]
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

    /** Label untuk log & tautan, mis. 'MPTB 2026 · Senin, 05 Oktober 2026 · Sesi pagi'. */
    public function labelPeriode(): string
    {
        return $this->jenis === 'MPTB'
            ? $this->kegiatan->nama.' · '.Carbon::parse($this->periode)->locale('id')->translatedFormat('l, d F Y').' · Sesi '.strtolower($this->sesi)
            : $this->kegiatan->nama;
    }

    public function url(): string
    {
        return route('pemeriksaan-kesehatan.show', $this->jenis === 'MPTB'
            ? ['kegiatan' => $this->kegiatan_pemeriksaan_id, 'tanggal' => $this->periode, 'sesi' => $this->sesi]
            : ['kegiatan' => $this->kegiatan_pemeriksaan_id]);
    }

    /** @return BelongsTo<KegiatanPemeriksaan, $this> */
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(KegiatanPemeriksaan::class, 'kegiatan_pemeriksaan_id');
    }

    /** @return BelongsTo<Taruna, $this> */
    public function taruna(): BelongsTo
    {
        return $this->belongsTo(Taruna::class);
    }
}
