<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * State bersama untuk task backup/restore yang berjalan di latar belakang (queue).
 *
 * Progress disimpan di cache agar bisa dibaca request mana pun (widget polling
 * di halaman admin), dan flag pembatalan dicek secara kooperatif oleh job
 * yang sedang berjalan.
 */
class BackupProgress
{
    public const STATE_KEY = 'backup:task:state';

    public const CANCEL_KEY = 'backup:task:cancel';

    /** TTL cache (detik) — cukup untuk proses backup/restore terbesar. */
    protected const TTL = 3600;

    /**
     * State task saat ini, atau null bila tidak ada.
     *
     * @return array{type:string,status:string,percent:int,label:?string,message:?string,file:?string,started_at:int,updated_at:int}|null
     */
    public static function state(): ?array
    {
        $state = Cache::get(self::STATE_KEY);

        return is_array($state) ? $state : null;
    }

    /**
     * Apakah ada task backup/restore yang masih aktif (pending/running)?
     */
    public static function isActive(): bool
    {
        $state = self::state();

        return $state !== null && in_array($state['status'] ?? '', ['pending', 'running'], true);
    }

    /**
     * Mulai task baru. Return false bila masih ada task lain yang aktif.
     */
    public static function start(string $type, ?string $file = null): bool
    {
        if (self::isActive()) {
            return false;
        }

        self::clearCancel();

        Cache::put(self::STATE_KEY, [
            'type' => $type,
            'status' => 'pending',
            'percent' => 0,
            'label' => $type === 'restore' ? 'Menyiapkan restore…' : 'Menyiapkan backup…',
            'message' => null,
            'file' => $file,
            'started_at' => now()->getTimestamp(),
            'updated_at' => now()->getTimestamp(),
        ], self::TTL);

        return true;
    }

    /**
     * Perbarui sebagian field state (percent, label, status, ...).
     */
    public static function update(array $patch): void
    {
        $state = self::state();
        if ($state === null) {
            return;
        }

        $state = array_merge($state, $patch);
        $state['percent'] = max(0, min(100, (int) round((int) ($state['percent'] ?? 0))));
        $state['updated_at'] = now()->getTimestamp();

        Cache::put(self::STATE_KEY, $state, self::TTL);
    }

    /**
     * Tandai task selesai (done | failed | cancelled).
     */
    public static function finish(string $status, ?string $message = null, array $extra = []): void
    {
        $state = self::state() ?? [];

        Cache::put(self::STATE_KEY, array_merge($state, $extra, [
            'status' => $status,
            'percent' => $status === 'done' ? 100 : (int) ($state['percent'] ?? 0),
            'message' => $message,
            'updated_at' => now()->getTimestamp(),
        ]), self::TTL);

        self::clearCancel();
    }

    /**
     * Buat closure progress dengan throttle (tulis ke cache hanya saat persen
     * berubah atau tiap $minInterval detik) dan pemetaan ke rentang $from–$to.
     */
    public static function reporter(int $from = 0, int $to = 100, float $minInterval = 1.0): \Closure
    {
        $lastPercent = -1;
        $lastWrite = 0.0;

        return function (int $percent, string $label) use ($from, $to, $minInterval, &$lastPercent, &$lastWrite): void {
            $percent = max(0, min(100, $percent));
            $mapped = $from + (int) round(($to - $from) * $percent / 100);

            $now = microtime(true);
            if ($mapped === $lastPercent && ($now - $lastWrite) < $minInterval) {
                return;
            }

            $lastPercent = $mapped;
            $lastWrite = $now;

            self::update(['status' => 'running', 'percent' => $mapped, 'label' => $label]);
        };
    }

    /**
     * Minta pembatalan task yang sedang berjalan (dipenuhi secara kooperatif).
     */
    public static function requestCancel(): bool
    {
        if (! self::isActive()) {
            return false;
        }

        Cache::put(self::CANCEL_KEY, true, self::TTL);

        self::update(['label' => 'Membatalkan…']);

        return true;
    }

    public static function isCancelled(): bool
    {
        return (bool) Cache::get(self::CANCEL_KEY, false);
    }

    public static function clearCancel(): void
    {
        Cache::forget(self::CANCEL_KEY);
    }

    public static function clear(): void
    {
        Cache::forget(self::STATE_KEY);
        self::clearCancel();
    }
}
