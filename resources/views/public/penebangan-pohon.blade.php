@extends('layouts.app')

@section('title', 'Penebangan & Pemangkasan Pohon - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Layanan resmi pengajuan permohonan penebangan atau pemangkasan khusus pohon pelindung/perindang di fasilitas umum dan area publik Kota Palu.')

@section('content')
<div class="public-service-page max-w-5xl mx-auto space-y-6 md:space-y-8">
    <x-public.page-hero
        badge="{{ __('Bidang Ruang Terbuka Hijau (RTH)') }}"
        title="{{ __('Permohonan Penebangan / Pemangkasan Pohon') }}"
        description="{{ __('Layanan resmi penanganan pohon pelindung/perindang yang rawan tumbang, menghalangi pandangan jalan, atau membahayakan fasilitas umum di wilayah Kota Palu.') }}"
        icon="axe"
    />

    {{-- Panduan Singkat Layanan --}}
    <div class="reveal grid gap-3 sm:grid-cols-3" aria-label="{{ __('Panduan permohonan pohon') }}">
        <div class="rounded-2xl border border-emerald-100 bg-white/90 p-4 dark:border-emerald-900/50 dark:bg-slate-900/80 shadow-xs">
            <span class="mb-3 flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                <x-icons.ui name="map-pin" class="size-5" />
            </span>
            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('Area Fasilitas Umum') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Khusus pohon perindang di pinggir jalan raya, jalur hijau, median, atau taman publik.') }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-white/90 p-4 dark:border-emerald-900/50 dark:bg-slate-900/80 shadow-xs">
            <span class="mb-3 flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                <x-icons.ui name="image" class="size-5" />
            </span>
            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('Lampirkan Foto & Titik') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Sertakan foto kondisi fisik pohon serta tandai titik lokasi pada peta interaktif.') }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-white/90 p-4 dark:border-emerald-900/50 dark:bg-slate-900/80 shadow-xs">
            <span class="mb-3 flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                <x-icons.ui name="clipboard-check" class="size-5" />
            </span>
            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('Survei & Eksekusi DLH') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Tim teknis DLH akan melakukan survei lapangan dan menjadwalkan tindakan pemangkasan.') }}</p>
        </div>
    </div>

    {{-- Komponen Livewire: Form Pengajuan & Pengecekan Status --}}
    <div class="reveal">
        <livewire:public.penebangan-pohon />
    </div>
</div>
@endsection
