<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan HTTP dasar untuk seluruh respons web.
 *
 * - X-Content-Type-Options: cegah MIME-sniffing.
 * - X-Frame-Options: cegah clickjacking via iframe.
 * - Referrer-Policy: batasi informasi referrer lintas situs.
 * - Strict-Transport-Security: paksa HTTPS (hanya dikirim saat request
 *   sudah HTTPS; browser mengabaikan header ini di HTTP).
 * - Permissions-Policy: batasi akses fitur browser (kamera, mic, geolokasi).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', "camera=(), microphone=(self), geolocation=(self)");

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
