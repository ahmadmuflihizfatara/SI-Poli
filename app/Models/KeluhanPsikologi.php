<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['taruna_id', 'created_by', 'tanggal_awal', 'keluhan', 'terapi', 'lanjut_konseling', 'tanggal_konseling_selanjutnya', 'selesai_at'])]
class KeluhanPsikologi extends Model
{
    protected $table = 'keluhan_psikologi';

    protected function casts(): array
    {
        return [
            'tanggal_awal' => 'date',
            'lanjut_konseling' => 'boolean',
            'tanggal_konseling_selanjutnya' => 'date',
            'selesai_at' => 'datetime',
        ];
    }

    public function keterangan(): string
    {
        return $this->lanjut_konseling ? 'Melanjutkan konseling' : 'Tidak melanjutkan konseling';
    }

    /** @return BelongsTo<Taruna, $this> */
    public function taruna(): BelongsTo
    {
        return $this->belongsTo(Taruna::class);
    }

    /** @return HasMany<RiwayatKonseling, $this> */
    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatKonseling::class);
    }

    /** @return HasOne<RiwayatKonseling, $this> */
    public function konselingTerakhir(): HasOne
    {
        return $this->hasOne(RiwayatKonseling::class)->latestOfMany();
    }
}
