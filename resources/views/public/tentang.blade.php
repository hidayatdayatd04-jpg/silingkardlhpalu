@extends('layouts.app')

@section('title', 'Tentang Kami - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Ketahui visi, misi, struktur organisasi, dan komitmen Dinas Lingkungan Hidup Kota Palu dalam mewujudkan pembangunan berkelanjutan yang berwawasan lingkungan.')

@section('content')
<div class="space-y-16 pb-16">

    <section class="relative overflow-hidden before:absolute before:top-0 before:start-1/2 before:bg-no-repeat before:bg-top before:bg-cover before:size-full before:-z-[1] before:transform before:-translate-x-1/2 dark:before:bg-[url('https://preline.co/assets/svg/examples-dark/polygon-bg-element.svg')]">
        <div class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-10">
            <div class="mt-5 max-w-2xl text-center mx-auto">
                <h1 class="block font-bold text-slate-800 text-4xl md:text-5xl lg:text-6xl dark:text-slate-200">
                    {{ __('Tentang') }} <span class="bg-clip-text bg-gradient-to-tl from-brand-600 to-emerald-400 text-transparent">{{ __('Kami') }}</span>
                </h1>
                <p class="mt-4 text-lg text-slate-600 dark:text-slate-400">
                    {{ __('Mengenal lebih dekat Dinas Lingkungan Hidup Kota Palu, visi, misi, dan struktur organisasi kami.') }}
                </p>
            </div>
        </div>
    </section>

    <section class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white border border-slate-200 rounded-3xl p-8 sm:p-12 dark:bg-slate-900 dark:border-slate-700 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-brand-50 rounded-full blur-3xl -z-10 dark:bg-brand-900/20 opacity-50 translate-x-1/2 -translate-y-1/2"></div>
            
            <div class="grid lg:grid-cols-12 gap-10 lg:gap-16 items-center">
                <div class="lg:col-span-4 flex justify-center lg:justify-start">
                    <div class="relative rounded-2xl overflow-hidden group shadow-xl max-w-sm w-full border-4 border-white dark:border-slate-800">
                        <img class="w-full object-cover rounded-xl aspect-[3/4]" src="{{ asset('assets/images/foto_kadis.jpeg') }}" alt="{{ __('Foto Kepala Dinas') }}">
                        <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-slate-900/90 to-transparent p-6 text-center">
                            <p class="text-white font-bold text-xl">Mohamad Arif, S.STP., M.Si</p>
                            <p class="text-brand-300 text-sm font-medium mt-1">{{ __('Kepala Dinas Lingkungan Hidup Kota Palu') }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="lg:col-span-8 space-y-6">
                    <h2 class="text-3xl font-extrabold text-slate-800 dark:text-slate-200 relative inline-block">
                        {{ __('Sambutan Kepala Dinas') }}
                        <span class="absolute bottom-0 left-0 w-12 h-1.5 bg-brand-500 rounded-full"></span>
                    </h2>
                    <div class="prose prose-lg dark:prose-invert text-slate-600 dark:text-slate-400">
                        <p class="leading-relaxed relative">
                            <span class="absolute -top-4 -left-6 text-6xl text-slate-200 dark:text-slate-800 font-serif opacity-50">"</span>
                            {{ __('Puji syukur ke hadirat Tuhan Yang Maha Esa. Selamat datang di portal resmi Sistem Layanan Informasi Publik (SILP) Dinas Lingkungan Hidup Kota Palu. Di era digital ini, kami berkomitmen untuk terus berinovasi memberikan pelayanan yang cepat, transparan, dan responsif.') }}
                        </p>
                        <p class="leading-relaxed mt-4">
                            {{ __('Sistem ini kami hadirkan agar masyarakat Kota Palu dapat berpartisipasi aktif dalam menjaga keasrian, kebersihan, dan keamanan lingkungan kota kita tercinta. Mari bersama-sama wujudkan Palu yang bersih, hijau, dan nyaman.') }}
                            <span class="absolute -bottom-6 -right-2 text-6xl text-slate-200 dark:text-slate-800 font-serif opacity-50 rotate-180">"</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="visi-misi" class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-8">
            <div class="bg-brand-600 rounded-3xl p-8 sm:p-10 relative overflow-hidden shadow-lg group hover:shadow-brand-500/20 transition-all">
                <div class="absolute inset-0 bg-[url('https://preline.co/assets/svg/examples-dark/polygon-bg-element.svg')] opacity-10 mix-blend-overlay"></div>
                <div class="absolute -right-10 -bottom-10 opacity-20 text-white">
                    <svg class="size-64" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                </div>
                <div class="relative z-10 flex flex-col h-full justify-center">
                    <h2 class="text-3xl font-extrabold text-white mb-6 flex items-center gap-3">
                        <span class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </span>
                        {{ __('Visi Kami') }}
                    </h2>
                    <p class="text-xl sm:text-2xl font-bold text-white leading-relaxed">
                        {{ __('"Terwujudnya Kota Palu yang Bersih, Hijau, Berkelanjutan, dan Tangguh terhadap Bencana Lingkungan."') }}
                    </p>
                </div>
            </div>

            <!-- Misi -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-8 sm:p-10 shadow-sm">
                <h2 class="text-3xl font-extrabold text-slate-800 dark:text-slate-200 mb-6 flex items-center gap-3">
                    <span class="p-2 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-lg">
                        <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </span>
                    {{ __('Misi Kami') }}
                </h2>
                <ul class="space-y-5">
                    <li class="flex items-start gap-4">
                        <span class="flex-shrink-0 size-8 flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-sm">1</span>
                        <p class="text-slate-600 dark:text-slate-400 text-base sm:text-lg">{{ __('Meningkatkan kualitas pelayanan kebersihan dan pengelolaan sampah yang terintegrasi.') }}</p>
                    </li>
                    <li class="flex items-start gap-4">
                        <span class="flex-shrink-0 size-8 flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-sm">2</span>
                        <p class="text-slate-600 dark:text-slate-400 text-base sm:text-lg">{{ __('Mengoptimalkan pemeliharaan Ruang Terbuka Hijau (RTH) dan mitigasi risiko pohon pelindung.') }}</p>
                    </li>
                    <li class="flex items-start gap-4">
                        <span class="flex-shrink-0 size-8 flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-sm">3</span>
                        <p class="text-slate-600 dark:text-slate-400 text-base sm:text-lg">{{ __('Meningkatkan kesadaran dan partisipasi aktif masyarakat dalam pelestarian lingkungan hidup.') }}</p>
                    </li>
                    <li class="flex items-start gap-4">
                        <span class="flex-shrink-0 size-8 flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-sm">4</span>
                        <p class="text-slate-600 dark:text-slate-400 text-base sm:text-lg">{{ __('Mewujudkan tata kelola pemerintahan yang baik (Good Corporate Governance) berbasis teknologi informasi.') }}</p>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Tugas dan Fungsi Section -->
    <section id="tugas-fungsi" class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-8 sm:p-12 shadow-sm">
            <h2 class="text-3xl font-extrabold text-slate-800 dark:text-slate-200 mb-6 flex items-center gap-3">
                <span class="p-2 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-lg">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </span>
                {{ __('Tugas dan Fungsi Dinas Lingkungan Hidup') }}
            </h2>
            <div class="grid md:grid-cols-2 gap-8 text-slate-600 dark:text-slate-400">
                <div class="space-y-4">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-200">{{ __('Tugas Pokok') }}</h3>
                    <p class="leading-relaxed">
                        {{ __('Melaksanakan urusan pemerintahan daerah di bidang lingkungan hidup, persampahan, kebersihan, serta pengelolaan ruang terbuka hijau berdasarkan asas otonomi dan tugas pembantuan.') }}
                    </p>
                </div>
                <div class="space-y-4">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-200">{{ __('Fungsi Utama') }}</h3>
                    <ul class="list-disc pl-5 space-y-2">
                        <li>{{ __('Perumusan kebijakan teknis operasional di bidang perlindungan dan pengelolaan lingkungan hidup.') }}</li>
                        <li>{{ __('Pelaksanaan pengawasan, pengendalian, dan pemulihan kualitas lingkungan terhadap dampak pembangunan.') }}</li>
                        <li>{{ __('Penyelenggaraan pengelolaan persampahan, kebersihan wilayah, dan penataan pertamanan kota.') }}</li>
                        <li>{{ __('Pembinaan dan pemberdayaan peran serta masyarakat dalam pemeliharaan lingkungan hidup.') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="struktur-organisasi" class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-extrabold text-slate-800 dark:text-slate-200 mb-4">{{ __('Struktur Organisasi') }}</h2>
        <p class="text-slate-600 dark:text-slate-400 max-w-2xl mx-auto mb-10">
            {{ __('Bagan hierarki kepemimpinan dan pembagian bidang kerja di lingkungan DLH Kota Palu.') }}
        </p>
        
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 sm:p-8 rounded-3xl shadow-sm flex items-center justify-center min-h-[400px]">
            <img src="{{ asset('assets/images/struktur_organisasi.png') }}" alt="{{ __('Bagan Struktur Organisasi DLH Kota Palu') }}" loading="lazy" class="max-w-full h-auto rounded-xl" onerror="this.outerHTML='<div class=\'border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-16 w-full flex flex-col items-center justify-center text-slate-400\'><svg class=\'size-12 mb-4 text-slate-300\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'></path></svg><p>{{ __('Gambar Struktur Organisasi Belum Tersedia') }} (assets/images/struktur_organisasi.png)</p></div>'">
        </div>
    </section>

    <section class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-slate-800 rounded-3xl overflow-hidden shadow-xl">
            <div class="grid lg:grid-cols-2">
                <div class="p-8 sm:p-12 flex flex-col justify-center relative">
                    <div class="absolute top-0 left-0 w-full h-full bg-brand-600/10 blur-3xl rounded-full pointer-events-none"></div>
                    <div class="relative z-10">
                        <h2 class="text-3xl font-extrabold text-white mb-8">{{ __('Hubungi Kami') }}</h2>
                        
                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-slate-700 rounded-xl text-brand-400">
                                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-white font-semibold text-lg">{{ __('Alamat Kantor') }}</h3>
                                    <p class="text-slate-400 mt-1">{{ __('Jl. Kakatua No. 09, Kelurahan Tanamodindi, Kecamatan Mantikulore, Kota Palu') }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-slate-700 rounded-xl text-brand-400">
                                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-white font-semibold text-lg">{{ __('Jam Pelayanan') }}</h3>
                                    <p class="text-slate-400 mt-1">{{ __('Senin - Kamis (08.00 - 16.00 WITA)') }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-slate-700 rounded-xl text-brand-400">
                                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-white font-semibold text-lg">{{ __('Call Center / WhatsApp') }}</h3>
                                    <p class="mt-1"><a href="https://wa.me/6285191512076" target="_blank" rel="noopener noreferrer" class="text-brand-400 hover:text-brand-300 font-bold transition">0851-9151-2076</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-64 lg:h-auto w-full bg-slate-200 dark:bg-slate-800">
                    <iframe 
                        src="https://maps.google.com/maps?q=Dinas%20Lingkungan%20Hidup%20Kota%20Palu&t=&z=15&ie=UTF8&iwloc=&output=embed" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade"
                        class="min-h-full">
                    </iframe>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
