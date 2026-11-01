<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'taruna_id', 'created_by', 'pemeriksa_id', 'tanggal_awal', 'waktu_laporan', 'tanggal_kontrol_selanjutnya', 'keluhan', 'terapi', 'hasil_pemeriksaan',
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

    /** Isian terapi terstruktur: satu baris per obat (lihat terapiDariObat). Dipakai keluhan baru dan catat kontrol. */
    public const ATURAN_OBAT = [
        'obat' => 'required|array|min:1',
        'obat.*.nama' => 'required|string|max:100',
        'obat.*.aturan' => 'nullable|string|max:100',
        'obat.*.waktu' => 'nullable|in:Sebelum makan,Sesudah makan,Lainnya',
        'obat.*.waktu_lain' => 'nullable|required_if:obat.*.waktu,Lainnya|string|max:100',
    ];

    public const LABEL_OBAT = [
        'obat' => 'terapi dan obat', 'obat.*.nama' => 'terapi/obat yang diberikan', 'obat.*.aturan' => 'aturan pakai',
        'obat.*.waktu' => 'waktu minum', 'obat.*.waktu_lain' => 'keterangan waktu minum',
    ];

    /** Batas isian tanda vital [min, max]; di luar ini hampir pasti salah ketik. Dipakai validasi dan atribut min/max formulir. */
    public const BATAS_VITAL = [
        'sistolik' => [50, 250], 'diastolik' => [30, 150], 'suhu' => [30, 45],
        'nadi' => [30, 220], 'saturasi' => [50, 100], 'pernapasan' => [5, 60],
    ];

    public const LABEL_VITAL = ['sistolik' => 'tekanan sistolik', 'diastolik' => 'tekanan diastolik', 'saturasi' => 'saturasi oksigen', 'skala_nyeri' => 'skala nyeri'];

    public const PESAN_VITAL = ['diastolik.lt' => 'Tekanan diastolik harus lebih kecil dari sistolik.'];

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
     * Aturan isian Kondisi Kesehatan (tanda vital). Tekanan darah datang sebagai sistolik + diastolik. Dipakai keluhan baru dan catat kontrol.
     *
     * @return array<string, list<string>>
     */
    public static function aturanVital(): array
    {
        $batas = fn (string $kolom): string => 'between:'.implode(',', self::BATAS_VITAL[$kolom]);

        return [
            'sistolik' => ['required', 'integer', $batas('sistolik')],
            'diastolik' => ['required', 'integer', $batas('diastolik'), 'lt:sistolik'],
            'suhu' => ['required', 'numeric', $batas('suhu')],
            'nadi' => ['required', 'integer', $batas('nadi')],
            'saturasi' => ['nullable', 'integer', $batas('saturasi')],
            'pernapasan' => ['nullable', 'integer', $batas('pernapasan')],
            'skala_nyeri' => ['nullable', 'integer', 'between:0,10'],
        ];
    }

    /**
     * Kolom tanda vital dari isian tervalidasi (lihat aturanVital); sistolik + diastolik digabung jadi "120/80".
     *
     * @param  array<string, mixed>  $data
     * @return array{tekanan_darah: string, suhu: mixed, nadi: mixed, saturasi: mixed, pernapasan: mixed, skala_nyeri: mixed}
     */
    public static function kondisiDariIsian(array $data): array
    {
        return [
            'tekanan_darah' => "{$data['sistolik']}/{$data['diastolik']}",
            'suhu' => $data['suhu'],
            'nadi' => $data['nadi'],
            'saturasi' => $data['saturasi'] ?? null,
            'pernapasan' => $data['pernapasan'] ?? null,
            'skala_nyeri' => $data['skala_nyeri'] ?? null,
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
        return self::obatDariTerapi($this->terapi);
    }

    /**
     * Uraikan teks terapi (kolom keluhan atau riwayat kontrol) menjadi daftar obat; lihat daftarObat.
     *
     * @return list<array{nama: string, aturan: ?string, waktu: ?string}>
     */
    public static function obatDariTerapi(?string $terapi): array
    {
        return collect(preg_split('/\r?\n/', trim((string) $terapi)))->filter()
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

    /** @return BelongsTo<Pegawai, $this> */
    public function pemeriksa(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
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
