<?php

namespace App\Http\Controllers;

use App\Models\TelegramChat;
use App\Services\KontrolHariIni;
use App\Services\TelegramBot;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/** Menerima update dari Telegram: bot ditambahkan/dikeluarkan dari grup, dan perintah /start, /hariini, /ringkasan. */
class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, TelegramBot $bot, KontrolHariIni $kontrol): Response
    {
        $rahasia = (string) config('services.telegram.webhook_secret');
        abort_unless($rahasia !== '' && hash_equals($rahasia, (string) $request->header('X-Telegram-Bot-Api-Secret-Token')), 403);

        if ($anggota = $request->input('my_chat_member')) {
            $this->keanggotaan($anggota, $bot);
        } elseif ($pesan = $request->input('message')) {
            $this->perintah($pesan, $bot, $kontrol);
        }

        return response('ok');
    }

    /** Bot masuk/keluar grup. Hanya ID Telegram di TELEGRAM_ADMIN_IDS yang boleh menambahkan (kosong = terbuka untuk siapa saja). */
    private function keanggotaan(array $u, TelegramBot $bot): void
    {
        $chat = $u['chat'] ?? [];
        if (! in_array($chat['type'] ?? '', ['group', 'supergroup'], true)) {
            return;
        }

        if (in_array($u['new_chat_member']['status'] ?? '', ['member', 'administrator'], true)) {
            if (! $this->diizinkan($u['from']['id'] ?? 0)) {
                $bot->kirim($chat['id'], 'Maaf, bot ini hanya boleh ditambahkan oleh pengelola SI-Poli.');
                $bot->keluar($chat['id']);

                return;
            }
            TelegramChat::updateOrCreate(['chat_id' => $chat['id']], [
                'judul' => $chat['title'] ?? null, 'tipe' => $chat['type'], 'aktif' => true, 'ditambahkan_oleh' => $u['from']['id'] ?? null,
            ]);
            $bot->kirim($chat['id'], 'Halo! Setiap hari bot ini mengirim daftar taruna yang jadwal kontrolnya hari itu. Ketik /hariini untuk melihatnya sekarang, atau /ringkasan untuk ringkasan kesehatan hari ini.');
        } else {
            TelegramChat::where('chat_id', $chat['id'])->update(['aktif' => false]);
        }
    }

    private function perintah(array $pesan, TelegramBot $bot, KontrolHariIni $kontrol): void
    {
        $chatId = $pesan['chat']['id'] ?? null;
        $perintah = Str::before(Str::before(trim($pesan['text'] ?? ''), ' '), '@');
        if (! $chatId || ! in_array($perintah, ['/start', '/hariini', '/ringkasan'], true)) {
            return;
        }

        if ($perintah === '/start') {
            $bot->kirim($chatId, 'Bot SI-Poli: mengirim daftar taruna yang kontrol hari ini. Tambahkan ke grup, atau ketik /hariini dan /ringkasan.');

            return;
        }

        // Data taruna hanya untuk grup terdaftar atau pengelola
        if (TelegramChat::where('chat_id', $chatId)->where('aktif', true)->exists() || $this->diizinkan($pesan['from']['id'] ?? 0, tegas: true)) {
            foreach ($perintah === '/ringkasan' ? [$kontrol->ringkasan()] : $kontrol->pesan() as $isi) {
                $bot->kirim($chatId, $isi);
            }
        }
    }

    /** $tegas: daftar admin kosong tidak berarti terbuka (dipakai untuk chat yang belum terdaftar). */
    private function diizinkan(int $idTelegram, bool $tegas = false): bool
    {
        $admin = config('services.telegram.admin_ids');

        return $admin ? in_array($idTelegram, $admin, true) : ! $tegas;
    }
}
