<?php

namespace Tests\Feature;

use App\Http\Controllers\DashboardController;
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
}
