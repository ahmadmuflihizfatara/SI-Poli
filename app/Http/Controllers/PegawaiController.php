<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
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

        return view('data-pegawai.data-pegawai', [
            'pegawai' => Pegawai::when($q, fn ($query) => $query->where('nama', 'like', "%$q%"))
                ->orderBy('jabatan')
                ->orderBy('nama')
                ->paginate(15)
                ->withQueryString(),
            'q' => $q,
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

    /** @return array{nama: string, jabatan: string} */
    private function validasi(Request $request, ?Pegawai $pegawai = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('pegawai', 'nama')->ignore($pegawai)],
            'jabatan' => ['required', Rule::in(Pegawai::JABATAN)],
        ]);
    }
}
