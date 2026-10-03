<?php

namespace App\Http\Controllers;

use App\Models\Keluhan;
use App\Models\RiwayatKontrol;
use App\Models\Taruna;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\View\View;

class RiwayatKesehatanController extends Controller
{
    public function index(): View
    {
        return $this->halaman();
    }

    public function show(Taruna $taruna): View
    {
        abort_unless($taruna->keluhan()->exists(), 404);

        return $this->halaman($taruna->id);
    }

    private function halaman(?int $tarunaTerpilihId = null): View
    {
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

        return view('riwayat-kesehatan.riwayat-kesehatan-index', [
            'riwayatTaruna' => $riwayatTaruna,
            'tarunaTerpilihId' => $tarunaTerpilihId,
        ]);
    }
}
