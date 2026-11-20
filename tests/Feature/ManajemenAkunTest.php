<?php

namespace Tests\Feature;

use App\Models\KegiatanPemeriksaan;
use App\Models\Keluhan;
use App\Models\LogAktivitas;
use App\Models\Taruna;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\TarunaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_admin_membuat_mengubah_dan_menonaktifkan_akun_dan_semuanya_tercatat_di_log(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'admin']);
        $perawat = User::factory()->create(['role' => 'perawat']);

        $this->actingAs($perawat)->get(route('akun.index'))->assertForbidden();
        $this->get(route('dashboard'))->assertDontSee('Manajemen Akun');

        $this->actingAs($admin)->withSession(['auth.password_confirmed_at' => time()]);
        $this->get(route('akun.index'))->assertOk()->assertSee($perawat->username)->assertSee('Saat ini');
        $this->post(route('akun.store'), [
            'name' => 'Sari Perawat', 'username' => 'sari_perawat', 'role' => 'perawat',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect();
        $sari = User::firstWhere('username', 'sari_perawat');
        $this->assertTrue(Hash::check('rahasia123', $sari->password));
        $this->assertTrue($sari->wajib_ganti_sandi);

        $this->post(route('akun.store'), [
            'name' => 'Dokter Uji', 'username' => 'dokter-uji', 'role' => 'dokter',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect();
        $this->assertSame('dokter', User::where('username', 'dokter-uji')->sole()->role);

        // Nama pengguna harus unik, kata sandi harus huruf + angka
        $this->post(route('akun.store'), ['name' => 'X', 'username' => 'sari_perawat', 'role' => 'perawat', 'password' => 'abc', 'password_confirmation' => 'abc'])
            ->assertSessionHasErrors(['username', 'password']);

        $tokenLama = $sari->remember_token;
        $this->put(route('akun.update', $sari), ['role' => 'pengasuh', 'password' => 'baru12345', 'password_confirmation' => 'baru12345'])
            ->assertRedirect(route('akun.show', $sari));
        $sari->refresh();
        $this->assertSame('pengasuh', $sari->role);
        $this->assertTrue(Hash::check('baru12345', $sari->password));
        // Sesi "ingat saya" akun terdampak diputus
        $this->assertNotSame($tokenLama, $sari->remember_token);

        // Admin tidak bisa menurunkan role atau menonaktifkan dirinya sendiri
        $this->put(route('akun.update', $admin), ['role' => 'perawat'])->assertSessionHasErrors('role');
        $this->patch(route('akun.nonaktifkan', $admin))->assertForbidden();

        // Dinonaktifkan, bukan dihapus: baris & atribusi tetap ada, akun bisa diaktifkan kembali
        $this->get(route('akun.show', $sari))->assertOk()->assertSee('Nonaktifkan Akun');
        $this->patch(route('akun.nonaktifkan', $sari))->assertRedirect(route('akun.show', $sari));
        $this->assertNotNull($sari->fresh()->nonaktif_at);
        $this->get(route('akun.index'))->assertSee('Nonaktif');
        $this->patch(route('akun.aktifkan', $sari))->assertRedirect(route('akun.show', $sari));
        $this->assertNull($sari->fresh()->nonaktif_at);

        $this->assertSame([
            'Akun sari_perawat (Sari Perawat) ditambahkan dengan role Perawat.',
            'Akun dokter-uji (Dokter Uji) ditambahkan dengan role Dokter.',
            'Akun sari_perawat diubah: role Perawat menjadi Pengasuh, kata sandi diatur ulang admin.',
            'Akun sari_perawat dinonaktifkan.',
            'Akun sari_perawat diaktifkan kembali.',
        ], LogAktivitas::where('sumber_daya', 'Manajemen Akun')->where('user_id', $admin->id)->orderBy('id')->pluck('pesan')->all());
    }

    public function test_aksi_manajemen_akun_wajib_konfirmasi_ulang_kata_sandi_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $perawat = User::factory()->create(['role' => 'perawat']);

        $this->actingAs($admin)->get(route('akun.create'))->assertRedirect(route('password.confirm'));
        $this->from(route('akun.show', $perawat))->patch(route('akun.nonaktifkan', $perawat))->assertRedirect(route('password.confirm'));
        $this->assertNull($perawat->fresh()->nonaktif_at);

        $this->get(route('password.confirm'))->assertOk()->assertSee('Konfirmasi kata sandi')->assertSee($admin->username);
        $this->post(route('password.confirm.store'), ['password' => 'salah123'])->assertSessionHasErrors(['password' => 'Kata sandi salah.']);
        // Setelah konfirmasi, admin dikembalikan ke halaman formulir aksinya
        $this->post(route('password.confirm.store'), ['password' => 'password'])->assertRedirect(route('akun.show', $perawat));
        $this->patch(route('akun.nonaktifkan', $perawat))->assertRedirect(route('akun.show', $perawat));
        $this->assertNotNull($perawat->fresh()->nonaktif_at);
    }

    /** @return array<string, array{string}> */
    public static function namaPenggunaLemah(): array
    {
        return [
            'nama role' => ['dokter'],
            'akun umum' => ['administrator'],
            'kurang dari 5 karakter' => ['sari'],
            'huruf besar menyamarkan nama terlarang' => ['Admin'],
        ];
    }

    #[DataProvider('namaPenggunaLemah')]
    public function test_nama_pengguna_yang_mudah_ditebak_ditolak(string $username): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->withSession(['auth.password_confirmed_at' => time()])
            ->post(route('akun.store'), ['name' => 'Uji', 'username' => $username, 'role' => 'perawat', 'password' => 'rahasia123', 'password_confirmation' => 'rahasia123'])
            ->assertSessionHasErrors('username');

        $this->assertDatabaseMissing('users', ['name' => 'Uji']);
    }

    public function test_akun_dengan_sandi_dari_admin_wajib_mengganti_sandi_sebelum_memakai_sistem(): void
    {
        $user = User::factory()->create(['role' => 'perawat']);
        $user->forceFill(['wajib_ganti_sandi' => true])->save();

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('pengaturan'));
        $this->get(route('laporan-kesehatan.index'))->assertRedirect(route('pengaturan'));
        $this->get(route('pengaturan'))->assertOk();

        $this->put(route('pengaturan.sandi'), ['current_password' => 'password', 'password' => 'sandiSendiri1', 'password_confirmation' => 'sandiSendiri1'])
            ->assertRedirect(route('pengaturan'));
        $this->assertFalse($user->fresh()->wajib_ganti_sandi);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_akun_nonaktif_tidak_bisa_masuk_dan_sesinya_dikeluarkan(): void
    {
        $user = User::factory()->create(['role' => 'perawat', 'username' => 'budi_nonaktif']);
        $user->forceFill(['nonaktif_at' => now()])->save();

        $this->post(route('login.store'), ['username' => 'budi_nonaktif', 'password' => 'password'])
            ->assertSessionHasErrors(['password' => 'Nama pengguna atau kata sandi salah.']);
        $this->assertGuest();

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
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
        $this->withSession(['auth.password_confirmed_at' => time()])->get(route('akun.show', $perawat))->assertSee('Persetujuan Perubahan');
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
