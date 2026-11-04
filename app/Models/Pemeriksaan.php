<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['taruna_id', 'kegiatan_pemeriksaan_id', 'jenis', 'periode', 'sesi', 'tekanan_darah', 'nadi', 'suhu', 'saturasi', 'pernapasan', 'keluhan', 'terapi', 'keterangan'])]
class Pemeriksaan extends Model
{
    protected $table = 'pemeriksaan';

    /** Kolom yang diisi perawat di tabel input. */
    public const HASIL = ['tekanan_darah', 'nadi', 'suhu', 'saturasi', 'pernapasan', 'keluhan', 'terapi'];

    /** Baris dianggap "sudah diperiksa" kalau tanda vital + keluhan terisi (sama dengan status di tabel input). */
    public const WAJIB = ['tekanan_darah', 'nadi', 'suhu', 'saturasi', 'pernapasan', 'keluhan'];

    public const RUJUKAN_VITAL = [
        'tekanan_darah' => [
            'sistolik_min' => 110,
            'sistolik_max' => 120,
            'diastolik_min' => 70,
            'diastolik_max' => 80,
            'hipotensi_sistolik' => 90,
            'hipotensi_diastolik' => 60,
            'hipertensi_sistolik' => 140,
            'hipertensi_diastolik' => 90,
        ],
        'nadi' => ['min' => 60, 'max' => 100],
        'suhu' => ['min' => 36.5, 'max' => 37.5, 'hipotermia' => 36],
        'saturasi' => ['min' => 95, 'max' => 100, 'rendah_min' => 91],
        'pernapasan' => ['min' => 12, 'max' => 20],
    ];

    protected function casts(): array
    {
        return [
            'nadi' => 'integer',
            'suhu' => 'decimal:1',
            'saturasi' => 'integer',
            'pernapasan' => 'integer',
        ];
    }

    public static function statusVital(string $parameter, string|int|float|null $nilai): ?string
    {
        if ($nilai === null || trim((string) $nilai) === '') {
            return null;
        }

        $rujukan = self::RUJUKAN_VITAL[$parameter] ?? null;

        if ($rujukan === null) {
            return null;
        }

        if ($parameter === 'tekanan_darah') {
            if (! preg_match('/^\s*(\d{2,3})\s*\/\s*(\d{2,3})\s*$/', (string) $nilai, $matches)) {
                return null;
            }

            $sistolik = (int) $matches[1];
            $diastolik = (int) $matches[2];

            if (
                $sistolik < $rujukan['hipotensi_sistolik']
                || $diastolik < $rujukan['hipotensi_diastolik']
                || $sistolik >= $rujukan['hipertensi_sistolik']
                || $diastolik >= $rujukan['hipertensi_diastolik']
            ) {
                return 'danger';
            }

            return $sistolik < $rujukan['sistolik_min']
                || $sistolik > $rujukan['sistolik_max']
                || $diastolik < $rujukan['diastolik_min']
                || $diastolik > $rujukan['diastolik_max']
                ? 'warning'
                : null;
        }

        $angka = filter_var(str_replace(',', '.', (string) $nilai), FILTER_VALIDATE_FLOAT);

        if ($angka === false) {
            return null;
        }

        if ($parameter === 'saturasi' && $angka < $rujukan['min']) {
            return $angka >= $rujukan['rendah_min'] ? 'warning' : 'danger';
        }

        if ($parameter === 'suhu') {
            if ($angka < $rujukan['hipotermia']) {
                return 'danger';
            }

            if ($angka < $rujukan['min']) {
                return 'warning';
            }
        }

        return $angka < $rujukan['min'] || $angka > $rujukan['max'] ? 'danger' : null;
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
