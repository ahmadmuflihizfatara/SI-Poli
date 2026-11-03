<?php

namespace Tests\Feature;

use App\Models\Keluhan;
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

    public function test_filter_data_taruna_dan_pegawai(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Taruna::create(self::TARUNA);
        Taruna::create(['nama' => 'Citra Dewi', 'tingkat' => 'I', 'jenis_kelamin' => 'Perempuan']);
        Pegawai::create(['nama' => 'dr. Sinta Uji', 'jabatan' => 'Dokter']);
        Pegawai::create(['nama' => 'Ns. Rina', 'jabatan' => 'Perawat']);

        // dalam satu filter cukup salah satu nilai cocok; antarfilter harus cocok semua
        $this->get(route('taruna.index', ['tingkat' => ['I', 'III']]))->assertSee('Citra Dewi')->assertDontSee('Bima Sakti');
        $this->get(route('taruna.index', ['tingkat' => ['I', 'II'], 'jenis_kelamin' => ['Laki-laki']]))->assertSee('Bima Sakti')->assertDontSee('Citra Dewi');
        $this->get(route('pegawai.index', ['jabatan' => ['Perawat']]))->assertSee('Ns. Rina')->assertDontSee('dr. Sinta Uji');
    }

    public function test_data_taruna_hanya_wajib_nama(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->post(route('taruna.store'), ['nama' => ''])->assertSessionHasErrors('nama');
        $this->post(route('taruna.store'), ['nama' => 'Tanpa NPM'])->assertSessionHasNoErrors();
        $this->post(route('taruna.store'), ['nama' => 'Tanpa NPM Juga', 'npm' => ''])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('taruna', ['nama' => 'Tanpa NPM', 'npm' => null, 'tingkat' => null, 'kamar' => null]);
        $this->get(route('taruna.index'))->assertOk()->assertSee('Tanpa NPM Juga');
    }

    public function test_admin_menghapus_taruna_kecuali_yang_punya_riwayat_kesehatan(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        [$bebas, $sakit] = [Taruna::create(['nama' => 'Bebas']), Taruna::create(self::TARUNA)];
        Keluhan::create(['taruna_id' => $sakit->id, 'tanggal_awal' => today(), 'keluhan' => 'Demam', 'terapi' => 'Istirahat', 'status' => 'Ringan']);

        $this->delete(route('taruna.destroy', $bebas))->assertSessionHas('status', 'Taruna Bebas berhasil dihapus.');
        $this->delete(route('taruna.destroy', $sakit))->assertSessionHas('status', fn (string $pesan) => str_contains($pesan, 'tidak dapat dihapus'));

        $this->assertModelMissing($bebas);
        $this->assertModelExists($sakit);
        $this->assertDatabaseCount('keluhan', 1);
    }

    public function test_admin_menghapus_pegawai_kecuali_yang_tercatat_sebagai_pemeriksa(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        [$bebas, $pemeriksa] = [Pegawai::create(['nama' => 'Ns. Bebas', 'jabatan' => 'Perawat']), Pegawai::create(['nama' => 'dr. Andi', 'jabatan' => 'Dokter'])];
        Keluhan::create(['taruna_id' => Taruna::create(self::TARUNA)->id, 'pemeriksa_id' => $pemeriksa->id, 'tanggal_awal' => today(), 'keluhan' => 'Demam', 'terapi' => 'Istirahat', 'status' => 'Ringan']);

        $this->delete(route('pegawai.destroy', $bebas))->assertSessionHas('status', 'Pegawai Ns. Bebas berhasil dihapus.');
        $this->delete(route('pegawai.destroy', $pemeriksa))->assertSessionHas('status', fn (string $pesan) => str_contains($pesan, 'tidak dapat dihapus'));

        $this->assertModelMissing($bebas);
        $this->assertModelExists($pemeriksa);
    }

    public function test_selain_admin_tidak_bisa_mengelola_data_taruna_dan_pegawai(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'perawat']));

        $this->get(route('taruna.index'))->assertForbidden();
        $this->post(route('taruna.store'), self::TARUNA)->assertForbidden();
        $this->get(route('pegawai.index'))->assertForbidden();
        $this->post(route('pegawai.store'), ['nama' => 'Ns. Rina', 'jabatan' => 'Perawat'])->assertForbidden();
        $this->delete(route('taruna.destroy', Taruna::create(self::TARUNA)))->assertForbidden();
        $this->delete(route('pegawai.destroy', Pegawai::create(['nama' => 'Ns. Rina', 'jabatan' => 'Perawat'])))->assertForbidden();

        $this->assertDatabaseCount('taruna', 1);
        $this->assertDatabaseCount('pegawai', 1);
    }
}
