<?php

namespace App\Http\Controllers;

use App\Models\Taruna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Data Taruna (khusus admin, route can:admin): taruna yang dipilih di Laporan Kesehatan. */
class TarunaController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->query('q');

        return view('data-taruna.data-taruna', [
            'taruna' => Taruna::when($q, fn ($query) => $query->where(fn ($w) => $w->where('nama', 'like', "%$q%")->orWhere('npm', 'like', "%$q%")))
                ->orderBy('nama')
                ->paginate(15)
                ->withQueryString(),
            'q' => $q,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $taruna = Taruna::create($this->validasi($request));

        return redirect()->route('taruna.index')->with('status', "Taruna {$taruna->nama} berhasil ditambahkan.");
    }

    public function update(Request $request, Taruna $taruna): RedirectResponse
    {
        $taruna->update($this->validasi($request, $taruna));

        return back()->with('status', "Data taruna {$taruna->nama} berhasil diubah.");
    }

    /** @return array{nama: string, npm: string, tingkat: string, kelas: string, jenis_kelamin: string, kamar: string} */
    private function validasi(Request $request, ?Taruna $taruna = null): array
    {
        return $request->validate([
            'nama' => 'required|string|max:150',
            'npm' => ['required', 'string', 'max:20', Rule::unique('taruna', 'npm')->ignore($taruna)],
            'tingkat' => 'required|in:I,II,III,IV',
            'kelas' => 'required|string|max:20',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'kamar' => 'required|string|max:20',
        ], [], ['npm' => 'NPM', 'jenis_kelamin' => 'jenis kelamin']);
    }
}
