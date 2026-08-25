<?php

namespace App\Support\Admin;

/**
 * Utilitas URL panel admin.
 *
 * Notifikasi menyimpan href absolut pada saat dibuat. Dua hal bisa membuat
 * href tersebut mati di kemudian hari bila dirender apa adanya:
 *
 * 1. Segmen prefix lama '/admin/...' — route itu sengaja tidak ada setelah
 *    lokasi panel dipindah ke env ADMIN_PATH.
 * 2. Host berubah — mis. baris lama tersimpan dengan host non-www
 *    (https://domain.tld/...) sementara situs live diakses lewat www
 *    (https://www.domain.tld/...), sehingga klik tidak membawa sesi login.
 * 3. Host rusak — baris lama tersimpan saat APP_URL masih salah konfigurasi,
 *    mis. host tanpa TLD (https://domain/admin/...). Host seperti ini tak
 *    akan pernah cocok dengan APP_URL mana pun; segmen legacy '/admin'
 *    menjadi penanda bahwa href tersebut buatan panel ini sendiri.
 *
 * Helper ini menulis ulang href saat DIRENDER — bukan saat disimpan:
 * segmen legacy ditulis ulang ke prefix aktif, dan URL milik aplikasi
 * sendiri (host sama, www maupun non-www) diringkas menjadi path
 * relatif-root agar selalu mengikuti host yang sedang diakses pengunjung.
 */
class AdminUrl
{
    /**
     * Segmen path legacy yang dipakai sebelum env ADMIN_PATH diperkenalkan.
     */
    protected const LEGACY_SEGMENT = 'admin';

    /**
     * Normalisasi href notifikasi agar selalu valid pada host & prefix aktif.
     *
     * - '#' / kosong / skema non-http(s) / tautan domain lain: apa adanya.
     * - URL internal absolut: diringkas jadi path relatif-root, lalu segmen
     *   legacy '/admin' ditulis ulang ke ADMIN_PATH aktif (bila berbeda).
     * - Host luar yang kebetulan membawa segmen legacy '/admin' tetap
     *   dinormalisasi: segmen itu hanya pernah dipakai panel ini.
     */
    public static function normalizeLegacyHref(?string $href): string
    {
        if ($href === null || $href === '' || $href === '#') {
            return $href ?? '#';
        }

        $scheme = parse_url($href, PHP_URL_SCHEME);
        $host = parse_url($href, PHP_URL_HOST);

        // Hanya proses URL absolut http(s) atau path relatif-root.
        if (! is_string($host) && ! str_starts_with($href, '/')) {
            return $href;
        }

        if (is_string($scheme) && ! in_array(strtolower($scheme), ['http', 'https'], true)) {
            return $href;
        }

        $path = parse_url($href, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return $href;
        }

        $segments = explode('/', ltrim($path, '/'));
        $isLegacy = ($segments[0] ?? '') === self::LEGACY_SEGMENT;

        // Host di luar aplikasi dibiarkan apa adanya — KECUALI membawa
        // segmen legacy '/admin', yang hanya pernah dipakai panel ini dan
        // pasti berasal dari baris tersimpan saat APP_URL belum benar.
        if (is_string($host)
            && ! $isLegacy
            && ! self::isInternalHost(strtolower($host))) {
            return $href;
        }

        // Pertahankan query & fragment dari href asli.
        $suffix = '';
        foreach ([PHP_URL_QUERY => '?', PHP_URL_FRAGMENT => '#'] as $component => $glue) {
            $part = parse_url($href, $component);
            if ($part !== null && $part !== false) {
                $suffix .= $glue.$part;
            }
        }

        $current = trim((string) config('app.admin_path'), '/');
        if ($isLegacy && $current !== self::LEGACY_SEGMENT) {
            $segments[0] = $current;
        }

        // Path relatif-root: browser melengkapinya dengan host yang sedang
        // dibuka, sehingga www / non-www / localhost tidak lagi relevan.
        return '/'.implode('/', $segments).$suffix;
    }

    /**
     * Cek apakah host termasuk milik aplikasi ini — dibandingkan dengan host
     * APP_URL dan host request aktif. Varian www / non-www dan host satu
     * keluarga domain (mis. 'domain' vs 'domain.tld', jejak APP_URL lama
     * yang tidak lengkap) sama-sama dianggap internal.
     */
    protected static function isInternalHost(string $host): bool
    {
        $candidates = [];

        $appUrlHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        if (is_string($appUrlHost) && $appUrlHost !== '') {
            $candidates[] = strtolower($appUrlHost);
        }

        // Request hanya ada pada konteks HTTP (di console/queue tidak di-bound).
        if (app()->bound('request')) {
            $requestHost = request()->getHost();
            if (is_string($requestHost) && $requestHost !== '') {
                $candidates[] = strtolower($requestHost);
            }
        }

        foreach ($candidates as $candidate) {
            if (self::hostsRelated(self::withoutWww($host), self::withoutWww($candidate))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Dua host dianggap sekeluarga bila identik atau salah satunya merupakan
     * prefix domain yang lain — menangkap varian 'domain.tld' vs
     * 'www.domain.tld' maupun 'domain' vs 'domain.tld'.
     */
    protected static function hostsRelated(string $one, string $two): bool
    {
        if ($one === $two) {
            return true;
        }

        return str_starts_with($one, $two.'.') || str_starts_with($two, $one.'.');
    }

    /**
     * Buang prefiks 'www.' untuk perbandingan host yang setara.
     */
    protected static function withoutWww(string $host): string
    {
        return str_starts_with($host, 'www.') ? substr($host, 4) : $host;
    }
}
