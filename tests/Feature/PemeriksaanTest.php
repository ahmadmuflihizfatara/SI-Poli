<?php

namespace Tests\Feature;

use App\Models\KegiatanPemeriksaan;
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

    public function test_kartu_mptb_dan_samapta_membuka_formulir_tambah_pemeriksaan_dengan_nama_dan_rentang_tanggal(): void
    {
        $this->seed(TarunaSeeder::class);
        $this->actingAs(User::factory()->create(['role' => 'perawat']));
        $senior = Taruna::where('tingkat', 'II')->first();

        $this->get(route('pemeriksaan-kesehatan.index'))->assertOk()
            ->assertSee(route('pemeriksaan-kesehatan.index', ['tambah' => 'MPTB']))->assertDontSee('Nama Pemeriksaan Kesehatan');
        $this->get(route('pemeriksaan-kesehatan.index', ['tambah' => 'Samapta']))->assertOk()
            ->assertSee('Tambah Pemeriksaan Kesehatan Samapta')->assertSee('Nama Pemeriksaan Kesehatan')->assertSee('Tanggal selesai');

        $this->post(route('pemeriksaan-kesehatan.store'), ['jenis' => 'Samapta', 'nama' => 'Samapta I', 'tanggal_mulai' => '2026-10-10', 'tanggal_selesai' => '2026-10-09'])
            ->assertSessionHasErrors('tanggal_selesai');
        $this->post(route('pemeriksaan-kesehatan.store'), ['jenis' => 'Samapta', 'nama' => 'Samapta I', 'tanggal_mulai' => '2026-10-05', 'tanggal_selesai' => '2026-10-09'])
            ->assertRedirect(route('pemeriksaan-kesehatan.show', $kegiatan = KegiatanPemeriksaan::sole()));

        $this->assertSame(['Samapta', 'Samapta I', '5 – 9 Oktober 2026'], [$kegiatan->jenis, $kegiatan->nama, $kegiatan->labelRentang()]);
        $this->assertSame('Pemeriksaan Samapta I (5 – 9 Oktober 2026) ditambahkan.', LogAktivitas::sole()->pesan);
        $this->get(route('pemeriksaan-kesehatan.show', $kegiatan))->assertOk()->assertSee('Samapta I')->assertSee($senior->nama);
        $this->get(route('pemeriksaan-kesehatan.index'))->assertSee('Samapta I')->assertSee('5 – 9 Oktober 2026');
    }

    public function test_perawat_dan_admin_bisa_mengisi_pemeriksaan_dan_setiap_perubahan_tercatat_di_log(): void
    {
        $this->seed(TarunaSeeder::class);
        $perawat = User::factory()->create(['role' => 'perawat']);
        $admin = User::factory()->create(['role' => 'admin']);
        $baru = Taruna::where('tingkat', 'I')->orderBy('nama')->first();
        $senior = Taruna::where('tingkat', 'II')->first();
        $mptb = KegiatanPemeriksaan::create(['jenis' => 'MPTB', 'nama' => 'MPTB 2026', 'tanggal_mulai' => today()->subDay(), 'tanggal_selesai' => today()->addDay()]);
        $tanggal = today()->toDateString();
        $kirim = fn (array $data, ?string $tgl = null) => $this->post(route('pemeriksaan-kesehatan.simpan', $mptb), ['tanggal' => $tgl ?? $tanggal, 'sesi' => 'Pagi', 'data' => $data]);

        foreach ([$perawat, $admin] as $user) {
            $this->actingAs($user)->get(route('dashboard'))->assertSee('Pemeriksaan Kesehatan');
            $this->get(route('pemeriksaan-kesehatan.index'))->assertOk();
        }
        // MPTB hanya taruna tingkat I; tanggal default hari ini (di dalam rentang)
        $this->actingAs($perawat)->get(route('pemeriksaan-kesehatan.show', $mptb))->assertOk()
            ->assertSee($baru->nama)->assertDontSee($senior->nama)->assertSee('value="'.$tanggal.'" selected', false);

        $vital = ['tekanan_darah' => '120/80', 'nadi' => '80', 'suhu' => '36,5', 'pernapasan' => '20', 'keluhan' => 'Tidak ada', 'terapi' => '', 'keterangan' => ''];
        $kirim([
            $baru->id => $vital,
            $senior->id => $vital,                                   // bukan peserta MPTB: diabaikan
            Taruna::where('tingkat', 'I')->where('id', '!=', $baru->id)->value('id') => ['nadi' => ''], // baris kosong: tidak dibuat
        ])->assertRedirect()->assertSessionHasNoErrors();

        $p = Pemeriksaan::sole();
        $this->assertSame([$baru->id, $mptb->id, 'MPTB', $tanggal, 'Pagi', '36.5'], [$p->taruna_id, $p->kegiatan_pemeriksaan_id, $p->jenis, $p->periode, $p->sesi, $p->suhu]);

        $kirim([$baru->id => $vital]);                               // tanpa perubahan: tidak dicatat lagi
        $kirim([$baru->id => ['suhu' => '38'] + $vital]);
        $kirim([$baru->id => ['suhu' => '99'] + $vital])->assertSessionHasErrors("data.{$baru->id}.suhu");
        $kirim([$baru->id => $vital], today()->addDay()->toDateString())->assertSessionHasErrors('tanggal');   // belum terjadi
        $kirim([$baru->id => $vital], today()->subDays(2)->toDateString())->assertSessionHasErrors('tanggal'); // di luar rentang

        $log = LogAktivitas::orderBy('id')->get();
        $this->assertSame(['Tambah', 'Edit'], $log->pluck('aksi')->all());
        $this->assertSame([$p->id, $p->id], $log->pluck('pemeriksaan_id')->all());
        $this->assertSame('Pemeriksaan MPTB', $log[1]->sumber_daya);
        $this->assertStringContainsString("taruna {$baru->nama} (MPTB 2026 · ", $log[1]->pesan);
        $this->assertStringContainsString('suhu dari 36.5 menjadi 38', $log[1]->pesan);

        $this->get(route('pemeriksaan-kesehatan.index'))->assertSee('1 dari');
        $this->actingAs($admin)->get(route('log.index', ['sumber' => 'Pemeriksaan MPTB']))
            ->assertOk()->assertSee(route('log.show', $log[1]))->assertSee($baru->nama);
        $this->get(route('log.show', $log[1]))->assertOk()->assertSee('Lihat pemeriksaan')
            ->assertSee($p->url())->assertSee('suhu dari 36.5 menjadi 38');
    }
}
