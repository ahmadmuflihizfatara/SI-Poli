<?php

namespace App\Http\Controllers;

use App\Models\Keluhan;
use App\Models\LogAktivitas;
use App\Models\Taruna;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KeluhanController extends Controller
{
    private const SUMBER_LOG = 'Laporan Kesehatan';

    public function index(): View
    {
        return view('laporan-kesehatan.laporan-kesehatan', ['laporan' => $this->laporanAktif()]);
    }

    public function ekspor(): \Illuminate\Http\Response
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan-kesehatan.laporan-kesehatan-pdf', ['laporan' => $this->laporanAktif()])
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-kesehatan-'.now()->format('Y-m-d').'.pdf');
    }

    /** Baris yang ditandai Sembuh hilang dari tabel mulai keesokan harinya, bukan hari itu juga. */
    private function laporanAktif()
    {
        return Keluhan::with(['taruna', 'riwayatKontrol' => fn ($q) => $q->latest('tanggal_kontrol')])
            ->where(fn ($q) => $q->where('status_pemulihan', '!=', 'Sembuh')->orWhereDate('updated_at', today()))
            ->latest('tanggal_awal')
            ->get();
    }

    public function create(): View
    {
        return view('laporan-kesehatan.keluhan-baru', ['taruna' => Taruna::orderBy('nama')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'taruna_id' => 'required|exists:taruna,id',
            'tekanan_darah' => 'required|string|max:20',
            'suhu' => 'required|numeric|between:30,45',
            'nadi' => 'required|integer|min:0',
            'saturasi' => 'nullable|integer|between:0,100',
            'pernapasan' => 'nullable|integer|min:0',
            'skala_nyeri' => 'nullable|integer|between:0,10',
            'ruang_kelas' => 'nullable|string|max:50',
            'ruang_kamar' => 'nullable|string|max:50',
            'keluhan' => 'required|string',
            'terapi' => 'required|string',
            'hasil_pemeriksaan' => 'nullable|string',
            'status' => 'required|in:Ringan,Sedang,Berat',
            'keterangan' => 'nullable|string',
            'tanggal_kontrol_selanjutnya' => 'required|date|after_or_equal:today',
        ]);

        $keluhan = Keluhan::create([
            'taruna_id' => $data['taruna_id'],
            'created_by' => auth()->id(),
            'tanggal_awal' => now()->toDateString(),
            'tanggal_kontrol_selanjutnya' => $data['tanggal_kontrol_selanjutnya'],
            'keluhan' => $data['keluhan'],
            'terapi' => $data['terapi'],
            'hasil_pemeriksaan' => $data['hasil_pemeriksaan'] ?? null,
            'status' => $data['status'],
            'tekanan_darah' => $data['tekanan_darah'],
            'suhu' => $data['suhu'],
            'nadi' => $data['nadi'],
            'saturasi' => $data['saturasi'] ?? null,
            'pernapasan' => $data['pernapasan'] ?? null,
            'skala_nyeri' => $data['skala_nyeri'] ?? null,
            'ruang_kelas' => $data['ruang_kelas'] ?? null,
            'ruang_kamar' => $data['ruang_kamar'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
        ]);

        LogAktivitas::catat('Tambah', self::SUMBER_LOG,
            "Keluhan baru taruna {$keluhan->taruna->nama} ditambahkan: {$data['keluhan']}, status {$data['status']}, terapi {$data['terapi']}, kontrol selanjutnya ".$keluhan->tanggal_kontrol_selanjutnya->format('d-m-Y').'.',
            $keluhan->id);

        return redirect()->route('laporan-kesehatan.index')->with('status', 'Keluhan baru berhasil ditambahkan.');
    }

    public function show(Keluhan $keluhan): View
    {
        $keluhan->load(['taruna', 'riwayatKontrol' => fn ($q) => $q->oldest('tanggal_kontrol')]);

        return view('laporan-kesehatan.detail-keluhan', ['keluhan' => $keluhan]);
    }

    public function toggleSembuh(Keluhan $keluhan)
    {
        // Sembuh bersifat final, tidak bisa dikembalikan ke status sebelumnya.
        if ($keluhan->status_pemulihan !== 'Sembuh') {
            $lama = $keluhan->status_pemulihan;
            $keluhan->update(['status_pemulihan' => 'Sembuh']);
            LogAktivitas::catat('Edit', self::SUMBER_LOG,
                "Status pemulihan taruna {$keluhan->taruna->nama} diubah dari {$lama} menjadi Sembuh.", $keluhan->id);
        }

        return back()->with('status', 'Status pemulihan taruna diperbarui.');
    }

    public function editKontrol(Keluhan $keluhan): View
    {
        abort_if($keluhan->status_pemulihan === 'Sembuh', 403, 'Taruna sudah dinyatakan sembuh, keluhan tidak bisa diperbarui lagi.');

        $keluhan->load('taruna');

        return view('laporan-kesehatan.perbarui-keluhan', ['keluhan' => $keluhan]);
    }

    public function updateKontrol(Request $request, Keluhan $keluhan)
    {
        abort_if($keluhan->status_pemulihan === 'Sembuh', 403, 'Taruna sudah dinyatakan sembuh, keluhan tidak bisa diperbarui lagi.');

        $data = $request->validate([
            'hasil_pemeriksaan' => 'required|string',
            'terapi' => 'required|string',
            'keterangan' => 'nullable|string',
            'perlu_rujukan' => 'required|in:1,0',
            'tanggal_kontrol_selanjutnya' => 'required|date|after_or_equal:today',
        ]);

        $keluhan->riwayatKontrol()->create([
            'tanggal_kontrol' => now()->toDateString(),
            'hasil_kontrol' => $data['hasil_pemeriksaan'],
            'keterangan' => $data['keterangan'] ?? null,
            'perlu_rujukan' => (bool) $data['perlu_rujukan'],
        ]);

        $terapiLama = $keluhan->terapi;
        $keluhan->update([
            'terapi' => $data['terapi'],
            'tanggal_kontrol_selanjutnya' => $data['tanggal_kontrol_selanjutnya'],
        ]);

        $terapi = $terapiLama === $data['terapi'] ? "terapi tetap {$data['terapi']}" : "terapi diubah dari {$terapiLama} menjadi {$data['terapi']}";
        LogAktivitas::catat('Edit', self::SUMBER_LOG,
            "Keluhan taruna {$keluhan->taruna->nama} diperbarui dengan hasil kontrol {$data['hasil_pemeriksaan']}, {$terapi}, kontrol selanjutnya "
            .$keluhan->tanggal_kontrol_selanjutnya->format('d-m-Y').($data['perlu_rujukan'] ? ', perlu rujukan' : '').'.',
            $keluhan->id);

        return redirect()->route('laporan-kesehatan.index')->with('status', 'Hasil kontrol berhasil disimpan.');
    }
}
