<?php

namespace Tests\Feature;

use App\Models\LogAktivitas;
use App\Models\Pemeriksaan;
use App\Models\Taruna;
use App\Models\User;
use Database\Seeders\TarunaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PemeriksaanTest extends TestCase
{
    use RefreshDatabase;

    public function test_perawat_dan_admin_bisa_mengisi_pemeriksaan_dan_setiap_perubahan_tercatat_di_log(): void
    {
        $this->seed(TarunaSeeder::class);
        $perawat = User::factory()->create(['role' => 'perawat']);
        $admin = User::factory()->create(['role' => 'admin']);
        $baru = Taruna::where('tingkat', 'I')->orderBy('nama')->first();
        $senior = Taruna::where('tingkat', 'II')->first();
        $tanggal = today()->toDateString();
        $kirim = fn (array $data) => $this->post(route('pemeriksaan-kesehatan.mptb.simpan'), ['tanggal' => $tanggal, 'sesi' => 'Pagi', 'data' => $data]);

        foreach ([$perawat, $admin] as $user) {
            $this->actingAs($user)->get(route('dashboard'))->assertSee('Pemeriksaan Kesehatan');
            $this->get(route('pemeriksaan-kesehatan.index'))->assertOk();
            $this->get(route('pemeriksaan-kesehatan.samapta.index'))->assertOk()->assertSee($senior->nama);
        }
        // MPTB hanya taruna tingkat I
        $this->actingAs($perawat)->get(route('pemeriksaan-kesehatan.mptb.index'))->assertOk()->assertSee($baru->nama)->assertDontSee($senior->nama);

        $vital = ['tekanan_darah' => '120/80', 'nadi' => '80', 'suhu' => '36,5', 'pernapasan' => '20', 'keluhan' => 'Tidak ada', 'terapi' => '', 'keterangan' => ''];
        $kirim([
            $baru->id => $vital,
            $senior->id => $vital,                                   // bukan peserta MPTB: diabaikan
            Taruna::where('tingkat', 'I')->where('id', '!=', $baru->id)->value('id') => ['nadi' => ''], // baris kosong: tidak dibuat
        ])->assertRedirect()->assertSessionHasNoErrors();

        $p = Pemeriksaan::sole();
        $this->assertSame([$baru->id, 'MPTB', $tanggal, 'Pagi', '36.5'], [$p->taruna_id, $p->jenis, $p->periode, $p->sesi, $p->suhu]);

        $kirim([$baru->id => $vital]);                               // tanpa perubahan: tidak dicatat lagi
        $kirim([$baru->id => ['suhu' => '38'] + $vital]);
        $kirim([$baru->id => ['suhu' => '99'] + $vital])->assertSessionHasErrors("data.{$baru->id}.suhu");

        $log = LogAktivitas::orderBy('id')->get();
        $this->assertSame(['Tambah', 'Edit'], $log->pluck('aksi')->all());
        $this->assertSame([$p->id, $p->id], $log->pluck('pemeriksaan_id')->all());
        $this->assertSame('Pemeriksaan MPTB', $log[1]->sumber_daya);
        $this->assertStringContainsString("taruna {$baru->nama}", $log[1]->pesan);
        $this->assertStringContainsString('suhu dari 36.5 menjadi 38', $log[1]->pesan);

        $this->get(route('pemeriksaan-kesehatan.index'))->assertSee('1 dari');
        $this->actingAs($admin)->get(route('log.index', ['sumber' => 'Pemeriksaan MPTB']))
            ->assertOk()->assertSee('Lihat pemeriksaan')->assertSee($baru->nama);
    }
}
