<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Akun awal, kata sandi "password" (ganti setelah login pertama).
        User::factory()->create(['name' => 'Admin Poliklinik', 'username' => 'admin', 'email' => 'admin@sipoli.test', 'role' => 'admin']);
        User::factory()->create(['name' => 'Perawat Poliklinik', 'username' => 'perawat', 'email' => 'perawat@sipoli.test', 'role' => 'perawat']);

        $this->call(TarunaSeeder::class);
    }
}
