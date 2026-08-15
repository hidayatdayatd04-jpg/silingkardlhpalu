@extends('layouts.app')

@section('title', 'Profil Dinas - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Visi, misi, struktur organisasi, serta tugas dan fungsi Dinas Lingkungan Hidup Kota Palu.')

@php
    $hasStructureImage = ! empty($profil->struktur_organisasi_image);
    $assetStructurePath = 'assets/images/struktur.jpg';
    $structureImage = $hasStructureImage
        ? Storage::disk('public')->temporaryUrl($profil->struktur_organisasi_image, now()->addHours(24))
        : (file_exists(public_path($assetStructurePath)) ? asset($assetStructurePath) : null);
@endphp

@section('content')
<div
    x-data="{
        tab: window.location.hash === '#struktur-organisasi'
            ? 'struktur'
            : (window.location.hash === '#tugas-fungsi' ? 'tugas' : 'visi-misi'),
        setTab(name) {
            this.tab = name;
            const hash = name === 'visi-misi' ? '#visi-misi' : (name === 'struktur' ? '#struktur-organisasi' : '#tugas-fungsi');
            history.replaceState(null, '', hash);
            this.$nextTick(() => {
                const el = document.getElementById(hash.replace('#', ''));
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        },
        scrollToSection(hash) {
            if (!hash) return;
            const el = document.getElementById(hash.replace('#', ''));
            if (el) setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);
        }
    }"
    x-init="
        if (window.location.hash === '#struktur-organisasi') tab = 'struktur';
        else if (window.location.hash === '#tugas-fungsi') tab = 'tugas';
        else if (window.location.hash === '#visi-misi') tab = 'visi-misi';
        scrollToSection(window.location.hash);
        window.addEventListener('hashchange', () => {
            const hash = window.location.hash;
            if (hash === '#struktur-organisasi') this.tab = 'struktur';
            else if (hash === '#tugas-fungsi') this.tab = 'tugas';
            else if (hash === '#visi-misi') this.tab = 'visi-misi';
            scrollToSection(hash);
        });
    "
    class="space-y-8 pb-16"
>
    <x-public.page-hero
        title="{{ __('Profil Dinas Lingkungan Hidup') }}"
        description="{{ __('Informasi resmi mengenai visi, misi, struktur organisasi, serta tugas dan fungsi Dinas Lingkungan Hidup Kota Palu.') }}"
        badge="{{ __('Profil Instansi') }}"
    />

    <section class="grid gap-4 sm:grid-cols-3">
        <button type="button" @click="setTab('visi-misi')" class="group rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">
            <span class="inline-flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
            </span>
            <span class="mt-4 block text-base font-bold text-slate-900 dark:text-white">{{ __('Visi & Misi') }}</span>
            <span class="mt-1 block text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Arah pembangunan dan komitmen pelayanan lingkungan hidup.') }}</span>
        </button>

        <button type="button" @click="setTab('struktur')" class="group rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">
            <span class="inline-flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 8.25h12M6 15.75h12M9.75 3.75h4.5v4.5h-4.5zM3.75 15.75h4.5v4.5h-4.5zM15.75 15.75h4.5v4.5h-4.5zM12 8.25v3.75M6 12h12"/></svg>
            </span>
            <span class="mt-4 block text-base font-bold text-slate-900 dark:text-white">{{ __('Struktur Organisasi') }}</span>
            <span class="mt-1 block text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Ruang khusus untuk bagan organisasi yang akan ditambahkan dari asset.') }}</span>
        </button>

        <button type="button" @click="setTab('tugas')" class="group rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">
            <span class="inline-flex size-10 items-center justify-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-900/20 dark:text-sky-300">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M7.5 3.75h9A2.25 2.25 0 0 1 18.75 6v12A2.25 2.25 0 0 1 16.5 20.25h-9A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z"/></svg>
            </span>
            <span class="mt-4 block text-base font-bold text-slate-900 dark:text-white">{{ __('Tugas & Fungsi') }}</span>
            <span class="mt-1 block text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Uraian kewenangan, bidang kerja, dan fungsi perangkat daerah.') }}</span>
        </button>
    </section>

    <div class="sticky top-16 z-20 rounded-2xl border border-slate-200 bg-white/90 p-1.5 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
        <nav class="grid grid-cols-3 gap-1" aria-label="{{ __('Tab profil dinas') }}">
            <button type="button" @click="setTab('visi-misi')" :class="tab === 'visi-misi' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'" class="rounded-xl px-3 py-3 text-xs font-bold transition sm:text-sm">{{ __('Visi & Misi') }}</button>
            <button type="button" @click="setTab('struktur')" :class="tab === 'struktur' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'" class="rounded-xl px-3 py-3 text-xs font-bold transition sm:text-sm">{{ __('Struktur') }}</button>
            <button type="button" @click="setTab('tugas')" :class="tab === 'tugas' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'" class="rounded-xl px-3 py-3 text-xs font-bold transition sm:text-sm">{{ __('Tugas') }}</button>
        </nav>
    </div>

    <section x-show="tab === 'visi-misi'" x-cloak id="visi-misi" class="space-y-10">
        <div class="relative space-y-8">
            <div class="absolute -top-24 -left-24 w-64 h-64 bg-emerald-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 dark:opacity-20"></div>
            <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-teal-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 dark:opacity-20"></div>

            <article class="relative overflow-hidden rounded-3xl p-8 text-white sm:p-10 lg:p-14">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-700 via-teal-800 to-slate-900"></div>
                <div class="absolute inset-0">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-400/10 rounded-full -translate-y-1/2 translate-x-1/2 filter blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 w-80 h-80 bg-teal-400/10 rounded-full translate-y-1/2 -translate-x-1/2 filter blur-3xl"></div>
                </div>
                <div class="relative flex flex-col items-center text-center">
                    <span class="inline-flex size-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white ring-1 ring-white/20 backdrop-blur-sm">
                        <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                    </span>
                    <p class="mt-6 text-xs font-bold uppercase tracking-[0.3em] text-emerald-200/80">{{ __('Visi') }}</p>
                    <div class="profile-vision mt-6">
                        {!! $profil->visi_translated !!}
                    </div>

                </div>
            </article>

            <article class="relative rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xl shadow-slate-200/50 dark:border-slate-800/80 dark:bg-slate-900 dark:shadow-slate-900/50 sm:p-8 lg:p-10">
                <div class="absolute -top-px left-0 right-0 h-px bg-gradient-to-r from-transparent via-emerald-300 to-transparent dark:via-emerald-700"></div>
                <div class="flex flex-col items-center text-center mb-8">
                    <span class="inline-flex size-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 mb-4">
                        <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15L15 9.75M7.5 3.75h9A2.25 2.25 0 0118.75 6v12A2.25 2.25 0 0116.5 20.25h-9A2.25 2.25 0 015.25 18V6A2.25 2.25 0 017.5 3.75Z"/>
                        </svg>
                    </span>
                    <p class="text-xs font-bold uppercase tracking-[0.3em] text-emerald-600 dark:text-emerald-400">{{ __('Misi') }}</p>

                </div>
                <div class="profile-prose profile-mission mt-4">
                    {!! $profil->misi_translated !!}
                </div>
            </article>
        </div>
    </section>

    <section x-show="tab === 'struktur'" x-cloak id="struktur-organisasi" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
        <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-brand-600 dark:text-brand-300">{{ __('Struktur Organisasi') }}</p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('Bagan Organisasi DLH Kota Palu') }}</h2>
            </div>
        </div>

        <div class="flex min-h-[380px] items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center dark:border-slate-700 dark:bg-slate-950/40">
            @if ($structureImage)
                <img src="{{ $structureImage }}" alt="{{ __('Struktur Organisasi DLH Kota Palu') }}" loading="lazy" class="max-h-[720px] max-w-full rounded-xl object-contain">
            @else
                <div class="max-w-md">
                    <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                        <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75A2.25 2.25 0 0 1 6 4.5h12a2.25 2.25 0 0 1 2.25 2.25v10.5A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25V6.75Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 15 4.5-4.5a2.121 2.121 0 0 1 3 0l1.5 1.5 1.5-1.5a2.121 2.121 0 0 1 3 0l3 3M8.25 8.25h.008v.008H8.25V8.25Z"/></svg>
                    </span>
                    <h3 class="mt-5 text-lg font-extrabold text-slate-900 dark:text-white">{{ __('Gambar struktur belum tersedia') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Area ini sengaja dikosongkan dulu agar nanti bisa langsung diisi dengan gambar struktur organisasi dari folder asset.') }}</p>
                </div>
            @endif
        </div>
    </section>

    <section x-show="tab === 'tugas'" x-cloak id="tugas-fungsi" class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-lg shadow-slate-200/50 dark:border-slate-800/80 dark:bg-slate-900 dark:shadow-slate-900/50 sm:p-10">
        <div class="mb-10 max-w-3xl">
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-brand-600 dark:text-brand-300">{{ __('Tugas & Fungsi') }}</p>
        </div>
        <div class="profile-prose profile-prose-long">
            <h3>Tugas Dinas Lingkungan Hidup Kota Palu</h3>
            <p>Dinas Lingkungan Hidup mempunyai tugas membantu Wali Kota melaksanakan urusan Pemerintahan yang menjadi kewenangan Daerah di bidang lingkungan hidup dan Tugas Pembantuan yang diberikan kepada Daerah.</p>
            <h3>Fungsi Dinas Lingkungan Hidup Kota Palu</h3>
            <ol>
                <li>Pengoordinasian perumusan kebijakan teknis bidang lingkungan hidup;</li>
                <li>Penyelenggaraan pembinaan, pengumpulan dan pengolahan data, penyusunan rencana dan program bidang lingkungan hidup;</li>
                <li>Pengoordinasian, pengendalian dan pengawasan, serta evaluasi pelaksanaan tugas bidang lingkungan hidup;</li>
                <li>Pengelolaan perizinan dan pelaksanaan pelayanan bidang lingkungan hidup;</li>
                <li>Penyelenggaraan ketatausahaan dan tata laksana; dan</li>
                <li>Pelaksanaan fungsi lain yang diberikan oleh atasan sesuai dengan tugasnya.</li>
            </ol>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .profile-visi-card {
        background: linear-gradient(135deg, #0f172a 0%, #0c1222 50%, #0f172a 100%);
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.5);
    }

    .profile-visi-glow {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        pointer-events: none;
    }

    .profile-visi-glow-1 {
        width: 300px;
        height: 300px;
        top: -100px;
        right: -50px;
        background: rgba(16, 185, 129, 0.15);
    }

    .profile-visi-glow-2 {
        width: 250px;
        height: 250px;
        bottom: -80px;
        left: -50px;
        background: rgba(5, 150, 105, 0.12);
    }

    .profile-prose {
        color: rgb(71 85 105);
        font-size: 1rem;
        line-height: 1.85;
    }

    .profile-vision p {
        margin: 0;
        color: white;
        font-size: clamp(1.8rem, 3.5vw, 3.2rem);
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.02em;
        text-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
    }

    .dark .profile-prose {
        color: rgb(203 213 225);
    }

    .profile-prose p {
        margin: 1.1rem 0;
    }

    .profile-prose h3 {
        margin-top: 2.25rem;
        margin-bottom: 1rem;
        color: rgb(15 23 42);
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.4;
        letter-spacing: -0.01em;
    }

    .dark .profile-prose h3 {
        color: white;
    }

    .profile-prose ol,
    .profile-prose ul {
        margin: 1.25rem 0;
        padding-left: 0;
        list-style: none;
    }

    .profile-prose li {
        position: relative;
        margin-top: 0.85rem;
        border-radius: 1rem;
        border: 1px solid rgb(226 232 240);
        background: linear-gradient(135deg, rgb(248 250 252) 0%, rgb(241 245 249) 100%);
        padding: 1rem 1.15rem 1rem 3rem;
        transition: all 0.25s ease;
    }

    .profile-prose li:hover {
        border-color: rgb(16 185 112 / 0.3);
        box-shadow: 0 4px 16px rgb(16 185, 112, 0.1);
        transform: translateY(-2px);
    }

    .dark .profile-prose li {
        border-color: rgb(30 41 59);
        background: linear-gradient(135deg, rgb(15 23 42 / 0.55) 0%, rgb(15 23 42 / 0.7) 100%);
    }

    .dark .profile-prose li:hover {
        border-color: rgb(16 185 112 / 0.4);
        box-shadow: 0 4px 20px rgb(16 185, 112, 0.15);
    }

    .profile-prose li::before {
        content: "";
        position: absolute;
        left: 1rem;
        top: 1.42rem;
        width: 0.6rem;
        height: 0.6rem;
        border-radius: 999px;
        background: linear-gradient(135deg, rgb(5 150 105) 0%, rgb(16 185 112) 100%);
        box-shadow: 0 0 0 5px rgb(16 185, 112, 0.15);
    }

    .profile-mission ol {
        display: grid;
        gap: 1.25rem;
    }

    @media (min-width: 900px) {
        .profile-mission ol {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .profile-mission li {
        margin-top: 0;
        min-height: 7rem;
        padding: 1.25rem 1.35rem 1.25rem 3.35rem;
        border-radius: 1.25rem;
        border: 1px solid rgb(226 232 240);
        background: linear-gradient(135deg, rgb(248 250 252) 0%, rgb(241 245 249) 100%);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .profile-mission li::before {
        content: attr(data-number);
        position: absolute;
        left: 1rem;
        top: 1.5rem;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 999px;
        background: linear-gradient(135deg, rgb(5 150 105) 0%, rgb(16 185 112) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        color: white;
        box-shadow: 0 0 0 5px rgb(16 185, 112, 0.15);
    }

    .profile-mission li::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90, rgb(5 150 105), rgb(16 185 112));
        opacity: 0.5;
    }

    .profile-mission li:hover {
        border-color: rgb(16 185 112 / 0.3);
        box-shadow: 0 10px 25px rgb(16 185, 112, 0.1);
        transform: translateY(-4px);
    }

    .profile-mission li:hover::after {
        opacity: 1;
    }

    .dark .profile-mission li {
        border-color: rgb(30 41 59);
        background: linear-gradient(135deg, rgb(15 23 42 / 0.55) 0%, rgb(15 23 42 / 0.7) 100%);
    }

    .dark .profile-mission li::after {
        background: linear-gradient(90, rgb(16 185 112), rgb(5 150 105));
    }

    .dark .profile-mission li:hover {
        border-color: rgb(16 185 112 / 0.4);
        box-shadow: 0 10px 30px rgb(16 185, 112, 0.15);
    }

    .profile-prose-long {
        max-width: 80ch;
    }

    .profile-prose-long h3 {
        border-top: 1px solid rgb(226 232 240);
        padding-top: 1.75rem;
    }

    .profile-prose-long h3:first-child {
        border-top: none;
        padding-top: 0;
    }

    .dark .profile-prose-long h3 {
        border-top-color: rgb(30 41 59);
    }

    .profile-prose-long ol li {
        background: linear-gradient(135deg, rgb(240 253 250) 0%, rgb(236 253 245) 100%);
        border-color: rgb(16 185 112 / 0.15);
    }

    .dark .profile-prose-long ol li {
        background: linear-gradient(135deg, rgb(16 185 112 / 0.08) 0%, rgb(16 185 112 / 0.12) 100%);
        border-color: rgb(16 185 112 / 0.25);
    }

    .profile-prose-long ol li::before {
        background: linear-gradient(135deg, rgb(5 150 105) 0%, rgb(16 185 112) 100%);
        box-shadow: 0 0 0 5px rgb(16 185, 112, 0.2);
    }
</style>
@endpush
