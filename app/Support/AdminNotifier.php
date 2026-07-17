<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\AdminNotification;
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
            'konten'       => ['superadmin'],
            default        => [$group],
        };
    }

    public static function toGroup(string $group, array $payload): void
    {
        try {
            $roleNames = static::groupToRoleNames($group);

            $users = User::where('is_active', true)
                ->where(function ($query) use ($group, $roleNames) {
                    $query->whereHas('roles', function ($q) use ($roleNames) {
                        $q->whereIn('name', $roleNames);
                    })
                    ->orWhereJsonContains('additional_access', $group);
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
