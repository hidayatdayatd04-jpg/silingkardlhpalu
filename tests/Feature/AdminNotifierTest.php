<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Admin\AdminNotificationFeed;
use App\Support\AdminNotifier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Concerns\InteractsWithAdminNotifications;

/**
 * Distribusi notifikasi per bidang:
 * - Administrator Utama (role "admin") menerima SEMUA bidang.
 * - Admin bidang hanya menerima bidangnya (+ akses tambahan).
 * - Cache feed penerima selalu dihapus setelah notifikasi dibuat.
 */
class AdminNotifierTest extends TestCase
{
    use DatabaseTransactions, InteractsWithAdminNotifications;

    protected User $superadmin;

    protected User $adminSampah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = $this->makeUser('admin');
        $this->adminSampah = $this->makeUser('bidang-sampah-lb3');
    }

    public function test_admin_utama_menerima_notifikasi_semua_bidang(): void
    {
        $expectedGroups = [
            'pengendalian' => 'Uji pengendalian',
            'sampah-lb3' => 'Uji sampah-lb3',
            'rth' => 'Uji rth',
            'tata-penataan' => 'Uji tata-penataan',
            'konten' => 'Uji konten',
        ];

        foreach ($expectedGroups as $group => $title) {
            AdminNotifier::toGroup($group, [
                'title' => $title,
                'message' => 'Pesan uji.',
                'module' => 'system',
            ]);
        }

        $titles = $this->notificationTitles($this->superadmin);
        foreach ($expectedGroups as $group => $title) {
            $this->assertContains($title, $titles, "Administrator Utama harus menerima grup {$group}.");
        }
    }

    public function test_admin_bidang_hanya_menerima_bidangnya(): void
    {
        AdminNotifier::toGroup('sampah-lb3', ['title' => 'Registrasi Usaha LB3 Baru', 'message' => 'x']);
        AdminNotifier::toGroup('pengendalian', ['title' => 'Pengaduan Pengendalian Baru', 'message' => 'y']);
        AdminNotifier::toGroup('rth', ['title' => 'Penyewaan Taman Baru', 'message' => 'z']);

        $titles = $this->notificationTitles($this->adminSampah);

        $this->assertContains('Registrasi Usaha LB3 Baru', $titles);
        $this->assertNotContains('Pengaduan Pengendalian Baru', $titles);
        $this->assertNotContains('Penyewaan Taman Baru', $titles);
    }

    public function test_akses_tambahan_slug_membuka_notifikasi_grup_terkait(): void
    {
        // Admin RTH diberi akses tambahan ke satu menu di grup sampah-lb3.
        $registry = \App\Support\Admin\AdminRegistry::all();
        $slug = collect($registry['sampah-lb3']['items'])->pluck('slug')->filter()->first();

        $adminRth = $this->makeUser('bidang-rth', ['additional_access' => [$slug]]);

        AdminNotifier::toGroup('sampah-lb3', ['title' => 'Pengajuan RINTEK/PERTEK Baru', 'message' => 'x']);
        AdminNotifier::toGroup('tata-penataan', ['title' => 'Pelanggaran Terdeteksi', 'message' => 'y']);

        $titles = $this->notificationTitles($adminRth);

        $this->assertContains('Pengajuan RINTEK/PERTEK Baru', $titles, 'Akses tambahan slug harus membuka notifikasi grup terkait.');
        $this->assertNotContains('Pelanggaran Terdeteksi', $titles);
    }

    public function test_to_group_menghapus_cache_feed_setiap_penerima(): void
    {
        $keyAdmin = AdminNotificationFeed::cacheKey($this->superadmin);
        $keyBidang = AdminNotificationFeed::cacheKey($this->adminSampah);

        // Hangatkan cache kedua user.
        AdminNotificationFeed::forUser($this->superadmin);
        AdminNotificationFeed::forUser($this->adminSampah);
        $this->assertTrue(cache()->has($keyAdmin));
        $this->assertTrue(cache()->has($keyBidang));

        AdminNotifier::toGroup('konten', ['title' => 'Komentar Artikel Baru', 'message' => 'x']);

        $this->assertFalse(cache()->has($keyAdmin), 'Cache feed superadmin harus terhapus.');

        // User bidang sampah bukan penerima grup konten — cache-nya tidak boleh tersentuh.
        $this->assertTrue(
            cache()->has($keyBidang),
            'User non-penerima tidak boleh ikut di-invalidate.'
        );

        // Namun ketika bidangnya relevan, cache user bidang juga terhapus.
        AdminNotificationFeed::forUser($this->adminSampah);
        AdminNotifier::toGroup('sampah-lb3', ['title' => 'Registrasi Usaha LB3 Baru', 'message' => 'y']);
        $this->assertFalse(cache()->has($keyBidang), 'Cache feed penerima bidang harus terhapus.');
    }

    public function test_to_user_menghapus_cache_feed_penerima(): void
    {
        $key = AdminNotificationFeed::cacheKey($this->superadmin);
        AdminNotificationFeed::forUser($this->superadmin);
        $this->assertTrue(cache()->has($key));

        AdminNotifier::toUser($this->superadmin, ['title' => 'Cadangan Berhasil', 'message' => 'x']);

        $this->assertFalse(cache()->has($key));
    }
}
