<?php

namespace Tests\Feature;

use App\Models\Keluhan;
use App\Models\KeluhanPsikologi;
use App\Models\LogAktivitas;
use App\Models\Taruna;
use App\Models\User;
use Database\Seeders\TarunaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PsikologTest extends TestCase
{
    use RefreshDatabase;

    public function test_psikolog_hanya_dashboard_dan_laporan_konseling(): void
    {
        $this->seed(TarunaSeeder::class);
        $psikolog = User::factory()->create(['role' => 'psikolog']);
        $taruna = Taruna::first();
        $medis = Keluhan::create(['taruna_id' => $taruna->id, 'tanggal_awal' => today(), 'keluhan' => 'Demam', 'terapi' => 'Paracetamol', 'status' => 'Ringan']);

        $this->actingAs($psikolog)->get(route('dashboard'))->assertOk()
            ->assertSee('Taruna yang Melanjutkan Konseling')->assertDontSee('Sakit Ringan')
            ->assertDontSee('Pemeriksaan Kesehatan')->assertDontSee('Kejadian Luar Biasa')->assertDontSee('Log Sistem');
        // Bagian perawat tertutup, termasuk data keluhan medis
        $this->get(route('laporan-kesehatan.index'))->assertOk()->assertSee('Evaluasi konseling')->assertDontSee('Demam');
        $this->get(route('laporan-kesehatan.show', $medis))->assertForbidden();
        $this->get(route('pemeriksaan-kesehatan.index'))->assertForbidden();
        $this->get(route('klb.index'))->assertForbidden();
        // Pilihan bagian hanya untuk admin
        $this->get(route('laporan-kesehatan.index', ['bagian' => 'perawat']))->assertSee('Evaluasi konseling')->assertDontSee('Pilih tampilan bagian');

        $this->get(route('laporan-kesehatan.psikologi.create'))->assertOk();
        $this->post(route('laporan-kesehatan.psikologi.store'), [
            'taruna_id' => $taruna->id, 'keluhan' => 'Cemas', 'terapi' => 'Terapi kognitif', 'lanjut_konseling' => '1',
        ])->assertSessionHasErrors('tanggal_konseling_selanjutnya'); // lanjut wajib ada tanggal
        $this->post(route('laporan-kesehatan.psikologi.store'), [
            'taruna_id' => $taruna->id, 'keluhan' => 'Cemas', 'terapi' => 'Terapi kognitif', 'lanjut_konseling' => '1',
            'tanggal_konseling_selanjutnya' => today()->addWeek()->toDateString(),
        ])->assertRedirect(route('laporan-kesehatan.index'));
        $k = KeluhanPsikologi::sole();
        $this->assertTrue($k->lanjut_konseling);

        $this->get(route('laporan-kesehatan.index'))->assertSee('Cemas')->assertSee('Melanjutkan konseling');
        $this->get(route('dashboard'))->assertSee('data-hitung="1"', false);

        // Perbarui: tidak lanjut -> selesai, tanggal diabaikan, riwayat bertambah
        $this->get(route('laporan-kesehatan.psikologi.edit', $k))->assertOk()->assertSee('Perbarui Hasil Konseling');
        $this->put(route('laporan-kesehatan.psikologi.update', $k), [
            'hasil_konseling' => 'Sudah membaik', 'terapi' => 'Terapi kognitif', 'lanjut_konseling' => '0',
            'tanggal_konseling_selanjutnya' => today()->addDay()->toDateString(),
        ])->assertRedirect(route('laporan-kesehatan.index'));
        $k->refresh();
        $this->assertFalse($k->lanjut_konseling);
        $this->assertNull($k->tanggal_konseling_selanjutnya);
        $this->assertNotNull($k->selesai_at);
        $this->get(route('laporan-kesehatan.psikologi.show', $k))->assertOk()->assertSee('Sudah membaik')->assertSee('Tidak melanjutkan konseling');
        $this->get(route('laporan-kesehatan.ekspor'))->assertOk()->assertDownload('laporan-psikologi-'.now()->format('Y-m-d').'.pdf');

        $this->assertSame(['Laporan Psikologi'], LogAktivitas::distinct()->pluck('sumber_daya')->all());

        // Perawat tidak bisa membuka data psikologi
        $perawat = User::factory()->create(['role' => 'perawat']);
        $this->actingAs($perawat)->get(route('laporan-kesehatan.index'))->assertSee('Demam')->assertDontSee('Cemas');
        $this->get(route('laporan-kesehatan.psikologi.show', $k))->assertForbidden();
    }

    public function test_admin_bisa_berpindah_bagian_perawat_dan_psikolog(): void
    {
        $this->seed(TarunaSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);
        KeluhanPsikologi::create(['taruna_id' => Taruna::first()->id, 'tanggal_awal' => today(), 'keluhan' => 'Cemas', 'terapi' => 'Konseling', 'lanjut_konseling' => true]);

        $this->actingAs($admin)->get(route('laporan-kesehatan.index'))
            ->assertSee('Pilih tampilan bagian')
            ->assertSee('Kesehatan')
            ->assertSee('Psikologi')
            ->assertSee('Status Pemulihan');
        $this->get(route('laporan-kesehatan.index', ['bagian' => 'psikolog']))
            ->assertSee('Evaluasi konseling')
            ->assertSee('Cemas')
            ->assertSee('Kesehatan')
            ->assertSee('Psikologi');
        // Pilihan diingat saat pindah halaman lewat sidebar
        $this->get(route('dashboard'))->assertSee('Perbandingan Kesehatan Psikologi Taruna');
        $this->get(route('dashboard', ['bagian' => 'perawat']))->assertSee('Sakit Ringan');
        $this->get(route('laporan-kesehatan.index'))->assertSee('Status Pemulihan');
    }
}
