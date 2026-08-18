@extends('layouts.app')

@section('title', 'Permohonan/Rekomendasi - Bidang Pengendalian DLH Kota Palu')
@section('description', 'Formulir permohonan dan rekomendasi lingkungan untuk pelaku usaha di Kota Palu.')

@section('content')
<div class="public-service-page max-w-3xl mx-auto space-y-6 md:space-y-8">
    <x-public.page-hero badge="{{ __('Bidang Pengendalian') }}" title="{{ __('Permohonan/Rekomendasi') }}" description="{{ __('Ajukan permohonan rekomendasi lingkungan dengan melengkapi data perusahaan dan dokumen pendukung.') }}" icon="document" />

    <div class="reveal grid gap-3 sm:grid-cols-3" aria-label="{{ __('Tahapan permohonan rekomendasi') }}">
        <div class="rounded-2xl border border-brand-100 bg-white/90 p-4 dark:border-brand-900/50 dark:bg-slate-900/80">
            <span class="mb-3 flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300"><x-icons.ui name="building" class="size-5" /></span>
            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('1. Data usaha') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Lengkapi identitas perusahaan dan kontak penanggung jawab.') }}</p>
        </div>
        <div class="rounded-2xl border border-brand-100 bg-white/90 p-4 dark:border-brand-900/50 dark:bg-slate-900/80">
            <span class="mb-3 flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300"><x-icons.ui name="upload" class="size-5" /></span>
            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('2. Unggah berkas') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Siapkan surat permohonan dan dokumen pendukung yang diperlukan.') }}</p>
        </div>
        <div class="rounded-2xl border border-brand-100 bg-white/90 p-4 dark:border-brand-900/50 dark:bg-slate-900/80">
            <span class="mb-3 flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300"><x-icons.ui name="copy" class="size-5" /></span>
            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('3. Simpan tiket') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Gunakan nomor tiket untuk memantau perkembangan pengajuan.') }}</p>
        </div>
    </div>

    <div class="reveal">
        <livewire:public.permohonan-rekomendasi />
    </div>
</div>
@endsection
