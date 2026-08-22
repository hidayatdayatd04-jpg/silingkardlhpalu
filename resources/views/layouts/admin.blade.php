<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a2f24">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Prefix panel admin untuk skrip klien (mis. cek halaman backup di alpine.js). --}}
    <script>window.ADMIN_BASE_URL = @js('/'.trim((string) config('app.admin_path'), '/'));</script>
    <title>@yield('title', 'Admin DLH Kota Palu')</title>
    <meta name="description" content="Panel admin Ruang Kendali Operasional Dinas Lingkungan Hidup Kota Palu — kelola permohonan, pengaduan, sampah, RTH, tata penataan, konten, dan pengguna.">
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    @include('partials.web-fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin-common.js'])
    <style>
        /* Hide scrollbar on sidebar nav */
        .sidebar-nav::-webkit-scrollbar { display: none; }
        .sidebar-nav { -ms-overflow-style: none; scrollbar-width: none; }
        /* Hide scrollbar on main content */
        .main-scroll::-webkit-scrollbar { display: none; }
        .main-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        /* Prevent Alpine flash before init */
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="admin-shell min-h-screen text-ink-900 antialiased" x-data data-alpine-bootstrap>
    @php
        $user = auth()->user();
        $adminGroups = \App\Support\Admin\AdminRegistry::forUser($user);
        $allGroups = \App\Support\Admin\AdminRegistry::all();
        $activeResource = request()->route('resource');
        $heading = '';
    @endphp

    <a href="#admin-main-content" class="admin-skip-link">
        Lewati navigasi ke konten utama
    </a>

    <div class="admin-shell flex h-screen overflow-hidden">
        <!-- Sidebar Component -->
        <x-admin.sidebar :groups="$adminGroups" :allGroups="$allGroups" :user="$user" />

        <div class="admin-main-scroll main-scroll flex min-h-0 min-w-0 flex-1 flex-col overflow-y-auto">
            <!-- Topbar Component -->
            <x-admin.topbar :heading="$heading = trim(view()->yieldContent('heading') ?: 'Dashboard')" />

            @if(trim(view()->yieldContent('full_width')))
                <main id="admin-main-content" class="admin-main flex min-h-0 flex-1 flex-col" tabindex="-1">
                    @yield('content')
                </main>
            @else
            <main id="admin-main-content" class="admin-main mx-auto w-full max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8" tabindex="-1">
                <div class="space-y-6" data-page-content>
                <!-- Flash Messages -->
                @if(session('success'))
                    <x-admin.alert type="success" dismissible class="mb-6">
                        {{ session('success') }}
                    </x-admin.alert>
                @endif

                @if(session('error'))
                    <x-admin.alert type="error" dismissible class="mb-6">
                        {{ session('error') }}
                    </x-admin.alert>
                @endif

                @if(session('warning'))
                    <x-admin.alert type="warning" dismissible class="mb-6">
                        {{ session('warning') }}
                    </x-admin.alert>
                @endif

                @if(session('info'))
                    <x-admin.alert type="info" dismissible class="mb-6">
                        {{ session('info') }}
                    </x-admin.alert>
                @endif

                @yield('content')
                </div>
            </main>
            @endif
        </div>
    </div>

    {{-- Command Palette (Ctrl+K) --}}
    <x-admin.command-palette :user="$user" />

    {{-- Widget progres backup/restore latar belakang --}}
    <x-admin.backup-progress />

    {{-- Toast host (Alpine store 'toasts') --}}
    <x-admin.toast />

    @livewireScripts
    @stack('styles')
    @stack('scripts')
</body>

</html>
