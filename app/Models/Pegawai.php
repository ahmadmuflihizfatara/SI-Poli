<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama', 'jabatan'])]
class Pegawai extends Model
{
    protected $table = 'pegawai';
}
