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
        User::unguard();

        // Akun awal, kata sandi "password" (ganti setelah login pertama).
        // Tanpa factory (Faker hanya ada di dev) dan firstOrCreate supaya aman diulang tanpa menimpa sandi yang sudah diganti.
        foreach ([
            ['admin', 'Admin Poliklinik', 'admin@sipoli.test', 'admin'],
            ['perawat', 'Perawat Poliklinik', 'perawat@sipoli.test', 'perawat'],
        ] as [$username, $name, $email, $role]) {
            User::firstOrCreate(['username' => $username], [
                'name' => $name, 'email' => $email, 'role' => $role,
                'password' => 'password', 'email_verified_at' => now(),
            ]);
        }

        $this->call(TarunaSeeder::class);
    }
}
