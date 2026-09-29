<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Halaman utama mengarahkan ke login, dan halaman login bisa dibuka.
     */
    public function test_halaman_utama_mengarahkan_ke_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
        $this->get('/login')->assertOk();
    }
}
