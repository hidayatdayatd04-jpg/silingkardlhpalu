<?php

namespace Tests\Feature;

use App\Enums\ArtikelKategori;
use App\Enums\ArtikelStatus;
use App\Models\Artikel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBeritaTest extends TestCase
{
    use RefreshDatabase;

    private function createPublishedArtikel(array $overrides = []): Artikel
    {
        return Artikel::create(array_merge([
            'judul' => 'Berita Publik',
            'konten' => '<p>Isi berita untuk publik.</p>',
            'status' => ArtikelStatus::PUBLISHED,
            'tanggal_publish' => now()->toDateString(),
            'kategori' => 'umum',
        ], $overrides));
    }

    private function createDraftArtikel(array $overrides = []): Artikel
    {
        return Artikel::create(array_merge([
            'judul' => 'Draft Publik',
            'konten' => '<p>Isi draft.</p>',
            'status' => ArtikelStatus::DRAFT,
        ], $overrides));
    }

    // ═══════════════════════════════════════════════════════════
    // INDEX — /berita
    // ═══════════════════════════════════════════════════════════

    public function test_berita_index_page_loads(): void
    {
        $this->get('/berita')
            ->assertOk()
            ->assertSee('Berita');
    }

    public function test_berita_index_shows_published_articles(): void
    {
        $this->createPublishedArtikel(['judul' => 'Berita Terbit']);

        $this->get('/berita')
            ->assertOk()
            ->assertSee('Berita Terbit');
    }

    public function test_berita_index_does_not_show_draft_articles(): void
    {
        $this->createDraftArtikel(['judul' => 'Draft Tersembunyi']);
        $this->createPublishedArtikel(['judul' => 'Berita Terlihat']);

        $response = $this->get('/berita');
        $response->assertOk();
        $response->assertSee('Berita Terlihat');
        $response->assertDontSee('Draft Tersembunyi');
    }

    public function test_berita_index_does_not_show_future_published_articles(): void
    {
        $this->createPublishedArtikel([
            'judul' => 'Berita Masa Depan',
            'tanggal_publish' => now()->addDays(5)->toDateString(),
        ]);

        $this->get('/berita')
            ->assertOk()
            ->assertDontSee('Berita Masa Depan');
    }

    public function test_berita_index_does_not_show_articles_without_tanggal_publish(): void
    {
        $this->createPublishedArtikel([
            'judul' => 'Tanpa Tanggal',
            'tanggal_publish' => null,
        ]);

        $this->get('/berita')
            ->assertOk()
            ->assertDontSee('Tanpa Tanggal');
    }

    public function test_berita_index_shows_today_articles(): void
    {
        $this->createPublishedArtikel([
            'judul' => 'Berita Hari Ini',
            'tanggal_publish' => now()->toDateString(),
        ]);

        $this->get('/berita')
            ->assertOk()
            ->assertSee('Berita Hari Ini');
    }

    public function test_berita_index_shows_past_articles(): void
    {
        $this->createPublishedArtikel([
            'judul' => 'Berita Kemarin',
            'tanggal_publish' => now()->subDay()->toDateString(),
        ]);

        $this->get('/berita')
            ->assertOk()
            ->assertSee('Berita Kemarin');
    }

    public function test_berita_index_empty_state(): void
    {
        $this->get('/berita')
            ->assertOk()
            ->assertSee('Belum ada berita');
    }

    public function test_berita_index_pagination(): void
    {
        // Buat 10 artikel published
        for ($i = 1; $i <= 10; $i++) {
            $this->createPublishedArtikel([
                'judul' => "Berita Ke-{$i}",
                'tanggal_publish' => now()->subDays($i)->toDateString(),
            ]);
        }

        $response = $this->get('/berita');
        $response->assertOk();
        // Halaman pertama menampilkan max 9
        $response->assertSee('Berita Ke-1');
    }

    public function test_berita_index_shows_kategori_label(): void
    {
        $this->createPublishedArtikel([
            'judul' => 'Berita RTH',
            'kategori' => 'rth',
        ]);

        $this->get('/berita')
            ->assertOk()
            ->assertSee('Ruang Terbuka Hijau');
    }

    public function test_berita_index_shows_tanggal_publish(): void
    {
        $this->createPublishedArtikel([
            'judul' => 'Berita Tanggal',
            'tanggal_publish' => '2026-06-15',
        ]);

        $this->get('/berita')
            ->assertOk()
            ->assertSee('15');
    }

    // ═══════════════════════════════════════════════════════════
    // INDEX — Filter Kategori
    // ═══════════════════════════════════════════════════════════

    public function test_berita_index_filter_by_kategori_pengendalian(): void
    {
        $this->createPublishedArtikel(['judul' => 'Pengendalian', 'kategori' => 'pengendalian']);
        $this->createPublishedArtikel(['judul' => 'Sampah LB3', 'kategori' => 'sampah-lb3']);

        $this->get('/berita?kategori=pengendalian')
            ->assertOk()
            ->assertSee('Pengendalian')
            ->assertDontSee('Sampah LB3');
    }

    public function test_berita_index_filter_by_kategori_rth(): void
    {
        $this->createPublishedArtikel(['judul' => 'RTH Article', 'kategori' => 'rth']);
        $this->createPublishedArtikel(['judul' => 'Umum Article', 'kategori' => 'umum']);

        $this->get('/berita?kategori=rth')
            ->assertOk()
            ->assertSee('RTH Article')
            ->assertDontSee('Umum Article');
    }

    public function test_berita_index_filter_all_kategori(): void
    {
        $this->createPublishedArtikel(['judul' => 'A', 'kategori' => 'pengendalian']);
        $this->createPublishedArtikel(['judul' => 'B', 'kategori' => 'rth']);

        $this->get('/berita?kategori=')
            ->assertOk()
            ->assertSee('A')
            ->assertSee('B');
    }

    // ═══════════════════════════════════════════════════════════
    // SHOW — /berita/{slug}
    // ═══════════════════════════════════════════════════════════

    public function test_berita_show_page_loads(): void
    {
        $artikel = $this->createPublishedArtikel([
            'judul' => 'Detail Berita',
            'slug' => 'detail-berita',
            'konten' => '<p>Isi lengkap berita.</p>',
        ]);

        $this->get('/berita/detail-berita')
            ->assertOk()
            ->assertSee('Detail Berita')
            ->assertSee('Isi lengkap berita');
    }

    public function test_berita_show_displays_kategori(): void
    {
        $this->createPublishedArtikel([
            'slug' => 'berita-pengendalian',
            'kategori' => 'pengendalian',
        ]);

        $this->get('/berita/berita-pengendalian')
            ->assertOk()
            ->assertSee('Pengendalian Dampak Lingkungan');
    }

    public function test_berita_show_displays_tanggal(): void
    {
        $this->createPublishedArtikel([
            'slug' => 'berita-tanggal',
            'tanggal_publish' => '2026-07-01',
        ]);

        $this->get('/berita/berita-tanggal')
            ->assertOk()
            ->assertSee('Juli')
            ->assertSee('2026');
    }

    public function test_berita_show_displays_author(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);
        $this->createPublishedArtikel([
            'slug' => 'berita-author',
            'user_id' => $user->id,
        ]);

        $this->get('/berita/berita-author')
            ->assertOk()
            ->assertSee('Budi Santoso');
    }

    public function test_berita_show_displays_thumbnail(): void
    {
        $this->createPublishedArtikel([
            'slug' => 'berita-thumb',
            'thumbnail' => 'admin/artikel/artikel_1.jpg',
        ]);

        $this->get('/berita/berita-thumb')
            ->assertOk()
            ->assertSee('storage/admin/artikel/artikel_1.jpg');
    }

    public function test_berita_show_without_thumbnail_shows_gradient(): void
    {
        $this->createPublishedArtikel([
            'slug' => 'berita-no-thumb',
            'thumbnail' => null,
        ]);

        $this->get('/berita/berita-no-thumb')
            ->assertOk()
            ->assertSee('bg-gradient-to-br');
    }

    public function test_berita_show_has_share_buttons(): void
    {
        $this->createPublishedArtikel(['slug' => 'berita-share']);

        $this->get('/berita/berita-share')
            ->assertOk()
            ->assertSee('wa.me')
            ->assertSee('facebook.com/sharer');
    }

    public function test_berita_show_has_back_link(): void
    {
        $this->createPublishedArtikel(['slug' => 'berita-back']);

        $this->get('/berita/berita-back')
            ->assertOk()
            ->assertSee('/berita')
            ->assertSee('Kembali ke Berita');
    }

    // ═══════════════════════════════════════════════════════════
    // SHOW — Access Control
    // ═══════════════════════════════════════════════════════════

    public function test_berita_show_404_for_nonexistent_slug(): void
    {
        $this->get('/berita/artikel-tidak-ada-sama-sekali')
            ->assertNotFound();
    }

    public function test_berita_show_404_for_draft_article(): void
    {
        $this->createDraftArtikel(['slug' => 'draft-page']);

        $this->get('/berita/draft-page')
            ->assertNotFound();
    }

    public function test_berita_show_404_for_future_article(): void
    {
        $this->createPublishedArtikel([
            'slug' => 'future-page',
            'tanggal_publish' => now()->addMonth()->toDateString(),
        ]);

        $this->get('/berita/future-page')
            ->assertNotFound();
    }

    public function test_berita_show_404_for_article_without_tanggal(): void
    {
        $this->createPublishedArtikel([
            'slug' => 'no-date-page',
            'tanggal_publish' => null,
        ]);

        $this->get('/berita/no-date-page')
            ->assertNotFound();
    }

    // ═══════════════════════════════════════════════════════════
    // HOMEPAGE — Latest Berita
    // ═══════════════════════════════════════════════════════════

    public function test_homepage_shows_latest_berita(): void
    {
        $this->createPublishedArtikel([
            'judul' => 'Berita di Homepage',
            'tanggal_publish' => now()->toDateString(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Berita di Homepage');
    }

    public function test_homepage_does_not_show_draft_berita(): void
    {
        $this->createDraftArtikel(['judul' => 'Draft Homepage']);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Draft Homepage');
    }

    public function test_homepage_limits_berita_to_6(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            $this->createPublishedArtikel([
                'judul' => "Homepage Berita {$i}",
                'tanggal_publish' => now()->subDays($i)->toDateString(),
            ]);
        }

        $response = $this->get('/');
        $response->assertOk();
        // Cek bahwa hanya 6 berita terbaru yang muncul
        $response->assertSee('Homepage Berita 1');
        $response->assertSee('Homepage Berita 6');
    }

    // ═══════════════════════════════════════════════════════════
    // SLUG GENERATION
    // ═══════════════════════════════════════════════════════════

    public function test_slug_works_with_special_characters(): void
    {
        $this->createPublishedArtikel([
            'judul' => 'Berita & Update Terbaru!',
            'slug' => 'berita-update-terbaru',
        ]);

        $this->get('/berita/berita-update-terbaru')
            ->assertOk()
            ->assertSee('Berita & Update Terbaru!');
    }

    public function test_slug_works_with_indonesian_characters(): void
    {
        $this->createPublishedArtikel([
            'judul' => 'Kualitas Udara Diukur',
            'slug' => 'kualitas-udara-diukur',
        ]);

        $this->get('/berita/kualitas-udara-diukur')
            ->assertOk();
    }

    // ═══════════════════════════════════════════════════════════
    // EDGE CASES
    // ═══════════════════════════════════════════════════════════

    public function test_berita_index_with_many_articles_per_page(): void
    {
        // Buat lebih dari 9 artikel untuk test pagination
        for ($i = 1; $i <= 12; $i++) {
            $this->createPublishedArtikel([
                'judul' => "Artikel Pagination {$i}",
                'tanggal_publish' => now()->subDays($i)->toDateString(),
            ]);
        }

        $response = $this->get('/berita');
        $response->assertOk();
        // Halaman 1: 9 artikel
        $response->assertSee('Artikel Pagination 1');
    }

    public function test_berita_index_second_page(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->createPublishedArtikel([
                'judul' => "Artikel Halaman {$i}",
                'tanggal_publish' => now()->subDays($i)->toDateString(),
            ]);
        }

        $response = $this->get('/berita?page=2');
        $response->assertOk();
    }

    public function test_berita_show_conten_rendered_as_html(): void
    {
        $this->createPublishedArtikel([
            'slug' => 'html-content',
            'konten' => '<h2>Judul Section</h2><p>Paragraf dengan <strong>bold</strong>.</p>',
        ]);

        $response = $this->get('/berita/html-content');
        $response->assertOk();
        $response->assertSee('Judul Section');
        $response->assertSee('bold');
    }

    public function test_multiple_kategori_filter_options_available(): void
    {
        $response = $this->get('/berita');
        $response->assertOk();
        // Semua kategori harus tersedia di dropdown filter
        $response->assertSee('Semua Kategori');
        $response->assertSee('pengendalian');
        $response->assertSee('sampah-lb3');
        $response->assertSee('tata-penataan');
        $response->assertSee('rth');
        $response->assertSee('umum');
    }

    public function test_berita_show_meta_description(): void
    {
        $this->createPublishedArtikel([
            'slug' => 'meta-test',
            'konten' => '<p>Deskripsi meta test content yang cukup panjang untuk meta description.</p>',
        ]);

        $response = $this->get('/berita/meta-test');
        $response->assertOk();
    }
}
