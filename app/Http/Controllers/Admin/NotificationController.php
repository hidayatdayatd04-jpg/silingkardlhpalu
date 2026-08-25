<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\AdminNotificationFeed;
use App\Support\Admin\AdminRegistry;
use App\Support\Admin\AdminUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    /**
     * Halaman daftar semua notifikasi.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $notifications = $user->notifications()->latest()->paginate(20);

        // Filter notifikasi berdasarkan module/akses role di pagination.
        // Module disimpan sebagai slug resource (mis. 'artikel'), jadi daftar
        // yang diizinkan memuat key grup + slug item dalam grup tersebut.
        $allowedModules = AdminRegistry::allowedNotificationModules($user->accessibleGroups());

        $filteredNotifications = $notifications
            ->filter(function ($n) use ($allowedModules) {
                $module = $n->data['module'] ?? 'system';
                return in_array($module, $allowedModules);
            })
            // Baris DB lama bisa memuat href '/admin/...' — tulis ulang ke prefix aktif.
            ->each(function ($n) {
                $data = $n->data;
                $data['href'] = AdminUrl::normalizeLegacyHref($data['href'] ?? null);
                $n->data = $data;
            });

        $notifications->setCollection($filteredNotifications);

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'unreadCount'   => $user->unreadNotifications()->get()
                ->filter(fn ($n) => in_array($n->data['module'] ?? 'system', $allowedModules))
                ->count(),
        ]);
    }

    /**
     * Endpoint JSON untuk polling bell (fetch tiap 30 detik).
     *
     * Memakai cache feed yang sama dengan topbar (AdminNotificationFeed),
     * sehingga polling tidak memicu query DB remote tiap 30 detik
     * per tab — cukup saat cache expired (5 menit).
     */
    public function poll(Request $request)
    {
        $user = auth()->user();

        $data = AdminNotificationFeed::forUser($user);

        return response()->json([
            'unread'        => $data['count'],
            'notifications' => $data['notifications'],
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification && $notification->read_at === null) {
            $notification->markAsRead();
            AdminNotificationFeed::forget(auth()->user());
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'unread' => auth()->user()->unreadNotifications()->count()]);
        }

        return back();
    }

    public function markAllAsRead(Request $request)
    {
        auth()->user()->unreadNotifications->markAsRead();
        AdminNotificationFeed::forget(auth()->user());

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'unread' => 0]);
        }

        return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }

    /**
     * Hapus satu notifikasi milik akun yang sedang login.
     */
    public function destroy(Request $request, string $id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->whereKey($id)->firstOrFail();
        $notification->delete();

        AdminNotificationFeed::forget($user);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'unread' => $user->unreadNotifications()->count()]);
        }

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }
}
