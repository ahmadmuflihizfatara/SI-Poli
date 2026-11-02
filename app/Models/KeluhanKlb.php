<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'kejadian_luar_biasa_id', 'nama', 'npm', 'kelas', 'tingkat', 'jenis_kelamin', 'kamar', 'waktu_laporan',
    'tekanan_darah', 'suhu', 'nadi', 'saturasi', 'pernapasan', 'skala_nyeri',
    'keluhan', 'terapi', 'hasil_pemeriksaan', 'keterangan', 'status', 'tanggal_kontrol_selanjutnya', 'created_by', 'pemeriksa_id',
])]
class KeluhanKlb extends Model
{
    protected $table = 'keluhan_klb';

    protected function casts(): array
    {
        return ['waktu_laporan' => 'datetime', 'tanggal_kontrol_selanjutnya' => 'date'];
    }

    /** @return BelongsTo<KejadianLuarBiasa, $this> */
    public function klb(): BelongsTo
    {
        return $this->belongsTo(KejadianLuarBiasa::class, 'kejadian_luar_biasa_id');
    }

    /** @return HasMany<KontrolKlb, $this> */
    public function kontrol(): HasMany
    {
        return $this->hasMany(KontrolKlb::class);
    }

    /** @return HasOne<KontrolKlb, $this> */
    public function kontrolTerakhir(): HasOne
    {
        return $this->hasOne(KontrolKlb::class)->latestOfMany();
    }

    /** @return BelongsTo<User, $this> */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<Pegawai, $this> */
    public function pemeriksa(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
