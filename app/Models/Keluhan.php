<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'taruna_id', 'created_by', 'tanggal_awal', 'tanggal_kontrol_selanjutnya', 'keluhan', 'terapi', 'hasil_pemeriksaan',
    'status', 'status_pemulihan', 'tekanan_darah', 'suhu', 'nadi', 'saturasi', 'pernapasan',
    'skala_nyeri', 'ruang_kelas', 'ruang_kamar', 'keterangan',
])]
class Keluhan extends Model
{
    protected $table = 'keluhan';

    protected function casts(): array
    {
        return [
            'tanggal_awal' => 'date',
            'tanggal_kontrol_selanjutnya' => 'date',
        ];
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
