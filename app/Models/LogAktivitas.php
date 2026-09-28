<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'aksi', 'sumber_daya', 'keluhan_id', 'pesan'])]
class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';

    // Log hanya dicatat, tidak pernah diubah.
    public const UPDATED_AT = null;

    public static function catat(string $aksi, string $sumberDaya, string $pesan, ?int $keluhanId = null): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'aksi' => $aksi,
            'sumber_daya' => $sumberDaya,
            'keluhan_id' => $keluhanId,
            'pesan' => $pesan,
        ]);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
