<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Admin\AdminNotificationFeed;
use App\Support\AdminNotifier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Concerns\InteractsWithAdminNotifications;

/**
 * Cache feed harus terhapus saat mark as read / mark all as read,
 * agar polling 30 detik langsung melihat kondisi terbaru.
 */
class NotificationFeedInvalidationTest extends TestCase
{
    use DatabaseTransactions, InteractsWithAdminNotifications;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->makeUser('admin');
    }

    public function test_mark_as_read_menghapus_cache_feed(): void
    {
        AdminNotifier::toUser($this->user, [
            'title' => 'Komentar Artikel Baru',
            'message' => 'Budi mengomentari artikel "Judul".',
            'module' => 'artikel',
        ]);

        $notification = $this->user->unreadNotifications()->first();
        $this->assertNotNull($notification);

        // Hangatkan cache setelah notifikasi masuk.
        $feed = AdminNotificationFeed::forUser($this->user);
        $this->assertSame(1, $feed['count']);
        $key = AdminNotificationFeed::cacheKey($this->user);
        $this->assertTrue(cache()->has($key));

        $response = $this->actingAs($this->user)
            ->postJson(route('admin.notifications.read', $notification->id));

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertFalse(cache()->has($key), 'Cache feed harus dihapus setelah mark as read.');

        // Feed segar harus menunjukkan unread = 0.
        $fresh = AdminNotificationFeed::forUser($this->user);
        $this->assertSame(0, $fresh['count']);
    }

    public function test_mark_all_as_read_menghapus_cache_feed(): void
    {
        AdminNotifier::toUser($this->user, ['title' => 'Cadangan Berhasil', 'message' => 'x']);
        AdminNotifier::toUser($this->user, ['title' => 'Cadangan Gagal', 'message' => 'y']);

        $key = AdminNotificationFeed::cacheKey($this->user);
        AdminNotificationFeed::forUser($this->user);
        $this->assertTrue(cache()->has($key));

        $response = $this->actingAs($this->user)
            ->postJson(route('admin.notifications.read-all'));

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertFalse(cache()->has($key));
        $this->assertSame(
            0,
            $this->user->unreadNotifications()->count(),
            'Semua notifikasi harus berstatus sudah dibaca.'
        );
    }
}
