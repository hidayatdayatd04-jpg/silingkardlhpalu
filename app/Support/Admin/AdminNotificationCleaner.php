<?php

namespace App\Support\Admin;

use App\Models\DatabaseNotification;
use App\Notifications\AdminNotification;
use Closure;

/**
 * Menghapus notifikasi yang sumber datanya sudah tidak tersedia.
 *
 * Notifikasi lama belum selalu menyimpan ID resource. Karena itu pencocokan
 * juga memakai path tautannya agar notifikasi lama ikut dibersihkan.
 */
class AdminNotificationCleaner
{
    /**
     * Hapus semua notifikasi admin yang merujuk ke satu record resource.
     */
    public static function forResource(string $resource, int|string $recordId): int
    {
        $expectedPath = '/'.trim((string) config('app.admin_path'), '/').'/'.$resource.'/'.$recordId;

        return static::deleteMatching(function (array $data) use ($resource, $recordId, $expectedPath): bool {
            if (($data['module'] ?? null) !== $resource) {
                return false;
            }

            if (array_key_exists('resource_id', $data) && (string) $data['resource_id'] === (string) $recordId) {
                return true;
            }

            return static::pathFromHref($data['href'] ?? null) === $expectedPath;
        });
    }

    /**
     * Hapus notifikasi keberhasilan untuk file cadangan yang telah dihapus.
     */
    public static function forBackup(string $backupPath): int
    {
        $backupName = basename($backupPath);

        return static::deleteMatching(function (array $data) use ($backupName): bool {
            if (($data['module'] ?? null) !== 'system') {
                return false;
            }

            $notificationFile = $data['backup_file'] ?? null;

            if (is_string($notificationFile)) {
                return basename($notificationFile) === $backupName;
            }

            // Notifikasi lama belum menyimpan nama file cadangan. Pesannya
            // bersifat generik, sehingga tidak aman lagi dipertahankan saat
            // riwayat cadangan mulai dihapus.
            return ! array_key_exists('backup_file', $data)
                && in_array($data['title'] ?? null, ['Cadangan Berhasil', 'Pemulihan Berhasil'], true);
        });
    }

    /**
     * @param  Closure(array): bool  $matches
     */
    private static function deleteMatching(Closure $matches): int
    {
        $notifications = DatabaseNotification::query()
            ->where('type', AdminNotification::class)
            ->get(['id', 'notifiable_id', 'data'])
            ->filter(fn (DatabaseNotification $notification) => $matches($notification->data ?? []));

        if ($notifications->isEmpty()) {
            return 0;
        }

        $recipientIds = $notifications->pluck('notifiable_id')->unique();
        DatabaseNotification::query()->whereKey($notifications->pluck('id'))->delete();
        AdminNotificationFeed::forget($recipientIds);

        return $notifications->count();
    }

    private static function pathFromHref(mixed $href): ?string
    {
        if (! is_string($href) || $href === '') {
            return null;
        }

        $path = parse_url($href, PHP_URL_PATH);

        return is_string($path) ? rtrim($path, '/') : null;
    }
}
