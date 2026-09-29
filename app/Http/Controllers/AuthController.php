<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $user = User::where('username', $credentials['username'])
            ->orWhere('email', $credentials['username'])
            ->first();

        if (! $user || ! Auth::attempt(['id' => $user->id, 'password' => $credentials['password']])) {
            throw ValidationException::withMessages([
                'username' => 'Nama pengguna atau kata sandi salah.',
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
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        // Pesan sama walau nama pengguna tidak ada, supaya daftar akun tidak bisa ditebak dari halaman ini.
        User::where('username', $data['username'])->first()
            ?->forceFill(['sandi_baru' => Hash::make($data['password']), 'sandi_diminta_at' => now()])
            ->save();

        return redirect()->route('login')
            ->with('status', 'Permintaan ubah kata sandi terkirim. Kata sandi baru berlaku setelah disetujui admin.');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
