@extends('layouts.app')

@section('title', 'UPTD TPA Kawatuna - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'UPTD TPA Kawatuna Dinas Lingkungan Hidup Kota Palu.')

@section('content')
<div class="reveal is-revealed flex flex-col items-center justify-center min-h-[62vh] text-center px-4">
    <div class="relative mb-8">
        <div class="absolute inset-0 bg-brand-400/20 blur-3xl rounded-full animate-pulse"></div>
        <span class="relative inline-flex size-24 items-center justify-center rounded-3xl bg-gradient-to-br from-brand-50 to-bay-50 dark:from-brand-900/25 dark:to-bay-900/20 ring-1 ring-brand-200/70 dark:ring-brand-800">
            <x-icons.ui name="tool" class="size-12 text-brand-500 dark:text-brand-400" />
        </span>
    </div>

    <span class="inline-flex items-center gap-2 rounded-full bg-clay-50 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-[0.1em] text-clay-700 dark:bg-clay-900/25 dark:text-clay-300 mb-5">
        <span class="relative flex size-2"><span class="status-ping absolute inline-flex h-full w-full rounded-full bg-clay-400"></span><span class="relative inline-flex size-2 rounded-full bg-clay-500"></span></span>
        {{ __('Segera Hadir') }}
    </span>
    <h1 class="text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl tracking-tight">UPTD TPA Kawatuna</h1>

    <a href="/" class="group mt-8 inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-brand-600/25 transition-all duration-300 hover:-translate-y-0.5 hover:bg-brand-700 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950">
        <x-icons.ui name="arrow-left" class="size-4 transition-transform group-hover:-translate-x-0.5" />
        {{ __('Kembali ke Beranda') }}
    </a>
</div>
@endsection
