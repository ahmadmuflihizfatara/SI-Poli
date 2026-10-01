<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['keluhan_klb_id', 'tanggal_kontrol', 'hasil_kontrol'])]
class KontrolKlb extends Model
{
    protected $table = 'kontrol_klb';

    protected function casts(): array
    {
        return ['tanggal_kontrol' => 'date'];
    }
}
