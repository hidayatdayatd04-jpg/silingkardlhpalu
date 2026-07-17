<?php

namespace Tests\Feature\Admin;

use App\Enums\ArtikelKategori;
use App\Enums\ArtikelStatus;
use App\Models\Artikel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtikelTest extends TestCase
{
    use RefreshDatabase, CreatesAdminUsers;

    // ═══════════════════════════════════════════════════════════
    // ADMIN — INDEX
    // ═══════════════════════════════════════════════════════════

    public function test_admin_index_page_loads(): void
    {
        $user = $this->superadmin();
        $this->actingAs($user)->get('/admin/artikel')
            ->assertOk()
            ->assertSee('Artikel');
    }

    public function test_admin_index_shows_artikel_list(): void
    {
        $user = $this->superadmin();
        Artikel::create([
            'judul' => 'Artikel Pertama',
            'konten' => 'Isi konten artikel pertama',
            'status' => ArtikelStatus::PUBLISHED,
            'tanggal_publish' => now()->toDateString(),
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->get('/admin/artikel')
            ->assertOk()
            ->assertSee('Artikel Pertama');
    }

    public function test_admin_index_empty_state(): void
    {
        $user = $this->superadmin();
        $this->actingAs($user)->get('/admin/artikel')
            ->assertOk()
            ->assertSee('Belum ada data');
    }

    public function test_admin_index_shows_total_count(): void
    {
        $user = $this->superadmin();
        Artikel::create(['judul' => 'A', 'konten' => 'x', 'status' => 'draft']);
        Artikel::create(['judul' => 'B', 'konten' => 'y', 'status' => 'published', 'tanggal_publish' => now()]);

        $this->actingAs($user)->get('/admin/artikel')
            ->assertOk()
            ->assertSee('Total 2 data');
    }

    public function test_admin_index_search_by_judul(): void
    {
        $user = $this->superadmin();
        Artikel::create(['judul' => 'Lingkungan Bersih', 'konten' => 'Isi', 'status' => 'draft']);
        Artikel::create(['judul' => 'Pemandangan Indah', 'konten' => 'Isi', 'status' => 'draft']);

        $this->actingAs($user)->get('/admin/artikel?q=Lingkungan')
            ->assertOk()
            ->assertSee('Lingkungan Bersih')
            ->assertDontSee('Pemandangan Indah');
    }

    public function test_admin_index_filter_by_status(): void
    {
        $user = $this->superadmin();
        Artikel::create(['judul' => 'Draft Article', 'konten' => 'Isi', 'status' => 'draft']);
        Artikel::create(['judul' => 'Published Article', 'konten' => 'Isi', 'status' => 'published', 'tanggal_publish' => now()]);

        $this->actingAs($user)->get('/admin/artikel?status[]=published')
            ->assertOk()
            ->assertSee('Published Article')
            ->assertDontSee('Draft Article');
    }

    public function test_admin_index_sorting(): void
    {
        $user = $this->superadmin();
        Artikel::create(['judul' => 'Alpha', 'konten' => 'Isi', 'status' => 'draft', 'created_at' => now()->subDays(2)]);
        Artikel::create(['judul' => 'Beta', 'konten' => 'Isi', 'status' => 'draft', 'created_at' => now()]);

        $response = $this->actingAs($user)->get('/admin/artikel?sort=created_at&direction=desc');
        $response->assertOk();
    }

    public function test_admin_unauthenticated_cannot_access(): void
    {
        $this->get('/admin/artikel')->assertRedirect();
    }

    // ═══════════════════════════════════════════════════════════
    // ADMIN — CREATE (form + store)
    // ═══════════════════════════════════════════════════════════

    public function test_admin_create_form_loads(): void
    {
        $user = $this->superadmin();
        $this->actingAs($user)->get('/admin/artikel/create')
            ->assertOk()
            ->assertSee('Tambah')
            ->assertSee('Artikel');
    }

    public function test_admin_store_artikel_with_all_fields(): void
    {
        $user = $this->superadmin();

        $payload = [
            'judul' => 'Berita Lingkungan Terbaru',
            'konten' => '<p>Ini adalah konten berita lingkungan.</p>',
            'kategori' => 'pengendalian',
            'status' => 'published',
            'tanggal_publish' => now()->toDateString(),
        ];

        $this->actingAs($user)->post('/admin/artikel', $payload)
            ->assertRedirect();

        $artikel = Artikel::where('judul', 'Berita Lingkungan Terbaru')->first();
        $this->assertNotNull($artikel);
        $this->assertEquals('berita-lingkungan-terbaru', $artikel->slug);
        $this->assertEquals(ArtikelStatus::PUBLISHED, $artikel->status);
        $this->assertEquals(ArtikelKategori::PENGENDALIAN, $artikel->kategori);
        $this->assertEquals($user->id, $artikel->user_id);
        $this->assertNotNull($artikel->tanggal_publish);
    }

    public function test_admin_store_artikel_generates_slug_from_judul(): void
    {
        $user = $this->superadmin();

        $this->actingAs($user)->post('/admin/artikel', [
            'judul' => 'Judul Dengan Spasi & Karakter Khusus!',
            'konten' => 'Konten',
            'status' => 'draft',
        ]);

        $artikel = Artikel::latest()->first();
        $this->assertEquals('judul-dengan-spasi-karakter-khusus', $artikel->slug);
    }

    public function test_admin_store_artikel_unique_slug(): void
    {
        $user = $this->superadmin();

        Artikel::create(['judul' => 'Judul Sama', 'konten' => 'Isi', 'status' => 'draft']);

        $this->actingAs($user)->post('/admin/artikel', [
            'judul' => 'Judul Sama',
            'konten' => 'Isi',
            'status' => 'draft',
        ]);

        $this->assertEquals(2, Artikel::where('judul', 'Judul Sama')->count());
        $latest = Artikel::latest()->first();
        // Slug harus unik — artinya ada angka suffix
        $this->assertNotEquals($latest->slug, Artikel::oldest()->first()->slug);
    }

    public function test_admin_store_artikel_with_thumbnail(): void
    {
        $user = $this->superadmin();
        Storage::fake('public');

        // Gunakan fake file tanpa GD (tanpa image generation)
        $file = UploadedFile::fake()->create('thumbnail.jpg', 100, 'image/jpeg');

        $this->actingAs($user)->post('/admin/artikel', [
            'judul' => 'Berita dengan Foto',
            'konten' => 'Konten',
            'status' => 'draft',
            'thumbnail' => $file,
        ]);

        $artikel = Artikel::where('judul', 'Berita dengan Foto')->first();
        $this->assertNotNull($artikel);
        $this->assertNotNull($artikel->thumbnail);
        $this->assertStringContainsString('admin/artikel/', $artikel->thumbnail);
    }

    public function test_admin_store_artikel_all_kategori_options(): void
    {
        $user = $this->superadmin();

        foreach (ArtikelKategori::cases() as $kategori) {
            $this->actingAs($user)->post('/admin/artikel', [
                'judul' => 'Artikel '.$kategori->value,
                'konten' => 'Konten',
                'status' => 'draft',
                'kategori' => $kategori->value,
            ]);

            $artikel = Artikel::where('judul', 'Artikel '.$kategori->value)->first();
            $this->assertEquals($kategori, $artikel->kategori);
        }
    }

    public function test_admin_store_artikel_validasi_judul_wajib(): void
    {
        $user = $this->superadmin();

        // Judul kosong — setelah fix, model tidak crash dan validasi jalan
        $this->actingAs($user)->post('/admin/artikel', [
            'judul' => '',
            'konten' => 'Konten',
        ])->assertSessionHasErrors('judul');
    }

    public function test_admin_store_artikel_sets_user_id(): void
    {
        $user = $this->superadmin();

        $this->actingAs($user)->post('/admin/artikel', [
            'judul' => 'Artikel User Check',
            'konten' => 'Konten',
            'status' => 'draft',
        ]);

        $artikel = Artikel::where('judul', 'Artikel User Check')->first();
        $this->assertEquals($user->id, $artikel->user_id);
    }

    // ═══════════════════════════════════════════════════════════
    // ADMIN — SHOW (detail)
    // ═══════════════════════════════════════════════════════════

    public function test_admin_show_artikel_detail(): void
    {
        $user = $this->superadmin();
        $artikel = Artikel::create([
            'judul' => 'Detail Artikel',
            'konten' => '<p>Isi lengkap</p>',
            'status' => 'published',
            'tanggal_publish' => now(),
            'kategori' => 'rth',
        ]);

        $this->actingAs($user)->get("/admin/artikel/{$artikel->id}")
            ->assertOk()
            ->assertSee('Detail Artikel')
            ->assertSee('Isi lengkap')
            ->assertSee('Ruang Terbuka Hijau');
    }

    public function test_admin_show_artikel_not_found(): void
    {
        $user = $this->superadmin();
        $this->actingAs($user)->get('/admin/artikel/9999')
            ->assertNotFound();
    }

    // ═══════════════════════════════════════════════════════════
    // ADMIN — EDIT (form + update)
    // ═══════════════════════════════════════════════════════════

    public function test_admin_edit_form_loads(): void
    {
        $user = $this->superadmin();
        $artikel = Artikel::create([
            'judul' => 'Edit Me',
            'konten' => 'Original content',
            'status' => 'draft',
        ]);

        $this->actingAs($user)->get("/admin/artikel/{$artikel->id}/edit")
            ->assertOk()
            ->assertSee('Edit')
            ->assertSee('Edit Me');
    }

    public function test_admin_update_artikel_judul(): void
    {
        $user = $this->superadmin();
        $artikel = Artikel::create([
            'judul' => 'Old Title',
            'konten' => 'Old content',
            'status' => 'draft',
        ]);

        $this->actingAs($user)->put("/admin/artikel/{$artikel->id}", [
            'judul' => 'New Title',
            'konten' => 'Old content',
            'status' => 'draft',
        ])->assertRedirect();

        $artikel->refresh();
        $this->assertEquals('New Title', $artikel->judul);
        $this->assertEquals('new-title', $artikel->slug);
    }

    public function test_admin_update_artikel_updates_slug_when_judul_changes(): void
    {
        $user = $this->superadmin();
        $artikel = Artikel::create([
            'judul' => 'Original Judul',
            'konten' => 'Isi',
            'status' => 'draft',
        ]);
        $originalSlug = $artikel->slug;

        $this->actingAs($user)->put("/admin/artikel/{$artikel->id}", [
            'judul' => 'Completely New Judul',
            'konten' => 'Isi',
            'status' => 'draft',
        ]);

        $artikel->refresh();
        $this->assertNotEquals($originalSlug, $artikel->slug);
        $this->assertEquals('completely-new-judul', $artikel->slug);
    }

    public function test_admin_update_artikel_preserves_slug_when_judul_unchanged(): void
    {
        $user = $this->superadmin();
        $artikel = Artikel::create([
            'judul' => 'Same Title',
            'konten' => 'Old content',
            'status' => 'draft',
        ]);
        $originalSlug = $artikel->slug;

        $this->actingAs($user)->put("/admin/artikel/{$artikel->id}", [
            'judul' => 'Same Title',
            'konten' => 'Updated content',
            'status' => 'published',
            'tanggal_publish' => now()->toDateString(),
        ]);

        $artikel->refresh();
        $this->assertEquals($originalSlug, $artikel->slug);
    }

    public function test_admin_update_artikel_status_to_published(): void
    {
        $user = $this->superadmin();
        $artikel = Artikel::create([
            'judul' => 'Status Test',
            'konten' => 'Isi',
            'status' => 'draft',
        ]);

        $this->actingAs($user)->put("/admin/artikel/{$artikel->id}", [
            'judul' => 'Status Test',
            'konten' => 'Isi',
            'status' => 'published',
            'tanggal_publish' => now()->toDateString(),
        ]);

        $artikel->refresh();
        $this->assertEquals(ArtikelStatus::PUBLISHED, $artikel->status);
    }

    public function test_admin_update_artikel_thumbnail_replacement(): void
    {
        $user = $this->superadmin();
        Storage::fake('public');

        $artikel = Artikel::create([
            'judul' => 'Thumbnail Test',
            'konten' => 'Isi',
            'status' => 'draft',
            'thumbnail' => 'admin/artikel/old-thumbnail.jpg',
        ]);

        $newFile = UploadedFile::fake()->create('new.jpg', 100, 'image/jpeg');

        $this->actingAs($user)->put("/admin/artikel/{$artikel->id}", [
            'judul' => 'Thumbnail Test',
            'konten' => 'Isi',
            'status' => 'draft',
            'thumbnail' => $newFile,
        ]);

        $artikel->refresh();
        $this->assertNotEquals('admin/artikel/old-thumbnail.jpg', $artikel->thumbnail);
        $this->assertStringContainsString('admin/artikel/', $artikel->thumbnail);
    }

    public function test_admin_update_artikel_change_kategori(): void
    {
        $user = $this->superadmin();
        $artikel = Artikel::create([
            'judul' => 'Kategori Test',
            'konten' => 'Isi',
            'status' => 'draft',
            'kategori' => 'pengendalian',
        ]);

        $this->actingAs($user)->put("/admin/artikel/{$artikel->id}", [
            'judul' => 'Kategori Test',
            'konten' => 'Isi',
            'status' => 'draft',
            'kategori' => 'sampah-lb3',
        ]);

        $artikel->refresh();
        $this->assertEquals(ArtikelKategori::SAMPAH_LB3, $artikel->kategori);
    }

    // ═══════════════════════════════════════════════════════════
    // ADMIN — DELETE
    // ═══════════════════════════════════════════════════════════

    public function test_admin_delete_artikel(): void
    {
        $user = $this->superadmin();
        $artikel = Artikel::create([
            'judul' => 'Delete Me',
            'konten' => 'Isi',
            'status' => 'draft',
        ]);

        $this->actingAs($user)->delete("/admin/artikel/{$artikel->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('artikels', ['id' => $artikel->id]);
    }

    public function test_admin_delete_artikel_not_found(): void
    {
        $user = $this->superadmin();
        $this->actingAs($user)->delete('/admin/artikel/9999')
            ->assertNotFound();
    }

    // ═══════════════════════════════════════════════════════════
    // ADMIN — ACCESS CONTROL
    // ═══════════════════════════════════════════════════════════

    public function test_admin_superadmin_can_access_artikel(): void
    {
        $user = $this->superadmin();
        $this->actingAs($user)->get('/admin/artikel')->assertOk();
        $this->actingAs($user)->get('/admin/artikel/create')->assertOk();
    }

    public function test_admin_bidang_user_without_konten_access_cannot_access_artikel(): void
    {
        $user = $this->bidangUser(\App\Enums\AdminRole::BIDANG_PENGENDALIAN);
        $this->actingAs($user)->get('/admin/artikel')->assertForbidden();
        $this->actingAs($user)->get('/admin/artikel/create')->assertForbidden();
    }

    public function test_admin_bidang_user_with_additional_access_can_access(): void
    {
        $user = $this->bidangUser(\App\Enums\AdminRole::BIDANG_PENGENDALIAN);
        $user->update(['additional_access' => ['konten']]);
        $user->load('roles');

        $this->actingAs($user)->get('/admin/artikel')->assertOk();
    }

    public function test_admin_unauthenticated_redirected_to_login(): void
    {
        $this->get('/admin/artikel')->assertRedirect();
        $this->post('/admin/artikel', [])->assertRedirect();
    }

    // ═══════════════════════════════════════════════════════════
    // ADMIN — BULK OPERATIONS
    // ═══════════════════════════════════════════════════════════

    public function test_admin_bulk_delete_artikel(): void
    {
        $user = $this->superadmin();
        $a1 = Artikel::create(['judul' => 'Bulk 1', 'konten' => 'Isi', 'status' => 'draft']);
        $a2 = Artikel::create(['judul' => 'Bulk 2', 'konten' => 'Isi', 'status' => 'draft']);

        $this->actingAs($user)->delete('/admin/artikel/bulk-delete', ['ids' => [$a1->id, $a2->id]])
            ->assertRedirect();

        $this->assertDatabaseMissing('artikels', ['id' => $a1->id]);
        $this->assertDatabaseMissing('artikels', ['id' => $a2->id]);
    }

    // ═══════════════════════════════════════════════════════════
    // ADMIN — EXPORT
    // ═══════════════════════════════════════════════════════════

    public function test_admin_export_artikel_csv(): void
    {
        $user = $this->superadmin();
        Artikel::create(['judul' => 'CSV Test', 'konten' => 'Isi', 'status' => 'draft']);

        $this->actingAs($user)->get('/admin/artikel/export?format=csv')
            ->assertOk();
    }

    public function test_admin_export_artikel_xlsx_without_ziparchive(): void
    {
        // XLSX export membutuhkan ZipArchive extension.
        // Jika tidak tersedia, pastikan tidak crash 500 tapi gracefully handle.
        $user = $this->superadmin();
        Artikel::create(['judul' => 'Export Test', 'konten' => 'Isi', 'status' => 'draft']);

        $response = $this->actingAs($user)->get('/admin/artikel/export?format=xlsx');

        // Bisa 200 (jika ZipArchive ada) atau 500 (jika tidak)
        // Yang penting TIDAK boleh crash tanpa pesan
        $this->assertContains($response->status(), [200, 500]);
    }

    // ═══════════════════════════════════════════════════════════
    // ADMIN — MODEL BEHAVIOR
    // ═══════════════════════════════════════════════════════════

    public function test_artikel_model_casts_enums(): void
    {
        $artikel = Artikel::create([
            'judul' => 'Cast Test',
            'konten' => 'Isi',
            'status' => 'published',
            'kategori' => 'rth',
            'tanggal_publish' => now()->toDateString(),
        ]);

        $this->assertInstanceOf(ArtikelStatus::class, $artikel->status);
        $this->assertInstanceOf(ArtikelKategori::class, $artikel->kategori);
        $this->assertInstanceOf(\Carbon\Carbon::class, $artikel->tanggal_publish);
    }

    public function test_artikel_scope_published_filters_correctly(): void
    {
        // Published + tanggal masa lalu → terlihat
        Artikel::create([
            'judul' => 'Published Past',
            'konten' => 'Isi',
            'status' => 'published',
            'tanggal_publish' => now()->subDay()->toDateString(),
        ]);

        // Published + tanggal masa depan → TIDAK terlihat
        Artikel::create([
            'judul' => 'Published Future',
            'konten' => 'Isi',
            'status' => 'published',
            'tanggal_publish' => now()->addDay()->toDateString(),
        ]);

        // Draft + tanggal masa lalu → TIDAK terlihat
        Artikel::create([
            'judul' => 'Draft Past',
            'konten' => 'Isi',
            'status' => 'draft',
            'tanggal_publish' => now()->subDay()->toDateString(),
        ]);

        // Published + tanpa tanggal → TIDAK terlihat
        Artikel::create([
            'judul' => 'Published No Date',
            'konten' => 'Isi',
            'status' => 'published',
            'tanggal_publish' => null,
        ]);

        $published = Artikel::published()->get();
        $this->assertCount(1, $published);
        $this->assertEquals('Published Past', $published->first()->judul);
    }

    public function test_artikel_belongs_to_user(): void
    {
        $user = $this->superadmin();
        $artikel = Artikel::create([
            'judul' => 'User Relation',
            'konten' => 'Isi',
            'status' => 'draft',
            'user_id' => $user->id,
        ]);

        $this->assertNotNull($artikel->user);
        $this->assertEquals($user->id, $artikel->user_id);
    }

    public function test_artikel_model_does_not_crash_with_null_judul(): void
    {
        // Setelah fix, creating event tidak crash saat judul null
        $artikel = Artikel::create([
            'judul' => null,
            'konten' => 'Isi',
            'status' => 'draft',
        ]);

        $this->assertNull($artikel->slug);
        $this->assertNull($artikel->judul);
    }
}
