<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PengaturanTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengaturan_tema_dibuka_dari_profil_dan_tombol_tema_mengambang_dihapus(): void
    {
        $this->get(route('pengaturan'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create(['role' => 'psikolog']))
            ->get(route('dashboard'))->assertOk()
            ->assertSee('href="'.route('pengaturan').'" class="pl-side__user"', false)
            ->assertDontSee('createThemeToggle');

        $this->get(route('pengaturan'))->assertOk()
            ->assertSee('Mode warna')->assertSee('Terang')->assertSee('Gelap')->assertSee('Ikuti sistem');
    }

    public function test_pengguna_bisa_mengubah_kata_sandinya_sendiri_dari_pengaturan(): void
    {
        $user = User::factory()->create(['role' => 'pengasuh', 'sandi_baru' => Hash::make('mintaLama1'), 'sandi_diminta_at' => now()]);
        $this->actingAs($user)->get(route('pengaturan'))->assertOk()->assertSee('Ubah Kata Sandi');

        $this->put(route('pengaturan.sandi'), ['current_password' => 'salah', 'password' => 'sandiBaru1', 'password_confirmation' => 'sandiBaru1'])
            ->assertSessionHasErrors(['current_password' => 'Kata sandi saat ini salah.']);
        $this->put(route('pengaturan.sandi'), ['current_password' => 'password', 'password' => 'pendek', 'password_confirmation' => 'pendek'])
            ->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('password', $user->refresh()->password));

        $this->put(route('pengaturan.sandi'), ['current_password' => 'password', 'password' => 'sandiBaru1', 'password_confirmation' => 'sandiBaru1'])
            ->assertRedirect(route('pengaturan'))->assertSessionHas('status', 'Kata sandi berhasil diubah.');
        $user->refresh();
        $this->assertTrue(Hash::check('sandiBaru1', $user->password));
        $this->assertNull($user->sandi_baru); // permintaan lupa sandi yang tertunda dibatalkan

        auth()->logout();
        $this->put(route('pengaturan.sandi'), [])->assertRedirect(route('login'));
    }
}
