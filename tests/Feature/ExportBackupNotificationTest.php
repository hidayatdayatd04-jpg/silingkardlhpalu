<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ExportReady;
use App\Support\Admin\AdminNotificationFeed;
use App\Support\DatabaseBackup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Concerns\InteractsWithAdminNotifications;

/**
 * Notifikasi hasil proses latar belakang:
 * - Ekspor siap → "Ekspor [data] siap" + cache feed terhapus.
 * - Cadangan gagal → notifikasi aman TANPA raw exception.
 */
class ExportBackupNotificationTest extends TestCase
{
    use DatabaseTransactions, InteractsWithAdminNotifications;

    public function test_ekspor_berhasil_mengirim_notifikasi_dan_menghapus_cache(): void
    {
        $user = $this->makeUser('admin');
        $key = AdminNotificationFeed::cacheKey($user);
        AdminNotificationFeed::forUser($user);
        $this->assertTrue(cache()->has($key));

        (new \App\Jobs\GenerateExportJob($user->id, 'artikel', 'all', 'csv'))->handle();

        $this->assertSame(1, $this->countNotifications($user, 'Ekspor Artikel siap'));

        $notif = $user->notifications()->first();
        $this->assertArrayHasKey('href', $notif->data);
        $this->assertStringContainsString('exports/download', (string) $notif->data['href']);

        $this->assertFalse(
            cache()->has($key),
            'GenerateExportJob harus menghapus cache feed penerima.'
        );
    }

    public function test_notifikasi_export_ready_memakai_channel_database(): void
    {
        $user = $this->makeUser('admin');
        $notif = new ExportReady(title: 'Ekspor Artikel siap', message: 'File CSV telah dibuat.', href: 'http://localhost/admin/exports/download/x', downloadName: 'a.csv');

        $this->assertContains('database', $notif->via($user));
    }

    public function test_cadangan_gagal_mengirim_notifikasi_tanpa_raw_exception(): void
    {
        $user = $this->makeUser('admin');

        // Stub DatabaseBackup yang gagal dengan pesan exception berisi detail teknis.
        $rawMessage = 'SQLSTATE[08006] connection failed to neondb-host.aws.neon.tech (bucket b2://dlh-backup)';
        $failing = new class($rawMessage) extends DatabaseBackup {
            public function __construct(protected string $message)
            {
                parent::__construct();
            }

            public function dump(?string $filename = null): string
            {
                throw new \RuntimeException($this->message);
            }
        };
        $this->app->instance(DatabaseBackup::class, $failing);

        (new \App\Jobs\RunBackupJob($user->id))->handle();

        $this->assertSame(1, $this->countNotifications($user, 'Cadangan Gagal'));

        $notif = $user->notifications()->first();
        $payload = json_encode($notif->data, JSON_UNESCAPED_SLASHES);

        $this->assertStringNotContainsString('SQLSTATE', $payload ?? '', 'Raw exception tidak boleh bocor ke notifikasi.');
        $this->assertStringNotContainsString('neondb', $payload ?? '');
        $this->assertStringNotContainsString('bucket', $payload ?? '');
        $this->assertStringContainsString('Silakan coba lagi', (string) $notif->data['message']);
    }
}
