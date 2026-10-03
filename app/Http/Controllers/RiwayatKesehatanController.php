<?php

namespace App\Http\Controllers;

use App\Models\Keluhan;
use App\Models\KeluhanPsikologi;
use App\Models\RiwayatKonseling;
use App\Models\RiwayatKontrol;
use App\Models\Taruna;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiwayatKesehatanController extends Controller
{
    public function index(Request $request): View
    {
        return $this->halaman($request);
    }

    public function show(Request $request, Taruna $taruna): View
    {
        abort_unless($taruna->keluhan()->exists(), 404);

        return $this->halaman($request, $taruna->id);
    }

    private function halaman(Request $request, ?int $tarunaTerpilihId = null): View
    {
        $bagian = $this->bagian($request);
        $bisaLihatPsikologi = $request->user()->can('bagian-psikolog');

        $riwayatTaruna = Taruna::query()
            ->whereHas('keluhan')
            ->with([
                'keluhan' => fn (HasMany $keluhan) => $keluhan
                    ->with(['riwayatKontrol' => fn (HasMany $kontrol) => $kontrol->orderBy('tanggal_kontrol')])
                    ->orderByDesc('tanggal_awal'),
            ])
            ->orderBy('nama')
            ->get()
            ->map(fn (Taruna $taruna): array => [
                'id' => $taruna->id,
                'nama' => $taruna->nama,
                'npm' => $taruna->npm,
                'kamar' => $taruna->kamar,
                'tingkat' => 'Tingkat '.$taruna->tingkat,
                'riwayat' => $taruna->keluhan->map(fn (Keluhan $keluhan): array => [
                    'tanggal_lapor' => $keluhan->tanggal_awal->toDateString(),
                    'status' => $keluhan->status,
                    'keluhan_awal' => $keluhan->keluhan,
                    'terapi' => $keluhan->terapi,
                    'kontrol' => $keluhan->riwayatKontrol->map(fn (RiwayatKontrol $kontrol): array => [
                        'tanggal' => $kontrol->tanggal_kontrol->format('d/m/Y'),
                        'hasil' => $kontrol->hasil_kontrol,
                        'keterangan' => $kontrol->keterangan ?: '-',
                    ])->all(),
                    'catatan' => $keluhan->keterangan ?: '-',
                ])->all(),
            ])
            ->all();

        $riwayatPsikologi = [];
        if ($bisaLihatPsikologi) {
            $riwayatPsikologi = KeluhanPsikologi::query()
                ->with([
                    'taruna',
                    'riwayat' => fn (HasMany $riwayat) => $riwayat
                        ->orderBy('tanggal_konseling')
                        ->orderBy('id'),
                ])
                ->orderBy('taruna_id')
                ->orderByDesc('tanggal_awal')
                ->get()
                ->groupBy('taruna_id')
                ->map(function (Collection $keluhans): array {
                    $taruna = $keluhans->first()->taruna;

                    return [
                        'id' => $taruna->id,
                        'nama' => $taruna->nama,
                        'npm' => $taruna->npm,
                        'kamar' => $taruna->kamar,
                        'tingkat' => 'Tingkat '.$taruna->tingkat,
                        'riwayat' => $keluhans->map(fn (KeluhanPsikologi $keluhan): array => [
                            'tanggal_lapor' => $keluhan->tanggal_awal->toDateString(),
                            'status' => $keluhan->keterangan(),
                            'keluhan_awal' => $keluhan->keluhan,
                            'terapi' => $keluhan->terapi,
                            'kontrol' => $keluhan->riwayat->map(fn (RiwayatKonseling $konseling): array => [
                                'tanggal' => $konseling->tanggal_konseling->format('d/m/Y'),
                                'hasil' => $konseling->hasil_konseling,
                                'keterangan' => '-',
                            ])->all(),
                            'catatan' => $keluhan->tanggal_konseling_selanjutnya
                                ? 'Jadwal konseling selanjutnya: '.$keluhan->tanggal_konseling_selanjutnya->format('d/m/Y')
                                : $keluhan->keterangan(),
                        ])->all(),
                    ];
                })
                ->values()
                ->all();
        }

        return view('riwayat-kesehatan.riwayat-kesehatan-index', [
            'riwayatTaruna' => $riwayatTaruna,
            'riwayatPsikologi' => $riwayatPsikologi,
            'bisaLihatPsikologi' => $bisaLihatPsikologi,
            'bagian' => $bagian,
            'tarunaTerpilihId' => $tarunaTerpilihId,
        ]);
    }
}
