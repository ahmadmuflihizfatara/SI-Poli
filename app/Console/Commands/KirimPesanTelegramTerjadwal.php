<?php

namespace App\Console\Commands;

use App\Models\TelegramMessage;
use App\Services\TelegramMessageDispatcher;
use Illuminate\Console\Command;

class KirimPesanTelegramTerjadwal extends Command
{
    protected $signature = 'telegram:pesan-terjadwal';

    protected $description = 'Kirim pesan Telegram yang sudah jatuh tempo';

    public function handle(TelegramMessageDispatcher $dispatcher): int
    {
        $gagal = 0;

        TelegramMessage::where('status', TelegramMessage::STATUS_PENDING)
            ->where('send_at', '<=', now())
            ->orderBy('send_at')
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->each(function (TelegramMessage $message) use ($dispatcher, &$gagal): void {
                if (! $dispatcher->kirim($message) && $message->fresh()?->status === TelegramMessage::STATUS_FAILED) {
                    $gagal++;
                }
            });

        if ($gagal > 0) {
            $this->error("{$gagal} pesan gagal dikirim.");

            return self::FAILURE;
        }

        $this->info('Pesan terjadwal yang jatuh tempo telah diproses.');

        return self::SUCCESS;
    }
}
