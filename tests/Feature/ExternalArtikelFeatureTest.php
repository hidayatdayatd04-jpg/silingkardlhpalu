<?php

namespace Tests\Feature;

use App\Models\Artikel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithAdminNotifications;
use Tests\TestCase;

class ExternalArtikelFeatureTest extends TestCase
{
    use DatabaseTransactions;
    use InteractsWithAdminNotifications;

    private string $png;

    protected function setUp(): void
    {
        parent::setUp();
        $this->png = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2nsgAAAAASUVORK5CYII=');
    }

    public function test_internal_article_create_flow_still_uploads_thumbnail_and_keeps_content_and_comments(): void
    {
        Storage::fake('public');
        $admin = $this->makeUser('admin');

        $response = $this->actingAs($admin)->post(route('admin.resources.store', 'artikel'), [
            'article_type' => 'internal',
            'judul' => 'Artikel Internal Tetap Bekerja',
            'thumbnail' => UploadedFile::fake()->image('internal.jpg', 800, 500),
            'konten' => '<p>Konten artikel internal.</p>',
            'tanggal_publish' => now()->toDateString(),
            'status' => 'published',
            'komentar_enabled' => '1',
        ]);

        $response->assertRedirect();
        $artikel = Artikel::where('judul', 'Artikel Internal Tetap Bekerja')->firstOrFail();
        $this->assertTrue($artikel->isInternal());
        $this->assertTrue($artikel->komentar_enabled);
        $this->assertSame('<p>Konten artikel internal.</p>', $artikel->konten);
        $this->assertNotNull($artikel->thumbnail);
        Storage::disk('public')->assertExists($artikel->thumbnail);
        $this->get('/berita/'.$artikel->slug)->assertOk()->assertSee('Konten artikel internal', false);
    }

    public function test_external_create_saves_metadata_urls_without_uploading_thumbnail(): void
    {
        Storage::fake('public');
        $this->fakeArticleSource('https://example.com/news', 'External News', '/cover.png');
        Cache::put('artikel:beranda', collect(['stale']), 600);

        $response = $this->actingAs($this->makeUser('admin'))->post(route('admin.resources.store', 'artikel'), [
            'article_type' => 'external',
            'external_url' => 'https://example.com/news',
            'tanggal_publish' => now()->toDateString(),
            'status' => 'published',
            'judul' => 'Judul palsu dari browser',
            'komentar_enabled' => '1',
        ]);

        $response->assertRedirect();
        $artikel = Artikel::where('external_url', 'https://example.com/news')->firstOrFail();
        $this->assertSame('External News', $artikel->judul);
        $this->assertSame('external', $artikel->article_type);
        $this->assertSame('https://example.com/news', $artikel->external_url);
        $this->assertSame(now()->toDateString(), $artikel->tanggal_publish->toDateString());
        $this->assertSame('published', $artikel->status->value);
        $this->assertSame('https://example.com/cover.png', $artikel->external_thumbnail_url);
        $this->assertNull($artikel->thumbnail);
        $this->assertNull($artikel->konten);
        $this->assertFalse($artikel->komentar_enabled);
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertFalse(Cache::has('artikel:beranda'));

        $this->get('/berita/'.$artikel->slug)->assertRedirect('https://example.com/news');
        $this->get('/berita')->assertOk()->assertSee('href="https://example.com/news"', false)->assertSee('rel="noopener noreferrer"', false);
        $this->get('/')->assertOk()->assertSee('href="https://example.com/news"', false);
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/berita/'.$artikel->slug, false);
        $this->postJson('/api/berita/'.$artikel->slug.'/komentar', ['body' => 'Tidak boleh'])->assertNotFound();
        $this->actingAs($this->makeUser('admin'))->get(route('admin.artikel.komentar.index', $artikel->id))->assertNotFound();
    }

    public function test_external_edit_without_url_change_does_not_fetch_again(): void
    {
        $artikel = $this->makeExternal();
        Http::fake();

        $this->actingAs($this->makeUser('admin'))->put(route('admin.resources.update', ['artikel', $artikel]), [
            'article_type' => 'external',
            'external_url' => $artikel->external_url,
            'tanggal_publish' => now()->addDay()->toDateString(),
            'status' => 'draft',
        ])->assertRedirect();

        Http::assertNothingSent();
        $artikel->refresh();
        $this->assertSame('https://example.com/old', $artikel->external_url);
        $this->assertSame(now()->addDay()->toDateString(), $artikel->tanggal_publish->toDateString());
        $this->assertSame('draft', $artikel->status->value);
    }

    public function test_external_url_change_refetches_metadata_and_failed_refetch_preserves_old_data(): void
    {
        $artikel = $this->makeExternal();
        $this->fakeArticleSource('https://example.com/new', 'New Title', '/new.png');

        $this->actingAs($this->makeUser('admin'))->put(route('admin.resources.update', ['artikel', $artikel]), [
            'article_type' => 'external', 'external_url' => 'https://example.com/new',
            'tanggal_publish' => now()->toDateString(), 'status' => 'published',
        ])->assertRedirect();

        $artikel->refresh();
        $this->assertSame('New Title', $artikel->judul);
        $this->assertSame('https://example.com/new.png', $artikel->external_thumbnail_url);

        Http::fake(['https://example.com/broken' => Http::response('', 500)]);
        $this->actingAs($this->makeUser('admin'))->from(route('admin.resources.edit', ['artikel', $artikel]))
            ->put(route('admin.resources.update', ['artikel', $artikel]), [
                'article_type' => 'external', 'external_url' => 'https://example.com/broken',
                'tanggal_publish' => now()->toDateString(), 'status' => 'draft',
            ])->assertSessionHasErrors('external_url');

        $artikel->refresh();
        $this->assertSame('https://example.com/new', $artikel->external_url);
        $this->assertSame('https://example.com/new.png', $artikel->external_thumbnail_url);
    }

    public function test_external_admin_views_do_not_expose_internal_fields_or_comment_management(): void
    {
        $artikel = $this->makeExternal();
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get(route('admin.resources.create', 'artikel'))
            ->assertOk()
            ->assertSee('Tulis Artikel')
            ->assertSee('Insert Link');

        $this->actingAs($admin)->get(route('admin.resources.edit', ['artikel', $artikel]))
            ->assertOk()
            ->assertSee('name="external_url"', false)
            ->assertDontSee('id="artikel_judul"', false)
            ->assertDontSee('Izinkan Komentar');

        $this->actingAs($admin)->get(route('admin.resources.index', 'artikel'))
            ->assertOk()
            ->assertSee($artikel->judul)
            ->assertDontSee(route('admin.artikel.komentar.index', $artikel->id), false);

        $this->actingAs($admin)->get(route('admin.resources.show', ['artikel', $artikel]))
            ->assertOk()
            ->assertSee('Sumber Berita Eksternal')
            ->assertSee('href="'.$artikel->external_url.'"', false);
    }

    public function test_metadata_preview_is_admin_only_and_never_writes_to_storage(): void
    {
        Storage::fake('public');
        $this->post(route('admin.artikel.metadata.preview'), ['external_url' => 'https://example.com/news'])
            ->assertRedirect(route('admin.login'));

        $this->fakeArticleSource('https://example.com/news', 'Preview News', '/preview.png');
        $this->actingAs($this->makeUser('admin'))
            ->postJson(route('admin.artikel.metadata.preview'), ['external_url' => 'https://example.com/news'])
            ->assertOk()
            ->assertJson([
                'title' => 'Preview News',
                'image_url' => 'https://example.com/preview.png',
            ]);

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_deleting_external_article_does_not_delete_unrelated_storage_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('admin/artikel/keep.jpg', 'unrelated');
        $artikel = $this->makeExternal();

        $this->actingAs($this->makeUser('admin'))
            ->delete(route('admin.resources.destroy', ['artikel', $artikel]))
            ->assertRedirect(route('admin.resources.index', 'artikel'));

        $this->assertDatabaseMissing('artikel', ['id' => $artikel->id]);
        Storage::disk('public')->assertExists('admin/artikel/keep.jpg');
    }

    private function fakeArticleSource(string $url, string $title, string $image): void
    {
        $imageUrl = str_starts_with($image, 'http') ? $image : 'https://example.com'.$image;
        Http::fake([
            $url => Http::response('<meta property="og:title" content="'.$title.'"><meta property="og:image" content="'.$image.'">', 200, ['Content-Type' => 'text/html']),
            $imageUrl => Http::response($this->png, 200, ['Content-Type' => 'image/png']),
        ]);
    }

    private function makeExternal(): Artikel
    {
        return Artikel::create([
            'article_type' => 'external',
            'external_url' => 'https://example.com/old',
            'external_thumbnail_url' => 'https://example.com/old.png',
            'judul' => 'Old Title',
            'konten' => null,
            'tanggal_publish' => now()->toDateString(),
            'status' => 'published',
            'komentar_enabled' => false,
        ]);
    }
}
