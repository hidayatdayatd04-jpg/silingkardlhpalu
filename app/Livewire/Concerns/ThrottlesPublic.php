<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Helper rate limiting sederhana untuk aksi publik (submit/cek) berdasarkan
 * IP per jam. Mengembalikan true bila batas terlampaui (serta mencatat pesan
 * error ke field yang diberikan), false bila masih diperbolehkan.
 */
trait ThrottlesPublic
{
    /**
     * @param  string  $key      Identifier unik aksi, mis. 'cek-permohonan-rekomendasi:search'.
     * @param  int     $maxPerHour  Maksimal eksekusi per IP per jam.
     * @param  string  $field    Field tempat pesan error ditambahkan.
     * @param  string  $message  Pesan peringatan (tanpa info menit).
     */
    protected function hitRateLimit(string $key, int $maxPerHour, string $field, string $message): bool
    {
        $rlKey = 'public:'.$key.':'.request()->ip();

        if (RateLimiter::tooManyAttempts($rlKey, $maxPerHour)) {
            $seconds = RateLimiter::availableIn($rlKey);
            $this->addError($field, $message.' '.__('Coba lagi dalam :minutes menit.', [
                'minutes' => (int) ceil($seconds / 60),
            ]));

            return true;
        }

        RateLimiter::hit($rlKey, 3600);

        return false;
    }
}
