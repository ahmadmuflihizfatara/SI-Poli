<?php

namespace Tests\Feature;

use App\Models\Keluhan;
use App\Models\Pemeriksaan;
use App\Models\Taruna;
use App\Models\User;
use Database\Seeders\TarunaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ManajemenAkunTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_membuat_mengubah_dan_menghapus_akun(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'admin']);
        $perawat = User::factory()->create(['role' => 'perawat']);

        $this->actingAs($perawat)->get(route('akun.index'))->assertForbidden();
        $this->get(route('dashboard'))->assertDontSee('Manajemen Akun');

        $this->actingAs($admin)->get(route('akun.index'))->assertOk()->assertSee($perawat->username)->assertSee('Saat ini');
        $this->post(route('akun.store'), [
            'name' => 'Sari Perawat', 'username' => 'sari', 'role' => 'perawat', 'akses_tambah' => '1',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect();
        $sari = User::firstWhere('username', 'sari');
        $this->assertTrue($sari->akses_tambah);
        $this->assertFalse($sari->akses_edit);
        $this->assertTrue(Hash::check('rahasia123', $sari->password));

        // Nama pengguna harus unik, kata sandi harus huruf + angka
        $this->post(route('akun.store'), ['name' => 'X', 'username' => 'sari', 'role' => 'perawat', 'password' => 'abc', 'password_confirmation' => 'abc'])
            ->assertSessionHasErrors(['username', 'password']);

        $this->put(route('akun.update', $sari), ['role' => 'perawat', 'akses_edit' => '1', 'password' => 'baru12345', 'password_confirmation' => 'baru12345'])
            ->assertRedirect(route('akun.show', $sari));
        $sari->refresh();
        $this->assertSame([false, true], [$sari->akses_tambah, $sari->akses_edit]);
        $this->assertTrue(Hash::check('baru12345', $sari->password));

        // Admin tidak bisa menurunkan role atau menghapus dirinya sendiri
        $this->put(route('akun.update', $admin), ['role' => 'perawat'])->assertSessionHasErrors('role');
        $this->delete(route('akun.destroy', $admin))->assertForbidden();

        $this->get(route('akun.show', $sari))->assertOk()->assertSee('Hapus Akun');
        $this->delete(route('akun.destroy', $sari))->assertRedirect(route('akun.index'));
        $this->assertModelMissing($sari);
    }

    public function test_lupa_kata_sandi_berlaku_setelah_disetujui_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $perawat = User::factory()->create(['role' => 'perawat', 'username' => 'budi']);

        $this->post(route('password.store'), ['username' => 'budi', 'password' => 'sandiBaru1', 'password_confirmation' => 'sandiBaru1'])
            ->assertRedirect(route('login'))->assertSessionHas('status');
        // Nama pengguna tidak dikenal: pesan sama, tidak ada yang berubah
        $this->post(route('password.store'), ['username' => 'tidakada', 'password' => 'sandiBaru1', 'password_confirmation' => 'sandiBaru1'])
            ->assertRedirect(route('login'))->assertSessionHas('status');

        // Belum disetujui: kata sandi lama masih berlaku
        $this->post(route('login.store'), ['username' => 'budi', 'password' => 'sandiBaru1'])->assertSessionHasErrors('username');
        $this->assertNotNull($perawat->fresh()->sandi_diminta_at);

        $this->actingAs($admin)->get(route('akun.index'))->assertSee('menunggu persetujuan');
        $this->get(route('akun.show', $perawat))->assertSee('Persetujuan Perubahan');
        $this->post(route('akun.sandi.setujui', $perawat))->assertRedirect();
        $this->assertNull($perawat->fresh()->sandi_diminta_at);
        auth()->logout();

        $this->post(route('login.store'), ['username' => 'budi', 'password' => 'sandiBaru1'])->assertRedirect(route('dashboard'));
    }

    public function test_akses_tambah_dan_edit_membatasi_perawat(): void
    {
        $this->seed(TarunaSeeder::class);
        $taruna = Taruna::where('tingkat', 'I')->first();
        $lihat = User::factory()->create(['role' => 'perawat', 'akses_tambah' => false, 'akses_edit' => false]);
        $keluhan = Keluhan::create(['taruna_id' => $taruna->id, 'tanggal_awal' => today(), 'keluhan' => 'Demam', 'terapi' => 'Paracetamol', 'status' => 'Ringan']);

        $this->actingAs($lihat)->get(route('laporan-kesehatan.index'))->assertOk()->assertDontSee('Tambahkan keluhan baru')->assertSee('Tidak punya akses Edit');
        $this->get(route('laporan-kesehatan.create'))->assertForbidden();
        $this->patch(route('laporan-kesehatan.toggle-sembuh', $keluhan))->assertForbidden();
        $this->get(route('laporan-kesehatan.kontrol.edit', $keluhan))->assertForbidden();

        $vital = ['tekanan_darah' => '120/80', 'nadi' => '80', 'suhu' => '36.5', 'pernapasan' => '20', 'keluhan' => 'Tidak ada'];
        $kirim = fn ($data) => $this->post(route('pemeriksaan-kesehatan.mptb.simpan'), ['tanggal' => today()->toDateString(), 'sesi' => 'Pagi', 'data' => [$taruna->id => $data]]);
        $kirim($vital)->assertSessionHas('status', fn ($s) => str_contains($s, '1 dilewati'));
        $this->assertSame(0, Pemeriksaan::count());

        // Hanya akses Tambah: boleh mengisi baris baru, tapi tidak mengubahnya lagi
        $lihat->update(['akses_tambah' => true]);
        $kirim($vital);
        $kirim(['suhu' => '38'] + $vital);
        $this->assertSame('36.5', Pemeriksaan::sole()->suhu);
        $this->get(route('pemeriksaan-kesehatan.mptb.index'))->assertSee('disabled');
    }
}
