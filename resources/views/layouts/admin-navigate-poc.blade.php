<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PoC wire:navigate — DLH')</title>
    {{-- Hanya CSS dari Vite — TANPA app.js, supaya tidak ada Alpine kedua dari bundle kita. --}}
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>

<body class="min-h-screen bg-slate-100 p-6 text-slate-800 antialiased">
    <div class="mx-auto max-w-2xl space-y-4">
        @include('admin.navigate-poc._nav')

        <main class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @yield('content')
        </main>

        {{-- Script ini TIDAK di-eksekusi ulang saat wire:navigate (hanya load sekali). --}}
        <script data-navigate-once>
            window.__pocOnce = (window.__pocOnce || 0) + 1;
        </script>

        <p class="text-xs leading-relaxed text-slate-400">
            Semua link memakai <code class="rounded bg-slate-200 px-1">wire:navigate</code> (SPA-like Livewire).
            Script di dalam <code>&lt;body&gt;</code> halaman baru <strong>dieksekusi ulang</strong> oleh Livewire,
            kecuali yang bertanda <code class="rounded bg-slate-200 px-1">data-navigate-once</code>.
        </p>
    </div>

    @livewireScripts
</body>

</html>