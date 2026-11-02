<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\Taruna;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataTarunaPegawaiTest extends TestCase
{
    use RefreshDatabase;

    private const TARUNA = ['nama' => 'Bima Sakti', 'npm' => '2201999', 'tingkat' => 'II', 'kelas' => 'II RKS B', 'jenis_kelamin' => 'Laki-laki', 'kamar' => 'B202'];

    public function test_admin_menambah_dan_mengubah_data_taruna(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('taruna.index'))->assertOk()->assertSee('Data Taruna');

        $this->post(route('taruna.store'), self::TARUNA)->assertRedirect(route('taruna.index'));
        $taruna = Taruna::where('npm', '2201999')->firstOrFail();

        // NPM wajib unik, kecuali untuk taruna itu sendiri saat diubah
        $this->post(route('taruna.store'), ['nama' => 'Lain'] + self::TARUNA)->assertSessionHasErrors('npm');
        $this->put(route('taruna.update', $taruna), ['kamar' => 'C303'] + self::TARUNA)->assertSessionHasNoErrors();

        $this->assertSame('C303', $taruna->fresh()->kamar);
        $this->get(route('taruna.index', ['q' => 'Bima']))->assertSee('Bima Sakti')->assertSee('C303');
    }

    public function test_admin_menambah_dan_mengubah_data_pegawai(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->post(route('pegawai.store'), ['nama' => 'Ns. Rina', 'jabatan' => 'Perawat'])->assertRedirect(route('pegawai.index'));
        $pegawai = Pegawai::where('nama', 'Ns. Rina')->firstOrFail();

        $this->post(route('pegawai.store'), ['nama' => 'Budi', 'jabatan' => 'Satpam'])->assertSessionHasErrors('jabatan');
        $this->post(route('pegawai.store'), ['nama' => 'Ns. Rina', 'jabatan' => 'Dokter'])->assertSessionHasErrors('nama');
        $this->put(route('pegawai.update', $pegawai), ['nama' => 'Ns. Rina', 'jabatan' => 'Psikolog'])->assertSessionHasNoErrors();

        $this->assertSame('Psikolog', $pegawai->fresh()->jabatan);
        $this->get(route('pegawai.index'))->assertOk()->assertSee('Ns. Rina');
    }

    public function test_selain_admin_tidak_bisa_mengelola_data_taruna_dan_pegawai(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'perawat']));

        $this->get(route('taruna.index'))->assertForbidden();
        $this->post(route('taruna.store'), self::TARUNA)->assertForbidden();
        $this->get(route('pegawai.index'))->assertForbidden();
        $this->post(route('pegawai.store'), ['nama' => 'Ns. Rina', 'jabatan' => 'Perawat'])->assertForbidden();

        $this->assertDatabaseCount('taruna', 0);
        $this->assertDatabaseCount('pegawai', 0);
    }
}
