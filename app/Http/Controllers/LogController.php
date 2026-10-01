<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    public const WAKTU = ['hari-ini' => 'Hari ini', '7-hari' => '7 hari terakhir', 'bulan-ini' => 'Bulan ini'];

    public const AKSI = ['Tambah', 'Edit'];

    public const PENGGUNA = ['admin' => 'Admin', 'perawat' => 'Perawat', 'psikolog' => 'Psikolog'];

    public function index(Request $request): View
    {
        $f = $request->only(['q', 'waktu', 'aksi', 'sumber', 'pengguna']);

        $log = LogAktivitas::with('user')
            ->when($f['q'] ?? null, fn ($q, $v) => $q->where('pesan', 'like', '%'.$v.'%'))
            ->when(self::WAKTU[$f['waktu'] ?? ''] ?? null, fn ($q) => $q->whereDate('created_at', '>=', match ($f['waktu']) {
                '7-hari' => today()->subDays(6),
                'bulan-ini' => today()->startOfMonth(),
                default => today(),
            }))
            ->when(in_array($f['aksi'] ?? null, self::AKSI, true), fn ($q) => $q->where('aksi', $f['aksi']))
            ->when($f['sumber'] ?? null, fn ($q, $v) => $q->where('sumber_daya', $v))
            ->when(isset(self::PENGGUNA[$f['pengguna'] ?? '']), fn ($q) => $q->whereRelation('user', 'role', $f['pengguna']))
            ->latest('created_at')->latest('id')
            ->paginate(15)
            ->withQueryString();

        // Sebaran 7 hari terakhir: [tanggal => [aksi => jumlah]]
        $hari = collect(range(6, 0))->map(fn ($n) => today()->subDays($n));
        $jumlah = LogAktivitas::selectRaw('DATE(created_at) as tgl, aksi, COUNT(*) as n')
            ->whereDate('created_at', '>=', $hari->first())
            ->groupBy('tgl', 'aksi')
            ->get()
            ->groupBy('tgl');
        $sebaran = [];
        foreach (self::AKSI as $aksi) {
            $sebaran[$aksi] = $hari->map(fn ($d) => (int) ($jumlah->get($d->toDateString())?->firstWhere('aksi', $aksi)->n ?? 0))->all();
        }

        return view('log-sistem.log-sistem', [
            'log' => $log,
            'filter' => $f,
            'total' => LogAktivitas::count(),
            'hariIni' => LogAktivitas::whereDate('created_at', today())->count(),
            'hari' => $hari,
            'sebaran' => $sebaran,
            'sumberDaya' => LogAktivitas::distinct()->orderBy('sumber_daya')->pluck('sumber_daya'),
        ]);
    }

    public function show(LogAktivitas $log): View
    {
        return view('log-sistem.detail-log', ['log' => $log->load(['user', 'pemeriksaan'])]);
    }
}
