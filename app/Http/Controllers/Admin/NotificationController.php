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

        return view('admin.notifications.index', [
            'notifications' => $user->notifications()->paginate(20),
            'unreadCount'   => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Endpoint JSON untuk polling bell (fetch tiap 30 detik).
     */
    public function poll(Request $request)
    {
        $user = auth()->user();

        $recent = $user->notifications()->latest()->take(10)->get()->map(function ($n) {
            $data = $n->data;

            return [
                'id'       => $n->id,
                'title'    => $data['title'] ?? 'Notifikasi',
                'message'  => $data['message'] ?? '',
                'icon'     => $data['icon'] ?? 'bell',
                'color'    => $data['color'] ?? 'emerald',
                'href'     => $data['href'] ?? null,
                'read'     => $n->read_at !== null,
                'time'     => $n->created_at?->diffForHumans(),
            ];
        });

        return response()->json([
            'unread'        => $user->unreadNotifications()->count(),
            'notifications' => $recent,
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
