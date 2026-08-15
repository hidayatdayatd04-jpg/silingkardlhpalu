<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\AdminNotification;
use App\Support\Admin\AdminRegistry;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Kirim notifikasi admin ke seluruh user yang berhak mengakses grup tertentu.
 */
class AdminNotifier
{
    /**
     * @param  string  $group   key grup (pengendalian|sampah-lb3|rth|tata-penataan|konten)
     * @param  array{title:string,message:string,icon?:string,color?:string,href?:string,module?:string}  $payload
     */
    /**
     * Map group keys to actual Spatie role names.
     */
    protected static function groupToRoleNames(string $group): array
    {
        return match ($group) {
            'pengendalian' => ['bidang-pengendalian'],
            'sampah-lb3'   => ['bidang-sampah-lb3'],
            'rth'          => ['bidang-rth'],
            'tata-penataan' => ['bidang-tata-penataan'],
            'konten'       => ['admin'],
            default        => [$group],
        };
    }

    public static function toGroup(string $group, array $payload): void
    {
        try {
            $roleNames = static::groupToRoleNames($group);

            // Slug menu yang termasuk grup ini, untuk mencocokkan additional_access
            // yang kini menyimpan slug menu spesifik (bukan key grup).
            $groupSlugs = collect(AdminRegistry::all()[$group]['items'] ?? [])
                ->pluck('slug')
                ->filter()
                ->values()
                ->all();

            $users = User::where('is_active', true)
                ->where(function ($query) use ($group, $roleNames, $groupSlugs) {
                    $query->whereHas('roles', function ($q) use ($roleNames) {
                        $q->whereIn('name', $roleNames);
                    });

                    // Akses tambahan per-menu (slug spesifik).
                    foreach ($groupSlugs as $slug) {
                        $query->orWhereJsonContains('additional_access', $slug);
                    }

                    // Backward-compat: jika masih menyimpan key grup.
                    $query->orWhereJsonContains('additional_access', $group);
                })
                ->get();

            if ($users->isEmpty()) {
                return;
            }

            Notification::send($users, new AdminNotification($payload));
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * Kirim ke satu user spesifik.
     */
    public static function toUser(User $user, array $payload): void
    {
        try {
            $user->notify(new AdminNotification($payload));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
