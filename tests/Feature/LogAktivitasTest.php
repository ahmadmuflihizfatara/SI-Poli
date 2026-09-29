<?php

namespace Tests\Feature;

use App\Models\Keluhan;
use App\Models\LogAktivitas;
use App\Models\Taruna;
use App\Models\User;
use Database\Seeders\TarunaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogAktivitasTest extends TestCase
{
    use RefreshDatabase;

    public function test_tambah_dan_edit_laporan_kesehatan_tercatat_dan_hanya_admin_bisa_melihat_log(): void
    {
        $this->seed(TarunaSeeder::class);
        $perawat = User::factory()->create(['role' => 'perawat']);
        $admin = User::factory()->create(['role' => 'admin']);
        $taruna = Taruna::first();

        $this->actingAs($perawat)->post(route('laporan-kesehatan.store'), [
            'taruna_id' => $taruna->id, 'tekanan_darah' => '120/80', 'suhu' => 38, 'nadi' => 90,
            'keluhan' => 'Demam', 'terapi' => 'Paracetamol', 'status' => 'Ringan',
            'tanggal_kontrol_selanjutnya' => today()->addDay()->toDateString(),
        ])->assertRedirect();
        $keluhan = Keluhan::first();

        $this->post(route('laporan-kesehatan.kontrol.update', $keluhan), [
            'hasil_pemeriksaan' => 'Demam turun', 'terapi' => 'Istirahat', 'perlu_rujukan' => '0',
            'tanggal_kontrol_selanjutnya' => today()->addDays(2)->toDateString(),
        ])->assertRedirect();
        $this->patch(route('laporan-kesehatan.toggle-sembuh', $keluhan));
        $this->patch(route('laporan-kesehatan.toggle-sembuh', $keluhan)); // sudah sembuh: tidak dicatat lagi

        $this->assertSame(['Tambah', 'Edit', 'Edit'], LogAktivitas::orderBy('id')->pluck('aksi')->all());
        $this->assertTrue(LogAktivitas::where('user_id', $perawat->id)->where('keluhan_id', $keluhan->id)->count() === 3);
        $this->assertStringContainsString('dari Paracetamol menjadi Istirahat', LogAktivitas::find(2)->pesan);

        $this->get(route('log.index'))->assertForbidden();
        $this->get(route('log.show', LogAktivitas::first()))->assertForbidden();
        $this->get(route('dashboard'))->assertOk()->assertDontSee('Log Sistem');

        $this->actingAs($admin)->get(route('dashboard'))->assertSee('Log Sistem');
        $this->get(route('log.index'))->assertOk()->assertSee($taruna->nama)->assertSee('Perawat');
        $this->get(route('log.index', ['aksi' => 'Tambah']))->assertOk()->assertDontSee('menjadi Sembuh');
        $this->get(route('log.show', LogAktivitas::first()))->assertOk()->assertSee('Lihat laporan kesehatan')
            ->assertSee(route('laporan-kesehatan.show', $keluhan));
        $this->get(route('log.index', ['pengguna' => 'admin']))->assertOk()->assertSee('Tidak ada log yang cocok');
    }
}
