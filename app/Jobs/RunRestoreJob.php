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
 * Jalankan restore database + storage di latar belakang (queue).
 *
 * File sumber salah satu dari:
 *  - $filePath       : file lokal persisten hasil upload (.zip/.sql), atau
 *  - $backupRelative : path relatif file backup di disk backup (diunduh dulu).
 *
 * Pre-restore dibuat lebih dulu (fase 0–30%), lalu restore (fase 30–100%).
 */
class RunRestoreJob implements ShouldQueue
{
    use Queueable;

    /** Restore besar (unduh B2 + eksekusi SQL) diizinkan hingga 30 menit. */
    public int $timeout = 1800;

    /** Jangan ulangi otomatis — restore merge tetap tidak boleh dijalankan ganda. */
    public int $tries = 1;

    public function __construct(
        public int $userId,
        public string $sourceName,
        public ?string $filePath = null,
        public ?string $backupRelative = null,
    ) {}

    public function handle(): void
    {
        // Guard anti-eksekusi-ganda — gunakan database lock agar konsisten dengan BackupProgress store
        $lock = Cache::store('database')->lock('backup:task:run', $this->timeout + 60);
        if (! $lock) {
            $lock = Cache::lock('backup:task:run', $this->timeout + 60);
        }
        if (! $lock->get()) {
            return;
        }

        $user = User::query()->find($this->userId);
        $preRestore = null;
        $localFile = $this->filePath;
        $downloaded = null;

        try {
            // File dari disk backup (B2) → unduh ke lokal dulu.
            if ($localFile === null && $this->backupRelative !== null) {
                BackupProgress::update([
                    'status' => 'running',
                    'percent' => 1,
                    'label' => 'Membuka cadangan…',
                ]);

                $downloaded = DatabaseBackup::downloadToTemp($this->backupRelative);
                if ($downloaded === null) {
                    throw new \RuntimeException('Gagal mengambil file backup dari storage.');
                }
                $localFile = $downloaded;
            }

            if ($localFile === null || ! is_file($localFile)) {
                throw new \RuntimeException('File backup tidak ditemukan.');
            }

            $service = app(DatabaseBackup::class);
            $isCancelled = fn () => BackupProgress::isCancelled();

            // 1) Amankan kondisi saat ini dulu — pre-restore (fase 0–30%).
            $service->withProgress(BackupProgress::reporter(0, 30), $isCancelled);
            $preRestore = basename($service->dump('pre-restore-'.now()->format('Ymd-His').'.zip'));

            // 2) Restore (fase 30–100%).
            $service->withProgress(BackupProgress::reporter(30, 100), $isCancelled);
            $count = $service->restore($localFile);

            ActivityLogger::log(
                'restore',
                'Pemulihan data + dokumen dari: '.$this->sourceName.' (latar belakang)',
                'system',
                null, null, null, $user,
            );

            BackupProgress::finish('done', 'Data berhasil dipulihkan dari cadangan.');

            if ($user) {
                \App\Support\AdminNotifier::toUser($user, [
                    'title' => 'Pemulihan Berhasil',
                    'message' => 'Data dan dokumen aplikasi berhasil dipulihkan.',
                    'icon' => 'refresh',
                    'color' => 'emerald',
                    'href' => route('admin.backup.index'),
                    'module' => 'system',
                ]);
            }
        } catch (BackupCancelledException $e) {
            BackupProgress::finish('cancelled', 'Proses pemulihan dibatalkan.');
        } catch (\Throwable $e) {
            report($e);
            BackupProgress::finish('failed', 'Pemulihan data belum berhasil. Silakan coba lagi atau hubungi pengelola sistem.');

            if ($user) {
                \App\Support\AdminNotifier::toUser($user, [
                    'title' => 'Pemulihan Gagal',
                    'message' => 'Pemulihan data belum berhasil. Silakan coba lagi atau hubungi pengelola sistem.',
                    'icon' => 'alert-triangle',
                    'color' => 'rose',
                    'href' => route('admin.backup.index'),
                    'module' => 'system',
                ]);
            }
        } finally {
            // Bersihkan file lokal sementara (upload maupun hasil unduhan).
            foreach (array_filter([$this->filePath, $downloaded]) as $f) {
                if (is_file($f)) {
                    @unlink($f);
                }
            }

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

        BackupProgress::finish('failed', 'Pemulihan data belum berhasil. Silakan coba lagi.');

        $user = User::query()->find($this->userId);
        if ($user) {
            \App\Support\AdminNotifier::toUser($user, [
                'title' => 'Pemulihan Gagal',
                'message' => 'Pemulihan data belum berhasil. Silakan coba lagi atau hubungi pengelola sistem.',
                'icon' => 'alert-triangle',
                'color' => 'rose',
                'href' => route('admin.backup.index'),
                'module' => 'system',
            ]);
        }

        if ($this->filePath && is_file($this->filePath)) {
            @unlink($this->filePath);
        }
    }
}
