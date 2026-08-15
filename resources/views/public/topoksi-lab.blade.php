@extends('layouts.app')

@section('title', 'Topoksi Lab - UPTD Laboratorium Lingkungan Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Tugas pokok dan fungsi UPTD Laboratorium Lingkungan Dinas Lingkungan Hidup Kota Palu: pemantauan kualitas lingkungan, pengambilan sampel, pengujian, dan penjaminan mutu laboratorium.')

@section('content')
<div class="space-y-16 pb-16">

    {{-- Hero Section --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 p-8 sm:p-12 lg:p-16 text-white">
        <div class="absolute inset-0 opacity-10 mix-blend-overlay" style="background-image: radial-gradient(circle at 20% 30%, rgba(16,185,129,.6) 0, transparent 40%), radial-gradient(circle at 80% 70%, rgba(16,185,129,.4) 0, transparent 45%);"></div>
        <div class="absolute -right-20 -top-20 w-72 h-72 bg-emerald-500/20 rounded-full blur-3xl"></div>
        <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-brand-500/15 rounded-full blur-3xl"></div>
        <div class="relative z-10 max-w-3xl mx-auto text-center">
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm text-sm font-semibold mb-6 ring-1 ring-white/20">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
                UPTD Laboratorium Lingkungan
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight">
                Topoksi Lab<br>
                <span class="text-emerald-400">Tugas Pokok &amp; Fungsi</span>
            </h1>
            <p class="mt-6 text-lg text-slate-300 leading-relaxed max-w-2xl mx-auto">
                UPTD Laboratorium Lingkungan melaksanakan sebagian kegiatan teknis operasional dinas dalam lingkup penyelenggaraan pemantauan kualitas lingkungan dalam rangka peningkatan kualitas lingkungan.
            </p>
        </div>
    </section>

    {{-- Galeri UPTD --}}
    <section class="grid lg:grid-cols-2 gap-8">
        <figure class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm hover:shadow-xl transition-shadow duration-300">
            <div class="relative h-64 shrink-0 overflow-hidden sm:h-80">
                <img src="{{ asset('assets/images/lab-lingkungan-1.jpeg') }}" alt="Aktivitas pengujian sampel di UPTD Laboratorium Lingkungan"
                     class="size-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
            </div>
            <figcaption class="flex flex-1 items-center gap-4 p-6">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-500 to-emerald-600 text-white shadow-lg shadow-brand-500/20">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
                </span>
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Kegiatan Pengujian Sampel</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-600 dark:text-slate-400">Pengambilan dan pengujian contoh uji sesuai standar, verifikasi data hasil uji, hingga penerbitan laporan hasil pengujian.</p>
                </div>
            </figcaption>
        </figure>
        <figure class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm hover:shadow-xl transition-shadow duration-300">
            <div class="relative h-64 shrink-0 overflow-hidden sm:h-80">
                <img src="{{ asset('assets/images/lab-lingkungan-2.jpeg') }}" alt="Peralatan dan kegiatan UPTD Laboratorium Lingkungan"
                     class="size-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
            </div>
            <figcaption class="flex flex-1 items-center gap-4 p-6">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-500 to-emerald-600 text-white shadow-lg shadow-brand-500/20">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/></svg>
                </span>
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Fasilitas &amp; Peralatan Laboratorium</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-600 dark:text-slate-400">Peralatan dan instrumen laboratorium yang direncanakan, dipelihara, dan diverifikasi guna mendukung pelayanan pengujian.</p>
                </div>
            </figcaption>
        </figure>
    </section>

    {{-- Tugas Pokok --}}
    <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-8 sm:p-12 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-brand-50 rounded-full blur-3xl -z-10 dark:bg-brand-900/20 opacity-50 translate-x-1/2 -translate-y-1/2"></div>
        <div class="flex items-center gap-3 mb-6">
            <span class="p-2 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-lg">
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-slate-200">Tugas Pokok &amp; Fungsi Utama</h2>
        </div>
        <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-base sm:text-lg max-w-4xl">
            UPTD Laboratorium Lingkungan melaksanakan sebagian kegiatan teknis operasional dinas dalam lingkup penyelenggaraan pemantauan kualitas lingkungan dalam rangka peningkatan kualitas lingkungan, meliputi:
        </p>
    </section>

    {{-- Ruang Lingkup --}}
    <section class="grid md:grid-cols-2 gap-6">
        @php
            $ruangLingkup = [
                [
                    'title' => 'Perencanaan & Sistem Manajemen Mutu',
                    'desc' => 'Menyusun rencana program kegiatan, mengesahkan panduan mutu, serta melakukan kaji ulang dan perbaikan Sistem Manajemen Mutu Laboratorium secara berkala.',
                    'icon' => 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25',
                ],
                [
                    'title' => 'Teknis Pengambilan Sampel & Pengujian',
                    'desc' => 'Melaksanakan pengambilan contoh uji sesuai standar (Good Sampling Practice), melakukan pengujian dan kalibrasi, memverifikasi data hasil uji, hingga menerbitkan laporan/sertifikat hasil pengujian.',
                    'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z',
                ],
                [
                    'title' => 'Penjaminan Mutu (QA/QC) & Audit',
                    'desc' => 'Menerapkan dan mengawasi Quality Assurance/Quality Control (QA/QC), menyelenggarakan audit internal, memvalidasi metode uji, serta berpartisipasi dalam uji profisiensi/uji banding antar laboratorium.',
                    'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
                [
                    'title' => 'Pengelolaan Fasilitas & Logistik',
                    'desc' => 'Merencanakan, mengadakan, memverifikasi, dan memelihara peralatan, instrumen, serta bahan habis pakai laboratorium beserta rekaman pemasoknya.',
                    'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
                ],
                [
                    'title' => 'Pelayanan & Penanganan Keluhan',
                    'desc' => 'Menangani administrasi penerimaan sampel, merespons pengaduan pelanggan, serta melakukan penelusuran atau pengujian ulang (terhadap retained sample) jika diperlukan.',
                    'icon' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
                ],
                [
                    'title' => 'Ketatausahaan & Pelaporan',
                    'desc' => 'Melaksanakan urusan tata usaha, rumah tangga, koordinasi lintas instansi, penyusunan laporan evaluasi, serta melaksanakan tugas kedinasan lain dari Kepala Dinas.',
                    'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
                ],
            ];
        @endphp
        @foreach ($ruangLingkup as $i => $item)
            <div class="group relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-7 sm:p-8 shadow-sm hover:shadow-lg hover:border-brand-300 dark:hover:border-brand-700 transition-all duration-300">
                <div class="flex items-start gap-4">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-500 to-emerald-600 text-white shadow-lg shadow-brand-500/20 group-hover:scale-110 transition-transform duration-300">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                    </span>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <span class="text-brand-500 text-sm font-extrabold">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            {{ $item['title'] }}
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $item['desc'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </section>
</div>
@endsection
