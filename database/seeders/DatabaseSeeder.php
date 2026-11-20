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
        $this->call([TarunaSeeder::class, PegawaiSeeder::class]);

        // Audit WSTG-IDNT-04: akun bawaan (nama = role, sandi "password") hanya untuk local/testing, tidak pernah di production.
        // Akun admin pertama di production dibuat manual, misalnya lewat tinker, dengan nama pengguna personal.
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Akun bawaan dilewati: hanya dibuat di lingkungan local/testing.');

            return;
        }

        User::unguard();

        // Akun awal untuk pengembangan, kata sandi "password".
        // Tanpa factory (Faker hanya ada di dev) dan firstOrCreate supaya aman diulang tanpa menimpa sandi yang sudah diganti.
        foreach ([
            ['admin', 'Admin Poliklinik', 'admin@sipoli.test', 'admin'],
            ['perawat', 'Perawat Poliklinik', 'perawat@sipoli.test', 'perawat'],
            ['dokter', 'Dokter Poliklinik', 'dokter@sipoli.test', 'dokter'],
            ['psikolog', 'Psikolog Poliklinik', 'psikolog@sipoli.test', 'psikolog'],
            ['asisten', 'Asisten Kesehatan', 'asisten@sipoli.test', 'asisten_kesehatan'],
            ['pengasuh', 'Pengasuh Taruna', 'pengasuh@sipoli.test', 'pengasuh'],
        ] as [$username, $name, $email, $role]) {
            User::firstOrCreate(['username' => $username], [
                'name' => $name, 'email' => $email, 'role' => $role,
                'password' => 'password', 'email_verified_at' => now(),
            ]);
        }
    }
}
