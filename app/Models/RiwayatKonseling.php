<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['keluhan_psikologi_id', 'tanggal_konseling', 'hasil_konseling'])]
class RiwayatKonseling extends Model
{
    protected $table = 'riwayat_konseling';

    protected function casts(): array
    {
        return ['tanggal_konseling' => 'date'];
    }
}
