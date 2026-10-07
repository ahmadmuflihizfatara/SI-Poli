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
}
