<?php

namespace Tests\Feature;

use App\Models\TelegramChat;
use App\Models\TelegramMessage;
use App\Models\TelegramMessageTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ManajemenBotTest extends TestCase
{
    use RefreshDatabase;

    private function fakeTelegram(): void
    {
        config(['services.telegram.token' => 'TOKEN-UJI']);
        Http::preventStrayRequests();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
    }

    private function jumlahPesanTelegram(): int
    {
        return Http::recorded(fn ($request) => str_ends_with($request->url(), '/sendMessage'))->count();
    }

    public function test_admin_mengelola_template_pesan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->post(route('bot.templates.store'), ['nama' => 'Pengumuman', 'isi' => 'Jadwal baru'])
            ->assertRedirect(route('bot.index'));
        $template = TelegramMessageTemplate::sole();
        $this->assertSame('Jadwal baru', $template->isi);

        $this->put(route('bot.templates.update', $template), ['nama' => 'Perubahan Jadwal', 'isi' => 'Jadwal diperbarui'])
            ->assertRedirect(route('bot.index'));
        $this->assertSame('Jadwal diperbarui', $template->fresh()->isi);

        $this->delete(route('bot.templates.destroy', $template))->assertRedirect(route('bot.index'));
        $this->assertModelMissing($template);
    }

    public function test_admin_dapat_mengirim_pesan_dari_template_dan_isi_diamankan(): void
    {
        $this->fakeTelegram();
        TelegramChat::create(['chat_id' => -123, 'tipe' => 'group']);
        TelegramChat::create(['chat_id' => -456, 'tipe' => 'supergroup']);
        $template = TelegramMessageTemplate::create(['nama' => 'Info', 'isi' => '<script>jadwal & info</script>']);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('bot.messages.store'), [
                'aksi' => 'sekarang',
                'template_id' => $template->id,
                'message' => '',
            ])
            ->assertRedirect(route('bot.index'))
            ->assertSessionHas('status');

        $message = TelegramMessage::sole();
        $this->assertSame(TelegramMessage::STATUS_SENT, $message->status);
        $this->assertSame($template->isi, $message->isi);
        $this->assertSame(2, $this->jumlahPesanTelegram());
        Http::assertSent(fn ($request) => $request['chat_id'] === -123 && $request['text'] === '&lt;script&gt;jadwal &amp; info&lt;/script&gt;');
    }

    public function test_pesan_terjadwal_dikirim_otomatis_satu_kali_setelah_jatuh_tempo(): void
    {
        $this->fakeTelegram();
        $this->freezeTime();
        TelegramChat::create(['chat_id' => -55, 'tipe' => 'group']);
        $admin = User::factory()->create(['role' => 'admin']);
        $waktuKirim = now()->addHour()->format('Y-m-d\TH:i');

        $this->actingAs($admin)
            ->post(route('bot.messages.store'), [
                'aksi' => 'jadwalkan',
                'message' => 'Pengingat rapat',
                'send_at' => $waktuKirim,
            ])
            ->assertRedirect(route('bot.index'))
            ->assertSessionHas('status');

        $message = TelegramMessage::sole();
        $this->assertSame(TelegramMessage::STATUS_PENDING, $message->status);
        $this->assertSame(0, $this->jumlahPesanTelegram());

        $this->travelTo($message->send_at->addMinute());
        $this->artisan('telegram:pesan-terjadwal')->assertSuccessful();
        $this->artisan('telegram:pesan-terjadwal')->assertSuccessful();

        $this->assertSame(TelegramMessage::STATUS_SENT, $message->fresh()->status);
        $this->assertSame(1, $this->jumlahPesanTelegram());
    }

    public function test_pesan_terjadwal_dapat_diedit_dikirim_sekarang_atau_dibatalkan(): void
    {
        $this->fakeTelegram();
        $this->freezeTime();
        TelegramChat::create(['chat_id' => -77, 'tipe' => 'group']);
        $admin = User::factory()->create(['role' => 'admin']);
        $message = TelegramMessage::create([
            'isi' => 'Pesan awal',
            'send_at' => now()->addHour(),
            'status' => TelegramMessage::STATUS_PENDING,
        ]);
        $dibatalkan = TelegramMessage::create([
            'isi' => 'Jangan dikirim',
            'send_at' => now()->addHours(2),
            'status' => TelegramMessage::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->patch(route('bot.messages.update', $message), [
                'isi' => 'Pesan diperbarui',
                'send_at' => now()->addHours(3)->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('bot.index'));
        $this->assertSame('Pesan diperbarui', $message->fresh()->isi);

        $this->post(route('bot.messages.send-now', $message))
            ->assertRedirect(route('bot.index'))
            ->assertSessionHas('status');
        $this->assertSame(TelegramMessage::STATUS_SENT, $message->fresh()->status);
        $this->post(route('bot.messages.send-now', $message))->assertConflict();

        $this->post(route('bot.messages.cancel', $dibatalkan))->assertRedirect(route('bot.index'));
        $this->assertSame(TelegramMessage::STATUS_CANCELLED, $dibatalkan->fresh()->status);
        $this->artisan('telegram:pesan-terjadwal')->assertSuccessful();
        $this->assertSame(1, $this->jumlahPesanTelegram());
    }

    public function test_validasi_menolak_pesan_kosong_terlalu_panjang_dan_waktu_kedaluwarsa(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('bot.messages.store'), ['aksi' => 'sekarang'])
            ->assertSessionHasErrors(['message', 'template_id']);

        $this->post(route('bot.messages.store'), [
            'aksi' => 'sekarang',
            'message' => str_repeat('x', 4097),
        ])->assertSessionHasErrors('message');

        $this->post(route('bot.messages.store'), [
            'aksi' => 'jadwalkan',
            'message' => 'Terlambat',
            'send_at' => now()->subMinute()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('send_at');

        $this->assertSame(0, TelegramMessage::count());
    }

    public function test_hanya_admin_dapat_mengakses_manajemen_bot_dan_pesan_gagal_memiliki_status(): void
    {
        $this->fakeTelegram();
        $perawat = User::factory()->create(['role' => 'perawat']);

        $this->actingAs($perawat)
            ->get(route('bot.index'))
            ->assertForbidden();
        $this->post(route('bot.messages.store'), ['aksi' => 'sekarang', 'message' => 'Tidak boleh'])
            ->assertForbidden();
        $this->assertSame(0, TelegramMessage::count());

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->post(route('bot.messages.store'), ['aksi' => 'sekarang', 'message' => 'Belum ada grup'])
            ->assertRedirect(route('bot.index'))
            ->assertSessionHas('error');
        $this->assertSame(TelegramMessage::STATUS_FAILED, TelegramMessage::sole()->status);
        $this->assertSame(0, $this->jumlahPesanTelegram());
    }
}
