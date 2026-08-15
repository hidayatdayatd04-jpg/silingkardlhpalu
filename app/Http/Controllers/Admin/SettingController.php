<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $estimatedAt = Setting::get('maintenance_estimated_at');

        return view('admin.settings.edit', [
            'user'         => auth()->user(),
            'isSuperadmin' => auth()->user()->isSuperadmin(),
            'maintenanceEnabled'    => (bool) Setting::get('maintenance_enabled', false),
            'maintenanceEstimatedAt' => $estimatedAt ? Carbon::parse($estimatedAt)->format('Y-m-d\TH:i') : '',
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'maintenance_enabled' => ['nullable', 'string', 'in:1,0'],
            'maintenance_estimated_at' => ['nullable', 'string'],
        ]);

        // Setting global — hanya superadmin
        if ($user->isSuperadmin()) {
            $maintenanceValue = ($validated['maintenance_enabled'] ?? '0') === '1';
            $estimatedAt = ! empty($validated['maintenance_estimated_at'])
                ? Carbon::parse($validated['maintenance_estimated_at'])->format('Y-m-d H:i:s')
                : null;

            Setting::put('maintenance_enabled', $maintenanceValue, 'system');
            Setting::put('maintenance_estimated_at', $estimatedAt, 'system');
        }

        ActivityLogger::log('updated', 'Pengaturan diperbarui', 'settings', null, ['settings' => $validated], $user);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
