<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'deskripsi', 'status', 'selesai_at', 'created_by'])]
class KejadianLuarBiasa extends Model
{
    protected $table = 'kejadian_luar_biasa';

    protected function casts(): array
    {
        return ['selesai_at' => 'datetime'];
    }

    public function selesai(): bool
    {
        return $this->status === 'Selesai';
    }

    /** @return HasMany<KeluhanKlb, $this> */
    public function keluhan(): HasMany
    {
        return $this->hasMany(KeluhanKlb::class);
    }
}
