<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Kolaborasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/** Perbaikan audit Session Management Testing (WSTG-SESS-04/05/07/09). */
class ManajemenSesiTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_hanya_lewat_post_dan_tombol_keluar_berupa_formulir(): void
    {
        $user = User::factory()->create(['role' => 'perawat']);

        $this->actingAs($user)->get(route('dashboard'))->assertOk()
            ->assertSee('<form method="POST" action="'.route('logout').'">', false);
        $this->get('/logout')->assertMethodNotAllowed();
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_polling_kolaborasi_tanpa_ketikan_tidak_memperpanjang_sesi_idle(): void
    {
        $this->freezeTime();
        $user = User::factory()->create(['role' => 'perawat']);
        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $token = Kolaborasi::token('rikkes.1', true, false);

        $this->travel(config('session.lifetime') - 5)->minutes();
        $this->postJson(route('kolaborasi'), ['token' => $token, 'since' => 0])->assertOk();
        $this->travel(6)->minutes();
        $this->postJson(route('kolaborasi'), ['token' => $token, 'since' => 0])->assertUnauthorized();

        $this->assertGuest();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_ketikan_di_formulir_bersama_dihitung_aktivitas(): void
    {
        $this->freezeTime();
        $user = User::factory()->create(['role' => 'perawat']);
        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $token = Kolaborasi::token('rikkes.1', true, false);

        $this->travel(config('session.lifetime') - 5)->minutes();
        $this->postJson(route('kolaborasi'), ['token' => $token, 'since' => 0, 'isian' => ['data[1][nadi]' => '80']])->assertOk();
        $this->travel(config('session.lifetime') - 5)->minutes();
        $this->get(route('dashboard'))->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_sesi_aktif_tetap_berakhir_setelah_batas_absolut_12_jam(): void
    {
        $this->freezeTime();
        $user = User::factory()->create(['role' => 'perawat']);
        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        for ($menit = 20; $menit <= 12 * 60; $menit += 20) {
            $this->travel(20)->minutes();
            $this->get(route('dashboard'))->assertOk();
        }
        $this->travel(20)->minutes();

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_ganti_kata_sandi_mencabut_sesi_lain_tetapi_sesi_sendiri_tetap_berjalan(): void
    {
        $user = User::factory()->create(['role' => 'psikolog']);
        $this->post(route('login.store'), ['username' => $user->username, 'password' => 'password']);
        $this->get(route('dashboard'))->assertOk();

        // kata sandi diganti dari perangkat lain: sesi ini (milik penyerang) harus terputus
        User::find($user->id)->forceFill(['password' => 'sandiBaru1'])->save();
        Auth::forgetGuards();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();

        $this->actingAs($user->refresh())->get(route('pengaturan'))->assertOk();
        $this->put(route('pengaturan.sandi'), ['current_password' => 'sandiBaru1', 'password' => 'sandiBaru2', 'password_confirmation' => 'sandiBaru2']);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_halaman_terautentikasi_tidak_disimpan_cache_peramban(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'perawat']))
            ->get(route('dashboard'))->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }
}
