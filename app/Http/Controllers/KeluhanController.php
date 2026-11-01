<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKeluhanRequest;
use App\Models\Keluhan;
use App\Models\LogAktivitas;
use App\Models\Pegawai;
use App\Models\RiwayatKontrol;
use App\Models\Taruna;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class KeluhanController extends Controller
{
    private const SUMBER_LOG = 'Laporan Kesehatan';

    public function index(Request $request): View
    {
        // Psikolog (atau admin yang memilih bagian psikolog) melihat laporan konseling
        if ($this->bagian($request) === 'psikolog') {
            return app(KonselingController::class)->index();
        }

        return view('laporan-kesehatan.laporan-kesehatan', ['laporan' => $this->laporanAktif()]);
    }

    public function ekspor(Request $request): Response
    {
        if ($this->bagian($request) === 'psikolog') {
            return app(KonselingController::class)->ekspor();
        }

        $pdf = Pdf::loadView('laporan-kesehatan.laporan-kesehatan-pdf', ['laporan' => $this->laporanAktif()])
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-kesehatan-'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * Baris yang ditandai Sembuh tetap tampil di tabel selama 24 jam sejak ditandai, lalu hilang.
     * Urutan bawaan: kontrol terdekat/terlambat di atas; sudah sembuh atau tanpa jadwal di bawah (sama tanggal: keluhan terbaru dulu).
     */
    private function laporanAktif()
    {
        return Keluhan::with(['taruna', 'pembuat:id,name', 'riwayatKontrol' => fn ($q) => $q->latest('tanggal_kontrol')])
            ->where(fn ($q) => $q->where('status_pemulihan', '!=', 'Sembuh')->orWhere('updated_at', '>=', now()->subDay()))
            ->latest('tanggal_awal')
            ->get()
            ->sortBy(fn (Keluhan $k) => $k->status_pemulihan !== 'Sembuh' && $k->tanggal_kontrol_selanjutnya
                ? $k->tanggal_kontrol_selanjutnya->format('Ymd')
                : '99999999')
            // keluhan taruna yang sama selalu bersusun; urutan kelompok = keluhan paling mendesak di dalamnya
            ->groupBy('taruna_id')
            ->flatten(1)
            ->values();
    }

    public function create(): View
    {
        return view('laporan-kesehatan.keluhan-baru', ['taruna' => Taruna::orderBy('nama')->get()]);
    }

    public function store(StoreKeluhanRequest $request)
    {
        $data = $request->validated();
        $data['terapi'] = Keluhan::terapiDariObat($data['obat']);
        $waktuLaporan = Carbon::parse($data['waktu_laporan']);

        $taruna = Taruna::findOrFail($data['taruna_id']);

        $keluhan = Keluhan::create([
            'taruna_id' => $taruna->id,
            'created_by' => auth()->id(),
            'tanggal_awal' => $waktuLaporan->toDateString(),
            'waktu_laporan' => $waktuLaporan,
            'tanggal_kontrol_selanjutnya' => $data['tanggal_kontrol_selanjutnya'] ?? null,
            'keluhan' => $data['keluhan'],
            'terapi' => $data['terapi'],
            'hasil_pemeriksaan' => $data['hasil_pemeriksaan'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
            'status' => $data['status'],
            ...Keluhan::kondisiDariIsian($data),
            'ruang_kelas' => $taruna->kelas,
            'ruang_kamar' => $taruna->kamar,
        ]);

        LogAktivitas::catat('Tambah', self::SUMBER_LOG,
            "Keluhan baru taruna {$keluhan->taruna->nama} ditambahkan: {$data['keluhan']}, status {$data['status']}, terapi {$data['terapi']}, "
            .($keluhan->tanggal_kontrol_selanjutnya ? 'kontrol selanjutnya '.$keluhan->tanggal_kontrol_selanjutnya->format('d-m-Y') : 'tanpa jadwal kontrol').'.',
            $keluhan->id);

        return redirect()->route('laporan-kesehatan.index')->with('status', 'Keluhan baru berhasil ditambahkan.');
    }

    public function show(Keluhan $keluhan): View
    {
        $keluhan->load(['taruna', 'pembuat:id,name', 'riwayatKontrol' => fn ($q) => $q->with(['pembuat:id,name', 'pemeriksa:id,nama'])->oldest('tanggal_kontrol')->oldest('id')]);

        return view('laporan-kesehatan.detail-keluhan', [
            'keluhan' => $keluhan,
            'pegawai' => Pegawai::orderBy('jabatan')->orderBy('nama')->get(['id', 'nama', 'jabatan']),
        ]);
    }

    public function toggleSembuh(Keluhan $keluhan)
    {
        // Sembuh bersifat final: permintaan ulang ditolak, status tidak bisa dikembalikan atau diubah lagi.
        abort_if($keluhan->status_pemulihan === 'Sembuh', 403, 'Taruna sudah dinyatakan sembuh, status tidak bisa diubah lagi.');

        $lama = $keluhan->status_pemulihan;
        $keluhan->update(['status_pemulihan' => 'Sembuh']);
        LogAktivitas::catat('Edit', self::SUMBER_LOG,
            "Status pemulihan taruna {$keluhan->taruna->nama} diubah dari {$lama} menjadi Sembuh.", $keluhan->id);

        return back()->with('status', 'Status pemulihan taruna diperbarui.');
    }

    public function updateKontrol(Request $request, Keluhan $keluhan)
    {
        abort_if($keluhan->status_pemulihan === 'Sembuh', 403, 'Taruna sudah dinyatakan sembuh, keluhan tidak bisa diperbarui lagi.');

        $data = $request->validate([
            'hasil_pemeriksaan' => 'required|string',
            'pemeriksa_id' => 'required|exists:pegawai,id',
            ...Keluhan::aturanVital(),
            ...Keluhan::ATURAN_OBAT,
            'perlu_rujukan' => 'required|in:1,0',
            'tempat_rujukan' => 'nullable|string|max:150',
            'tanggal_kontrol_selanjutnya' => 'required|date|after_or_equal:today',
        ], Keluhan::PESAN_VITAL, Keluhan::LABEL_OBAT + Keluhan::LABEL_VITAL + ['hasil_pemeriksaan' => 'hasil kontrol', 'pemeriksa_id' => 'nama pemeriksa']);
        // tempat rujukan hanya berarti bila memang dirujuk
        $tempatRujukan = $data['perlu_rujukan'] ? ($data['tempat_rujukan'] ?? null) : null;
        $data['terapi'] = Keluhan::terapiDariObat($data['obat']);

        $kontrol = $keluhan->riwayatKontrol()->create([
            'created_by' => auth()->id(),
            'pemeriksa_id' => $data['pemeriksa_id'],
            'tanggal_kontrol' => now()->toDateString(),
            'hasil_kontrol' => $data['hasil_pemeriksaan'],
            ...Keluhan::kondisiDariIsian($data),
            'terapi' => $data['terapi'],
            'perlu_rujukan' => (bool) $data['perlu_rujukan'],
            'tempat_rujukan' => $tempatRujukan,
            'tanggal_kontrol_selanjutnya' => $data['tanggal_kontrol_selanjutnya'],
        ]);

        $terapiLama = $keluhan->terapi;
        $keluhan->update([
            'terapi' => $data['terapi'],
            'tanggal_kontrol_selanjutnya' => $data['tanggal_kontrol_selanjutnya'],
        ]);

        $terapi = $terapiLama === $data['terapi'] ? "terapi tetap {$data['terapi']}" : "terapi diubah dari {$terapiLama} menjadi {$data['terapi']}";
        LogAktivitas::catat('Edit', self::SUMBER_LOG,
            "Keluhan taruna {$keluhan->taruna->nama} diperbarui dengan hasil kontrol {$data['hasil_pemeriksaan']} oleh {$kontrol->pemeriksa->nama}, {$terapi}, kontrol selanjutnya "
            .$keluhan->tanggal_kontrol_selanjutnya->format('d-m-Y').($data['perlu_rujukan'] ? ', perlu rujukan'.($tempatRujukan ? " ke {$tempatRujukan}" : '') : '').'.',
            $keluhan->id);

        return redirect()->route('laporan-kesehatan.show', $keluhan)->with('status', 'Hasil kontrol berhasil disimpan.');
    }

    /** Isi atau perbarui tempat rujukan sebuah kontrol yang dirujuk, setelah tempatnya jelas. */
    public function updateRujukan(Request $request, Keluhan $keluhan, RiwayatKontrol $kontrol)
    {
        abort_unless($kontrol->keluhan_id === $keluhan->id && $kontrol->perlu_rujukan, 404);
        abort_if($keluhan->status_pemulihan === 'Sembuh', 403, 'Taruna sudah dinyatakan sembuh, keluhan tidak bisa diperbarui lagi.');

        $data = $request->validate(['tempat_rujukan' => 'required|string|max:150'], [], ['tempat_rujukan' => 'tempat rujukan']);
        $lama = $kontrol->tempat_rujukan;
        $kontrol->update($data);

        LogAktivitas::catat('Edit', self::SUMBER_LOG,
            "Tempat rujukan taruna {$keluhan->taruna->nama} (kontrol ".$kontrol->tanggal_kontrol->format('d-m-Y').') '
            .($lama ? "diubah dari {$lama} menjadi {$data['tempat_rujukan']}." : "diisi {$data['tempat_rujukan']}."),
            $keluhan->id);

        return redirect()->route('laporan-kesehatan.show', $keluhan)->with('status', 'Tempat rujukan berhasil disimpan.');
    }
}
