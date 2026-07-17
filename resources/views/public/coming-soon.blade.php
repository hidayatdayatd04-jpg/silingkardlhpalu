@extends('layouts.app')

@section('title', $title . ' - DLH Kota Palu')
@section('description', 'Halaman ' . $title . ' Dinas Lingkungan Hidup Kota Palu.')

@section('content')
<div class="reveal is-revealed flex flex-col items-center justify-center min-h-[62vh] text-center px-4">
    <div class="relative mb-8">
        <div class="absolute inset-0 bg-brand-400/20 blur-3xl rounded-full animate-pulse"></div>
        <span class="relative inline-flex size-24 items-center justify-center rounded-3xl bg-gradient-to-br from-brand-50 to-bay-50 dark:from-brand-900/25 dark:to-bay-900/20 ring-1 ring-brand-200/70 dark:ring-brand-800">
            <svg class="size-12 text-brand-500 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
            </svg>
        </span>
    </div>

    <span class="inline-flex items-center gap-2 rounded-full bg-clay-50 px-3.5 py-1.5 text-xs font-bold uppercase tracking-[0.18em] text-clay-700 dark:bg-clay-900/25 dark:text-clay-300 mb-5">
        <span class="relative flex size-2"><span class="status-ping absolute inline-flex h-full w-full rounded-full bg-clay-400"></span><span class="relative inline-flex size-2 rounded-full bg-clay-500"></span></span>
        {{ __('Segera Hadir') }}
    </span>
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white sm:text-4xl tracking-tight">{{ $title }}</h1>
    <p class="mt-4 max-w-md text-base leading-relaxed text-slate-500 dark:text-slate-400">
        {{ __('Halaman ini sedang dalam tahap pengembangan. Silakan kunjungi kembali nanti untuk mendapatkan informasi terkini dari Dinas Lingkungan Hidup Kota Palu.') }}
    </p>

    <a href="/" class="group mt-8 inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-brand-600/25 transition-all duration-300 hover:-translate-y-0.5 hover:bg-brand-700 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950">
        <svg class="size-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        {{ __('Kembali ke Beranda') }}
    </a>
</div>
@endsection
