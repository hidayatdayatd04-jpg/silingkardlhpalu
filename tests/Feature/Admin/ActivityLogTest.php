<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\Artikel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase, CreatesAdminUsers;

    public function test_membuat_record_menulis_activity_log_created(): void
    {
        $user = $this->superadmin();
        $this->actingAs($user);

        $artikel = Artikel::create([
            'judul' => 'Berita Uji',
            'slug'  => 'berita-uji',
            'konten' => 'Isi berita.',
            'kategori' => \App\Enums\ArtikelKategori::UMUM->value,
            'status' => \App\Enums\ArtikelStatus::DRAFT->value,
        ]);

        $log = ActivityLog::where('event', 'created')
            ->where('auditable_type', Artikel::class)
            ->where('auditable_id', $artikel->id)
            ->first();

        $this->assertNotNull($log, 'Log created harus tercatat.');
        $this->assertSame('created', $log->event);
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('artikel', $log->module);
    }

    public function test_update_record_mencatat_old_dan_new(): void
    {
        $user = $this->superadmin();
        $this->actingAs($user);

        $artikel = Artikel::create([
            'judul' => 'Judul Lama', 'slug' => 'judul-lama', 'konten' => 'x',
            'kategori' => \App\Enums\ArtikelKategori::UMUM->value, 'status' => \App\Enums\ArtikelStatus::DRAFT->value,
        ]);

        $artikel->update(['judul' => 'Judul Baru']);

        $log = ActivityLog::where('event', 'updated')
            ->where('auditable_id', $artikel->id)
            ->latest()->first();

        $this->assertNotNull($log);
        $this->assertSame('Judul Lama', $log->properties['old']['judul'] ?? null);
        $this->assertSame('Judul Baru', $log->properties['new']['judul'] ?? null);
    }

    public function test_delete_record_mencatat_event_deleted(): void
    {
        $user = $this->superadmin();
        $this->actingAs($user);

        $artikel = Artikel::create([
            'judul' => 'Hapus Saya', 'slug' => 'hapus-saya', 'konten' => 'x',
            'kategori' => \App\Enums\ArtikelKategori::UMUM->value, 'status' => \App\Enums\ArtikelStatus::DRAFT->value,
        ]);
        $id = $artikel->id;
        $artikel->delete();

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'deleted',
            'auditable_type' => Artikel::class,
            'auditable_id' => $id,
        ]);
    }

    public function test_login_mencatat_event_login(): void
    {
        $user = $this->superadmin();

        event(new \Illuminate\Auth\Events\Login('web', $user, false));

        $this->assertDatabaseHas('activity_logs', ['event' => 'login', 'user_id' => $user->id]);
    }

    public function test_halaman_activity_log_hanya_superadmin(): void
    {
        $bidang = $this->bidangUser(\App\Enums\AdminRole::BIDANG_RTH);
        $this->actingAs($bidang)->get('/admin/activity-log')->assertForbidden();

        $super = $this->superadmin();
        $this->actingAs($super)->get('/admin/activity-log')->assertOk();
    }
}
