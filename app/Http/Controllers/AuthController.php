<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Akun nonaktif diperlakukan sama dengan akun yang tidak ada.
        $user = User::whereNull('nonaktif_at')
            ->where(fn ($q) => $q->where('username', $credentials['username'])->orWhere('email', $credentials['username']))
            ->first();

        // Akun tidak ada tetap menjalankan bcrypt (biaya make = check, mengikuti BCRYPT_ROUNDS)
        // agar waktu respons tidak membocorkan nama pengguna yang valid. Nilai tetap: biaya bcrypt tidak bergantung
        // panjang masukan, dan sandi > BCRYPT_LIMIT tidak memicu galat 500 yang membedakan akun tidak ada.
        if (! $user) {
            Hash::make('akun-tidak-ada');
        }

        // Galat di kolom kata sandi: nama pengguna tetap terisi dan fokus pindah ke kata sandi untuk diketik ulang
        if (! $user || ! Auth::attempt(['id' => $user->id, 'password' => $credentials['password']])) {
            throw ValidationException::withMessages([
                'password' => 'Nama pengguna atau kata sandi salah.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /** Lupa kata sandi: kata sandi baru disimpan sebagai permintaan dan baru berlaku setelah disetujui admin. */
    public function mintaSandi(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Pesan dan biaya hash sama walau nama pengguna tidak ada, supaya daftar akun tidak bisa ditebak dari halaman ini.
        $sandiBaru = Hash::make($data['password']);
        User::where('username', $data['username'])->whereNull('nonaktif_at')->first()
            ?->forceFill(['sandi_baru' => $sandiBaru, 'sandi_diminta_at' => now()])
            ->save();

        return redirect()->route('login')
            ->with('status', 'Permintaan ubah kata sandi terkirim. Kata sandi baru berlaku setelah disetujui admin.');
    }

    /** Pengaturan: pengguna mengganti kata sandinya sendiri; permintaan lupa kata sandi yang tertunda ikut dibatalkan. */
    public function ubahSandi(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::defaults()],
        ], ['current_password.current_password' => 'Kata sandi saat ini salah.', 'password.different' => 'Kata sandi baru harus berbeda dari kata sandi saat ini.'],
            ['current_password' => 'kata sandi saat ini', 'password' => 'kata sandi baru']);

        $request->user()->forceFill([
            'password' => $data['password'], 'remember_token' => Str::random(60), 'sandi_baru' => null, 'sandi_diminta_at' => null,
            'wajib_ganti_sandi' => false,
        ])->save();

        return redirect()->route('pengaturan')->with('status', 'Kata sandi berhasil diubah.');
    }

    /** Konfirmasi ulang kata sandi sebelum aksi admin berisiko tinggi (middleware password.confirm). */
    public function konfirmasiSandi(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']],
            ['password.current_password' => 'Kata sandi salah.'], ['password' => 'kata sandi']);

        $request->session()->passwordConfirmed();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
