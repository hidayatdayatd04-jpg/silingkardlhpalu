<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Admin DLH Kota Palu</title>
    <meta name="description" content="Masuk ke panel admin SILINGKAR — Sistem Informasi Lingkungan Hidup Dinas Lingkungan Hidup Kota Palu.">
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    @include('partials.web-fonts')
    {{-- Vite bundle memuat Alpine self-hosted (resources/js/alpine.js) — tanpa CDN. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="admin-login min-h-screen text-ink-900 antialiased" data-alpine-bootstrap>
    <main class="admin-login-shell grid min-h-screen lg:grid-cols-[1.1fr_0.9fr]">
        {{-- â•â•â•â•â•â•â•â•â•â• Brand panel (kiri) â•â•â•â•â•â•â•â•â•â• --}}
        <section class="admin-login-brand relative hidden overflow-hidden px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="bg-grain pointer-events-none absolute inset-0 opacity-[0.05]"></div>

            {{-- Floating decorative shapes (reduced-motion aman) --}}
            <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                <div class="login-float login-float--1 absolute -left-10 top-20 size-40 rounded-full bg-brand-400/10 blur-2xl"></div>
                <div class="login-float login-float--2 absolute right-10 top-1/2 size-56 rounded-full bg-info-400/10 blur-3xl"></div>
                <div class="login-float login-float--3 absolute bottom-10 left-1/3 size-32 rounded-full bg-brand-300/10 blur-2xl"></div>
            </div>

            <div class="relative flex items-center gap-3">
                <img src="{{ asset('assets/images/logo-web.webp') }}" alt="Logo Kota Palu" width="320" height="337" class="h-20 w-auto">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-brand-200">DLH Kota Palu</p>
                    <p class="text-xl font-bold">Panel Administrasi</p>
                </div>
            </div>

            <div class="relative max-w-xl">
                <p class="mb-5 text-sm font-semibold uppercase tracking-[0.14em] text-brand-200">Portal Pegawai</p>
                <h1 class="text-3xl font-bold leading-tight xl:text-4xl">Dinas Lingkungan Hidup Kota Palu</h1>
                <p class="mt-5 text-lg leading-8 text-white/80">Kelola permohonan layanan, pengaduan masyarakat, data persampahan, RTH, tata penataan, dan publikasi informasi dalam satu panel kerja terpadu.</p>

                <div class="mt-10 max-w-md rounded-xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <p class="text-sm leading-6 text-white/70">Masuk dengan akun pegawai yang telah diberikan oleh pengelola sistem. Jika belum memiliki akun atau lupa password, hubungi Administrator Utama.</p>
                </div>
            </div>

            <p class="relative text-sm text-white/60">&copy; {{ date('Y') }} Dinas Lingkungan Hidup Kota Palu</p>
        </section>

        {{-- ══════════ Form panel (kanan) ══════════ --}}
        <section class="flex items-center justify-center px-5 py-10">
            <div class="login-card w-full max-w-md">
                {{-- Mobile brand --}}
                <div class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                    <img src="{{ asset('assets/images/logo-web.webp') }}" alt="Logo Kota Palu" width="320" height="337" class="h-16 w-auto">
                    <div>
                        <p class="text-sm font-bold text-brand-700">DLH Kota Palu</p>
                        <p class="text-xs text-slate-500">Panel Administrasi</p>
                    </div>
                </div>

                <div class="admin-login-form-surface rounded-2xl border bg-white p-8 dark:bg-[#102019]">
                    <div class="mb-8">
                        <div class="grid size-12 place-items-center rounded-xl bg-brand-50 text-brand-600">
                            <x-admin.icon name="lock" :size="24" />
                        </div>
                        <h1 class="mt-4 text-h1 font-bold tracking-tight text-ink-950">Selamat Datang</h1>
                        <p class="mt-2 text-sm text-slate-500">Masuk untuk mengelola data dan layanan DLH Kota Palu.</p>
                    </div>

                    @if (session('error'))
                        <div class="mb-5 flex items-start gap-3 rounded-xl border border-danger-200 bg-danger-50 px-4 py-3 text-sm font-semibold text-danger-800">
                            <x-admin.icon name="alert-circle" :size="18" class="mt-px shrink-0" />
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                            <x-admin.icon name="check-circle" :size="18" class="mt-px shrink-0" />
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5" x-data="{ showPassword: false, submitting: false }" x-on:submit="submitting = true">
                        @csrf

                        <x-admin.form-input
                            name="login"
                            label="Username / Email"
                            icon="user"
                            :value="old('login')"
                            autocomplete="username"
                            :error="$errors->first('login')"
                            x-ref="loginField"
                            autofocus
                        />

                        {{-- Password + toggle --}}
                        <div class="space-y-1.5">
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <x-admin.icon name="lock" :size="18" />
                                </div>
                                <input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    name="password"
                                    autocomplete="current-password"
                                    placeholder=" "
                                    @if($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif
                                    class="peer block w-full rounded-lg border bg-white pb-2 pl-11 pr-11 pt-5 text-sm font-medium text-ink-900 shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-slate-400 focus:ring-4 dark:bg-[#13261e] dark:text-white {{ $errors->has('password') ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-100' : 'border-slate-300 hover:border-slate-400 focus:border-brand-500 focus:ring-brand-100' }}"
                                >
                                <label for="password" class="pointer-events-none absolute left-11 top-1.5 origin-left text-xs font-semibold text-slate-500 transition-all duration-150 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-sm peer-placeholder-shown:font-medium peer-focus:top-1.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:text-brand-700">
                                    Password
                                </label>
                                <button type="button" x-on:click="showPassword = !showPassword" class="admin-icon-button absolute inset-y-0 right-0 grid w-11 place-items-center text-slate-400 transition-colors duration-150 hover:text-slate-600" :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                                    <x-admin.icon x-show="!showPassword" name="eye" :size="18" />
                                    <span x-show="showPassword" x-cloak><x-admin.icon name="eye" :size="18" class="opacity-50" /></span>
                                </button>
                            </div>
                            @error('password')
                                <p id="password-error" role="alert" class="flex items-center gap-1 text-xs font-semibold text-danger-600">
                                    <x-admin.icon name="alert-circle" :size="14" /> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                                <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 transition focus:ring-brand-500">
                                Ingat saya
                            </label>
                            <span class="text-sm font-semibold text-slate-500" title="Hubungi Administrator Utama untuk reset password">Lupa password?</span>
                        </div>

                        <button type="submit" :disabled="submitting" class="group flex w-full items-center justify-center gap-2 rounded-lg bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-[0_2px_5px_rgb(5_120_83_/_0.25)] transition-[background-color,transform,box-shadow] duration-150 hover:bg-brand-800 focus:outline-none focus:ring-4 focus:ring-brand-100 active:scale-[0.99] disabled:opacity-70">
                            <span x-show="submitting" x-cloak class="size-4 animate-spin rounded-full border-2 border-white/35 border-t-white" aria-hidden="true"></span>
                            <span x-text="submitting ? 'Memproses...' : 'Masuk'"></span>
                            <x-admin.icon x-show="!submitting" name="chevron-right" :size="18" class="transition group-hover:translate-x-0.5" />
                        </button>
                    </form>
                </div>

                <p class="mt-6 text-center text-xs text-slate-400">
                    &copy; {{ date('Y') }} Dinas Lingkungan Hidup Kota Palu
                </p>
            </div>
        </section>
    </main>

    @livewireScripts
</body>

</html>
