<?php

namespace App\Http\Controllers;

use App\Models\KeluhanPsikologi;
use App\Models\Pemeriksaan;
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

    /** Taruna yang sudah punya catatan kesehatan tidak dihapus supaya riwayatnya tidak ikut hilang (FK cascade). */
    public function destroy(Taruna $taruna): RedirectResponse
    {
        $punyaRiwayat = $taruna->keluhan()->exists()
            || KeluhanPsikologi::whereBelongsTo($taruna)->exists()
            || Pemeriksaan::whereBelongsTo($taruna)->exists();

        if ($punyaRiwayat) {
            return back()->with('status', "Taruna {$taruna->nama} tidak dapat dihapus karena sudah memiliki riwayat kesehatan.");
        }

        $taruna->delete();

        return back()->with('status', "Taruna {$taruna->nama} berhasil dihapus.");
    }

    /** @return array{nama: string, npm: ?string, tingkat: ?string, kelas: ?string, jenis_kelamin: ?string, kamar: ?string} */
    private function validasi(Request $request, ?Taruna $taruna = null): array
    {
        return $request->validate([
            'nama' => 'required|string|max:150',
            'npm' => ['nullable', 'string', 'max:20', Rule::unique('taruna', 'npm')->ignore($taruna)],
            'tingkat' => 'nullable|in:I,II,III,IV',
            'kelas' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'kamar' => 'nullable|string|max:20',
        ], [], ['npm' => 'NPM', 'jenis_kelamin' => 'jenis kelamin']);
    }
}
