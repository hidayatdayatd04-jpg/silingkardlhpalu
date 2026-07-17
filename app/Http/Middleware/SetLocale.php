<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Locale yang didukung situs publik.
     *
     * @var array<int, string>
     */
    protected array $supported = ['id', 'en'];

    /**
     * Terapkan locale dari sesi (dipilih lewat language switcher) ke setiap request.
     * Default 'id' agar konten Bahasa Indonesia tetap tampil bila belum memilih.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Default 'id' — konten sumber situs berbahasa Indonesia; 'en' hanya bila dipilih.
        $locale = session('locale', 'id');

        if (! in_array($locale, $this->supported, true)) {
            $locale = 'id';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
