<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Admin\AdminNotificationFeed;
use App\Support\Admin\AdminUrl;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Concerns\InteractsWithAdminNotifications;

/**
 * Notifikasi menyimpan href absolut pada saat dibuat. Setelah prefix panel
 * dipindah ke env ADMIN_PATH dan domain live berpindah antara www/non-www,
 * href lama harus dinormalisasi saat dirender agar klik link tetap sampai
 * — tanpa mengubah baris database yang sudah ada.
 */
class LegacyNotificationHrefTest extends TestCase
{
    use DatabaseTransactions, InteractsWithAdminNotifications;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->makeUser('admin');
    }

    /**
     * Simpan notifikasi database mentah meniru baris lama dengan href tertentu.
     */
    protected function seedLegacy(string $href, ?string $title = null): void
    {
        $this->user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => \App\Notifications\AdminNotification::class,
            'data' => [
                'title' => $title ?? 'Notifikasi lama',
                'message' => 'Baris simulasi sebelum ADMIN_PATH.',
                'module' => 'system',
                'href' => $href,
            ],
        ]);
    }

    protected function currentAdminSegment(): string
    {
        return trim((string) config('app.admin_path'), '/');
    }

    public function test_href_legacy_relatif_ditulis_ulang_di_feed(): void
    {
        $this->seedLegacy('/admin/resources/artikel');

        $feed = AdminNotificationFeed::forUser($this->user);

        $this->assertSame(
            '/'.$this->currentAdminSegment().'/resources/artikel',
            $feed['notifications'][0]['href']
        );
    }

    public function test_href_legacy_absolut_internal_jadi_relatif_beserta_query_fragment(): void
    {
        config(['app.url' => 'http://localhost']);

        $this->seedLegacy('http://localhost/admin/exports/download/x?q=1#top');

        $feed = AdminNotificationFeed::forUser($this->user);

        $this->assertSame(
            '/'.$this->currentAdminSegment().'/exports/download/x?q=1#top',
            $feed['notifications'][0]['href']
        );
    }

    public function test_href_host_non_www_dari_deploy_jadi_path_relatif(): void
    {
        // Baris lama tersimpan dengan host non-www sementara situs live
        // diakses lewat www — klik harus tetap mendarat di host yang benar.
        config(['app.url' => 'https://silingkardlhpalu.web.id']);

        $this->seedLegacy(
            'https://silingkardlhpalu.web.id/'.$this->currentAdminSegment().'/pengaduan-sampah/6'
        );

        $feed = AdminNotificationFeed::forUser($this->user);

        $this->assertSame(
            '/'.$this->currentAdminSegment().'/pengaduan-sampah/6',
            $feed['notifications'][0]['href']
        );
    }

    public function test_href_varian_www_juga_diakui_sebagai_internal(): void
    {
        config(['app.url' => 'https://silingkardlhpalu.web.id']);

        $this->seedLegacy('https://www.silingkardlhpalu.web.id/admin/backup');

        $feed = AdminNotificationFeed::forUser($this->user);

        $this->assertSame('/'.$this->currentAdminSegment().'/backup', $feed['notifications'][0]['href']);
    }

    public function test_domain_eksternal_tetap_absolut(): void
    {
        config(['app.url' => 'https://silingkardlhpalu.web.id']);

        $external = 'https://drive.google.com/file/d/abc/view';

        $this->seedLegacy($external);

        $feed = AdminNotificationFeed::forUser($this->user);

        $this->assertSame($external, $feed['notifications'][0]['href']);
    }

    public function test_href_non_admin_tidak_diubah(): void
    {
        $this->seedLegacy('/pengaduan', 'Publik');
        $this->seedLegacy('#', 'Anchor');

        $hrefs = AdminNotificationFeed::forUser($this->user)['notifications']
            ->pluck('href', 'title')
            ->all();

        $this->assertSame('/pengaduan', $hrefs['Publik']);
        $this->assertSame('#', $hrefs['Anchor']);
    }

    public function test_helper_membiarkan_skema_non_http_dan_segmen_lain(): void
    {
        // Segmen pertama bukan 'admin'.
        $this->assertSame('/uptd/tpa-kawatuna', AdminUrl::normalizeLegacyHref('/uptd/tpa-kawatuna'));

        // Skema non-http(s).
        $this->assertSame('mailto:admin@dlhpalu.go.id', AdminUrl::normalizeLegacyHref('mailto:admin@dlhpalu.go.id'));

        // Path relatif-root tanpa segmen legacy.
        $this->assertSame('/login', AdminUrl::normalizeLegacyHref('/login'));
    }

    public function test_halaman_index_merender_href_yang_sudah_diperbaiki(): void
    {
        $this->seedLegacy('/admin/resources/artikel');

        $segment = $this->currentAdminSegment();
        $response = $this->actingAs($this->user)->get(route('admin.notifications.index'));

        $response->assertOk();
        $response->assertSee('/'.$segment.'/resources/artikel', false);

        // Hanya relevan bila prefix aktif memang bukan 'admin'.
        if ($segment !== 'admin') {
            $response->assertDontSee('/admin/resources/artikel', false);
        }
    }
}
