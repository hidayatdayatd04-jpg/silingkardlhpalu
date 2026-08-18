@extends('layouts.app')

@section('title', 'Penyewaan Taman - DLH Kota Palu')

@section('content')
<div class="public-service-page max-w-5xl mx-auto space-y-6 md:space-y-8">
    <x-public.page-hero badge="{{ __('Bidang RTH') }}" title="{{ __('Permohonan Penyewaan Taman') }}" description="{{ __('Ajukan penyewaan taman kota untuk kegiatan komunitas.') }}" icon="leaf" />

    <div class="reveal grid gap-3 sm:grid-cols-3" aria-label="{{ __('Panduan penyewaan taman') }}">
        <div class="rounded-2xl border border-emerald-100 bg-white/90 p-4 dark:border-emerald-900/50 dark:bg-slate-900/80">
            <span class="mb-3 flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300"><x-icons.ui name="calendar" class="size-5" /></span>
            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('Pilih jadwal') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Tentukan waktu kegiatan yang sesuai dengan rencana acara Anda.') }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-white/90 p-4 dark:border-emerald-900/50 dark:bg-slate-900/80">
            <span class="mb-3 flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300"><x-icons.ui name="document" class="size-5" /></span>
            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('Lengkapi dokumen') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Siapkan berkas yang diminta pada formulir pengajuan.') }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-white/90 p-4 dark:border-emerald-900/50 dark:bg-slate-900/80">
            <span class="mb-3 flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300"><x-icons.ui name="shield" class="size-5" /></span>
            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('Jaga kebersihan') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Cantumkan komitmen menjaga area taman selama kegiatan berlangsung.') }}</p>
        </div>
    </div>

    <div class="reveal">
        <livewire:public.pinjam-taman />
    </div>
</div>
@endsection
