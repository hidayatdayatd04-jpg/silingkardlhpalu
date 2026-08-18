@extends('layouts.admin')

@section('title', ($title ?? 'Kesekretariatan').' - Admin DLH')
@section('heading', $title ?? 'Kesekretariatan')

@section('content')
<div class="admin-state-copy flex min-h-[60vh] flex-col items-center justify-center px-4 text-center">
    <div class="relative mb-8">
        <div class="absolute inset-0 rounded-full bg-brand-400/20 blur-3xl"></div>
        <span class="relative inline-flex size-24 items-center justify-center rounded-3xl bg-gradient-to-br from-brand-50 to-bay-50 dark:from-brand-900/25 dark:to-bay-900/20 ring-1 ring-brand-200/70 dark:ring-brand-800">
            <x-admin.icon name="settings" :size="48" class="text-brand-500 dark:text-brand-400" />
        </span>
    </div>

    <span class="inline-flex items-center gap-2 rounded-full bg-clay-50 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-[0.1em] text-clay-700 dark:bg-clay-900/25 dark:text-clay-300 mb-5">
        <span class="relative flex size-2"><span class="status-ping absolute inline-flex h-full w-full rounded-full bg-clay-400"></span><span class="relative inline-flex size-2 rounded-full bg-clay-500"></span></span>
        {{ __('Segera Hadir') }}
    </span>
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:text-3xl tracking-tight">{{ $title ?? 'Kesekretariatan' }}</h1>
    <p class="mt-4 max-w-md text-base leading-relaxed text-slate-500 dark:text-slate-400">
        {{ __('Modul Kesekretariatan sedang dalam tahap pengembangan. Silakan kunjungi kembali nanti untuk mengelola data dan informasi Sekretariat DLH Kota Palu.') }}
    </p>

    <a href="{{ route('admin.dashboard') }}" class="group mt-8 inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-brand-600/25 transition-[background-color,box-shadow,transform] duration-200 hover:-translate-y-0.5 hover:bg-brand-700 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950">
        <x-admin.icon name="arrow-left" :size="16" class="transition-transform duration-200 group-hover:-translate-x-0.5" />
        {{ __('Kembali ke Dashboard') }}
    </a>
</div>
@endsection
