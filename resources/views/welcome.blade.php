@extends('layouts.app')

@section('title', 'Beranda - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Selamat datang di Portal SILP Dinas Lingkungan Hidup Kota Palu. Akses layanan multi-bidang: pengaduan lingkungan, pengelolaan sampah & LB3, ruang terbuka hijau, pelacakan armada, dan survei kepuasan.')
@section('full_width', '')

@push('styles')
    {{-- LCP = foto hero: preload dengan prioritas tinggi agar mulai diunduh
         paralel dengan CSS, tidak menunggu parse HTML sampai <img>.
         Varian mobile (800px, ~55KB) dipakai di layar < 768px — Lighthouse
         mobile (412px @ DPR 1.75 = 721px fisik) mengunduh file yang jauh
         lebih kecil; desktop tetap memakai hero.webp (1200px). --}}
    <link rel="preload" as="image" href="{{ asset('assets/images/hero-mobile.webp') }}" media="(max-width: 767px)" fetchpriority="high">
    <link rel="preload" as="image" href="{{ asset('assets/images/hero.webp') }}" media="(min-width: 768px)" fetchpriority="high">
@endpush

@section('content')
{{-- Preloader penyambutan — HANYA di beranda, tampil ~3 detik setiap refresh. --}}
<x-public.preloader />
<div class="overflow-x-clip">

    {{-- ============================================================= --}}
    {{-- HERO — pernyataan utama halaman + momen signature (entrance)   --}}
    {{-- ============================================================= --}}
    <section class="relative isolate overflow-hidden">
        {{-- Foto latar lingkungan/Kota Palu --}}
        <div class="absolute inset-0 -z-10">
            <picture class="block h-full w-full">
                <source srcset="{{ asset('assets/images/hero-mobile.webp') }}" media="(max-width: 767px)">
                <img src="{{ asset('assets/images/hero.webp') }}" alt="" aria-hidden="true" fetchpriority="high"
                     class="h-full w-full object-cover object-center scale-105">
            </picture>
            {{-- Lapisan gradien untuk kontras teks (WCAG AA) + nuansa teluk --}}
            <div class="absolute inset-0 bg-gradient-to-br from-brand-950/95 via-brand-800/85 to-bay-900/80"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(45,212,191,0.22),transparent_46%),radial-gradient(circle_at_82%_78%,rgba(13,171,206,0.20),transparent_44%)]"></div>
            <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-slate-50 dark:from-slate-950 to-transparent"></div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-32 sm:pt-28 sm:pb-40 lg:pt-32">
            <div class="max-w-3xl">
                <span class="hero-enter inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-inset ring-white/25 px-4 py-1.5 text-xs font-semibold tracking-wide uppercase text-white backdrop-blur-md"
                      style="--hero-delay:0ms">
                    <span class="relative flex size-2">
                        <span class="status-ping absolute inline-flex h-full w-full rounded-full bg-brand-300"></span>
                        <span class="relative inline-flex size-2 rounded-full bg-brand-300"></span>
                    </span>
                    {{ __('Sistem Layanan Publik Digital Terpadu') }}
                </span>

                <h1 class="hero-enter mt-6 text-4xl sm:text-5xl lg:text-[3.5rem] font-bold tracking-tight text-white leading-[1.05]"
                    style="--hero-delay:90ms">
                    {{ __('Menjaga Palu Tetap') }}
                    <span class="block bg-gradient-to-r from-brand-200 via-emerald-200 to-bay-200 bg-clip-text text-transparent">
                        {{ __('Bersih, Hijau & Asri') }}
                    </span>
                </h1>

                <p class="hero-enter mt-6 text-base sm:text-lg text-brand-50/90 max-w-2xl leading-relaxed"
                   style="--hero-delay:180ms">
                    {{ __('Portal resmi Dinas Lingkungan Hidup Kota Palu untuk pengaduan lingkungan, pengelolaan sampah & LB3, ruang terbuka hijau, dan pelacakan armada — cepat, transparan, tanpa perlu mendaftar akun.') }}
                </p>

                <div class="hero-enter mt-9 flex flex-col sm:flex-row gap-3" style="--hero-delay:270ms">
                    <a href="/pengaduan"
                       class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-7 py-3.5 text-sm font-bold text-brand-700 shadow-xl shadow-brand-950/30 ring-1 ring-white/60 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-2xl hover:shadow-brand-900/40 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-white/50">
                        <svg class="size-4 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        {{ __('Laporkan Aduan') }}
                    </a>
                    <a href="/lacak"
                       class="group inline-flex items-center justify-center gap-2 rounded-2xl border border-white/40 bg-white/5 px-7 py-3.5 text-sm font-semibold text-white backdrop-blur-md transition-all duration-300 hover:bg-white/15 hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-white/30">
                        <svg class="size-4 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                        {{ __('Lacak Status') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================= --}}
    {{-- STATISTIK — kartu mengambang menimpa hero                      --}}
    {{-- ============================================================= --}}
    <section class="relative z-10 -mt-20 sm:-mt-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                @php $stats = [
                    ['label' => __('Pengunjung Hari Ini'), 'value' => $statistik['pengunjung_hari_ini'] ?? 0, 'icon' => 'M15 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75', 'ring' => 'ring-bay-500/20', 'grad' => 'from-bay-500 to-bay-600'],
                    ['label' => __('Total Pengunjung'), 'value' => $statistik['total_pengunjung'] ?? 0, 'icon' => 'M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z', 'ring' => 'ring-brand-500/20', 'grad' => 'from-brand-500 to-emerald-500'],
                    ['label' => __('Total Pelapor'), 'value' => $statistik['total_pelapor'] ?? 0, 'icon' => 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10zM9 12l2 2 4-4', 'ring' => 'ring-clay-500/20', 'grad' => 'from-clay-500 to-clay-600'],
                    ['label' => __('Total Pengajuan'), 'value' => $statistik['total_pengajuan'] ?? 0, 'icon' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8ZM14 2v6h6M9 13h6M9 17h4', 'ring' => 'ring-amber-500/20', 'grad' => 'from-amber-500 to-orange-500'],
                ]; @endphp
                @foreach ($stats as $i => $card)
                    <div class="reveal group rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm border border-slate-200/70 dark:border-slate-800 p-4 sm:p-5 shadow-[0_10px_40px_-12px_rgba(15,23,42,0.18)] ring-1 {{ $card['ring'] }} transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_20px_50px_-12px_rgba(15,23,42,0.28)]"
                         style="--reveal-delay: {{ $i * 80 }}ms">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 size-11 rounded-2xl bg-gradient-to-br {{ $card['grad'] }} text-white flex items-center justify-center shadow-sm transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $card['icon'] }}"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-2xl font-bold text-slate-900 dark:text-white truncate tracking-tight" data-countup data-count="{{ (int) $card['value'] }}">{{ number_format($card['value']) }}</p>
                                <p class="text-[11px] sm:text-xs font-medium text-slate-500 dark:text-slate-400 leading-tight">{{ $card['label'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Konten selanjutnya dengan ritme vertikal yang disengaja --}}
    <div class="space-y-28 sm:space-y-40 pt-24 sm:pt-32 pb-24">

        {{-- ========================================================= --}}
        {{-- PILIH BIDANG LAYANAN — kartu premium, ikon konsisten       --}}
        {{-- ========================================================= --}}
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal max-w-2xl mb-12">
                <span class="inline-block text-xs font-semibold uppercase tracking-[0.14em] text-brand-600 dark:text-brand-400 mb-3">{{ __('Layanan Terpadu') }}</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">{{ __('Pilih Bidang Layanan Anda') }}</h2>
                <p class="mt-3 text-base text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('Empat bidang utama DLH Kota Palu, dirancang untuk mengarahkan Anda ke layanan yang tepat dalam sekali klik.') }}</p>
            </div>

            @php
            $bidangs = [
                [
                    'title' => __('Pengendalian'),
                    'desc' => __('Pengaduan dampak lingkungan, permohonan rekomendasi lingkungan.'),
                    'accent' => 'clay',
                    'icon' => 'pengendalian',
                    'links' => [
                        [__('Pengaduan'), '/pengaduan'],
                        [__('Cek Status'), '/lacak'],
                        [__('Permohonan'), '/permohonan-rekomendasi'],
                    ],
                ],
                [
                    'title' => __('Sampah & LB3'),
                    'desc' => __('Peta persampahan, pengaduan, registrasi LB3, & RINTEK/PERTEK.'),
                    'accent' => 'amber',
                    'icon' => 'sampah',
                    'links' => [
                        [__('Peta Sampah'), '/peta-persampahan'],
                        [__('Pengaduan'), '/pengaduan'],
                        [__('Registrasi LB3'), '/registrasi-usaha-lb3'],
                        ['RINTEK/PERTEK', '/pengajuan-rintek-pertek'],
                        [__('Cek RINTEK/PERTEK'), '/cek-rintek-pertek'],
                    ],
                ],
                [
                    'title' => __('Tata Penataan'),
                    'desc' => __('Pengaduan limbah/asap/kebisingan, peta objek pengawasan & sidak.'),
                    'accent' => 'bay',
                    'icon' => 'tata-penataan',
                    'links' => [
                        [__('Pengaduan'), '/pengaduan'],
                        [__('Cek Status'), '/lacak'],
                        [__('Peta Objek'), '/peta-objek-pengawasan'],
                    ],
                ],
                [
                    'title' => __('Ruang Terbuka Hijau'),
                    'desc' => __('Pengaduan dan penyewaan taman.'),
                    'accent' => 'brand',
                    'icon' => 'rth',
                    'links' => [
                        [__('Pengaduan'), '/pengaduan'],
                        [__('Penyewaan Taman'), '/pinjam-taman'],
                    ],
                ],
            ];
            $accentMap = [
                'brand' => ['grad' => 'from-brand-500 to-emerald-400', 'chip' => 'bg-brand-50 text-brand-600 dark:bg-brand-900/25 dark:text-brand-300', 'hover' => 'hover:border-brand-300 dark:hover:border-brand-700', 'linkHover' => 'hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-brand-900/20 dark:hover:text-brand-300', 'accentVar' => 'var(--color-brand-500)'],
                'bay'   => ['grad' => 'from-bay-500 to-bay-400', 'chip' => 'bg-bay-50 text-bay-600 dark:bg-bay-900/25 dark:text-bay-300', 'hover' => 'hover:border-bay-300 dark:hover:border-bay-700', 'linkHover' => 'hover:border-bay-300 hover:bg-bay-50 hover:text-bay-700 dark:hover:bg-bay-900/20 dark:hover:text-bay-300', 'accentVar' => 'var(--color-bay-500)'],
                'clay'  => ['grad' => 'from-clay-500 to-clay-400', 'chip' => 'bg-clay-50 text-clay-600 dark:bg-clay-900/25 dark:text-clay-300', 'hover' => 'hover:border-clay-300 dark:hover:border-clay-700', 'linkHover' => 'hover:border-clay-300 hover:bg-clay-50 hover:text-clay-700 dark:hover:bg-clay-900/20 dark:hover:text-clay-300', 'accentVar' => 'var(--color-clay-500)'],
                'amber' => ['grad' => 'from-amber-500 to-amber-400', 'chip' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/25 dark:text-amber-300', 'hover' => 'hover:border-amber-300 dark:hover:border-amber-700', 'linkHover' => 'hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 dark:hover:bg-amber-900/20 dark:hover:text-amber-300', 'accentVar' => 'var(--color-amber-500)'],
            ];
            @endphp

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($bidangs as $i => $bidang)
                    @php $a = $accentMap[$bidang['accent']]; @endphp
                    <div class="reveal group relative flex flex-col rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-[0_2px_10px_rgba(15,23,42,0.04)] transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_24px_50px_-18px_rgba(15,23,42,0.28)] {{ $a['hover'] }}"
                         style="--reveal-delay: {{ $i * 90 }}ms; --icon-accent: {{ $a['accentVar'] }}">
                        {{-- Aksen atas kartu --}}
                        <span class="absolute inset-x-6 top-0 h-1 rounded-full bg-gradient-to-r {{ $a['grad'] }} opacity-70 transition-opacity duration-300 group-hover:opacity-100"></span>

                        <div class="size-12 rounded-2xl {{ $a['chip'] }} flex items-center justify-center mb-5 transition-all duration-300 group-hover:scale-110 group-hover:-rotate-3">
                            <x-dynamic-component :component="'icons.'.$bidang['icon']" class="size-6" />
                        </div>

                        <h3 class="font-bold text-lg text-slate-900 dark:text-white">{{ $bidang['title'] }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">{{ $bidang['desc'] }}</p>

                        <div class="flex flex-wrap gap-2 mt-6 pt-5 border-t border-slate-100 dark:border-slate-800">
                            @foreach ($bidang['links'] as [$label, $url])
                                <a href="{{ $url }}" class="inline-flex items-center whitespace-nowrap rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/40 px-2.5 py-1.5 text-[11px] font-semibold text-slate-600 dark:text-slate-300 transition-all duration-200 {{ $a['linkHover'] }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- CARA MELAPOR — 3 langkah dengan konektor alur               --}}
        {{-- ========================================================= --}}
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal text-center max-w-2xl mx-auto mb-14">
                <span class="inline-block text-xs font-semibold uppercase tracking-[0.14em] text-bay-600 dark:text-bay-400 mb-3">{{ __('Alur Sederhana') }}</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">{{ __('Cara Melapor Tanpa Ribet') }}</h2>
                <p class="mt-3 text-base text-slate-500 dark:text-slate-400">{{ __('Tiga langkah berurutan — tanpa perlu mendaftar akun.') }}</p>
            </div>

            <div class="relative grid md:grid-cols-3 gap-8 md:gap-6">
                {{-- Garis konektor alur (desktop) --}}
                <div class="hidden md:block absolute top-9 left-[16.66%] right-[16.66%] h-px bg-gradient-to-r from-brand-200 via-bay-300 to-brand-200 dark:from-brand-800 dark:via-bay-800 dark:to-brand-800" aria-hidden="true"></div>

                @foreach ([
                    ['step' => '01', 'title' => __('Pilih Layanan'), 'desc' => __('Buka menu bidang terkait dan pilih jenis pengaduan atau permohonan.'), 'icon' => 'pilih-layanan'],
                    ['step' => '02', 'title' => __('Isi Formulir'), 'desc' => __('Lengkapi data, lokasi, deskripsi, dan lampirkan foto atau dokumen pendukung.'), 'icon' => 'isi-formulir'],
                    ['step' => '03', 'title' => __('Pantau Status'), 'desc' => __('Simpan nomor tiket untuk melacak progres penanganan kapan saja.'), 'icon' => 'pantau-status'],
                ] as $i => $item)
                <div class="reveal relative text-center" style="--reveal-delay: {{ $i * 140 }}ms">
                    <div class="relative z-10 mx-auto mb-6 flex size-[4.5rem] items-center justify-center">
                        <div class="absolute inset-0 rounded-full bg-white dark:bg-slate-900 shadow-[0_10px_30px_-8px_rgba(5,150,105,0.35)] ring-1 ring-brand-100 dark:ring-brand-900/50"></div>
                        <div class="relative flex size-[4.5rem] flex-col items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-emerald-400 text-white">
                            <x-dynamic-component :component="'icons.'.$item['icon']" class="size-7" />
                        </div>
                        <span class="absolute -top-2 -right-1 z-20 flex size-7 items-center justify-center rounded-full bg-slate-900 dark:bg-white text-[11px] font-bold text-white dark:text-slate-900 ring-4 ring-slate-50 dark:ring-slate-950">{{ $item['step'] }}</span>
                    </div>
                    <h3 class="font-bold text-lg text-slate-900 dark:text-white">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 leading-relaxed max-w-xs mx-auto">{{ $item['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- PROFIL KEPALA DINAS — pelayanan transparan & cepat         --}}
        {{-- ========================================================= --}}
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal relative overflow-hidden rounded-[2rem] bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-[0_20px_60px_-24px_rgba(15,23,42,0.25)]">
                <div class="absolute -top-24 -right-24 size-72 rounded-full bg-brand-500/10 blur-3xl" aria-hidden="true"></div>
                <div class="grid lg:grid-cols-5 gap-0 items-stretch">
                    {{-- Foto --}}
                    <div class="lg:col-span-2 relative min-h-[320px] lg:min-h-full">
                        <img class="absolute inset-0 h-full w-full object-cover object-top" src="{{ asset('assets/images/foto_kadis.webp') }}" alt="Kepala Dinas Lingkungan Hidup Kota Palu" loading="lazy" decoding="async">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/85 via-slate-900/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-6">
                            <p class="text-white font-bold text-lg leading-tight">Mohamad Arif, S.STP., M.Si</p>
                            <p class="text-brand-200 text-sm font-medium mt-0.5">{{ __('Kepala Dinas Lingkungan Hidup Kota Palu') }}</p>
                        </div>
                    </div>

                    {{-- Narasi --}}
                    <div class="lg:col-span-3 p-7 sm:p-10 lg:p-12 flex flex-col justify-center">
                        <span class="inline-block text-xs font-semibold uppercase tracking-[0.14em] text-brand-600 dark:text-brand-400 mb-4">{{ __('Komitmen Kami') }}</span>
                        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white leading-snug tracking-tight">
                            {{ __('Pelayanan Publik yang Transparan & Cepat') }}
                        </h2>
                        <p class="mt-4 text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ __('Dinas Lingkungan Hidup Kota Palu terus meningkatkan kualitas kebersihan, pengelolaan persampahan, dan pelestarian Ruang Terbuka Hijau melalui sistem layanan digital yang terintegrasi dan mudah diakses seluruh masyarakat.') }}
                        </p>
                        <div class="mt-7 grid sm:grid-cols-3 gap-3">
                            @foreach ([
                                ['t' => __('Terintegrasi'), 's' => __('Satu pintu layanan'), 'c' => 'brand', 'icon' => 'terintegrasi'],
                                ['t' => __('Real-time'), 's' => __('Pelacakan via GPS'), 'icon' => 'real-time', 'c' => 'bay'],
                                ['t' => __('Terbuka'), 's' => __('Status dapat dipantau'), 'icon' => 'terbuka', 'c' => 'clay'],
                            ] as $feat)
                            @php
                                $fc = [
                                    'brand' => ['chip' => 'bg-brand-50 text-brand-600 dark:bg-brand-900/25 dark:text-brand-300', 'accentVar' => 'var(--color-brand-500)'],
                                    'bay'   => ['chip' => 'bg-bay-50 text-bay-600 dark:bg-bay-900/25 dark:text-bay-300', 'accentVar' => 'var(--color-bay-500)'],
                                    'clay'  => ['chip' => 'bg-clay-50 text-clay-600 dark:bg-clay-900/25 dark:text-clay-300', 'accentVar' => 'var(--color-clay-500)'],
                                ][$feat['c']];
                            @endphp
                            <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 p-4">
                                <div class="size-9 rounded-xl {{ $fc['chip'] }} flex items-center justify-center mb-3" style="--icon-accent: {{ $fc['accentVar'] }}">
                                    <x-dynamic-component :component="'icons.'.$feat['icon']" class="size-5" />
                                </div>
                                <p class="font-bold text-slate-800 dark:text-white text-sm">{{ $feat['t'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $feat['s'] }}</p>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-7">
                            <a href="/tentang" class="inline-flex items-center gap-1.5 text-sm font-bold text-brand-600 dark:text-brand-400 hover:gap-2.5 transition-all">
                                {{ __('Selengkapnya tentang DLH Kota Palu') }}
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- DAMPAK & CAPAIAN — angka animasi count-up (bukti nyata)     --}}
        {{-- Catatan: nilai di bawah bersifat ilustratif; hubungkan ke  --}}
        {{-- data nyata bila tersedia lewat controller.                 --}}
        {{-- ========================================================= --}}
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal text-center max-w-2xl mx-auto mb-12">
                <span class="inline-block text-xs font-semibold uppercase tracking-[0.14em] text-brand-600 dark:text-brand-400 mb-3">{{ __('Dampak Nyata') }}</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">{{ __('Kerja Nyata untuk Lingkungan Palu') }}</h2>
                <p class="mt-3 text-base text-slate-500 dark:text-slate-400">{{ __('Capaian layanan dan operasional DLH Kota Palu yang terus berjalan setiap hari.') }}</p>
            </div>

            @php
            $capaian = [
                ['value' => 180, 'suffix' => ' ton', 'label' => __('Sampah Terangkut / Hari'), 'grad' => 'from-amber-500 to-orange-500', 'soft' => 'bg-amber-50 dark:bg-amber-900/20', 'text' => 'text-amber-600 dark:text-amber-400', 'icon' => 'ton-sampah', 'accentVar' => 'var(--color-amber-500)'],
                ['value' => 68, 'suffix' => ' Ha', 'label' => __('Ruang Terbuka Hijau Dikelola'), 'grad' => 'from-brand-500 to-emerald-500', 'soft' => 'bg-brand-50 dark:bg-brand-900/20', 'text' => 'text-brand-600 dark:text-brand-400', 'icon' => 'rth-ha', 'accentVar' => 'var(--color-brand-500)'],
                ['value' => 45, 'suffix' => ' Titik', 'label' => __('TPS & Kontainer Aktif'), 'grad' => 'from-bay-500 to-bay-600', 'soft' => 'bg-bay-50 dark:bg-bay-900/20', 'text' => 'text-bay-600 dark:text-bay-400', 'icon' => 'titik-tps', 'accentVar' => 'var(--color-bay-500)'],
                ['value' => 24, 'suffix' => ' Jam', 'label' => __('Respons Aduan Mendesak'), 'grad' => 'from-clay-500 to-clay-600', 'soft' => 'bg-clay-50 dark:bg-clay-900/20', 'text' => 'text-clay-600 dark:text-clay-400', 'icon' => 'jam-respons', 'accentVar' => 'var(--color-clay-500)'],
            ];
            @endphp
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($capaian as $i => $c)
                <div class="reveal group relative overflow-hidden rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 sm:p-7 text-center shadow-[0_2px_10px_rgba(15,23,42,0.04)] transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_24px_50px_-18px_rgba(15,23,42,0.25)]"
                     style="--reveal-delay: {{ $i * 90 }}ms; --icon-accent: {{ $c['accentVar'] }}">
                    <div class="absolute inset-x-0 -top-16 h-32 bg-gradient-to-b {{ $c['grad'] }} opacity-0 blur-2xl transition-opacity duration-500 group-hover:opacity-10" aria-hidden="true"></div>
                    <div class="relative mx-auto mb-4 size-14 rounded-2xl {{ $c['soft'] }} {{ $c['text'] }} flex items-center justify-center transition-transform duration-300 group-hover:scale-110">
                        <x-dynamic-component :component="'icons.'.$c['icon']" class="size-7" />
                    </div>
                    <p class="relative text-3xl sm:text-4xl font-bold tracking-tight bg-gradient-to-br {{ $c['grad'] }} bg-clip-text text-transparent"
                       data-countup data-count="{{ $c['value'] }}" data-suffix="{{ $c['suffix'] }}">0{{ $c['suffix'] }}</p>
                    <p class="relative mt-2 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 leading-tight">{!! $c['label'] !!}</p>
                </div>
                @endforeach
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- BERITA & ARTIKEL — grid diperbaiki, proporsi gambar rapi   --}}
        {{-- ========================================================= --}}
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
                <div class="max-w-xl">
                    <span class="inline-block text-xs font-semibold uppercase tracking-[0.14em] text-brand-600 dark:text-brand-400 mb-3">{{ __('Informasi Terkini') }}</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">{{ __('Berita & Artikel') }}</h2>
                    <p class="mt-3 text-base text-slate-500 dark:text-slate-400">{{ __('Update kegiatan, edukasi lingkungan, dan informasi layanan DLH Kota Palu.') }}</p>
                </div>
                <a href="/berita" class="group inline-flex items-center justify-center gap-1.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-bold text-brand-600 dark:text-brand-400 shadow-sm transition-all hover:border-brand-300 hover:bg-brand-50 dark:hover:bg-brand-900/20 shrink-0">
                    {{ __('Lihat Semua Berita') }}
                    <svg class="size-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            </div>

            @if(isset($artikels) && $artikels->count())
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($artikels->take(6) as $i => $artikel)
                        <a href="/berita/{{ $artikel->slug }}?dari=beranda"
                           class="reveal group flex flex-col rounded-3xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-[0_2px_10px_rgba(15,23,42,0.04)] transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_24px_50px_-18px_rgba(15,23,42,0.25)] hover:border-brand-200 dark:hover:border-brand-800"
                           style="--reveal-delay: {{ ($i % 3) * 100 }}ms">
                            <div class="relative aspect-[16/10] overflow-hidden bg-slate-100 dark:bg-slate-800">
                                @if($artikel->thumbnail)
                                    <img src="{{ $artikel->thumbnail ? Storage::disk('public')->temporaryUrl($artikel->thumbnail, now()->addHours(24)) : '' }}" alt="{{ $artikel->judul }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
                                @else
                                    <div class="h-full w-full bg-gradient-to-br from-brand-600 via-brand-500 to-bay-400"></div>
                                @endif
                            </div>
                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="font-bold text-slate-900 dark:text-white line-clamp-2 leading-snug group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{{ $artikel->judul }}</h3>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed flex-1">{{ Str::limit(strip_tags($artikel->konten), 120) }}</p>
                                <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-400 dark:text-slate-500">
                                    <span class="font-medium">{{ $artikel->tanggal_publish?->translatedFormat('d M Y') }}</span>
                                    @if($artikel->user)
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="size-1.5 rounded-full bg-brand-400"></span>
                                            {{ $artikel->user->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="reveal rounded-3xl border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 p-12 text-center">
                    <div class="mx-auto size-16 rounded-2xl bg-brand-50 dark:bg-brand-900/25 flex items-center justify-center text-brand-600 dark:text-brand-400 mb-4">
                        <svg class="size-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 1-2-2V7m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8Z"/></svg>
                    </div>
                    <p class="font-semibold text-slate-800 dark:text-slate-200">{{ __('Belum ada berita dipublikasikan') }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">{{ __('Artikel akan ditampilkan di sini setelah admin mempublikasikannya melalui panel admin.') }}</p>
                </div>
            @endif
        </section>

        {{-- ========================================================= --}}
        {{-- VISI & MISI — panel editorial gelap, premium & dramatis    --}}
        {{-- ========================================================= --}}
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-brand-950 via-brand-900 to-bay-950 p-8 sm:p-12 lg:p-16 ring-1 ring-white/10 shadow-[0_30px_80px_-30px_rgba(4,120,87,0.6)]">
                {{-- Aksen cahaya & motif --}}
                <div class="absolute -top-32 -right-20 size-96 rounded-full bg-brand-500/20 blur-3xl" aria-hidden="true"></div>
                <div class="absolute -bottom-32 -left-16 size-80 rounded-full bg-bay-500/20 blur-3xl" aria-hidden="true"></div>
                <svg class="absolute -top-8 right-6 size-52 text-white/[0.04] rotate-12" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17 8C8 10 5.9 16.17 3.82 21.34l1.89.66C7.72 16.5 9.8 12 17 12v3l5-5-5-5v3z"/></svg>
                <div class="absolute inset-0 opacity-[0.05]" aria-hidden="true"
                     style="background-image:radial-gradient(circle,white 1px,transparent 1px);background-size:26px 26px"></div>

                <div class="relative grid lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                    {{-- Kolom judul --}}
                    <div class="lg:col-span-4 reveal" style="--reveal-delay:80ms">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-inset ring-white/20 px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-200 mb-5">
                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 2v4M12 18v4M2 12h4M18 12h4"/></svg>
                            {{ __('Arah & Tujuan') }}
                        </span>
                        <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight leading-[1.05]">
                            {{ __('Visi &') }}<br class="hidden lg:block">
                            <span class="bg-gradient-to-r from-brand-200 via-emerald-200 to-bay-200 bg-clip-text text-transparent">{{ __('Misi Kami') }}</span>
                        </h2>
                        <p class="mt-5 text-brand-50/70 leading-relaxed max-w-md">
                            {{ __('Fondasi arah pembangunan lingkungan hidup Kota Palu yang berkelanjutan, inklusif, dan berpihak pada masyarakat.') }}
                        </p>
                        <a href="/profil#visi-misi" class="group mt-7 inline-flex items-center gap-2 rounded-2xl bg-white px-6 py-3 text-sm font-bold text-brand-800 shadow-lg shadow-brand-950/40 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl">
                            {{ __('Baca Selengkapnya') }}
                            <svg class="size-4 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </div>

                    {{-- Kolom konten --}}
                    <div class="lg:col-span-8 space-y-5">
                        <div class="reveal group relative overflow-hidden rounded-3xl bg-white/[0.07] backdrop-blur-md ring-1 ring-white/15 p-6 sm:p-8 transition-all duration-300 hover:bg-white/[0.1] hover:ring-white/25" style="--reveal-delay:160ms">
                            <span class="absolute left-0 top-8 bottom-8 w-1 rounded-full bg-gradient-to-b from-brand-300 to-emerald-400"></span>
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 size-12 rounded-2xl bg-gradient-to-br from-brand-400 to-emerald-500 text-white flex items-center justify-center shadow-lg shadow-brand-900/50 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
                                    <x-icons.visi class="size-6" />
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-300 mb-2">{{ __('Visi') }}</h3>
                                    <div class="text-lg sm:text-xl font-semibold text-white leading-snug line-clamp-4">Terwujudnya Kota Palu Mantap Berkelanjutan yang Akseleratif, Inovatif dan Kolaboratif</div>
                                </div>
                            </div>
                        </div>
                        <div class="reveal group relative overflow-hidden rounded-3xl bg-white/[0.07] backdrop-blur-md ring-1 ring-white/15 p-6 sm:p-8 transition-all duration-300 hover:bg-white/[0.1] hover:ring-white/25" style="--reveal-delay:240ms">
                            <span class="absolute left-0 top-8 bottom-8 w-1 rounded-full bg-gradient-to-b from-bay-300 to-bay-500"></span>
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 size-12 rounded-2xl bg-gradient-to-br from-bay-400 to-bay-600 text-white flex items-center justify-center shadow-lg shadow-bay-900/50 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
                                    <x-icons.misi class="size-6" />
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-xs font-semibold uppercase tracking-[0.14em] text-bay-300 mb-2">{{ __('Misi') }}</h3>
                                    <div class="text-sm sm:text-base text-brand-50/85 leading-relaxed line-clamp-5">Meningkatkan akselerasi pengelolaan lingkungan dan penataan kota yang layak huni. Bersinergi dengan Rencana Strategis Kementerian Lingkungan Hidup dan Kehutanan tahun 2025 - 2029. Rencana Strategis Dinas Lingkungan Hidup Pemerintah Provinsi Sulawesi Tengah Tahun 2025-2029.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- FAQ — akordeon pertanyaan umum (Alpine)                    --}}
        {{-- ========================================================= --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal text-center max-w-2xl mx-auto mb-10">
                <span class="inline-block text-xs font-semibold uppercase tracking-[0.14em] text-bay-600 dark:text-bay-400 mb-3">{{ __('Pertanyaan Umum') }}</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">{{ __('Hal yang Sering Ditanyakan') }}</h2>
                <p class="mt-3 text-base text-slate-500 dark:text-slate-400">{{ __('Belum menemukan jawaban? Hubungi call center kami di bagian bawah halaman.') }}</p>
            </div>

            @php
            $faqs = [
                ['q' => __('Apakah saya perlu mendaftar akun untuk melapor?'), 'a' => __('Tidak. Seluruh layanan pengaduan dan permohonan dapat diakses tanpa registrasi akun. Cukup isi formulir, dan Anda akan mendapatkan nomor tiket untuk memantau status.')],
                ['q' => __('Bagaimana cara melacak status laporan saya?'), 'a' => __('Simpan nomor tiket yang muncul setelah Anda mengirim laporan, lalu buka menu “Lacak Pelaporan” atau halaman Cek Status pada bidang terkait, dan masukkan nomor tiket tersebut.')],
                ['q' => __('Apakah laporan saya bersifat rahasia?'), 'a' => __('Ya. Anda dapat melapor tanpa membuka akun dan identitas Anda dijaga kerahasiaannya. Cukup simpan nomor tiket untuk memantau status penanganan.')],
                ['q' => __('Berapa lama laporan saya akan ditindaklanjuti?'), 'a' => __('Laporan mendesak diupayakan direspons dalam 24 jam. Waktu penanganan akhir menyesuaikan jenis aduan dan tingkat kompleksitas di lapangan, dan dapat Anda pantau melalui nomor tiket.')],
                ['q' => __('Apa saja yang perlu saya siapkan saat melapor?'), 'a' => __('Sebaiknya siapkan deskripsi singkat kejadian, titik lokasi, serta foto atau dokumen pendukung agar petugas dapat memverifikasi dan menindaklanjuti dengan lebih cepat.')],
            ];
            @endphp

            <div class="space-y-3" x-data="{ open: 0 }">
                @foreach ($faqs as $i => $faq)
                <div class="reveal rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden transition-colors"
                     :class="open === {{ $i }} ? 'ring-1 ring-brand-500/30 shadow-[0_12px_40px_-16px_rgba(5,150,105,0.35)]' : ''"
                     style="--reveal-delay: {{ $i * 70 }}ms">
                    <button type="button" @click="open = (open === {{ $i }} ? null : {{ $i }})"
                            class="w-full flex items-center justify-between gap-4 p-5 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500/40 rounded-2xl"
                            :aria-expanded="open === {{ $i }}">
                        <span class="font-semibold text-slate-800 dark:text-slate-100 text-sm sm:text-base">{{ $faq['q'] }}</span>
                        <span class="flex-shrink-0 size-8 rounded-full flex items-center justify-center transition-all duration-300"
                              :class="open === {{ $i }} ? 'bg-brand-600 text-white rotate-45' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                        </span>
                    </button>
                    <div x-cloak class="grid overflow-hidden transition-all duration-300 ease-out"
                         :class="open === {{ $i }} ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'">
                        <div class="min-h-0 overflow-hidden">
                            <p class="px-5 pb-5 text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- LOKASI & JAM LAYANAN — info faktual + peta lokasi kantor    --}}
        {{-- ========================================================= --}}
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal grid lg:grid-cols-2 gap-0 overflow-hidden rounded-[2rem] border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-[0_20px_60px_-28px_rgba(15,23,42,0.22)]">
                {{-- Info --}}
                <div class="p-7 sm:p-10 lg:p-12 flex flex-col justify-center">
                    <span class="inline-block text-xs font-semibold uppercase tracking-[0.14em] text-bay-600 dark:text-bay-400 mb-3">{{ __('Kunjungi Kami') }}</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">{{ __('Lokasi & Jam Layanan') }}</h2>
                    <p class="mt-3 text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('Datang langsung ke kantor kami pada jam kerja, atau akses seluruh layanan secara daring 24 jam.') }}</p>

                    <div class="mt-7 space-y-4">
                        <div class="flex items-start gap-3.5">
                            <span class="mt-0.5 flex-shrink-0 size-10 rounded-2xl bg-brand-50 dark:bg-brand-900/25 text-brand-600 dark:text-brand-300 flex items-center justify-center" style="--icon-accent: var(--color-brand-500)">
                                <x-icons.alamat class="size-5" />
                            </span>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-white text-sm">{{ __('Alamat Kantor') }}</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('Jl. Kakatua No. 09, Kelurahan Tanamodindi, Kecamatan Mantikulore, Kota Palu') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3.5">
                            <span class="mt-0.5 flex-shrink-0 size-10 rounded-2xl bg-bay-50 dark:bg-bay-900/25 text-bay-600 dark:text-bay-300 flex items-center justify-center" style="--icon-accent: var(--color-bay-500)">
                                <x-icons.jam-kerja class="size-5" />
                            </span>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-white text-sm">{{ __('Jam Kerja') }}</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('Senin – Kamis, 08.00 – 16.00 WITA') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3.5">
                            <span class="mt-0.5 flex-shrink-0 size-10 rounded-2xl bg-clay-50 dark:bg-clay-900/25 text-clay-600 dark:text-clay-300 flex items-center justify-center" style="--icon-accent: var(--color-clay-500)">
                                <x-icons.whatsapp class="size-5" />
                            </span>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-white text-sm">{{ __('Call Center / WhatsApp') }}</p>
                                <a href="https://wa.me/6285191512076" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-brand-600 dark:text-brand-400 hover:underline">0851-9151-2076</a>
                            </div>
                        </div>
                    </div>

                    <a href="https://www.google.com/maps/search/?api=1&query=Dinas+Lingkungan+Hidup+Kota+Palu" target="_blank" rel="noopener noreferrer"
                       class="group mt-8 inline-flex w-fit items-center gap-2 rounded-2xl bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-brand-600/25 transition-all duration-300 hover:-translate-y-0.5 hover:bg-brand-700">
                        <x-icons.alamat class="size-4" />
                        {{ __('Buka di Google Maps') }}
                    </a>
                </div>

                {{-- Peta --}}
                <div class="relative min-h-[320px] lg:min-h-full bg-slate-100 dark:bg-slate-800">
                    <iframe
                        title="Peta Lokasi DLH Kota Palu"
                        src="https://www.google.com/maps?q=Dinas%20Lingkungan%20Hidup%20Kota%20Palu&output=embed"
                        class="absolute inset-0 h-full w-full grayscale-[0.15] contrast-[1.05]"
                        style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                </div>
            </div>
        </section>

    </div>
</div>

{{-- Scroll-reveal + count-up: IntersectionObserver ringan (tanpa library baru).
     Progressive enhancement — bila JS mati atau reduced-motion aktif, konten tetap tampil. --}}
@push('scripts')
<script>
    (function () {
        var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function initMotion() {
        /* ---- Scroll reveal (bidirectional: masuk & keluar viewport) ---- */
        var els = document.querySelectorAll('.reveal');
        if (reduced || !('IntersectionObserver' in window)) {
            els.forEach(function (el) { el.classList.add('is-revealed'); });
        } else {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-revealed');
                    } else if (entry.boundingClientRect.top > 0) {
                        // Hanya sembunyikan lagi bila elemen keluar ke BAWAH viewport,
                        // agar animasi berulang saat scroll naik-turun tanpa "berkedip" di atas.
                        entry.target.classList.remove('is-revealed');
                    }
                });
            }, { rootMargin: '0px 0px -10% 0px', threshold: 0.12 });
            els.forEach(function (el) { observer.observe(el); });
        }

        /* ---- Count-up angka (statistik & capaian) ---- */
        var counters = document.querySelectorAll('[data-countup]');
        var fmt = function (n) { return new Intl.NumberFormat('id-ID').format(Math.round(n)); };

        if (reduced || !('IntersectionObserver' in window)) {
            counters.forEach(function (el) {
                el.textContent = fmt(parseFloat(el.getAttribute('data-count')) || 0) + (el.getAttribute('data-suffix') || '');
            });
        } else {
            var animate = function (el) {
                var target = parseFloat(el.getAttribute('data-count')) || 0;
                var suffix = el.getAttribute('data-suffix') || '';
                var dur = 1400, start = null;
                var step = function (ts) {
                    if (start === null) start = ts;
                    var p = Math.min((ts - start) / dur, 1);
                    var eased = 1 - Math.pow(1 - p, 3); // easeOutCubic
                    el.textContent = fmt(target * eased) + suffix;
                    if (p < 1) requestAnimationFrame(step);
                    else el.textContent = fmt(target) + suffix;
                };
                requestAnimationFrame(step);
            };
            var countObs = new IntersectionObserver(function (entries, obs) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) { animate(entry.target); obs.unobserve(entry.target); }
                });
            }, { threshold: 0.4 });
            counters.forEach(function (el) { countObs.observe(el); });
        }
        } /* /initMotion */

        // Mulai animasi SETELAH preloader terangkat, agar entrance hero & reveal
        // layar pertama tidak terbuang di balik layar loading.
        var started = false;
        function start() {
            if (started) return;
            started = true;
            document.documentElement.classList.add('dlh-ready');
            initMotion();
        }

        if (document.documentElement.classList.contains('dlh-ready') ||
            !document.getElementById('dlh-preloader')) {
            start();
        } else {
            window.addEventListener('dlh:ready', start, { once: true });
            // Failsafe bila event tak pernah terkirim (lebih lama dari durasi preloader 6 dtk).
            setTimeout(start, 7000);
        }
    })();
</script>
@endpush
@endsection
