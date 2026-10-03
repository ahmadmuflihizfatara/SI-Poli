<?php

namespace Tests\Feature;

use App\Models\Keluhan;
use App\Models\KeluhanPsikologi;
use App\Models\RiwayatKonseling;
use App\Models\RiwayatKontrol;
use App\Models\Taruna;
use App\Models\User;
use Database\Seeders\TarunaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatKesehatanTest extends TestCase
{
    use RefreshDatabase;

    public function test_perawat_melihat_riwayat_dari_database_dan_dapat_membuka_detail_taruna(): void
    {
        $this->seed(TarunaSeeder::class);
        $perawat = User::factory()->create(['role' => 'perawat']);
        $taruna = Taruna::firstOrFail();
        $keluhan = Keluhan::create([
            'taruna_id' => $taruna->id,
            'tanggal_awal' => '2026-10-01',
            'keluhan' => 'Demam dari database',
            'terapi' => 'Istirahat',
            'status' => 'Sedang',
            'keterangan' => 'Catatan pemulihan',
        ]);
        RiwayatKontrol::create([
            'keluhan_id' => $keluhan->id,
            'tanggal_kontrol' => '2026-10-02',
            'hasil_kontrol' => 'Suhu mulai turun',
            'keterangan' => 'Kontrol ulang besok',
        ]);

        $this->actingAs($perawat)
            ->get(route('riwayat-kesehatan.index'))
            ->assertOk()
            ->assertSee('Riwayat Kesehatan')
            ->assertSee($taruna->nama)
            ->assertSee('Demam dari database')
            ->assertSee('Suhu mulai turun')
            ->assertDontSee('Ahmad Fauzan');

        $this->get(route('riwayat-kesehatan.show', $taruna))
            ->assertOk()
            ->assertSee('Demam dari database')
            ->assertSee("const selectedTarunaId = {$taruna->id};");
    }

    public function test_riwayat_kesehatan_bisa_dilihat_semua_role_yang_login_dan_taruna_tanpa_riwayat_tidak_memiliki_detail(): void
    {
        $this->seed(TarunaSeeder::class);
        $taruna = Taruna::firstOrFail();

        $this->get(route('riwayat-kesehatan.index'))->assertRedirect(route('login'));

        foreach (['perawat', 'psikolog', 'admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('riwayat-kesehatan.index'))
                ->assertOk()
                ->assertSee('Riwayat Kesehatan')
                ->assertSee(route('riwayat-kesehatan.index'));
        }

        $this->actingAs(User::factory()->create(['role' => 'perawat']))
            ->get(route('riwayat-kesehatan.show', $taruna))
            ->assertNotFound();
    }

    public function test_riwayat_psikologi_tersedia_untuk_psikolog_dan_admin_tetapi_tidak_untuk_perawat(): void
    {
        $this->seed(TarunaSeeder::class);
        $taruna = Taruna::firstOrFail();
        $keluhan = KeluhanPsikologi::create([
            'taruna_id' => $taruna->id,
            'tanggal_awal' => '2026-10-01',
            'keluhan' => 'Kecemasan menjelang ujian',
            'terapi' => 'Konseling awal',
            'lanjut_konseling' => true,
            'tanggal_konseling_selanjutnya' => '2026-10-08',
        ]);
        RiwayatKonseling::create([
            'keluhan_psikologi_id' => $keluhan->id,
            'tanggal_konseling' => '2026-10-03',
            'hasil_konseling' => 'Evaluasi sesi pertama',
        ]);

        foreach (['psikolog', 'admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('riwayat-kesehatan.index'))
                ->assertOk()
                ->assertSee('id="psychologyHistoryTab"', false)
                ->assertSee('Kecemasan menjelang ujian')
                ->assertSee('Evaluasi sesi pertama');
        }

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('riwayat-kesehatan.index', ['bagian' => 'psikolog']))
            ->assertOk()
            ->assertSee('Pilih tampilan bagian')
            ->assertSee('id="psychologyHistoryTab"', false)
            ->assertSee('aria-pressed="true"', false)
            ->assertSee('Riwayat Psikologi')
            ->assertSee('Status Konseling')
            ->assertSee('class="pl-table lk"', false);

        $this->actingAs(User::factory()->create(['role' => 'perawat']))
            ->get(route('riwayat-kesehatan.index'))
            ->assertOk()
            ->assertDontSee('Pilih tampilan bagian')
            ->assertDontSee('Kecemasan menjelang ujian')
            ->assertDontSee('Evaluasi sesi pertama');
    }
}
