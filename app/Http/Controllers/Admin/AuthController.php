<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([$field => $credentials['login'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'login' => 'Username/email atau password tidak sesuai.',
            ]);
        }

        $request->session()->regenerate();

        if (! AdminAccess::hasAnyPanelRole($request->user())) {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => 'Akun ini belum aktif atau tidak punya akses admin.',
            ]);
        }

        // Arahkan admin SELALU ke halaman panel admin. redirect()->intended()
        // menyimpan URL asal (mis. halaman publik '/' yang sedang dalam mode
        // pemeliharaan), sehingga tanpa pengecekan ini admin bisa terlempar
        // ke halaman pemeliharaan setelah login. Kita validasi target-nya
        // agar hanya URL bertipe /admin yang diperbolehkan.
        $intended = redirect()->intended(route('admin.dashboard'))->getTargetUrl();
        $intendedPath = parse_url($intended, PHP_URL_PATH) ?: '/';

        if (! str_starts_with($intendedPath, '/admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect($intended);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
