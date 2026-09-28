<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['keluhan_id', 'tanggal_kontrol', 'hasil_kontrol', 'keterangan', 'perlu_rujukan'])]
class RiwayatKontrol extends Model
{
    protected $table = 'riwayat_kontrol';

    protected function casts(): array
    {
        return [
            'tanggal_kontrol' => 'date',
            'perlu_rujukan' => 'boolean',
        ];
    }

    /** @return BelongsTo<Keluhan, $this> */
    public function keluhan(): BelongsTo
    {
        return $this->belongsTo(Keluhan::class);
    }
}
