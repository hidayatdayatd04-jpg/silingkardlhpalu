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
 * - Content-Security-Policy-Report-Only: kebijakan CSP longgar yang baru
 *   dilaporkan (report-only) agar tidak memecah fitur pihak ketiga yang
 *   sudah ada; naikkan jadi Content-Security-Policy setelah dipantau.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Report-only dulu: kebijakan sengaja longgar (self + inline) agar
        // tidak memecah skrip/inline style yang sudah ada, sambil tetap
        // mendapat laporan pelanggaran di log server.
        $response->headers->set(
            'Content-Security-Policy-Report-Only',
            "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob: https:; font-src 'self' data:; connect-src 'self' https:; frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com; frame-ancestors 'self'; base-uri 'self'; form-action 'self'"
        );

        return $response;
    }
}
