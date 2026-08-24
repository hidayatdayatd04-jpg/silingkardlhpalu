<?php

namespace App\Jobs;

use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\BackupCancelledException;
use App\Support\BackupProgress;
use App\Support\DatabaseBackup;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

/**
 * Jalankan backup database + storage di latar belakang (queue) agar pengguna
 * bebas berpindah halaman. Progres & pembatalan via App\Support\BackupProgress
 * (dipantau widget global di layout admin).
 */
class RunBackupJob implements ShouldQueue
{
    use Queueable;

    /** Backup besar (dump + upload B2) diizinkan hingga 30 menit. */
    public int $timeout = 1800;

    /** Jangan ulangi otomatis — state progres tidak aman di-reset. */
    public int $tries = 1;

    public function __construct(public int $userId) {}

    public function handle(): void
    {
        // Guard anti-eksekusi-ganda — gunakan database lock agar konsisten dengan BackupProgress store
        $lock = Cache::store('database')->lock('backup:task:run', $this->timeout + 60);
        // Fallback ke default store bila database lock tidak tersedia
        if (! $lock) {
            $lock = Cache::lock('backup:task:run', $this->timeout + 60);
        }
        if (! $lock->get()) {
            return;
        }

        $user = User::query()->find($this->userId);

        try {
            $path = app(DatabaseBackup::class)
                ->withProgress(BackupProgress::reporter(), fn () => BackupProgress::isCancelled())
                ->dump();

            $name = basename($path);

            ActivityLogger::log(
                'backup',
                'Cadangan data + dokumen: '.$name.' (latar belakang)',
                'system',
                null, null, null, $user,
            );

            BackupProgress::finish('done', 'Cadangan data berhasil dibuat.', ['file' => $name]);

            if ($user) {
                \App\Support\AdminNotifier::toUser($user, [
                    'title' => 'Cadangan Berhasil',
                    'message' => 'Cadangan data dan dokumen aplikasi berhasil dibuat.',
                    'icon' => 'archive',
                    'color' => 'emerald',
                    'href' => route('admin.backup.index'),
                    'module' => 'system',
                    'backup_file' => $path,
                ]);
            }
        } catch (BackupCancelledException $e) {
            BackupProgress::finish('cancelled', 'Pembuatan cadangan dibatalkan.');
        } catch (\Throwable $e) {
            report($e);
            BackupProgress::finish('failed', 'Pembuatan cadangan belum berhasil. Silakan coba lagi.');

            if ($user) {
                \App\Support\AdminNotifier::toUser($user, [
                    'title' => 'Cadangan Gagal',
                    'message' => 'Pembuatan cadangan belum berhasil dibuat. Silakan coba lagi.',
                    'icon' => 'alert-triangle',
                    'color' => 'rose',
                    'href' => route('admin.backup.index'),
                    'module' => 'system',
                ]);
            }
        } finally {
            $lock->release();
        }
    }

    /** Bila worker gagal fatal (timeout/kill) sebelum handle() sempat catch. */
    public function failed(\Throwable $e): void
    {
        report($e);

        // Jangan timpa status akhir bila job sebenarnya sudah selesai
        if ((BackupProgress::state()['status'] ?? null) === 'done') {
            return;
        }

        BackupProgress::finish('failed', 'Pembuatan cadangan belum berhasil. Silakan coba lagi.');

        $user = User::query()->find($this->userId);
        if ($user) {
            \App\Support\AdminNotifier::toUser($user, [
                'title' => 'Cadangan Gagal',
                'message' => 'Pembuatan cadangan belum berhasil dibuat. Silakan coba lagi.',
                'icon' => 'alert-triangle',
                'color' => 'rose',
                'href' => route('admin.backup.index'),
                'module' => 'system',
            ]);
        }
    }
}
