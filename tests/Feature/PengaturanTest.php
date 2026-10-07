<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
