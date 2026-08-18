@extends('layouts.app')

@section('title', 'Pengajuan RINTEK/PERTEK - Sampah & LB3 DLH Kota Palu')

@section('content')
<div class="public-service-page max-w-4xl mx-auto space-y-6 md:space-y-8">
    <x-public.page-hero badge="{{ __('Sampah & LB3') }}" title="{{ __('Pengajuan RINTEK / PERTEK') }}" description="{{ __('Ajukan rekomendasi teknis dan persetujuan teknis pengelolaan lingkungan beserta kelengkapan dokumen.') }}" icon="document" />

    <div class="reveal grid gap-3 sm:grid-cols-3" aria-label="{{ __('Panduan pengajuan RINTEK dan PERTEK') }}">
        <div class="rounded-2xl border border-brand-100 bg-white/90 p-4 dark:border-brand-900/50 dark:bg-slate-900/80">
            <span class="mb-3 flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300"><x-icons.ui name="building" class="size-5" /></span>
            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('Identitas pemohon') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Masukkan data usaha dan penanggung jawab dengan lengkap.') }}</p>
        </div>
        <div class="rounded-2xl border border-brand-100 bg-white/90 p-4 dark:border-brand-900/50 dark:bg-slate-900/80">
            <span class="mb-3 flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300"><x-icons.ui name="upload" class="size-5" /></span>
            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('Dokumen pendukung') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Unggah dokumen sesuai jenis pengajuan yang dipilih.') }}</p>
        </div>
        <div class="rounded-2xl border border-brand-100 bg-white/90 p-4 dark:border-brand-900/50 dark:bg-slate-900/80">
            <span class="mb-3 flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300"><x-icons.ui name="search" class="size-5" /></span>
            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('Lacak pengajuan') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Simpan nomor pengajuan untuk melihat perkembangan status.') }}</p>
        </div>
    </div>

    <div class="reveal">
        <livewire:public.pengajuan-rintek-pertek />
    </div>
</div>
@endsection
