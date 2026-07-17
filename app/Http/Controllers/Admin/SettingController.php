<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', [
            'user'         => auth()->user(),
            'isSuperadmin' => auth()->user()->isSuperadmin(),
            'app'          => [
                'app_name'    => Setting::get('app_name', config('app.name')),
                'contact_email' => Setting::get('contact_email', ''),
                'contact_phone' => Setting::get('contact_phone', ''),
            ],
            'ikmFingerprintEnabled' => Setting::get('ikm_fingerprint_enabled', true),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'per_page'    => ['nullable', 'integer', 'min:5', 'max:100'],
            'locale'      => ['nullable', 'in:id,en'],
            // global (superadmin)
            'app_name'      => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'ikm_fingerprint_enabled' => ['nullable', 'string', 'in:1,0'],
        ]);

        // Preferensi per-user
        $prefs = $user->preferences ?? [];
        $prefs['per_page'] = $validated['per_page'] ?? ($prefs['per_page'] ?? 15);
        $prefs['locale'] = $validated['locale'] ?? ($prefs['locale'] ?? 'id');
        $user->preferences = $prefs;
        $user->save();

        // Setting global — hanya superadmin
        if ($user->isSuperadmin()) {
            if ($request->filled('app_name')) {
                Setting::put('app_name', $validated['app_name'], 'general');
            }
            Setting::put('contact_email', $validated['contact_email'] ?? '', 'contact');
            Setting::put('contact_phone', $validated['contact_phone'] ?? '', 'contact');
            $fingerprintValue = ($validated['ikm_fingerprint_enabled'] ?? '1') === '1';
            Setting::put('ikm_fingerprint_enabled', $fingerprintValue, 'survey');
        }

        ActivityLogger::log('updated', 'Pengaturan diperbarui', 'settings', null, ['preferences' => $prefs], $user);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
