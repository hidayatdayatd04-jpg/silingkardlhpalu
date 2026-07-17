<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (function () {
            var stored = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (stored === 'dark' || (!stored && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <title>@yield('title', 'Portal Operasional DLH Kota Palu')</title>

    <meta name="description" content="@yield('description', 'Portal Operasional SILP Dinas Lingkungan Hidup Kota Palu — layanan multi-bidang: pengendalian lingkungan, pengelolaan sampah & LB3, ruang terbuka hijau, pelacakan armada, dan survei kepuasan masyarakat.')">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Portal Operasional DLH Kota Palu')">
    <meta property="og:description" content="@yield('description', 'Portal Operasional SILP Dinas Lingkungan Hidup Kota Palu — layanan multi-bidang: pengendalian lingkungan, pengelolaan sampah & LB3, ruang terbuka hijau, pelacakan armada, dan survei kepuasan masyarakat.')">
    <meta property="og:image" content="{{ asset('assets/images/logo_kota_palu.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('title', 'Portal Operasional DLH Kota Palu')">
    <meta property="twitter:description" content="@yield('description', 'Portal Operasional SILP Dinas Lingkungan Hidup Kota Palu — layanan multi-bidang: pengendalian lingkungan, pengelolaan sampah & LB3, ruang terbuka hijau, pelacakan armada, dan survei kepuasan masyarakat.')">
    <meta property="twitter:image" content="{{ asset('assets/images/logo_kota_palu.png') }}">

    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        /* Textarea — paksa hide resize handle & scrollbar */
        textarea.pub-textarea { resize: none !important; overflow-y: auto !important; scrollbar-width: none !important; -ms-overflow-style: none !important; }
        textarea.pub-textarea::-webkit-scrollbar { display: none !important; width: 0 !important; }
        textarea.pub-textarea::-webkit-resizer { display: none !important; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
    @yield('styles')
    @stack('styles')
</head>

<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen flex flex-col antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-emerald-600 focus:text-white focus:rounded-md focus:shadow-md">{{ __('Lewati ke konten utama') }}</a>

    <header x-data="{ mobileMenuOpen: false }"
        class="sticky top-0 z-50 bg-white/85 dark:bg-slate-900/85 backdrop-blur-xl border-b border-slate-200/60 dark:border-slate-800/60 shadow-[0_1px_0_0_rgba(15,23,42,0.02),0_8px_24px_-16px_rgba(15,23,42,0.15)]">
        <div class="h-0.5 w-full bg-gradient-to-r from-brand-500 via-bay-500 to-brand-500"></div>
        <div class="max-w-[88rem] mx-auto px-4 sm:px-5 lg:px-6 h-16 flex items-center justify-between gap-3">
            <div class="flex items-center shrink-0">
                <a href="/" class="group flex items-center gap-2.5 sm:gap-3">
                    <span class="relative inline-flex items-center justify-center shrink-0">
                        <span class="absolute -inset-1 rounded-full bg-brand-500/15 blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300" aria-hidden="true"></span>
                        <img src="{{ asset('assets/images/logo_kota_palu.png') }}" alt="Logo Kota Palu" class="relative h-9 sm:h-11 w-auto object-contain transition-transform duration-300 group-hover:scale-105" />
                    </span>
                    <div class="border-l border-slate-200 dark:border-slate-700 pl-2.5 sm:pl-3">
                        <span class="block text-sm sm:text-base font-extrabold tracking-tight text-slate-900 dark:text-white uppercase leading-none whitespace-nowrap">
                            <span class="hidden xl:inline">Dinas Lingkungan Hidup</span>
                            <span class="xl:hidden">DLH</span>
                        </span>
                        <span class="mt-1 block text-[10px] sm:text-[11px] font-bold tracking-[0.18em] text-brand-600 dark:text-brand-400 uppercase leading-none whitespace-nowrap">
                            Kota Palu
                        </span>
                    </div>
                </a>
            </div>

            <nav class="hidden lg:flex items-center gap-0.5 xl:gap-1 shrink-0">
                <!-- Profile Dropdown -->
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open" 
                        class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('profil') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none cursor-pointer select-none whitespace-nowrap">
                        <span>{{ __('Profil') }}</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" 
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute left-0 mt-3 w-max min-w-[300px] rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl p-1.5 z-50 focus:outline-none"
                        style="display: none;">
                        <a href="/profil#visi-misi" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap">{{ __('Visi & Misi DLH Kota Palu') }}</a>
                        <a href="/profil#struktur-organisasi" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap">{{ __('Struktur Organisasi Dinas Lingkungan Hidup') }}</a>
                        <a href="/profil#tugas-fungsi" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap">{{ __('Tugas & Fungsi Dinas Lingkungan Hidup') }}</a>
                    </div>
                </div>

                <!-- Sekretariat Link -->
                <a href="/sekretariat"
                    class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('sekretariat') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none whitespace-nowrap">
                    <span>{{ __('Sekretariat') }}</span>
                </a>

                <!-- Bidang Pengendalian Dropdown -->
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('cek-pengaduan-pengendalian', 'permohonan-rekomendasi', 'cek-permohonan-rekomendasi') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none cursor-pointer select-none whitespace-nowrap">
                        <span>{{ __('Pengendalian') }}</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute left-0 mt-3 w-max min-w-[240px] rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl p-1.5 z-50 focus:outline-none"
                        style="display: none;">
                        <a href="/permohonan-rekomendasi" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('permohonan-rekomendasi') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Permohonan/Rekomendasi') }}</a>
                        <a href="/cek-permohonan-rekomendasi" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('cek-permohonan-rekomendasi') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Cek Status Permohonan') }}</a>
                    </div>
                </div>

                <!-- Bidang Sampah LB3 -->
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('peta-persampahan', 'cek-pengaduan-sampah', 'registrasi-usaha-lb3', 'cek-registrasi-lb3', 'armada', 'pengajuan-rintek-pertek', 'cek-rintek-pertek') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none cursor-pointer select-none whitespace-nowrap">
                        <span>{{ __('Sampah & LB3') }}</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute left-0 mt-3 w-max min-w-[260px] rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl p-1.5 z-50 focus:outline-none"
                        style="display: none;">
                        <a href="/peta-persampahan" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('peta-persampahan') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Peta Persampahan') }}</a>
                        <a href="/registrasi-usaha-lb3" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('registrasi-usaha-lb3') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Registrasi Usaha LB3') }}</a>
                        <a href="/cek-registrasi-lb3" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('cek-registrasi-lb3') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Cek Registrasi LB3') }}</a>
                        <a href="/pengajuan-rintek-pertek" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('pengajuan-rintek-pertek') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Pengajuan RINTEK/PERTEK') }}</a>
                        <a href="/cek-rintek-pertek" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('cek-rintek-pertek') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Cek RINTEK/PERTEK') }}</a>
                    </div>
                </div>

                <!-- Bidang Tata Penataan Dropdown -->
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('tata-penataan', 'cek-pengaduan-tata-penataan', 'peta-objek-pengawasan') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none cursor-pointer select-none whitespace-nowrap">
                        <span>{{ __('Tata Penataan') }}</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute left-0 mt-3 w-max min-w-[240px] rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl p-1.5 z-50 focus:outline-none"
                        style="display: none;">
                        <a href="/tata-penataan" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('tata-penataan') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Info Modul') }}</a>
                        <a href="/peta-objek-pengawasan" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('peta-objek-pengawasan') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Peta Objek Pengawasan') }}</a>
                    </div>
                </div>

                <!-- Bidang RTH Dropdown -->
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('peta-rth', 'cek-pengaduan-rth', 'perizinan-tebang-pohon', 'cek-perizinan-tebang-pohon', 'pinjam-taman', 'cek-pinjam-taman') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none cursor-pointer select-none whitespace-nowrap">
                        <span>{{ __('RTH') }}</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" 
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute left-0 mt-3 w-max min-w-[240px] rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl p-1.5 z-50 focus:outline-none"
                        style="display: none;">
                        <a href="/peta-rth" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('peta-rth') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Peta RTH') }}</a>
                        <a href="/perizinan-tebang-pohon" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('perizinan-tebang-pohon') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Izin Tebang Pohon') }}</a>
                        <a href="/cek-perizinan-tebang-pohon" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('cek-perizinan-tebang-pohon') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Cek Izin Tebang') }}</a>
                        <a href="/pinjam-taman" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('pinjam-taman') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Pinjam Pakai Taman') }}</a>
                        <a href="/cek-pinjam-taman" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('cek-pinjam-taman') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Cek Pinjam Taman') }}</a>
                    </div>
                </div>

                <!-- UPTD Dropdown -->
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('uptd*') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none cursor-pointer select-none whitespace-nowrap">
                        <span>{{ __('UPTD') }}</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute left-0 mt-3 w-max min-w-[180px] rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl p-1.5 z-50 focus:outline-none"
                        style="display: none;">
                        <a href="/uptd/lab-lingkungan" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('uptd/lab-lingkungan') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">UPTD Lab Lingkungan</a>
                        <a href="/uptd/tpa-kawatuna" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('uptd/tpa-kawatuna') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">UPTD TPA Kawatuna</a>
                    </div>
                </div>

                <!-- Informasi Dropdown -->
                <div x-data="{ open: false, subOpen: false }" @click.away="open = false; subOpen = false" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('lacak', 'berita*', 'survei', 'profil', 'pengaduan') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none cursor-pointer select-none whitespace-nowrap">
                        <span>{{ __('Informasi') }}</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute right-0 mt-3 w-max min-w-[240px] rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl p-1.5 z-50 focus:outline-none"
                        style="display: none;">
                        <div class="relative">
                            <button @click="subOpen = !subOpen" class="w-full flex items-center justify-between px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('pengaduan') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">
                                <span>{{ __('Layanan Informasi Publik') }}</span>
                                <svg class="h-4 w-4 transition-transform duration-200 ml-2" :class="{ 'rotate-180': subOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                            <div x-show="subOpen"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-x-1"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 -translate-x-1"
                                class="pl-4 space-y-1 mt-1" style="display: none;">
                                <a href="/pengaduan" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('pengaduan') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Pengaduan') }}</a>
                            </div>
                        </div>
                        <div class="my-1 border-t border-slate-100 dark:border-slate-800"></div>
                        <a href="/lacak" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('lacak') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Lacak Pelaporan') }}</a>
                        <a href="/berita" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('berita*') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Berita') }}</a>
                        <a href="/survei" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('survei') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Survei Kepuasan (IKM)') }}</a>
                        <a href="/profil" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('profil') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Tentang Kami') }}</a>
                    </div>
                </div>
            </nav>

            <div class="flex items-center gap-2 shrink-0">
                <x-public.language-switcher />
                <x-public.dark-mode-toggle />
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="lg:hidden h-10 w-10 inline-flex justify-center items-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm hover:border-brand-300 transition-all dark:bg-slate-800/60 dark:border-slate-700 dark:text-white dark:hover:border-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                    <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x-show="!mobileMenuOpen"><line x1="3" x2="21" y1="6" y2="6"/><line x1="3" x2="21" y1="12" y2="12"/><line x1="3" x2="21" y1="18" y2="18"/></svg>
                    <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x-show="mobileMenuOpen" style="display: none;"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
        </div>

        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="lg:hidden absolute top-[100%] inset-x-0 border-b border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-lg" style="display: none;">
            <div class="flex flex-col px-4 pt-2 pb-6 space-y-1.5">
                <!-- Profile (Dropdown Mobile) -->
                <div x-data="{ open: {{ request()->is('profil') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('profil') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                        <span>{{ __('Profil') }}</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" class="pl-4 space-y-1 mt-1" style="display: none;"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <a href="/profil#visi-misi" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Visi & Misi DLH Kota Palu') }}</a>
                        <a href="/profil#struktur-organisasi" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Struktur Organisasi Dinas Lingkungan Hidup') }}</a>
                        <a href="/profil#tugas-fungsi" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Tugas & Fungsi Dinas Lingkungan Hidup') }}</a>
                    </div>
                </div>

                <!-- Sekretariat Link (Mobile) -->
                <a href="/sekretariat" class="block px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('sekretariat') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">{{ __('Sekretariat') }}</a>

                <!-- Bidang Pengendalian (Dropdown Mobile) -->
                <div x-data="{ open: {{ request()->is('permohonan-rekomendasi', 'cek-permohonan-rekomendasi') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('permohonan-rekomendasi', 'cek-permohonan-rekomendasi') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                        <span>{{ __('Pengendalian') }}</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" class="pl-4 space-y-1 mt-1" style="display: none;"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <a href="/permohonan-rekomendasi" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Permohonan/Rekomendasi') }}</a>
                        <a href="/cek-permohonan-rekomendasi" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Cek Status Permohonan') }}</a>
                    </div>
                </div>

                <!-- Bidang Sampah LB3 (Dropdown Mobile) -->
                <div x-data="{ open: {{ request()->is('peta-persampahan', 'registrasi-usaha-lb3', 'cek-registrasi-lb3', 'armada', 'pengajuan-rintek-pertek', 'cek-rintek-pertek') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('peta-persampahan', 'registrasi-usaha-lb3', 'cek-registrasi-lb3', 'armada', 'pengajuan-rintek-pertek', 'cek-rintek-pertek') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                        <span>{{ __('Sampah & LB3') }}</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" class="pl-4 space-y-1 mt-1" style="display: none;"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <a href="/peta-persampahan" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Peta Persampahan') }}</a>
                        <a href="/registrasi-usaha-lb3" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Registrasi Usaha LB3') }}</a>
                        <a href="/cek-registrasi-lb3" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Cek Registrasi LB3') }}</a>
                        <a href="/pengajuan-rintek-pertek" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Pengajuan RINTEK/PERTEK') }}</a>
                        <a href="/cek-rintek-pertek" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Cek RINTEK/PERTEK') }}</a>
                    </div>
                </div>

                <!-- Bidang Tata Penataan (Dropdown Mobile) -->
                <div x-data="{ open: {{ request()->is('tata-penataan', 'peta-objek-pengawasan') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('tata-penataan', 'peta-objek-pengawasan') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                        <span>{{ __('Bidang Tata Penataan') }}</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" class="pl-4 space-y-1 mt-1" style="display: none;"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <a href="/tata-penataan" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Info Modul') }}</a>
                        <a href="/peta-objek-pengawasan" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Peta Objek Pengawasan') }}</a>
                    </div>
                </div>

                <!-- Bidang RTH (Dropdown Mobile) -->
                <div x-data="{ open: {{ request()->is('peta-rth', 'perizinan-tebang-pohon', 'cek-perizinan-tebang-pohon', 'pinjam-taman', 'cek-pinjam-taman') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('peta-rth', 'perizinan-tebang-pohon', 'cek-perizinan-tebang-pohon', 'pinjam-taman', 'cek-pinjam-taman') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                        <span>{{ __('Bidang RTH') }}</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" class="pl-4 space-y-1 mt-1" style="display: none;"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <a href="/peta-rth" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Peta RTH') }}</a>
                        <a href="/perizinan-tebang-pohon" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Izin Tebang Pohon') }}</a>
                        <a href="/cek-perizinan-tebang-pohon" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Cek Izin Tebang') }}</a>
                        <a href="/pinjam-taman" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Pinjam Taman') }}</a>
                        <a href="/cek-pinjam-taman" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Cek Pinjam Taman') }}</a>
                    </div>
                </div>

                <!-- UPTD (Dropdown Mobile) -->
                <div x-data="{ open: {{ request()->is('uptd*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('uptd*') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                        <span>{{ __('UPTD') }}</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" class="pl-4 space-y-1 mt-1" style="display: none;"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <a href="/uptd/lab-lingkungan" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">UPTD Lab Lingkungan</a>
                        <a href="/uptd/tpa-kawatuna" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">UPTD TPA Kawatuna</a>
                    </div>
                </div>

                <!-- Informasi (Dropdown Mobile) -->
                <div x-data="{ open: {{ request()->is('lacak', 'berita*', 'survei', 'profil', 'pengaduan') ? 'true' : 'false' }}, subOpen: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('lacak', 'berita*', 'survei', 'profil', 'pengaduan') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                        <span>{{ __('Informasi') }}</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" class="pl-4 space-y-1 mt-1" style="display: none;"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <div x-data="{ subOpen: false }">
                            <button @click="subOpen = !subOpen" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold {{ request()->is('pengaduan') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/30 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                                <span>{{ __('Layanan Informasi Publik') }}</span>
                                <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-90': subOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                            <div x-show="subOpen" class="pl-4 space-y-1 mt-1" style="display: none;"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0">
                                <a href="/pengaduan" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('pengaduan') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/30 dark:bg-brand-900/10' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">{{ __('Pengaduan') }}</a>
                            </div>
                        </div>
                        <div class="border-t border-slate-200 dark:border-slate-700 my-1"></div>
                        <a href="/lacak" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('lacak') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/30 dark:bg-brand-900/10' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">{{ __('Lacak Pelaporan') }}</a>
                        <a href="/berita" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('berita*') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/30 dark:bg-brand-900/10' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">{{ __('Berita') }}</a>
                        <a href="/survei" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('survei') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/30 dark:bg-brand-900/10' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">{{ __('Survei Kepuasan') }}</a>
                        <a href="/profil" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('profil') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/30 dark:bg-brand-900/10' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">{{ __('Tentang Kami') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    @hasSection('full_width')
    <main id="main-content" class="dlh-public flex-1 w-full">
        @yield('content')
    </main>
    @else
    <main id="main-content" class="dlh-public flex-1 w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        @yield('content')
    </main>
    @endif

<footer class="relative mt-auto bg-white dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800/80 overflow-hidden">
        {{-- Aksen gradien atas + cahaya latar --}}
        <div class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-brand-500 via-bay-500 to-brand-500" aria-hidden="true"></div>
        <div class="absolute -top-24 right-10 size-72 rounded-full bg-brand-500/5 blur-3xl pointer-events-none" aria-hidden="true"></div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-8">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-12 gap-8 lg:gap-6 pb-10 border-b border-slate-200 dark:border-slate-800">

                <div class="col-span-2 md:col-span-4 lg:col-span-4 space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('assets/images/logo_kota_palu.png') }}" alt="Logo Kota Palu" class="h-12 w-auto object-contain drop-shadow-sm">
                        <div>
                            <p class="font-extrabold text-slate-900 dark:text-white text-sm tracking-tight">DLH Kota Palu</p>
                            <p class="text-xs text-brand-600 dark:text-brand-400 font-semibold">Dinas Lingkungan Hidup</p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed max-w-sm">
                        {{ __('Portal layanan publik digital untuk pengaduan lingkungan, pengelolaan sampah & LB3, serta pelestarian ruang terbuka hijau di Kota Palu.') }}
                    </p>
                    <div class="flex items-center gap-2 pt-1">
                        <a href="https://www.instagram.com/dlhkotapalu" target="_blank" rel="noopener noreferrer" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-white hover:bg-gradient-to-br hover:from-pink-500 hover:to-purple-500 transition-all duration-300 hover:-translate-y-0.5" title="Instagram">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        <a href="https://www.facebook.com/share/18qHSySQr4/?locale=id_ID" target="_blank" rel="noopener noreferrer" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-white hover:bg-blue-600 transition-all duration-300 hover:-translate-y-0.5" title="Facebook">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                        </a>
                        <a href="https://wa.me/6285191512076" target="_blank" rel="noopener noreferrer" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-white hover:bg-brand-600 transition-all duration-300 hover:-translate-y-0.5" title="WhatsApp">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.405 0 .025 5.38.025 12.006c0 2.118.552 4.186 1.603 6.002L.002 24l6.14-1.61c1.748.956 3.722 1.463 5.889 1.463 6.626 0 12.006-5.38 12.006-12.006S18.657 0 12.031 0z"/></svg>
                        </a>
                    </div>
                </div>

                <div class="col-span-1 lg:col-span-2 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">{{ __('Pengendalian') }}</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/pengaduan" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-all duration-200">{{ __('Pengaduan') }}</a></li>
                        <li><a href="/permohonan-rekomendasi" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-all duration-200">{{ __('Permohonan') }}</a></li>
                        <li><a href="/cek-pengaduan-pengendalian" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-all duration-200">{{ __('Cek Status') }}</a></li>
                    </ul>
                </div>

                <div class="col-span-1 lg:col-span-2 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">{{ __('Sampah & LB3') }}</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/peta-persampahan" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-all duration-200">{{ __('Peta Persampahan & Armada') }}</a></li>
                        <li><a href="/pengaduan" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-all duration-200">{{ __('Pengaduan Sampah') }}</a></li>
                        <li><a href="/registrasi-usaha-lb3" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-all duration-200">{{ __('Registrasi LB3') }}</a></li>
                    </ul>
                </div>

                <div class="col-span-1 lg:col-span-2 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">{{ __('RTH') }}</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/peta-rth" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-all duration-200">{{ __('Peta RTH') }}</a></li>
                        <li><a href="/pengaduan" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-all duration-200">{{ __('Pengaduan RTH') }}</a></li>
                        <li><a href="/perizinan-tebang-pohon" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-all duration-200">{{ __('Izin Tebang') }}</a></li>
                        <li><a href="/pinjam-taman" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-all duration-200">{{ __('Pinjam Taman') }}</a></li>
                    </ul>
                </div>

                <div class="col-span-2 md:col-span-4 lg:col-span-2 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">{{ __('Kontak') }}</h4>
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Jl. Pipit, Tanamodindi, Kec. Palu Sel., Kota Palu, Sulawesi Tengah 94111
                    </p>
                    <a href="https://wa.me/6285191512076" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl bg-brand-50 dark:bg-brand-900/20 px-3 py-2 text-sm font-semibold text-brand-700 dark:text-brand-300 hover:bg-brand-100 dark:hover:bg-brand-900/40 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.405 0 .025 5.38.025 12.006c0 2.118.552 4.186 1.603 6.002L.002 24l6.14-1.61c1.748.956 3.722 1.463 5.889 1.463 6.626 0 12.006-5.38 12.006-12.006S18.657 0 12.031 0z"/></svg>
                        0851-9151-2076
                    </a>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
                <p>© {{ date('Y') }} Dinas Lingkungan Hidup Kota Palu. {{ __('Hak cipta dilindungi.') }}</p>
                <div class="flex items-center gap-4">
                    <a href="/kebijakan-privasi" class="hover:text-brand-600 dark:hover:text-brand-400 transition-colors">{{ __('Kebijakan Privasi') }}</a>
                    <a href="/syarat-ketentuan" class="hover:text-brand-600 dark:hover:text-brand-400 transition-colors">{{ __('Syarat & Ketentuan') }}</a>
                    <a href="/survei" class="hover:text-brand-600 dark:hover:text-brand-400 transition-colors">{{ __('Survei IKM') }}</a>
                </div>
            </div>
        </div>
    </footer>

    @livewire('chat-bot')
    @livewireScripts
    @yield('scripts')
    @stack('scripts')
</body>

</html>