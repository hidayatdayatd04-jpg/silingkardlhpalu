<?php

namespace Tests\Feature;

use App\Exceptions\ExternalArticleMetadataException;
use App\Services\ExternalArticleMetadataService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalArticleMetadataServiceTest extends TestCase
{
    private string $png;

    protected function setUp(): void
    {
        parent::setUp();

        $this->png = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2nsgAAAAASUVORK5CYII=');
    }

    public function test_metadata_priority_and_relative_image_are_resolved_and_validated(): void
    {
        Http::fake([
            'https://example.com/news/item' => Http::response(<<<'HTML'
                <html><head>
                    <title>Fallback Title</title>
                    <meta name="twitter:title" content="Twitter Title">
                    <meta property="og:title" content="OG Title">
                    <meta name="twitter:image" content="/twitter.png">
                    <meta property="og:image" content="../images/cover.png">
                </head></html>
                HTML, 200, ['Content-Type' => 'text/html; charset=utf-8']),
            'https://example.com/images/cover.png' => Http::response($this->png, 200, ['Content-Type' => 'image/png']),
        ]);

        $result = app(ExternalArticleMetadataService::class)->preview('https://example.com/news/item');

        $this->assertSame('OG Title', $result['title']);
        $this->assertSame('https://example.com/images/cover.png', $result['image_url']);
        $this->assertArrayNotHasKey('preview_image', $result);
        Http::assertSentCount(2);
    }

    public function test_title_and_image_fallbacks_work(): void
    {
        Http::fake([
            'https://example.com/twitter' => Http::response('<meta name="twitter:title" content="Twitter"><meta name="twitter:image" content="//example.com/a.png">', 200, ['Content-Type' => 'text/html']),
            'https://example.com/title' => Http::response('<title>Document Title</title><meta name="twitter:image" content="https://example.com/b.png">', 200, ['Content-Type' => 'text/html']),
            'https://example.com/a.png' => Http::response($this->png, 200, ['Content-Type' => 'image/png']),
            'https://example.com/b.png' => Http::response($this->png, 200, ['Content-Type' => 'image/png']),
        ]);

        $service = app(ExternalArticleMetadataService::class);
        $this->assertSame('Twitter', $service->preview('https://example.com/twitter')['title']);
        $this->assertSame('Document Title', $service->preview('https://example.com/title')['title']);
    }

    public function test_ssrf_targets_and_unsupported_schemes_are_rejected_before_request(): void
    {
        Http::fake();
        $service = app(ExternalArticleMetadataService::class);
        $urls = [
            'http://localhost/news',
            'http://127.0.0.1/news',
            'http://0.0.0.0/news',
            'http://[::1]/news',
            'http://10.0.0.1/news',
            'http://172.16.0.1/news',
            'http://192.168.1.1/news',
            'http://169.254.169.254/latest/meta-data',
            'ftp://example.com/news',
            'file:///etc/passwd',
            'data:text/html,test',
            'javascript:alert(1)',
        ];

        foreach ($urls as $url) {
            try {
                $service->fetchMetadata($url);
                $this->fail('URL seharusnya ditolak: '.$url);
            } catch (ExternalArticleMetadataException $e) {
                $this->assertNotSame('', $e->getMessage());
            }
        }

        Http::assertNothingSent();
    }

    public function test_redirect_to_private_ip_is_rejected(): void
    {
        Http::fake([
            'https://example.com/redirect' => Http::response('', 302, ['Location' => 'http://169.254.169.254/latest/meta-data']),
        ]);

        $this->expectException(ExternalArticleMetadataException::class);
        app(ExternalArticleMetadataService::class)->fetchMetadata('https://example.com/redirect');
    }

    public function test_public_hostname_resolving_to_private_ip_is_rejected(): void
    {
        Http::fake();
        $service = new class extends ExternalArticleMetadataService
        {
            protected function resolveHost(string $host): array
            {
                return ['10.10.10.10'];
            }
        };

        try {
            $service->fetchMetadata('https://public-looking.example.test/news');
            $this->fail('Hostname yang resolve ke IP private seharusnya ditolak.');
        } catch (ExternalArticleMetadataException $e) {
            $this->assertStringContainsString('internal', $e->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_image_redirect_to_private_ip_and_oversized_image_are_rejected(): void
    {
        Http::fake([
            'https://example.com/private-image' => Http::response('<meta property="og:title" content="News"><meta property="og:image" content="https://example.com/image-redirect">', 200, ['Content-Type' => 'text/html']),
            'https://example.com/image-redirect' => Http::response('', 302, ['Location' => 'http://127.0.0.1/private.png']),
            'https://example.com/large-image' => Http::response('<meta property="og:title" content="News"><meta property="og:image" content="https://example.com/huge.png">', 200, ['Content-Type' => 'text/html']),
            'https://example.com/huge.png' => Http::response('x', 200, ['Content-Type' => 'image/png', 'Content-Length' => '5242881']),
        ]);

        $service = app(ExternalArticleMetadataService::class);
        foreach (['https://example.com/private-image', 'https://example.com/large-image'] as $url) {
            try {
                $service->preview($url);
                $this->fail('Thumbnail tidak aman seharusnya ditolak: '.$url);
            } catch (ExternalArticleMetadataException $e) {
                $this->assertNotSame('', $e->getMessage());
            }
        }
    }

    public function test_timeout_and_too_many_redirects_return_safe_errors(): void
    {
        Http::fake([
            'https://example.com/timeout' => Http::failedConnection('timeout'),
            'https://example.com/loop-1' => Http::response('', 302, ['Location' => '/loop-2']),
            'https://example.com/loop-2' => Http::response('', 302, ['Location' => '/loop-3']),
            'https://example.com/loop-3' => Http::response('', 302, ['Location' => '/loop-4']),
            'https://example.com/loop-4' => Http::response('', 302, ['Location' => '/loop-5']),
        ]);

        $service = app(ExternalArticleMetadataService::class);
        foreach (['https://example.com/timeout', 'https://example.com/loop-1'] as $url) {
            try {
                $service->fetchMetadata($url);
                $this->fail('Kegagalan jaringan seharusnya ditangani: '.$url);
            } catch (ExternalArticleMetadataException $e) {
                $this->assertNotSame('', $e->getMessage());
            }
        }
    }

    public function test_oversized_html_non_image_and_missing_metadata_return_safe_errors(): void
    {
        Http::fake([
            'https://example.com/large' => Http::response('x', 200, ['Content-Type' => 'text/html', 'Content-Length' => '2000001']),
            'https://example.com/missing' => Http::response('<html><head></head></html>', 200, ['Content-Type' => 'text/html']),
            'https://example.com/non-image' => Http::response('<meta property="og:title" content="News"><meta property="og:image" content="https://example.com/not-image">', 200, ['Content-Type' => 'text/html']),
            'https://example.com/not-image' => Http::response('not an image', 200, ['Content-Type' => 'text/plain']),
        ]);

        $service = app(ExternalArticleMetadataService::class);

        foreach (['https://example.com/large', 'https://example.com/missing', 'https://example.com/non-image'] as $url) {
            try {
                $service->preview($url);
                $this->fail('Respons invalid seharusnya ditolak: '.$url);
            } catch (ExternalArticleMetadataException $e) {
                $this->assertNotSame('', $e->getMessage());
            }
        }
    }
}
