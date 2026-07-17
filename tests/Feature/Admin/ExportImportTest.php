<?php

namespace Tests\Feature\Admin;

use App\Enums\AdminRole;
use App\Imports\ResourceImport;
use App\Models\Artikel;
use App\Support\Admin\AdminRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportImportTest extends TestCase
{
    use RefreshDatabase, CreatesAdminUsers;

    public function test_export_xlsx_dapat_diunduh(): void
    {
        $user = $this->superadmin();
        Artikel::create(['judul' => 'A', 'konten' => 'x', 'kategori' => 'umum', 'status' => 'draft']);

        $response = $this->actingAs($user)->get('/admin/artikel/export?format=xlsx');
        $response->assertOk();
        $this->assertStringContainsString('spreadsheet', $response->headers->get('content-type'));
    }

    public function test_export_pdf_valid_header(): void
    {
        $user = $this->superadmin();
        Artikel::create(['judul' => 'A', 'konten' => 'x', 'kategori' => 'umum', 'status' => 'draft']);

        $response = $this->actingAs($user)->get('/admin/artikel/export?format=pdf');
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->streamedContent());
    }

    public function test_export_csv_berisi_header_kolom(): void
    {
        $user = $this->superadmin();
        Artikel::create(['judul' => 'Judul CSV', 'konten' => 'x', 'kategori' => 'umum', 'status' => 'draft']);

        $response = $this->actingAs($user)->get('/admin/artikel/export?format=csv');
        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('Judul', $content);
        $this->assertStringContainsString('Judul CSV', $content);
    }

    public function test_import_template_dapat_diunduh(): void
    {
        $user = $this->superadmin();
        $response = $this->actingAs($user)->get('/admin/artikel/import-template');
        $response->assertOk();
    }

    public function test_round_trip_export_import_artikel(): void
    {
        $user = $this->superadmin();
        $this->actingAs($user);

        Artikel::create(['judul' => 'Artikel Satu', 'konten' => 'Isi satu', 'kategori' => 'umum', 'status' => 'draft']);
        Artikel::create(['judul' => 'Artikel Dua', 'konten' => 'Isi dua', 'kategori' => 'rth', 'status' => 'published', 'tanggal_publish' => now()]);

        $meta = AdminRegistry::find('artikel');

        // Export ke file sementara pakai DataIO
        $path = storage_path('app/private/test-artikel.xlsx');
        app(\App\Support\DataIO::class)->writeXlsx(Artikel::query(), $meta['columns'], $path);

        $before = Artikel::count();
        $this->assertSame(2, $before);

        // Import balik file yang sama
        $import = new ResourceImport($meta);
        $rows = \App\Support\DataIO::readFile($path);
        $import->collection($rows);

        // Judul (kolom fillable) harus terimpor kembali → total bertambah
        $this->assertGreaterThanOrEqual(2, $import->imported);
        $this->assertTrue(Artikel::where('judul', 'Artikel Satu')->count() >= 2);

        @unlink($path);
    }

    public function test_import_enum_label_dibalik_ke_value(): void
    {
        $user = $this->superadmin();
        $this->actingAs($user);

        $meta = AdminRegistry::find('artikel');
        $import = new ResourceImport($meta);

        // Simulasi baris dengan label enum "Published" & kategori label
        $rows = collect([
            collect(['judul' => 'Dari Import', 'konten' => 'isi', 'kategori' => 'Umum', 'status' => 'Published']),
        ]);
        $import->collection($rows);

        $artikel = Artikel::where('judul', 'Dari Import')->first();
        $this->assertNotNull($artikel);
        $this->assertSame(\App\Enums\ArtikelStatus::PUBLISHED, $artikel->status);
        $this->assertSame(\App\Enums\ArtikelKategori::UMUM, $artikel->kategori);
    }
}
