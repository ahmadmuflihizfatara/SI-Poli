<?php

namespace Tests\Feature;

use App\Models\Keluhan;
use App\Models\Taruna;
use App\Models\User;
use Database\Seeders\TarunaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanKesehatanTest extends TestCase
{
    use RefreshDatabase;

    public function test_kontrol_terlambat_ditandai_dan_diurutkan_paling_atas(): void
    {
        $this->seed(TarunaSeeder::class);
        $dasar = ['taruna_id' => Taruna::first()->id, 'tanggal_awal' => today(), 'terapi' => 'Istirahat', 'status' => 'Ringan'];
        Keluhan::create($dasar + ['keluhan' => 'Kontrol minggu depan', 'tanggal_kontrol_selanjutnya' => today()->addWeek()]);
        Keluhan::create($dasar + ['keluhan' => 'Kontrol bulan lalu', 'tanggal_kontrol_selanjutnya' => today()->subDays(29)]);
        Keluhan::create($dasar + ['keluhan' => 'Kontrol hari ini', 'tanggal_kontrol_selanjutnya' => today()]);

        $this->actingAs(User::factory()->create())->get(route('laporan-kesehatan.index'))->assertOk()
            ->assertSeeInOrder(['Kontrol bulan lalu', 'Terlambat 29 hari', 'Kontrol hari ini', 'Hari ini', 'Kontrol minggu depan'])
            ->assertDontSee('title="Kontrol bulan lalu"', false);
    }

    public function test_pasien_sembuh_tidak_dianggap_terlambat(): void
    {
        $this->seed(TarunaSeeder::class);
        Keluhan::create([
            'taruna_id' => Taruna::first()->id, 'tanggal_awal' => today(), 'terapi' => 'Istirahat', 'status' => 'Ringan',
            'keluhan' => 'Sudah pulih', 'tanggal_kontrol_selanjutnya' => today()->subDays(3), 'status_pemulihan' => 'Sembuh',
        ]);

        $this->actingAs(User::factory()->create())->get(route('laporan-kesehatan.index'))->assertOk()
            ->assertSee('Sudah pulih')->assertDontSee('Terlambat 3 hari');
    }

    public function test_popup_ringkas_memuat_info_taruna_dan_aksi_sesuai_izin(): void
    {
        $this->seed(TarunaSeeder::class);
        $taruna = Taruna::first();
        $keluhan = Keluhan::create([
            'taruna_id' => $taruna->id, 'tanggal_awal' => today(), 'terapi' => 'Kompres', 'status' => 'Sedang',
            'keluhan' => 'Keseleo', 'tanggal_kontrol_selanjutnya' => today()->addDay(),
        ]);
        $info = fn ($res) => json_decode(html_entity_decode(str($res->getContent())->match('/data-info="([^"]+)"/')), true);

        $admin = User::factory()->create(['role' => 'admin']);
        $data = $info($this->actingAs($admin)->get(route('laporan-kesehatan.index'))->assertOk()->assertSee('id="lk-pop"', false));
        $this->assertSame([$taruna->kamar, 'Keseleo', 'Kompres'], [$data['kamar'], $data['keluhan'], $data['terapi']]);
        $this->assertSame(route('laporan-kesehatan.toggle-sembuh', $keluhan), $data['sembuh']);
        $this->assertSame(route('laporan-kesehatan.kontrol.edit', $keluhan), $data['edit']);

        $keluhan->update(['status_pemulihan' => 'Sembuh']);
        $data = $info($this->actingAs($admin)->get(route('laporan-kesehatan.index')));
        $this->assertNull($data['sembuh']);
        $this->assertSame('Sudah sembuh', $data['alasan']);
    }

    public function test_toolbar_punya_cari_npm_filter_ganda_reset_dan_konfirmasi_sembuh(): void
    {
        $this->seed(TarunaSeeder::class);
        $taruna = Taruna::first();
        Keluhan::create([
            'taruna_id' => $taruna->id, 'tanggal_awal' => today(), 'terapi' => 'Obat', 'status' => 'Berat',
            'keluhan' => 'Demam', 'tanggal_kontrol_selanjutnya' => today()->subDay(),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('laporan-kesehatan.index'))->assertOk()
            ->assertSee('placeholder="Cari nama atau NPM"', false)
            ->assertSee('data-npm="'.$taruna->npm.'"', false)
            ->assertSee('data-kontrol="terlambat"', false)
            ->assertSee('name="saring-status"', false)
            ->assertSee('name="saring-tingkat"', false)
            ->assertSee('id="reset-saring"', false)
            ->assertSee('id="konfirmasi-sembuh"', false)
            ->assertDontSee('<select', false);
    }

    public function test_keluhan_taruna_yang_sama_selalu_bersusun(): void
    {
        $this->seed(TarunaSeeder::class);
        [$a, $b] = Taruna::take(2)->get();
        $dasar = ['tanggal_awal' => today(), 'terapi' => 'Obat', 'status' => 'Ringan'];
        Keluhan::create($dasar + ['taruna_id' => $a->id, 'keluhan' => 'A dekat', 'tanggal_kontrol_selanjutnya' => today()->addDay()]);
        Keluhan::create($dasar + ['taruna_id' => $b->id, 'keluhan' => 'B tengah', 'tanggal_kontrol_selanjutnya' => today()->addDays(5)]);
        Keluhan::create($dasar + ['taruna_id' => $a->id, 'keluhan' => 'A jauh', 'tanggal_kontrol_selanjutnya' => today()->addDays(10)]);

        // tanpa pengelompokan urutannya A dekat, B tengah, A jauh
        $this->actingAs(User::factory()->create())->get(route('laporan-kesehatan.index'))->assertOk()
            ->assertSeeInOrder(['A dekat', 'A jauh', 'B tengah']);
    }
}
