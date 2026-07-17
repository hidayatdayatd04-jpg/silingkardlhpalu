<?php

namespace App\Support;

use stdClass;

class ProfileMarkdown
{
    public static function load(?string $path = null): ?stdClass
    {
        $path ??= base_path('profile.md');

        if (! is_file($path)) {
            return null;
        }

        $content = trim((string) file_get_contents($path));

        if ($content === '') {
            return null;
        }

        $visi = self::between($content, 'A. Visi', 'B. Misi');
        $misi = self::between($content, 'B. Misi', 'tugas dan fungsi');
        $tugasFungsi = self::after($content, 'tugas dan fungsi');

        return (object) [
            'visi' => self::markdownLikeToHtml($visi),
            'misi' => self::markdownLikeToHtml($misi),
            'tugas_fungsi' => self::markdownLikeToHtml($tugasFungsi),
            'struktur_organisasi_image' => null,
            'pejabats' => [],
        ];
    }

    private static function between(string $content, string $start, string $end): string
    {
        $startPosition = stripos($content, $start);

        if ($startPosition === false) {
            return '';
        }

        $startPosition += strlen($start);
        $endPosition = stripos($content, $end, $startPosition);

        return trim(substr($content, $startPosition, $endPosition === false ? null : $endPosition - $startPosition));
    }

    private static function after(string $content, string $marker): string
    {
        $position = stripos($content, $marker);

        if ($position === false) {
            return '';
        }

        $lineEnd = strpos($content, "\n", $position);

        return trim(substr($content, $lineEnd === false ? $position + strlen($marker) : $lineEnd));
    }

    private static function markdownLikeToHtml(string $content): string
    {
        $content = str_replace(["\r\n", "\r"], "\n", trim($content));

        if ($content === '') {
            return '';
        }

        $html = [];
        $paragraph = [];
        $listType = null;

        $flushParagraph = function () use (&$html, &$paragraph): void {
            if ($paragraph === []) {
                return;
            }

            $html[] = '<p>'.e(implode(' ', $paragraph)).'</p>';
            $paragraph = [];
        };

        $closeList = function () use (&$html, &$listType): void {
            if ($listType === null) {
                return;
            }

            $html[] = '</'.$listType.'>';
            $listType = null;
        };

        foreach (explode("\n", $content) as $line) {
            $line = trim($line);

            if ($line === '') {
                $flushParagraph();
                $closeList();
                continue;
            }

            if (preg_match('/^(I|II|III|IV|V|VI|VII|VIII|IX|X)\.\s+(.+)$/i', $line, $matches)) {
                $flushParagraph();
                $closeList();
                $html[] = '<h3>'.e($matches[1].'. '.$matches[2]).'</h3>';
                continue;
            }

            if (preg_match('/^\d+\.\s+(.+)$/', $line, $matches)) {
                $flushParagraph();
                if ($listType !== 'ol') {
                    $closeList();
                    $html[] = '<ol>';
                    $listType = 'ol';
                }
                $html[] = '<li>'.e($matches[1]).'</li>';
                continue;
            }

            if (preg_match('/^[a-z]\.\s+(.+)$/i', $line, $matches)) {
                $flushParagraph();
                if ($listType !== 'ul') {
                    $closeList();
                    $html[] = '<ul>';
                    $listType = 'ul';
                }
                $html[] = '<li>'.e($matches[1]).'</li>';
                continue;
            }

            if (preg_match('/^[A-Z]\.\s+(.+)$/', $line, $matches)) {
                $flushParagraph();
                $closeList();
                $html[] = '<h3>'.e($matches[1]).'</h3>';
                continue;
            }

            $paragraph[] = $line;
        }

        $flushParagraph();
        $closeList();

        return implode("\n", $html);
    }
}
