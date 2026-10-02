<?php

namespace App\Console\Commands;

use App\Services\TelegramBot;
use Illuminate\Console\Command;

class AturWebhookTelegram extends Command
{
    protected $signature = 'telegram:webhook';

    protected $description = 'Daftarkan URL webhook bot Telegram (jalankan sekali setelah deploy / ganti domain)';

    public function handle(TelegramBot $bot): int
    {
        $res = $bot->setWebhook(route('telegram.webhook'));
        $this->line($res->body());

        return $res->successful() && $res->json('ok') ? self::SUCCESS : self::FAILURE;
    }
}
