<?php

namespace App\Http\Middleware;

use App\Models\WebsiteVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackWebsiteVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Dicatat SETELAH response dikirim (terminable middleware) agar query DB
     * remote tidak memblokir TTFB. Dengan mod_php, response sudah di-flush
     * ke browser sebelum metode ini dijalankan.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Jangan sentuh output buffer saat unit/feature test — PHPUnit memiliki
        // buffer sendiri yang tidak boleh ditutup dari dalam aplikasi.
        if (! app()->runningUnitTests()) {
            // mod_php menahan response sampai script selesai. Flush buffer SEKARANG
            // agar byte response terdorong ke browser sebelum query DB remote yang
            // lambat di bawah dijalankan — TTFB tidak lagi membayar query ini.
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            flush();
        }

        if (! $request->isMethod('GET') || $request->is(config('app.admin_path').'*') || $request->is('api/*')) {
            return;
        }

        $hasTable = Cache::remember('schema:has:website_visit', now()->addHour(), fn () => Schema::hasTable('website_visit'));

        if (! $hasTable) {
            return;
        }

        $sessionId = $request->hasSession() ? $request->session()->getId() : '';
        $ip = $request->ip() ?? '0.0.0.0';

        // DB berada di host remote — hindari query firstOrCreate pada setiap
        // request. Cukup catat sekali per sesi per hari (cache 1 jam). Versi
        // baru memastikan throttle sebelum reset tidak menghalangi pencatatan
        // kunjungan baru setelah tabel statistik dikosongkan.
        $throttleKey = 'visit:tracked:v3:' . $sessionId . ':' . today()->toDateString();

        if (! Cache::has($throttleKey)) {
            WebsiteVisit::query()->firstOrCreate([
                'visit_date' => today()->toDateString(),
                'ip_address' => $ip,
                'session_id' => $sessionId,
            ]);

            Cache::put($throttleKey, true, now()->addHour());
        }
    }
}
