<?php

namespace Tests\Feature;

use App\Models\Keluhan;
use App\Models\KeluhanPsikologi;
use App\Models\RiwayatKonseling;
use App\Models\RiwayatKontrol;
use App\Models\Taruna;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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

    public function test_ekspor_pdf_riwayat_kesehatan_semua_dan_per_taruna(): void
    {
        $this->seed(TarunaSeeder::class);
        [$a, $b, $tanpaRiwayat] = Taruna::take(3)->get();
        $dasar = ['terapi' => 'Istirahat'];
        Keluhan::create($dasar + ['taruna_id' => $a->id, 'tanggal_awal' => '2026-10-01', 'keluhan' => 'Batuk', 'status' => 'Ringan']);
        Keluhan::create($dasar + ['taruna_id' => $a->id, 'tanggal_awal' => '2026-09-01', 'keluhan' => 'Demam', 'status' => 'Ringan']);
        Keluhan::create($dasar + ['taruna_id' => $b->id, 'tanggal_awal' => '2026-10-02', 'keluhan' => 'Pusing', 'status' => 'Sedang']);

        $this->actingAs(User::factory()->create(['role' => 'perawat']));
        $this->get(route('riwayat-kesehatan.ekspor'))->assertOk()->assertDownload('riwayat-kesehatan-'.now()->format('Y-m-d').'.pdf');
        $this->get(route('riwayat-kesehatan.ekspor.satu', $a))->assertOk()->assertDownload("riwayat-kesehatan-{$a->npm}.pdf");
        $this->get(route('riwayat-kesehatan.ekspor.satu', $tanpaRiwayat))->assertNotFound();
        $this->get(route('riwayat-kesehatan.ekspor', ['status' => 'Kritis']))->assertSessionHasErrors('status');

        // Filter halaman ikut ke PDF: hanya episode Ringan sejak 15 September
        Pdf::shouldReceive('loadView')->once()->withArgs(function (string $view, array $data) use ($a): bool {
            return $data['perTaruna']->keys()->all() === [$a->id] && $data['perTaruna'][$a->id]->pluck('keluhan')->all() === ['Batuk'];
        })->andReturnSelf();
        Pdf::shouldReceive('setPaper')->andReturnSelf();
        Pdf::shouldReceive('download')->andReturn(response('pdf'));
        $this->get(route('riwayat-kesehatan.ekspor', ['status' => 'Ringan', 'dari' => '2026-09-15']))->assertOk();

        $this->actingAs(User::factory()->create(['role' => 'psikolog']))
            ->get(route('riwayat-kesehatan.ekspor'))->assertForbidden();
    }

    public function test_ekspor_pdf_riwayat_psikologi_untuk_psikolog_dan_admin(): void
    {
        $this->seed(TarunaSeeder::class);
        [$a, $tanpaRiwayat] = Taruna::take(2)->get();
        $dasar = ['taruna_id' => $a->id, 'terapi' => 'Konseling awal'];
        KeluhanPsikologi::create($dasar + ['tanggal_awal' => '2026-10-01', 'keluhan' => 'Cemas', 'lanjut_konseling' => true, 'tanggal_konseling_selanjutnya' => '2026-10-08']);
        KeluhanPsikologi::create($dasar + ['tanggal_awal' => '2026-09-01', 'keluhan' => 'Sulit tidur', 'lanjut_konseling' => false]);

        $this->actingAs(User::factory()->create(['role' => 'psikolog']));
        $this->get(route('riwayat-kesehatan.index'))->assertSee('data-ekspor', false)->assertSee('"psikologi":true', false);
        $this->get(route('riwayat-kesehatan.psikologi.ekspor'))->assertOk()->assertDownload('riwayat-psikologi-'.now()->format('Y-m-d').'.pdf');
        $this->get(route('riwayat-kesehatan.psikologi.ekspor.satu', $a))->assertOk()->assertDownload("riwayat-psikologi-{$a->npm}.pdf");
        $this->get(route('riwayat-kesehatan.psikologi.ekspor.satu', $tanpaRiwayat))->assertNotFound();
        $this->get(route('riwayat-kesehatan.psikologi.ekspor', ['status' => 'Ringan']))->assertSessionHasErrors('status');

        // Status psikologi = keterangan konseling
        Pdf::shouldReceive('loadView')->once()->withArgs(function (string $view, array $data): bool {
            return $data['psikologi'] && $data['perTaruna']->flatten()->pluck('keluhan')->all() === ['Cemas'];
        })->andReturnSelf();
        Pdf::shouldReceive('setPaper')->andReturnSelf();
        Pdf::shouldReceive('download')->andReturn(response('pdf'));
        $this->get(route('riwayat-kesehatan.psikologi.ekspor', ['status' => 'Melanjutkan konseling']))->assertOk();

        $this->actingAs(User::factory()->create(['role' => 'perawat']))
            ->get(route('riwayat-kesehatan.psikologi.ekspor'))->assertForbidden();
    }
}
