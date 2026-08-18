@extends('layouts.app')

@section('title', 'Lacak Laporan - DLH Kota Palu')
@section('description', 'Masukkan nomor tiket atau nomor telepon aduan Anda untuk memantau status verifikasi dan tindak lanjut penanganan pohon oleh Dinas Lingkungan Hidup Kota Palu.')

@section('content')
<div class="public-service-page max-w-3xl mx-auto space-y-6 md:space-y-8">
    <x-public.page-hero
        badge="{{ __('Pelacakan Layanan') }}"
        title="{{ __('Lacak Status Aduan') }}"
        description="{{ __('Masukkan nomor tiket atau nomor telepon untuk melihat status verifikasi dan tindak lanjut petugas.') }}"
        icon="search"
    />

    <div class="reveal grid gap-3 sm:grid-cols-3" aria-label="{{ __('Panduan pelacakan aduan') }}">
        <div class="rounded-2xl border border-brand-100 bg-white/90 p-4 shadow-[0_12px_30px_-24px_rgba(20,106,68,0.42)] dark:border-brand-900/50 dark:bg-slate-900/80">
            <span class="mb-3 flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300"><x-icons.ui name="document" class="size-5" /></span>
            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('Siapkan tiket') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Gunakan nomor tiket atau telepon yang dipakai saat melapor.') }}</p>
        </div>
        <div class="rounded-2xl border border-brand-100 bg-white/90 p-4 shadow-[0_12px_30px_-24px_rgba(20,106,68,0.42)] dark:border-brand-900/50 dark:bg-slate-900/80">
            <span class="mb-3 flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300"><x-icons.ui name="search" class="size-5" /></span>
            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('Lihat perkembangan') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Status laporan ditampilkan dalam satu tampilan yang ringkas.') }}</p>
        </div>
        <div class="rounded-2xl border border-brand-100 bg-white/90 p-4 shadow-[0_12px_30px_-24px_rgba(20,106,68,0.42)] dark:border-brand-900/50 dark:bg-slate-900/80">
            <span class="mb-3 flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300"><x-icons.ui name="shield" class="size-5" /></span>
            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('Data tetap terjaga') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Masukkan data pelacakan Anda hanya pada halaman layanan resmi ini.') }}</p>
        </div>
    </div>

    <div class="reveal">
        <livewire:public.lacak-laporan />
    </div>
</div>
@endsection
