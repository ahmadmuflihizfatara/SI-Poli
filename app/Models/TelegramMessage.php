<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['template_id', 'isi', 'send_at', 'status', 'sent_at', 'failure_reason'])]
class TelegramMessage extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    /** @return BelongsTo<TelegramMessageTemplate, $this> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(TelegramMessageTemplate::class, 'template_id');
    }

    protected function casts(): array
    {
        return [
            'send_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }
}
