<?php

namespace Tests\Feature;

use App\Models\KegiatanPemeriksaan;
use App\Models\Keluhan;
use App\Models\Taruna;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\TarunaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ManajemenAkunTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_menyiapkan_role_dokter(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['username' => 'dokter', 'role' => 'dokter']);
    }

    public function test_filter_role_manajemen_akun(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'username' => 'admin']));
        User::factory()->create(['role' => 'psikolog', 'username' => 'psikolog_uji']);
        User::factory()->create(['role' => 'perawat', 'username' => 'perawat_uji']);

        $this->get(route('akun.index', ['role' => ['psikolog']]))->assertOk()->assertSee('psikolog_uji')->assertDontSee('perawat_uji');
    }

    public function test_admin_membuat_mengubah_dan_menghapus_akun(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'admin']);
        $perawat = User::factory()->create(['role' => 'perawat']);

        $this->actingAs($perawat)->get(route('akun.index'))->assertForbidden();
        $this->get(route('dashboard'))->assertDontSee('Manajemen Akun');

        $this->actingAs($admin)->get(route('akun.index'))->assertOk()->assertSee($perawat->username)->assertSee('Saat ini');
        $this->post(route('akun.store'), [
            'name' => 'Sari Perawat', 'username' => 'sari', 'role' => 'perawat',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect();
        $sari = User::firstWhere('username', 'sari');
        $this->assertTrue(Hash::check('rahasia123', $sari->password));

        $this->post(route('akun.store'), [
            'name' => 'Dokter Uji', 'username' => 'dokter-uji', 'role' => 'dokter',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect();
        $this->assertSame('dokter', User::where('username', 'dokter-uji')->sole()->role);

        // Nama pengguna harus unik, kata sandi harus huruf + angka
        $this->post(route('akun.store'), ['name' => 'X', 'username' => 'sari', 'role' => 'perawat', 'password' => 'abc', 'password_confirmation' => 'abc'])
            ->assertSessionHasErrors(['username', 'password']);

        $this->put(route('akun.update', $sari), ['role' => 'pengasuh', 'password' => 'baru12345', 'password_confirmation' => 'baru12345'])
            ->assertRedirect(route('akun.show', $sari));
        $sari->refresh();
        $this->assertSame('pengasuh', $sari->role);
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
        $this->post(route('login.store'), ['username' => 'budi', 'password' => 'sandiBaru1'])->assertSessionHasErrors('password');
        $this->assertNotNull($perawat->fresh()->sandi_diminta_at);

        $this->actingAs($admin)->get(route('akun.index'))->assertSee('menunggu persetujuan');
        $this->get(route('akun.show', $perawat))->assertSee('Persetujuan Perubahan');
        $this->post(route('akun.sandi.setujui', $perawat))->assertRedirect();
        $this->assertNull($perawat->fresh()->sandi_diminta_at);
        auth()->logout();

        $this->post(route('login.store'), ['username' => 'budi', 'password' => 'sandiBaru1'])->assertRedirect(route('dashboard'));
    }

    public function test_asisten_kesehatan_dan_pengasuh_hanya_melihat_dashboard_dan_laporan_kesehatan(): void
    {
        $this->seed(TarunaSeeder::class);
        $keluhan = Keluhan::create(['taruna_id' => Taruna::first()->id, 'tanggal_awal' => today(), 'keluhan' => 'Demam', 'terapi' => 'Paracetamol', 'status' => 'Ringan']);
        $kegiatan = KegiatanPemeriksaan::create(['jenis' => 'MPTB', 'nama' => 'MPTB 2026', 'tanggal_mulai' => today()]);

        foreach (['asisten_kesehatan', 'pengasuh'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));

            $this->get(route('dashboard'))->assertOk()->assertDontSee('Riwayat Kesehatan');
            $this->get(route('laporan-kesehatan.index'))->assertOk()->assertSee('Demam')->assertDontSee('Tambah keluhan');
            $this->get(route('laporan-kesehatan.show', $keluhan))->assertOk()->assertDontSee('Nyatakan Sembuh')->assertDontSee('Catat Kontrol');

            // Tanpa tambah/edit dan tanpa halaman lain
            $this->get(route('laporan-kesehatan.create'))->assertForbidden();
            $this->patch(route('laporan-kesehatan.toggle-sembuh', $keluhan))->assertForbidden();
            $this->post(route('laporan-kesehatan.kontrol.update', $keluhan))->assertForbidden();
            $this->get(route('riwayat-kesehatan.index'))->assertForbidden();
            // Pengasuh boleh membuka rikkes aktif (lihat PemeriksaanTest), asisten kesehatan tidak
            $this->get(route('pemeriksaan-kesehatan.show', $kegiatan))->assertStatus($role === 'pengasuh' ? 200 : 403);
            $this->get(route('klb.index'))->assertForbidden();
            $this->get(route('laporan-kesehatan.psikologi.create'))->assertForbidden();
        }
        $this->assertNotSame('Sembuh', $keluhan->fresh()->status_pemulihan);
    }
}
