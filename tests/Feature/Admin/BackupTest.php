<?php

namespace Tests\Feature\Admin;

use App\Enums\AdminRole;
use App\Models\Artikel;
use App\Support\DatabaseBackup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase, CreatesAdminUsers;

    public function test_dump_menghasilkan_file_sql(): void
    {
        Storage::fake('local');
        Artikel::create(['judul' => 'Backup Uji', 'konten' => 'x', 'kategori' => 'umum', 'status' => 'draft']);

        $path = app(DatabaseBackup::class)->dump();

        Storage::disk('local')->assertExists($path);
        $content = Storage::disk('local')->get($path);
        $this->assertStringContainsString('artikels', $content);
        $this->assertStringContainsString('Backup Uji', $content);
    }

    public function test_round_trip_backup_restore_memulihkan_data(): void
    {
        $service = app(DatabaseBackup::class);

        Artikel::create(['judul' => 'Sebelum Restore', 'konten' => 'x', 'kategori' => 'umum', 'status' => 'draft']);
        $this->assertSame(1, Artikel::count());

        // Backup penuh ke file nyata pada storage_path.
        $relative = $service->dump('roundtrip-test.sql');
        $fullPath = Storage::disk(DatabaseBackup::DISK)->path($relative);

        // Ubah data setelah backup.
        Artikel::create(['judul' => 'Setelah Backup', 'konten' => 'y', 'kategori' => 'rth', 'status' => 'draft']);
        Artikel::where('judul', 'Sebelum Restore')->delete();
        $this->assertFalse(Artikel::where('judul', 'Sebelum Restore')->exists());

        // Restore → kembali ke kondisi saat backup.
        $service->restore($fullPath);

        $this->assertTrue(Artikel::where('judul', 'Sebelum Restore')->exists());
        $this->assertFalse(Artikel::where('judul', 'Setelah Backup')->exists());

        @unlink($fullPath);
    }

    public function test_backup_index_hanya_superadmin(): void
    {
        $bidang = $this->bidangUser(AdminRole::BIDANG_RTH);
        $this->actingAs($bidang)->get('/admin/backup')->assertForbidden();

        $super = $this->superadmin();
        $this->actingAs($super)->get('/admin/backup')->assertOk();
    }

    public function test_safe_path_menolak_path_traversal(): void
    {
        $this->assertNull(DatabaseBackup::safePath('../../.env'));
        $this->assertNull(DatabaseBackup::safePath('evil.php'));
    }

    public function test_membuat_backup_via_route(): void
    {
        $super = $this->superadmin();
        $this->actingAs($super)->post('/admin/backup')->assertRedirect();

        $this->assertDatabaseHas('activity_logs', ['event' => 'backup', 'module' => 'system']);
    }
}
