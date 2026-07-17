<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ActivityLogController extends Controller
{
    protected function authorizeSuperadmin(): void
    {
        if (! auth()->user()?->isSuperadmin()) {
            throw new AccessDeniedHttpException('Hanya Kepala Bidang (superadmin) yang dapat mengakses log aktivitas.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeSuperadmin();

        $query = ActivityLog::query()->with('user')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        if ($search = trim($request->string('q')->toString())) {
            $query->where(function ($q) use ($search) {
                $q->where('subject_label', 'like', '%'.$search.'%')
                    ->orWhere('user_name', 'like', '%'.$search.'%')
                    ->orWhere('ip_address', 'like', '%'.$search.'%');
            });
        }

        return view('admin.activity-log.index', [
            'logs'    => $query->paginate(25)->withQueryString(),
            'users'   => User::orderBy('name')->get(['id', 'name']),
            'events'  => $this->eventOptions(),
            'modules' => ActivityLog::query()->distinct()->orderBy('module')->pluck('module')->filter()->values(),
            'filters' => [
                'user_id'   => $request->input('user_id'),
                'event'     => $request->input('event'),
                'module'    => $request->input('module'),
                'date_from' => $request->input('date_from'),
                'date_to'   => $request->input('date_to'),
                'q'         => $request->input('q'),
            ],
        ]);
    }

    protected function eventOptions(): array
    {
        return [
            'created'  => 'Tambah',
            'updated'  => 'Ubah',
            'deleted'  => 'Hapus',
            'restored' => 'Pulihkan',
            'login'    => 'Masuk',
            'logout'   => 'Keluar',
            'exported' => 'Ekspor',
            'imported' => 'Impor',
            'backup'   => 'Backup',
            'restore'  => 'Restore',
        ];
    }
}
