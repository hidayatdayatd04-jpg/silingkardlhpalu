@extends('layouts.admin-navigate-poc')

@section('title', 'Index — PoC wire:navigate')

@section('content')
    <h1 class="text-xl font-extrabold text-slate-900">Index — PoC wire:navigate</h1>
    <p class="mt-1 text-sm text-slate-500">
        Navigasi antar-halaman ini memakai <code class="rounded bg-slate-100 px-1">wire:navigate</code> milik Livewire 4:
        halaman di-fetch dengan <code>fetch()</code>, lalu <code>&lt;body&gt;</code> di-swap tanpa reload penuh.
        JS/CSS di-<code>&lt;head&gt;</code> dievaluasi sekali saja; script di <code>&lt;body&gt;</code> dievaluasi ulang
        setiap kali halaman baru masuk.
    </p>

    <div class="mt-4 grid gap-3 sm:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
            <p class="font-bold text-slate-700">Script body (dievaluasi ulang)</p>
            <p id="poc-body-visits" class="mt-1 font-mono text-emerald-700">0</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
            <p class="font-bold text-slate-700">Script data-navigate-once (sekali saja)</p>
            <p id="poc-once" class="mt-1 font-mono text-amber-700">0</p>
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4">
        <p class="text-sm font-bold text-blue-700">Alpine (dari bundle Livewire) — counter &ldquo;survive&rdquo; navigasi SPA
            secara konseptual, tapi state komponen di-reset tiap pindah halaman (sesuai dokumentasi Livewire).</p>
        <div x-data="{ count: 0 }" class="mt-2 flex items-center gap-2">
            <button @click="count--" class="rounded-lg bg-slate-700 px-3 py-1 text-xs font-bold text-white">-</button>
            <span class="font-mono text-lg font-bold" x-text="count">0</span>
            <button @click="count++" class="rounded-lg bg-slate-700 px-3 py-1 text-xs font-bold text-white">+</button>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Script yang dievaluasi ulang saat wire:navigate membawa halaman ini lagi. --}}
    <script>
        window.__pocBodyVisits = (window.__pocBodyVisits || 0) + 1;
        var elVisits = document.getElementById('poc-body-visits');
        if (elVisits) elVisits.textContent = window.__pocBodyVisits;
        var elOnce = document.getElementById('poc-once');
        if (elOnce) elOnce.textContent = window.__pocOnce || 1;
    </script>
@endpush