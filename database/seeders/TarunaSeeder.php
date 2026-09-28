<?php

namespace Database\Seeders;

use App\Models\Taruna;
use Illuminate\Database\Seeder;

class TarunaSeeder extends Seeder
{
    public function run(): void
    {
        $taruna = [
            ['Aditya Pratama', '2201001', 'I', 'I RKS A', 'Laki-laki', 'A101'],
            ['Nabila Putri Lestari', '2201002', 'I', 'I RKS A', 'Perempuan', 'D102'],
            ['Rizky Maulana', '2201003', 'I', 'I RPK B', 'Laki-laki', 'A103'],
            ['Dimas Saputra', '2102004', 'II', 'II RKS A', 'Laki-laki', 'B201'],
            ['Salsabila Rahmawati', '2102005', 'II', 'II RKS B', 'Perempuan', 'D202'],
            ['Fajar Nugroho', '2102006', 'II', 'II RPK A', 'Laki-laki', 'B203'],
            ['Anisa Kurniawati', '2003007', 'III', 'III RKS A', 'Perempuan', 'E301'],
            ['Bagas Wicaksono', '2003008', 'III', 'III RPK B', 'Laki-laki', 'C302'],
            ['Muhammad Iqbal', '1904009', 'IV', 'IV RKS A', 'Laki-laki', 'C401'],
            ['Dewi Anggraini', '1904010', 'IV', 'IV RPK A', 'Perempuan', 'E402'],
        ];

        // upsert per NPM supaya seeder aman dijalankan ulang
        Taruna::upsert(
            array_map(fn ($t) => array_combine(['nama', 'npm', 'tingkat', 'kelas', 'jenis_kelamin', 'kamar'], $t), $taruna),
            ['npm']
        );
    }
}
