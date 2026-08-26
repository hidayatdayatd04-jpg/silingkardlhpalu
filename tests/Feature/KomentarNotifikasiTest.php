<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Concerns\InteractsWithAdminNotifications;

/**
 * Notifikasi komentar artikel:
 * - Komentar masyarakat via API publik → notifikasi "Komentar Artikel Baru" ke grup konten.
 * - Balasan → judul "Balasan Komentar Baru".
 * - Komentar yang dibuat dari panel admin (oleh admin sendiri) → tidak menotify.
 */
class KomentarNotifikasiTest extends TestCase
{
    use DatabaseTransactions, InteractsWithAdminNotifications;

    protected function makeArtikelPublished(): Artikel
    {
        return Artikel::create([
            'judul' => 'Artikel Komentar Uji '.Str::random(6),
            'slug' => 'artikel-komentar-uji-'.Str::lower(Str::random(10)),
            'konten' => '<p>Isi.</p>',
            'status' => 'published',
            'tanggal_publish' => now()->toDateString(),
        ]);
    }

    public function test_komentar_masyarakat_menghasilkan_notifikasi(): void
    {
        $admin = $this->makeUser('admin');
        $artikel = $this->makeArtikelPublished();

        $response = $this->postJson("/api/berita/{$artikel->slug}/komentar", [
            'nama' => 'Budi',
            'body' => 'Komentar uji dari masyarakat.',
        ]);

        $response->assertCreated();
        $this->assertSame(1, $this->countNotifications($admin, 'Komentar Artikel Baru'));

        // Fixture artikel published ikut memicu notifikasi observer
        // ("Artikel ... telah ditayangkan"), jadi ambil notifikasi komentar
        // berdasarkan judulnya — bukan sekadar notifikasi terbaru.
        $notif = $admin->notifications()
            ->latest()
            ->get()
            ->first(fn ($n) => ($n->data['title'] ?? null) === 'Komentar Artikel Baru');
        $this->assertNotNull($notif);
        $this->assertStringContainsString('Budi', (string) $notif->data['message']);
        $this->assertStringContainsString($artikel->judul, (string) $notif->data['message']);
    }

    public function test_balasan_komentar_memakai_judul_notifikasi_balasan(): void
    {
        $admin = $this->makeUser('admin');
        $artikel = $this->makeArtikelPublished();

        $root = \App\Models\ArtikelKomentar::create([
            'artikel_id' => $artikel->id,
            'nama' => 'Ani',
            'body' => 'Komentar utama.',
            'is_hidden' => false,
        ]);

        $response = $this->postJson("/api/berita/{$artikel->slug}/komentar", [
            'nama' => 'Siti',
            'body' => 'Ini balasan.',
            'parent_id' => $root->id,
        ]);

        $response->assertCreated();
        $this->assertSame(1, $this->countNotifications($admin, 'Balasan Komentar Baru'));
        $this->assertSame(0, $this->countNotifications($admin, 'Komentar Artikel Baru'));
    }

    public function test_komentar_dari_panel_admin_tidak_menotify_admin(): void
    {
        $admin = $this->makeUser('admin');
        $artikel = $this->makeArtikelPublished();

        // Admin membalas dari halaman pengelolaan komentar (panel admin).
        $response = $this->actingAs($admin)
            ->post(route('admin.artikel.komentar.store', $artikel->id), [
                'body' => 'Balasan dari admin.',
                'pin' => false,
            ]);

        $response->assertRedirect();
        $this->assertSame(
            0,
            $this->countNotifications($admin, 'Komentar Artikel Baru'),
            'Aksi komentar oleh admin sendiri tidak boleh menotify dirinya.'
        );
        $this->assertSame(0, $this->countNotifications($admin, 'Balasan Komentar Baru'));
    }
}
