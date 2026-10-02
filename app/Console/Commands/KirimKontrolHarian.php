<?php

namespace App\Console\Commands;

use App\Models\TelegramChat;
use App\Services\KontrolHariIni;
use App\Services\TelegramBot;
use Illuminate\Console\Command;

class KirimKontrolHarian extends Command
{
    protected $signature = 'telegram:kontrol-harian';

    protected $description = 'Kirim daftar taruna yang kontrol hari ini ke semua grup Telegram aktif';

    public function handle(TelegramBot $bot, KontrolHariIni $kontrol): int
    {
        $pesan = $kontrol->pesan();
        $terkirim = 0;
        // Satu grup gagal tidak menghentikan grup lain (kegagalan dicatat di TelegramBot::kirim)
        foreach (TelegramChat::where('aktif', true)->get() as $chat) {
            $terkirim += collect($pesan)->every(fn ($isi) => $bot->kirim($chat->chat_id, $isi));
        }
        $this->info("Terkirim ke {$terkirim} grup.");

        return self::SUCCESS;
    }
}
