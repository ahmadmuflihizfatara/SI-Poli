<?php

namespace Tests\Feature;

use App\Models\KejadianLuarBiasa;
use App\Models\Keluhan;
use App\Models\KeluhanKlb;
use App\Models\KeluhanPsikologi;
use App\Models\Taruna;
use App\Models\TelegramChat;
use App\Services\KontrolHariIni;
use Database\Seeders\TarunaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramBotTest extends TestCase
{
    use RefreshDatabase;

    private const RAHASIA = 'rahasia-uji';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.telegram' => ['token' => 'T0KEN', 'webhook_secret' => self::RAHASIA, 'admin_ids' => [111], 'jam_kirim' => '06:30']]);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
    }

    private function kirimUpdate(array $update, ?string $rahasia = self::RAHASIA)
    {
        return $this->postJson(route('telegram.webhook'), $update, $rahasia ? ['X-Telegram-Bot-Api-Secret-Token' => $rahasia] : []);
    }

    private function tambahkanKeGrup(int $oleh, string $status = 'member', int $chatId = -100)
    {
        return $this->kirimUpdate(['my_chat_member' => [
            'chat' => ['id' => $chatId, 'title' => 'Grup Uji', 'type' => 'supergroup'],
            'from' => ['id' => $oleh], 'new_chat_member' => ['status' => $status],
        ]]);
    }

    private function dikirimKe(string $metode, ?int $chatId = null): int
    {
        return Http::recorded(fn ($r) => str_ends_with($r->url(), "/{$metode}") && ($chatId === null || $r['chat_id'] === $chatId))->count();
    }

    public function test_webhook_menolak_tanpa_atau_dengan_rahasia_salah(): void
    {
        $this->kirimUpdate([], null)->assertForbidden();
        $this->kirimUpdate([], 'salah')->assertForbidden();
        $this->assertDatabaseCount('telegram_chats', 0);
    }

    public function test_bot_hanya_tinggal_di_grup_yang_ditambahkan_pengelola(): void
    {
        $this->tambahkanKeGrup(111)->assertOk();
        $this->assertTrue(TelegramChat::where('chat_id', -100)->sole()->aktif);
        $this->assertSame(0, $this->dikirimKe('leaveChat'));

        $this->tambahkanKeGrup(999, chatId: -200)->assertOk();
        $this->assertDatabaseMissing('telegram_chats', ['chat_id' => -200]);
        $this->assertSame(1, $this->dikirimKe('leaveChat', -200));

        $this->tambahkanKeGrup(111, 'kicked')->assertOk();
        $this->assertFalse(TelegramChat::where('chat_id', -100)->sole()->aktif);
    }

    public function test_kontrol_harian_hanya_memuat_jadwal_hari_ini_ke_grup_aktif(): void
    {
        $this->seed(TarunaSeeder::class);
        [$a, $b, $c] = Taruna::take(3)->get();
        $dasar = ['tanggal_awal' => today(), 'keluhan' => 'RAHASIA-KELUHAN', 'terapi' => 'RAHASIA-TERAPI', 'status' => 'Ringan'];
        Keluhan::create($dasar + ['taruna_id' => $a->id, 'tanggal_kontrol_selanjutnya' => today()]);
        Keluhan::create($dasar + ['taruna_id' => $b->id, 'tanggal_kontrol_selanjutnya' => today(), 'status_pemulihan' => 'Sembuh']);
        Keluhan::create($dasar + ['taruna_id' => $c->id, 'tanggal_kontrol_selanjutnya' => today()->addDay()]);
        KeluhanPsikologi::create(['taruna_id' => $b->id, 'tanggal_awal' => today(), 'keluhan' => 'x', 'terapi' => 'y', 'lanjut_konseling' => true, 'tanggal_konseling_selanjutnya' => today()]);
        $klb = KejadianLuarBiasa::create(['nama' => 'Diare', 'deskripsi' => 'd']);
        KeluhanKlb::create([
            'kejadian_luar_biasa_id' => $klb->id, 'nama' => 'Pasien Klb', 'npm' => '1', 'kelas' => 'I RKS A', 'tingkat' => 'I', 'jenis_kelamin' => 'Laki-laki',
            'kamar' => 'A1', 'keluhan' => 'x', 'terapi' => 'y', 'status' => 'Ringan', 'tanggal_kontrol_selanjutnya' => today(),
        ]);

        TelegramChat::create(['chat_id' => -1, 'tipe' => 'group', 'aktif' => true]);
        TelegramChat::create(['chat_id' => -2, 'tipe' => 'group', 'aktif' => false]);

        $this->artisan('telegram:kontrol-harian')->assertSuccessful();

        $this->assertSame(1, $this->dikirimKe('sendMessage', -1));
        $this->assertSame(0, $this->dikirimKe('sendMessage', -2));
        Http::assertSent(function ($r) {
            $teks = $r['text'] ?? '';

            return $r['chat_id'] === -1
                && str_contains($teks, 'Kontrol kesehatan (1)') && str_contains($teks, 'Konseling (1)') && str_contains($teks, 'Kejadian luar biasa (1)')
                && str_contains($teks, 'Pasien Klb')
                && ! str_contains($teks, 'RAHASIA') // isi keluhan/terapi tidak ikut terkirim
                && ! str_contains($teks, 'Kontrol kesehatan (2)');
        });
    }

    public function test_hari_tanpa_jadwal_tetap_dikirim_dan_pesan_panjang_dipecah(): void
    {
        TelegramChat::create(['chat_id' => -1, 'tipe' => 'group']);
        $this->artisan('telegram:kontrol-harian');
        Http::assertSent(fn ($r) => str_contains($r['text'], 'Tidak ada jadwal kontrol hari ini'));

        $klb = KejadianLuarBiasa::create(['nama' => 'Diare', 'deskripsi' => 'd']);
        foreach (range(1, 120) as $i) {
            KeluhanKlb::create([
                'kejadian_luar_biasa_id' => $klb->id, 'nama' => 'Taruna dengan nama yang cukup panjang nomor '.$i, 'npm' => (string) $i, 'kelas' => 'III RPK B', 'tingkat' => 'III',
                'jenis_kelamin' => 'Perempuan', 'kamar' => 'B'.$i, 'keluhan' => 'x', 'terapi' => 'y', 'status' => 'Ringan', 'tanggal_kontrol_selanjutnya' => today(),
            ]);
        }
        $pesan = app(KontrolHariIni::class)->pesan();
        $this->assertGreaterThan(1, count($pesan));
        foreach ($pesan as $p) {
            $this->assertLessThan(4096, strlen($p));
        }
    }

    public function test_hariini_hanya_untuk_grup_terdaftar_atau_pengelola(): void
    {
        $perintah = fn (int $chat, int $dari) => $this->kirimUpdate(['message' => ['chat' => ['id' => $chat], 'from' => ['id' => $dari], 'text' => '/hariini@bot_uji']]);

        $perintah(-77, 999)->assertOk(); // grup tak terdaftar, pengirim asing
        $this->assertSame(0, $this->dikirimKe('sendMessage', -77));

        $perintah(555, 111); // chat pribadi pengelola
        $this->assertSame(1, $this->dikirimKe('sendMessage', 555));

        TelegramChat::create(['chat_id' => -88, 'tipe' => 'group']);
        $perintah(-88, 999); // grup terdaftar
        $this->assertSame(1, $this->dikirimKe('sendMessage', -88));
    }
}
