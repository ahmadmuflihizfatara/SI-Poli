<?php

namespace App\Http\Controllers;

use App\Models\Keluhan;
use App\Models\Taruna;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public const PERIODE = ['hari-ini' => 'Hari ini', '7-hari' => '7 hari terakhir', 'bulan-ini' => 'Bulan ini'];

    /** Persen bulat yang jumlahnya tepat 100 (sisa terbesar dapat +1); semua 0 bila total 0. */
    public static function persen(array $nilai): array
    {
        $total = array_sum($nilai);
        $hasil = $pecahan = [];
        foreach ($nilai as $k => $n) {
            $p = $total ? $n / $total * 100 : 0;
            $hasil[$k] = (int) floor($p);
            $pecahan[$k] = $p - floor($p);
        }
        arsort($pecahan);
        foreach (array_slice(array_keys($pecahan), 0, $total ? 100 - array_sum($hasil) : 0) as $k) {
            $hasil[$k]++;
        }

        return $hasil;
    }

    public function index(Request $request): View
    {
        $periode = array_key_exists($request->query('periode'), self::PERIODE) ? $request->query('periode') : 'hari-ini';
        $awal = match ($periode) {
            '7-hari' => today()->subDays(6),
            'bulan-ini' => today()->startOfMonth(),
            default => today(),
        };

        // Kartu sakit & chart = snapshot keluhan yang belum Sembuh (tidak ikut periode); periode hanya untuk kartu Sembuh.
        $sakit = Keluhan::with('taruna')->where('status_pemulihan', '!=', 'Sembuh')->get();

        $perTingkat = [];
        foreach (['I', 'II', 'III', 'IV'] as $t) {
            $subset = $sakit->filter(fn ($k) => $k->taruna->tingkat === $t);
            $perTingkat['Tingkat '.$t] = [
                $subset->where('status', 'Ringan')->count(),
                $subset->where('status', 'Sedang')->count(),
                $subset->where('status', 'Berat')->count(),
            ];
        }

        $jenisKelamin = [
            'Laki-laki' => $sakit->filter(fn ($k) => $k->taruna->jenis_kelamin === 'Laki-laki')->count(),
            'Perempuan' => $sakit->filter(fn ($k) => $k->taruna->jenis_kelamin === 'Perempuan')->count(),
        ];

        // Pie "Perbandingan Kesehatan" pakai total taruna, bukan total keluhan:
        // biar default 100% sehat saat belum ada yang sakit, ikut jumlah taruna di tabel.
        $totalTaruna = Taruna::count();
        $tarunaSakit = $sakit->pluck('taruna_id')->unique()->count();

        return view('dashboard.dashboard', [
            'ringan' => $sakit->where('status', 'Ringan')->count(),
            'sedang' => $sakit->where('status', 'Sedang')->count(),
            'berat' => $sakit->where('status', 'Berat')->count(),
            'sembuh' => Keluhan::where('status_pemulihan', 'Sembuh')->whereDate('updated_at', '>=', $awal)->count(),
            'isoman' => $sakit->where('status_pemulihan', 'Isolasi mandiri')->count(),
            'periode' => $periode,
            'awal' => $awal,
            'perTingkat' => $perTingkat,
            'jenisKelamin' => $jenisKelamin,
            'perbandingan' => ['Sembuh' => max($totalTaruna - $tarunaSakit, 0), 'Sakit' => $tarunaSakit],
        ]);
    }
}
