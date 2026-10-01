<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'kejadian_luar_biasa_id', 'nama', 'npm', 'kelas', 'tingkat', 'jenis_kelamin', 'kamar',
    'keluhan', 'terapi', 'hasil_pemeriksaan', 'status', 'tanggal_kontrol_selanjutnya', 'created_by',
])]
class KeluhanKlb extends Model
{
    protected $table = 'keluhan_klb';

    protected function casts(): array
    {
        return ['tanggal_kontrol_selanjutnya' => 'date'];
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
}
