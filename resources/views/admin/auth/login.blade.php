<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Admin DLH Kota Palu</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    {{-- Vite bundle harus dimuat sebelum Alpine CDN start (urutan script deferred). --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen text-ink-900 antialiased">
    <main class="grid min-h-screen lg:grid-cols-[1.1fr_0.9fr]">
        {{-- â•â•â•â•â•â•â•â•â•â• Brand panel (kiri) â•â•â•â•â•â•â•â•â•â• --}}
        <section class="relative hidden overflow-hidden px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between" style="background: var(--gradient-header-hero);">
            <div class="bg-grain pointer-events-none absolute inset-0 opacity-[0.05]"></div>

            {{-- Floating decorative shapes (reduced-motion aman) --}}
            <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                <div class="login-float login-float--1 absolute -left-10 top-20 size-40 rounded-full bg-brand-400/10 blur-2xl"></div>
                <div class="login-float login-float--2 absolute right-10 top-1/2 size-56 rounded-full bg-info-400/10 blur-3xl"></div>
                <div class="login-float login-float--3 absolute bottom-10 left-1/3 size-32 rounded-full bg-brand-300/10 blur-2xl"></div>
            </div>

            <div class="relative flex items-center gap-3">
                <img src="{{ asset('assets/images/logo-web.png') }}" alt="Logo Kota Palu" class="h-20 w-auto">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-brand-200">DLH Kota Palu</p>
                    <p class="text-xl font-bold">Ruang Kendali Operasional</p>
                </div>
            </div>

            <div class="relative max-w-xl">
                <p class="mb-5 text-sm font-bold uppercase tracking-[0.22em] text-brand-200">Portal Admin</p>
                <h1 class="text-4xl font-extrabold leading-tight xl:text-5xl">Dinas Lingkungan Hidup Kota Palu</h1>
                <p class="mt-5 text-lg leading-8 text-white/80">Kelola permohonan, pengaduan, sampah, RTH, tata penataan, konten, dan pengguna dalam satu panel yang cepat &amp; rapi.</p>

                <div class="mt-10 grid grid-cols-3 gap-4">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <div class="grid size-8 place-items-center rounded-lg bg-brand-500/20 text-brand-300">
                            <x-admin.icon name="dashboard" :size="18" />
                        </div>
                        <p class="mt-3 text-2xl font-extrabold">20+</p>
                        <p class="text-xs text-white/50">Modul Data</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <div class="grid size-8 place-items-center rounded-lg bg-info-500/20 text-info-300">
                            <x-admin.icon name="eye" :size="18" />
                        </div>
                        <p class="mt-3 text-2xl font-extrabold">Real-time</p>
                        <p class="text-xs text-white/50">Monitoring</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <div class="grid size-8 place-items-center rounded-lg bg-warning-500/20 text-warning-300">
                            <x-admin.icon name="circle-check" :size="18" />
                        </div>
                        <p class="mt-3 text-2xl font-extrabold">Akurat</p>
                        <p class="text-xs text-white/50">Pelaporan</p>
                    </div>
                </div>
            </div>

            <p class="relative text-sm text-white/60">&copy; {{ date('Y') }} Dinas Lingkungan Hidup Kota Palu</p>
        </section>

        {{-- â•â•â•â•â•â•â•â•â•â• Form panel (kanan) â•â•â•â•â•â•â•â•â•â• --}}
        <section class="flex items-center justify-center px-5 py-10" style="background: var(--gradient-page);">
            <div class="login-card w-full max-w-md">
                {{-- Mobile brand --}}
                <div class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                    <img src="{{ asset('assets/images/logo-web.png') }}" alt="Logo Kota Palu" class="h-16 w-auto">
                    <div>
                        <p class="text-sm font-bold text-brand-700">DLH Kota Palu</p>
                        <p class="text-xs text-slate-500">Ruang Kendali Admin</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/80 bg-white p-8 shadow-[var(--shadow-modal)]">
                    <div class="mb-8">
                        <div class="grid size-12 place-items-center rounded-xl bg-brand-50 text-brand-600">
                            <x-admin.icon name="lock" :size="24" />
                        </div>
                        <h1 class="mt-4 text-h1 font-extrabold tracking-tight text-ink-950">Selamat Datang</h1>
                        <p class="mt-2 text-sm text-slate-500">Masuk untuk mengakses ruang kendali admin DLH Kota Palu.</p>
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
                                    @if($errors->has('password')) aria-invalid="true" @endif
                                    class="peer block w-full rounded-lg border pl-11 pr-11 pt-5 pb-2 text-sm font-medium text-ink-900 shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition placeholder:text-slate-400 focus:ring-4 {{ $errors->has('password') ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-100' : 'border-slate-300 hover:border-slate-400 focus:border-brand-500 focus:ring-brand-100' }} bg-white"
                                >
                                <label for="password" class="pointer-events-none absolute left-11 top-1.5 origin-left text-xs font-semibold text-slate-400 transition-all duration-150 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-sm peer-placeholder-shown:font-medium peer-focus:top-1.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:text-brand-600">
                                    Password
                                </label>
                                <button type="button" x-on:click="showPassword = !showPassword" class="absolute inset-y-0 right-0 grid w-11 place-items-center text-slate-400 transition hover:text-slate-600" :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                                    <x-admin.icon x-show="!showPassword" name="eye" :size="18" />
                                    <span x-show="showPassword" x-cloak><x-admin.icon name="eye" :size="18" class="opacity-50" /></span>
                                </button>
                            </div>
                            @error('password')
                                <p class="flex items-center gap-1 text-xs font-semibold text-danger-600">
                                    <x-admin.icon name="alert-circle" :size="14" /> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                                <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 transition focus:ring-brand-500">
                                Ingat saya
                            </label>
                            <span class="text-sm font-semibold text-slate-400" title="Hubungi administrator untuk reset password">Lupa password?</span>
                        </div>

                        <button type="submit" :disabled="submitting" class="group flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-3 text-sm font-extrabold text-white shadow-[var(--shadow-brand-glow)] transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-100 active:scale-[0.99] disabled:opacity-70">
                            <svg x-show="submitting" x-cloak class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="submitting ? 'Memproses...' : 'Masuk ke Dashboard'"></span>
                            <x-admin.icon x-show="!submitting" name="chevron-right" :size="18" class="transition group-hover:translate-x-0.5" />
                        </button>
                    </form>
                </div>

                <p class="mt-6 text-center text-xs text-slate-400">
                    &copy; {{ date('Y') }} Dinas Lingkungan Hidup Kota Palu &middot; Ruang Kendali Operasional
                </p>
            </div>
        </section>
    </main>

    <style>
        .login-card {
            animation: loginCardIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes loginCardIn {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: none; }
        }
        .login-float { animation: loginFloat 8s ease-in-out infinite; }
        .login-float--2 { animation-duration: 11s; animation-delay: -3s; }
        .login-float--3 { animation-duration: 9s; animation-delay: -5s; }
        @keyframes loginFloat {
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(-24px) translateX(12px); }
        }
        @media (prefers-reduced-motion: reduce) {
            .login-card, .login-float { animation: none !important; }
        }
    </style>
</body>

</html>
