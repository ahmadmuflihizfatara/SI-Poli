<?php

namespace App\Services;

use App\Models\KejadianLuarBiasa;
use App\Models\Keluhan;
use App\Models\KeluhanKlb;
use App\Models\KeluhanPsikologi;
use App\Models\Taruna;

/** Pesan Telegram (HTML): daftar taruna yang kontrol/konseling hari ini, dan ringkasan kesehatan hari ini. */
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

    /** Ringkasan angka saja (tanpa nama), hitungannya sama dengan kartu dashboard periode "Hari ini". */
    public function ringkasan(): string
    {
        $sakit = Keluhan::where('status_pemulihan', '!=', 'Sembuh')->get(['taruna_id', 'status', 'status_pemulihan']);
        $klb = KejadianLuarBiasa::where('status', 'Berlangsung')->withCount('keluhan')->get();

        $baris = [
            '📊 <b>Ringkasan kesehatan</b> — '.e(today()->locale('id')->translatedFormat('l, j F Y')),
            '',
            '<b>Poli</b>',
            'Taruna sakit: '.$sakit->pluck('taruna_id')->unique()->count().' dari '.Taruna::count(),
            '• Ringan '.$sakit->where('status', 'Ringan')->count().' · Sedang '.$sakit->where('status', 'Sedang')->count().' · Berat '.$sakit->where('status', 'Berat')->count(),
            '• Isolasi mandiri: '.$sakit->where('status_pemulihan', 'Isolasi mandiri')->count(),
            'Keluhan baru hari ini: '.Keluhan::whereDate('tanggal_awal', today())->count(),
            'Sembuh hari ini: '.Keluhan::where('status_pemulihan', 'Sembuh')->whereDate('updated_at', today())->count(),
            '',
            '<b>Psikologi</b>',
            'Keluhan baru hari ini: '.KeluhanPsikologi::whereDate('tanggal_awal', today())->count(),
            'Masih konseling: '.KeluhanPsikologi::where('lanjut_konseling', true)->distinct()->count('taruna_id').' taruna',
            '',
            '<b>Kejadian luar biasa</b>',
        ];
        foreach ($klb as $k) {
            $baris[] = '• '.e($k->nama).': '.$k->keluhan_count.' pasien';
        }
        if ($klb->isEmpty()) {
            $baris[] = 'Tidak ada yang berlangsung.';
        }

        return implode("\n", $baris);
    }
}
