@extends('layouts.app')

@section('title', 'Tentang Kami - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Mengenal lebih dekat Dinas Lingkungan Hidup Kota Palu: sejarah, program unggulan, dan komitmen kami dalam menjaga kelestarian lingkungan.')

@section('content')
<div class="space-y-16 pb-16">

    {{-- Hero Section --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 p-8 sm:p-12 lg:p-16 text-white">
        <div class="absolute inset-0 opacity-10 mix-blend-overlay" style="background-image:url('/assets/images/polygon-bg-element.svg')"></div>
        <div class="absolute -right-20 -top-20 w-72 h-72 bg-emerald-500/20 rounded-full blur-3xl"></div>
        <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-brand-500/15 rounded-full blur-3xl"></div>
        <div class="relative z-10 max-w-3xl mx-auto text-center">
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm text-sm font-semibold mb-6 ring-1 ring-white/20">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21"/></svg>
                {{ __('Tentang Kami') }}
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-[2.75rem] font-bold leading-tight">
                {{ __('Dinas Lingkungan Hidup') }}<br>
                <span class="text-emerald-400">{{ __('Kota Palu') }}</span>
            </h1>
            <p class="mt-6 text-lg text-slate-300 leading-relaxed max-w-2xl mx-auto">
                {{ __('Garda terdepan pelindung lingkungan di jantung Sulawesi Tengah. Kami berkomitmen mewujudkan Kota Palu yang bersih, hijau, dan berkelanjutan.') }}
            </p>
        </div>
    </section>

    {{-- Sambutan Kepala Dinas --}}
    <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-8 sm:p-12 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-brand-50 rounded-full blur-3xl -z-10 dark:bg-brand-900/20 opacity-50 translate-x-1/2 -translate-y-1/2"></div>
        <div class="grid lg:grid-cols-12 gap-10 lg:gap-16 items-center">
            <div class="lg:col-span-4 flex justify-center lg:justify-start">
                <div class="relative rounded-2xl overflow-hidden group shadow-xl max-w-sm w-full border-4 border-white dark:border-slate-800">
                    <img class="w-full object-cover rounded-xl aspect-[3/4]" src="{{ asset('assets/images/foto_kadis.webp') }}" alt="{{ __('Foto Kepala Dinas') }}" decoding="async">
                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-slate-900/90 to-transparent p-6 text-center">
                        <p class="text-white font-bold text-xl">Mohamad Arif, S.STP., M.Si</p>
                        <p class="text-brand-300 text-sm font-medium mt-1">{{ __('Kepala Dinas Lingkungan Hidup Kota Palu') }}</p>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-8 space-y-6">
                <h2 class="text-3xl font-bold text-slate-800 dark:text-slate-200 relative inline-block">
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
    </section>

    {{-- Sejarah & Tentang --}}
    <section class="grid lg:grid-cols-2 gap-8">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-8 sm:p-10 shadow-sm">
            <div class="flex items-center gap-3 mb-6">
                <span class="p-2 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-lg">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                </span>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-200">{{ __('Sejarah Singkat') }}</h2>
            </div>
            <div class="space-y-4 text-slate-600 dark:text-slate-400 leading-relaxed">
                <p>
                    {{ __('Dinas Lingkungan Hidup (DLH) Kota Palu merupakan organisasi perangkat daerah yang berada di bawah Pemerintah Kota Palu, Provinsi Sulawesi Tengah. DLH Palu bertanggung jawab langsung dalam pengelolaan dan perlindungan lingkungan hidup di wilayah Kota Palu.') }}
                </p>
                <p>
                    {{ __('Sebagai ibu kota Provinsi Sulawesi Tengah, Kota Palu memiliki topografi yang unik dengan kombinasi pegunungan, teluk, dan dataran rendah. DLH Palu hadir untuk menjaga keseimbangan antara pembangunan kota dengan kelestarian alam, memastikan bahwa pertumbuhan ekonomi berjalan beriringan dengan pelestarian lingkungan.') }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-8 sm:p-10 shadow-sm">
            <div class="flex items-center gap-3 mb-6">
                <span class="p-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                </span>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-200">{{ __('Wilayah Kerja') }}</h2>
            </div>
            <div class="space-y-4 text-slate-600 dark:text-slate-400 leading-relaxed">
                <p>
                    {{ __('DLH Kota Palu membawahi pengelolaan lingkungan hidup di seluruh wilayah Kota Palu yang terdiri dari 8 kecamatan. Dengan luas wilayah sekitar 395,06 km persejiang, DLH Palu mengelola berbagai aspek lingkungan mulai dari pengelolaan sampah, penghijauan, pengawasan limbah industri, hingga mitigasi bencana lingkungan.') }}
                </p>
                <p>
                    {{ __('Kota Palu terletak di garis khatulistiwa dengan curah hujan yang fluktuatif dan suhu udara yang tinggi, menjadikan pengelolaan lingkungan sebagai prioritas utama untuk kenyamanan dan kesehatan masyarakat.') }}
                </p>
            </div>
        </div>
    </section>

    {{-- Program Unggulan --}}
    <section>
        <div class="text-center mb-10">
            <span class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-700 dark:text-brand-400">{{ __('Program Unggulan') }}</span>
            <h2 class="mt-3 text-3xl font-bold text-slate-800 dark:text-slate-200">{{ __('Inovasi Lingkungan DLH Palu') }}</h2>
            <p class="mt-3 text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">{{ __('Berbagai program strategis yang kami jalankan untuk menjaga kelestarian lingkungan di Kota Palu.') }}</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="group bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                <div class="size-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-4 group-hover:scale-110 transition-transform">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-2">{{ __('Bank Sampah') }}</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ __('Program pemberdayaan masyarakat dalam pemilahan dan daur ulang sampah dari sumbernya, tersebar di berbagai kecamatan di Kota Palu.') }}</p>
            </div>

            <div class="group bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                <div class="size-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400 mb-4 group-hover:scale-110 transition-transform">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.115 5.19l.319 1.913A6 6 0 008.11 10.36L9.75 12l-.387.775c-.217.433-.132.956.21 1.298l1.348 1.348c.21.21.329.497.329.795v1.089c0 .426.24.815.622 1.006l.153.076c.433.217.956.132 1.298-.21l.723-.723a8.7 8.7 0 002.288-4.042 1.087 1.087 0 00-.358-1.099l-1.33-1.108c-.251-.21-.582-.299-.905-.245l-1.17.195a1.125 1.125 0 01-.98-.314l-.295-.295a1.125 1.125 0 010-1.591l.13-.132a1.125 1.125 0 011.3-.21l.603.302a.809.809 0 001.086-1.086L14.25 7.5l1.256-.837a4.5 4.5 0 001.528-1.732l.146-.292M6.115 5.19A9 9 0 1017.18 4.64M6.115 5.19A8.965 8.965 0 0112 3c1.929 0 3.716.607 5.18 1.64"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-2">{{ __('Palu Hijau') }}</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ __('Gerakan penghijauan masif yang menargetkan kawasan perkotaan, taman publik, dan jalur hijau di sepanjang jalan utama Kota Palu.') }}</p>
            </div>

            <div class="group bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                <div class="size-12 rounded-xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center text-sky-600 dark:text-sky-400 mb-4 group-hover:scale-110 transition-transform">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-2">{{ __('Mitigasi Bencana') }}</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ __('Pemulihan area hijau dan penanaman mangrove di pesisir Teluk Palu untuk mitigasi abrasi pascabencana 2018.') }}</p>
            </div>

            <div class="group bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                <div class="size-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 mb-4 group-hover:scale-110 transition-transform">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-2">{{ __('Eco School') }}</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ __('Program edukasi lingkungan di sekolah-sekolah untuk menanamkan kesadaran dini tentang pemilahan sampah dan pelestarian alam.') }}</p>
            </div>

            <div class="group bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                <div class="size-12 rounded-xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 dark:text-rose-400 mb-4 group-hover:scale-110 transition-transform">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1A3.75 3.75 0 0012 18z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-2">{{ __('Pengawasan Limbah') }}</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ __('Inspeksi rutin ke perusahaan, rumah makan, dan bengkel untuk memastikan pengelolaan limbah sesuai regulasi lingkungan.') }}</p>
            </div>

            <div class="group bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                <div class="size-12 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center text-violet-600 dark:text-violet-400 mb-4 group-hover:scale-110 transition-transform">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-2">{{ __('Kampanye Lingkungan') }}</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ __('Kolaborasi dengan komunitas lokal seperti Palu Bersih dan Sahabat Hijau dalam kegiatan bersih pantai dan sungai.') }}</p>
            </div>
        </div>
    </section>

    {{-- Hubungi Kami --}}
    <section class="bg-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="grid lg:grid-cols-2">
            <div class="p-8 sm:p-12 flex flex-col justify-center relative">
                <div class="absolute top-0 left-0 w-full h-full bg-brand-600/10 blur-3xl rounded-full pointer-events-none"></div>
                <div class="relative z-10">
                    <h2 class="text-3xl font-bold text-white mb-8">{{ __('Hubungi Kami') }}</h2>
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
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-slate-700 rounded-xl text-brand-400">
                                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold text-lg">{{ __('Media Sosial') }}</h3>
                                <div class="flex items-center gap-3 mt-2">
                                    <a href="https://www.instagram.com/dlhkotapalu" target="_blank" rel="noopener noreferrer" class="p-2 bg-slate-700 rounded-lg text-slate-400 hover:text-white hover:bg-pink-600 transition-all" title="Instagram">
                                        <x-icons.social.instagram class="w-5 h-5" />
                                    </a>
                                    <a href="https://www.facebook.com/share/18qHSySQr4/?locale=id_ID" target="_blank" rel="noopener noreferrer" class="p-2 bg-slate-700 rounded-lg text-slate-400 hover:text-white hover:bg-blue-600 transition-all" title="Facebook">
                                        <x-icons.social.facebook class="w-5 h-5" />
                                    </a>
                                    <a href="https://wa.me/6285191512076" target="_blank" rel="noopener noreferrer" class="p-2 bg-slate-700 rounded-lg text-slate-400 hover:text-white hover:bg-brand-600 transition-all" title="WhatsApp">
                                        <x-icons.social.whatsapp class="w-5 h-5" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="h-64 lg:h-auto w-full bg-slate-200 dark:bg-slate-800">
                <iframe 
                    src="https://maps.google.com/maps?q=Dinas%20Lingkungan%20Hidup%20Kota%20Palu&t=&z=15&ie=UTF8&iwloc=&output=embed" 
                    title="{{ __('Peta lokasi Dinas Lingkungan Hidup Kota Palu') }}"
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
    </section>

</div>
@endsection
