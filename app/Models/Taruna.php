<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['nama', 'npm', 'tingkat', 'kelas', 'jenis_kelamin', 'kamar'])]
class Taruna extends Model
{
    protected $table = 'taruna';

    /** @return HasMany<Keluhan, $this> */
    public function keluhan(): HasMany
    {
        return $this->hasMany(Keluhan::class);
    }

    /** @return HasOne<Keluhan, $this> */
    public function keluhanTerakhir(): HasOne
    {
        return $this->hasOne(Keluhan::class)->latestOfMany();
    }
}
