@extends('layouts.app')

@section('title', 'Pengaduan Masyarakat - DLH Kota Palu')
@section('description', 'Formulir pengaduan masyarakat terpadu untuk semua bidang: Pengendalian, Sampah & LB3, Tata Penataan, dan RTH.')
@section('full_width', '')

@section('content')
<div class="public-service-page max-w-3xl mx-auto space-y-6 md:space-y-8">
    <x-public.page-hero
        badge="{{ __('Layanan Informasi Publik') }}"
        title="{{ __('Pengaduan Masyarakat') }}"
        description="{{ __('Pilih bidang terkait dan sampaikan pengaduan Anda melalui formulir di bawah ini.') }}"
        icon="message"
    />

    <div class="reveal rounded-[1.4rem] border border-brand-100 bg-gradient-to-r from-brand-50/80 via-white to-emerald-50/60 px-5 py-4 shadow-[0_16px_36px_-30px_rgba(20,106,68,0.46)] dark:border-brand-900/50 dark:from-brand-950/30 dark:via-slate-900 dark:to-emerald-950/20">
        <div class="flex gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-700 text-white shadow-lg shadow-brand-800/15"><x-icons.ui name="shield" class="size-5" /></span>
            <div>
                <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('Sampaikan laporan secara jelas dan bertanggung jawab') }}</p>
                <p class="mt-1 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Pilih bidang yang paling sesuai, sertakan lokasi dan keterangan yang membantu petugas memverifikasi laporan Anda.') }}</p>
            </div>
        </div>
    </div>

    <div class="reveal">
        <livewire:public.pengaduan-unified />
    </div>
</div>
@endsection
@push('scripts')
{{-- Task 5: form pengaduan unified lazy-load peta via ensureMaplibreLoaded --}}
@endpush
