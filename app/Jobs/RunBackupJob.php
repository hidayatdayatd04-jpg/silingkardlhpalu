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
                'Backup database + storage: '.$name.' → '.DatabaseBackup::diskName().' (latar belakang)',
                'system',
                null, null, null, $user,
            );

            BackupProgress::finish('done', 'Backup berhasil dibuat: '.$name, ['file' => $name]);
        } catch (BackupCancelledException $e) {
            BackupProgress::finish('cancelled', 'Backup dibatalkan oleh pengguna.');
        } catch (\Throwable $e) {
            report($e);
            BackupProgress::finish('failed', 'Gagal membuat backup: '.$e->getMessage());
        } finally {
            $lock->release();
        }
    }

    /** Bila worker gagal fatal (timeout/kill) sebelum handle() sempat catch. */
    public function failed(\Throwable $e): void
    {
        // Jangan timpa status akhir bila job sebenarnya sudah selesai —
        // mis. worker menandai job gagal karena retry_after terlampaui
        // padahal handle() sudah menulis status 'done'.
        if ((BackupProgress::state()['status'] ?? null) === 'done') {
            return;
        }

        BackupProgress::finish('failed', 'Gagal membuat backup: '.$e->getMessage());
    }
}
