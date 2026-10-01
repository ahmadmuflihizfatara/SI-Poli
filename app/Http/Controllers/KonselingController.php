<?php

namespace App\Http\Controllers;

use App\Models\KeluhanPsikologi;
use App\Models\LogAktivitas;
use App\Models\Taruna;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/** Laporan Kesehatan bagian psikolog: keluhan psikologi & riwayat konseling (admin & psikolog, route can:bagian-psikolog). */
class KonselingController extends Controller
{
    private const SUMBER_LOG = 'Laporan Psikologi';

    public function index(): View
    {
        return view('laporan-kesehatan.laporan-psikologi', ['laporan' => $this->laporan()]);
    }

    public function ekspor(): Response
    {
        return Pdf::loadView('laporan-kesehatan.laporan-psikologi-pdf', ['laporan' => $this->laporan()])
            ->setPaper('a4', 'landscape')
            ->download('laporan-psikologi-'.now()->format('Y-m-d').'.pdf');
    }

    /** Yang masih melanjutkan konseling tampil paling atas. */
    private function laporan()
    {
        return KeluhanPsikologi::with(['taruna', 'konselingTerakhir'])
            ->orderByDesc('lanjut_konseling')
            ->latest('tanggal_awal')->latest('id')
            ->get();
    }

    public function create(): View
    {
        return view('laporan-kesehatan.keluhan-psikologi-baru', ['taruna' => Taruna::orderBy('nama')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'taruna_id' => 'required|exists:taruna,id',
            'keluhan' => 'required|string',
            'terapi' => 'required|string',
            ...$this->aturanLanjut(),
        ], [], ['terapi' => 'terapi psikologi']);

        $lanjut = (bool) $data['lanjut_konseling'];
        $konseling = KeluhanPsikologi::create([
            'taruna_id' => $data['taruna_id'],
            'created_by' => $request->user()->id,
            'tanggal_awal' => today(),
            'keluhan' => $data['keluhan'],
            'terapi' => $data['terapi'],
            'lanjut_konseling' => $lanjut,
            'tanggal_konseling_selanjutnya' => $data['tanggal_konseling_selanjutnya'] ?? null,
            'selesai_at' => $lanjut ? null : now(),
        ]);

        LogAktivitas::catat('Tambah', self::SUMBER_LOG,
            "Keluhan psikologi taruna {$konseling->taruna->nama} ditambahkan: {$konseling->keluhan}, terapi {$konseling->terapi}, {$this->ringkasLanjut($konseling)}.");

        return redirect()->route('laporan-kesehatan.index')->with('status', 'Keluhan psikologi berhasil ditambahkan.');
    }

    public function show(KeluhanPsikologi $konseling): View
    {
        $konseling->load(['taruna', 'riwayat' => fn ($q) => $q->oldest('tanggal_konseling')->oldest('id')]);

        return view('laporan-kesehatan.detail-keluhan-psikologi', ['konseling' => $konseling]);
    }

    public function edit(KeluhanPsikologi $konseling): View
    {
        return view('laporan-kesehatan.perbarui-keluhan-psikologi', ['konseling' => $konseling->load('taruna')]);
    }

    public function update(Request $request, KeluhanPsikologi $konseling): RedirectResponse
    {
        $data = $request->validate([
            'hasil_konseling' => 'required|string',
            'terapi' => 'required|string',
            ...$this->aturanLanjut(),
        ], [], ['hasil_konseling' => 'hasil konseling', 'terapi' => 'terapi psikologi']);

        $konseling->riwayat()->create(['tanggal_konseling' => today(), 'hasil_konseling' => $data['hasil_konseling']]);

        $terapiLama = $konseling->terapi;
        $lanjut = (bool) $data['lanjut_konseling'];
        $konseling->update([
            'terapi' => $data['terapi'],
            'lanjut_konseling' => $lanjut,
            'tanggal_konseling_selanjutnya' => $data['tanggal_konseling_selanjutnya'] ?? null,
            // Waktu selesai dicatat saat pertama dinyatakan tidak lanjut; dibuka lagi bila konseling dilanjutkan
            'selesai_at' => $lanjut ? null : ($konseling->selesai_at ?? now()),
        ]);

        $terapi = $terapiLama === $data['terapi'] ? "terapi tetap {$data['terapi']}" : "terapi diubah dari {$terapiLama} menjadi {$data['terapi']}";
        LogAktivitas::catat('Edit', self::SUMBER_LOG,
            "Keluhan psikologi taruna {$konseling->taruna->nama} diperbarui dengan hasil konseling {$data['hasil_konseling']}, {$terapi}, {$this->ringkasLanjut($konseling)}.");

        return redirect()->route('laporan-kesehatan.index')->with('status', 'Hasil konseling berhasil disimpan.');
    }

    /** Tanggal konseling selanjutnya wajib hanya bila konseling dilanjutkan (bila tidak, diabaikan). */
    private function aturanLanjut(): array
    {
        return [
            'lanjut_konseling' => 'required|boolean',
            'tanggal_konseling_selanjutnya' => 'exclude_unless:lanjut_konseling,1|required|date|after_or_equal:today',
        ];
    }

    private function ringkasLanjut(KeluhanPsikologi $k): string
    {
        return $k->lanjut_konseling
            ? 'melanjutkan konseling, konseling selanjutnya '.$k->tanggal_konseling_selanjutnya->format('d-m-Y')
            : 'tidak melanjutkan konseling';
    }
}
