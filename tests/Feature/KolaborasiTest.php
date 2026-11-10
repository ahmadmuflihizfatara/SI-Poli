<?php

namespace Tests\Feature;

use App\Models\KegiatanPemeriksaan;
use App\Models\Keluhan;
use App\Models\Pegawai;
use App\Models\Taruna;
use App\Models\User;
use App\Services\Kolaborasi;
use Database\Seeders\TarunaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class KolaborasiTest extends TestCase
{
    use RefreshDatabase;

    private const NADI = 'data[1][nadi]';

    public function test_isian_kursor_dan_fokus_tersinkron_antar_akun_dan_isian_yang_dipegang_akun_lain_terkunci(): void
    {
        [$a, $b] = User::factory()->count(2)->create(['role' => 'perawat']);
        $tokenA = $this->token($a, 'rikkes.1');
        $tokenB = $this->token($b, 'rikkes.1');

        $this->sinkron($b, $tokenB)->assertOk()->assertJsonPath('pengguna', []);
        $seqA = $this->sinkron($a, $tokenA, ['fokus' => self::NADI, 'isian' => [self::NADI => '88']])
            ->assertJsonPath('pengguna.0.nama', $b->name)->json('seq');

        // gema isian sendiri tidak dikirim balik
        $this->sinkron($a, $tokenA, ['since' => $seqA, 'fokus' => self::NADI, 'kursor' => ['a' => '#form-mptb', 'x' => 0.5, 'y' => 0.25], 'isian' => [self::NADI => '89']])
            ->assertJsonPath('isian', []);

        $this->sinkron($b, $tokenB, ['since' => 0])
            ->assertJsonPath('isian', [self::NADI => '89'])
            ->assertJsonPath('kunci', [self::NADI => $a->id])
            ->assertJsonPath('pengguna.0.kursor', ['a' => '#form-mptb', 'x' => 0.5, 'y' => 0.25])
            ->assertJsonPath('pengguna.0.fokus', self::NADI);

        // B menulis isian yang sedang difokuskan A: ditolak dan dikembalikan ke nilai A
        $this->sinkron($b, $tokenB, ['since' => 99, 'isian' => [self::NADI => '70']])
            ->assertJsonPath('tolak', [self::NADI])
            ->assertJsonPath('isian', [self::NADI => '89']);

        // A pindah fokus: B boleh mengubah
        $this->sinkron($a, $tokenA, ['since' => 99]);
        $this->sinkron($b, $tokenB, ['since' => 99, 'isian' => [self::NADI => '70']])->assertJsonPath('tolak', []);
        $this->sinkron($a, $tokenA, ['since' => $seqA])->assertJsonPath('isian', [self::NADI => '70']);
    }

    public function test_token_akun_lain_ditolak_dan_akun_hanya_lihat_tidak_bisa_mengubah_atau_mengunci(): void
    {
        [$a, $pengasuh] = [User::factory()->create(['role' => 'perawat']), User::factory()->create(['role' => 'pengasuh'])];
        $tokenA = $this->token($a, 'rikkes.1');
        $tokenLihat = $this->token($pengasuh, 'rikkes.1', ubah: false);

        $this->sinkron($pengasuh, $tokenA)->assertForbidden();
        $this->sinkron($pengasuh, 'palsu')->assertForbidden();

        $this->sinkron($pengasuh, $tokenLihat, ['fokus' => self::NADI, 'isian' => [self::NADI => '120']])->assertOk();
        $this->sinkron($a, $tokenA)->assertJsonPath('isian', [])->assertJsonPath('kunci', [])->assertJsonPath('pengguna.0.nama', $pengasuh->name);
    }

    public function test_akun_lain_diberi_tahu_saat_hasil_rikkes_disimpan(): void
    {
        $this->seed(TarunaSeeder::class);
        [$a, $b] = User::factory()->count(2)->create(['role' => 'dokter']);
        $kegiatan = KegiatanPemeriksaan::create(['jenis' => 'Samapta', 'nama' => 'Samapta I', 'tanggal_mulai' => today(), 'created_by' => $a->id]);
        $tokenA = $this->token($a, "rikkes.{$kegiatan->id}");
        $tokenB = $this->token($b, "rikkes.{$kegiatan->id}");
        $seqB = $this->sinkron($b, $tokenB)->json('seq');

        $this->actingAs($a)->get(route('pemeriksaan-kesehatan.show', $kegiatan))->assertOk()->assertSee('kolab-hadir', false);
        $this->post(route('pemeriksaan-kesehatan.simpan', $kegiatan), ['_kolab' => $tokenA, 'data' => [Taruna::first()->id => ['nadi' => '90']]])
            ->assertSessionHasNoErrors();

        $this->sinkron($b, $tokenB, ['since' => $seqB])->assertJsonPath('simpan', $a->name);
    }

    public function test_draf_keluhan_baru_bisa_digabung_dan_hanya_tersimpan_sekali(): void
    {
        $this->seed(TarunaSeeder::class);
        [$a, $b] = User::factory()->count(2)->create(['role' => 'perawat']);
        $kanal = 'draf.keluhan-baru.AbCdEfGhIjKl';
        $tokenA = $this->token($a, $kanal, sekali: true);
        $tokenB = $this->token($b, $kanal, sekali: true);

        $this->actingAs($a)->get(route('laporan-kesehatan.create', ['draf' => 'AbCdEfGhIjKl']))->assertOk()->assertSee('"AbCdEfGhIjKl"', false);
        $this->sinkron($a, $tokenA);
        $this->actingAs($b)->get(route('laporan-kesehatan.index'))
            ->assertSee('Gabung dengan '.$a->name)->assertSee(route('laporan-kesehatan.create', ['draf' => 'AbCdEfGhIjKl']), false);
        $this->sinkron($b, $tokenB);

        $this->actingAs($a)->post(route('laporan-kesehatan.store'), $this->isianKeluhan() + ['_kolab' => $tokenA])
            ->assertRedirect(route('laporan-kesehatan.index'))->assertSessionHas('status', 'Keluhan baru berhasil ditambahkan.');
        $this->sinkron($b, $tokenB, ['since' => 99])->assertJsonPath('simpan', $a->name);
        $this->actingAs($b)->post(route('laporan-kesehatan.store'), $this->isianKeluhan() + ['_kolab' => $tokenB])
            ->assertRedirect(route('laporan-kesehatan.index'))->assertSessionHas('status', "Keluhan ini sudah disimpan oleh {$a->name}.");

        $this->assertSame(1, Keluhan::count());
        $this->actingAs($b)->get(route('laporan-kesehatan.index'))->assertDontSee('Gabung dengan');
    }

    private function token(User $user, string $kanal, bool $ubah = true, bool $sekali = false): string
    {
        $this->actingAs($user);

        return Kolaborasi::token($kanal, $ubah, $sekali);
    }

    private function sinkron(User $user, string $token, array $isi = []): TestResponse
    {
        return $this->actingAs($user)->postJson(route('kolaborasi'), $isi + ['token' => $token, 'since' => 0]);
    }

    /** @return array<string, mixed> */
    private function isianKeluhan(): array
    {
        return [
            'taruna_id' => Taruna::first()->id, 'waktu_laporan' => now()->subHour()->format('Y-m-d\TH:i'),
            'sistolik' => 120, 'diastolik' => 80, 'suhu' => 37, 'nadi' => 80, 'keluhan' => 'Pusing', 'status' => 'Sedang',
            'obat' => [['nama' => 'Istirahat']], 'tanggal_kontrol_selanjutnya' => today()->toDateString(),
            'pemeriksa_id' => Pegawai::firstOrCreate(['nama' => 'Rina Kartika, A.Md.Kep.'], ['jabatan' => 'Perawat'])->id,
        ];
    }
}
