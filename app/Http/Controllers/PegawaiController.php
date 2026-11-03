<?php

namespace App\Http\Controllers;

use App\Models\Keluhan;
use App\Models\KeluhanKlb;
use App\Models\KeluhanPsikologi;
use App\Models\KontrolKlb;
use App\Models\Pegawai;
use App\Models\RiwayatKontrol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Data Pegawai (khusus admin, route can:admin): dokter, perawat, dan psikolog yang dipilih sebagai pemeriksa. */
class PegawaiController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->query('q');
        $jabatan = (array) $request->query('jabatan');

        return view('data-pegawai.data-pegawai', [
            'pegawai' => Pegawai::when($q, fn ($query) => $query->where('nama', 'like', "%$q%"))
                ->when($jabatan, fn ($query) => $query->whereIn('jabatan', $jabatan))
                ->orderBy('jabatan')
                ->orderBy('nama')
                ->paginate(15)
                ->withQueryString(),
            'q' => $q,
            'jabatan' => $jabatan,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $pegawai = Pegawai::create($this->validasi($request));

        return redirect()->route('pegawai.index')->with('status', "Pegawai {$pegawai->nama} berhasil ditambahkan.");
    }

    public function update(Request $request, Pegawai $pegawai): RedirectResponse
    {
        $pegawai->update($this->validasi($request, $pegawai));

        return back()->with('status', "Data pegawai {$pegawai->nama} berhasil diubah.");
    }

    /** Pegawai yang sudah tercatat sebagai pemeriksa tidak dihapus supaya nama pemeriksa di catatan lama tetap ada (FK null on delete). */
    public function destroy(Pegawai $pegawai): RedirectResponse
    {
        $pernahMemeriksa = collect([Keluhan::class, KeluhanPsikologi::class, KeluhanKlb::class, RiwayatKontrol::class, KontrolKlb::class])
            ->contains(fn (string $model) => $model::where('pemeriksa_id', $pegawai->id)->exists());

        if ($pernahMemeriksa) {
            return back()->with('status', "Pegawai {$pegawai->nama} tidak dapat dihapus karena sudah tercatat sebagai pemeriksa.");
        }

        $pegawai->delete();

        return back()->with('status', "Pegawai {$pegawai->nama} berhasil dihapus.");
    }

    /** @return array{nama: string, jabatan: string} */
    private function validasi(Request $request, ?Pegawai $pegawai = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('pegawai', 'nama')->ignore($pegawai)],
            'jabatan' => ['required', Rule::in(Pegawai::JABATAN)],
        ]);
    }
}
