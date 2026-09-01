@extends('layouts.admin')

@section('title', 'Detail '.$record->nama_tpu.' - SILINGKAR DLH ADMIN')
@section('heading', $resource['label'])

@php
    $isSuperadmin = auth()->user()?->isSuperadmin() ?? false;
    $vegetasi = is_array($record->vegetasi) ? $record->vegetasi : [];
    $kapasitas = is_array($record->kapasitas_blok) ? $record->kapasitas_blok : [];
    $photos = $record->getDokumentasiList();
@endphp

@section('content')
<div
    x-data="{
        lightboxOpen: false,
        lightboxImage: '',
        lightboxTitle: '',
        openLightbox(url, title) {
            this.lightboxImage = url;
            this.lightboxTitle = title;
            this.lightboxOpen = true;
        },
        closeLightbox() {
            this.lightboxOpen = false;
        }
    }"
    x-on:keydown.escape.window="closeLightbox()"
    class="max-w-5xl mx-auto space-y-6"
>
    {{-- Header Detail --}}
    <x-admin.page-header
        :title="$record->nama_tpu"
        subtitle="Detail data inventarisasi, vegetasi, dan kapasitas blok makam."
        icon="park"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => $resource['label'], 'url' => route('admin.resources.index', $resource['slug'])],
            ['label' => $record->nama_tpu],
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.resources.index', $resource['slug']) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition-all shadow-sm cursor-pointer"
                >
                    <x-icons.ui name="arrow-left" class="w-4 h-4" />
                    <span>Kembali</span>
                </a>

                @if(!$isSuperadmin)
                    <a
                        href="{{ route('admin.resources.edit', [$resource['slug'], $record]) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-brand-600 hover:bg-brand-700 text-white shadow-sm shadow-brand-600/20 transition-all cursor-pointer"
                    >
                        <x-icons.ui name="edit" class="w-4 h-4" />
                        <span>Ubah Data</span>
                    </a>
                @endif
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Ringkasan Kartu --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Luas Area Makam</p>
                <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $record->luas_area_makam }}</h4>
            </div>
            <div class="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                <x-icons.ui name="park" class="w-6 h-6" />
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Pohon Pelindung</p>
                <h4 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $record->totalPohon() }} Pohon</h4>
                <p class="text-xs text-slate-400">{{ count($vegetasi) }} jenis pohon terdata</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <x-icons.ui name="seedling" class="w-6 h-6" />
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Kapasitas Makam</p>
                <h4 class="text-2xl font-black text-sky-600 dark:text-sky-400 mt-1">{{ number_format($record->totalMakam()) }} Makam</h4>
                <p class="text-xs text-slate-400">{{ $record->totalBlok() }} blok makam</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                <x-icons.ui name="building" class="w-6 h-6" />
            </div>
        </div>
    </div>

    {{-- Detail Data Grid: Vegetasi & Kapasitas Blok --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Kolom Kiri: Tabel Vegetasi --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                <div class="flex items-center gap-2.5">
                    <x-icons.ui name="tree" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Vegetasi Pohon Pelindung</h3>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                    {{ count($vegetasi) }} Jenis
                </span>
            </div>

            <div class="p-0 flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-slate-100 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/30 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2.5 w-12 text-center">No.</th>
                            <th class="px-4 py-2.5">Jenis Pohon Pelindung</th>
                            <th class="px-4 py-2.5 text-right w-32">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($vegetasi as $idx => $v)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-4 py-2.5 text-center text-xs text-slate-400 font-semibold">{{ $idx + 1 }}</td>
                                <td class="px-4 py-2.5 font-medium text-slate-800 dark:text-slate-200">{{ $v['jenis_pohon'] ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-right font-bold text-slate-900 dark:text-white">{{ $v['jumlah'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-xs text-slate-400 italic">Belum ada data vegetasi pohon</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kolom Kanan: Tabel Kapasitas Blok --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                <div class="flex items-center gap-2.5">
                    <x-icons.ui name="building" class="w-5 h-5 text-sky-600 dark:text-sky-400" />
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Kapasitas Blok Makam</h3>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300">
                    {{ count($kapasitas) }} Kategori Agama
                </span>
            </div>

            <div class="p-0 flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-slate-100 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/30 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2.5">Agama</th>
                            <th class="px-4 py-2.5 text-center w-28">Jumlah Blok</th>
                            <th class="px-4 py-2.5 text-right w-36">Jumlah Makam</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($kapasitas as $k)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-4 py-2.5 font-bold text-slate-800 dark:text-slate-200">{{ $k['agama'] ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $k['jumlah_blok'] ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-right font-black text-slate-900 dark:text-white">{{ $k['jumlah_makam'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-xs text-slate-400 italic">Belum ada data kapasitas blok</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Dokumentasi Foto --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2.5">
                <x-icons.ui name="image" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                <h3 class="font-bold text-slate-900 dark:text-white text-base">Dokumentasi Foto Lapangan</h3>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300">
                {{ count($photos) }} Foto Terlampir
            </span>
        </div>

        @if(count($photos) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($photos as $p)
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <span>{{ $p['label'] }}</span>
                        </div>
                        <div
                            class="aspect-video rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700 relative group cursor-pointer bg-slate-100 dark:bg-slate-800"
                            @click="openLightbox('{{ $p['url'] }}', '{{ $p['label'] }} - {{ $record->nama_tpu }}')"
                        >
                            <img src="{{ $p['url'] }}" alt="{{ $p['label'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white text-sm font-semibold gap-1.5">
                                <x-icons.ui name="eye" class="w-4 h-4" />
                                <span>Perbesar</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 text-slate-400 text-xs italic">
                Belum ada dokumentasi foto yang diunggah untuk TPU ini.
            </div>
        @endif
    </div>

    {{-- Lightbox Modal --}}
    <div
        x-show="lightboxOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-sm"
        @click.self="closeLightbox()"
    >
        <div class="relative max-w-5xl max-h-[90vh] bg-slate-900 rounded-2xl overflow-hidden shadow-2xl border border-slate-700">
            <div class="flex items-center justify-between px-4 py-3 bg-slate-800/90 text-white border-b border-slate-700">
                <span class="text-sm font-semibold truncate" x-text="lightboxTitle"></span>
                <button @click="closeLightbox()" class="p-1 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white transition-colors cursor-pointer">
                    <x-icons.ui name="close" class="w-5 h-5" />
                </button>
            </div>
            <div class="p-2 flex items-center justify-center max-h-[calc(90vh-60px)]">
                <img :src="lightboxImage" alt="Preview Foto" class="max-h-[calc(90vh-80px)] max-w-full object-contain rounded-lg" />
            </div>
        </div>
    </div>
</div>
@endsection
