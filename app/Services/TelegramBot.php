<?php

namespace App\Services;

use App\Models\TelegramChat;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Pembungkus tipis Telegram Bot API. */
class TelegramBot
{
    public function panggil(string $metode, array $data = []): Response
    {
        return Http::timeout(15)->post('https://api.telegram.org/bot'.config('services.telegram.token').'/'.$metode, $data);
    }

    /**
     * Kirim teks HTML ke chat. Bot dikeluarkan/diblokir (403) → grup dinonaktifkan;
     * grup naik jadi supergroup (400 + migrate_to_chat_id) → chat_id diperbarui dan dicoba sekali lagi.
     */
    public function kirim(int $chatId, string $html): bool
    {
        $res = $this->panggil('sendMessage', ['chat_id' => $chatId, 'text' => $html, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true]);
        if ($res->successful()) {
            return true;
        }

        if ($res->status() === 403) {
            TelegramChat::where('chat_id', $chatId)->update(['aktif' => false]);
        } elseif ($baru = $res->json('parameters.migrate_to_chat_id')) {
            TelegramChat::where('chat_id', $chatId)->update(['chat_id' => $baru]);

            return $this->kirim((int) $baru, $html);
        }
        Log::warning('Telegram sendMessage gagal', ['chat_id' => $chatId, 'status' => $res->status(), 'body' => $res->body()]);

        return false;
    }

    public function keluar(int $chatId): void
    {
        $this->panggil('leaveChat', ['chat_id' => $chatId]);
    }

    public function setWebhook(string $url): Response
    {
        return $this->panggil('setWebhook', [
            'url' => $url,
            'secret_token' => config('services.telegram.webhook_secret'),
            'allowed_updates' => ['message', 'my_chat_member'],
        ]);
    }
}
