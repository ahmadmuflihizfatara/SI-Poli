<?php

namespace Tests\Feature;

use App\Models\Keluhan;
use App\Models\Taruna;
use App\Models\User;
use Database\Seeders\TarunaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Audit WSTG-IDNT-01: data keluhan medis ditegakkan oleh satu Gate (lihat-keluhan) di setiap rute yang menampilkannya. */
class AksesDataMedisTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string, bool, bool}> [role, boleh lihat laporan medis, boleh buka riwayat medis] */
    public static function matriksRole(): array
    {
        return [
            'admin' => ['admin', true, true],
            'perawat' => ['perawat', true, true],
            'dokter' => ['dokter', true, true],
            'asisten kesehatan' => ['asisten_kesehatan', true, false],
            'pengasuh' => ['pengasuh', true, false],
            'psikolog' => ['psikolog', false, false],
        ];
    }

    #[DataProvider('matriksRole')]
    public function test_akses_data_keluhan_medis_sesuai_definisi_role(string $role, bool $lihatLaporan, bool $lihatRiwayat): void
    {
        $this->seed(TarunaSeeder::class);
        $taruna = Taruna::firstOrFail();
        $keluhan = Keluhan::create(['taruna_id' => $taruna->id, 'tanggal_awal' => today(), 'keluhan' => 'Demam medis rahasia', 'terapi' => 'Paracetamol', 'status' => 'Ringan']);

        $this->actingAs(User::factory()->create(['role' => $role]));

        $laporan = $this->get(route('laporan-kesehatan.index'))->assertOk();
        $lihatLaporan ? $laporan->assertSee('Demam medis rahasia') : $laporan->assertDontSee('Demam medis rahasia');
        $this->get(route('laporan-kesehatan.show', $keluhan))->assertStatus($lihatLaporan ? 200 : 403);
        if ($lihatLaporan) {
            $this->get(route('laporan-kesehatan.ekspor'))->assertDownload('laporan-kesehatan-'.now()->format('Y-m-d').'.pdf');
        }

        $this->get(route('riwayat-kesehatan.show', $taruna))->assertStatus($lihatRiwayat ? 200 : 403);
        $this->get(route('riwayat-kesehatan.keluhan.show', [$taruna, $keluhan]))->assertStatus($lihatRiwayat ? 200 : 403);
    }

    public function test_halaman_riwayat_psikolog_tidak_memuat_data_keluhan_medis(): void
    {
        $this->seed(TarunaSeeder::class);
        Keluhan::create(['taruna_id' => Taruna::firstOrFail()->id, 'tanggal_awal' => today(), 'keluhan' => 'Demam medis rahasia', 'terapi' => 'Paracetamol', 'status' => 'Ringan']);

        $this->actingAs(User::factory()->create(['role' => 'psikolog']))
            ->get(route('riwayat-kesehatan.index'))->assertOk()->assertDontSee('Demam medis rahasia');
    }
}
