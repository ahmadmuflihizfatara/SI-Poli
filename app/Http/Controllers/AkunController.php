<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Manajemen Akun (khusus admin, route can:admin). Aksi yang mengubah akun mewajibkan konfirmasi ulang
 * kata sandi admin (password.confirm) dan selalu tercatat di Log Sistem.
 */
class AkunController extends Controller
{
    public const ROLE = LogController::PENGGUNA;

    private const SUMBER_LOG = 'Manajemen Akun';

    /** Nama pengguna yang mudah ditebak (nama role & akun umum) tidak boleh dipakai akun baru. */
    public const USERNAME_TERLARANG = [
        'admin', 'administrator', 'root', 'test', 'superuser', 'sipoli', 'poliklinik',
        'perawat', 'dokter', 'psikolog', 'asisten', 'asisten_kesehatan', 'pengasuh',
    ];

    public function index(Request $request): View
    {
        $q = $request->query('q');
        $role = (array) $request->query('role');

        return view('manajemen-akun.manajemen-akun', [
            'akun' => User::when($q, fn ($query) => $query->where(fn ($w) => $w->where('name', 'like', "%$q%")->orWhere('username', 'like', "%$q%")))
                ->when($role, fn ($query) => $query->whereIn('role', $role))
                ->orderByRaw('sandi_diminta_at IS NULL') // yang menunggu persetujuan tampil paling atas
                ->orderByRaw('nonaktif_at IS NOT NULL') // akun nonaktif paling bawah
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'q' => $q,
            'role' => $role,
            'menunggu' => User::whereNotNull('sandi_diminta_at')->whereNull('nonaktif_at')->count(),
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
            'username' => ['required', 'alpha_dash', 'lowercase', 'min:5', 'max:50', Rule::notIn(self::USERNAME_TERLARANG), 'unique:users,username'],
            'role' => ['required', Rule::in(array_keys(self::ROLE))],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], ['username.not_in' => 'Nama pengguna ini mudah ditebak; gunakan identitas personal (misalnya nama lengkap atau NIP).'],
            ['name' => 'nama lengkap', 'username' => 'nama pengguna', 'password' => 'kata sandi']);

        $akun = User::create($data);
        // Kata sandi dibuat admin: pemilik akun wajib menggantinya saat pertama masuk.
        $akun->forceFill(['wajib_ganti_sandi' => true])->save();

        LogAktivitas::catat('Tambah', self::SUMBER_LOG, "Akun {$akun->username} ({$akun->name}) ditambahkan dengan role ".self::ROLE[$akun->role].'.');

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
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ], [], ['password' => 'kata sandi baru']);

        // Admin tidak bisa menurunkan role dirinya sendiri, supaya sistem tidak kehilangan admin.
        if ($akun->is($request->user()) && $data['role'] !== 'admin') {
            throw ValidationException::withMessages(['role' => 'Role akun sendiri tidak bisa diubah.']);
        }

        $perubahan = [];
        if ($akun->role !== $data['role']) {
            $perubahan[] = 'role '.self::ROLE[$akun->role].' menjadi '.self::ROLE[$data['role']];
            $akun->role = $data['role'];
        }
        if ($data['password'] ?? null) {
            $perubahan[] = 'kata sandi diatur ulang admin';
            $akun->forceFill(['password' => $data['password'], 'wajib_ganti_sandi' => ! $akun->is($request->user())]);
        }
        $akun->save();

        if ($perubahan) {
            // Hak akses / kata sandi lama tidak boleh tetap berlaku di sesi yang sedang berjalan.
            if (! $akun->is($request->user())) {
                $akun->putusSesi();
            }
            LogAktivitas::catat('Edit', self::SUMBER_LOG, "Akun {$akun->username} diubah: ".implode(', ', $perubahan).'.');
        }

        return redirect()->route('akun.show', $akun)->with('status', 'Informasi akun berhasil diubah.');
    }

    /** De-provisioning tanpa menghapus baris, supaya atribusi pembuat pada data medis dan log tetap utuh. */
    public function nonaktifkan(Request $request, User $akun): RedirectResponse
    {
        abort_if($akun->is($request->user()), 403, 'Akun sendiri tidak bisa dinonaktifkan.');

        $akun->forceFill(['nonaktif_at' => now(), 'sandi_baru' => null, 'sandi_diminta_at' => null])->save();
        $akun->putusSesi();
        LogAktivitas::catat('Edit', self::SUMBER_LOG, "Akun {$akun->username} dinonaktifkan.");

        return redirect()->route('akun.show', $akun)->with('status', "Akun {$akun->username} berhasil dinonaktifkan.");
    }

    public function aktifkan(User $akun): RedirectResponse
    {
        $akun->forceFill(['nonaktif_at' => null])->save();
        LogAktivitas::catat('Edit', self::SUMBER_LOG, "Akun {$akun->username} diaktifkan kembali.");

        return redirect()->route('akun.show', $akun)->with('status', "Akun {$akun->username} berhasil diaktifkan kembali.");
    }

    public function setujuiSandi(User $akun): RedirectResponse
    {
        abort_unless($akun->sandi_diminta_at, 404);
        // sandi_baru sudah di-hash; cast "hashed" tidak meng-hash ulang nilai yang sudah ter-hash.
        $akun->forceFill([
            'password' => $akun->sandi_baru, 'sandi_baru' => null, 'sandi_diminta_at' => null,
        ])->save();
        $akun->putusSesi();
        LogAktivitas::catat('Edit', self::SUMBER_LOG, "Permintaan ubah kata sandi akun {$akun->username} disetujui.");

        return back()->with('status', "Perubahan kata sandi {$akun->username} disetujui.");
    }

    public function tolakSandi(User $akun): RedirectResponse
    {
        $akun->forceFill(['sandi_baru' => null, 'sandi_diminta_at' => null])->save();
        LogAktivitas::catat('Edit', self::SUMBER_LOG, "Permintaan ubah kata sandi akun {$akun->username} ditolak.");

        return back()->with('status', "Permintaan ubah kata sandi {$akun->username} ditolak.");
    }
}
