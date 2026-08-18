@extends('layouts.app')

@section('title', 'Registrasi Usaha LB3 - DLH Kota Palu')

@section('content')
<div class="public-service-page max-w-3xl mx-auto space-y-6 md:space-y-8">
    <x-public.page-hero badge="{{ __('Sampah & LB3') }}" title="{{ __('Registrasi Pelaku Usaha LB3') }}" description="{{ __('Daftarkan perusahaan/pelaku usaha pengelola limbah B3 untuk mendapatkan nomor registrasi resmi.') }}" icon="building" />

    <div class="reveal rounded-[1.4rem] border border-emerald-100 bg-gradient-to-r from-emerald-50/80 via-white to-brand-50/70 p-5 dark:border-emerald-900/50 dark:from-emerald-950/25 dark:via-slate-900 dark:to-brand-950/25">
        <div class="flex items-start gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-800/15"><x-icons.ui name="leaf" class="size-5" /></span>
            <div>
                <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('Registrasi yang lebih tertata') }}</p>
                <p class="mt-1 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Lengkapi data usaha dan kontak aktif agar nomor registrasi dapat digunakan untuk pelacakan berikutnya.') }}</p>
            </div>
        </div>
    </div>

    <div class="reveal">
        <livewire:public.registrasi-usaha-lb3 />
    </div>
</div>
@endsection
