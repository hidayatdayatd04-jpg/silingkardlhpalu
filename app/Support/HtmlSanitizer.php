<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Sanitasi HTML untuk konten rich-text (artikel, dsb).
 *
 * Konten artikel ditulis lewat editor WYSIWYG (Jodit) lalu dirender apa
 * adanya dengan {!! !!} di halaman publik. Tanpa sanitasi, admin (atau
 * pihak yang membajak akun admin) bisa menyisipkan <script> / handler
 * on* yang berujung stored XSS. Kelas ini membersihkan konten memakai
 * HTMLPurifier dengan whitelist yang cukup untuk kebutuhan artikel:
 * format teks, tabel, gambar, link, dan embed YouTube.
 */
class HtmlSanitizer
{
    protected static ?HTMLPurifier $purifier = null;

    /**
     * Bersihkan HTML: buang script/style/on*-handler/URL javascript:,
     * pertahankan markup artikel yang wajar.
     */
    public static function clean(?string $html): string
    {
        $html = (string) $html;

        if ($html === '') {
            return '';
        }

        return static::purifier()->purify($html);
    }

    protected static function purifier(): HTMLPurifier
    {
        if (static::$purifier !== null) {
            return static::$purifier;
        }

        $config = HTMLPurifier_Config::createDefault();

        // Cache hasil kompilasi definisi agar tidak diulang tiap request.
        $config->set('Cache.SerializerPath', storage_path('framework/cache'));

        // Izinkan iframe khusus YouTube (embed video).
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^https?://(www\.youtube(?:-nocookie)?\.com/embed/|www\.youtube\.com/v/|youtu\.be/)%');

        // Skema URL yang boleh dipakai pada href/src.
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);

        // Atribut umum yang dipakai editor (id/class untuk styling,
        // colspan untuk tabel, target+rel untuk link).
        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('a', 'target', 'Enum#_blank,_self,_parent,_top');
        $def->addAttribute('a', 'rel', 'Text');
        $def->addAttribute('img', 'loading', 'Enum#lazy,eager');
        foreach (['p', 'div', 'span', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'blockquote', 'figure', 'figcaption', 'img', 'a', 'iframe'] as $tag) {
            $def->addAttribute($tag, 'class', 'Text');
        }
        foreach (['td', 'th'] as $tag) {
            $def->addAttribute($tag, 'colspan', 'Number');
            $def->addAttribute($tag, 'rowspan', 'Number');
        }

        static::$purifier = new HTMLPurifier($config);

        return static::$purifier;
    }
}
