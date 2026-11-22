<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batas idle (SESSION_LIFETIME) dan batas absolut sesi. Polling /kolaborasi memperbarui last_activity sesi,
 * jadi aktivitas pengguna dicatat terpisah: polling hanya dihitung aktivitas bila membawa ketikan (isian/hapus).
 */
class BatasSesi
{
    /** NIST SP 800-63B AAL2: reautentikasi paling lambat 12 jam sejak login. */
    public const BATAS_ABSOLUT_DETIK = 12 * 3600;

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $sesi = $request->session();
        $kini = now()->getTimestamp();
        $masuk = $sesi->get('sesi_masuk_at', $kini);
        $aktif = $sesi->get('sesi_aktif_at', $kini);

        if ($kini - $aktif > config('session.lifetime') * 60 || $kini - $masuk > self::BATAS_ABSOLUT_DETIK) {
            Auth::logout();
            $sesi->invalidate();
            $sesi->regenerateToken();

            return $request->expectsJson()
                ? response()->json(['message' => 'Sesi berakhir.'], 401)
                : redirect()->route('login')->with('status', 'Sesi berakhir. Silakan masuk kembali.');
        }

        $sesi->put('sesi_masuk_at', $masuk);
        if (! $request->routeIs('kolaborasi') || ! empty($request->input('isian')) || ! empty($request->input('hapus'))) {
            $sesi->put('sesi_aktif_at', $kini);
        }

        return $next($request);
    }
}
