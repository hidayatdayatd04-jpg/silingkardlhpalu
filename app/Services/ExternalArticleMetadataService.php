<?php

namespace App\Services;

use App\Exceptions\ExternalArticleMetadataException;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class ExternalArticleMetadataService
{
    private const HTML_LIMIT = 2_000_000;

    private const IMAGE_LIMIT = 5_242_880;

    private const MAX_REDIRECTS = 3;

    /**
     * Ambil dan validasi metadata untuk preview tanpa menulis ke storage.
     *
     * @return array{title:string,image_url:string,source_url:string}
     */
    public function preview(string $url): array
    {
        $metadata = $this->fetchMetadata($url);
        $this->fetchImage($metadata['image_url']);

        return $metadata;
    }

    /**
     * Ambil gambar remote tervalidasi tanpa menyimpannya ke filesystem.
     *
     * @return array{body:string,content_type:string,url:string}
     */
    public function fetchImage(string $url): array
    {
        return $this->download($url, self::IMAGE_LIMIT, true);
    }

    /**
     * @return array{title:string,image_url:string,source_url:string}
     */
    public function fetchMetadata(string $url): array
    {
        $response = $this->download($url, self::HTML_LIMIT, false);
        $contentType = strtolower(trim(explode(';', $response['content_type'])[0]));

        if ($contentType !== '' && ! in_array($contentType, ['text/html', 'application/xhtml+xml'], true)) {
            throw new ExternalArticleMetadataException('Link tersebut tidak mengarah ke halaman berita HTML.');
        }

        [$title, $imageUrl] = $this->parseMetadata($response['body'], $response['url']);

        if ($title === null) {
            throw new ExternalArticleMetadataException('Judul berita tidak dapat ditemukan dari halaman tersebut.');
        }

        if ($imageUrl === null) {
            throw new ExternalArticleMetadataException('Thumbnail berita tidak dapat ditemukan dari halaman tersebut.');
        }

        return [
            'title' => Str::limit($title, 255, ''),
            'image_url' => $imageUrl,
            'source_url' => $response['url'],
        ];
    }

    /**
     * @return array{body:string,content_type:string,url:string}
     */
    protected function download(string $url, int $limit, bool $expectImage): array
    {
        $currentUrl = trim($url);

        for ($redirects = 0; $redirects <= self::MAX_REDIRECTS; $redirects++) {
            $resolvedIp = $this->assertSafeUrl($currentUrl);

            try {
                $options = [
                    'allow_redirects' => false,
                    'stream' => true,
                    'verify' => true,
                ];

                if (defined('CURLOPT_RESOLVE')) {
                    $parts = parse_url($currentUrl);
                    $port = $parts['port'] ?? (($parts['scheme'] ?? '') === 'https' ? 443 : 80);
                    $options['curl'] = [CURLOPT_RESOLVE => [($parts['host'] ?? '').':'.$port.':'.$resolvedIp]];
                }

                $response = Http::withHeaders([
                    // Sejumlah portal berita menolak user-agent bot generik
                    // walaupun metadata Open Graph-nya publik. Gunakan header
                    // browser read-only yang wajar tanpa cookie atau bypass.
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36',
                    'Accept' => $expectImage ? 'image/*' : 'text/html,application/xhtml+xml',
                    'Accept-Language' => 'id-ID,id;q=0.9,en;q=0.7',
                    'Cache-Control' => 'no-cache',
                ])->connectTimeout(5)->timeout(12)->withOptions($options)->get($currentUrl);
            } catch (Throwable $e) {
                throw new ExternalArticleMetadataException('Website sumber tidak dapat diakses. Silakan periksa link atau coba lagi nanti.');
            }

            if ($this->isRedirect($response)) {
                if ($redirects >= self::MAX_REDIRECTS) {
                    throw new ExternalArticleMetadataException('Website sumber melakukan terlalu banyak pengalihan.');
                }

                $location = trim((string) $response->header('Location'));
                if ($location === '') {
                    throw new ExternalArticleMetadataException('Pengalihan website sumber tidak valid.');
                }

                $currentUrl = $this->resolveUrl($currentUrl, $location);

                continue;
            }

            if (! $response->successful()) {
                throw new ExternalArticleMetadataException('Website sumber merespons dengan status HTTP '.$response->status().'.');
            }

            $declaredLength = (int) $response->header('Content-Length', 0);
            if ($declaredLength > $limit) {
                throw new ExternalArticleMetadataException($expectImage
                    ? 'Ukuran thumbnail berita melebihi batas 5MB.'
                    : 'Halaman berita terlalu besar untuk diproses.');
            }

            $body = $this->readLimitedBody($response, $limit, $expectImage);
            $contentType = (string) $response->header('Content-Type', '');

            if ($expectImage) {
                $mime = strtolower(trim(explode(';', $contentType)[0]));
                if (! str_starts_with($mime, 'image/') || in_array($mime, ['image/svg+xml', 'image/svg'], true)) {
                    throw new ExternalArticleMetadataException('Thumbnail berita bukan file gambar yang valid.');
                }

                $detected = @getimagesizefromstring($body);
                if ($detected === false || ! str_starts_with((string) ($detected['mime'] ?? ''), 'image/')) {
                    throw new ExternalArticleMetadataException('Isi thumbnail berita bukan gambar yang valid.');
                }
            }

            return ['body' => $body, 'content_type' => $contentType, 'url' => $currentUrl];
        }

        throw new ExternalArticleMetadataException('Website sumber tidak dapat diakses.');
    }

    protected function assertSafeUrl(string $url): string
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));

        if (! in_array($scheme, ['http', 'https'], true) || $host === '' || isset($parts['user']) || isset($parts['pass'])) {
            throw new ExternalArticleMetadataException('Link berita harus berupa URL HTTP atau HTTPS yang valid.');
        }

        if (in_array($host, ['localhost', 'localhost.localdomain'], true)
            || Str::endsWith($host, ['.localhost', '.local', '.internal', '.lan', '.home'])) {
            throw new ExternalArticleMetadataException('Link menuju alamat jaringan internal tidak diizinkan.');
        }

        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : $this->resolveHost($host);
        if ($ips === []) {
            throw new ExternalArticleMetadataException('Host website sumber tidak dapat ditemukan.');
        }

        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new ExternalArticleMetadataException('Link menuju alamat jaringan internal atau terlarang tidak diizinkan.');
            }
        }

        return $ips[0];
    }

    /** @return string[] */
    protected function resolveHost(string $host): array
    {
        $ips = [];

        try {
            foreach ((array) dns_get_record($host, DNS_A | DNS_AAAA) as $record) {
                $ip = $record['ip'] ?? $record['ipv6'] ?? null;
                if (is_string($ip)) {
                    $ips[] = $ip;
                }
            }
        } catch (Throwable $e) {
            return [];
        }

        return array_values(array_unique($ips));
    }

    protected function readLimitedBody(Response $response, int $limit, bool $expectImage): string
    {
        $stream = $response->toPsrResponse()->getBody();
        $body = '';

        while (! $stream->eof()) {
            $body .= $stream->read(min(8192, $limit + 1 - strlen($body)));
            if (strlen($body) > $limit) {
                throw new ExternalArticleMetadataException($expectImage
                    ? 'Ukuran thumbnail berita melebihi batas 5MB.'
                    : 'Halaman berita terlalu besar untuk diproses.');
            }
        }

        if ($body === '') {
            throw new ExternalArticleMetadataException($expectImage
                ? 'Thumbnail berita kosong atau tidak dapat dibaca.'
                : 'Halaman berita kosong atau tidak dapat dibaca.');
        }

        return $body;
    }

    protected function isRedirect(Response $response): bool
    {
        return in_array($response->status(), [301, 302, 303, 307, 308], true);
    }

    protected function resolveUrl(string $base, string $relative): string
    {
        try {
            return (string) UriResolver::resolve(new Uri($base), new Uri($relative));
        } catch (Throwable $e) {
            throw new ExternalArticleMetadataException('URL hasil pengalihan atau thumbnail tidak valid.');
        }
    }

    /** @return array{0:?string,1:?string} */
    protected function parseMetadata(string $html, string $baseUrl): array
    {
        $previous = libxml_use_internal_errors(true);
        $document = new \DOMDocument;

        try {
            $loaded = $document->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR);
            if (! $loaded) {
                throw new ExternalArticleMetadataException('Halaman berita tidak dapat dibaca.');
            }

            $xpath = new \DOMXPath($document);
            $title = $this->firstMeta($xpath, 'property', 'og:title')
                ?? $this->firstMeta($xpath, 'name', 'twitter:title')
                ?? $this->firstNodeText($xpath, '//title');
            $image = $this->firstMeta($xpath, 'property', 'og:image')
                ?? $this->firstMeta($xpath, 'name', 'twitter:image');

            $title = $title === null ? null : trim(html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $image = $image === null ? null : trim(html_entity_decode($image, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            return [
                $title !== '' ? $title : null,
                $image !== null && $image !== '' ? $this->resolveUrl($baseUrl, $image) : null,
            ];
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    protected function firstMeta(\DOMXPath $xpath, string $attribute, string $value): ?string
    {
        $query = sprintf('//meta[translate(@%s,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="%s"]/@content', $attribute, strtolower($value));
        $node = $xpath->query($query)?->item(0);

        return $node ? trim($node->nodeValue) : null;
    }

    protected function firstNodeText(\DOMXPath $xpath, string $query): ?string
    {
        $node = $xpath->query($query)?->item(0);

        return $node ? trim($node->textContent) : null;
    }
}
