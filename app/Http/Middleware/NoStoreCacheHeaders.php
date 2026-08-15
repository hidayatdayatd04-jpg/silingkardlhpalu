<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Task 10 — header HTTP Cache-Control asli untuk halaman admin.
 *
 * Meta tag `<meta http-equiv="Cache-Control">` di layout tidak reliable
 * (browser modern mengutamakan HTTP header asli). Middleware ini menggantikan
 * meta tag tersebut dengan response header sungguhan, sehingga browser/proxy
 * benar-benar tidak me-nyimpan halaman admin transaksional (data selalu fresh).
 *
 * Dipasang pada grup route admin yang butuh data real-time/transaksi
 * (bukan blanket ke halaman statis).
 */
class NoStoreCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}