<?php

namespace Tests\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Helper bersama untuk test notifikasi admin.
 */
trait InteractsWithAdminNotifications
{
    /**
     * Buat user aktif dengan role Spatie tertentu ('admin', 'bidang-sampah-lb3', dst).
     */
    protected function makeUser(?string $role = null, array $attributes = []): User
    {
        $user = User::create(array_merge([
            'name' => 'User '.Str::random(6),
            'username' => 'u_'.Str::lower(Str::random(10)),
            'email' => Str::lower(Str::random(12)).'@example.test',
            'password' => 'PasswordRahasia123',
            'is_active' => true,
        ], $attributes));

        if ($role !== null) {
            $user->assignRole($role);
        }

        return $user;
    }

    /**
     * Judul seluruh notifikasi database milik user.
     *
     * @return string[]
     */
    protected function notificationTitles(User $user): array
    {
        return $user->notifications()
            ->get()
            ->map(fn ($n) => (string) ($n->data['title'] ?? ''))
            ->all();
    }

    /**
     * Hitung notifikasi user dengan judul tertentu.
     */
    protected function countNotifications(User $user, string $title): int
    {
        return collect($this->notificationTitles($user))->filter(
            fn (string $t) => $t === $title
        )->count();
    }

    /**
     * Pastikan model fixture dibuat tanpa memicu observer (untuk noise yang tidak relevan).
     */
    protected function createWithoutEvents(string $modelClass, array $attributes): Model
    {
        $model = new $modelClass;
        $model->unguard();

        foreach ($attributes as $key => $value) {
            $model->setAttribute($key, $value);
        }

        $model->saveQuietly();
        $model->reguard();

        return $model;
    }
}
