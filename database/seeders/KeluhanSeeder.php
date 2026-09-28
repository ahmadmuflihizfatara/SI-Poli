<?php

namespace Database\Seeders;

use App\Models\Keluhan;
use App\Models\Taruna;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data contoh keluhan untuk gambaran dashboard: 7 hari terakhir, awal bulan ini, dan satu dari bulan lalu.
 * Tanggal relatif terhadap hari seeder dijalankan. Jalankan sekali: menjalankan ulang akan menduplikasi data.
 */
class KeluhanSeeder extends Seeder
{
    public function run(): void
    {
        $taruna = Taruna::pluck('id', 'npm');
        $petugas = User::value('id');

        // [npm, hari lalu awal keluhan, status, status pemulihan, hari lalu sembuh (null = belum), keluhan, terapi, hasil pemeriksaan,
        //  [tekanan darah, suhu, nadi, saturasi, pernapasan, skala nyeri], [[hari lalu kontrol, hasil kontrol, perlu rujukan], ...]]
        $data = [
            // 7 hari terakhir
            ['2201001', 0, 'Ringan', 'Dalam perawatan', null, 'Demam dan pusing sejak pagi', 'Paracetamol 3x500 mg, istirahat', 'Suhu tinggi, tenggorokan tidak merah',
                ['120/80', 38.2, 92, 98, 20, 3], []],
            ['2201002', 2, 'Sedang', 'Dalam perawatan', null, 'Batuk berdahak dan pilek', 'Ambroxol 3x30 mg, vitamin C', 'Ronki basah halus minimal',
                ['110/70', 37.6, 88, 97, 22, 2], [[0, 'Batuk berkurang, dahak masih ada', false]]],
            ['2102004', 3, 'Berat', 'Isolasi mandiri', null, 'Demam tinggi disertai bintik berair di badan (cacar air)', 'Acyclovir 5x800 mg, bedak salisil', 'Vesikel menyebar di badan dan wajah',
                ['115/75', 38.9, 104, 96, 24, 5], [[1, 'Vesikel mulai mengering, demam turun', false]]],
            ['2003008', 4, 'Sedang', 'Dalam perawatan', null, 'Keseleo pergelangan kaki kanan saat latihan fisik', 'Kompres dingin, Ibuprofen 3x400 mg, elastic bandage', 'Bengkak ringan, gerak terbatas',
                ['125/80', 36.7, 84, 99, 18, 6], [[2, 'Bengkak berkurang, masih nyeri saat menapak', false]]],
            ['2102005', 5, 'Ringan', 'Sembuh', 1, 'Nyeri ulu hati dan mual (maag)', 'Antasida 3x1 tab, Omeprazole 1x20 mg', 'Nyeri tekan epigastrium',
                ['110/70', 36.8, 80, 99, 18, 4], [[3, 'Nyeri berkurang, makan teratur', false], [1, 'Tidak ada keluhan', false]]],
            ['1904010', 6, 'Ringan', 'Sembuh', 0, 'Sakit kepala dan kurang tidur', 'Paracetamol 3x500 mg, istirahat cukup', 'Tanda vital normal',
                ['118/78', 36.6, 76, 99, 16, 3], [[0, 'Keluhan hilang', false]]],

            // Awal bulan ini
            ['2201003', 8, 'Berat', 'Dalam perawatan', null, 'Sesak napas, asma kambuh setelah lari', 'Nebulizer Salbutamol, Salbutamol 3x2 mg', 'Mengi di kedua lapang paru',
                ['130/85', 36.9, 110, 93, 28, 5], [[6, 'Sesak berkurang, mengi masih ada', true], [2, 'Kontrol dari RS, lanjut obat', false]]],
            ['1904009', 9, 'Sedang', 'Isolasi mandiri', null, 'Demam, nyeri otot, dan batuk (influenza)', 'Oseltamivir 2x75 mg, Paracetamol 3x500 mg', 'Faring hiperemis',
                ['120/80', 38.4, 96, 97, 20, 4], [[5, 'Demam turun, batuk kering', false]]],
            ['2003007', 12, 'Ringan', 'Sembuh', 10, 'Migrain sebelah kiri', 'Ibuprofen 3x400 mg', 'Tanda vital normal',
                ['115/75', 36.5, 78, 99, 16, 5], [[10, 'Nyeri kepala hilang', false]]],
            ['2201003', 20, 'Sedang', 'Sembuh', 14, 'Diare lebih dari 5 kali sehari', 'Oralit, Zinc 1x20 mg, Attapulgite', 'Turgor kulit sedikit menurun',
                ['105/70', 37.2, 94, 98, 20, 3], [[17, 'BAB 2 kali, mulai padat', false], [14, 'Tidak ada keluhan', false]]],
            ['2201001', 18, 'Ringan', 'Sembuh', 15, 'Luka lecet di lutut kiri', 'Rawat luka, Povidone iodine', 'Luka lecet 3x2 cm, bersih',
                ['120/80', 36.6, 80, 99, 16, 2], [[15, 'Luka mengering', false]]],
            ['2102004', 22, 'Ringan', 'Sembuh', 20, 'Radang tenggorokan', 'Amoxicillin 3x500 mg, lozenges', 'Tonsil T2-T2 hiperemis',
                ['118/76', 37.8, 90, 98, 18, 4], [[20, 'Nyeri telan hilang', false]]],
            ['2102006', 25, 'Berat', 'Sembuh', 16, 'Demam naik turun lebih dari 5 hari (suspek tifoid)', 'Dirujuk ke RS, Ciprofloxacin 2x500 mg', 'Lidah kotor, nyeri tekan perut',
                ['110/70', 39.1, 86, 97, 20, 5], [[24, 'Dirujuk ke RS untuk tes Widal', true], [16, 'Pulang dari RS, kondisi baik', false]]],

            // Bulan lalu (tidak ikut hitungan Sembuh "Bulan ini")
            ['2102005', 40, 'Sedang', 'Sembuh', 35, 'Gatal dan ruam merah di lengan', 'Cetirizine 1x10 mg, salep hidrokortison', 'Dermatitis kontak',
                ['112/72', 36.6, 78, 99, 16, 2], [[35, 'Ruam hilang', false]]],
        ];

        foreach ($data as [$npm, $awal, $status, $pemulihan, $sembuh, $keluhan, $terapi, $hasil, $vital, $kontrol]) {
            [$td, $suhu, $nadi, $saturasi, $napas, $nyeri] = $vital;
            $dibuat = today()->subDays($awal)->setTime(8, 0);
            // updated_at = waktu terakhir diubah; untuk Sembuh ini tanggal sembuh yang dibaca dashboard
            $diubah = $sembuh !== null ? today()->subDays($sembuh)->setTime(14, 0) : now();

            $k = new Keluhan;
            $k->timestamps = false;
            $k->forceFill([
                'taruna_id' => $taruna[$npm],
                'created_by' => $petugas,
                'tanggal_awal' => $dibuat->toDateString(),
                'tanggal_kontrol_selanjutnya' => $sembuh === null ? today()->addDays(2)->toDateString() : null,
                'keluhan' => $keluhan,
                'terapi' => $terapi,
                'hasil_pemeriksaan' => $hasil,
                'status' => $status,
                'status_pemulihan' => $pemulihan,
                'tekanan_darah' => $td,
                'suhu' => $suhu,
                'nadi' => $nadi,
                'saturasi' => $saturasi,
                'pernapasan' => $napas,
                'skala_nyeri' => $nyeri,
                'created_at' => $dibuat,
                'updated_at' => $diubah,
            ])->save();

            foreach ($kontrol as [$hari, $hasilKontrol, $rujuk]) {
                $waktu = today()->subDays($hari)->setTime(10, 0);
                $k->riwayatKontrol()->forceCreate([
                    'tanggal_kontrol' => $waktu->toDateString(),
                    'hasil_kontrol' => $hasilKontrol,
                    'perlu_rujukan' => $rujuk,
                    'created_at' => $waktu,
                    'updated_at' => $waktu,
                ]);
            }
        }
    }
}
