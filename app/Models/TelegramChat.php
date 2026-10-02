<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['chat_id', 'judul', 'tipe', 'aktif', 'ditambahkan_oleh'])]
class TelegramChat extends Model
{
    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }
}
