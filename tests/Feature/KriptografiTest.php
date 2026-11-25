<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Kolaborasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Perbaikan audit Testing for Weak Cryptography (WSTG-CRYP-01/03/04). */
class KriptografiTest extends TestCase
{
    use RefreshDatabase;

    public function test_hsts_dikirim_hanya_pada_respons_https(): void
    {
        $this->get('https://localhost/login')->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000');

        $this->get('http://localhost/login')->assertOk()
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_halaman_tidak_memuat_skrip_tailwind_dari_cdn(): void
    {
        $this->get(route('login'))->assertOk()
            ->assertDontSee('cdn.tailwindcss.com')
            ->assertSee('/build/assets/', false);
    }

    public function test_kata_sandi_lebih_dari_72_byte_ditolak_dengan_pesan_bukan_dipotong(): void
    {
        $user = User::factory()->create(['username' => 'budi']);
        $sandiPanjang = str_repeat('a1', 36).'x'; // 73 byte

        $this->from(route('password.request'))
            ->post(route('password.store'), ['username' => 'budi', 'password' => $sandiPanjang, 'password_confirmation' => $sandiPanjang])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors(['password' => 'Kata sandi maksimal 72 karakter (huruf beraksen atau emoji dihitung lebih dari satu).']);
        $this->assertNull($user->fresh()->sandi_baru);

        $sandiPas = str_repeat('a1', 36); // 72 byte
        $this->post(route('password.store'), ['username' => 'budi', 'password' => $sandiPas, 'password_confirmation' => $sandiPas])
            ->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check($sandiPas, $user->fresh()->sandi_baru));
    }

    public function test_karakter_multibyte_dihitung_per_byte(): void
    {
        $sandi = str_repeat('é', 36).'1'; // 37 karakter, 73 byte

        $this->post(route('password.store'), ['username' => 'budi', 'password' => $sandi, 'password_confirmation' => $sandi])
            ->assertSessionHasErrors('password');
    }

    public function test_login_akun_tidak_ada_dengan_sandi_melebihi_batas_bcrypt_tetap_galat_biasa(): void
    {
        $this->from(route('login'))
            ->post(route('login.store'), ['username' => 'tidak-ada', 'password' => str_repeat('x', 100)])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['password' => 'Nama pengguna atau kata sandi salah.']);
    }

    public function test_token_kolaborasi_ditolak_setelah_8_jam(): void
    {
        $this->freezeTime();
        $user = User::factory()->create(['role' => 'perawat']);
        $this->actingAs($user);
        $token = Kolaborasi::token('rikkes.1', true);

        $this->travel(8 * 60 - 1)->minutes();
        $this->flushSession()->actingAs($user)->postJson(route('kolaborasi'), ['token' => $token, 'since' => 0])->assertOk();

        $this->travel(2)->minutes();
        $this->flushSession()->actingAs($user)->postJson(route('kolaborasi'), ['token' => $token, 'since' => 0])->assertForbidden();
    }
}
