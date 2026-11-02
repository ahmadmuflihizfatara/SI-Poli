<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'keluhan_klb_id', 'created_by', 'pemeriksa_id', 'tanggal_kontrol', 'hasil_kontrol', 'perlu_rujukan', 'tempat_rujukan',
    'tekanan_darah', 'suhu', 'nadi', 'saturasi', 'pernapasan', 'skala_nyeri', 'terapi', 'tanggal_kontrol_selanjutnya',
])]
class KontrolKlb extends Model
{
    protected $table = 'kontrol_klb';

    protected function casts(): array
    {
        return ['tanggal_kontrol' => 'date', 'tanggal_kontrol_selanjutnya' => 'date', 'perlu_rujukan' => 'boolean'];
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
