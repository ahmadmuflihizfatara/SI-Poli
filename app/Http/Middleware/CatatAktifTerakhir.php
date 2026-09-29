<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Isi kolom "Aktif Terakhir" di Manajemen Akun; ditulis paling sering sekali per menit per pengguna. */
class CatatAktifTerakhir
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && ! $user->terakhir_aktif_at?->gt(now()->subMinute())) {
            $user->timestamps = false; // aktivitas bukan perubahan data akun
            $user->forceFill(['terakhir_aktif_at' => now()])->save();
            $user->timestamps = true;
        }

        return $next($request);
    }
}
