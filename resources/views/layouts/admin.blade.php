<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="theme-color" content="#0a2f24">
    <title>@yield('title', 'Admin DLH Kota Palu')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Hide scrollbar on sidebar nav */
        .sidebar-nav::-webkit-scrollbar { display: none; }
        .sidebar-nav { -ms-overflow-style: none; scrollbar-width: none; }
        /* Hide scrollbar on main content */
        .main-scroll::-webkit-scrollbar { display: none; }
        .main-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', {
                collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
                toggle() {
                    this.collapsed = !this.collapsed;
                    localStorage.setItem('sidebar-collapsed', this.collapsed);
                }
            });
        });
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen text-ink-900 antialiased" x-data style="background: var(--gradient-page);">
    @php
        $user = auth()->user();
        $adminGroups = \App\Support\Admin\AdminRegistry::forUser($user);
        $allGroups = \App\Support\Admin\AdminRegistry::all();
        $activeResource = request()->route('resource');
        $heading = '';
    @endphp

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Component -->
        <x-admin.sidebar :groups="$adminGroups" :allGroups="$allGroups" :user="$user" />

        <div class="main-scroll flex min-w-0 flex-1 flex-col {{ trim(view()->yieldContent('full_width')) ? '' : 'overflow-y-auto' }}">
            <!-- Topbar Component -->
            <x-admin.topbar :heading="$heading = trim(view()->yieldContent('heading') ?: 'Dashboard')" />

            @if(trim(view()->yieldContent('full_width')))
                @yield('content')
            @else
            <main class="mx-auto w-full max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8">
                <div class="space-y-6">
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

    {{-- Toast host (Alpine store 'toasts') --}}
    <x-admin.toast />

    @stack('styles')
    @stack('scripts')
</body>

</html>
