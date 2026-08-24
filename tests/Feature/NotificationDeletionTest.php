<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PengaduanSampah;
use App\Support\Admin\AdminNotificationCleaner;
use App\Support\Admin\AdminNotificationFeed;
use App\Support\AdminNotifier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\InteractsWithAdminNotifications;
use Tests\TestCase;

class NotificationDeletionTest extends TestCase
{
    use DatabaseTransactions, InteractsWithAdminNotifications;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->makeUser('admin');
    }

    public function test_admin_dapat_menghapus_notifikasi_miliknya_sendiri_dan_cache_diperbarui(): void
    {
        AdminNotifier::toUser($this->user, [
            'title' => 'Komentar Artikel Baru',
            'message' => 'Ada komentar baru.',
            'module' => 'artikel',
        ]);

        $notification = $this->user->notifications()->firstOrFail();
        $cacheKey = AdminNotificationFeed::cacheKey($this->user);
        AdminNotificationFeed::forUser($this->user);
        $this->assertTrue(cache()->has($cacheKey));

        $response = $this->actingAs($this->user)
            ->deleteJson(route('admin.notifications.destroy', $notification->id));

        $response->assertOk()->assertJson(['ok' => true, 'unread' => 0]);
        $this->assertDatabaseMissing('notification', ['id' => $notification->id]);
        $this->assertFalse(cache()->has($cacheKey));
    }

    public function test_admin_tidak_dapat_menghapus_notifikasi_milik_akun_lain(): void
    {
        $otherUser = $this->makeUser('admin');
        AdminNotifier::toUser($otherUser, [
            'title' => 'Cadangan Berhasil',
            'message' => 'Cadangan tersedia.',
        ]);

        $notification = $otherUser->notifications()->firstOrFail();

        $this->actingAs($this->user)
            ->deleteJson(route('admin.notifications.destroy', $notification->id))
            ->assertNotFound();

        $this->assertDatabaseHas('notification', ['id' => $notification->id]);
    }

    public function test_notifikasi_backup_terhapus_saat_file_backup_dihapus(): void
    {
        AdminNotifier::toUser($this->user, [
            'title' => 'Cadangan Berhasil',
            'message' => 'Cadangan tersedia.',
            'module' => 'system',
            'backup_file' => 'backups/backup-test.zip',
        ]);

        $this->assertSame(1, $this->user->notifications()->count());

        AdminNotificationCleaner::forBackup('backups/backup-test.zip');

        $this->assertSame(0, $this->user->fresh()->notifications()->count());
    }

    public function test_notifikasi_pengaduan_terhapus_bersama_pengaduannya(): void
    {
        $pengaduan = PengaduanSampah::create([
            'nama_pelapor' => 'Pelapor Uji',
            'nomor_hp' => '081234567890',
            'jenis_pengaduan' => 'Sampah menumpuk',
            'deskripsi' => 'Ada sampah yang perlu ditindaklanjuti.',
            'alamat' => 'Palu',
        ]);

        $this->assertSame(1, $this->user->notifications()->count());

        $pengaduan->delete();

        $this->assertSame(0, $this->user->fresh()->notifications()->count());
    }
}
