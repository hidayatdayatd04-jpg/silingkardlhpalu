@extends('layouts.app')

@section('title', 'Topoksi Lab - UPTD Laboratorium Lingkungan Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Tugas pokok dan fungsi UPTD Laboratorium Lingkungan Dinas Lingkungan Hidup Kota Palu: pemantauan kualitas lingkungan, pengambilan sampel, pengujian, dan penjaminan mutu laboratorium.')

@section('content')
<div class="lab-page space-y-8 pb-20 sm:space-y-10">
    <x-public.page-hero
        title="{{ __('Topoksi Lab') }}"
        description="{{ __('UPTD Laboratorium Lingkungan melaksanakan sebagian kegiatan teknis operasional dinas dalam lingkup penyelenggaraan pemantauan kualitas lingkungan dalam rangka peningkatan kualitas lingkungan.') }}"
        badge="{{ __('UPTD Laboratorium Lingkungan') }}"
        icon="tool"
    />

    <section class="reveal grid gap-3 rounded-[1.75rem] border border-brand-100 bg-white p-4 shadow-[0_20px_46px_-34px_rgba(15,23,42,0.45)] dark:border-brand-900/60 dark:bg-slate-900 sm:grid-cols-3 sm:p-5" aria-label="{{ __('Ruang layanan laboratorium') }}">
        <div class="flex items-center gap-3 rounded-2xl bg-brand-50/70 p-4 dark:bg-brand-950/30">
            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-brand-700 shadow-sm ring-1 ring-brand-100 dark:bg-slate-900 dark:text-brand-200 dark:ring-brand-800">
                <x-icons.ui name="leaf" class="size-5" />
            </span>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-600 dark:text-brand-300">{{ __('Fokus') }}</p>
                <p class="mt-1 text-sm font-extrabold text-slate-800 dark:text-slate-100">{{ __('Kualitas lingkungan') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl bg-sky-50/70 p-4 dark:bg-sky-950/25">
            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-sky-700 shadow-sm ring-1 ring-sky-100 dark:bg-slate-900 dark:text-sky-200 dark:ring-sky-800">
                <x-icons.ui name="tool" class="size-5" />
            </span>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-sky-600 dark:text-sky-300">{{ __('Layanan') }}</p>
                <p class="mt-1 text-sm font-extrabold text-slate-800 dark:text-slate-100">{{ __('Sampel dan pengujian') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl bg-amber-50/70 p-4 dark:bg-amber-950/25">
            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-amber-700 shadow-sm ring-1 ring-amber-100 dark:bg-slate-900 dark:text-amber-200 dark:ring-amber-800">
                <x-icons.ui name="shield" class="size-5" />
            </span>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700 dark:text-amber-300">{{ __('Standar') }}</p>
                <p class="mt-1 text-sm font-extrabold text-slate-800 dark:text-slate-100">{{ __('Penjaminan mutu') }}</p>
            </div>
        </div>
    </section>

    <section class="grid gap-5 lg:grid-cols-2" aria-label="{{ __('Aktivitas dan fasilitas laboratorium') }}">
        <figure class="reveal group flex h-full flex-col overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-[0_20px_46px_-34px_rgba(15,23,42,0.5)] transition-[transform,border-color,box-shadow] duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-[0_28px_54px_-34px_rgba(5,150,105,0.34)] dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-700">
            <div class="relative h-64 shrink-0 overflow-hidden sm:h-80">
                <img src="{{ asset('assets/images/lab-lingkungan-1.jpeg') }}" alt="{{ __('Aktivitas pengujian sampel di UPTD Laboratorium Lingkungan') }}" class="size-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-slate-950/50 to-transparent" aria-hidden="true"></div>
                <span class="absolute bottom-4 left-4 inline-flex size-11 items-center justify-center rounded-2xl bg-white/90 text-brand-700 shadow-lg backdrop-blur-sm dark:bg-slate-900/90 dark:text-brand-200">
                    <x-icons.ui name="tool" class="size-5" />
                </span>
            </div>
            <figcaption class="flex flex-1 items-start gap-4 p-6 sm:p-7">
                <span class="mt-0.5 inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-200">
                    <x-icons.ui name="check" class="size-4" />
                </span>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-600 dark:text-brand-300">{{ __('Proses terstandar') }}</p>
                    <h2 class="mt-2 text-xl font-extrabold tracking-[-0.025em] text-slate-900 dark:text-white">{{ __('Kegiatan Pengujian Sampel') }}</h2>
                    <p class="mt-2.5 text-sm leading-7 text-slate-600 dark:text-slate-400">{{ __('Pengambilan dan pengujian contoh uji sesuai standar, verifikasi data hasil uji, hingga penerbitan laporan hasil pengujian.') }}</p>
                </div>
            </figcaption>
        </figure>

        <figure class="reveal group flex h-full flex-col overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-[0_20px_46px_-34px_rgba(15,23,42,0.5)] transition-[transform,border-color,box-shadow] duration-300 hover:-translate-y-1 hover:border-sky-300 hover:shadow-[0_28px_54px_-34px_rgba(2,132,199,0.3)] dark:border-slate-800 dark:bg-slate-900 dark:hover:border-sky-700">
            <div class="relative h-64 shrink-0 overflow-hidden sm:h-80">
                <img src="{{ asset('assets/images/lab-lingkungan-2.jpeg') }}" alt="{{ __('Peralatan dan kegiatan UPTD Laboratorium Lingkungan') }}" class="size-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-slate-950/50 to-transparent" aria-hidden="true"></div>
                <span class="absolute bottom-4 left-4 inline-flex size-11 items-center justify-center rounded-2xl bg-white/90 text-sky-700 shadow-lg backdrop-blur-sm dark:bg-slate-900/90 dark:text-sky-200">
                    <x-icons.ui name="tool" class="size-5" />
                </span>
            </div>
            <figcaption class="flex flex-1 items-start gap-4 p-6 sm:p-7">
                <span class="mt-0.5 inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-200">
                    <x-icons.ui name="folder" class="size-4" />
                </span>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-sky-600 dark:text-sky-300">{{ __('Fasilitas terkelola') }}</p>
                    <h2 class="mt-2 text-xl font-extrabold tracking-[-0.025em] text-slate-900 dark:text-white">{{ __('Fasilitas & Peralatan Laboratorium') }}</h2>
                    <p class="mt-2.5 text-sm leading-7 text-slate-600 dark:text-slate-400">{{ __('Peralatan dan instrumen laboratorium yang direncanakan, dipelihara, dan diverifikasi guna mendukung pelayanan pengujian.') }}</p>
                </div>
            </figcaption>
        </figure>
    </section>

    <section class="reveal relative isolate overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-[0_20px_46px_-34px_rgba(15,23,42,0.45)] dark:border-slate-800 dark:bg-slate-900 sm:p-8 lg:p-10">
        <div class="pointer-events-none absolute -right-16 -top-20 size-64 rounded-full bg-brand-100/70 blur-3xl dark:bg-brand-900/20" aria-hidden="true"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-4xl">
                <div class="flex items-center gap-4">
                    <span class="inline-flex size-14 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-100 dark:bg-brand-900/30 dark:text-brand-200 dark:ring-brand-800/70">
                        <x-icons.ui name="shield" class="size-6" />
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-600 dark:text-brand-300">{{ __('Mandat pelayanan') }}</p>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-[-0.03em] text-slate-900 dark:text-white sm:text-3xl">{{ __('Tugas Pokok & Fungsi Utama') }}</h2>
                    </div>
                </div>
                <p class="mt-6 text-base leading-8 text-slate-600 dark:text-slate-300 sm:text-lg">{{ __('UPTD Laboratorium Lingkungan melaksanakan sebagian kegiatan teknis operasional dinas dalam lingkup penyelenggaraan pemantauan kualitas lingkungan dalam rangka peningkatan kualitas lingkungan, meliputi:') }}</p>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-900/25 dark:text-emerald-200 dark:ring-emerald-800/60">
                <x-icons.ui name="leaf" class="size-4" />
                {{ __('Data yang dapat dipertanggungjawabkan') }}
            </span>
        </div>
    </section>

    @php
        $ruangLingkup = [
            [
                'title' => 'Perencanaan & Sistem Manajemen Mutu',
                'desc' => 'Menyusun rencana program kegiatan, mengesahkan panduan mutu, serta melakukan kaji ulang dan perbaikan Sistem Manajemen Mutu Laboratorium secara berkala.',
                'icon' => 'document',
            ],
            [
                'title' => 'Teknis Pengambilan Sampel & Pengujian',
                'desc' => 'Melaksanakan pengambilan contoh uji sesuai standar (Good Sampling Practice), melakukan pengujian dan kalibrasi, memverifikasi data hasil uji, hingga menerbitkan laporan/sertifikat hasil pengujian.',
                'icon' => 'tool',
            ],
            [
                'title' => 'Penjaminan Mutu (QA/QC) & Audit',
                'desc' => 'Menerapkan dan mengawasi Quality Assurance/Quality Control (QA/QC), menyelenggarakan audit internal, memvalidasi metode uji, serta berpartisipasi dalam uji profisiensi/uji banding antar laboratorium.',
                'icon' => 'shield',
            ],
            [
                'title' => 'Pengelolaan Fasilitas & Logistik',
                'desc' => 'Merencanakan, mengadakan, memverifikasi, dan memelihara peralatan, instrumen, serta bahan habis pakai laboratorium beserta rekaman pemasoknya.',
                'icon' => 'folder',
            ],
            [
                'title' => 'Pelayanan & Penanganan Keluhan',
                'desc' => 'Menangani administrasi penerimaan sampel, merespons pengaduan pelanggan, serta melakukan penelusuran atau pengujian ulang (terhadap retained sample) jika diperlukan.',
                'icon' => 'message',
            ],
            [
                'title' => 'Ketatausahaan & Pelaporan',
                'desc' => 'Melaksanakan urusan tata usaha, rumah tangga, koordinasi lintas instansi, penyusunan laporan evaluasi, serta melaksanakan tugas kedinasan lain dari Kepala Dinas.',
                'icon' => 'document',
            ],
        ];
    @endphp

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" aria-label="{{ __('Ruang lingkup tugas UPTD Laboratorium Lingkungan') }}">
        @foreach ($ruangLingkup as $i => $item)
            <article class="reveal group relative flex min-h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_16px_36px_-30px_rgba(15,23,42,0.46)] transition-[transform,border-color,box-shadow] duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-[0_24px_42px_-30px_rgba(5,150,105,0.36)] dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-700">
                <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-500 via-emerald-400 to-teal-400 opacity-0 transition-opacity duration-300 group-hover:opacity-100" aria-hidden="true"></span>
                <div class="flex items-start justify-between gap-4">
                    <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-100 transition-transform duration-300 group-hover:scale-105 dark:bg-brand-900/30 dark:text-brand-200 dark:ring-brand-800/70">
                        <x-icons.ui :name="$item['icon']" class="size-5" />
                    </span>
                    <span class="text-xs font-extrabold tracking-[0.14em] text-brand-500/80 dark:text-brand-300/80">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                </div>
                <h2 class="mt-5 text-lg font-extrabold leading-7 tracking-[-0.02em] text-slate-900 dark:text-white">{{ $item['title'] }}</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400">{{ $item['desc'] }}</p>
            </article>
        @endforeach
    </section>
</div>
@endsection
