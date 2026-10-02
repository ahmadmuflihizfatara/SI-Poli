<?php

namespace App\Services;

use App\Models\Keluhan;
use App\Models\KeluhanKlb;
use App\Models\KeluhanPsikologi;

/** Daftar taruna yang dijadwalkan kontrol/konseling hari ini, dalam bentuk pesan Telegram (HTML). */
class KontrolHariIni
{
    private const BATAS = 3800; // batas Telegram 4096 karakter per pesan

    /** @return list<string> satu atau beberapa pesan siap kirim */
    public function pesan(): array
    {
        $bagian = [
            'Kontrol kesehatan' => Keluhan::with('taruna')->whereDate('tanggal_kontrol_selanjutnya', today())
                ->where('status_pemulihan', '!=', 'Sembuh')->get()
                ->map(fn ($k) => [$k->taruna->nama, $k->taruna->kelas, $k->taruna->kamar]),
            'Konseling' => KeluhanPsikologi::with('taruna')->where('lanjut_konseling', true)
                ->whereDate('tanggal_konseling_selanjutnya', today())->get()
                ->map(fn ($k) => [$k->taruna->nama, $k->taruna->kelas, $k->taruna->kamar]),
            'Kejadian luar biasa' => KeluhanKlb::whereHas('klb', fn ($q) => $q->where('status', 'Berlangsung'))
                ->whereDate('tanggal_kontrol_selanjutnya', today())->get()
                ->map(fn ($k) => [$k->nama, $k->kelas, $k->kamar]),
        ];

        // Hanya nama, kelas, dan kamar: isi keluhan/terapi/hasil pemeriksaan sengaja tidak ikut terkirim.
        $judul = '📋 <b>Kontrol hari ini</b> — '.e(today()->locale('id')->translatedFormat('l, j F Y'));
        $baris = [$judul];
        $ada = false;
        foreach ($bagian as $nama => $daftar) {
            if ($daftar->isEmpty()) {
                continue;
            }
            $ada = true;
            $baris[] = '';
            $baris[] = '<b>'.$nama.' ('.$daftar->count().')</b>';
            foreach ($daftar->values() as $i => [$n, $kelas, $kamar]) {
                $baris[] = ($i + 1).'. '.e($n).' — '.e($kelas).' · Kamar '.e($kamar);
            }
        }
        if (! $ada) {
            return [$judul."\n\nTidak ada jadwal kontrol hari ini."];
        }

        $pesan = [];
        $sekarang = '';
        foreach ($baris as $b) {
            if ($sekarang !== '' && strlen($sekarang) + strlen($b) + 1 > self::BATAS) {
                $pesan[] = $sekarang;
                $sekarang = '';
            }
            $sekarang .= ($sekarang === '' ? '' : "\n").$b;
        }
        $pesan[] = $sekarang;

        return $pesan;
    }
}
