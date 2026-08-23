@extends('layouts.app')

@section('title', 'Tentang Kami - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Mengenal lebih dekat Dinas Lingkungan Hidup Kota Palu: sejarah, program unggulan, dan komitmen kami dalam menjaga kelestarian lingkungan.')

@section('content')
@php
    $profilCards = [
        [
            'title' => __('Sejarah Singkat'),
            'icon' => 'terintegrasi',
            'tone' => 'bg-brand-50 text-brand-700 ring-brand-100 dark:bg-brand-900/30 dark:text-brand-300 dark:ring-brand-800/70',
            'accent' => 'var(--color-brand-500)',
            'copy' => [
                __('Dinas Lingkungan Hidup (DLH) Kota Palu merupakan organisasi perangkat daerah yang berada di bawah Pemerintah Kota Palu, Provinsi Sulawesi Tengah. DLH Palu bertanggung jawab langsung dalam pengelolaan dan perlindungan lingkungan hidup di wilayah Kota Palu.'),
                __('Sebagai ibu kota Provinsi Sulawesi Tengah, Kota Palu memiliki topografi yang unik dengan kombinasi pegunungan, teluk, dan dataran rendah. DLH Palu hadir untuk menjaga keseimbangan antara pembangunan kota dengan kelestarian alam, memastikan bahwa pertumbuhan ekonomi berjalan beriringan dengan pelestarian lingkungan.'),
            ],
        ],
        [
            'title' => __('Wilayah Kerja'),
            'icon' => 'rth-ha',
            'tone' => 'bg-bay-50 text-bay-700 ring-bay-100 dark:bg-bay-900/30 dark:text-bay-300 dark:ring-bay-800/70',
            'accent' => 'var(--color-bay-500)',
            'copy' => [
                __('DLH Kota Palu membawahi pengelolaan lingkungan hidup di seluruh wilayah Kota Palu yang terdiri dari 8 kecamatan. Dengan luas wilayah sekitar 395,06 km persejiang, DLH Palu mengelola berbagai aspek lingkungan mulai dari pengelolaan sampah, penghijauan, pengawasan limbah industri, hingga mitigasi bencana lingkungan.'),
                __('Kota Palu terletak di garis khatulistiwa dengan curah hujan yang fluktuatif dan suhu udara yang tinggi, menjadikan pengelolaan lingkungan sebagai prioritas utama untuk kenyamanan dan kesehatan masyarakat.'),
            ],
        ],
    ];

    $programUnggulan = [
        [
            'title' => __('Bank Sampah'),
            'description' => __('Program pemberdayaan masyarakat dalam pemilahan dan daur ulang sampah dari sumbernya, tersebar di berbagai kecamatan di Kota Palu.'),
            'icon' => 'sampah',
            'tone' => 'bg-brand-50 text-brand-700 ring-brand-100 dark:bg-brand-900/30 dark:text-brand-300 dark:ring-brand-800/70',
            'accent' => 'var(--color-brand-500)',
        ],
        [
            'title' => __('Palu Hijau'),
            'description' => __('Gerakan penghijauan masif yang menargetkan kawasan perkotaan, taman publik, dan jalur hijau di sepanjang jalan utama Kota Palu.'),
            'icon' => 'rth',
            'tone' => 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-800/70',
            'accent' => '#10b981',
        ],
        [
            'title' => __('Mitigasi Bencana'),
            'description' => __('Pemulihan area hijau dan penanaman mangrove di pesisir Teluk Palu untuk mitigasi abrasi pascabencana 2018.'),
            'icon' => 'pengendalian',
            'tone' => 'bg-sky-50 text-sky-700 ring-sky-100 dark:bg-sky-900/30 dark:text-sky-300 dark:ring-sky-800/70',
            'accent' => '#0ea5e9',
        ],
        [
            'title' => __('Eco School'),
            'description' => __('Program edukasi lingkungan di sekolah-sekolah untuk menanamkan kesadaran dini tentang pemilahan sampah dan pelestarian alam.'),
            'icon' => 'misi',
            'tone' => 'bg-amber-50 text-amber-700 ring-amber-100 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-800/70',
            'accent' => '#f59e0b',
        ],
        [
            'title' => __('Pengawasan Limbah'),
            'description' => __('Inspeksi rutin ke perusahaan, rumah makan, dan bengkel untuk memastikan pengelolaan limbah sesuai regulasi lingkungan.'),
            'icon' => 'pantau-status',
            'tone' => 'bg-rose-50 text-rose-700 ring-rose-100 dark:bg-rose-900/30 dark:text-rose-300 dark:ring-rose-800/70',
            'accent' => '#f43f5e',
        ],
        [
            'title' => __('Kampanye Lingkungan'),
            'description' => __('Kolaborasi dengan komunitas lokal seperti Palu Bersih dan Sahabat Hijau dalam kegiatan bersih pantai dan sungai.'),
            'icon' => 'sapa',
            'tone' => 'bg-violet-50 text-violet-700 ring-violet-100 dark:bg-violet-900/30 dark:text-violet-300 dark:ring-violet-800/70',
            'accent' => '#8b5cf6',
        ],
    ];
@endphp

<div id="tentang-kami" class="space-y-14 pb-6 sm:space-y-20 sm:pb-12">
    {{-- Hero: sebuah lanskap digital untuk mandat lingkungan Kota Palu. --}}
    <section aria-labelledby="tentang-title" class="relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-br from-brand-950 via-brand-900 to-bay-950 px-6 py-10 text-white shadow-[0_28px_80px_-32px_rgba(4,120,87,0.75)] ring-1 ring-white/10 sm:rounded-[2.5rem] sm:px-10 sm:py-14 lg:px-14 lg:py-16">
        <div class="pointer-events-none absolute inset-0 opacity-70" aria-hidden="true" style="background-image:radial-gradient(circle at 82% 18%, rgba(110,231,183,.18), transparent 23rem), radial-gradient(circle at 18% 100%, rgba(56,189,248,.16), transparent 24rem), linear-gradient(135deg, rgba(255,255,255,.05) 1px, transparent 1px); background-size:auto, auto, 30px 30px"></div>
        <div class="pointer-events-none absolute -right-24 top-8 size-72 rounded-full border border-white/10 sm:size-96" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-8 top-24 size-44 rounded-full border border-white/10 sm:size-64" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-28 -left-14 size-72 rounded-full bg-brand-400/10 blur-3xl" aria-hidden="true"></div>

        <div class="relative grid items-center gap-10 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-7 xl:col-span-8">
                <div class="page-hero-enter inline-flex items-center gap-2.5 rounded-full bg-white/[0.09] px-3.5 py-2 text-xs font-bold uppercase tracking-[0.16em] text-brand-100 ring-1 ring-inset ring-white/15 backdrop-blur-md" style="--hero-delay: 40ms">
                    <span class="flex size-6 items-center justify-center rounded-lg bg-brand-400/20 text-brand-100" style="--icon-accent: #6ee7b7">
                        <x-icons.terintegrasi class="size-4" />
                    </span>
                    {{ __('Tentang Kami') }}
                </div>

                <h1 id="tentang-title" class="page-hero-enter mt-5 max-w-3xl text-4xl font-bold tracking-[-0.045em] text-white sm:text-5xl lg:text-6xl" style="--hero-delay: 120ms">
                    {{ __('Dinas Lingkungan Hidup') }}
                    <span class="block bg-gradient-to-r from-emerald-200 via-brand-200 to-bay-200 bg-clip-text text-transparent">{{ __('Kota Palu') }}</span>
                </h1>

                <p class="page-hero-enter mt-5 max-w-2xl text-base leading-8 text-brand-50/80 sm:text-lg" style="--hero-delay: 200ms">
                    {{ __('Garda terdepan pelindung lingkungan di jantung Sulawesi Tengah. Kami berkomitmen mewujudkan Kota Palu yang bersih, hijau, dan berkelanjutan.') }}
                </p>

                <div class="page-hero-enter mt-7 inline-flex items-center gap-3 text-sm font-medium text-white/70" style="--hero-delay: 280ms">
                    <span class="size-2 rounded-full bg-emerald-300 shadow-[0_0_0_6px_rgba(110,231,183,.12)]" aria-hidden="true"></span>
                    <span>{{ __('Kota Palu, Sulawesi Tengah') }}</span>
                </div>
            </div>

            <div class="page-hero-enter relative mx-auto hidden w-full max-w-sm lg:col-span-5 lg:block xl:col-span-4" style="--hero-delay: 180ms" aria-hidden="true">
                <div class="relative aspect-square overflow-hidden rounded-[2rem] border border-white/15 bg-white/[0.07] shadow-2xl shadow-brand-950/50 backdrop-blur-md">
                    <div class="absolute inset-[12%] rounded-full border border-dashed border-white/20"></div>
                    <div class="absolute inset-[25%] rounded-full border border-white/15 bg-brand-400/[0.06]"></div>
                    <div class="absolute left-1/2 top-1/2 flex size-20 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-3xl bg-gradient-to-br from-emerald-300 to-brand-400 text-brand-950 shadow-xl shadow-brand-950/40" style="--icon-accent: rgba(255,255,255,.55)">
                        <x-icons.rth class="size-10" />
                    </div>
                    <span class="absolute left-[14%] top-[20%] flex size-12 items-center justify-center rounded-2xl bg-white/10 text-brand-100 ring-1 ring-white/15" style="--icon-accent: #6ee7b7"><x-icons.sampah class="size-6" /></span>
                    <span class="absolute bottom-[18%] right-[14%] flex size-12 items-center justify-center rounded-2xl bg-white/10 text-bay-100 ring-1 ring-white/15" style="--icon-accent: #7dd3fc"><x-icons.pengendalian class="size-6" /></span>
                    <span class="absolute bottom-[14%] left-[22%] size-2.5 rounded-full bg-emerald-300 shadow-[0_0_0_7px_rgba(110,231,183,.12)]"></span>
                    <span class="absolute right-[19%] top-[27%] size-2 rounded-full bg-bay-200 shadow-[0_0_0_6px_rgba(125,211,252,.10)]"></span>
                </div>
            </div>
        </div>
    </section>

    {{-- Sambutan Kepala Dinas --}}
    <section aria-labelledby="sambutan-title" class="reveal relative overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white p-6 shadow-[0_18px_55px_-30px_rgba(15,23,42,.3)] dark:border-slate-800 dark:bg-slate-900 sm:p-9 lg:p-12">
        <div class="pointer-events-none absolute right-0 top-0 size-72 translate-x-1/3 -translate-y-1/3 rounded-full bg-brand-100/70 blur-3xl dark:bg-brand-900/20" aria-hidden="true"></div>
        <div class="pointer-events-none absolute bottom-0 left-0 h-40 w-full bg-gradient-to-t from-bay-50/60 to-transparent dark:from-bay-950/10" aria-hidden="true"></div>

        <div class="relative grid items-center gap-10 lg:grid-cols-12 lg:gap-14">
            <figure class="reveal-left lg:col-span-4" style="--reveal-delay: 70ms">
                <div class="relative mx-auto max-w-sm">
                    <div class="absolute -inset-3 rounded-[2rem] bg-gradient-to-br from-brand-200/60 via-transparent to-bay-200/60 blur-sm dark:from-brand-700/20 dark:to-bay-700/20" aria-hidden="true"></div>
                    <div class="relative overflow-hidden rounded-[1.7rem] border-4 border-white bg-slate-100 shadow-[0_24px_50px_-18px_rgba(15,23,42,.45)] dark:border-slate-800 dark:bg-slate-800">
                        <img class="aspect-[3/4] w-full object-cover transition-transform duration-700 ease-out hover:scale-[1.03]" src="{{ asset('assets/images/foto_kadis.webp') }}" alt="{{ __('Foto Kepala Dinas') }}" decoding="async">
                        <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950 via-slate-950/85 to-transparent px-5 pb-5 pt-14 text-center sm:px-6 sm:pb-6">
                            <p class="text-lg font-bold tracking-tight text-white">Mohamad Arif, S.STP., M.Si</p>
                            <p class="mt-1 text-sm font-medium text-brand-200">{{ __('Kepala Dinas Lingkungan Hidup Kota Palu') }}</p>
                        </figcaption>
                    </div>
                </div>
            </figure>

            <div class="reveal-right lg:col-span-8" style="--reveal-delay: 130ms">
                <div class="flex items-center gap-3">
                    <span class="flex size-11 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-100 dark:bg-brand-900/30 dark:text-brand-300 dark:ring-brand-800/70" style="--icon-accent: var(--color-brand-500)">
                        <x-icons.sapa class="size-6" />
                    </span>
                    <span class="text-xs font-bold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('Sambutan') }}</span>
                </div>
                <h2 id="sambutan-title" class="mt-5 max-w-xl text-3xl font-bold tracking-[-0.035em] text-slate-900 dark:text-white sm:text-4xl">{{ __('Sambutan Kepala Dinas') }}</h2>

                <blockquote class="mt-7 border-l-2 border-brand-400 pl-5 text-base leading-8 text-slate-600 dark:border-brand-500 dark:text-slate-300 sm:pl-6 sm:text-lg">
                    <p>{{ __('Puji syukur ke hadirat Tuhan Yang Maha Esa. Selamat datang di portal resmi Sistem Layanan Informasi Publik (SILP) Dinas Lingkungan Hidup Kota Palu. Di era digital ini, kami berkomitmen untuk terus berinovasi memberikan pelayanan yang cepat, transparan, dan responsif.') }}</p>
                    <p class="mt-5">{{ __('Sistem ini kami hadirkan agar masyarakat Kota Palu dapat berpartisipasi aktif dalam menjaga keasrian, kebersihan, dan keamanan lingkungan kota kita tercinta. Mari bersama-sama wujudkan Palu yang bersih, hijau, dan nyaman.') }}</p>
                </blockquote>
            </div>
        </div>
    </section>

    {{-- Sejarah dan wilayah kerja --}}
    <section aria-label="{{ __('Profil Dinas Lingkungan Hidup Kota Palu') }}">
        <div class="reveal max-w-2xl" style="--reveal-delay: 30ms">
            <span class="text-xs font-bold uppercase tracking-[0.16em] text-bay-700 dark:text-bay-300">{{ __('Profil Singkat') }}</span>
            <h2 class="mt-3 text-3xl font-bold tracking-[-0.035em] text-slate-900 dark:text-white sm:text-4xl">{{ __('Mengenal DLH Kota Palu') }}</h2>
        </div>

        <div class="mt-8 grid gap-5 lg:grid-cols-2 lg:gap-7">
            @foreach ($profilCards as $index => $card)
                <article class="reveal group relative overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white p-6 shadow-[0_14px_36px_-26px_rgba(15,23,42,.32)] transition-[border-color,box-shadow,transform] duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-[0_25px_50px_-28px_rgba(15,23,42,.35)] dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800 sm:p-8" style="--reveal-delay: {{ 80 + ($index * 100) }}ms">
                    <div class="absolute right-0 top-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-gradient-to-br from-brand-100/70 to-bay-100/50 transition-transform duration-500 group-hover:scale-125 dark:from-brand-900/20 dark:to-bay-900/20" aria-hidden="true"></div>
                    <div class="relative">
                        <span class="flex size-12 items-center justify-center rounded-2xl ring-1 {{ $card['tone'] }}" style="--icon-accent: {{ $card['accent'] }}">
                            <x-dynamic-component :component="'icons.' . $card['icon']" class="size-6" />
                        </span>
                        <h3 class="mt-6 text-2xl font-bold tracking-[-0.025em] text-slate-900 dark:text-white">{{ $card['title'] }}</h3>
                        <div class="mt-5 space-y-4 text-sm leading-7 text-slate-600 dark:text-slate-400 sm:text-base">
                            @foreach ($card['copy'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Program Unggulan --}}
    <section aria-labelledby="program-title">
        <div class="reveal mx-auto max-w-2xl text-center" style="--reveal-delay: 30ms">
            <span class="text-xs font-bold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('Program Unggulan') }}</span>
            <h2 id="program-title" class="mt-3 text-3xl font-bold tracking-[-0.035em] text-slate-900 dark:text-white sm:text-4xl">{{ __('Inovasi Lingkungan DLH Palu') }}</h2>
            <p class="mt-4 text-base leading-7 text-slate-600 dark:text-slate-400">{{ __('Berbagai program strategis yang kami jalankan untuk menjaga kelestarian lingkungan di Kota Palu.') }}</p>
        </div>

        <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 sm:gap-5">
            @foreach ($programUnggulan as $index => $program)
                <article class="reveal group relative overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white p-6 shadow-[0_12px_32px_-25px_rgba(15,23,42,.3)] transition-[border-color,box-shadow,transform] duration-300 hover:-translate-y-1.5 hover:border-brand-200 hover:shadow-[0_24px_48px_-26px_rgba(15,23,42,.38)] dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800" style="--reveal-delay: {{ ($index % 3) * 90 }}ms">
                    <div class="absolute inset-x-0 top-0 h-1 origin-left scale-x-0 bg-gradient-to-r from-brand-500 via-emerald-400 to-bay-400 transition-transform duration-500 group-hover:scale-x-100" aria-hidden="true"></div>
                    <span class="flex size-12 items-center justify-center rounded-2xl ring-1 transition-transform duration-300 group-hover:-rotate-3 group-hover:scale-110 {{ $program['tone'] }}" style="--icon-accent: {{ $program['accent'] }}">
                        <x-dynamic-component :component="'icons.' . $program['icon']" class="size-6" />
                    </span>
                    <h3 class="mt-5 text-lg font-bold tracking-[-0.02em] text-slate-900 dark:text-white">{{ $program['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $program['description'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Hubungi Kami: memakai set ikon kustom yang sama dengan beranda. --}}
    <section aria-labelledby="hubungi-title" class="reveal relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-brand-950 via-brand-900 to-bay-950 shadow-[0_26px_60px_-34px_rgba(4,120,87,.72)] ring-1 ring-white/10 sm:rounded-[2rem]">
        <div class="pointer-events-none absolute inset-0 opacity-50" aria-hidden="true" style="background-image:radial-gradient(circle at 8% 0%, rgba(110,231,183,.18), transparent 21rem), radial-gradient(circle at 96% 100%, rgba(56,189,248,.16), transparent 22rem)"></div>
        <div class="relative grid lg:grid-cols-[minmax(0,.94fr)_minmax(0,1.06fr)]">
            <div class="p-5 sm:p-7 lg:p-8">
                <div class="reveal" style="--reveal-delay: 80ms">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/[0.09] px-3 py-1 text-[11px] font-bold uppercase tracking-[0.12em] text-brand-100 ring-1 ring-white/15">
                        <span class="size-1.5 rounded-full bg-emerald-300" aria-hidden="true"></span>
                        {{ __('Layanan dan Informasi') }}
                    </span>
                    <h2 id="hubungi-title" class="mt-3 text-2xl font-bold tracking-[-0.03em] text-white sm:text-3xl">{{ __('Hubungi Kami') }}</h2>
                </div>

                <div class="mt-5 space-y-2.5">
                    <article class="reveal group flex gap-3 rounded-xl bg-white/[0.06] p-3.5 ring-1 ring-white/10 transition-[background-color,box-shadow] duration-300 hover:bg-white/[0.1] hover:ring-white/20" style="--reveal-delay: 120ms">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-400/15 text-brand-100 ring-1 ring-brand-200/20 transition-transform duration-300 group-hover:scale-105 group-hover:-rotate-3" style="--icon-accent: #6ee7b7">
                            <x-icons.alamat class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-[15px] font-bold text-white">{{ __('Alamat Kantor') }}</h3>
                            <p class="mt-0.5 text-[13px] leading-5 text-brand-50/70">{{ __('Jl. Kakatua No. 09, Kelurahan Tanamodindi, Kecamatan Mantikulore, Kota Palu') }}</p>
                        </div>
                    </article>

                    <article class="reveal group flex gap-3 rounded-xl bg-white/[0.06] p-3.5 ring-1 ring-white/10 transition-[background-color,box-shadow] duration-300 hover:bg-white/[0.1] hover:ring-white/20" style="--reveal-delay: 170ms">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-bay-400/15 text-bay-100 ring-1 ring-bay-200/20 transition-transform duration-300 group-hover:scale-105 group-hover:-rotate-3" style="--icon-accent: #7dd3fc">
                            <x-icons.jam-kerja class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-[15px] font-bold text-white">{{ __('Jam Pelayanan') }}</h3>
                            <p class="mt-0.5 text-[13px] leading-5 text-brand-50/70">{{ __('Senin - Kamis (08.00 - 16.00 WITA)') }}</p>
                        </div>
                    </article>

                    <article class="reveal group flex gap-3 rounded-xl bg-white/[0.06] p-3.5 ring-1 ring-white/10 transition-[background-color,box-shadow] duration-300 hover:bg-white/[0.1] hover:ring-white/20" style="--reveal-delay: 220ms">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-400/15 text-emerald-100 ring-1 ring-emerald-200/20 transition-transform duration-300 group-hover:scale-105 group-hover:-rotate-3" style="--icon-accent: #6ee7b7">
                            <x-icons.whatsapp class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-[15px] font-bold text-white">{{ __('Call Center / WhatsApp') }}</h3>
                            <p class="mt-0.5"><a href="https://wa.me/6285191512076" target="_blank" rel="noopener noreferrer" class="inline-flex rounded-lg text-[13px] font-bold text-emerald-200 underline-offset-4 transition hover:text-white hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-200">0851-9151-2076</a></p>
                        </div>
                    </article>

                    <article class="reveal flex gap-3 rounded-xl bg-white/[0.06] p-3.5 ring-1 ring-white/10" style="--reveal-delay: 270ms">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white/10 text-brand-100 ring-1 ring-white/15" style="--icon-accent: #6ee7b7">
                            <x-icons.terintegrasi class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-[15px] font-bold text-white">{{ __('Media Sosial') }}</h3>
                            <div class="mt-2.5 flex items-center gap-2">
                                <a href="https://www.instagram.com/dlhkotapalu" target="_blank" rel="noopener noreferrer" class="flex size-9 items-center justify-center rounded-lg bg-white/10 text-brand-50 ring-1 ring-white/10 transition-[background-color,color,transform] duration-300 hover:-translate-y-0.5 hover:bg-pink-600 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white" title="Instagram" aria-label="Instagram">
                                    <x-icons.social.instagram class="size-[1.125rem]" />
                                </a>
                                <a href="https://www.facebook.com/share/18qHSySQr4/?locale=id_ID" target="_blank" rel="noopener noreferrer" class="flex size-9 items-center justify-center rounded-lg bg-white/10 text-brand-50 ring-1 ring-white/10 transition-[background-color,color,transform] duration-300 hover:-translate-y-0.5 hover:bg-blue-600 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white" title="Facebook" aria-label="Facebook">
                                    <x-icons.social.facebook class="size-[1.125rem]" />
                                </a>
                                <a href="https://wa.me/6285191512076" target="_blank" rel="noopener noreferrer" class="flex size-9 items-center justify-center rounded-lg bg-white/10 text-brand-50 ring-1 ring-white/10 transition-[background-color,color,transform] duration-300 hover:-translate-y-0.5 hover:bg-brand-600 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white" title="WhatsApp" aria-label="WhatsApp">
                                    <x-icons.social.whatsapp class="size-[1.125rem]" />
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <div class="reveal-right relative min-h-[20rem] overflow-hidden bg-slate-200 dark:bg-slate-800 lg:min-h-full" style="--reveal-delay: 140ms">
                <iframe
                    src="https://maps.google.com/maps?q=Dinas%20Lingkungan%20Hidup%20Kota%20Palu&t=&z=15&ie=UTF8&iwloc=&output=embed"
                    title="{{ __('Peta lokasi Dinas Lingkungan Hidup Kota Palu') }}"
                    width="100%"
                    height="100%"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    class="absolute inset-0 h-full w-full contrast-[1.04] saturate-[.88] dark:brightness-75">
                </iframe>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        function initTentangMotion() {
            var section = document.getElementById('tentang-kami');
            if (!section) return;

            var elements = section.querySelectorAll('.reveal');
            var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (reduced || !('IntersectionObserver' in window)) {
                elements.forEach(function (element) { element.classList.add('is-revealed'); });
                return;
            }

            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                });
            }, { rootMargin: '0px 0px -8% 0px', threshold: 0.1 });

            elements.forEach(function (element) { observer.observe(element); });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTentangMotion, { once: true });
        } else {
            initTentangMotion();
        }
    }());
</script>
@endpush
