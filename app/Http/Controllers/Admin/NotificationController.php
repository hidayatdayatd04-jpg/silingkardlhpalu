<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Halaman daftar semua notifikasi.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $allowedGroups = $user->accessibleGroups();

        $notifications = $user->notifications()->latest()->paginate(20);

        // Filter notifikasi berdasarkan module/akses role di pagination.
        $moduleGroupMap = [
            'pengendalian' => 'pengendalian',
            'sampah-lb3' => 'sampah-lb3',
            'rth' => 'rth',
            'tata-penataan' => 'tata-penataan',
        ];

        $allowedModules = collect($allowedGroups)->map(function ($g) use ($moduleGroupMap) {
            return $moduleGroupMap[$g] ?? $g;
        })->push('system')->push('global')->all();

        $filteredNotifications = $notifications->filter(function ($n) use ($allowedModules) {
            $module = $n->data['module'] ?? 'system';
            return in_array($module, $allowedModules);
        });

        $notifications->setCollection($filteredNotifications);

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'unreadCount'   => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Endpoint JSON untuk polling bell (fetch tiap 30 detik).
     */
    public function poll(Request $request)
    {
        $user = auth()->user();
        $allowedGroups = $user->accessibleGroups();

        $recent = $user->notifications()->latest()->take(20)->get()->map(function ($n) {
            $data = $n->data;

            return [
                'id'       => $n->id,
                'title'    => $data['title'] ?? 'Notifikasi',
                'message'  => $data['message'] ?? '',
                'icon'     => $data['icon'] ?? 'bell',
                'color'    => $data['color'] ?? 'emerald',
                'href'     => $data['href'] ?? null,
                'read'     => $n->read_at !== null,
                'module'   => $data['module'] ?? 'system',
            ];
        });

        // Filter berdasarkan module/akses role.
        $moduleGroupMap = [
            'pengendalian' => 'pengendalian',
            'sampah-lb3' => 'sampah-lb3',
            'rth' => 'rth',
            'tata-penataan' => 'tata-penataan',
        ];

        $allowedModules = collect($allowedGroups)->map(function ($g) use ($moduleGroupMap) {
            return $moduleGroupMap[$g] ?? $g;
        })->push('system')->push('global')->all();

        $filtered = $recent->filter(function ($n) use ($allowedModules) {
            return in_array($n['module'], $allowedModules);
        });

        return response()->json([
            'unread'        => $user->unreadNotifications()->count(),
            'notifications' => $filtered->values(),
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification && $notification->read_at === null) {
            $notification->markAsRead();
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'unread' => auth()->user()->unreadNotifications()->count()]);
        }

        return back();
    }

    public function markAllAsRead(Request $request)
    {
        auth()->user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'unread' => 0]);
        }

        return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }
}
