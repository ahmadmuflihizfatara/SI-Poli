<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'taruna_id', 'created_by', 'tanggal_awal', 'waktu_laporan', 'tanggal_kontrol_selanjutnya', 'keluhan', 'terapi', 'hasil_pemeriksaan',
    'status', 'status_pemulihan', 'tekanan_darah', 'suhu', 'nadi', 'saturasi', 'pernapasan',
    'skala_nyeri', 'ruang_kelas', 'ruang_kamar', 'keterangan',
])]
class Keluhan extends Model
{
    /** Urutan bagian tiap baris obat di kolom terapi: "terapi/obat · aturan pakai · waktu minum". */
    public const BAGIAN_OBAT = ['nama', 'aturan', 'waktu'];

    /** Pilihan waktu minum obat; "Lainnya" diganti keterangan yang diketik petugas. */
    public const WAKTU_OBAT = ['Sebelum makan', 'Sesudah makan', 'Lainnya'];

    private const PEMISAH_OBAT = ' · ';

    /** Pintasan di kalender tanggal kontrol selanjutnya: label => selisih hari dari hari ini. */
    public const PINTASAN_KONTROL = ['Besok' => 1, '3 hari lagi' => 3, '4 hari lagi' => 4, '5 hari lagi' => 5, '1 minggu' => 7];

    protected $table = 'keluhan';

    protected function casts(): array
    {
        return [
            'tanggal_awal' => 'date',
            'waktu_laporan' => 'datetime',
            'tanggal_kontrol_selanjutnya' => 'date',
        ];
    }

    /**
     * Gabungkan isian obat dari formulir menjadi teks terapi, satu obat per baris.
     * Bagian kosong di tengah ditulis "-", di ujung dibuang (obat tanpa rincian tetap terbaca sebagai namanya saja).
     * Waktu minum "Lainnya" disimpan sebagai keterangannya.
     *
     * @param  list<array{nama: string, aturan?: ?string, waktu?: ?string, waktu_lain?: ?string}>  $obat
     */
    public static function terapiDariObat(array $obat): string
    {
        return collect($obat)->map(function (array $o) {
            if (($o['waktu'] ?? null) === 'Lainnya') {
                $o['waktu'] = $o['waktu_lain'] ?? null;
            }
            $bagian = array_map(fn (string $k) => trim(str_replace(['·', "\r", "\n"], ' ', $o[$k] ?? '')) ?: '-', self::BAGIAN_OBAT);
            while (end($bagian) === '-') {
                array_pop($bagian);
            }

            return implode(self::PEMISAH_OBAT, $bagian);
        })->implode("\n");
    }

    /**
     * Uraikan teks terapi menjadi daftar obat. Baris lama berupa teks bebas masuk ke "nama" seluruhnya;
     * bagian ketiga dan seterusnya terbaca sebagai waktu minum (baris format lama "nama · dosis · aturan · durasi" tetap utuh).
     *
     * @return list<array{nama: string, aturan: ?string, waktu: ?string}>
     */
    public function daftarObat(): array
    {
        return collect(preg_split('/\r?\n/', trim((string) $this->terapi)))->filter()
            ->map(function (string $baris) {
                $bagian = array_pad(array_map('trim', explode(trim(self::PEMISAH_OBAT), $baris, 3)), 3, null);

                return array_combine(self::BAGIAN_OBAT, array_map(fn (?string $b) => $b === '-' || $b === '' ? null : $b, $bagian));
            })
            ->values()->all();
    }

    /** @return BelongsTo<Taruna, $this> */
    public function taruna(): BelongsTo
    {
        return $this->belongsTo(Taruna::class);
    }

    /** @return BelongsTo<User, $this> */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<RiwayatKontrol, $this> */
    public function riwayatKontrol(): HasMany
    {
        return $this->hasMany(RiwayatKontrol::class);
    }
}
