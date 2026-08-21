@extends('layouts.app')

@section('title', 'UPTD TEMPAT PEMROSESAN AKHIR (TPA) KAWATUNA - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'UPTD TPA Kawatuna merupakan Tempat Pemrosesan Akhir Kota Palu yang terletak di Jalan Watunjamboko, Kelurahan Kawatuna, Kecamatan Mantikulore Kota Palu.')

@section('content')
@php
    $img1 = 'assets/images/UPTD-Kawatuna.jpg';
    $img2 = 'assets/images/UPTD-Kawatuna2.jpg';
    $hasImg1 = file_exists(public_path($img1));
    $hasImg2 = file_exists(public_path($img2));

    $innovations = [
        [
            'title' => 'Sanitary Landfill',
            'desc' => 'Mengubah sistem pembuangan terbuka (open dumping) lama menjadi penimbunan dan pemadatan berlapis tanah yang lebih bersih serta aman bagi lingkungan.',
            'icon' => 'sampah',
            'tone' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:ring-emerald-800',
            'stripe' => 'from-emerald-500 via-teal-500 to-emerald-600',
        ],
        [
            'title' => 'Pemanfaatan Gas Metana',
            'desc' => 'Mengolah gas dari tumpukan sampah untuk dijadikan sumber energi atau instalasi gas.',
            'icon' => 'pengendalian',
            'tone' => 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:ring-amber-800',
            'stripe' => 'from-amber-500 via-orange-500 to-amber-600',
        ],
        [
            'title' => 'Budidaya Maggot',
            'desc' => 'Mengurai sampah organik dengan cepat menggunakan larva maggot yang kemudian dimanfaatkan kembali sebagai pakan ikan bernutrisi tinggi.',
            'icon' => 'terintegrasi',
            'tone' => 'bg-teal-50 text-teal-700 ring-teal-200 dark:bg-teal-950/60 dark:text-teal-300 dark:ring-teal-800',
            'stripe' => 'from-teal-500 via-cyan-500 to-teal-600',
        ],
        [
            'title' => 'Aksi Lingkungan Hijau',
            'desc' => 'Melakukan penanaman ribuan pohon dan penataan sabuk hijau (greenbelt) secara berkala.',
            'icon' => 'rth',
            'tone' => 'bg-lime-50 text-lime-700 ring-lime-200 dark:bg-lime-950/60 dark:text-lime-300 dark:ring-lime-800',
            'stripe' => 'from-lime-500 via-emerald-500 to-teal-600',
        ],
    ];
@endphp

<div
    x-data="{
        lightboxOpen: false,
        activeImage: '',
        activeCaption: '',
        openLightbox(img, caption) {
            this.activeImage = img;
            this.activeCaption = caption;
            this.lightboxOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        closeLightbox() {
            this.lightboxOpen = false;
            document.body.classList.remove('overflow-hidden');
        }
    }"
    @keydown.escape.window="closeLightbox()"
    class="space-y-16 pb-20 sm:space-y-20 sm:pb-28 lg:space-y-24"
>

    {{-- ── 1. Page Hero ── --}}
    <x-public.page-hero
        badge="{{ __('UPTD TPA KAWATUNA') }}"
        icon="recycle"
        title="{{ __('UPTD TEMPAT PEMROSESAN AKHIR (TPA) KAWATUNA') }}"
        description="{{ __('Jalan Watunjamboko, Kelurahan Kawatuna, Kecamatan Mantikulore, Kota Palu') }}"
    />

    {{-- ── 2. Galeri Foto Tampak Udara ── --}}
    @if ($hasImg1 || $hasImg2)
        <section aria-label="{{ __('Galeri Foto UPTD TPA Kawatuna') }}" class="reveal">
            <div class="grid grid-cols-1 gap-6 sm:gap-8 md:grid-cols-2">
                @if ($hasImg1)
                    <div
                        @click="openLightbox('{{ asset($img1) }}', 'UPTD TEMPAT PEMROSESAN AKHIR (TPA) KAWATUNA - Foto Udara 1')"
                        class="group relative aspect-[16/10] cursor-pointer overflow-hidden rounded-3xl border border-slate-200/90 bg-slate-100 shadow-md transition-all duration-500 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-800"
                    >
                        <img
                            src="{{ asset($img1) }}"
                            alt="UPTD TPA Kawatuna tampak udara 1"
                            loading="lazy"
                            class="size-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                        >
                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent"></div>

                        <div class="absolute inset-x-0 bottom-0 p-5 sm:p-6">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-950/70 px-3 py-1 text-xs font-semibold text-white backdrop-blur-md">
                                <x-icons.ui name="image" class="size-3.5 text-emerald-400" />
                                {{ __('UPTD TPA Kawatuna, Kota Palu') }}
                            </span>
                        </div>

                        <div class="absolute right-4 top-4 rounded-xl bg-slate-950/60 p-2.5 text-white backdrop-blur-md transition-transform duration-300 group-hover:scale-110">
                            <x-icons.ui name="eye" class="size-4" />
                        </div>
                    </div>
                @endif

                @if ($hasImg2)
                    <div
                        @click="openLightbox('{{ asset($img2) }}', 'UPTD TEMPAT PEMROSESAN AKHIR (TPA) KAWATUNA - Foto Udara 2')"
                        class="group relative aspect-[16/10] cursor-pointer overflow-hidden rounded-3xl border border-slate-200/90 bg-slate-100 shadow-md transition-all duration-500 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-800"
                    >
                        <img
                            src="{{ asset($img2) }}"
                            alt="UPTD TPA Kawatuna tampak udara 2"
                            loading="lazy"
                            class="size-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                        >
                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent"></div>

                        <div class="absolute inset-x-0 bottom-0 p-5 sm:p-6">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-950/70 px-3 py-1 text-xs font-semibold text-white backdrop-blur-md">
                                <x-icons.ui name="image" class="size-3.5 text-emerald-400" />
                                {{ __('Kawasan TPA Kawatuna, Kecamatan Mantikulore') }}
                            </span>
                        </div>

                        <div class="absolute right-4 top-4 rounded-xl bg-slate-950/60 p-2.5 text-white backdrop-blur-md transition-transform duration-300 group-hover:scale-110">
                            <x-icons.ui name="eye" class="size-4" />
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- ── 3. Deskripsi Utama ── --}}
    <section aria-labelledby="desc-heading" class="reveal">
        <div class="relative overflow-hidden rounded-3xl border border-slate-200/90 bg-white p-8 shadow-sm transition-all duration-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 sm:p-10 lg:p-12">
            <div class="flex items-center gap-4 border-b border-slate-100 pb-6 dark:border-slate-800/80">
                <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-200/70 dark:bg-brand-950/60 dark:text-brand-300 dark:ring-brand-800">
                    <x-icons.ui name="building" class="size-6" />
                </span>
                <div>
                    <h2 id="desc-heading" class="text-xl font-bold text-slate-900 dark:text-white sm:text-2xl">
                        {{ __('Tentang UPTD TPA Kawatuna') }}
                    </h2>
                    <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                        {{ __('Tempat Pemrosesan Akhir Kota Palu') }}
                    </p>
                </div>
            </div>

            <div class="mt-6">
                <p class="text-base leading-relaxed text-slate-700 dark:text-slate-300 sm:text-lg sm:leading-9">
                    UPTD TPA Kawatuna merupakan Tempat Pemrosesan Akhir Kota Palu yang terletak di Jalan Watunjamboko, Kelurahan Kawatuna, Kecamatan Mantikulore Kota Palu dan kini bertransformasi menjadi kawasan ramah lingkungan dengan penerapan teknologi modern serta sistem pengolahan yang berkelanjutan.
                </p>
            </div>
        </div>
    </section>

    {{-- ── 4. Sistem dan Inovasi Pengelolaan ── --}}
    <section aria-labelledby="innov-heading" class="reveal space-y-8">
        <div class="flex items-center gap-3.5">
            <span class="inline-flex size-11 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-200/70 dark:bg-brand-950/60 dark:text-brand-300 dark:ring-brand-800">
                <x-icons.ui name="tool" class="size-5.5" />
            </span>
            <h2 id="innov-heading" class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-3xl">
                {{ __('Sistem dan Inovasi Pengelolaan') }}
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:gap-8 md:grid-cols-2">
            @foreach ($innovations as $item)
                <div class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200/90 bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900 sm:p-8 dark:hover:border-brand-700">
                    {{-- Aksen Garis Atas --}}
                    <span class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r {{ $item['stripe'] }}"></span>

                    <div>
                        <div class="flex items-center gap-3.5">
                            <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-2xl ring-1 transition-transform duration-300 group-hover:scale-110 {{ $item['tone'] }}">
                                <x-icons.ui :name="$item['icon']" class="size-6" />
                            </span>
                            <h3 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white sm:text-xl">
                                {{ $item['title'] }}
                            </h3>
                        </div>

                        <p class="mt-4 text-sm leading-relaxed text-slate-600 dark:text-slate-300 sm:text-base sm:leading-7">
                            {{ $item['desc'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ── 5. Lightbox Modal Preview ── --}}
    <div
        x-show="lightboxOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 p-4 backdrop-blur-md"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('Pratinjau Foto TPA Kawatuna') }}"
    >
        <div
            @click.away="closeLightbox()"
            class="relative max-h-[90vh] max-w-5xl overflow-hidden rounded-3xl border border-white/15 bg-slate-900/90 shadow-2xl"
        >
            <button
                type="button"
                @click="closeLightbox()"
                class="absolute right-4 top-4 z-10 inline-flex size-10 items-center justify-center rounded-full bg-slate-950/70 text-white transition-transform duration-200 hover:scale-110 hover:bg-slate-950 focus:outline-none focus:ring-2 focus:ring-white"
                aria-label="{{ __('Tutup pratinjau') }}"
            >
                <x-icons.ui name="close" class="size-5" />
            </button>

            <div class="relative overflow-hidden">
                <img
                    :src="activeImage"
                    :alt="activeCaption"
                    class="max-h-[75vh] w-full object-contain"
                >
            </div>

            <div class="border-t border-white/10 bg-slate-950/90 px-6 py-4">
                <p class="text-sm font-semibold text-white sm:text-base" x-text="activeCaption"></p>
                <p class="mt-1 text-xs text-slate-400">{{ __('UPTD TPA Kawatuna • Dinas Lingkungan Hidup Kota Palu') }}</p>
            </div>
        </div>
    </div>

</div>
@endsection
