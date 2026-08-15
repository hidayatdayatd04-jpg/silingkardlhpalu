<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Support\AdminAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Prefix path yang selalu dilewati (tidak diblokir) oleh mode pemeliharaan.
     *
     * - admin   : panel admin harus tetap bisa diakses (termasuk /admin/login).
     * - up      : health check hosting.
     * - assets / build / storage / favicon : aset statis (ditangani web server,
     *             dicek di sini sebagai pengaman tambahan).
     *
     * @var array<int, string>
     */
    /**
     * Blokir akses halaman publik bila mode pemeliharaan aktif.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Path yang sudah dinormalisasi (bebas leading slash & segmen
        // 'index.php' bila rewrite tidak menyaringnya, mis. saat diakses
        // lewat reverse proxy / Cloudflare Tunnel).
        $path = $this->normalizePath($request);

        // 1) Path yang dikecualikan (admin, health, aset statis) selalu lolos.
        if ($this->isExcludedPath($path)) {
            return $next($request);
        }

        // 2) Hitung status admin panel (untuk opsi pratinjau eksplisit di bawah).
        $isAdmin = AdminAccess::hasAnyPanelRole($request->user());

        // 3) Bila tidak aktif, lanjutkan seperti biasa.
        if (! Setting::get('maintenance_enabled', false)) {
            return $next($request);
        }

        // 4) Endpoint API (web API) â†’ balas JSON 503.
        // 4) Admin pemilik hak panel boleh mem-pratinjau situs publik, tetapi
        //    HARUS eksplisit via ?preview=1 agar tidak mengira maintenance gagal.
        //    Tanpa parameter ini, seluruh pengunjung (termasuk admin) melihat
        //    halaman pemeliharaan -- sesuai tujuan memblokir akses publik.
        if ($isAdmin && $request->boolean('preview')) {
            return $next($request);
        }

        if (str_starts_with($path, 'api')) {
            return response()->json([
                'message' => 'Sistem sedang dalam pemeliharaan.',
            ], 503);
        }

        // 5) Sisa path publik â†’ tampilkan halaman maintenance (status 503).
        return response()->view('maintenance', [
            'logo'        => asset('assets/images/logo-web.png'),
            'estimatedAt' => Setting::get('maintenance_estimated_at'),
            'isAdmin'     => $isAdmin,
        ], 503);
    }

    /**
     * Normalisasi path permintaan.
     *
     * Menghilangkan leading slash dan segmen 'index.php' (mis. path menjadi
     * '/index.php/admin' saat rewrite tidak aktif / lewat proxy), agar
     * logika pengecualian konsisten baik di lokal maupun di produksi.
     */
    protected function normalizePath(Request $request): string
    {
        $path = ltrim($request->path(), '/');

        return (string) preg_replace('#^index\.php/#', '', $path);
    }

    /**
     * Cek apakah path (sudah dinormalisasi) termasuk yang dikecualikan
     * dari mode pemeliharaan.
     *
     * Pengecualian menggunakan batas segmen agar awalan pendek tidak
     * salah memotong route publik lain. Contoh: 'up' hanya cocok tepat
     * untuk health check, sehingga '/uptd/...' (halaman publik UPTD)
     * tetap terblokir saat pemeliharaan.
     */
    protected function isExcludedPath(string $path): bool
    {
        // Pengecualian tepat (tidak boleh memotong route lain).
        if (in_array($path, ['up', 'admin', 'login'], true)) {
            return true;
        }

        // Pengecualian berbasis awalan segmen.
        foreach (['admin/', 'assets/', 'build/', 'storage/', 'favicon'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
