<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_kosong_menampilkan_semua_galat_berbahasa_indonesia_di_bawah_kolomnya(): void
    {
        $this->from(route('login'))->post(route('login.store'), [])->assertRedirect(route('login'));

        $this->get(route('login'))->assertOk()
            ->assertSee('Nama pengguna wajib diisi.')
            ->assertSee('Kata sandi wajib diisi.')
            ->assertSee('id="username-galat"', false)
            ->assertSee('aria-describedby="username-galat"', false)
            ->assertSee('id="password-galat"', false)
            ->assertSee('aria-describedby="password-galat"', false)
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('novalidate', false);
    }

    public function test_gagal_masuk_galat_di_kata_sandi_dan_fokus_pindah_ke_kata_sandi(): void
    {
        User::factory()->create(['username' => 'budi']);

        $this->from(route('login'))->post(route('login.store'), ['username' => 'budi', 'password' => 'salah123'])
            ->assertSessionHasErrors(['password' => 'Nama pengguna atau kata sandi salah.']);

        $halaman = $this->get(route('login'))->assertOk()->assertSee('value="budi"', false)->getContent();
        $this->assertMatchesRegularExpression('/id="password"[^>]*autofocus/', $halaman);
        $this->assertDoesNotMatchRegularExpression('/id="username"[^>]*autofocus/', $halaman);
    }

    public function test_lupa_sandi_punya_tombol_mata_dan_daftar_syarat(): void
    {
        $this->from(route('password.request'))
            ->post(route('password.store'), ['username' => 'budi', 'password' => 'abcdefgh', 'password_confirmation' => 'beda'])
            ->assertRedirect(route('password.request'));

        $this->get(route('password.request'))->assertOk()
            ->assertSee('Konfirmasi kata sandi tidak cocok.')
            ->assertSee('Kata sandi harus berisi minimal satu angka.')
            ->assertSee('aria-describedby="password-cek password-galat"', false)
            ->assertSee('aria-label="Tampilkan kata sandi"', false)
            ->assertSee('data-syarat="panjang"', false)
            ->assertSee('data-syarat="angka"', false)
            ->assertSee('data-syarat="cocok"', false);
    }
}
