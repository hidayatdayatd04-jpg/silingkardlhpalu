<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Concerns\InteractsWithAdminNotifications;

/**
 * Notifikasi artikel:
 * - Artikel dibuat sebagai Draf → TIDAK ada notifikasi "telah ditayangkan".
 * - Artikel dibuat langsung Tayang → tepat satu notifikasi.
 * - Draf → Tayang → tepat satu notifikasi.
 * - Edit artikel tayang tanpa mengubah status → tidak ada notifikasi baru.
 */
class ArtikelObserverTest extends TestCase
{
    use DatabaseTransactions, InteractsWithAdminNotifications;

    protected string $constTitle = 'Artikel Ditayangkan';

    protected function makeArtikel(string $status, array $attributes = []): Artikel
    {
        return Artikel::create(array_merge([
            'judul' => 'Artikel Uji '.Str::random(8),
            'slug' => 'artikel-uji-'.Str::lower(Str::random(10)),
            'konten' => '<p>Isi artikel uji.</p>',
            'status' => $status,
            'tanggal_publish' => now()->toDateString(),
        ], $attributes));
    }

    protected function admin(): User
    {
        return $this->makeUser('admin');
    }

    public function test_artikel_draf_tidak_mengirim_notifikasi_publikasi(): void
    {
        $admin = $this->admin();

        $this->makeArtikel('draft');

        $this->assertSame(
            0,
            $this->countNotifications($admin, $this->constTitle),
            'Draf tidak boleh memicu notifikasi "telah ditayangkan".'
        );
    }

    public function test_artikel_dibuat_langsung_tayang_mengirim_tepat_satu_notifikasi(): void
    {
        $admin = $this->admin();

        $this->makeArtikel('published');

        $this->assertSame(1, $this->countNotifications($admin, $this->constTitle));

        $notif = collect($this->notificationTitles($admin));
        $this->assertTrue($notif->contains($this->constTitle));
    }

    public function test_artikel_draf_ke_tayang_mengirim_tepat_satu_notifikasi(): void
    {
        $admin = $this->admin();

        $artikel = $this->makeArtikel('draft');
        $this->assertSame(0, $this->countNotifications($admin, $this->constTitle));

        $artikel->update(['status' => 'published']);

        $this->assertSame(
            1,
            $this->countNotifications($admin, $this->constTitle),
            'Publikasi draf harus mengirim tepat satu notifikasi.'
        );
    }

    public function test_edit_artikel_tayang_tanpa_ubah_status_tidak_mengirim_notifikasi_baru(): void
    {
        $admin = $this->admin();

        $artikel = $this->makeArtikel('published');
        $this->assertSame(1, $this->countNotifications($admin, $this->constTitle));

        // Edit konten & judul — status tidak berubah.
        $artikel->update([
            'judul' => $artikel->judul.' (revisi)',
            'konten' => '<p>Isi revisi.</p>',
        ]);

        $this->assertSame(
            1,
            $this->countNotifications($admin, $this->constTitle),
            'Edit tanpa perubahan status tidak boleh membuat notifikasi publikasi baru.'
        );
    }

    public function test_pesan_notifikasi_memakai_kata_tayang(): void
    {
        $admin = $this->admin();

        $artikel = $this->makeArtikel('draft');
        $artikel->update(['status' => 'published']);

        $messages = $admin->notifications()->get()
            ->map(fn ($n) => (string) ($n->data['message'] ?? ''))
            ->filter(fn (string $m) => str_contains($m, 'telah ditayangkan'));

        $this->assertSame(1, $messages->count(), 'Pesan harus "Artikel ... telah ditayangkan."');
    }

    public function test_notifikasi_artikel_dihapus_bersama_artikelnya(): void
    {
        $admin = $this->admin();
        $artikel = $this->makeArtikel('published');

        $notification = $admin->notifications()->firstOrFail();
        $this->assertSame($artikel->id, $notification->data['resource_id']);

        $artikel->delete();

        $this->assertSame(
            0,
            $admin->fresh()->notifications()->count(),
            'Notifikasi yang menaut ke artikel terhapus tidak boleh tersisa.'
        );
    }
}
