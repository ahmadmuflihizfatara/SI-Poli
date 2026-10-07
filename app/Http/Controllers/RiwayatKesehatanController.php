<?php

namespace App\Http\Controllers;

use App\Models\Keluhan;
use App\Models\KeluhanPsikologi;
use App\Models\RiwayatKonseling;
use App\Models\RiwayatKontrol;
use App\Models\Taruna;
use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
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

    /** PDF riwayat kesehatan seluruh taruna, mengikuti filter aktif di halaman (cari, tingkat, status, rentang tanggal). */
    public function eksporSemua(Request $request): Response
    {
        $keluhan = $this->saring(Keluhan::query(), $request, ['Ringan', 'Sedang', 'Berat'], fn ($q, $status) => $q->where('status', $status));

        return $this->pdf($keluhan, false, 'riwayat-kesehatan-'.now()->format('Y-m-d').'.pdf');
    }

    /** PDF seluruh riwayat kesehatan satu taruna. */
    public function eksporSatu(Taruna $taruna): Response
    {
        abort_unless($taruna->keluhan()->exists(), 404);

        return $this->pdf($taruna->keluhan()->getQuery(), false, 'riwayat-kesehatan-'.$taruna->npm.'.pdf');
    }

    /** PDF riwayat psikologi seluruh taruna, mengikuti filter aktif di halaman. */
    public function eksporPsikologi(Request $request): Response
    {
        $keluhan = $this->saring(KeluhanPsikologi::query(), $request, ['Melanjutkan konseling', 'Tidak melanjutkan konseling'],
            fn ($q, $status) => $q->where('lanjut_konseling', $status === 'Melanjutkan konseling'));

        return $this->pdf($keluhan, true, 'riwayat-psikologi-'.now()->format('Y-m-d').'.pdf');
    }

    /** PDF seluruh riwayat psikologi satu taruna. */
    public function eksporPsikologiSatu(Taruna $taruna): Response
    {
        $keluhan = KeluhanPsikologi::where('taruna_id', $taruna->id);
        abort_unless($keluhan->exists(), 404);

        return $this->pdf($keluhan, true, 'riwayat-psikologi-'.$taruna->npm.'.pdf');
    }

    /**
     * Filter halaman riwayat: cari nama/NPM, tingkat, status, rentang tanggal awal keluhan.
     *
     * @param  Builder<Keluhan|KeluhanPsikologi>  $keluhan
     * @param  list<string>  $pilihanStatus
     * @return Builder<Keluhan|KeluhanPsikologi>
     */
    private function saring(Builder $keluhan, Request $request, array $pilihanStatus, Closure $filterStatus): Builder
    {
        $filter = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'tingkat' => ['nullable', 'in:I,II,III,IV'],
            'status' => ['nullable', Rule::in($pilihanStatus)],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        return $keluhan
            ->whereHas('taruna', fn ($taruna) => $taruna
                ->when($filter['cari'] ?? null, fn ($q, $cari) => $q->where(fn ($q) => $q->where('nama', 'like', "%{$cari}%")->orWhere('npm', 'like', "%{$cari}%")))
                ->when($filter['tingkat'] ?? null, fn ($q, $tingkat) => $q->where('tingkat', $tingkat)))
            ->when($filter['status'] ?? null, $filterStatus)
            ->when($filter['dari'] ?? null, fn ($q, $dari) => $q->whereDate('tanggal_awal', '>=', $dari))
            ->when($filter['sampai'] ?? null, fn ($q, $sampai) => $q->whereDate('tanggal_awal', '<=', $sampai));
    }

    /** @param  Builder<Keluhan|KeluhanPsikologi>  $keluhan */
    private function pdf(Builder $keluhan, bool $psikologi, string $namaFile): Response
    {
        $perTaruna = $keluhan
            ->with($psikologi
                ? ['taruna', 'riwayat' => fn (HasMany $riwayat) => $riwayat->orderBy('tanggal_konseling')->orderBy('id')]
                : ['taruna', 'riwayatKontrol' => fn (HasMany $kontrol) => $kontrol->orderBy('tanggal_kontrol')])
            ->orderByDesc('tanggal_awal')
            ->get()
            ->groupBy('taruna_id')
            ->sortBy(fn (Collection $episode) => $episode->first()->taruna->nama);

        return Pdf::loadView('riwayat-kesehatan.riwayat-kesehatan-pdf', ['perTaruna' => $perTaruna, 'psikologi' => $psikologi])
            ->setPaper('a4', 'landscape')
            ->download($namaFile);
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
            // Ekspor PDF per jenis riwayat mengikuti akses bagian (route ekspor ada di grup perawat / psikolog)
            'bisaEkspor' => ['kesehatan' => $request->user()->can('bagian-perawat'), 'psikologi' => $bisaLihatPsikologi],
        ]);
    }
}
