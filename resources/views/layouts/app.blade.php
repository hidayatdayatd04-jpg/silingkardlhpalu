<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (function () {
            document.documentElement.classList.add('js');
            var stored = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (stored === 'dark' || (!stored && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @php
        // Canonical SEO fleksibel via config('app.url') agar perubahan domain cukup via APP_URL.
        // Tanpa query string, mengikuti spec: '/' -> base saja, '/lacak' -> base.'/lacak', dll.
        // Host request (Railway *.up.railway.app / Cloudflare Tunnel) tidak akan jadi canonical.
        $canonicalBase = rtrim((string) config('app.url'), '/');
        $canonicalPath = request()->getPathInfo(); // '/' , '/lacak', '/berita/slug' — tanpa query string
        if ($canonicalBase === '') {
            $canonicalUrl = url()->current();
        } else {
            $canonicalUrl = $canonicalPath === '/' ? $canonicalBase : $canonicalBase . $canonicalPath;
        }
    @endphp
    <title>@yield('title', 'Portal Operasional DLH Kota Palu')</title>

    <meta name="description" content="@yield('description', 'Portal Operasional SILP Dinas Lingkungan Hidup Kota Palu - layanan multi-bidang: pengendalian lingkungan, pengelolaan sampah & LB3, ruang terbuka hijau, pelacakan armada, dan survei kepuasan masyarakat.')">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="@yield('title', 'Portal Operasional DLH Kota Palu')">
    <meta property="og:description" content="@yield('description', 'Portal Operasional SILP Dinas Lingkungan Hidup Kota Palu - layanan multi-bidang: pengendalian lingkungan, pengelolaan sampah & LB3, ruang terbuka hijau, pelacakan armada, dan survei kepuasan masyarakat.')">
    <meta property="og:image" content="@yield('og_image', asset('assets/images/logo_kota_palu.png'))">
    <meta property="og:image:width" content="@yield('og_image_width', '1200')">
    <meta property="og:image:height" content="@yield('og_image_height', '630')">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ $canonicalUrl }}">
    <meta property="twitter:title" content="@yield('title', 'Portal Operasional DLH Kota Palu')">
    <meta property="twitter:description" content="@yield('description', 'Portal Operasional SILP Dinas Lingkungan Hidup Kota Palu - layanan multi-bidang: pengendalian lingkungan, pengelolaan sampah & LB3, ruang terbuka hijau, pelacakan armada, dan survei kepuasan masyarakat.')">
    <meta property="twitter:image" content="@yield('og_image', asset('assets/images/logo_kota_palu.png'))">

    <link rel="canonical" href="{{ $canonicalUrl }}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">

    @include('partials.web-fonts')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        /* Textarea - paksa hide resize handle & scrollbar */
        textarea.pub-textarea { resize: none !important; overflow-y: auto !important; scrollbar-width: none !important; -ms-overflow-style: none !important; }
        textarea.pub-textarea::-webkit-scrollbar { display: none !important; width: 0 !important; }
        textarea.pub-textarea::-webkit-resizer { display: none !important; }

        /* ---- Custom scrollbar - menyatu dengan tema website ---- */
        html {
            scrollbar-width: thin;
            scrollbar-color: #6ee7b7 #ecfdf5;
        }
        html::-webkit-scrollbar {
            width: 8px;
        }
        html::-webkit-scrollbar-track {
            background: #ecfdf5;
        }
        html::-webkit-scrollbar-thumb {
            background: #6ee7b7;
            border-radius: 9999px;
            border: 2px solid #ecfdf5;
        }
        html::-webkit-scrollbar-thumb:hover {
            background: #10b981;
        }
        html.dark {
            scrollbar-color: #065f46 #022c22;
        }
        html.dark::-webkit-scrollbar-track {
            background: #022c22;
        }
        html.dark::-webkit-scrollbar-thumb {
            background: #065f46;
            border: 2px solid #022c22;
        }
        html.dark::-webkit-scrollbar-thumb:hover {
            background: #059669;
        }
    </style>
    {{-- chatbot.css di-inline agar tidak menjadi render-blocking request eksternal. --}}
    <style>
        #chatbot-fab{pointer-events:auto!important;position:fixed!important;z-index:9991!important}
        #chatbot-portal{pointer-events:none!important}
        #chatbot-panel{pointer-events:auto!important;position:fixed!important;z-index:9991!important}
        .typing-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background-color:#10b981;animation:typingBounce 1.4s infinite ease-in-out both}
        .typing-dot:nth-child(1){animation-delay:-0.32s}
        .typing-dot:nth-child(2){animation-delay:-0.16s}
        @keyframes typingBounce{0%,80%,100%{transform:scale(0.6);opacity:0.4}40%{transform:scale(1);opacity:1}}
        .chatbot-fab{animation:fabPulse 2s cubic-bezier(0.4,0,0.6,1) infinite}
        @keyframes fabPulse{0%,100%{box-shadow:0 10px 15px -3px rgba(16,185,129,0.3),0 4px 6px -2px rgba(16,185,129,0.2)}50%{box-shadow:0 20px 25px -5px rgba(16,185,129,0.4),0 10px 10px -5px rgba(16,185,129,0.3)}}
        .chatbot-panel{animation:panelSlideUp 0.4s cubic-bezier(0.34,1.56,0.64,1)}
        @keyframes panelSlideUp{from{opacity:0;transform:translateY(20px) scale(0.95)}to{opacity:1;transform:translateY(0) scale(1)}}
        .chatbot-message-in{animation:messageBubbleIn 0.3s cubic-bezier(0.34,1.56,0.64,1)}
        @keyframes messageBubbleIn{from{opacity:0;transform:translateY(10px) scale(0.95)}to{opacity:1;transform:translateY(0) scale(1)}}
        .streaming-cursor::after{content:'|';display:inline-block;animation:cursorBlink 1s step-end infinite;color:#10b981;font-weight:bold}
        @keyframes cursorBlink{0%,100%{opacity:1}50%{opacity:0}}
        .chatbot-header{background-size:200% 200%;animation:gradientShift 3s ease infinite}
        @keyframes gradientShift{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
        .chatbot-panel ::-webkit-scrollbar{width:6px}
        .chatbot-panel ::-webkit-scrollbar-track{background:transparent}
        .chatbot-panel ::-webkit-scrollbar-thumb{background-color:rgba(156,163,175,0.3);border-radius:3px}
        .chatbot-panel ::-webkit-scrollbar-thumb:hover{background-color:rgba(156,163,175,0.5)}
        .dark .chatbot-panel ::-webkit-scrollbar-thumb{background-color:rgba(71,85,105,0.5)}
        .dark .chatbot-panel ::-webkit-scrollbar-thumb:hover{background-color:rgba(71,85,105,0.7)}
        .chatbot-panel textarea:focus{box-shadow:0 0 0 3px rgba(16,185,129,0.1)}
        .chatbot-panel button[type="submit"]:hover:not(:disabled){transform:scale(1.05)}
        .chatbot-panel button[type="submit"]:active:not(:disabled){transform:scale(0.95)}
        .chatbot-panel .px-3.py-1\.5{transition:all 0.2s ease}
        .chatbot-panel .px-3.py-1\.5:hover{transform:translateY(-1px);box-shadow:0 2px 8px rgba(16,185,129,0.2)}
        @media (max-width:480px){.chatbot-panel{bottom:0;right:0;left:0;width:100%;height:100%;max-width:none;max-height:none;border-radius:0}.chatbot-fab{bottom:1rem;right:1rem;height:48px;width:48px}}
        @media print{.chatbot-fab,.chatbot-panel{display:none!important}}
        @media (prefers-reduced-motion:reduce){.chatbot-fab,.chatbot-panel,.typing-dot{animation:none}.chatbot-panel{transition:opacity 0.2s ease}}
    </style>
    @yield('styles')
    @stack('styles')
</head>

<body class="public-site dlh-public-page bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen flex flex-col antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-emerald-600 focus:text-white focus:rounded-md focus:shadow-md">{{ __('Lewati ke konten utama') }}</a>
    @php
        $mtPreview = false;
        try {
            $mtPreview = \App\Models\Setting::get('maintenance_enabled')
                && request()->boolean('preview')
                && \App\Support\AdminAccess::hasAnyPanelRole(auth()->user());
        } catch (\Throwable $e) {
            $mtPreview = false;
        }
    @endphp
    @if($mtPreview)
        <div style="position:sticky;top:0;z-index:60;display:flex;align-items:center;gap:.5rem;justify-content:center;flex-wrap:wrap;padding:.6rem 1rem;background:#fffbeb;border-bottom:1px solid #fcd34d;color:#92400e;font-size:.8rem;text-align:center;">
            <span style="width:.5rem;height:.5rem;border-radius:9999px;background:#f59e0b;display:inline-block;"></span>
            <span><strong>Mode Pemeliharaan AKTIF</strong> - Anda mem-pratinjau situs publik sebagai admin.</span>
            <a href="{{ request()->fullUrlWithoutQuery('preview') }}" style="margin-left:.5rem;font-weight:600;text-decoration:underline;">Tutup pratinjau</a>
        </div>
    @endif


    <header x-data="{ mobileMenuOpen: false }"
        class="public-site-header sticky top-0 z-50 bg-white/85 dark:bg-slate-900/85 backdrop-blur-xl border-b border-slate-200/60 dark:border-slate-800/60 shadow-[0_1px_0_0_rgba(15,23,42,0.02),0_8px_24px_-16px_rgba(15,23,42,0.15)]">
        <div class="h-0.5 w-full bg-gradient-to-r from-brand-500 via-bay-500 to-brand-500"></div>
        <div class="max-w-[88rem] mx-auto px-4 sm:px-5 lg:px-6 h-16 flex items-center justify-between gap-3">
            <div class="flex items-center shrink-0">
                <a href="/" class="group flex items-center gap-2.5 sm:gap-3">
                    <span class="relative inline-flex items-center justify-center shrink-0">
                        <span class="absolute -inset-1 rounded-full bg-brand-500/15 blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300" aria-hidden="true"></span>
                        <img src="{{ asset('assets/images/logo_kota_palu.webp') }}" alt="Logo Kota Palu" width="320" height="423" class="relative h-11 sm:h-14 w-auto object-contain transition-transform duration-300 group-hover:scale-105" />
                    </span>
                    <div class="border-l border-slate-200 dark:border-slate-700 pl-2.5 sm:pl-3">
                        <span class="block text-sm sm:text-base font-bold tracking-tight text-slate-900 dark:text-white uppercase leading-none whitespace-nowrap">
                            <span class="hidden xl:inline">Dinas Lingkungan Hidup</span>
                            <span class="xl:hidden">DLH</span>
                        </span>
                        <span class="mt-1 block text-[10px] sm:text-[11px] font-semibold tracking-[0.14em] text-brand-700 dark:text-brand-400 uppercase leading-none whitespace-nowrap">
                            Kota Palu
                        </span>
                    </div>
                </a>
            </div>

            <nav class="hidden lg:flex items-center gap-0.5 xl:gap-1 shrink-0">
                <!-- Beranda Link -->
                <a href="/"
                    class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('/') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none whitespace-nowrap">
                    <span>{{ __('Beranda') }}</span>
                </a>

                <!-- Profile Dropdown -->
                <div x-data="{ open: false, tfOpen: false, sekOpen: false, bidOpen: false, uptdOpen: false }" @click.away="open = false; tfOpen = false; sekOpen = false; bidOpen = false; uptdOpen = false" class="relative">
                    <button @click="open = !open" 
                        class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('profil') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none cursor-pointer select-none whitespace-nowrap">
                        <span>{{ __('Profil') }}</span>
                        <x-icons.ui name="chevron-down" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                    </button>
                    <div x-show="open" 
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute left-0 mt-3 w-max min-w-[280px] rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl p-1.5 z-50 focus:outline-none"
                        style="display: none;">
                        <a href="/profil#visi-misi" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap">{{ __('Visi & Misi') }}</a>
                        <a href="/profil#struktur-organisasi" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap">{{ __('Struktur Organisasi') }}</a>
                        
                        <div class="my-1 border-t border-slate-100 dark:border-slate-800"></div>

                        <!-- Tugas & Fungsi Nested Submenu -->
                        <div class="relative">
                            <div class="flex items-center justify-between rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <a href="/profil#tugas-dlh" class="flex-1 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 font-medium transition-colors whitespace-nowrap">
                                    {{ __('Tugas & Fungsi') }}
                                </a>
                                <button type="button" @click="tfOpen = !tfOpen" class="px-3 py-2.5 text-slate-500 hover:text-brand-600 focus:outline-none cursor-pointer">
                                    <x-icons.ui name="chevron-right" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': tfOpen }" />
                                </button>
                            </div>
                            <div x-show="tfOpen" 
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-x-1"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 -translate-x-1"
                                class="pl-4 space-y-1 mt-1"
                                style="display: none;">
                                
                                <!-- Sekretariat Submenu -->
                                <div class="relative">
                                    <button @click="sekOpen = !sekOpen" class="w-full flex items-center justify-between px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap cursor-pointer focus:outline-none">
                                        <span>{{ __('Sekretariat') }}</span>
                                        <x-icons.ui name="chevron-right" class="ml-2 h-3.5 w-3.5 transition-transform duration-200" x-bind:class="{ 'rotate-180': sekOpen }" />
                                    </button>
                                    <div x-show="sekOpen" 
                                        class="pl-4 space-y-1 mt-1"
                                        style="display: none;">
                                        <a href="/profil#sekretaris" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-lg transition-colors whitespace-nowrap">{{ __('Sekretaris') }}</a>
                                        <a href="/profil#umum-kepegawaian" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-lg transition-colors whitespace-nowrap">{{ __('Sub Bagian Umum dan Kepegawaian') }}</a>
                                    </div>
                                </div>

                                <!-- Bidang Submenu -->
                                <div class="relative">
                                    <button @click="bidOpen = !bidOpen" class="w-full flex items-center justify-between px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap cursor-pointer focus:outline-none">
                                        <span>{{ __('Bidang') }}</span>
                                        <x-icons.ui name="chevron-right" class="ml-2 h-3.5 w-3.5 transition-transform duration-200" x-bind:class="{ 'rotate-180': bidOpen }" />
                                    </button>
                                    <div x-show="bidOpen" 
                                        class="pl-4 space-y-1 mt-1"
                                        style="display: none;">
                                        <a href="/profil#tata-lingkungan" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-lg transition-colors whitespace-nowrap">{{ __('Bidang Tata dan Penataan Lingkungan') }}</a>
                                        <a href="/profil#pengendalian" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-lg transition-colors whitespace-nowrap">{{ __('Bidang Pengendalian Pencemaran, Kerusakan & Kapasitas') }}</a>
                                        <a href="/profil#sampah-lb3" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-lg transition-colors whitespace-nowrap">{{ __('Bidang Pengelolaan Sampah dan Limbah B3') }}</a>
                                        <a href="/profil#rth" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-lg transition-colors whitespace-nowrap">{{ __('Bidang Pengelolaan Ruang Terbuka Hijau') }}</a>
                                    </div>
                                </div>

                                <!-- UPTD Submenu -->
                                <div class="relative">
                                    <button @click="uptdOpen = !uptdOpen" class="w-full flex items-center justify-between px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap cursor-pointer focus:outline-none">
                                        <span>{{ __('UPTD') }}</span>
                                        <x-icons.ui name="chevron-right" class="ml-2 h-3.5 w-3.5 transition-transform duration-200" x-bind:class="{ 'rotate-180': uptdOpen }" />
                                    </button>
                                    <div x-show="uptdOpen" 
                                        class="pl-4 space-y-1 mt-1"
                                        style="display: none;">
                                        <a href="/profil#uptd-lab" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-lg transition-colors whitespace-nowrap">{{ __('UPTD Lab Lingkungan') }}</a>
                                        <a href="/profil#uptd-tpa" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-lg transition-colors whitespace-nowrap">{{ __('UPTD TPA Kawatuna') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Bidang Pengendalian Dropdown -->
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('permohonan-rekomendasi', 'cek-permohonan-rekomendasi') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none cursor-pointer select-none whitespace-nowrap">
                        <span>{{ __('Pengendalian') }}</span>
                        <x-icons.ui name="chevron-down" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
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
                        class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('peta-persampahan', 'registrasi-usaha-lb3', 'cek-registrasi-lb3', 'pengajuan-rintek-pertek', 'cek-rintek-pertek') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none cursor-pointer select-none whitespace-nowrap">
                        <span>{{ __('Sampah & LB3') }}</span>
                        <x-icons.ui name="chevron-down" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
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
                        class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('tata-lingkungan') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none cursor-pointer select-none whitespace-nowrap">
                        <span>{{ __('Tata Penataan') }}</span>
                        <x-icons.ui name="chevron-down" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
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
                        <a href="/tata-lingkungan" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('tata-lingkungan') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Tata Lingkungan') }}</a>
                    </div>
                </div>

                <!-- Bidang RTH Dropdown -->
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('pinjam-taman', 'cek-pinjam-taman') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none cursor-pointer select-none whitespace-nowrap">
                        <span>{{ __('RTH') }}</span>
                        <x-icons.ui name="chevron-down" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
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
                        <a href="/pinjam-taman" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('pinjam-taman') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Penyewaan Taman') }}</a>
                        <a href="/cek-pinjam-taman" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('cek-pinjam-taman') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Cek Penyewaan Taman') }}</a>
                    </div>
                </div>

                <!-- UPTD Dropdown -->
                <div x-data="{ open: false, labOpen: false, tpaOpen: false }" @click.away="open = false; labOpen = false; tpaOpen = false" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('uptd*') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none cursor-pointer select-none whitespace-nowrap">
                        <span>{{ __('UPTD') }}</span>
                        <x-icons.ui name="chevron-down" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                    </button>
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute left-0 mt-3 w-max min-w-[220px] rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl p-1.5 z-50 focus:outline-none"
                        style="display: none;">
                        <div class="relative">
                            <div class="flex items-center justify-between rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/40 {{ request()->is('uptd/lab-lingkungan', 'uptd/jurnal-lab') ? 'bg-brand-50/50 dark:bg-brand-900/10' : '' }}">
                                <a href="/uptd/lab-lingkungan" class="flex-1 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 font-medium transition-colors whitespace-nowrap {{ request()->is('uptd/lab-lingkungan') ? 'text-brand-600 dark:text-brand-400' : '' }}">UPTD Lab Lingkungan</a>
                                <button type="button" @click="labOpen = !labOpen" class="px-3 py-2.5 text-slate-500 hover:text-brand-600 focus:outline-none cursor-pointer">
                                    <x-icons.ui name="chevron-right" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': labOpen }" />
                                </button>
                            </div>
                            <div x-show="labOpen"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-x-1"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 -translate-x-1"
                                class="pl-4 space-y-1 mt-1" style="display: none;">
                                <a href="/uptd/jurnal-lab" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('uptd/jurnal-lab') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">Jurnal Lab</a>
                            </div>
                        </div>
                        <div class="my-1 border-t border-slate-100 dark:border-slate-800"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/40 {{ request()->is('uptd/tpa-kawatuna*') ? 'bg-brand-50/50 dark:bg-brand-900/10' : '' }}">
                                <a href="/uptd/tpa-kawatuna" class="flex-1 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 font-medium transition-colors whitespace-nowrap {{ request()->is('uptd/tpa-kawatuna') ? 'text-brand-600 dark:text-brand-400' : '' }}">UPTD TPA Kawatuna</a>
                                <button type="button" @click="tpaOpen = !tpaOpen" class="px-3 py-2.5 text-slate-500 hover:text-brand-600 focus:outline-none cursor-pointer">
                                    <x-icons.ui name="chevron-right" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': tpaOpen }" />
                                </button>
                            </div>
                            <div x-show="tpaOpen"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-x-1"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 -translate-x-1"
                                class="pl-4 space-y-1 mt-1" style="display: none;">
                                <a href="/uptd/tpa-kawatuna/sejarah" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('uptd/tpa-kawatuna/sejarah') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">Sejarah</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Dropdown -->
                <div x-data="{ open: false, subOpen: false }" @click.away="open = false; subOpen = false" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-1 px-2.5 py-2 rounded-xl text-[13px] xl:text-sm font-semibold {{ request()->is('lacak', 'berita*', 'tentang', 'pengaduan') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 ring-1 ring-brand-500/15' : 'text-slate-600 dark:text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-50/80 dark:hover:bg-slate-800/60' }} transition-colors focus:outline-none cursor-pointer select-none whitespace-nowrap">
                        <span>{{ __('Informasi') }}</span>
                        <x-icons.ui name="chevron-down" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
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
                                <x-icons.ui name="chevron-right" class="ml-2 h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': subOpen }" />
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
                        <a href="https://skm.go.id/share/instansi/032ced20-3ad5-4b83-97fe-044abcb65bd3/1" target="_blank" rel="noopener" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap">{{ __('Survei Kepuasan (IKM)') }}</a>
                        <a href="/tentang" class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-brand-600 dark:hover:text-brand-400 rounded-xl font-medium transition-colors whitespace-nowrap {{ request()->is('tentang') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : '' }}">{{ __('Tentang Kami') }}</a>
                    </div>
                </div>
            </nav>

            <div class="flex items-center gap-2 shrink-0">
                <x-public.dark-mode-toggle />
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" aria-label="Buka menu navigasi" class="lg:hidden h-10 w-10 inline-flex justify-center items-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm hover:border-brand-300 transition-colors duration-200 dark:bg-slate-800/60 dark:border-slate-700 dark:text-white dark:hover:border-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                    <x-icons.ui name="menu" class="flex-shrink-0 size-5" x-show="!mobileMenuOpen" />
                    <x-icons.ui name="close" class="flex-shrink-0 size-5" x-show="mobileMenuOpen" style="display: none;" />
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
                <!-- Beranda Link (Mobile) -->
                <a href="/" class="block px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('/') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">{{ __('Beranda') }}</a>

                <!-- Profile (Dropdown Mobile) -->
                <div x-data="{ open: {{ request()->is('profil*') ? 'true' : 'false' }}, openTf: false, openSek: false, openBid: false, openUptd: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('profil*') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                        <span>{{ __('Profil') }}</span>
                        <x-icons.ui name="chevron-down" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                    </button>
                    <div x-show="open" class="pl-4 space-y-1 mt-1" style="display: none;"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <a href="/profil#visi-misi" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Visi & Misi') }}</a>
                        <a href="/profil#struktur-organisasi" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Struktur Organisasi Dinas Lingkungan Hidup') }}</a>
                        
                        <!-- Tugas & Fungsi Nested Mobile -->
                        <div class="pt-1">
                            <div class="flex items-center justify-between rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                                <a href="/profil#tugas-dlh" class="flex-1 px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-brand-600">
                                    {{ __('Tugas & Fungsi') }}
                                </a>
                                <button @click="openTf = !openTf" class="px-3 py-2 text-slate-500 hover:text-brand-600 focus:outline-none cursor-pointer">
                                    <x-icons.ui name="chevron-right" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': openTf }" />
                                </button>
                            </div>
                            <div x-show="openTf" class="pl-4 space-y-1 mt-1" style="display: none;"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0">
                                <!-- Sekretariat -->
                                <div>
                                    <button @click="openSek = !openSek" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-brand-600 focus:outline-none cursor-pointer">
                                        <span>{{ __('Sekretariat') }}</span>
                                        <x-icons.ui name="chevron-right" class="h-3.5 w-3.5 transition-transform duration-200" x-bind:class="{ 'rotate-180': openSek }" />
                                    </button>
                                    <div x-show="openSek" class="pl-4 space-y-1 mt-1" style="display: none;">
                                        <a href="/profil#sekretaris" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400 hover:text-brand-600">{{ __('Sekretaris') }}</a>
                                        <a href="/profil#umum-kepegawaian" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400 hover:text-brand-600">{{ __('Sub Bagian Umum dan Kepegawaian') }}</a>
                                    </div>
                                </div>

                                <!-- Bidang -->
                                <div>
                                    <button @click="openBid = !openBid" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-brand-600 focus:outline-none cursor-pointer">
                                        <span>{{ __('Bidang') }}</span>
                                        <x-icons.ui name="chevron-right" class="h-3.5 w-3.5 transition-transform duration-200" x-bind:class="{ 'rotate-180': openBid }" />
                                    </button>
                                    <div x-show="openBid" class="pl-4 space-y-1 mt-1" style="display: none;">
                                        <a href="/profil#tata-lingkungan" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400 hover:text-brand-600">{{ __('Bidang Tata dan Penataan Lingkungan') }}</a>
                                        <a href="/profil#pengendalian" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400 hover:text-brand-600">{{ __('Bidang Pengendalian Pencemaran, Kerusakan & Kapasitas') }}</a>
                                        <a href="/profil#sampah-lb3" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400 hover:text-brand-600">{{ __('Bidang Pengelolaan Sampah dan Limbah B3') }}</a>
                                        <a href="/profil#rth" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400 hover:text-brand-600">{{ __('Bidang Pengelolaan Ruang Terbuka Hijau') }}</a>
                                    </div>
                                </div>

                                <!-- UPTD -->
                                <div>
                                    <button @click="openUptd = !openUptd" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-brand-600 focus:outline-none cursor-pointer">
                                        <span>{{ __('UPTD') }}</span>
                                        <x-icons.ui name="chevron-right" class="h-3.5 w-3.5 transition-transform duration-200" x-bind:class="{ 'rotate-180': openUptd }" />
                                    </button>
                                    <div x-show="openUptd" class="pl-4 space-y-1 mt-1" style="display: none;">
                                        <a href="/profil#uptd-lab" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400 hover:text-brand-600">{{ __('UPTD Lab Lingkungan') }}</a>
                                        <a href="/profil#uptd-tpa" class="block px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400 hover:text-brand-600">{{ __('UPTD TPA Kawatuna') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Bidang Pengendalian (Dropdown Mobile) -->
                <div x-data="{ open: {{ request()->is('permohonan-rekomendasi', 'cek-permohonan-rekomendasi') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('permohonan-rekomendasi', 'cek-permohonan-rekomendasi') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                        <span>{{ __('Pengendalian') }}</span>
                        <x-icons.ui name="chevron-down" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
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
                <div x-data="{ open: {{ request()->is('peta-persampahan', 'registrasi-usaha-lb3', 'cek-registrasi-lb3', 'pengajuan-rintek-pertek', 'cek-rintek-pertek') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('peta-persampahan', 'registrasi-usaha-lb3', 'cek-registrasi-lb3', 'pengajuan-rintek-pertek', 'cek-rintek-pertek') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                        <span>{{ __('Sampah & LB3') }}</span>
                        <x-icons.ui name="chevron-down" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
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
                <div x-data="{ open: {{ request()->is('tata-lingkungan') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('tata-lingkungan') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                        <span>{{ __('Tata Penataan') }}</span>
                        <x-icons.ui name="chevron-down" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                    </button>
                    <div x-show="open" class="pl-4 space-y-1 mt-1" style="display: none;"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <a href="/tata-lingkungan" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Tata Lingkungan') }}</a>
                    </div>
                </div>

                <!-- Bidang RTH (Dropdown Mobile) -->
                <div x-data="{ open: {{ request()->is('pinjam-taman', 'cek-pinjam-taman') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('pinjam-taman', 'cek-pinjam-taman') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                        <span>{{ __('RTH') }}</span>
                        <x-icons.ui name="chevron-down" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                    </button>
                    <div x-show="open" class="pl-4 space-y-1 mt-1" style="display: none;"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <a href="/pinjam-taman" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Penyewaan Taman') }}</a>
                        <a href="/cek-pinjam-taman" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Cek Penyewaan Taman') }}</a>
                    </div>
                </div>

                <!-- UPTD (Dropdown Mobile) -->
                <div x-data="{ open: {{ request()->is('uptd*') ? 'true' : 'false' }}, labOpen: {{ request()->is('uptd/lab-lingkungan', 'uptd/jurnal-lab') ? 'true' : 'false' }}, tpaOpen: {{ request()->is('uptd/tpa-kawatuna*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('uptd*') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                        <span>{{ __('UPTD') }}</span>
                        <x-icons.ui name="chevron-down" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                    </button>
                    <div x-show="open" class="pl-4 space-y-1 mt-1" style="display: none;"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <div>
                            <div class="flex items-center justify-between rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                                <a href="/uptd/lab-lingkungan" class="flex-1 px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-brand-600">UPTD Lab Lingkungan</a>
                                <button @click="labOpen = !labOpen" class="px-3 py-2 text-slate-500 hover:text-brand-600 focus:outline-none cursor-pointer">
                                    <x-icons.ui name="chevron-right" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': labOpen }" />
                                </button>
                            </div>
                            <div x-show="labOpen" class="pl-4 space-y-1 mt-1" style="display: none;"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0">
                                <a href="/uptd/jurnal-lab" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">Jurnal Lab</a>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                                <a href="/uptd/tpa-kawatuna" class="flex-1 px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-brand-600">UPTD TPA Kawatuna</a>
                                <button @click="tpaOpen = !tpaOpen" class="px-3 py-2 text-slate-500 hover:text-brand-600 focus:outline-none cursor-pointer">
                                    <x-icons.ui name="chevron-right" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': tpaOpen }" />
                                </button>
                            </div>
                            <div x-show="tpaOpen" class="pl-4 space-y-1 mt-1" style="display: none;"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0">
                                <a href="/uptd/tpa-kawatuna/sejarah" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">Sejarah</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi (Dropdown Mobile) -->
                <div x-data="{ open: {{ request()->is('lacak', 'berita*', 'tentang', 'pengaduan') ? 'true' : 'false' }}, subOpen: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-base font-semibold {{ request()->is('lacak', 'berita*', 'tentang', 'pengaduan') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                        <span>{{ __('Informasi') }}</span>
                        <x-icons.ui name="chevron-down" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                    </button>
                    <div x-show="open" class="pl-4 space-y-1 mt-1" style="display: none;"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <div x-data="{ subOpen: false }">
                            <button @click="subOpen = !subOpen" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold {{ request()->is('pengaduan') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/30 dark:bg-brand-900/10' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }} focus:outline-none cursor-pointer">
                                <span>{{ __('Layanan Informasi Publik') }}</span>
                                <x-icons.ui name="chevron-right" class="h-4 w-4 transition-transform duration-200" x-bind:class="{ 'rotate-90': subOpen }" />
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
                        <a href="https://skm.go.id/share/instansi/032ced20-3ad5-4b83-97fe-044abcb65bd3/1" target="_blank" rel="noopener" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Survei Kepuasan') }}</a>
                        <a href="/tentang" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tentang') ? 'text-brand-600 dark:text-brand-400 bg-brand-50/30 dark:bg-brand-900/10' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">{{ __('Tentang Kami') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    @hasSection('full_width')
    <main id="main-content" data-public-page class="public-main dlh-public flex-1 w-full relative z-10">
        @yield('content')
    </main>
    @else
    <main id="main-content" data-public-page class="public-main dlh-public flex-1 w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 relative z-10">
        @yield('content')
    </main>
    @endif

<footer class="public-site-footer relative z-0 mt-auto bg-white dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800/80 overflow-hidden">
        {{-- Aksen gradien atas + cahaya latar --}}
        <div class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-brand-500 via-bay-500 to-brand-500" aria-hidden="true"></div>
        <div class="absolute -top-24 right-10 size-72 rounded-full bg-brand-500/5 blur-3xl pointer-events-none" aria-hidden="true"></div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-8">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-[1.4fr_repeat(5,minmax(0,1fr))] items-start gap-8 lg:gap-6 pb-10 border-b border-slate-200 dark:border-slate-800">

                <div class="col-span-2 md:col-span-4 lg:col-span-1 space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('assets/images/logo_kota_palu.webp') }}" alt="Logo Kota Palu" width="320" height="423" class="h-14 sm:h-16 w-auto object-contain drop-shadow-sm">
                        <div class="min-w-0">
                            <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base tracking-tight leading-tight">Dinas Lingkungan Hidup Kota Palu</p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed max-w-sm">
                        {{ __('Gerbang digital DLH Kota Palu untuk menyampaikan pengaduan, mengakses informasi, dan memantau tindak lanjut layanan lingkungan.') }}
                    </p>
                    <div class="flex items-center gap-2 pt-1">
                        <a href="https://www.instagram.com/dlhkotapalu" target="_blank" rel="noopener noreferrer" aria-label="Instagram DLH Kota Palu" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-white hover:bg-gradient-to-br hover:from-pink-500 hover:to-purple-500 transition-[background-color,color,transform] duration-300 hover:-translate-y-0.5" title="Instagram">
                            <x-icons.social.instagram class="w-4 h-4" />
                        </a>
                        <a href="https://www.facebook.com/share/18qHSySQr4/?locale=id_ID" target="_blank" rel="noopener noreferrer" aria-label="Facebook DLH Kota Palu" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-white hover:bg-blue-600 transition-[background-color,color,transform] duration-300 hover:-translate-y-0.5" title="Facebook">
                            <x-icons.social.facebook class="w-4 h-4" />
                        </a>
                        <a href="https://wa.me/6285191512076" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp DLH Kota Palu" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-white hover:bg-brand-600 transition-[background-color,color,transform] duration-300 hover:-translate-y-0.5" title="WhatsApp">
                            <x-icons.social.whatsapp class="w-4 h-4" />
                        </a>
                    </div>
                </div>

                <div class="col-span-1 lg:col-span-1 min-w-0 space-y-3">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-900 dark:text-white">{{ __('Pengendalian') }}</h2>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/pengaduan" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Pengaduan Pengendalian') }}</a></li>
                        <li><a href="/permohonan-rekomendasi" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Permohonan') }}</a></li>
                        <li><a href="/cek-permohonan-rekomendasi" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Cek Permohonan') }}</a></li>
                        <li><a href="/lacak" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Cek Status') }}</a></li>
                    </ul>
                </div>

                <div class="col-span-1 lg:col-span-1 min-w-0 space-y-3">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-900 dark:text-white">{{ __('Sampah & LB3') }}</h2>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/peta-persampahan" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Peta Persampahan & Armada') }}</a></li>
                        <li><a href="/pengaduan" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Pengaduan Sampah') }}</a></li>
                        <li><a href="/registrasi-usaha-lb3" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Registrasi LB3') }}</a></li>
                        <li><a href="/cek-registrasi-lb3" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Cek Registrasi LB3') }}</a></li>
                        <li><a href="/pengajuan-rintek-pertek" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Pengajuan RINTEK/PERTEK') }}</a></li>
                        <li><a href="/cek-rintek-pertek" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Cek RINTEK/PERTEK') }}</a></li>
                    </ul>
                </div>

                <div class="col-span-1 lg:col-span-1 min-w-0 space-y-3">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-900 dark:text-white">{{ __('RTH') }}</h2>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/pengaduan" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Pengaduan RTH') }}</a></li>
                        <li><a href="/pinjam-taman" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Penyewaan Taman') }}</a></li>
                        <li><a href="/cek-pinjam-taman" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Cek Penyewaan Taman') }}</a></li>
                    </ul>
                </div>

                <div class="col-span-1 lg:col-span-1 min-w-0 space-y-3">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-900 dark:text-white">{{ __('Tata Penataan') }}</h2>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/tata-lingkungan" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Tata Lingkungan') }}</a></li>
                        <li><a href="/pengaduan?bidang=tata-penataan" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Pengaduan Tata Penataan') }}</a></li>
                        <li><a href="/lacak" class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:translate-x-0.5 transition-[color,transform] duration-200">{{ __('Cek Status') }}</a></li>
                    </ul>
                </div>

                <div class="col-span-2 md:col-span-4 lg:col-span-1 min-w-0 space-y-3">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-900 dark:text-white">{{ __('Kontak') }}</h2>
                    <p class="min-h-[4.5rem] text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Jl. Kakatua No. 09, Kelurahan Tanamodindi, Kecamatan Mantikulore, Kota Palu
                    </p>
                    <a href="https://wa.me/6285191512076" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl bg-brand-50 dark:bg-brand-900/20 px-3 py-2 text-sm font-semibold text-brand-700 dark:text-brand-300 hover:bg-brand-100 dark:hover:bg-brand-900/40 transition-colors whitespace-nowrap">
                        <x-icons.social.whatsapp class="w-4 h-4 flex-shrink-0" />
                        0851-9151-2076
                    </a>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
                <p class="text-center sm:text-left">&copy; {{ date('Y') }} <span class="font-semibold text-slate-600 dark:text-slate-300">Dinas Lingkungan Hidup Kota Palu</span>. {{ __('Hak cipta dilindungi.') }}</p>
                <div class="flex items-center gap-4">
                    <a href="/kebijakan-privasi" class="hover:text-brand-600 dark:hover:text-brand-400 transition-colors">{{ __('Kebijakan Privasi') }}</a>
                    <a href="/syarat-ketentuan" class="hover:text-brand-600 dark:hover:text-brand-400 transition-colors">{{ __('Syarat & Ketentuan') }}</a>
                    <a href="https://skm.go.id/share/instansi/032ced20-3ad5-4b83-97fe-044abcb65bd3/1" target="_blank" rel="noopener" class="hover:text-brand-600 dark:hover:text-brand-400 transition-colors">{{ __('Survei IKM') }}</a>
                </div>
            </div>
        </div>
    </footer>

    @livewire('chat-bot')
    <style>
        /* Umpan balik submit yang dipakai semua form layanan publik. */
        .dlh-form--loading button[type="submit"] {
            cursor: wait !important;
            pointer-events: none;
            opacity: .88;
            transform: translateY(0) !important;
        }
        .dlh-form--loading button[type="submit"]:not([data-dlh-original-html])::before {
            content: '';
            width: 1em;
            height: 1em;
            flex: 0 0 auto;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 999px;
            animation: dlh-submit-spin .65s linear infinite;
        }
        @keyframes dlh-submit-spin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) {
            .dlh-form--loading button[type="submit"]::before { animation: none; }
        }
    </style>
    @livewireScripts
    <script>
        // Validasi file upload sisi klien untuk semua form publik.
        // Dipasang via x-on:change.capture pada ELEMEN PARENT input file:
        // listener capture di parent dieksekusi sebelum listener wire:model
        // Livewire di input, sehingga stopPropagation() benar-benar membatalkan
        // upload file yang ditolak (ukuran/format/jumlah) sebelum terkirim.
        // Pesan penolakan ditampilkan sebagai alert custom tepat di bawah
        // field (bukan alert bawaan browser) dan hilang otomatis saat file
        // valid dipilih, ditutup manual, atau setelah 12 detik.
        (() => {
            var TTL_MS = 12000;
            var ALERT_CLASS = 'dlh-file-alert';

            if (!document.getElementById('dlh-file-alert-style')) {
                var style = document.createElement('style');
                style.id = 'dlh-file-alert-style';
                style.textContent = [
                    '.dlh-file-alert{display:flex;align-items:flex-start;gap:10px;margin-top:8px;padding:10px 12px;border:1px solid #fecaca;border-left:3px solid #ef4444;border-radius:12px;background:#fef2f2;color:#991b1b;font-size:12.5px;font-weight:500;line-height:1.6;animation:dlh-file-alert-in .18s ease;}',
                    '.dlh-file-alert--closing{opacity:0;transform:translateY(-4px);transition:opacity .18s ease,transform .18s ease;}',
                    '.dlh-file-alert-icon{flex:0 0 auto;width:16px;height:16px;margin-top:2px;color:#dc2626;}',
                    '.dlh-file-alert-body{flex:1 1 auto;min-width:0;}',
                    '.dlh-file-alert-title{font-weight:700;color:#b91c1c;}',
                    '.dlh-file-alert-items{margin:2px 0 0;padding-left:16px;list-style:disc;}',
                    '.dlh-file-alert-items li{margin:1px 0;overflow-wrap:anywhere;}',
                    '.dlh-file-alert-close{flex:0 0 auto;align-self:flex-start;border:0;background:transparent;color:#b91c1c;font-size:18px;line-height:1;cursor:pointer;padding:0 2px;}',
                    '.dlh-file-alert-close:hover{color:#7f1d1d;}',
                    '.dark .dlh-file-alert{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.35);color:#fca5a5;}',
                    '.dark .dlh-file-alert-title{color:#f87171;}',
                    '.dark .dlh-file-alert-icon{color:#f87171;}',
                    '.dark .dlh-file-alert-close{color:#f87171;}',
                    '.dark .dlh-file-alert-close:hover{color:#fca5a5;}',
                    '@keyframes dlh-file-alert-in{from{opacity:0;transform:translateY(-4px);}to{opacity:1;transform:none;}}',
                ].join('');
                document.head.appendChild(style);
            }

            // Alert selalu disisipkan tepat setelah zona drop milik field
            // (drop.nextElementSibling) agar mudah ditemukan kembali.
            function findAlert(drop) {
                var next = drop.nextElementSibling;

                return next && next.classList && next.classList.contains(ALERT_CLASS) ? next : null;
            }

            function closeAlert(alert) {
                if (!alert || alert.dataset.closed === '1') {
                    return;
                }

                alert.dataset.closed = '1';
                alert.classList.add(ALERT_CLASS + '--closing');
                setTimeout(function () { alert.remove(); }, 200);
            }

            function hideAlert(drop) {
                var alert = findAlert(drop);

                if (alert) {
                    closeAlert(alert);
                }
            }

            function showAlert(drop, title, items) {
                hideAlert(drop);

                var alert = document.createElement('div');
                alert.className = ALERT_CLASS;
                alert.setAttribute('role', 'alert');

                var iconTpl = document.createElement('template');
                iconTpl.innerHTML = '<svg class="dlh-file-alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>';
                alert.appendChild(iconTpl.content.firstChild);

                var body = document.createElement('div');
                body.className = 'dlh-file-alert-body';

                var titleEl = document.createElement('span');
                titleEl.className = 'dlh-file-alert-title';
                titleEl.textContent = title;
                body.appendChild(titleEl);

                if (items && items.length) {
                    var list = document.createElement('ul');
                    list.className = 'dlh-file-alert-items';

                    items.forEach(function (item) {
                        var li = document.createElement('li');
                        li.textContent = item;
                        list.appendChild(li);
                    });

                    body.appendChild(list);
                }

                alert.appendChild(body);

                var close = document.createElement('button');
                close.type = 'button';
                close.className = 'dlh-file-alert-close';
                close.setAttribute('aria-label', 'Tutup');
                close.innerHTML = '&times;';
                close.addEventListener('click', function () { closeAlert(alert); });
                alert.appendChild(close);

                drop.parentNode.insertBefore(alert, drop.nextSibling);

                setTimeout(function () { closeAlert(alert); }, TTL_MS);

                // Saat sebagian file valid ikut terunggah, Livewire me-render
                // ulang field (morph) dan dapat menghapus elemen yang tidak ada
                // di HTML server — pasang kembali alert selama belum ditutup.
                [60, 250, 700].forEach(function (delay) {
                    setTimeout(function () {
                        if (alert.dataset.closed !== '1' && drop.isConnected && !alert.isConnected) {
                            drop.parentNode.insertBefore(alert, drop.nextSibling);
                        }
                    }, delay);
                });
            }

            window.dlhFileGuard = function (event, opts) {
                var input = event.target;

                if (!input || input.type !== 'file') {
                    return;
                }

                var files = Array.from(input.files || []);

                if (!files.length) {
                    return;
                }

                var drop = input.closest('.fi-file-drop') || input.parentElement;
                var maxSizeMB = opts.maxSizeMB || 5;
                var maxBytes = maxSizeMB * 1024 * 1024;
                var exts = opts.exts || [];
                var maxCount = opts.maxCount || 0;
                var existingCount = 0;

                if (maxCount && opts.countSelector) {
                    existingCount = document.querySelectorAll(opts.countSelector).length;
                }

                var rejectAll = function () {
                    input.value = '';
                    event.preventDefault();
                    event.stopPropagation();
                };

                // Batas jumlah file sudah tercapai → tolak seluruh seleksi baru.
                if (maxCount && existingCount >= maxCount) {
                    showAlert(drop, opts.label + ' — tidak dapat menambah file', [
                        'Jumlah file sudah mencapai batas maksimal (' + maxCount + ' file). Hapus salah satu file terlebih dahulu bila ingin mengganti.',
                    ]);
                    rejectAll();
                    return;
                }

                var room = maxCount ? maxCount - existingCount : Infinity;
                var errors = [];
                var kept = [];

                files.forEach(function (file) {
                    var ext = (file.name.split('.').pop() || '').toLowerCase();

                    if (exts.length && exts.indexOf(ext) === -1) {
                        errors.push('"' + file.name + '" tidak didukung. Format yang diterima: ' + exts.join(', ').toUpperCase() + '.');
                    } else if (file.size > maxBytes) {
                        errors.push('"' + file.name + '" melebihi ' + maxSizeMB + 'MB (ukuran ' + (file.size / 1048576).toFixed(2) + 'MB). Silakan kompres atau pilih file lain.');
                    } else if (kept.length >= room) {
                        errors.push('"' + file.name + '" tidak ditambahkan karena melebihi batas ' + maxCount + ' file.');
                    } else {
                        kept.push(file);
                    }
                });

                if (errors.length) {
                    showAlert(drop, opts.label + ' — file ditolak:', errors);
                } else {
                    hideAlert(drop);
                }

                // Semua file ditolak → kosongkan input & hentikan event agar
                // Livewire (wire:model) tidak ikut mengunggah.
                if (!kept.length) {
                    rejectAll();
                    return;
                }

                // Sebagian file ditolak → sisakan hanya file valid di input
                // sebelum event diteruskan ke Livewire.
                if (kept.length < files.length) {
                    try {
                        var dt = new DataTransfer();
                        kept.forEach(function (file) { dt.items.add(file); });
                        input.files = dt.files;
                    } catch (e) {
                        rejectAll();
                    }
                }
            };
        })();
    </script>
    <script>
        (() => {
            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || form.dataset.dlhSubmitting === 'false') return;

                form.classList.add('dlh-form--loading');
            }, true);
        })();
    </script>
    @yield('scripts')
    @stack('scripts')
</body>

</html>
