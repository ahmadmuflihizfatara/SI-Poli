<?php

namespace App\Http\Controllers;

use App\Models\KejadianLuarBiasa;
use App\Models\KeluhanKlb;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function createKeluhan(KejadianLuarBiasa $klb): View
    {
        $this->pastikanBerlangsung($klb);

        return view('kejadian-luar-biasa.tambah-keluhan-klb', ['klb' => $klb]);
    }

    public function storeKeluhan(Request $request, KejadianLuarBiasa $klb): RedirectResponse
    {
        $this->pastikanBerlangsung($klb);

        $data = $request->validate([
            'nama' => 'required|string|max:150',
            'npm' => 'required|string|max:20',
            'kelas' => 'required|string|max:20',
            'tingkat' => 'required|in:I,II,III,IV',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'kamar' => 'required|string|max:20',
            'keluhan' => 'required|string',
            'terapi' => 'required|string',
            'hasil_pemeriksaan' => 'nullable|string',
            'status' => 'required|in:Ringan,Sedang,Berat',
            'tanggal_kontrol_selanjutnya' => 'required|date|after_or_equal:today',
        ], [], ['npm' => 'NPM', 'terapi' => 'terapi dan obat', 'status' => 'tingkat keparahan']);

        $keluhan = $klb->keluhan()->create($data + ['created_by' => $request->user()->id]);
        LogAktivitas::catat('Tambah', self::SUMBER_LOG,
            "Keluhan taruna {$keluhan->nama} ditambahkan pada KLB {$klb->nama}: {$keluhan->keluhan}, status {$keluhan->status}, terapi {$keluhan->terapi}.");

        return redirect()->route('klb.show', $klb)->with('status', "Keluhan {$keluhan->nama} berhasil ditambahkan.");
    }

    public function showKeluhan(KejadianLuarBiasa $klb, KeluhanKlb $keluhan): View
    {
        abort_unless((int) $keluhan->kejadian_luar_biasa_id === $klb->id, 404);
        $keluhan->load(['kontrol' => fn ($q) => $q->oldest('tanggal_kontrol')->oldest('id')]);

        return view('kejadian-luar-biasa.detail-keluhan-klb', ['klb' => $klb, 'keluhan' => $keluhan]);
    }

    public function storeKontrol(Request $request, KejadianLuarBiasa $klb, KeluhanKlb $keluhan): RedirectResponse
    {
        abort_unless((int) $keluhan->kejadian_luar_biasa_id === $klb->id, 404);
        $this->pastikanBerlangsung($klb);

        $data = $request->validate([
            'hasil_kontrol' => 'required|string',
            'tanggal_kontrol_selanjutnya' => 'nullable|date|after_or_equal:today',
        ], [], ['hasil_kontrol' => 'hasil kontrol']);

        $keluhan->kontrol()->create(['tanggal_kontrol' => today(), 'hasil_kontrol' => $data['hasil_kontrol']]);
        if ($data['tanggal_kontrol_selanjutnya'] ?? null) {
            $keluhan->update(['tanggal_kontrol_selanjutnya' => $data['tanggal_kontrol_selanjutnya']]);
        }
        LogAktivitas::catat('Edit', self::SUMBER_LOG,
            "Keluhan taruna {$keluhan->nama} pada KLB {$klb->nama} diperbarui dengan hasil kontrol {$data['hasil_kontrol']}.");

        return back()->with('status', 'Hasil kontrol berhasil disimpan.');
    }

    /** KLB yang sudah selesai tidak bisa ditambah keluhan/kontrol lagi. */
    private function pastikanBerlangsung(KejadianLuarBiasa $klb): void
    {
        abort_if($klb->selesai(), 403, 'Kejadian luar biasa sudah selesai, data tidak bisa ditambah lagi.');
    }
}
