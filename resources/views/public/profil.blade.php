@extends('layouts.app')

@section('title', 'Profil Dinas - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Visi, misi, struktur organisasi, serta tugas dan fungsi Dinas Lingkungan Hidup Kota Palu.')

@php
    $assetStructurePath = 'assets/images/struktur.jpg';
    $structureImage = file_exists(public_path($assetStructurePath)) ? asset($assetStructurePath) : null;
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
    class="profile-page space-y-8 pb-20 sm:space-y-10"
>
    <x-public.page-hero
        title="{{ __('Profil Dinas Lingkungan Hidup') }}"
        description="{{ __('Informasi resmi mengenai visi, misi, struktur organisasi, serta tugas dan fungsi Dinas Lingkungan Hidup Kota Palu.') }}"
        badge="{{ __('Profil Instansi') }}"
        icon="building"
    />

    <section class="reveal grid gap-3 sm:grid-cols-3" aria-label="{{ __('Jelajahi informasi profil') }}">
        <button
            type="button"
            @click="setTab('visi-misi')"
            :aria-pressed="tab === 'visi-misi'"
            class="group relative overflow-hidden rounded-2xl border border-brand-200/80 bg-white p-5 text-left shadow-[0_12px_32px_-24px_rgba(15,23,42,0.45)] transition-[transform,border-color,box-shadow] duration-300 hover:-translate-y-1 hover:border-brand-400 hover:shadow-[0_18px_36px_-22px_rgba(5,150,105,0.35)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:border-brand-900/70 dark:bg-slate-900 dark:hover:border-brand-600 dark:focus-visible:ring-offset-slate-950"
        >
            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-500 via-emerald-400 to-teal-400"></span>
            <span class="inline-flex size-11 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-100 transition-transform duration-300 group-hover:scale-105 dark:bg-brand-900/35 dark:text-brand-200 dark:ring-brand-800/70">
                <x-icons.visi class="size-5" />
            </span>
            <span class="mt-5 flex items-center justify-between gap-3">
                <span class="block text-base font-extrabold tracking-[-0.02em] text-slate-900 dark:text-white">{{ __('Visi & Misi') }}</span>
                <x-icons.ui name="arrow-right" class="size-4 text-brand-600 transition-transform duration-300 group-hover:translate-x-1 dark:text-brand-300" />
            </span>
            <span class="mt-1.5 block text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Arah pembangunan dan komitmen pelayanan lingkungan hidup.') }}</span>
        </button>

        <button
            type="button"
            @click="setTab('struktur')"
            :aria-pressed="tab === 'struktur'"
            class="group relative overflow-hidden rounded-2xl border border-amber-200/80 bg-white p-5 text-left shadow-[0_12px_32px_-24px_rgba(15,23,42,0.45)] transition-[transform,border-color,box-shadow] duration-300 hover:-translate-y-1 hover:border-amber-400 hover:shadow-[0_18px_36px_-22px_rgba(180,83,9,0.26)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:border-amber-900/60 dark:bg-slate-900 dark:hover:border-amber-600 dark:focus-visible:ring-offset-slate-950"
        >
            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 via-yellow-400 to-orange-400"></span>
            <span class="inline-flex size-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-700 ring-1 ring-amber-100 transition-transform duration-300 group-hover:scale-105 dark:bg-amber-900/25 dark:text-amber-200 dark:ring-amber-800/70">
                <x-icons.terintegrasi class="size-5" />
            </span>
            <span class="mt-5 flex items-center justify-between gap-3">
                <span class="block text-base font-extrabold tracking-[-0.02em] text-slate-900 dark:text-white">{{ __('Struktur Organisasi') }}</span>
                <x-icons.ui name="arrow-right" class="size-4 text-amber-600 transition-transform duration-300 group-hover:translate-x-1 dark:text-amber-300" />
            </span>
            <span class="mt-1.5 block text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Ruang khusus untuk bagan organisasi yang akan ditambahkan dari asset.') }}</span>
        </button>

        <button
            type="button"
            @click="setTab('tugas')"
            :aria-pressed="tab === 'tugas'"
            class="group relative overflow-hidden rounded-2xl border border-sky-200/80 bg-white p-5 text-left shadow-[0_12px_32px_-24px_rgba(15,23,42,0.45)] transition-[transform,border-color,box-shadow] duration-300 hover:-translate-y-1 hover:border-sky-400 hover:shadow-[0_18px_36px_-22px_rgba(2,132,199,0.28)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 dark:border-sky-900/60 dark:bg-slate-900 dark:hover:border-sky-600 dark:focus-visible:ring-offset-slate-950"
        >
            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-sky-500 via-cyan-400 to-teal-400"></span>
            <span class="inline-flex size-11 items-center justify-center rounded-2xl bg-sky-50 text-sky-700 ring-1 ring-sky-100 transition-transform duration-300 group-hover:scale-105 dark:bg-sky-900/25 dark:text-sky-200 dark:ring-sky-800/70">
                <x-icons.ui name="document" class="size-5" />
            </span>
            <span class="mt-5 flex items-center justify-between gap-3">
                <span class="block text-base font-extrabold tracking-[-0.02em] text-slate-900 dark:text-white">{{ __('Tugas & Fungsi') }}</span>
                <x-icons.ui name="arrow-right" class="size-4 text-sky-600 transition-transform duration-300 group-hover:translate-x-1 dark:text-sky-300" />
            </span>
            <span class="mt-1.5 block text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Uraian kewenangan, bidang kerja, dan fungsi perangkat daerah.') }}</span>
        </button>
    </section>

    <div class="sticky top-[4.5rem] z-20 rounded-2xl border border-slate-200/90 bg-white/90 p-1.5 shadow-[0_14px_30px_-24px_rgba(15,23,42,0.65)] backdrop-blur-xl dark:border-slate-800/90 dark:bg-slate-900/90">
        <nav class="grid grid-cols-3 gap-1" role="tablist" aria-label="{{ __('Tab profil dinas') }}">
            <button id="profile-tab-visi" type="button" role="tab" aria-controls="visi-misi" @click="setTab('visi-misi')" :aria-selected="tab === 'visi-misi'" :class="tab === 'visi-misi' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-brand-50 hover:text-brand-800 dark:text-slate-300 dark:hover:bg-brand-900/30 dark:hover:text-brand-100'" class="inline-flex items-center justify-center gap-2 rounded-xl px-3 py-3 text-xs font-bold transition-[background-color,color,box-shadow] duration-200 sm:text-sm">
                <x-icons.visi class="size-4" />
                <span>{{ __('Visi & Misi') }}</span>
            </button>
            <button id="profile-tab-struktur" type="button" role="tab" aria-controls="struktur-organisasi" @click="setTab('struktur')" :aria-selected="tab === 'struktur'" :class="tab === 'struktur' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-brand-50 hover:text-brand-800 dark:text-slate-300 dark:hover:bg-brand-900/30 dark:hover:text-brand-100'" class="inline-flex items-center justify-center gap-2 rounded-xl px-3 py-3 text-xs font-bold transition-[background-color,color,box-shadow] duration-200 sm:text-sm">
                <x-icons.terintegrasi class="size-4" />
                <span>{{ __('Struktur') }}</span>
            </button>
            <button id="profile-tab-tugas" type="button" role="tab" aria-controls="tugas-fungsi" @click="setTab('tugas')" :aria-selected="tab === 'tugas'" :class="tab === 'tugas' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-brand-50 hover:text-brand-800 dark:text-slate-300 dark:hover:bg-brand-900/30 dark:hover:text-brand-100'" class="inline-flex items-center justify-center gap-2 rounded-xl px-3 py-3 text-xs font-bold transition-[background-color,color,box-shadow] duration-200 sm:text-sm">
                <x-icons.ui name="document" class="size-4" />
                <span>{{ __('Tugas') }}</span>
            </button>
        </nav>
    </div>

    <section x-show="tab === 'visi-misi'" x-cloak x-transition.opacity.duration.200ms id="visi-misi" role="tabpanel" aria-labelledby="profile-tab-visi" class="scroll-mt-32 space-y-6">
        <article class="relative isolate overflow-hidden rounded-[1.75rem] border border-emerald-400/20 bg-gradient-to-br from-emerald-800 via-teal-800 to-slate-950 px-6 py-10 text-white shadow-[0_28px_64px_-32px_rgba(6,78,59,0.76)] sm:px-10 lg:px-14 lg:py-14">
            <div class="pointer-events-none absolute -right-24 -top-24 size-80 rounded-full bg-emerald-300/20 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-16 size-80 rounded-full bg-teal-300/15 blur-3xl" aria-hidden="true"></div>
            <div class="absolute inset-0 opacity-30" aria-hidden="true" style="background-image: linear-gradient(115deg, transparent 20%, rgba(255,255,255,0.08) 20.5%, transparent 21%), radial-gradient(circle at 75% 15%, rgba(255,255,255,0.16) 0 1px, transparent 1px); background-size: auto, 24px 24px;"></div>

            <div class="relative mx-auto max-w-4xl text-center">
                <span class="inline-flex size-16 items-center justify-center rounded-2xl bg-white/10 text-white ring-1 ring-white/20 backdrop-blur-sm">
                    <x-icons.visi class="size-8" />
                </span>
                <p class="mt-6 text-xs font-bold uppercase tracking-[0.28em] text-emerald-100/90">{{ __('Visi') }}</p>
                <p class="mt-5 text-balance text-3xl font-extrabold leading-[1.15] tracking-[-0.035em] text-white sm:text-4xl lg:text-5xl">Terwujudnya Kota Palu Mantap Berkelanjutan yang Akseleratif, Inovatif dan Kolaboratif</p>
                <div class="mx-auto mt-8 h-px w-20 bg-gradient-to-r from-transparent via-emerald-200 to-transparent"></div>
                <p class="mx-auto mt-5 max-w-2xl text-sm leading-7 text-emerald-50/90 sm:text-base">{{ __('Landasan pelayanan untuk menjaga kualitas lingkungan dan mewujudkan kota yang layak huni.') }}</p>
            </div>
        </article>

        <article class="relative overflow-hidden rounded-[1.75rem] border border-slate-200/90 bg-white p-6 shadow-[0_22px_50px_-34px_rgba(15,23,42,0.5)] dark:border-slate-800 dark:bg-slate-900 sm:p-8 lg:p-10">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-500 via-emerald-400 to-teal-400" aria-hidden="true"></div>
            <div class="flex flex-col gap-4 border-b border-slate-100 pb-7 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                <div class="flex items-center gap-4">
                    <span class="inline-flex size-14 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-100 dark:bg-brand-900/30 dark:text-brand-200 dark:ring-brand-800/70">
                        <x-icons.misi class="size-7" />
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-brand-600 dark:text-brand-300">{{ __('Misi') }}</p>
                        <h2 class="mt-1 text-xl font-extrabold tracking-[-0.025em] text-slate-900 dark:text-white">{{ __('Komitmen kerja DLH Kota Palu') }}</h2>
                    </div>
                </div>
                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700 dark:bg-brand-900/30 dark:text-brand-200">
                    <x-icons.ui name="leaf" class="size-3.5" />
                    {{ __('Lingkungan berkelanjutan') }}
                </span>
            </div>

            <ol class="mt-7 grid gap-3 lg:grid-cols-3">
                <li class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/80 p-5 transition-[transform,border-color,box-shadow] duration-300 hover:-translate-y-1 hover:border-brand-300 hover:bg-white hover:shadow-[0_16px_30px_-24px_rgba(5,150,105,0.35)] dark:border-slate-800 dark:bg-slate-950/30 dark:hover:border-brand-700 dark:hover:bg-slate-950/60">
                    <span class="inline-flex size-8 items-center justify-center rounded-xl bg-brand-600 text-xs font-extrabold text-white shadow-sm">01</span>
                    <p class="mt-4 text-sm leading-7 text-slate-700 dark:text-slate-300">Meningkatkan akselerasi pengelolaan lingkungan dan penataan kota yang layak huni.</p>
                </li>
                <li class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/80 p-5 transition-[transform,border-color,box-shadow] duration-300 hover:-translate-y-1 hover:border-brand-300 hover:bg-white hover:shadow-[0_16px_30px_-24px_rgba(5,150,105,0.35)] dark:border-slate-800 dark:bg-slate-950/30 dark:hover:border-brand-700 dark:hover:bg-slate-950/60">
                    <span class="inline-flex size-8 items-center justify-center rounded-xl bg-brand-600 text-xs font-extrabold text-white shadow-sm">02</span>
                    <p class="mt-4 text-sm leading-7 text-slate-700 dark:text-slate-300">Bersinergi dengan Rencana Strategis Kementerian Lingkungan Hidup dan Kehutanan tahun 2025 - 2029.</p>
                </li>
                <li class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/80 p-5 transition-[transform,border-color,box-shadow] duration-300 hover:-translate-y-1 hover:border-brand-300 hover:bg-white hover:shadow-[0_16px_30px_-24px_rgba(5,150,105,0.35)] dark:border-slate-800 dark:bg-slate-950/30 dark:hover:border-brand-700 dark:hover:bg-slate-950/60">
                    <span class="inline-flex size-8 items-center justify-center rounded-xl bg-brand-600 text-xs font-extrabold text-white shadow-sm">03</span>
                    <p class="mt-4 text-sm leading-7 text-slate-700 dark:text-slate-300">Rencana Strategis Dinas Lingkungan Hidup Pemerintah Provinsi Sulawesi Tengah Tahun 2025-2029.</p>
                </li>
            </ol>
        </article>
    </section>

    <section x-show="tab === 'struktur'" x-cloak x-transition.opacity.duration.200ms id="struktur-organisasi" role="tabpanel" aria-labelledby="profile-tab-struktur" class="scroll-mt-32 rounded-[1.75rem] border border-slate-200/90 bg-white p-4 shadow-[0_22px_50px_-34px_rgba(15,23,42,0.5)] dark:border-slate-800 dark:bg-slate-900 sm:p-8">
        <div class="mb-7 flex flex-col gap-4 border-b border-slate-100 pb-7 sm:flex-row sm:items-end sm:justify-between dark:border-slate-800">
            <div class="flex items-start gap-4">
                <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-700 ring-1 ring-amber-100 dark:bg-amber-900/25 dark:text-amber-200 dark:ring-amber-800/70">
                    <x-icons.terintegrasi class="size-6" />
                </span>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-700 dark:text-amber-300">{{ __('Struktur Organisasi') }}</p>
                    <h2 class="mt-1 text-2xl font-extrabold tracking-[-0.03em] text-slate-900 dark:text-white">{{ __('Bagan Organisasi DLH Kota Palu') }}</h2>
                </div>
            </div>
            @if ($structureImage)
                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">
                    <x-icons.ui name="check" class="size-3.5" />
                    {{ __('Dokumen tersedia') }}
                </span>
            @endif
        </div>

        <div class="relative flex min-h-[380px] items-center justify-center overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-center dark:border-slate-700 dark:bg-slate-950/40 sm:p-8">
            <div class="pointer-events-none absolute -right-16 -top-16 size-48 rounded-full bg-amber-200/40 blur-3xl dark:bg-amber-900/20" aria-hidden="true"></div>
            @if ($structureImage)
                <img src="{{ $structureImage }}" alt="{{ __('Struktur Organisasi DLH Kota Palu') }}" loading="lazy" class="relative max-h-[720px] max-w-full rounded-xl border border-white/70 object-contain shadow-[0_18px_40px_-28px_rgba(15,23,42,0.45)] dark:border-slate-700">
            @else
                <div class="relative max-w-md">
                    <span class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-800">
                        <x-icons.ui name="building" class="size-7" />
                    </span>
                    <h3 class="mt-5 text-lg font-extrabold tracking-[-0.02em] text-slate-900 dark:text-white">{{ __('Gambar struktur belum tersedia') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Area ini sengaja dikosongkan dulu agar nanti bisa langsung diisi dengan gambar struktur organisasi dari folder asset.') }}</p>
                </div>
            @endif
        </div>
    </section>

    <section x-show="tab === 'tugas'" x-cloak x-transition.opacity.duration.200ms id="tugas-fungsi" role="tabpanel" aria-labelledby="profile-tab-tugas" class="scroll-mt-32 overflow-hidden rounded-[1.75rem] border border-slate-200/90 bg-white shadow-[0_22px_50px_-34px_rgba(15,23,42,0.5)] dark:border-slate-800 dark:bg-slate-900">
        <div class="relative overflow-hidden bg-gradient-to-r from-brand-700 via-emerald-700 to-teal-800 px-6 py-8 text-white sm:px-8 lg:px-10">
            <div class="pointer-events-none absolute -right-12 -top-14 size-48 rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
            <div class="relative flex items-start gap-4">
                <span class="inline-flex size-14 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white ring-1 ring-white/20 backdrop-blur-sm">
                    <x-icons.ui name="document" class="size-6" />
                </span>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-brand-100">{{ __('Tugas & Fungsi') }}</p>
                    <h2 class="mt-2 text-2xl font-extrabold tracking-[-0.03em] sm:text-3xl">{{ __('Peran DLH Kota Palu') }}</h2>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8 lg:p-10">
            <div class="max-w-4xl">
                <div class="rounded-2xl border border-brand-100 bg-brand-50/60 p-5 dark:border-brand-900/60 dark:bg-brand-950/25">
                    <h3 class="flex items-center gap-3 text-lg font-extrabold tracking-[-0.02em] text-slate-900 dark:text-white">
                        <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white text-brand-700 shadow-sm ring-1 ring-brand-100 dark:bg-slate-900 dark:text-brand-200 dark:ring-brand-800">
                            <x-icons.ui name="building" class="size-4" />
                        </span>
                        Tugas Dinas Lingkungan Hidup Kota Palu
                    </h3>
                    <p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">Dinas Lingkungan Hidup mempunyai tugas membantu Wali Kota melaksanakan urusan Pemerintahan yang menjadi kewenangan Daerah di bidang lingkungan hidup dan Tugas Pembantuan yang diberikan kepada Daerah.</p>
                </div>

                <div class="mt-8">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex size-9 items-center justify-center rounded-xl bg-sky-50 text-sky-700 ring-1 ring-sky-100 dark:bg-sky-900/25 dark:text-sky-200 dark:ring-sky-800/70">
                            <x-icons.ui name="check" class="size-4" />
                        </span>
                        <h3 class="text-lg font-extrabold tracking-[-0.02em] text-slate-900 dark:text-white">Fungsi Dinas Lingkungan Hidup Kota Palu</h3>
                    </div>
                    <ol class="mt-5 grid gap-3">
                        <li class="flex gap-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-950/35"><span class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-xs font-extrabold text-white">01</span><span class="text-sm leading-7 text-slate-700 dark:text-slate-300">Pengoordinasian perumusan kebijakan teknis bidang lingkungan hidup;</span></li>
                        <li class="flex gap-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-950/35"><span class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-xs font-extrabold text-white">02</span><span class="text-sm leading-7 text-slate-700 dark:text-slate-300">Penyelenggaraan pembinaan, pengumpulan dan pengolahan data, penyusunan rencana dan program bidang lingkungan hidup;</span></li>
                        <li class="flex gap-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-950/35"><span class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-xs font-extrabold text-white">03</span><span class="text-sm leading-7 text-slate-700 dark:text-slate-300">Pengoordinasian, pengendalian dan pengawasan, serta evaluasi pelaksanaan tugas bidang lingkungan hidup;</span></li>
                        <li class="flex gap-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-950/35"><span class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-xs font-extrabold text-white">04</span><span class="text-sm leading-7 text-slate-700 dark:text-slate-300">Pengelolaan perizinan dan pelaksanaan pelayanan bidang lingkungan hidup;</span></li>
                        <li class="flex gap-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-950/35"><span class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-xs font-extrabold text-white">05</span><span class="text-sm leading-7 text-slate-700 dark:text-slate-300">Penyelenggaraan ketatausahaan dan tata laksana; dan</span></li>
                        <li class="flex gap-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-950/35"><span class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-xs font-extrabold text-white">06</span><span class="text-sm leading-7 text-slate-700 dark:text-slate-300">Pelaksanaan fungsi lain yang diberikan oleh atasan sesuai dengan tugasnya.</span></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
