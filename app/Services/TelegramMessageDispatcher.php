<?php

namespace App\Services;

use App\Models\TelegramChat;
use App\Models\TelegramMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class TelegramMessageDispatcher
{
    public function __construct(private TelegramBot $bot) {}

    public function kirim(TelegramMessage $message): bool
    {
        $claimed = TelegramMessage::whereKey($message->id)
            ->where('status', TelegramMessage::STATUS_PENDING)
            ->update(['status' => TelegramMessage::STATUS_SENDING]);

        if ($claimed !== 1) {
            return false;
        }

        $message->refresh();
        $chats = TelegramChat::where('aktif', true)->orderBy('id')->get();

        if ($chats->isEmpty()) {
            $message->update([
                'status' => TelegramMessage::STATUS_FAILED,
                'failure_reason' => 'Tidak ada grup Telegram aktif.',
            ]);

            return false;
        }

        $gagal = 0;
        $isi = htmlspecialchars($message->isi, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        foreach ($chats as $chat) {
            try {
                if (! $this->bot->kirim($chat->chat_id, $isi)) {
                    $gagal++;
                }
            } catch (ConnectionException $exception) {
                $gagal++;
                Log::warning('Koneksi pengiriman pesan Telegram gagal', [
                    'message_id' => $message->id,
                    'chat_id' => $chat->chat_id,
                    'exception' => $exception::class,
                ]);
            }
        }

        $message->update([
            'status' => $gagal === 0 ? TelegramMessage::STATUS_SENT : TelegramMessage::STATUS_FAILED,
            'sent_at' => $gagal === 0 ? now() : null,
            'failure_reason' => $gagal === 0 ? null : "{$gagal} grup gagal menerima pesan.",
        ]);

        return $gagal === 0;
    }
}
