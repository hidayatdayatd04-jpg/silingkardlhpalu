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
        // Juga regenerate file fisik public/sitemap.xml agar fallback tetap sinkron.
        $xml = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn (): string => self::buildXml());

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Build XML string tanpa cache — dipakai untuk regenerasi file fisik dan cache.
     */
    public static function buildXml(): string
    {
        $base = rtrim((string) config('app.url'), '/');
        if ($base === '') {
            $base = 'https://www.silingkardlhpalu.web.id';
        }

        $static = [
            ['loc' => $base,                             'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => $base.'/pengaduan',                'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => $base.'/lacak',                    'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => $base.'/berita',                   'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => $base.'/profil',                   'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => $base.'/tentang',                  'priority' => '0.6', 'changefreq' => 'monthly'],
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
    }

    /**
     * Regenerasi file fisik public/sitemap.xml agar fallback tidak stale.
     * Dipanggil otomatis saat Artikel saved/deleted; juga bisa dipanggil via command/deploy hook.
     */
    public static function regenerateStaticFile(): void
    {
        try {
            $xml = self::buildXml();
            $path = public_path('sitemap.xml');
            // Tulis atomically: tulis ke .tmp lalu rename agar tidak corrupt saat dibaca crawler
            $tmp = $path.'.tmp';
            file_put_contents($tmp, $xml, LOCK_EX);
            @rename($tmp, $path);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal regenerate public/sitemap.xml: '.$e->getMessage());
        }
    }

    /**
     * Helper untuk invalidasi cache dari luar (mis. observer / command).
     */
    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
