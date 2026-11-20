<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akun nonaktif langsung dikeluarkan (cadangan bila sesinya masih tersisa), dan akun yang kata sandinya
 * ditetapkan admin hanya bisa membuka Pengaturan sampai kata sandinya diganti sendiri.
 */
class PastikanAkunSiapDipakai
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->nonaktif_at) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        if ($user?->wajib_ganti_sandi && ! $request->routeIs('pengaturan', 'pengaturan.sandi', 'logout')) {
            return redirect()->route('pengaturan')
                ->with('status', 'Kata sandi akun ini ditetapkan admin. Ganti kata sandi terlebih dahulu sebelum memakai sistem.');
        }

        return $next($request);
    }
}
