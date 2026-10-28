<?php

namespace App\Http\Controllers;

use App\Models\Keluhan;
use App\Models\KeluhanPsikologi;
use App\Models\Taruna;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public const PERIODE = ['hari-ini' => 'Hari ini', '7-hari' => '7 hari terakhir', 'bulan-ini' => 'Bulan ini'];

    /** Teks pembanding tren kartu untuk tiap periode. */
    public const PEMBANDING = ['hari-ini' => 'dari kemarin', '7-hari' => 'dari 7 hari sebelumnya', 'bulan-ini' => 'dari periode sama bulan lalu', 'kustom' => 'dari periode sebelumnya'];

    /**
     * Rentang tanggal pembanding tren kartu: kemarin, 7 hari sebelumnya, tanggal yang sama bulan lalu,
     * atau (rentang pilihan) rentang sepanjang itu tepat sebelumnya.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private static function periodeSebelumnya(string $periode, Carbon $awal, Carbon $akhir): array
    {
        return match ($periode) {
            '7-hari' => [$awal->copy()->subDays(7), $awal->copy()->subDay()],
            'bulan-ini' => [$awal->copy()->subMonthNoOverflow(), today()->subMonthNoOverflow()],
            'kustom' => [$awal->copy()->subDays((int) $awal->diffInDays($akhir) + 1), $awal->copy()->subDay()],
            default => [today()->subDay(), today()->subDay()],
        };
    }

    /** Batas atas sumbu grafik batang: angka bulat 1/2/5 × 10ⁿ di atas nilai terbesar, minimal 5. */
    public static function batasSumbu(int $maks): int
    {
        $pangkat = 10 ** max(0, (int) floor(log10(max($maks, 1))));
        foreach ([1, 2, 5, 10] as $kali) {
            if ($kali * $pangkat >= $maks) {
                return max(5, $kali * $pangkat);
            }
        }

        return max(5, 10 * $pangkat);
    }

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
        $akhir = today();

        // Rentang pilihan sendiri (?dari=&sampai=) menggantikan periode bawaan
        if ($request->filled(['dari', 'sampai'])) {
            $rentang = $request->validate([
                'dari' => ['required', 'date', 'before_or_equal:sampai'],
                'sampai' => ['required', 'date', 'before_or_equal:today'],
            ]);
            $periode = 'kustom';
            $awal = Carbon::parse($rentang['dari'])->startOfDay();
            $akhir = Carbon::parse($rentang['sampai'])->startOfDay();
        }
        [$awalLalu, $akhirLalu] = self::periodeSebelumnya($periode, $awal, $akhir);
        $waktu = ['periode' => $periode, 'awal' => $awal, 'akhir' => $akhir];

        if ($this->bagian($request) === 'psikolog') {
            return view('dashboard.dashboard', ['bagian' => 'psikolog'] + $waktu + $this->dataPsikolog($awal, $akhir, $awalLalu, $akhirLalu));
        }

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

        // Tren kartu sakit = snapshot sekarang dibanding akhir kemarin: keluhan lama yang belum sembuh atau baru sembuh hari ini.
        // ponytail: tingkat & tanggal sembuh lama tidak disimpan (dipakai status sekarang & updated_at), jadi perkiraan; tambah riwayat status bila perlu tepat.
        $kemarin = Keluhan::whereDate('created_at', '<', today())
            ->where(fn ($q) => $q->where('status_pemulihan', '!=', 'Sembuh')->orWhereDate('updated_at', '>=', today()))
            ->get();
        $sembuh = Keluhan::where('status_pemulihan', 'Sembuh')->whereDate('updated_at', '>=', $awal)->whereDate('updated_at', '<=', $akhir)->count();
        $jumlahStatus = fn (string $status) => $sakit->where('status', $status)->count();

        return view('dashboard.dashboard', [
            'bagian' => 'perawat',
            'ringan' => $jumlahStatus('Ringan'),
            'sedang' => $jumlahStatus('Sedang'),
            'berat' => $jumlahStatus('Berat'),
            'sembuh' => $sembuh,
            'tren' => [
                'ringan' => $jumlahStatus('Ringan') - $kemarin->where('status', 'Ringan')->count(),
                'sedang' => $jumlahStatus('Sedang') - $kemarin->where('status', 'Sedang')->count(),
                'berat' => $jumlahStatus('Berat') - $kemarin->where('status', 'Berat')->count(),
                'sembuh' => $sembuh - Keluhan::where('status_pemulihan', 'Sembuh')->whereDate('updated_at', '>=', $awalLalu)->whereDate('updated_at', '<=', $akhirLalu)->count(),
            ],
            'isoman' => $sakit->where('status_pemulihan', 'Isolasi mandiri')->count(),
            ...$waktu,
            'perTingkat' => $perTingkat,
            'jenisKelamin' => $jenisKelamin,
            'perbandingan' => ['Sembuh' => max($totalTaruna - $tarunaSakit, 0), 'Sakit' => $tarunaSakit],
            'keluhanBaru' => Keluhan::with('taruna')->whereDate('created_at', today())->latest()->get(),
        ]);
    }

    /**
     * Dashboard psikolog. Kartu "menyatakan keluhan" & "selesai konseling" ikut periode,
     * "melanjutkan konseling" dan grafik = snapshot yang masih konseling (pola sama dengan dashboard perawat).
     */
    private function dataPsikolog(Carbon $awal, Carbon $akhir, Carbon $awalLalu, Carbon $akhirLalu): array
    {
        $menyatakan = fn (Carbon $dari, Carbon $sampai) => KeluhanPsikologi::whereDate('tanggal_awal', '>=', $dari)
            ->whereDate('tanggal_awal', '<=', $sampai)->distinct()->count('taruna_id');
        $lanjut = KeluhanPsikologi::with('taruna')->where('lanjut_konseling', true)->get();
        $selesai = KeluhanPsikologi::with('taruna')->where('lanjut_konseling', false)
            ->whereDate('selesai_at', '>=', $awal)->whereDate('selesai_at', '<=', $akhir)->get();
        $jumlahTaruna = fn ($keluhan) => $keluhan->pluck('taruna_id')->unique()->count();

        $perTingkat = [];
        foreach (['I', 'II', 'III', 'IV'] as $t) {
            $perTingkat['Tingkat '.$t] = [
                $lanjut->filter(fn ($k) => $k->taruna->tingkat === $t)->count(),
                $selesai->filter(fn ($k) => $k->taruna->tingkat === $t)->count(),
            ];
        }

        $totalTaruna = Taruna::count();
        $tarunaLanjut = $jumlahTaruna($lanjut);

        return [
            'menyatakan' => $menyatakan($awal, $akhir),
            'melanjutkan' => $tarunaLanjut,
            'selesai' => $jumlahTaruna($selesai),
            'tren' => [
                'menyatakan' => $menyatakan($awal, $akhir) - $menyatakan($awalLalu, $akhirLalu),
                'selesai' => $jumlahTaruna($selesai) - KeluhanPsikologi::where('lanjut_konseling', false)
                    ->whereDate('selesai_at', '>=', $awalLalu)->whereDate('selesai_at', '<=', $akhirLalu)->distinct()->count('taruna_id'),
            ],
            'perTingkat' => $perTingkat,
            'jenisKelamin' => [
                'Laki-laki' => $lanjut->filter(fn ($k) => $k->taruna->jenis_kelamin === 'Laki-laki')->count(),
                'Perempuan' => $lanjut->filter(fn ($k) => $k->taruna->jenis_kelamin === 'Perempuan')->count(),
            ],
            'perbandingan' => ['Sembuh' => max($totalTaruna - $tarunaLanjut, 0), 'Sakit' => $tarunaLanjut],
            'keluhanBaru' => KeluhanPsikologi::with('taruna')->whereDate('created_at', today())->latest()->get(),
        ];
    }
}
