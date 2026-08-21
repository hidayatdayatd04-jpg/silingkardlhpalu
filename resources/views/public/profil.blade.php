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
        tab: 'visi-misi',
        activeSub: 'dlh',
        parseHash(hash) {
            if (!hash) return;
            if (hash === '#struktur-organisasi') {
                this.tab = 'struktur';
            } else if (hash === '#visi-misi') {
                this.tab = 'visi-misi';
            } else if (['#tugas-fungsi', '#tugas-dlh', '#sekretaris', '#umum-kepegawaian', '#tata-lingkungan', '#pengendalian', '#sampah-lb3', '#rth', '#uptd-lab', '#uptd-tpa'].includes(hash)) {
                this.tab = 'tugas';
                if (hash === '#tugas-dlh' || hash === '#tugas-fungsi') this.activeSub = 'dlh';
                else if (hash === '#sekretaris') this.activeSub = 'sekretaris';
                else if (hash === '#umum-kepegawaian') this.activeSub = 'umum-kepegawaian';
                else if (hash === '#tata-lingkungan') this.activeSub = 'tata-lingkungan';
                else if (hash === '#pengendalian') this.activeSub = 'pengendalian';
                else if (hash === '#sampah-lb3') this.activeSub = 'sampah-lb3';
                else if (hash === '#rth') this.activeSub = 'rth';
                else if (hash === '#uptd-lab') this.activeSub = 'uptd-lab';
                else if (hash === '#uptd-tpa') this.activeSub = 'uptd-tpa';
            }
        },
        setTab(name) {
            this.tab = name;
            const hash = name === 'visi-misi' ? '#visi-misi' : (name === 'struktur' ? '#struktur-organisasi' : (this.activeSub === 'dlh' ? '#tugas-dlh' : '#' + this.activeSub));
            history.replaceState(null, '', hash);
            this.$nextTick(() => {
                const targetId = name === 'tugas' ? 'tugas-fungsi' : hash.replace('#', '');
                const el = document.getElementById(targetId);
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        },
        setSub(subKey) {
            this.tab = 'tugas';
            this.activeSub = subKey;
            const hash = '#' + (subKey === 'dlh' ? 'tugas-dlh' : subKey);
            history.replaceState(null, '', hash);
            if (window.innerWidth < 1024) {
                this.$nextTick(() => {
                    const el = document.getElementById('tugas-content-card');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            }
        },
        scrollToSection(hash) {
            if (!hash) return;
            this.parseHash(hash);
            const targetId = ['#tugas-fungsi', '#tugas-dlh', '#sekretaris', '#umum-kepegawaian', '#tata-lingkungan', '#pengendalian', '#sampah-lb3', '#rth', '#uptd-lab', '#uptd-tpa'].includes(hash)
                ? 'tugas-fungsi'
                : hash.replace('#', '');
            const el = document.getElementById(targetId);
            if (el) setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);
        }
    }"
    x-init="
        scrollToSection(window.location.hash);
        window.addEventListener('hashchange', () => {
            scrollToSection(window.location.hash);
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
                <span>{{ __('Tugas & Fungsi') }}</span>
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
                <p class="mt-5 text-balance text-3xl font-extrabold leading-[1.15] tracking-[-0.035em] text-white sm:text-4xl lg:text-5xl">“Terwujudnya Kota Palu Mantap Berkelanjutan yang Akseleratif, Inovatif dan Kolaboratif”</p>
                <div class="mx-auto mt-8 h-px w-20 bg-gradient-to-r from-transparent via-emerald-200 to-transparent"></div>
                <p class="mx-auto mt-5 max-w-2xl text-sm leading-7 text-emerald-50/90 sm:text-base">{{ __('Landasan komitmen pelayanan untuk menjaga kualitas lingkungan dan mewujudkan kota yang layak huni.') }}</p>
            </div>
        </article>

        <article class="rounded-[1.75rem] border border-slate-200/90 bg-white p-6 shadow-[0_22px_50px_-34px_rgba(15,23,42,0.5)] dark:border-slate-800 dark:bg-slate-900 sm:p-8 lg:p-10">
            <div class="flex items-center gap-3">
                <span class="inline-flex size-11 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-100 dark:bg-brand-900/30 dark:text-brand-200 dark:ring-brand-800/70">
                    <x-icons.misi class="size-5" />
                </span>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-brand-600 dark:text-brand-400">{{ __('Misi Pembangunan') }}</p>
                    <h3 class="text-xl font-extrabold tracking-[-0.03em] text-slate-900 dark:text-white sm:text-2xl">{{ __('Misi Dinas Lingkungan Hidup Kota Palu') }}</h3>
                </div>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-3">
                <div class="flex gap-4 rounded-2xl border border-slate-200/90 bg-slate-50/70 p-5 transition-shadow duration-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-950/40">
                    <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-sm font-extrabold text-white shadow-sm">01</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium leading-relaxed text-slate-800 dark:text-slate-200 sm:text-base">{{ __('Meningkatkan akselerasi pengelolaan lingkungan dan penataan kota yang layak huni.') }}</p>
                    </div>
                </div>

                <div class="flex gap-4 rounded-2xl border border-slate-200/90 bg-slate-50/70 p-5 transition-shadow duration-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-950/40">
                    <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-sm font-extrabold text-white shadow-sm">02</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium leading-relaxed text-slate-800 dark:text-slate-200 sm:text-base">{{ __('Bersinergi dengan Rencana Strategis Kementerian Lingkungan Hidup dan Kehutanan tahun 2025 - 2029') }}</p>
                    </div>
                </div>

                <div class="flex gap-4 rounded-2xl border border-slate-200/90 bg-slate-50/70 p-5 transition-shadow duration-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-950/40">
                    <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-sm font-extrabold text-white shadow-sm">03</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium leading-relaxed text-slate-800 dark:text-slate-200 sm:text-base">{{ __('Rencana Strategis Dinas Lingkungan Hidup Pemerintah Provinsi Sulawesi Tengah Tahun 2025-2029.') }}</p>
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section x-show="tab === 'struktur'" x-cloak x-transition.opacity.duration.200ms id="struktur-organisasi" role="tabpanel" aria-labelledby="profile-tab-struktur" class="scroll-mt-32 space-y-6">
        <article class="relative isolate overflow-hidden rounded-[1.75rem] border border-amber-400/20 bg-gradient-to-br from-amber-800 via-orange-800 to-slate-950 px-6 py-10 text-white shadow-[0_28px_64px_-32px_rgba(180,83,9,0.76)] sm:px-10 lg:px-14 lg:py-14">
            <div class="pointer-events-none absolute -right-24 -top-24 size-80 rounded-full bg-amber-300/20 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-16 size-80 rounded-full bg-orange-300/15 blur-3xl" aria-hidden="true"></div>
            <div class="absolute inset-0 opacity-30" aria-hidden="true" style="background-image: linear-gradient(115deg, transparent 20%, rgba(255,255,255,0.08) 20.5%, transparent 21%), radial-gradient(circle at 75% 15%, rgba(255,255,255,0.16) 0 1px, transparent 1px); background-size: auto, 24px 24px;"></div>

            <div class="relative mx-auto max-w-4xl text-center">
                <span class="inline-flex size-16 items-center justify-center rounded-2xl bg-white/10 text-white ring-1 ring-white/20 backdrop-blur-sm">
                    <x-icons.terintegrasi class="size-8" />
                </span>
                <p class="mt-6 text-xs font-bold uppercase tracking-[0.28em] text-amber-100/90">{{ __('Bagan Perangkat Daerah') }}</p>
                <h2 class="mt-5 text-balance text-3xl font-extrabold leading-[1.15] tracking-[-0.035em] text-white sm:text-4xl lg:text-5xl">{{ __('Struktur Organisasi Dinas') }}</h2>
                <div class="mx-auto mt-8 h-px w-20 bg-gradient-to-r from-transparent via-amber-200 to-transparent"></div>
                <p class="mx-auto mt-5 max-w-2xl text-sm leading-7 text-amber-50/90 sm:text-base">{{ __('Bagan hierarki kelembagaan Dinas Lingkungan Hidup Kota Palu dalam menjalankan tata kelola urusan lingkungan hidup daerah.') }}</p>
            </div>
        </article>

        <div class="overflow-hidden rounded-[1.75rem] border border-slate-200/90 bg-white p-6 shadow-[0_22px_50px_-34px_rgba(15,23,42,0.5)] dark:border-slate-800 dark:bg-slate-900 sm:p-8 lg:p-10">
            @if($structureImage)
                <div class="space-y-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ __('Bagan Resmi Struktur Organisasi') }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Dinas Lingkungan Hidup Kota Palu') }}</p>
                        </div>
                        <a href="{{ $structureImage }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl bg-brand-50 px-3 py-2 text-xs font-bold text-brand-700 hover:bg-brand-100 dark:bg-brand-900/30 dark:text-brand-300">
                            <x-icons.ui name="arrow-right" class="size-3.5" />
                            <span>{{ __('Buka Gambar Penuh') }}</span>
                        </a>
                    </div>
                    <div class="overflow-auto rounded-2xl border border-slate-100 bg-slate-50/50 p-2 dark:border-slate-800 dark:bg-slate-950/40">
                        <img src="{{ $structureImage }}" alt="{{ __('Struktur Organisasi Dinas Lingkungan Hidup Kota Palu') }}" class="w-full h-auto rounded-xl object-contain max-h-[850px] mx-auto shadow-sm" loading="lazy" />
                    </div>
                </div>
            @else
                <div class="relative max-w-md mx-auto text-center py-8">
                    <span class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-800">
                        <x-icons.ui name="building" class="size-7" />
                    </span>
                    <h3 class="mt-5 text-lg font-extrabold tracking-[-0.02em] text-slate-900 dark:text-white">{{ __('Gambar struktur belum tersedia') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Area ini sengaja dikosongkan dulu agar nanti bisa langsung diisi dengan gambar struktur organisasi dari folder asset.') }}</p>
                </div>
            @endif
        </div>
    </section>

    <!-- TAB TUGAS & FUNGSI DENGAN TAMPILAN MODERN & ELEGAN -->
    <section x-show="tab === 'tugas'" x-cloak x-transition.opacity.duration.200ms id="tugas-fungsi" role="tabpanel" aria-labelledby="profile-tab-tugas" class="scroll-mt-32">
        
        <div class="grid gap-6 lg:grid-cols-12 items-start">
            
            <!-- PANEL NAVIGASI / PINTASAN UNIT KERJA (Sticky di Desktop) -->
            <aside class="col-span-12 lg:col-span-4 lg:sticky lg:top-28 space-y-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white/95 p-4 shadow-[0_14px_35px_-20px_rgba(15,23,42,0.12)] backdrop-blur-xl dark:border-slate-800/90 dark:bg-slate-900/95 sm:p-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 dark:border-slate-800">
                        <div>
                            <span class="text-[11px] font-extrabold uppercase tracking-widest text-brand-600 dark:text-brand-400">{{ __('Navigasi Profil') }}</span>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">{{ __('Struktur Unit Kerja') }}</h3>
                        </div>
                        <span class="inline-flex size-8 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-300">
                            <x-icons.ui name="folder" class="size-4" />
                        </span>
                    </div>

                    <div class="mt-4 space-y-4">
                        <!-- Grup 1: Dinas Lingkungan Hidup -->
                        <div>
                            <span class="block px-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Tingkat Dinas') }}</span>
                            <div class="mt-1.5">
                                <button
                                    type="button"
                                    @click="setSub('dlh')"
                                    :class="activeSub === 'dlh' ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30 ring-1 ring-brand-500 font-bold' : 'bg-slate-50 text-slate-800 hover:bg-brand-50/80 hover:text-brand-900 border border-slate-200/80 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700 font-medium'"
                                    class="group flex w-full items-center justify-between rounded-xl px-3.5 py-2.5 text-left text-xs transition-all duration-200 sm:text-sm"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span :class="activeSub === 'dlh' ? 'bg-white/20 text-white' : 'bg-brand-50 text-brand-700 dark:bg-slate-900 dark:text-brand-300'" class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg transition-colors">
                                            <x-icons.ui name="building" class="size-3.5" />
                                        </span>
                                        <div class="truncate">
                                            <span :class="activeSub === 'dlh' ? 'text-white font-bold' : 'text-slate-900 dark:text-white font-semibold'" class="block truncate">Dinas Lingkungan Hidup</span>
                                            <span :class="activeSub === 'dlh' ? 'text-white/80' : 'text-slate-500 dark:text-slate-400'" class="block text-[11px]">Urusan Pemerintahan Daerah</span>
                                        </div>
                                    </div>
                                    <x-icons.ui name="chevron-right" :class="activeSub === 'dlh' ? 'translate-x-0.5 text-white opacity-100' : 'text-slate-400 opacity-0 group-hover:opacity-100'" class="size-4 shrink-0 transition-all" />
                                </button>
                            </div>
                        </div>

                        <!-- Grup 2: Sekretariat -->
                        <div>
                            <span class="block px-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Sekretariat') }}</span>
                            <div class="mt-1.5 space-y-1.5">
                                <button
                                    type="button"
                                    @click="setSub('sekretaris')"
                                    :class="activeSub === 'sekretaris' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30 ring-1 ring-amber-500 font-bold' : 'bg-slate-50 text-slate-800 hover:bg-amber-50/80 hover:text-amber-900 border border-slate-200/80 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700 font-medium'"
                                    class="group flex w-full items-center justify-between rounded-xl px-3.5 py-2.5 text-left text-xs transition-all duration-200 sm:text-sm"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span :class="activeSub === 'sekretaris' ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-700 dark:bg-slate-900 dark:text-amber-300'" class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg transition-colors">
                                            <x-icons.ui name="user" class="size-3.5" />
                                        </span>
                                        <div class="truncate">
                                            <span :class="activeSub === 'sekretaris' ? 'text-white font-bold' : 'text-slate-900 dark:text-white font-semibold'" class="block truncate">1. Sekretaris</span>
                                            <span :class="activeSub === 'sekretaris' ? 'text-white/80' : 'text-slate-500 dark:text-slate-400'" class="block text-[11px]">Perencanaan & Pelayanan</span>
                                        </div>
                                    </div>
                                    <x-icons.ui name="chevron-right" :class="activeSub === 'sekretaris' ? 'translate-x-0.5 text-white opacity-100' : 'text-slate-400 opacity-0 group-hover:opacity-100'" class="size-4 shrink-0 transition-all" />
                                </button>

                                <button
                                    type="button"
                                    @click="setSub('umum-kepegawaian')"
                                    :class="activeSub === 'umum-kepegawaian' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30 ring-1 ring-amber-500 font-bold' : 'bg-slate-50 text-slate-800 hover:bg-amber-50/80 hover:text-amber-900 border border-slate-200/80 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700 font-medium'"
                                    class="group flex w-full items-center justify-between rounded-xl px-3.5 py-2.5 text-left text-xs transition-all duration-200 sm:text-sm"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span :class="activeSub === 'umum-kepegawaian' ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-700 dark:bg-slate-900 dark:text-amber-300'" class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg transition-colors">
                                            <x-icons.ui name="users" class="size-3.5" />
                                        </span>
                                        <div class="truncate">
                                            <span :class="activeSub === 'umum-kepegawaian' ? 'text-white font-bold' : 'text-slate-900 dark:text-white font-semibold'" class="block truncate">2. Sub Bagian Umum & Kepeg.</span>
                                            <span :class="activeSub === 'umum-kepegawaian' ? 'text-white/80' : 'text-slate-500 dark:text-slate-400'" class="block text-[11px]">Administrasi & Aparatur</span>
                                        </div>
                                    </div>
                                    <x-icons.ui name="chevron-right" :class="activeSub === 'umum-kepegawaian' ? 'translate-x-0.5 text-white opacity-100' : 'text-slate-400 opacity-0 group-hover:opacity-100'" class="size-4 shrink-0 transition-all" />
                                </button>
                            </div>
                        </div>

                        <!-- Grup 3: Bidang Pelaksana Teknis -->
                        <div>
                            <span class="block px-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Bidang Pelaksana Teknis') }}</span>
                            <div class="mt-1.5 space-y-1.5">
                                <button
                                    type="button"
                                    @click="setSub('tata-lingkungan')"
                                    :class="activeSub === 'tata-lingkungan' ? 'bg-teal-700 text-white shadow-md shadow-teal-900/30 ring-1 ring-teal-600 font-bold' : 'bg-slate-50 text-slate-800 hover:bg-teal-50/80 hover:text-teal-900 border border-slate-200/80 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700 font-medium'"
                                    class="group flex w-full items-center justify-between rounded-xl px-3.5 py-2.5 text-left text-xs transition-all duration-200 sm:text-sm"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span :class="activeSub === 'tata-lingkungan' ? 'bg-white/20 text-white' : 'bg-teal-50 text-teal-700 dark:bg-slate-900 dark:text-teal-300'" class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg transition-colors">
                                            <x-icons.tata-penataan class="size-3.5" />
                                        </span>
                                        <div class="truncate">
                                            <span :class="activeSub === 'tata-lingkungan' ? 'text-white font-bold' : 'text-slate-900 dark:text-white font-semibold'" class="block truncate">1. Bidang Tata & Penataan</span>
                                            <span :class="activeSub === 'tata-lingkungan' ? 'text-white/80' : 'text-slate-500 dark:text-slate-400'" class="block text-[11px]">Kebijakan & AMDAL</span>
                                        </div>
                                    </div>
                                    <x-icons.ui name="chevron-right" :class="activeSub === 'tata-lingkungan' ? 'translate-x-0.5 text-white opacity-100' : 'text-slate-400 opacity-0 group-hover:opacity-100'" class="size-4 shrink-0 transition-all" />
                                </button>

                                <button
                                    type="button"
                                    @click="setSub('pengendalian')"
                                    :class="activeSub === 'pengendalian' ? 'bg-teal-700 text-white shadow-md shadow-teal-900/30 ring-1 ring-teal-600 font-bold' : 'bg-slate-50 text-slate-800 hover:bg-teal-50/80 hover:text-teal-900 border border-slate-200/80 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700 font-medium'"
                                    class="group flex w-full items-center justify-between rounded-xl px-3.5 py-2.5 text-left text-xs transition-all duration-200 sm:text-sm"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span :class="activeSub === 'pengendalian' ? 'bg-white/20 text-white' : 'bg-teal-50 text-teal-700 dark:bg-slate-900 dark:text-teal-300'" class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg transition-colors">
                                            <x-icons.pengendalian class="size-3.5" />
                                        </span>
                                        <div class="truncate">
                                            <span :class="activeSub === 'pengendalian' ? 'text-white font-bold' : 'text-slate-900 dark:text-white font-semibold'" class="block truncate">2. Pengendalian Pencemaran</span>
                                            <span :class="activeSub === 'pengendalian' ? 'text-white/80' : 'text-slate-500 dark:text-slate-400'" class="block text-[11px]">Pengawasan & Mutu</span>
                                        </div>
                                    </div>
                                    <x-icons.ui name="chevron-right" :class="activeSub === 'pengendalian' ? 'translate-x-0.5 text-white opacity-100' : 'text-slate-400 opacity-0 group-hover:opacity-100'" class="size-4 shrink-0 transition-all" />
                                </button>

                                <button
                                    type="button"
                                    @click="setSub('sampah-lb3')"
                                    :class="activeSub === 'sampah-lb3' ? 'bg-teal-700 text-white shadow-md shadow-teal-900/30 ring-1 ring-teal-600 font-bold' : 'bg-slate-50 text-slate-800 hover:bg-teal-50/80 hover:text-teal-900 border border-slate-200/80 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700 font-medium'"
                                    class="group flex w-full items-center justify-between rounded-xl px-3.5 py-2.5 text-left text-xs transition-all duration-200 sm:text-sm"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span :class="activeSub === 'sampah-lb3' ? 'bg-white/20 text-white' : 'bg-teal-50 text-teal-700 dark:bg-slate-900 dark:text-teal-300'" class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg transition-colors">
                                            <x-icons.sampah class="size-3.5" />
                                        </span>
                                        <div class="truncate">
                                            <span :class="activeSub === 'sampah-lb3' ? 'text-white font-bold' : 'text-slate-900 dark:text-white font-semibold'" class="block truncate">3. Sampah & Limbah B3</span>
                                            <span :class="activeSub === 'sampah-lb3' ? 'text-white/80' : 'text-slate-500 dark:text-slate-400'" class="block text-[11px]">Persampahan & B3</span>
                                        </div>
                                    </div>
                                    <x-icons.ui name="chevron-right" :class="activeSub === 'sampah-lb3' ? 'translate-x-0.5 text-white opacity-100' : 'text-slate-400 opacity-0 group-hover:opacity-100'" class="size-4 shrink-0 transition-all" />
                                </button>

                                <button
                                    type="button"
                                    @click="setSub('rth')"
                                    :class="activeSub === 'rth' ? 'bg-teal-700 text-white shadow-md shadow-teal-900/30 ring-1 ring-teal-600 font-bold' : 'bg-slate-50 text-slate-800 hover:bg-teal-50/80 hover:text-teal-900 border border-slate-200/80 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700 font-medium'"
                                    class="group flex w-full items-center justify-between rounded-xl px-3.5 py-2.5 text-left text-xs transition-all duration-200 sm:text-sm"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span :class="activeSub === 'rth' ? 'bg-white/20 text-white' : 'bg-teal-50 text-teal-700 dark:bg-slate-900 dark:text-teal-300'" class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg transition-colors">
                                            <x-icons.rth class="size-3.5" />
                                        </span>
                                        <div class="truncate">
                                            <span :class="activeSub === 'rth' ? 'text-white font-bold' : 'text-slate-900 dark:text-white font-semibold'" class="block truncate">4. Ruang Terbuka Hijau</span>
                                            <span :class="activeSub === 'rth' ? 'text-white/80' : 'text-slate-500 dark:text-slate-400'" class="block text-[11px]">Taman & Pemakaman</span>
                                        </div>
                                    </div>
                                    <x-icons.ui name="chevron-right" :class="activeSub === 'rth' ? 'translate-x-0.5 text-white opacity-100' : 'text-slate-400 opacity-0 group-hover:opacity-100'" class="size-4 shrink-0 transition-all" />
                                </button>
                            </div>
                        </div>

                        <!-- Grup 4: UPTD -->
                        <div>
                            <span class="block px-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('UPTD') }}</span>
                            <div class="mt-1.5 space-y-1.5">
                                <button
                                    type="button"
                                    @click="setSub('uptd-lab')"
                                    :class="activeSub === 'uptd-lab' ? 'bg-emerald-700 text-white shadow-md shadow-emerald-900/30 ring-1 ring-emerald-600 font-bold' : 'bg-slate-50 text-slate-800 hover:bg-emerald-50/80 hover:text-emerald-900 border border-slate-200/80 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700 font-medium'"
                                    class="group flex w-full items-center justify-between rounded-xl px-3.5 py-2.5 text-left text-xs transition-all duration-200 sm:text-sm"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span :class="activeSub === 'uptd-lab' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700 dark:bg-slate-900 dark:text-emerald-300'" class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg transition-colors">
                                            <x-icons.ui name="beaker" class="size-3.5" />
                                        </span>
                                        <div class="truncate">
                                            <span :class="activeSub === 'uptd-lab' ? 'text-white font-bold' : 'text-slate-900 dark:text-white font-semibold'" class="block truncate">1. UPTD Lab Lingkungan</span>
                                            <span :class="activeSub === 'uptd-lab' ? 'text-white/80' : 'text-slate-500 dark:text-slate-400'" class="block text-[11px]">Pemantauan Kualitas Lingkungan</span>
                                        </div>
                                    </div>
                                    <x-icons.ui name="chevron-right" :class="activeSub === 'uptd-lab' ? 'translate-x-0.5 text-white opacity-100' : 'text-slate-400 opacity-0 group-hover:opacity-100'" class="size-4 shrink-0 transition-all" />
                                </button>

                                <button
                                    type="button"
                                    @click="setSub('uptd-tpa')"
                                    :class="activeSub === 'uptd-tpa' ? 'bg-emerald-700 text-white shadow-md shadow-emerald-900/30 ring-1 ring-emerald-600 font-bold' : 'bg-slate-50 text-slate-800 hover:bg-emerald-50/80 hover:text-emerald-900 border border-slate-200/80 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700 font-medium'"
                                    class="group flex w-full items-center justify-between rounded-xl px-3.5 py-2.5 text-left text-xs transition-all duration-200 sm:text-sm"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span :class="activeSub === 'uptd-tpa' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700 dark:bg-slate-900 dark:text-emerald-300'" class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg transition-colors">
                                            <x-icons.ui name="recycle" class="size-3.5" />
                                        </span>
                                        <div class="truncate">
                                            <span :class="activeSub === 'uptd-tpa' ? 'text-white font-bold' : 'text-slate-900 dark:text-white font-semibold'" class="block truncate">2. UPTD TPA Kawatuna</span>
                                            <span :class="activeSub === 'uptd-tpa' ? 'text-white/80' : 'text-slate-500 dark:text-slate-400'" class="block text-[11px]">Pengelolaan TPA</span>
                                        </div>
                                    </div>
                                    <x-icons.ui name="chevron-right" :class="activeSub === 'uptd-tpa' ? 'translate-x-0.5 text-white opacity-100' : 'text-slate-400 opacity-0 group-hover:opacity-100'" class="size-4 shrink-0 transition-all" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- PANEL UTAMA: DETAIL KONTEN TUGAS & FUNGSI -->
            <main class="col-span-12 lg:col-span-8 scroll-mt-36" id="tugas-content-card">
                
                <!-- 1. DETAIL DINAS LINGKUNGAN HIDUP -->
                <div x-show="activeSub === 'dlh'" x-cloak x-transition.opacity.duration.250ms class="space-y-6">
                    <div class="relative isolate overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-800 via-teal-800 to-slate-950 p-6 text-white shadow-xl shadow-emerald-950/20 sm:p-8">
                        <div class="pointer-events-none absolute -right-12 -top-12 size-48 rounded-full bg-emerald-400/20 blur-3xl"></div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-11 items-center justify-center rounded-xl bg-white/10 text-emerald-300 ring-1 ring-white/20 backdrop-blur-md">
                                <x-icons.ui name="building" class="size-6" />
                            </span>
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-200/90">{{ __('Urusan Daerah') }}</span>
                                <h2 class="text-xl font-extrabold text-white sm:text-2xl">Dinas Lingkungan Hidup Kota Palu</h2>
                            </div>
                        </div>
                        <div class="mt-5 border-t border-white/10 pt-5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-300">{{ __('Dasar Hukum & Tugas Pokok:') }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-emerald-50/90 text-justify sm:text-left">
                                Sesuai dengan Peraturan Pemerintah Nomor 18 Tahun 2016 tentang Perangkat Daerah dinyatakan bahwa lingkungan hidup merupakan perumpunan urusan yang diwadahi dalam Dinas sehingga melalui Peraturan Daerah Kota Palu Nomor 10 tahun 2016 tentang Pembentukan dan susunan perangkat Daerah Kota Palu dan Peraturan Wali Kota Nomor 4 Tahun 2024 Tentang Kedudukan, Susunan Organisasi, Tugas, Fungsi dan Tata Kerja Perangkat Daerah maka Dinas Lingkungan Hidup mempunyai tugas membantu Wali Kota melaksanakan urusan Pemerintahan yang menjadi kewenangan Daerah di bidang lingkungan hidup dan Tugas Pembantuan yang diberikan kepada Daerah.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-7">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                    <x-icons.ui name="check-circle" class="size-4" />
                                </span>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Penyelenggaraan Fungsi Dinas</h3>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">6 Poin Fungsi</span>
                        </div>
                        <ol class="mt-5 grid gap-3 sm:grid-cols-2">
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">01</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300">Pengoordinasian perumusan kebijakan teknis bidang lingkungan hidup;</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">02</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300">Penyelenggaraan pembinaan, pengumpulan dan pengolahan data, penyusunan rencana dan program bidang lingkungan hidup;</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">03</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300">Pengoordinasian, pengendalian dan pengawasan, serta evaluasi pelaksanaan tugas bidang lingkungan hidup;</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">04</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300">Pengelolaan perizinan dan pelaksanaan pelayanan bidang lingkungan hidup;</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">05</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300">Penyelenggaraan ketatausahaan dan tata laksana; dan</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">06</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300">Pelaksanaan fungsi lain yang diberikan oleh atasan sesuai dengan tugasnya.</span>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- 2. DETAIL SEKRETARIS -->
                <div x-show="activeSub === 'sekretaris'" x-cloak x-transition.opacity.duration.250ms class="space-y-6">
                    <div class="relative isolate overflow-hidden rounded-2xl bg-gradient-to-br from-amber-700 via-orange-800 to-slate-950 p-6 text-white shadow-xl shadow-amber-950/20 sm:p-8">
                        <div class="pointer-events-none absolute -right-12 -top-12 size-48 rounded-full bg-amber-400/20 blur-3xl"></div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-11 items-center justify-center rounded-xl bg-white/10 text-amber-300 ring-1 ring-white/20 backdrop-blur-md">
                                <x-icons.ui name="user" class="size-6" />
                            </span>
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-200/90">{{ __('Sekretariat') }}</span>
                                <h2 class="text-xl font-extrabold text-white sm:text-2xl">1. Sekretaris</h2>
                            </div>
                        </div>
                        <div class="mt-5 border-t border-white/10 pt-5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-300">{{ __('Tugas Pokok:') }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-amber-50/90">
                                Mengoordinasikan perencanaan, pembinaan dan pengendalian terhadap program serta memberikan pelayanan teknis dan administrasi kepada seluruh unit organisasi di lingkungan Dinas Lingkungan Hidup.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-7">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                    <x-icons.ui name="check-circle" class="size-4" />
                                </span>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Rincian Fungsi Sekretaris</h3>
                            </div>
                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-950 dark:text-amber-300">16 Poin Fungsi</span>
                        </div>
                        <ul class="mt-5 grid gap-2.5 sm:grid-cols-2">
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengoordinasian Perencanaan program kerja pada Sekretariat;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengoordinasian dan perumusan kebijakan teknis kesekretariatan;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengoordinasian pelaksanaan program kerja pada Sekretariat;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Membantu Kepala Dinas dalam pengoordinasian program kegiatan bidang;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengoordinasian penyelenggaraan administrasi umum dan kepegawaian Dinas;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengoordinasiaan penyusunan Analisis Jabatan dan Beban Kerja;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengoordinasiaan penyelenggaraan pengelolaan keuangan dan aset Dinas;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengoordinasian penyelenggaraan Perencanaan, evaluasi dan pelaporan;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengoordinasian fasilitasi, koordinasi dan sinkronisasi pengelolaan data dan informasi;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengoordinasian pengelolaan kearsipan dan perpustakaan;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengoordinasian fasilitasi Kelompok Jabatan Fungsional;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengoordinasian fasilitasi pelaksanaan kegiatan reformasi birokrasi, inovasi, sistem pengendalian internal pemerintah, zona integritas, ketatalaksanaan dan budaya ASN;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengoordinasian pelaksanaan reformasi birokrasi, inovasi, sistem pengendalian internal pemerintah, zona integritas, ketatalaksanaan dan budaya ASN pada Sekretariat;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengoordinasian penyiapan bahan dan penyusunan pelaporan;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengoordinasian pelaksanaan pemantauan, pengendalian, evaluasi dan penyusunan laporan program kerja Sekretariat; dan</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pelaksanaan fungsi lain yang diberikan oleh atasan sesuai dengan bidang tugas.</span></li>
                        </ul>
                    </div>
                </div>

                <!-- 3. DETAIL SUB BAGIAN UMUM DAN KEPEGAWAIAN -->
                <div x-show="activeSub === 'umum-kepegawaian'" x-cloak x-transition.opacity.duration.250ms class="space-y-6">
                    <div class="relative isolate overflow-hidden rounded-2xl bg-gradient-to-br from-amber-700 via-orange-800 to-slate-950 p-6 text-white shadow-xl shadow-amber-950/20 sm:p-8">
                        <div class="pointer-events-none absolute -right-12 -top-12 size-48 rounded-full bg-amber-400/20 blur-3xl"></div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-11 items-center justify-center rounded-xl bg-white/10 text-amber-300 ring-1 ring-white/20 backdrop-blur-md">
                                <x-icons.ui name="users" class="size-6" />
                            </span>
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-200/90">{{ __('Sekretariat') }}</span>
                                <h2 class="text-xl font-extrabold text-white sm:text-2xl">2. Sub Bagian Umum dan Kepegawaian</h2>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-7">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                    <x-icons.ui name="check-circle" class="size-4" />
                                </span>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Tugas dan Fungsi Sub Bagian</h3>
                            </div>
                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-950 dark:text-amber-300">16 Poin Rincian</span>
                        </div>
                        <ul class="mt-5 grid gap-2.5 sm:grid-cols-2">
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Penyusunaan Perencanaan kegiatan pada Sub Bagian Kepegawaian dan Umum;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Penyiapan bahan perumusan kebijakan teknis terkait administrasi Kepegawaian dan Umum;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengelolaan administrasi perkantoran dan persuratan Dinas;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Penyelenggaraan kerumahtanggaan dan pengelolaan aset Dinas;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pelaksanaan fasilitasi Kelompok Jabatan Fungsional Dinas;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengelolaan administrasi kepegawaian Dinas;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Penyiapan bahan penyusunan Analisis Jabatan dan Beban Kerja;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Penyiapan bahan pengembangan kapasitas ASN di lingkungan Dinas;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pengelolaan kearsipan dan Perpustakaan Dinas;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pelaksanaan pengelolaan kearsipan pada Sub Bagian Kepegawaian dan Umum;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Faslitasi pelaksanaan kehumasan, keprotokolan, publikasi dan Dokumentasi;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Fasilitasi penyusunan dan pelaporan ketatalaksanaan, yang meliputi proses bisnis, standar operasional prosedur, standar pelayanan publik, dan survey kepuasan masyarakat Dinas;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pelaksanaan reformasi birokrasi, inovasi, sistem pengendalian internal pemerintah, zona integritas, ketatalaksanaan dan budaya ASN pada Sub Bagian Kepegawaian dan Umum;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Fasilitasi dan koordinasi tindak lanjut laporan hasil pemeriksaan pada Sub Bagian Kepegawaian dan Umum;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pelaksanaan pemantauan, evaluasi dan penyusunan laporan kegiatan pada Sub Bagian Kepegawaian dan Umum; dan</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span><span>Pelaksanaan fungsi lain yang diberikan oleh atasan sesuai dengan tugas dinas.</span></li>
                        </ul>
                    </div>
                </div>

                <!-- 4. DETAIL BIDANG TATA DAN PENATAAN LINGKUNGAN -->
                <div x-show="activeSub === 'tata-lingkungan'" x-cloak x-transition.opacity.duration.250ms class="space-y-6">
                    <div class="relative isolate overflow-hidden rounded-2xl bg-gradient-to-br from-teal-700 via-cyan-800 to-slate-950 p-6 text-white shadow-xl shadow-teal-950/20 sm:p-8">
                        <div class="pointer-events-none absolute -right-12 -top-12 size-48 rounded-full bg-teal-400/20 blur-3xl"></div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-11 items-center justify-center rounded-xl bg-white/10 text-teal-300 ring-1 ring-white/20 backdrop-blur-md">
                                <x-icons.tata-penataan class="size-6" />
                            </span>
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-teal-200/90">{{ __('Bidang Pelaksana Teknis') }}</span>
                                <h2 class="text-xl font-extrabold text-white sm:text-2xl">3. Bidang Tata dan Penataan Lingkungan</h2>
                            </div>
                        </div>
                        <div class="mt-5 border-t border-white/10 pt-5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-teal-300">{{ __('Tugas Pokok:') }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-teal-50/90">
                                Membantu Kepala Dinas merumuskan, menyusun, mengoordinasikan, menyelenggarakan, pembinaan, monitoring, evaluasi dan pelaporan pelaksanaan kebijakan di bidang Tata dan Penataan Lingkungan.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-7">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-teal-100 text-teal-700 dark:bg-teal-950 dark:text-teal-300">
                                    <x-icons.ui name="check-circle" class="size-4" />
                                </span>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Rincian Fungsi Bidang</h3>
                            </div>
                            <span class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-bold text-teal-700 dark:bg-teal-950 dark:text-teal-300">6 Poin Fungsi</span>
                        </div>
                        <ul class="mt-5 grid gap-2.5 sm:grid-cols-2">
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Penyusunan rencana program dan kegiatan Bidang Tata dan Penataan Lingkungan;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Penyusunan petunjuk teknis Bidang Tata dan Penataan Lingkungan;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Pelaksanaan koordinasi dan sinkronisasi penerapan kebijakan di Bidang Tata dan Penataan Lingkungan;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Pelaksanaan, pengawasan dan pembinaan, serta pengembangan di Bidang Tata dan Penataan Lingkungan;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Pelaksanaan evaluasi dan pelaporan kinerja Bidang Tata dan Penataan Lingkungan;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Melaksanakan fungsi lain yang diberikan oleh atasan sesuai dengan tugasnya.</span></li>
                        </ul>
                    </div>
                </div>

                <!-- 5. DETAIL BIDANG PENGENDALIAN PENCEMARAN -->
                <div x-show="activeSub === 'pengendalian'" x-cloak x-transition.opacity.duration.250ms class="space-y-6">
                    <div class="relative isolate overflow-hidden rounded-2xl bg-gradient-to-br from-teal-700 via-cyan-800 to-slate-950 p-6 text-white shadow-xl shadow-teal-950/20 sm:p-8">
                        <div class="pointer-events-none absolute -right-12 -top-12 size-48 rounded-full bg-teal-400/20 blur-3xl"></div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-11 items-center justify-center rounded-xl bg-white/10 text-teal-300 ring-1 ring-white/20 backdrop-blur-md">
                                <x-icons.pengendalian class="size-6" />
                            </span>
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-teal-200/90">{{ __('Bidang Pelaksana Teknis') }}</span>
                                <h2 class="text-xl font-extrabold text-white sm:text-2xl">4. Bidang Pengendalian Pencemaran, Kerusakan & Kapasitas</h2>
                            </div>
                        </div>
                        <div class="mt-5 border-t border-white/10 pt-5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-teal-300">{{ __('Tugas Pokok:') }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-teal-50/90">
                                Membantu Kepala Dinas merumuskan, menyusun, mengoordinasikan, menyelenggarakan, pembinaan, monitoring, evaluasi dan pelaporan pelaksanaan kebijakan di bidang Pengendalian Pencemaran Kerusakan, dan Pengembangan Kapasitas Lingkungan.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-7">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-teal-100 text-teal-700 dark:bg-teal-950 dark:text-teal-300">
                                    <x-icons.ui name="check-circle" class="size-4" />
                                </span>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Rincian Fungsi Bidang</h3>
                            </div>
                            <span class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-bold text-teal-700 dark:bg-teal-950 dark:text-teal-300">6 Poin Fungsi</span>
                        </div>
                        <ul class="mt-5 grid gap-2.5 sm:grid-cols-2">
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Penyusunan rencana program dan kegiatan Bidang Pengendalian Pencemaran Kerusakan, dan Pengembangan Kapasitas Lingkungan;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Pemberian petunjuk teknis di Bidang Pengendalian Pencemaran Kerusakan, dan Pengembangan Kapasitas Lingkungan;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Pelaksanaan koordinasi dan sinkronisasi penerapan kebijakan Pengendalian Pencemaran Kerusakan, dan Pengembangan Kapasitas Lingkungan;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Pelaksanaan, pengawasan, dan pembinaan serta pengembangan di bidang Pengendalian Pencemaran Kerusakan, dan Pengembangan Kapasitas Lingkungan;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Melaksanaan evaluasi dan pelaporan kinerja Bidang Pengendalian Pencemaran Kerusakan, dan Pengembangan Kapasitas Lingkungan;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Pelaksanaan fungsi lain yang diberikan oleh atasan sesuai dengan tugasnya.</span></li>
                        </ul>
                    </div>
                </div>

                <!-- 6. DETAIL BIDANG PENGELOLAAN SAMPAH DAN LIMBAH B3 -->
                <div x-show="activeSub === 'sampah-lb3'" x-cloak x-transition.opacity.duration.250ms class="space-y-6">
                    <div class="relative isolate overflow-hidden rounded-2xl bg-gradient-to-br from-teal-700 via-cyan-800 to-slate-950 p-6 text-white shadow-xl shadow-teal-950/20 sm:p-8">
                        <div class="pointer-events-none absolute -right-12 -top-12 size-48 rounded-full bg-teal-400/20 blur-3xl"></div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-11 items-center justify-center rounded-xl bg-white/10 text-teal-300 ring-1 ring-white/20 backdrop-blur-md">
                                <x-icons.sampah class="size-6" />
                            </span>
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-teal-200/90">{{ __('Bidang Pelaksana Teknis') }}</span>
                                <h2 class="text-xl font-extrabold text-white sm:text-2xl">5. Bidang Pengelolaan Sampah dan Limbah B3</h2>
                            </div>
                        </div>
                        <div class="mt-5 border-t border-white/10 pt-5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-teal-300">{{ __('Tugas Pokok:') }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-teal-50/90">
                                Membantu Kepala Dinas merumuskan, menyusun, mengoordinasikan, menyelenggarakan, pembinaan, monitoring, evaluasi dan pelaporan pelaksanaan kebijakan Bidang Pengelolaan Sampah dan Limbah Bahan Beracun Berbahaya.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-7">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-teal-100 text-teal-700 dark:bg-teal-950 dark:text-teal-300">
                                    <x-icons.ui name="check-circle" class="size-4" />
                                </span>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Rincian Fungsi Bidang</h3>
                            </div>
                            <span class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-bold text-teal-700 dark:bg-teal-950 dark:text-teal-300">6 Poin Fungsi</span>
                        </div>
                        <ul class="mt-5 grid gap-2.5 sm:grid-cols-2">
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Penyusunan rencana program dan kegiatan Bidang Pengelolaan Sampah dan Limbah Bahan Beracun Berbahaya;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Penyusunan pemberian petunjuk teknis Bidang Pengelolaan Sampah dan Limbah Bahan Beracun Berbahaya;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Pelaksanaan koordinasi dan sinkronisasi penerapan kebijakan Bidang Pengelolaan Sampah dan Limbah Bahan Beracun Berbahaya;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Pelaksanaan, pengawasan, dan pembinaan serta pengembangan di Bidang Pengelolaan Sampah dan Limbah Bahan Beracun Berbahaya;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Pelaksanaan evaluasi dan pelaporan kinerja Bidang Pengelolaan Sampah dan Limbah Bahan Beracun Berbahaya;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Pelaksanaan fungsi lain yang diberikan oleh atasan sesuai dengan tugasnya.</span></li>
                        </ul>
                    </div>
                </div>

                <!-- 7. DETAIL BIDANG PENGELOLAAN RUANG TERBUKA HIJAU -->
                <div x-show="activeSub === 'rth'" x-cloak x-transition.opacity.duration.250ms class="space-y-6">
                    <div class="relative isolate overflow-hidden rounded-2xl bg-gradient-to-br from-teal-700 via-cyan-800 to-slate-950 p-6 text-white shadow-xl shadow-teal-950/20 sm:p-8">
                        <div class="pointer-events-none absolute -right-12 -top-12 size-48 rounded-full bg-teal-400/20 blur-3xl"></div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-11 items-center justify-center rounded-xl bg-white/10 text-teal-300 ring-1 ring-white/20 backdrop-blur-md">
                                <x-icons.rth class="size-6" />
                            </span>
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-teal-200/90">{{ __('Bidang Pelaksana Teknis') }}</span>
                                <h2 class="text-xl font-extrabold text-white sm:text-2xl">6. Bidang Pengelolaan Ruang Terbuka Hijau</h2>
                            </div>
                        </div>
                        <div class="mt-5 border-t border-white/10 pt-5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-teal-300">{{ __('Tugas Pokok:') }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-teal-50/90">
                                Membantu Kepala Dinas merumuskan, menyusun, mengoordinasikan, menyelenggarakan, pembinaan, monitoring, evaluasi dan pelaporan pelaksanaan kebijakan Bidang Pengelolaan Ruang Terbuka Hijau.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-7">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-teal-100 text-teal-700 dark:bg-teal-950 dark:text-teal-300">
                                    <x-icons.ui name="check-circle" class="size-4" />
                                </span>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Rincian Fungsi Bidang</h3>
                            </div>
                            <span class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-bold text-teal-700 dark:bg-teal-950 dark:text-teal-300">8 Poin Fungsi</span>
                        </div>
                        <ul class="mt-5 grid gap-2.5 sm:grid-cols-2">
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Penyusunan rencana program dan kegiatan Bidang Pengelolaan Ruang Terbuka Hijau.</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Penyusunan petunjuk teknis di Bidang Pengelolaan Ruang Terbuka Hijau;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Pelaksanaan koordinasi dan sinkronisasi penerapan kebijakan pengelolaan ruang terbuka hijau;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Pelaksanaan, pengawasan, dan pembinaan serta pengembangan Bidang Pengelolaan Ruang Terbuka Hijau;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Melaksanakan evaluasi dan pelaporan kinerja Bidang Pengelolaan Ruang Terbuka Hijau;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Melaksanaan evaluasi dan pelaporan kinerja Seksi Pemakaman;</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Penyusunan laporan pelaksanaan kebijakan teknis pemakaman; dan</span></li>
                            <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs sm:text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"><span class="mt-1 size-1.5 shrink-0 rounded-full bg-teal-500"></span><span>Melaksanakan fungsi lain yang diberikan oleh atasan.</span></li>
                        </ul>
                    </div>
                </div>

                <!-- 8. DETAIL UPTD LAB LINGKUNGAN -->
                <div x-show="activeSub === 'uptd-lab'" x-cloak x-transition.opacity.duration.250ms class="space-y-6">
                    <div class="relative isolate overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-800 via-teal-800 to-slate-950 p-6 text-white shadow-xl shadow-emerald-950/20 sm:p-8">
                        <div class="pointer-events-none absolute -right-12 -top-12 size-48 rounded-full bg-emerald-400/20 blur-3xl"></div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-11 items-center justify-center rounded-xl bg-white/10 text-emerald-300 ring-1 ring-white/20 backdrop-blur-md">
                                <x-icons.ui name="beaker" class="size-6" />
                            </span>
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-200/90">{{ __('Unit Pelaksana Teknis Daerah') }}</span>
                                <h2 class="text-xl font-extrabold text-white sm:text-2xl">UPTD Laboratorium Lingkungan</h2>
                            </div>
                        </div>
                        <div class="mt-5 border-t border-white/10 pt-5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-300">{{ __('Tugas Pokok:') }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-emerald-50/90 text-justify">
                                Melaksanakan sebagian kegiatan teknis operasional dinas dalam lingkup penyelenggaraan pemantauan kualitas lingkungan dalam rangka peningkatan kualitas lingkungan.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-7">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                    <x-icons.ui name="check-circle" class="size-4" />
                                </span>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Penyelenggaraan Fungsi UPTD</h3>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">6 Poin Fungsi</span>
                        </div>
                        <ol class="mt-5 grid gap-3 sm:grid-cols-2">
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">01</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300"><strong class="font-bold text-slate-900 dark:text-white">Perencanaan & Sistem Manajemen Mutu:</strong> Menyusun rencana program kegiatan, mengesahkan panduan mutu, serta melakukan kaji ulang dan perbaikan Sistem Manajemen Mutu Laboratorium secara berkala.</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">02</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300"><strong class="font-bold text-slate-900 dark:text-white">Teknis Pengambilan Sampel & Pengujian:</strong> Melaksanakan pengambilan contoh uji sesuai standar (Good Sampling Practice), melakukan pengujian dan kalibrasi, memverifikasi data hasil uji, hingga menerbitkan laporan/sertifikat hasil pengujian.</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">03</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300"><strong class="font-bold text-slate-900 dark:text-white">Penjaminan Mutu (QA/QC) & Audit:</strong> Menerapkan dan mengawasi Quality Assurance/Quality Control (QA/QC), menyelenggarakan audit internal, memvalidasi metode uji, serta berpartisipasi dalam uji profisiensi/uji banding antar laboratorium.</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">04</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300"><strong class="font-bold text-slate-900 dark:text-white">Pengelolaan Fasilitas & Logistik:</strong> Merencanakan, mengadakan, memverifikasi, dan memelihara peralatan, instrumen, serta bahan habis pakai laboratorium beserta rekaman pemasoknya.</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">05</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300"><strong class="font-bold text-slate-900 dark:text-white">Pelayanan & Penanganan Keluhan:</strong> Menangani administrasi penerimaan sampel, merespons pengaduan pelanggan, serta melakukan penelusuran atau pengujian ulang (terhadap retained sample) jika diperlukan.</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">06</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300"><strong class="font-bold text-slate-900 dark:text-white">Ketatausahaan & Pelaporan:</strong> Melaksanakan urusan tata usaha, rumah tangga, koordinasi lintas instansi, penyusunan laporan evaluasi, serta melaksanakan tugas kedinasan lain dari Kepala Dinas.</span>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- 9. DETAIL UPTD TPA KAWATUNA -->
                <div x-show="activeSub === 'uptd-tpa'" x-cloak x-transition.opacity.duration.250ms class="space-y-6">
                    <div class="relative isolate overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-800 via-teal-800 to-slate-950 p-6 text-white shadow-xl shadow-emerald-950/20 sm:p-8">
                        <div class="pointer-events-none absolute -right-12 -top-12 size-48 rounded-full bg-emerald-400/20 blur-3xl"></div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-11 items-center justify-center rounded-xl bg-white/10 text-emerald-300 ring-1 ring-white/20 backdrop-blur-md">
                                <x-icons.ui name="recycle" class="size-6" />
                            </span>
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-200/90">{{ __('Unit Pelaksana Teknis Daerah') }}</span>
                                <h2 class="text-xl font-extrabold text-white sm:text-2xl">UPTD TPA Kawatuna</h2>
                            </div>
                        </div>
                        <div class="mt-5 border-t border-white/10 pt-5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-300">{{ __('Tugas Pokok:') }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-emerald-50/90 text-justify">
                                Membantu Kepala Dinas dalam melaksanakan kegiatan teknis operasional penyelenggaraan dan pengelolaan Tempat Pemrosesan Akhir (TPA).
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-7">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                    <x-icons.ui name="check-circle" class="size-4" />
                                </span>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Penyelenggaraan Fungsi UPTD</h3>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">6 Poin Fungsi</span>
                        </div>
                        <ol class="mt-5 grid gap-3 sm:grid-cols-2">
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">01</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300"><strong class="font-bold text-slate-900 dark:text-white">Perencanaan:</strong> Menyusun rencana program dan kegiatan operasional pengelolaan TPA.</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">02</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300"><strong class="font-bold text-slate-900 dark:text-white">Pengelolaan:</strong> Melaksanakan penanganan dan pengelolaan sampah secara langsung di lokasi TPA.</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">03</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300"><strong class="font-bold text-slate-900 dark:text-white">Pemeliharaan:</strong> Merawat dan memelihara seluruh sarana serta prasarana operasional TPA.</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">04</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300"><strong class="font-bold text-slate-900 dark:text-white">Administrasi:</strong> Menjalankan kegiatan ketatausahaan dan urusan rumah tangga internal UPTD.</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">05</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300"><strong class="font-bold text-slate-900 dark:text-white">Evaluasi & Pelaporan:</strong> Melakukan evaluasi kinerja dan menyusun laporan pelaksanaan tugas secara berkala.</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-emerald-200 hover:bg-white hover:shadow-sm dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-xs font-black text-white">06</span>
                                <span class="text-xs sm:text-sm leading-relaxed text-slate-700 dark:text-slate-300"><strong class="font-bold text-slate-900 dark:text-white">Tugas Tambahan:</strong> Melaksanakan instruksi dan tugas lain yang diberikan oleh Kepala Dinas sesuai dengan bidang terkait.</span>
                            </li>
                        </ol>
                    </div>
                </div>

            </main>
        </div>
    </section>
</div>
@endsection
