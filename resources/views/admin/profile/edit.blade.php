@extends('layouts.admin')

@section('title', 'Profil Saya - Admin DLH')
@section('heading', 'Profil Saya')

@section('content')
@php
    $initials = \Illuminate\Support\Str::of($user->name)->explode(' ')->map(fn($w) => mb_substr($w,0,1))->take(2)->implode('');
    $roleColor = $user->roleColor();
    $roleBadge = [
        'danger'  => 'bg-rose-500/15 text-rose-100 ring-1 ring-inset ring-rose-300/30',
        'info'    => 'bg-sky-500/15 text-sky-100 ring-1 ring-inset ring-sky-300/30',
        'warning' => 'bg-amber-500/15 text-amber-100 ring-1 ring-inset ring-amber-300/30',
        'gray'    => 'bg-white/15 text-white ring-1 ring-inset ring-white/25',
        'success' => 'bg-emerald-500/15 text-emerald-100 ring-1 ring-inset ring-emerald-300/30',
    ][$roleColor] ?? 'bg-white/15 text-white ring-1 ring-inset ring-white/25';

    // Kelompokkan error agar tiap form menampilkan alert di konteksnya.
    $accountErrorKeys  = ['name', 'username', 'email', 'photo'];
    $passwordErrorKeys = ['current_password', 'password', 'password_confirmation'];
    $accountErrors  = [];
    $passwordErrors = [];
    foreach ($accountErrorKeys as $key) {
        if ($errors->has($key)) {
            foreach ((array) $errors->get($key) as $msg) {
                $accountErrors[] = $msg;
            }
        }
    }
    foreach ($passwordErrorKeys as $key) {
        if ($errors->has($key)) {
            foreach ((array) $errors->get($key) as $msg) {
                $passwordErrors[] = $msg;
            }
        }
    }
@endphp

<div class="admin-profile" x-data="{ preview: '{{ $user->photoUrl() ?? '' }}', fileName: '', removePhoto: false }">
    {{-- ============================================================ --}}
    {{-- HERO BANNER --}}
    {{-- ============================================================ --}}
    <div class="admin-dashboard-hero relative overflow-hidden rounded-3xl p-6 text-white sm:p-8">

        <div class="relative flex flex-col items-center gap-5 sm:flex-row sm:items-center">
            <div class="group relative shrink-0">
                @if($user->photoUrl())
                    <img x-show="preview" src="{{ $user->photoUrl() }}" x-bind:src="preview || undefined" alt="{{ $user->name }}"
                        class="size-24 rounded-3xl object-cover ring-4 ring-white/40 shadow-xl transition duration-300 group-hover:scale-[1.03] sm:size-28">
                    <div x-show="!preview" x-cloak
                        class="grid size-24 place-items-center rounded-3xl bg-white/20 text-3xl font-bold ring-4 ring-white/40 shadow-xl backdrop-blur-sm transition duration-300 sm:size-28">
                        {{ $initials }}
                    </div>
                @else
                    <img x-show="preview" x-cloak x-bind:src="preview || undefined" alt="{{ $user->name }}"
                        class="size-24 rounded-3xl object-cover ring-4 ring-white/40 shadow-xl transition duration-300 group-hover:scale-[1.03] sm:size-28">
                    <div x-show="!preview"
                        class="grid size-24 place-items-center rounded-3xl bg-white/20 text-3xl font-bold ring-4 ring-white/40 shadow-xl backdrop-blur-sm transition duration-300 sm:size-28">
                        {{ $initials }}
                    </div>
                @endif
                <div class="absolute -bottom-1 -right-1 grid size-9 place-items-center rounded-full bg-white text-brand-600 shadow-lg ring-2 ring-brand-500">
                    <x-admin.icon name="user" :size="16" />
                </div>
            </div>

            <div class="text-center sm:text-left">
                <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                    <span class="inline-flex items-center gap-1.5 rounded-full {{ $roleBadge }} px-3 py-1 text-xs font-semibold backdrop-blur-sm">
                        <x-admin.icon name="shield" :size="12" />
                        {{ $user->roleLabel() }}
                    </span>
                    @if($user->email)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white/90 ring-1 ring-inset ring-white/20 backdrop-blur-sm">
                            <x-admin.icon name="mail" :size="12" />
                            {{ $user->email }}
                        </span>
                    @endif
                </div>
                <h1 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">{{ $user->name }}</h1>
                <p class="mt-1 text-sm font-medium text-white/80">{{ '@' . $user->username }}</p>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SUCCESS MESSAGE --}}
    {{-- ============================================================ --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
            x-init="setTimeout(() => show = false, 5000)"
            class="mt-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3.5 text-sm font-medium text-emerald-700 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
            <div class="grid size-8 shrink-0 place-items-center rounded-xl bg-emerald-100 dark:bg-emerald-500/20">
                <x-admin.icon name="check-circle" :size="18" />
            </div>
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="ml-auto shrink-0 text-emerald-400 hover:text-emerald-600">
                <x-admin.icon name="x" :size="16" />
            </button>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- ERROR PROFIL --}}
    {{-- ============================================================ --}}
    @if(count($accountErrors))
        <x-admin.alert-error title="Profil belum tersimpan" :errors="$accountErrors" class="mt-6" />
    @endif

    {{-- ============================================================ --}}
    {{-- GRID: INFORMASI AKUN + KEAMANAN (sama tinggi & sejajar) --}}
    {{-- ============================================================ --}}
    <div class="mt-6 grid items-stretch gap-6 pb-28 lg:grid-cols-2">

        {{-- ---------- INFORMASI AKUN ---------- --}}
        <div class="lg:col-span-1">
            <div class="flex h-full flex-col rounded-3xl border border-slate-200/70 bg-white p-6 shadow-[0_12px_40px_rgba(15,23,42,0.06)] sm:p-7 dark:border-white/10 dark:bg-slate-900">
                <div class="mb-6 flex items-center gap-3">
                    <div class="grid size-11 place-items-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">
                        <x-admin.icon name="user" :size="22" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-ink-900 dark:text-white">Informasi Akun</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Perbarui foto, nama, username, dan email Anda.</p>
                    </div>
                </div>

                <form id="profile-account-form" method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="flex flex-1 flex-col gap-6">
                    @csrf
                    @method('PUT')

                    {{-- Photo Upload --}}
                    <div class="flex flex-col items-center gap-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50/60 p-5 transition hover:border-brand-400 hover:bg-brand-50/40 sm:flex-row dark:border-white/15 dark:bg-white/5 dark:hover:border-brand-400/60">
                        @if($user->photoUrl())
                            <img x-show="preview" src="{{ $user->photoUrl() }}" x-bind:src="preview || undefined" alt="Foto profil"
                                class="size-20 rounded-2xl object-cover ring-2 ring-white shadow-md">
                            <div x-show="!preview" x-cloak
                                class="grid size-20 place-items-center rounded-2xl bg-gradient-to-br from-brand-100 to-emerald-100 text-xl font-bold text-brand-600">
                                {{ $initials }}
                            </div>
                        @else
                            <img x-show="preview" x-cloak x-bind:src="preview || undefined" alt="Foto profil"
                                class="size-20 rounded-2xl object-cover ring-2 ring-white shadow-md">
                            <div x-show="!preview"
                                class="grid size-20 place-items-center rounded-2xl bg-gradient-to-br from-brand-100 to-emerald-100 text-xl font-bold text-brand-600">
                                {{ $initials }}
                            </div>
                        @endif
                        <div class="flex-1 text-center sm:text-left">
                            <label class="inline-flex cursor-pointer">
                                <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-ink-700 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10">
                                    <x-admin.icon name="upload" :size="16" /> Pilih Foto Baru
                                </span>
                                <input type="file" name="photo" accept="image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif" class="hidden" x-ref="photoInput"
                                    x-on:change="preview = URL.createObjectURL($event.target.files[0]); fileName = $event.target.files[0].name; removePhoto = false">
                            </label>
                            <button type="button" x-show="preview" @click="removePhoto = true; preview = ''; fileName = ''; if ($refs.photoInput) $refs.photoInput.value = ''"
                                class="ml-2 inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-600 shadow-sm transition hover:bg-rose-100 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300 dark:hover:bg-rose-500/20">
                                <x-admin.icon name="trash" :size="16" /> Hapus Foto
                            </button>
                            <input type="hidden" name="photo_remove" :value="removePhoto ? 1 : 0">
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                <span x-text="fileName || 'JPG, PNG, WEBP, AVIF, atau HEIC. Maksimal 5MB.'"></span>
                            </p>
                            @error('photo')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Form Fields --}}
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-ink-700 dark:text-slate-200">Nama Lengkap</label>
                            <input name="name" value="{{ old('name', $user->name) }}" required
                                class="admin-field dark:border-white/10 dark:bg-slate-800/60 dark:text-white"
                                placeholder="Masukkan nama lengkap">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-ink-700 dark:text-slate-200">Username</label>
                            <input name="username" value="{{ old('username', $user->username) }}" required
                                class="admin-field dark:border-white/10 dark:bg-slate-800/60 dark:text-white"
                                placeholder="Masukkan username">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-semibold text-ink-700 dark:text-slate-200">Email</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <x-admin.icon name="mail" :size="16" class="text-slate-400" />
                                </div>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                    class="admin-field py-3 pl-11 dark:border-white/10 dark:bg-slate-800/60 dark:text-white"
                                    placeholder="contoh@email.com">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ---------- KEAMANAN ---------- --}}
        <div class="lg:col-span-1">
            <div class="flex h-full flex-col rounded-3xl border border-slate-200/70 bg-white p-6 shadow-[0_12px_40px_rgba(15,23,42,0.06)] dark:border-white/10 dark:bg-slate-900">
                <div class="mb-6 flex items-center gap-3">
                    <div class="grid size-11 place-items-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300">
                        <x-admin.icon name="lock" :size="22" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-ink-900 dark:text-white">Keamanan</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Ubah password akun Anda secara berkala.</p>
                    </div>
                </div>

                @if(count($passwordErrors))
                    <x-admin.alert-error title="Password belum dapat diubah" :errors="$passwordErrors" class="mb-5" />
                @endif

                <form method="POST" action="{{ route('admin.profile.password') }}"
                    class="flex flex-1 flex-col gap-4"
                    x-data="{
                        revealCurrent: false, revealNew: false, revealConfirm: false, cpLocked: true, newPassword: '',
                        strength() {
                            const v = this.newPassword || '';
                            if (!v) return 0;
                            let s = 0;
                            if (v.length >= 8) s++;
                            if (v.length >= 12) s++;
                            if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
                            if (/\d/.test(v)) s++;
                            if (/[^A-Za-z0-9]/.test(v)) s++;
                            return Math.min(s, 4);
                        },
                        strengthLabel() {
                            return ['', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'][this.strength()] || '';
                        }
                    }">
                    @csrf
                    @method('PUT')

                    {{-- Decoy (off-screen) untuk menyerap autofill password-manager.
                         Tanpa ini, browser sering mengisi field "Password Saat Ini"
                         dengan password lama yang tersimpan sehingga validasi gagal
                         padahal password yang diketik sudah benar. --}}
                    <div aria-hidden="true" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;">
                        <input type="text" name="current_password_decoy" autocomplete="username" tabindex="-1" readonly>
                        <input type="password" name="current_password_autofill" autocomplete="current-password" tabindex="-1" readonly>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-ink-700 dark:text-slate-200">Password Saat Ini</label>
                        <div class="relative">
                            <input :type="revealCurrent ? 'text' : 'password'"
                                name="current_password" required autocomplete="new-password"
                                x-bind:readonly="cpLocked" @focus="cpLocked = false"
                                class="admin-field pr-11 dark:border-white/10 dark:bg-slate-800/60 dark:text-white {{ $errors->has('current_password') ? 'field-shake border-rose-400 focus:border-rose-500 focus:ring-rose-100' : '' }}"
                                placeholder="Ketik password saat ini">
                            <button type="button" @click="revealCurrent = !revealCurrent"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-brand-600 focus:outline-none focus-visible:text-brand-600 dark:text-slate-500 dark:hover:text-brand-300"
                                :aria-label="revealCurrent ? 'Sembunyikan password' : 'Tampilkan password'">
                                <x-admin.icon name="eye" :size="18" x-show="!revealCurrent" />
                                <x-admin.icon name="eye-off" :size="18" x-show="revealCurrent" />
                            </button>
                        </div>
                        @error('current_password')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="relative">
                        <div class="absolute -top-px left-0 right-0 h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent dark:via-white/10"></div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-ink-700 dark:text-slate-200">Password Baru</label>
                        <div class="relative">
                            <input :type="revealNew ? 'text' : 'password'" name="password" required autocomplete="new-password"
                                x-model="newPassword"
                                class="admin-field pr-11 dark:border-white/10 dark:bg-slate-800/60 dark:text-white {{ $errors->has('password') ? 'field-shake border-rose-400 focus:border-rose-500 focus:ring-rose-100' : '' }}"
                                placeholder="Minimal 8 karakter">
                            <button type="button" @click="revealNew = !revealNew"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-brand-600 focus:outline-none focus-visible:text-brand-600 dark:text-slate-500 dark:hover:text-brand-300"
                                :aria-label="revealNew ? 'Sembunyikan password' : 'Tampilkan password'">
                                <x-admin.icon name="eye" :size="18" x-show="!revealNew" />
                                <x-admin.icon name="eye-off" :size="18" x-show="revealNew" />
                            </button>
                        </div>
                        {{-- Password strength meter --}}
                        <div class="mt-2 flex items-center gap-2">
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                <div class="h-full rounded-full transition-all duration-300"
                                    :class="{
                                        'bg-rose-500': strength() <= 1,
                                        'bg-amber-500': strength() === 2,
                                        'bg-yellow-400': strength() === 3,
                                        'bg-emerald-500': strength() >= 4
                                    }"
                                    :style="'width: ' + (strength() / 4 * 100) + '%'"></div>
                            </div>
                            <span class="w-16 text-right text-xs font-semibold"
                                :class="{
                                    'text-rose-500': strength() <= 1,
                                    'text-amber-500': strength() === 2,
                                    'text-yellow-500': strength() === 3,
                                    'text-emerald-600': strength() >= 4
                                }"
                                x-text="strengthLabel()"></span>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-ink-700 dark:text-slate-200">Konfirmasi Password Baru</label>
                        <div class="relative">
                            <input :type="revealConfirm ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password"
                                class="admin-field pr-11 dark:border-white/10 dark:bg-slate-800/60 dark:text-white {{ $errors->has('password_confirmation') ? 'field-shake border-rose-400 focus:border-rose-500 focus:ring-rose-100' : '' }}"
                                placeholder="Ulangi password baru">
                            <button type="button" @click="revealConfirm = !revealConfirm"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-brand-600 focus:outline-none focus-visible:text-brand-600 dark:text-slate-500 dark:hover:text-brand-300"
                                :aria-label="revealConfirm ? 'Sembunyikan password' : 'Tampilkan password'">
                                <x-admin.icon name="eye" :size="18" x-show="!revealConfirm" />
                                <x-admin.icon name="eye-off" :size="18" x-show="revealConfirm" />
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-auto flex items-center justify-start border-t border-slate-100 pt-4 dark:border-white/10">
                        <x-admin.button variant="primary" type="submit" icon="lock" class="rounded-xl">
                            Ubah Password
                        </x-admin.button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- FLOATING ACTION BUTTON (Simpan Perubahan) --}}
    {{-- ============================================================ --}}
    <div class="fixed bottom-6 right-6 z-40">
        <x-admin.button variant="primary" type="submit" form="profile-account-form" icon="check"
            class="rounded-full px-5 py-3 shadow-lg shadow-brand-600/30 ring-1 ring-white/20 transition hover:shadow-xl hover:shadow-brand-600/40">
            Simpan Perubahan
        </x-admin.button>
    </div>
</div>
@endsection
