<?php

use App\Http\Middleware\EnsureAdminPanelAccess;
use App\Http\Middleware\TrackWebsiteVisit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Trust reverse proxies (Cloudflare Tunnel, Nginx Proxy, dll).
        // Daftar proxy yang dipercaya diambil dari env TRUSTED_PROXIES
        // (pisahkan dengan koma). '*' berarti percaya semua alamat —
        // hanya dipakai sebagai fallback bila env belum diisi.
        $trustedProxies = env('TRUSTED_PROXIES', '172.16.0.0/12,127.0.0.1');
        $middleware->trustProxies(
            at: $trustedProxies === '*'
                ? '*'
                : array_values(array_filter(array_map('trim', explode(',', $trustedProxies)))),
            headers:
                Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->alias([
            'admin.access' => EnsureAdminPanelAccess::class,
            'track.visit' => TrackWebsiteVisit::class,
            'no-store' => \App\Http\Middleware\NoStoreCacheHeaders::class,
        ]);

        $middleware->appendToGroup('web', TrackWebsiteVisit::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckMaintenanceMode::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\SecurityHeaders::class);

        // Bila pengunjung (belum login) membuka rute terlindungi seperti
        // /admin saat mode pemeliharaan, arahkan langsung ke login panel
        // (/admin/login), bukan ke rute /login (redirect) yang ikut
        // terblokir mode pemeliharaan sehingga admin tidak bisa login.
        // (di Laravel 12, guest() memperlakukan nilai ini sebagai path,
        // bukan nama route, sehingga harus berupa path lengkap.)
        $middleware->redirectGuestsTo('/admin/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
