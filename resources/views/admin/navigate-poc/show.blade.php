@extends('layouts.admin-navigate-poc')

@section('title', 'Item '.$n.' — PoC wire:navigate')

@section('content')
    <h1 class="text-xl font-extrabold text-slate-900">Detail Item #{{ $n }}</h1>
    <p class="mt-1 text-sm text-slate-500">
        Halaman ini juga di-swap via <code class="rounded bg-slate-100 px-1">wire:navigate</code>.
        Klik <strong>Item 3</strong> di nav untuk membandingkan perilaku <em>full-page reload</em> biasa.
    </p>

    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
        <p class="font-bold text-slate-700">Script body — run count (halaman ini)</p>
        <p id="poc-show-visits" class="mt-1 font-mono text-emerald-700">0</p>
    </div>

    <div class="mt-4 flex gap-2">
        <a wire:navigate href="{{ route('admin.navigate-poc.index') }}"
           class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-700">← Kembali ke Index</a>
        <a wire:navigate href="{{ route('admin.navigate-poc.show', ($n + 1) % 5 + 1) }}"
           class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-500">Lanjut #{{ ($n + 1) % 5 + 1 }}</a>
    </div>
@endsection

@push('scripts')
    <script>
        var key = 'poc-show-visits-' + {{ (int) $n }};
        window[key] = (window[key] || 0) + 1;
        var el = document.getElementById('poc-show-visits');
        if (el) el.textContent = window[key];
    </script>
@endpush