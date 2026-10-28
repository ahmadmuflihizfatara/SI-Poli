<?php

namespace Tests\Feature;

use App\Http\Controllers\DashboardController;
use App\Models\Keluhan;
use App\Models\Taruna;
use App\Models\User;
use Database\Seeders\TarunaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_persen_selalu_berjumlah_100_dan_grafik_tanpa_trik_dasharray(): void
    {
        $this->assertSame(['a' => 50, 'b' => 50], DashboardController::persen(['a' => 1, 'b' => 1]));
        $this->assertSame(['a' => 13, 'b' => 87], DashboardController::persen(['a' => 1, 'b' => 7])); // 12.5/87.5: dulu 13+88
        $this->assertSame(['a' => 33, 'b' => 33, 'c' => 34], DashboardController::persen(['a' => 1, 'b' => 1, 'c' => 1.0001]));
        $this->assertSame(['a' => 0, 'b' => 100], DashboardController::persen(['a' => 0, 'b' => 3]));
        $this->assertSame(['a' => 0, 'b' => 0], DashboardController::persen(['a' => 0, 'b' => 0]));

        // Tanpa keluhan: 100% sembuh digambar sebagai lingkaran penuh, setengah donat abu-abu
        $this->seed(TarunaSeeder::class);
        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertOk()
            ->assertSee('Sembuh (100%)')->assertSee('Sakit (0%)')
            ->assertDontSee('stroke-dasharray')->assertDontSee('pathLength', false);
    }

    public function test_tabel_keluhan_baru_hanya_menampilkan_keluhan_yang_masuk_hari_ini(): void
    {
        $this->seed(TarunaSeeder::class);
        $dasar = ['taruna_id' => Taruna::first()->id, 'tanggal_awal' => today(), 'terapi' => 'Istirahat', 'status' => 'Ringan'];
        $this->travel(-1)->days();
        Keluhan::create($dasar + ['keluhan' => 'Batuk kemarin']);
        $this->travelBack();
        Keluhan::create($dasar + ['keluhan' => 'Demam hari ini']);

        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertOk()
            ->assertSee('Keluhan Baru Hari Ini')->assertSee('Demam hari ini')->assertDontSee('Batuk kemarin');
    }

    public function test_kartu_sakit_menampilkan_tren_dibanding_kemarin(): void
    {
        $this->seed(TarunaSeeder::class);
        $dasar = ['taruna_id' => Taruna::first()->id, 'tanggal_awal' => today(), 'terapi' => 'Istirahat', 'keluhan' => 'Demam'];
        $this->travel(-1)->days();
        Keluhan::create($dasar + ['status' => 'Berat']);
        Keluhan::create($dasar + ['status' => 'Sedang']);
        $this->travelBack();
        Keluhan::create($dasar + ['status' => 'Berat']);
        Keluhan::create($dasar + ['status' => 'Berat']);

        // Berat: 1 kemarin → 3 sekarang (naik 2); Sedang tetap 1; Ringan tetap 0
        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertOk()
            ->assertSeeInOrder(['Sakit Ringan', 'Tidak berubah dari kemarin', 'Sakit Sedang', 'Tidak berubah dari kemarin', 'Sakit Berat', 'Naik', '2 dari kemarin']);
    }

    public function test_sumbu_batang_dibulatkan_dan_tingkat_campuran_ditumpuk(): void
    {
        $this->assertSame(5, DashboardController::batasSumbu(0));
        $this->assertSame(5, DashboardController::batasSumbu(1));
        $this->assertSame(10, DashboardController::batasSumbu(7));
        $this->assertSame(20, DashboardController::batasSumbu(12));
        $this->assertSame(100, DashboardController::batasSumbu(100));

        // Satu tingkat dengan kasus Ringan & Berat: dua segmen di batang yang sama, lebar 2/5 = 40%, bukan 100%
        $this->seed(TarunaSeeder::class);
        $taruna = Taruna::where('tingkat', 'I')->first();
        $dasar = ['taruna_id' => $taruna->id, 'tanggal_awal' => today(), 'terapi' => 'Istirahat', 'keluhan' => 'Demam'];
        Keluhan::create($dasar + ['status' => 'Ringan']);
        Keluhan::create($dasar + ['status' => 'Berat']);

        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertOk()
            ->assertSee('width: 40%', false)
            ->assertSee('data-judul="Tingkat I · Ringan"', false)
            ->assertSee('data-judul="Tingkat I · Berat"', false);
    }

    public function test_rentang_tanggal_pilihan_sendiri(): void
    {
        $this->seed(TarunaSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard', ['dari' => '2026-10-01', 'sampai' => '2026-10-15']))->assertOk()
            ->assertSee('1 – 15 Oktober 2026')->assertSee('dari periode sebelumnya');

        $this->actingAs($user)->get(route('dashboard', ['dari' => '2026-10-15', 'sampai' => '2026-10-01']))
            ->assertSessionHasErrors('dari');
    }

    public function test_keluhan_baru_tampil_sebagai_daftar_dengan_filter_lima_terbaru(): void
    {
        $this->seed(TarunaSeeder::class);
        $dasar = ['taruna_id' => Taruna::first()->id, 'tanggal_awal' => today(), 'terapi' => 'Istirahat', 'status' => 'Ringan'];
        $user = User::factory()->create();

        Keluhan::create($dasar + ['keluhan' => 'Pusing']);
        $this->actingAs($user)->get(route('dashboard'))->assertOk()
            ->assertSee('class="daftar-keluhan', false)->assertDontSee('5 terbaru')->assertDontSee('Cetak / PDF');

        foreach (range(1, 5) as $i) {
            Keluhan::create($dasar + ['keluhan' => "Keluhan {$i}"]);
        }
        $this->actingAs($user)->get(route('dashboard'))->assertOk()
            ->assertSee('5 terbaru')->assertSee('Semua (6)')->assertSee('class="daftar-keluhan m-0 flex list-none flex-col gap-2 p-0" data-batas="semua"', false);
    }
}
