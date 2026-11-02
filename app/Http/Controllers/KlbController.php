<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKeluhanKlbRequest;
use App\Http\Requests\StoreKontrolRequest;
use App\Models\KejadianLuarBiasa;
use App\Models\Keluhan;
use App\Models\KeluhanKlb;
use App\Models\KontrolKlb;
use App\Models\LogAktivitas;
use App\Models\Pegawai;
use App\Models\Taruna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/** Kejadian Luar Biasa: laporan wabah sederhana, datanya terpisah dari Laporan Kesehatan utama. */
class KlbController extends Controller
{
    private const SUMBER_LOG = 'Kejadian Luar Biasa';

    public function index(): View
    {
        return view('kejadian-luar-biasa.kejadian-luar-biasa', [
            'klb' => KejadianLuarBiasa::withCount('keluhan')
                ->orderByRaw("status = 'Selesai'") // yang berlangsung di depan
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => 'required|string|max:150',
            'deskripsi' => 'required|string|max:2000',
        ], [], ['nama' => 'nama kejadian luar biasa', 'deskripsi' => 'deskripsi kejadian luar biasa']);

        $klb = KejadianLuarBiasa::create($data + ['created_by' => $request->user()->id]);
        LogAktivitas::catat('Tambah', self::SUMBER_LOG, "Kejadian luar biasa {$klb->nama} ditambahkan: {$klb->deskripsi}");

        return redirect()->route('klb.show', $klb)->with('status', 'Kejadian luar biasa berhasil ditambahkan.');
    }

    public function show(KejadianLuarBiasa $klb): View
    {
        $klb->load(['keluhan' => fn ($q) => $q->with('kontrolTerakhir')->orderBy('nama')]);

        return view('kejadian-luar-biasa.detail-klb', ['klb' => $klb]);
    }

    public function selesai(KejadianLuarBiasa $klb): RedirectResponse
    {
        if (! $klb->selesai()) {
            $klb->update(['status' => 'Selesai', 'selesai_at' => now()]);
            LogAktivitas::catat('Edit', self::SUMBER_LOG, "Kejadian luar biasa {$klb->nama} dinyatakan selesai.");
        }

        return back()->with('status', 'Kejadian luar biasa dinyatakan selesai.');
    }

    /** Formulir sama dengan Tambahkan Keluhan Baru di Laporan Kesehatan, tanpa tingkat keparahan. */
    public function createKeluhan(KejadianLuarBiasa $klb): View
    {
        $this->pastikanBerlangsung($klb);

        return view('laporan-kesehatan.keluhan-baru', [
            'klb' => $klb,
            'taruna' => Taruna::orderBy('nama')->get(),
            'pegawai' => Pegawai::pemeriksa(Pegawai::JABATAN_POLIKLINIK),
        ]);
    }

    public function storeKeluhan(StoreKeluhanKlbRequest $request, KejadianLuarBiasa $klb): RedirectResponse
    {
        $this->pastikanBerlangsung($klb);

        $data = $request->validated();
        $data['terapi'] = Keluhan::terapiDariObat($data['obat']);
        $waktuLaporan = Carbon::parse($data['waktu_laporan']);
        $isian = [
            'created_by' => $request->user()->id,
            'pemeriksa_id' => $data['pemeriksa_id'],
            ...Keluhan::kondisiDariIsian($data),
            'terapi' => $data['terapi'],
            'tanggal_kontrol_selanjutnya' => $data['tanggal_kontrol_selanjutnya'],
        ];

        // Data umum disalin dari tabel taruna saat keluhan dibuat (keluhan KLB menyimpan salinannya sendiri)
        $taruna = Taruna::findOrFail($data['taruna_id'])->only(['nama', 'npm', 'kelas', 'tingkat', 'jenis_kelamin', 'kamar']);
        $keluhan = $klb->keluhan()->create($taruna + $isian + [
            'waktu_laporan' => $waktuLaporan,
            'keluhan' => $data['keluhan'],
            'hasil_pemeriksaan' => $data['hasil_pemeriksaan'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
        ]);

        // baris pertama Riwayat Kontrol = data saat keluhan dilaporkan (sama dengan Laporan Kesehatan)
        $kontrolAwal = $keluhan->kontrol()->make($isian + [
            'tanggal_kontrol' => $waktuLaporan->toDateString(),
            'hasil_kontrol' => $data['hasil_pemeriksaan'] ?? $data['keluhan'],
        ]);
        $kontrolAwal->created_at = $waktuLaporan;
        $kontrolAwal->save();

        LogAktivitas::catat('Tambah', self::SUMBER_LOG,
            "Keluhan taruna {$keluhan->nama} ditambahkan pada KLB {$klb->nama} oleh pemeriksa {$keluhan->pemeriksa->nama}: {$keluhan->keluhan}, terapi {$keluhan->terapi}.");

        return redirect()->route('klb.show', $klb)->with('status', "Keluhan {$keluhan->nama} berhasil ditambahkan.");
    }

    /** Tampilan sama dengan Informasi Keluhan di Laporan Kesehatan. */
    public function showKeluhan(KejadianLuarBiasa $klb, KeluhanKlb $keluhan): View
    {
        abort_unless((int) $keluhan->kejadian_luar_biasa_id === $klb->id, 404);
        $keluhan->load(['pembuat:id,name', 'pemeriksa:id,nama', 'kontrol' => fn ($q) => $q->with(['pembuat:id,name', 'pemeriksa:id,nama'])->latest('tanggal_kontrol')->latest('id')]);

        return view('laporan-kesehatan.detail-keluhan', [
            'klb' => $klb,
            'keluhan' => $keluhan,
            'pegawai' => Pegawai::pemeriksa(Pegawai::JABATAN_POLIKLINIK),
        ]);
    }

    public function storeKontrol(StoreKontrolRequest $request, KejadianLuarBiasa $klb, KeluhanKlb $keluhan): RedirectResponse
    {
        abort_unless((int) $keluhan->kejadian_luar_biasa_id === $klb->id, 404);
        $this->pastikanBerlangsung($klb);

        $kontrol = $keluhan->kontrol()->create($request->kontrol());
        $keluhan->update($kontrol->only('terapi', 'tanggal_kontrol_selanjutnya'));
        LogAktivitas::catat('Edit', self::SUMBER_LOG,
            "Keluhan taruna {$keluhan->nama} pada KLB {$klb->nama} diperbarui dengan hasil kontrol {$kontrol->hasil_kontrol} oleh {$kontrol->pemeriksa->nama}, terapi {$kontrol->terapi}, kontrol selanjutnya "
            .$keluhan->tanggal_kontrol_selanjutnya->format('d-m-Y').($kontrol->perlu_rujukan ? ', perlu rujukan'.($kontrol->tempat_rujukan ? " ke {$kontrol->tempat_rujukan}" : '') : '').'.');

        return redirect()->route('klb.keluhan.show', [$klb, $keluhan])->with('status', 'Hasil kontrol berhasil disimpan.');
    }

    /** Isi atau perbarui tempat rujukan sebuah kontrol yang dirujuk, setelah tempatnya jelas. */
    public function updateRujukan(Request $request, KejadianLuarBiasa $klb, KeluhanKlb $keluhan, KontrolKlb $kontrol): RedirectResponse
    {
        abort_unless((int) $keluhan->kejadian_luar_biasa_id === $klb->id && (int) $kontrol->keluhan_klb_id === $keluhan->id && $kontrol->perlu_rujukan, 404);
        $this->pastikanBerlangsung($klb);

        $data = $request->validate(['tempat_rujukan' => 'required|string|max:150'], [], ['tempat_rujukan' => 'tempat rujukan']);
        $kontrol->update($data);
        LogAktivitas::catat('Edit', self::SUMBER_LOG,
            "Tempat rujukan taruna {$keluhan->nama} pada KLB {$klb->nama} (kontrol ".$kontrol->tanggal_kontrol->format('d-m-Y').") diisi {$data['tempat_rujukan']}.");

        return redirect()->route('klb.keluhan.show', [$klb, $keluhan])->with('status', 'Tempat rujukan berhasil disimpan.');
    }

    /** KLB yang sudah selesai tidak bisa ditambah keluhan/kontrol lagi. */
    private function pastikanBerlangsung(KejadianLuarBiasa $klb): void
    {
        abort_if($klb->selesai(), 403, 'Kejadian luar biasa sudah selesai, data tidak bisa ditambah lagi.');
    }
}
