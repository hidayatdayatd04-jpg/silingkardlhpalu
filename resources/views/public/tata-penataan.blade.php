@extends('layouts.app')

@section('title', 'Bidang Tata Penataan - DLH Kota Palu')
@section('description', 'Informasi modul Bidang Tata Penataan Dinas Lingkungan Hidup Kota Palu: objek pengawasan, pengaduan, sidak, pelanggaran, sanksi, dan sosialisasi.')

@section('content')
<div class="space-y-8 max-w-4xl mx-auto">

    <x-public.page-hero
        badge="{{ __('Bidang Tata Penataan') }}"
        title="{{ __('Modul Bidang Tata Penataan') }}"
        description="{{ __('Sistem pengawasan dan penegakan tata ruang lingkungan hidup untuk perusahaan dan industri di Kota Palu.') }}"
    />

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 sm:p-8 space-y-5">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('6 Modul Tata Penataan') }}</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                {{ __('Bidang Tata Penataan mengelola pengawasan lingkungan hidup melalui enam modul terintegrasi. Modul berikut dikelola admin di panel internal, sementara layanan publik tersedia untuk pengaduan, cek status, dan peta objek pengawasan.') }}
            </p>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            @foreach ([
                ['title' => __('Objek Pengawasan'), 'desc' => __('Database perusahaan/industri yang diawasi beserta dokumen AMDAL, UKL-UPL, dan SPPL.'), 'admin' => true],
                ['title' => __('Pengaduan Masyarakat'), 'desc' => __('Laporan limbah, asap, dan kebisingan dari masyarakat dengan bukti foto dan lokasi peta.'), 'url' => '/pengaduan'],
                ['title' => __('Sidak (Inspeksi)'), 'desc' => __('Pencatatan sidak lapangan, temuan, rekomendasi, dan berita acara inspeksi.'), 'admin' => true],
                ['title' => __('Pelanggaran'), 'desc' => __('Pencatatan jenis pelanggaran lingkungan dan pasal yang dilanggar.'), 'admin' => true],
                ['title' => __('Sanksi'), 'desc' => __('Penerbitan teguran, penghentian kegiatan, atau denda administratif.'), 'admin' => true],
                ['title' => __('Sosialisasi'), 'desc' => __('Kegiatan pembinaan dan sertifikat kehadiran peserta objek pengawasan.'), 'admin' => true],
            ] as $modul)
            @if (isset($modul['url']))
                <a href="{{ $modul['url'] }}" class="group rounded-xl border border-slate-200 dark:border-slate-700 p-4 hover:border-brand-400 hover:bg-brand-50/50 dark:hover:bg-brand-900/10 transition-colors">
                    <h3 class="font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400">{{ $modul['title'] }}</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $modul['desc'] }}</p>
                    <span class="inline-block mt-2 text-xs font-medium text-brand-600 dark:text-brand-400">{{ __('Buka layanan →') }}</span>
                </a>
            @else
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50/50 dark:bg-slate-900/50">
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ $modul['title'] }}</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $modul['desc'] }}</p>
                    <span class="inline-block mt-2 text-xs font-medium text-slate-400">{{ __('Panel Admin') }}</span>
                </div>
            @endif
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-brand-200 dark:border-brand-900/40 bg-brand-50 dark:bg-brand-900/10 p-6 sm:p-8 space-y-5">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Layanan Publik') }}</h2>
        <div class="grid sm:grid-cols-3 gap-4">
            @foreach ([
                ['title' => __('Pengaduan'), 'desc' => __('Laporkan limbah, asap, atau kebisingan'), 'url' => '/pengaduan'],
                ['title' => __('Cek Status'), 'desc' => __('Lacak pengaduan via tiket atau HP'), 'url' => '/cek-pengaduan-tata-penataan'],
                ['title' => __('Peta Objek Pengawasan'), 'desc' => __('Lihat lokasi perusahaan & status dokumen'), 'url' => '/peta-objek-pengawasan'],
            ] as $layanan)
            <a href="{{ $layanan['url'] }}" class="group rounded-xl border border-brand-200 dark:border-brand-800 bg-white dark:bg-slate-900 p-4 hover:border-brand-400 transition-colors">
                <h3 class="font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400">{{ $layanan['title'] }}</h3>
                <p class="text-xs text-slate-500 mt-1">{{ $layanan['desc'] }}</p>
                <span class="inline-block mt-2 text-xs font-medium text-brand-600 dark:text-brand-400">{{ __('Buka →') }}</span>
            </a>
            @endforeach
        </div>
    </div>

</div>
@endsection
