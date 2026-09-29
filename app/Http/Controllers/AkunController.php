<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Manajemen Akun (khusus admin, route can:admin). */
class AkunController extends Controller
{
    public const ROLE = LogController::PENGGUNA;

    public function index(Request $request): View
    {
        $q = $request->query('q');

        return view('manajemen-akun.manajemen-akun', [
            'akun' => User::when($q, fn ($query) => $query->where(fn ($w) => $w->where('name', 'like', "%$q%")->orWhere('username', 'like', "%$q%")))
                ->orderByRaw('sandi_diminta_at IS NULL') // yang menunggu persetujuan tampil paling atas
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'q' => $q,
            'menunggu' => User::whereNotNull('sandi_diminta_at')->count(),
        ]);
    }

    public function create(): View
    {
        return view('manajemen-akun.tambah-akun');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|alpha_dash|max:50|unique:users,username',
            'role' => ['required', Rule::in(array_keys(self::ROLE))],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [], ['name' => 'nama lengkap', 'username' => 'nama pengguna', 'password' => 'kata sandi']);

        $akun = User::create($data + $this->akses($request, $data['role']));

        return redirect()->route('akun.show', $akun)->with('status', "Akun {$akun->username} berhasil ditambahkan.");
    }

    public function show(User $akun): View
    {
        return view('manajemen-akun.detail-akun', ['akun' => $akun]);
    }

    public function update(Request $request, User $akun): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(self::ROLE))],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [], ['password' => 'kata sandi baru']);

        // Admin tidak bisa menurunkan role dirinya sendiri, supaya sistem tidak kehilangan admin.
        if ($akun->is($request->user()) && $data['role'] !== 'admin') {
            throw ValidationException::withMessages(['role' => 'Role akun sendiri tidak bisa diubah.']);
        }

        $akun->fill(['role' => $data['role']] + $this->akses($request, $data['role']));
        if ($data['password'] ?? null) {
            $akun->forceFill(['password' => $data['password'], 'remember_token' => Str::random(60)]);
        }
        $akun->save();

        return redirect()->route('akun.show', $akun)->with('status', 'Informasi akun berhasil diubah.');
    }

    public function destroy(Request $request, User $akun): RedirectResponse
    {
        abort_if($akun->is($request->user()), 403, 'Akun sendiri tidak bisa dihapus.');
        $akun->delete();

        return redirect()->route('akun.index')->with('status', "Akun {$akun->username} berhasil dihapus.");
    }

    public function setujuiSandi(User $akun): RedirectResponse
    {
        abort_unless($akun->sandi_diminta_at, 404);
        // sandi_baru sudah di-hash; cast "hashed" tidak meng-hash ulang nilai yang sudah ter-hash.
        $akun->forceFill([
            'password' => $akun->sandi_baru, 'remember_token' => Str::random(60),
            'sandi_baru' => null, 'sandi_diminta_at' => null,
        ])->save();

        return back()->with('status', "Perubahan kata sandi {$akun->username} disetujui.");
    }

    public function tolakSandi(User $akun): RedirectResponse
    {
        $akun->forceFill(['sandi_baru' => null, 'sandi_diminta_at' => null])->save();

        return back()->with('status', "Permintaan ubah kata sandi {$akun->username} ditolak.");
    }

    /** Checkbox akses; admin selalu punya semua akses. */
    private function akses(Request $request, string $role): array
    {
        return [
            'akses_tambah' => $role === 'admin' || $request->boolean('akses_tambah'),
            'akses_edit' => $role === 'admin' || $request->boolean('akses_edit'),
        ];
    }
}
