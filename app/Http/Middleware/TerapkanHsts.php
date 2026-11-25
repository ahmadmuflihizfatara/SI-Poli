<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HSTS (RFC 6797): peramban selalu memakai HTTPS untuk kunjungan berikutnya, menutup SSL stripping
 * pada permintaan http:// pertama. Hanya dikirim lewat HTTPS, karena header di HTTP diabaikan peramban.
 */
class TerapkanHsts
{
    /** Satu tahun, ambang minimum Mozilla Observatory dan SSL Labs untuk grade A+. */
    public const MAX_AGE_DETIK = 31536000;

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age='.self::MAX_AGE_DETIK);
        }

        return $response;
    }
}
