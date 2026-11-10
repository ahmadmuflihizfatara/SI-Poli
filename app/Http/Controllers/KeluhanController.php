<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKeluhanRequest;
use App\Http\Requests\StoreKontrolRequest;
use App\Models\Keluhan;
use App\Models\LogAktivitas;
use App\Models\Pegawai;
use App\Models\RiwayatKontrol;
use App\Models\Taruna;
use App\Services\Kolaborasi;
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
     * Urutan bawaan: keluhan terbaru di atas.
     */
    private function laporanAktif()
    {
        return Keluhan::with(['taruna', 'pembuat:id,name', 'pemeriksa:id,nama', 'riwayatKontrol' => fn ($q) => $q->latest('tanggal_kontrol')])
            ->where(fn ($q) => $q->where('status_pemulihan', '!=', 'Sembuh')->orWhere('updated_at', '>=', now()->subDay()))
            ->latest('tanggal_awal')->latest('id')
            ->get()
            // keluhan taruna yang sama selalu bersusun; urutan kelompok = keluhan terbaru di dalamnya
            ->groupBy('taruna_id')
            ->flatten(1)
            ->values();
    }

    public function create(Request $request): View
    {
        return view('laporan-kesehatan.keluhan-baru', [
            'taruna' => Taruna::orderBy('nama')->get(),
            'pegawai' => Pegawai::pemeriksa(Pegawai::JABATAN_POLIKLINIK),
            'draf' => Kolaborasi::draf($request),
        ]);
    }

    public function store(StoreKeluhanRequest $request)
    {
        $data = $request->validated();
        // draf yang diisi bersama hanya disimpan sekali, oleh akun yang lebih dulu menekan simpan
        if ($oleh = Kolaborasi::catatSimpan($request->input('_kolab'), $request->user())) {
            return redirect()->route('laporan-kesehatan.index')->with('status', "Keluhan ini sudah disimpan oleh $oleh.");
        }
        $data['terapi'] = Keluhan::terapiDariObat($data['obat']);
        $waktuLaporan = Carbon::parse($data['waktu_laporan']);

        $taruna = Taruna::findOrFail($data['taruna_id']);

        $keluhan = Keluhan::create([
            'taruna_id' => $taruna->id,
            'created_by' => auth()->id(),
            'pemeriksa_id' => $data['pemeriksa_id'],
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

        // baris pertama Riwayat Kontrol = data saat keluhan dilaporkan (waktu = waktu laporan)
        $kontrolAwal = $keluhan->riwayatKontrol()->make([
            'created_by' => auth()->id(),
            'pemeriksa_id' => $data['pemeriksa_id'],
            'tanggal_kontrol' => $waktuLaporan->toDateString(),
            'hasil_kontrol' => $data['hasil_pemeriksaan'] ?? $data['keluhan'],
            ...Keluhan::kondisiDariIsian($data),
            'terapi' => $data['terapi'],
            'tanggal_kontrol_selanjutnya' => $data['tanggal_kontrol_selanjutnya'] ?? null,
        ]);
        $kontrolAwal->created_at = $waktuLaporan;
        $kontrolAwal->save();

        LogAktivitas::catat('Tambah', self::SUMBER_LOG,
            "Keluhan baru taruna {$keluhan->taruna->nama} ditambahkan oleh pemeriksa {$keluhan->pemeriksa->nama}: {$data['keluhan']}, status {$data['status']}, terapi {$data['terapi']}, "
            .($keluhan->tanggal_kontrol_selanjutnya ? 'kontrol selanjutnya '.$keluhan->tanggal_kontrol_selanjutnya->format('d-m-Y') : 'tanpa jadwal kontrol').'.',
            $keluhan->id);

        return redirect()->route('laporan-kesehatan.index')->with('status', 'Keluhan baru berhasil ditambahkan.');
    }

    public function show(Keluhan $keluhan): View
    {
        $keluhan->load(['taruna', 'pembuat:id,name', 'pemeriksa:id,nama', 'riwayatKontrol' => fn ($q) => $q->with(['pembuat:id,name', 'pemeriksa:id,nama'])->latest('tanggal_kontrol')->latest('id')]);

        return view('laporan-kesehatan.detail-keluhan', [
            'keluhan' => $keluhan,
            'pegawai' => Pegawai::pemeriksa(Pegawai::JABATAN_POLIKLINIK),
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

    public function updateKontrol(StoreKontrolRequest $request, Keluhan $keluhan)
    {
        abort_if($keluhan->status_pemulihan === 'Sembuh', 403, 'Taruna sudah dinyatakan sembuh, keluhan tidak bisa diperbarui lagi.');

        $kontrol = $keluhan->riwayatKontrol()->create($request->kontrol());

        $terapiLama = $keluhan->terapi;
        $keluhan->update($kontrol->only('terapi', 'tanggal_kontrol_selanjutnya'));

        $terapi = $terapiLama === $kontrol->terapi ? "terapi tetap {$kontrol->terapi}" : "terapi diubah dari {$terapiLama} menjadi {$kontrol->terapi}";
        LogAktivitas::catat('Edit', self::SUMBER_LOG,
            "Keluhan taruna {$keluhan->taruna->nama} diperbarui dengan hasil kontrol {$kontrol->hasil_kontrol} oleh {$kontrol->pemeriksa->nama}, {$terapi}, kontrol selanjutnya "
            .$keluhan->tanggal_kontrol_selanjutnya->format('d-m-Y').($kontrol->perlu_rujukan ? ', perlu rujukan'.($kontrol->tempat_rujukan ? " ke {$kontrol->tempat_rujukan}" : '') : '').'.',
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
