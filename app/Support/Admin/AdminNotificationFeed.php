<?php

namespace App\Support\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class AdminNotificationFeed
{
    /**
     * TTL cache untuk feed topbar & polling. Notifikasi cukup segar untuk UI,
     * dan cache ini mencegah polling (tiap 30 detik per tab) menghantam DB
     * remote berulang kali.
     */
    public const CACHE_MINUTES = 5;

    /**
     * Ambil feed notifikasi topbar untuk user (dari cache).
     *
     * @return array{notifications: \Illuminate\Support\Collection, count: int}
     */
    public static function forUser(User $user): array
    {
        return Cache::remember(
            static::cacheKey($user),
            now()->addMinutes(self::CACHE_MINUTES),
            fn () => static::build($user)
        );
    }

    public static function cacheKey(User $user): string
    {
        return 'admin:notifications:' . $user->id;
    }

    /**
     * Susun feed dari DB — hanya dipanggil saat cache kosong/expired.
     *
     * @return array{notifications: \Illuminate\Support\Collection, count: int}
     */
    protected static function build(User $user): array
    {
        // Observer menyimpan module sebagai slug resource (mis. 'artikel'),
        // jadi daftar modul yang diizinkan memuat key grup + slug itemnya.
        $allowedModules = AdminRegistry::allowedNotificationModules($user->accessibleGroups());

        $notifications = $user->notifications()->latest()->take(20)->get()->map(function ($n) use ($allowedModules) {
            $data = $n->data;
            $module = $data['module'] ?? 'system';

            // Tampilkan notifikasi jika module-nya sesuai dengan akses role,
            // atau jika module adalah 'system'/'global' yang selalu terlihat.
            if (! in_array($module, $allowedModules)) {
                return null;
            }

            return [
                'id' => $n->id,
                'icon' => $data['icon'] ?? 'bell',
                'color' => $data['color'] ?? 'emerald',
                'title' => $data['title'] ?? 'Notifikasi',
                'message' => $data['message'] ?? '',
                'time' => $n->created_at?->diffForHumans() ?? 'Baru',
                'href' => $data['href'] ?? '#',
                'read' => $n->read_at !== null,
            ];
        })->filter()->values();

        return [
            'notifications' => $notifications,
            'count' => $user->unreadNotifications()->get()
                ->filter(fn ($n) => in_array($n->data['module'] ?? 'system', $allowedModules))
                ->count(),
        ];
    }
}
