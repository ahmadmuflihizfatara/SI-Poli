<?php

namespace App\Http\Controllers;

use App\Models\TelegramChat;
use App\Models\TelegramMessage;
use App\Models\TelegramMessageTemplate;
use App\Services\TelegramMessageDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManajemenBotController extends Controller
{
    public function index(Request $request): View
    {
        $template = $request->filled('template')
            ? TelegramMessageTemplate::findOrFail($request->integer('template'))
            : null;

        return view('manajemen-bot.index', [
            'template' => $template,
            'templates' => TelegramMessageTemplate::orderBy('nama')->get(),
            'messages' => TelegramMessage::with('template')->latest('created_at')->latest('id')->paginate(15),
            'jumlahGrupAktif' => TelegramChat::where('aktif', true)->count(),
        ]);
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:telegram_message_templates,nama'],
            'isi' => ['required', 'string', 'max:4096'],
        ], [], ['nama' => 'nama template', 'isi' => 'isi pesan']);

        $template = TelegramMessageTemplate::create($data);

        return redirect()->route('bot.index')->with('status', "Template {$template->nama} berhasil disimpan.");
    }

    public function updateTemplate(Request $request, TelegramMessageTemplate $template): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('telegram_message_templates', 'nama')->ignore($template->id)],
            'isi' => ['required', 'string', 'max:4096'],
        ], [], ['nama' => 'nama template', 'isi' => 'isi pesan']);

        $template->update($data);

        return redirect()->route('bot.index')->with('status', "Template {$template->nama} berhasil diperbarui.");
    }

    public function destroyTemplate(TelegramMessageTemplate $template): RedirectResponse
    {
        $nama = $template->nama;
        $template->delete();

        return redirect()->route('bot.index')->with('status', "Template {$nama} berhasil dihapus.");
    }

    public function storeMessage(Request $request, TelegramMessageDispatcher $dispatcher): RedirectResponse
    {
        $data = $request->validate([
            'aksi' => ['required', Rule::in(['sekarang', 'jadwalkan'])],
            'template_id' => ['nullable', 'integer', 'exists:telegram_message_templates,id', 'required_without:message'],
            'message' => ['nullable', 'string', 'max:4096', 'required_without:template_id'],
            'send_at' => ['nullable', 'date', 'after:now', Rule::requiredIf($request->input('aksi') === 'jadwalkan')],
        ], [], ['message' => 'isi pesan', 'send_at' => 'waktu pengiriman']);

        $template = isset($data['template_id'])
            ? TelegramMessageTemplate::findOrFail($data['template_id'])
            : null;
        $isi = ($data['message'] ?? '') !== '' ? $data['message'] : $template?->isi;

        abort_if($isi === null, 422, 'Isi pesan wajib tersedia.');

        $scheduled = $data['aksi'] === 'jadwalkan';
        $message = TelegramMessage::create([
            'template_id' => $template?->id,
            'isi' => $isi,
            'send_at' => $scheduled ? Carbon::parse($data['send_at']) : now(),
            'status' => TelegramMessage::STATUS_PENDING,
        ]);

        if ($scheduled) {
            return redirect()->route('bot.index')->with('status', 'Pesan berhasil dijadwalkan.');
        }

        $terkirim = $dispatcher->kirim($message);

        return redirect()->route('bot.index')->with(
            $terkirim ? 'status' : 'error',
            $terkirim ? 'Pesan berhasil dikirim ke seluruh grup aktif.' : 'Pengiriman pesan gagal. Periksa status dan konfigurasi Telegram.',
        );
    }

    public function updateMessage(Request $request, TelegramMessage $message): RedirectResponse
    {
        $data = $request->validate([
            'isi' => ['required', 'string', 'max:4096'],
            'send_at' => ['required', 'date', 'after:now'],
        ], [], ['isi' => 'isi pesan', 'send_at' => 'waktu pengiriman']);

        $updated = TelegramMessage::whereKey($message->id)
            ->where('status', TelegramMessage::STATUS_PENDING)
            ->where('send_at', '>', now())
            ->update([
                'isi' => $data['isi'],
                'send_at' => Carbon::parse($data['send_at']),
            ]);

        abort_unless($updated === 1, 409, 'Pesan tidak dapat diedit karena sudah diproses atau jatuh tempo.');

        return redirect()->route('bot.index')->with('status', 'Pesan terjadwal berhasil diperbarui.');
    }

    public function cancelMessage(TelegramMessage $message): RedirectResponse
    {
        $cancelled = TelegramMessage::whereKey($message->id)
            ->where('status', TelegramMessage::STATUS_PENDING)
            ->update(['status' => TelegramMessage::STATUS_CANCELLED]);

        abort_unless($cancelled === 1, 409, 'Pesan tidak dapat dibatalkan karena sudah diproses.');

        return redirect()->route('bot.index')->with('status', 'Pesan terjadwal berhasil dibatalkan.');
    }

    public function sendMessageNow(TelegramMessage $message, TelegramMessageDispatcher $dispatcher): RedirectResponse
    {
        abort_unless($message->status === TelegramMessage::STATUS_PENDING, 409, 'Pesan tidak lagi menunggu pengiriman.');

        $terkirim = $dispatcher->kirim($message);
        $status = $message->fresh()?->status;

        abort_if(! $terkirim && $status !== TelegramMessage::STATUS_FAILED, 409, 'Pesan sedang diproses oleh scheduler.');

        return redirect()->route('bot.index')->with(
            $terkirim ? 'status' : 'error',
            $terkirim ? 'Pesan terjadwal berhasil dikirim sekarang.' : 'Pengiriman pesan gagal. Periksa status dan konfigurasi Telegram.',
        );
    }

    public function destroyMessage(TelegramMessage $message): RedirectResponse
    {
        $deleted = TelegramMessage::whereKey($message->id)
            ->where('status', '!=', TelegramMessage::STATUS_SENDING)
            ->delete();

        abort_unless($deleted === 1, 409, 'Pesan tidak dapat dihapus saat sedang diproses.');

        return redirect()->route('bot.index')->with('status', 'Pesan berhasil dihapus.');
    }
}
