<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $pegawai = [
            ['dr. Andi Wijaya', 'Dokter'],
            ['dr. Maya Sari', 'Dokter'],
            ['Rina Kartika, A.Md.Kep.', 'Perawat'],
            ['Sinta Dewi, A.Md.Kep.', 'Perawat'],
            ['Budi Santoso, S.Kep., Ners', 'Perawat'],
        ];

        // upsert per nama supaya seeder aman dijalankan ulang
        Pegawai::upsert(array_map(fn ($p) => array_combine(['nama', 'jabatan'], $p), $pegawai), ['nama']);
    }
}
