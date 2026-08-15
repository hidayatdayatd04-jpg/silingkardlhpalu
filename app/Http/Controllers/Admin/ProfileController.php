<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('admin.profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'photo'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'name.required'     => 'Nama wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah dipakai pengguna lain.',
            'email.unique'      => 'Email sudah dipakai pengguna lain.',
            'photo.image'       => 'File foto harus berupa gambar.',
            'photo.max'         => 'Ukuran foto maksimal 2MB.',
        ]);

        $old = $user->only(['name', 'username', 'email', 'photo_path']);

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'] ?? null;

        if ($request->boolean('photo_remove') && $user->photo_path) {
            // Hapus foto profil.
            if (Storage::disk('public')->exists($user->photo_path)) {
                Storage::disk('public')->delete($user->photo_path);
            }
            $user->photo_path = null;
        } elseif ($request->hasFile('photo')) {
            // Ganti foto lama bila ada.
            if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
                Storage::disk('public')->delete($user->photo_path);
            }
            $user->photo_path = $request->file('photo')->store('avatars', 'public');
        }

        $user->save();

        ActivityLogger::log('updated', 'Profil: '.$user->name, 'profile', $old, $user->only(['name', 'username', 'email', 'photo_path']), $user);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', 'min:8'],
        ], [
            'current_password.required'         => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini salah.',
            'password.required'                 => 'Password baru wajib diisi.',
            'password.confirmed'                => 'Konfirmasi password baru tidak cocok.',
            'password.min'                      => 'Password baru minimal 8 karakter.',
        ]);

        $user->password = Hash::make($request->input('password'));
        // Simpan petunjuk password (plaintext) agar bisa dilihat di detail pengguna.
        $user->password_hint = $request->input('password');
        $user->save();

        ActivityLogger::log('updated', 'Ubah password: '.$user->name, 'profile', null, null, $user);

        // Logout agar pengguna login kembali dengan password baru
        // (deman keamanan sekaligus bukti bahwa password benar-benar berubah).
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Password berhasil diubah. Silakan login kembali menggunakan password baru Anda.');
    }
}
