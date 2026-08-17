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
 * - Content-Security-Policy: kebijakan CSP aktif (enforced). Policy masih
 *   memakai 'unsafe-inline'/'unsafe-eval' pada script-src karena banyak
 *   inline script Blade/Livewire/Alpine; migrasi ke nonce adalah langkah
 *   lanjutan agar proteksi XSS CSP benar-benar efektif.
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

        // CSP enforced: memblokir sumber daya di luar policy. 'unsafe-inline'
        // dan 'unsafe-eval' masih dipertahankan pada script-src karena
        // inline script Blade/Livewire/Alpine; hapus bertahap via nonce.
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob: https:; font-src 'self' data:; connect-src 'self' https:; frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com; frame-ancestors 'self'; base-uri 'self'; form-action 'self'"
        );

        return $response;
    }
}
