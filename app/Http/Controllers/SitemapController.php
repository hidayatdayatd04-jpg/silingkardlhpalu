<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Cache key untuk XML hasil render.
     */
    public const CACHE_KEY = 'sitemap:xml';

    /**
     * TTL cache dalam detik (1 jam).
     */
    private const CACHE_TTL = 3600;

    public function index(): Response
    {
        // Cache menyimpan XML string hasil render, bukan view object.
        // Mekanisme invalidasi: TTL 1 jam + forget on Artikel saved/deleted (lihat Artikel::boot).
        $xml = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function (): string {
            // Base URL fleksibel via config('app.url'); Railway production harus set APP_URL=https://www.silingkardlhpalu.web.id
            // sehingga perubahan domain cukup via env tanpa edit source code.
            // Domain non-www (https://silingkardlhpalu.web.id) khusus lokal/dev, bukan production, dan tidak akan masuk sitemap.
            $base = rtrim((string) config('app.url'), '/');
            if ($base === '') {
                $base = 'https://www.silingkardlhpalu.web.id';
            }

            // Daftar halaman public canonical yang layak diindeks Google.
            // lastmod di-omit untuk static URL karena tidak ada sumber tanggal valid
            // (sesuai keputusan final revisi #3). Hanya artikel yang punya lastmod valid.
            // Homepage canonical tanpa trailing slash sesuai spec: '/' -> base saja.
            $static = [
                ['loc' => $base,                             'priority' => '1.0', 'changefreq' => 'daily'],
                ['loc' => $base.'/pengaduan',                'priority' => '0.8', 'changefreq' => 'weekly'],
                ['loc' => $base.'/lacak',                    'priority' => '0.8', 'changefreq' => 'weekly'],
                ['loc' => $base.'/berita',                   'priority' => '0.8', 'changefreq' => 'daily'],
                ['loc' => $base.'/profil',                   'priority' => '0.7', 'changefreq' => 'monthly'],
                ['loc' => $base.'/tentang',                  'priority' => '0.6', 'changefreq' => 'monthly'],
                ['loc' => $base.'/armada',                   'priority' => '0.7', 'changefreq' => 'weekly'],
                ['loc' => $base.'/peta-persampahan',         'priority' => '0.7', 'changefreq' => 'weekly'],
                ['loc' => $base.'/tata-lingkungan',          'priority' => '0.7', 'changefreq' => 'weekly'],
                ['loc' => $base.'/permohonan-rekomendasi',   'priority' => '0.7', 'changefreq' => 'monthly'],
                ['loc' => $base.'/pengajuan-rintek-pertek',  'priority' => '0.7', 'changefreq' => 'monthly'],
                ['loc' => $base.'/registrasi-usaha-lb3',     'priority' => '0.7', 'changefreq' => 'monthly'],
                ['loc' => $base.'/pinjam-taman',             'priority' => '0.7', 'changefreq' => 'monthly'],
                ['loc' => $base.'/uptd/lab-lingkungan',      'priority' => '0.6', 'changefreq' => 'monthly'],
                ['loc' => $base.'/uptd/tpa-kawatuna',        'priority' => '0.6', 'changefreq' => 'monthly'],
                ['loc' => $base.'/uptd/tpa-kawatuna/sejarah','priority' => '0.6', 'changefreq' => 'yearly'],
                ['loc' => $base.'/kebijakan-privasi',        'priority' => '0.5', 'changefreq' => 'yearly'],
                ['loc' => $base.'/syarat-ketentuan',         'priority' => '0.5', 'changefreq' => 'yearly'],
            ];

            $artikels = Artikel::published()
                ->select(['slug', 'updated_at', 'tanggal_publish'])
                ->orderByDesc('tanggal_publish')
                ->get();

            return view('sitemap', [
                'static'   => $static,
                'artikels' => $artikels,
                'base'     => $base,
            ])->render();
        });

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Helper untuk invalidasi cache dari luar (mis. observer / command).
     */
    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
