@extends('layouts.app')

@section('title', 'Taman Pemakaman Umum (TPU) Kota Palu - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Informasi resmi data Taman Pemakaman Umum (TPU), kapasitas daya tampung blok makam, serta vegetasi pohon pelindung di wilayah Kota Palu.')

@section('content')
<div
    x-data="{
        search: '',
        activeTab: {},
        lightboxOpen: false,
        lightboxPhotos: [],
        lightboxIndex: 0,
        lightboxTpuName: '',
        openLightbox(photos, index, tpuName) {
            this.lightboxPhotos = photos || [];
            this.lightboxIndex = index || 0;
            this.lightboxTpuName = tpuName || '';
            this.lightboxOpen = true;
            document.body.style.overflow = 'hidden';
        },
        closeLightbox() {
            this.lightboxOpen = false;
            document.body.style.overflow = '';
        },
        nextPhoto() {
            if (this.lightboxPhotos.length > 1) {
                this.lightboxIndex = (this.lightboxIndex + 1) % this.lightboxPhotos.length;
            }
        },
        prevPhoto() {
            if (this.lightboxPhotos.length > 1) {
                this.lightboxIndex = (this.lightboxIndex - 1 + this.lightboxPhotos.length) % this.lightboxPhotos.length;
            }
        },
        currentPhoto() {
            return this.lightboxPhotos[this.lightboxIndex] || { url: '', label: '' };
        },
        getTab(tpuId) {
            return this.activeTab[tpuId] || 'overview';
        },
        setTab(tpuId, tab) {
            this.activeTab[tpuId] = tab;
        }
    }"
    x-on:keydown.escape.window="if (lightboxOpen) closeLightbox()"
    x-on:keydown.arrow-right.window="if (lightboxOpen) nextPhoto()"
    x-on:keydown.arrow-left.window="if (lightboxOpen) prevPhoto()"
    class="min-h-screen bg-slate-50/70 dark:bg-slate-950 pb-20 antialiased"
>
    {{-- Hero Section --}}
    <section class="relative pt-12 pb-16 md:pt-16 md:pb-20 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-4">
                {{-- Badges --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold tracking-wide">
                    <x-icons.ui name="park" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                    <span>Ruang Terbuka Hijau & Sarana Pemakaman</span>
                </div>

                {{-- Heading: Sharp, High-Contrast Typography --}}
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                    Taman Pemakaman Umum <span class="text-emerald-700 dark:text-emerald-400">(TPU) Kota Palu</span>
                </h1>

                {{-- Subtitle --}}
                <p class="text-base sm:text-lg text-slate-700 dark:text-slate-300 leading-relaxed font-normal">
                    Data keterbukaan informasi publik sarana pemakaman, kapasitas blok makam, serta pemeliharaan vegetasi pohon pelindung di wilayah Kota Palu.
                </p>
            </div>

            {{-- Stat Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-10 md:mt-12">
                <div class="p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-800/80 border border-slate-200/90 dark:border-slate-700 shadow-sm text-center">
                    <div class="w-11 h-11 mx-auto rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 flex items-center justify-center mb-2.5">
                        <x-icons.ui name="park" class="w-5 h-5" />
                    </div>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ number_format($totalTpu, 0, ',', '.') }}</p>
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mt-1">TPU Terdata</p>
                </div>

                <div class="p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-800/80 border border-slate-200/90 dark:border-slate-700 shadow-sm text-center">
                    <div class="w-11 h-11 mx-auto rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 flex items-center justify-center mb-2.5">
                        <x-icons.ui name="tree" class="w-5 h-5" />
                    </div>
                    <p class="text-2xl sm:text-3xl font-black text-emerald-700 dark:text-emerald-400 tracking-tight">{{ $totalLuas > 0 ? $totalLuas : $tpus->count() * 2 }} Ha</p>
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mt-1">Total Luas Lahan</p>
                </div>

                <div class="p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-800/80 border border-slate-200/90 dark:border-slate-700 shadow-sm text-center">
                    <div class="w-11 h-11 mx-auto rounded-xl bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 flex items-center justify-center mb-2.5">
                        <x-icons.ui name="building" class="w-5 h-5" />
                    </div>
                    <p class="text-2xl sm:text-3xl font-black text-sky-700 dark:text-sky-400 tracking-tight">{{ number_format($totalMakam, 0, ',', '.') }}</p>
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mt-1">Total Makam</p>
                </div>

                <div class="p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-800/80 border border-slate-200/90 dark:border-slate-700 shadow-sm text-center">
                    <div class="w-11 h-11 mx-auto rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 flex items-center justify-center mb-2.5">
                        <x-icons.ui name="seedling" class="w-5 h-5" />
                    </div>
                    <p class="text-2xl sm:text-3xl font-black text-amber-700 dark:text-amber-400 tracking-tight">{{ number_format($totalPohon, 0, ',', '.') }}</p>
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mt-1">Pohon Pelindung</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Content Section --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 space-y-8">
        {{-- Search & Filter Bar --}}
        <div class="space-y-3">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="relative flex-1 w-full max-w-2xl">
                    <div class="relative flex items-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all focus-within:border-emerald-500 focus-within:ring-4 focus-within:ring-emerald-500/10 hover:border-slate-300 dark:hover:border-slate-700">
                        <div class="flex items-center justify-center pl-4 pr-2 text-slate-400 dark:text-slate-500 pointer-events-none">
                            <x-icons.ui name="search" class="w-5 h-5 text-slate-400 dark:text-slate-500" />
                        </div>
                        <input
                            type="text"
                            x-model="search"
                            placeholder="Cari nama TPU (contoh: Lambara, Poboya, Valagguni)..."
                            class="w-full py-3 pr-4 text-sm font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 bg-transparent border-0 focus:outline-none focus:ring-0"
                        />
                        <button
                            type="button"
                            x-show="search !== ''"
                            x-cloak
                            @click="search = ''"
                            class="p-1.5 mr-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                            title="Hapus pencarian"
                        >
                            <x-icons.ui name="close" class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-start sm:self-center">
                    <div class="flex items-center gap-2 px-3.5 py-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Bidang Ruang Terbuka Hijau (RTH)</span>
                    </div>
                </div>
            </div>

            {{-- Indikator Hasil Pencarian --}}
            <div x-show="search !== ''" x-cloak class="text-xs text-slate-500 dark:text-slate-400 pl-1 flex items-center gap-1.5">
                <span>Menampilkan hasil pencarian untuk:</span>
                <strong class="text-emerald-700 dark:text-emerald-400 font-bold" x-text="'&ldquo;' + search + '&rdquo;'"></strong>
            </div>
        </div>

        {{-- TPU Cards List --}}
        <div class="space-y-8">
            @forelse($tpus as $tpu)
                @php
                    $vegetasi = is_array($tpu->vegetasi) ? $tpu->vegetasi : [];
                    $kapasitas = is_array($tpu->kapasitas_blok) ? $tpu->kapasitas_blok : [];
                    $photos = $tpu->getDokumentasiList();
                @endphp
                <article
                    x-show="search === '' || '{{ strtolower($tpu->nama_tpu) }}'.includes(search.toLowerCase())"
                    class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md"
                >
                    {{-- Card Header --}}
                    <div class="p-6 md:p-8 bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                                        <x-icons.ui name="park" class="w-6 h-6" />
                                    </div>
                                    <div>
                                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                                            {{ $tpu->nama_tpu }}
                                        </h2>
                                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">Kota Palu, Sulawesi Tengah</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                {{-- Badge Luas --}}
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-300 text-xs font-bold">
                                    <x-icons.ui name="tree" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                                    <span>Luas: {{ $tpu->luas_area_makam }}</span>
                                </span>

                                {{-- Badge Kapasitas --}}
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 dark:bg-sky-950/60 border border-sky-200 dark:border-sky-800 text-sky-900 dark:text-sky-300 text-xs font-bold">
                                    <x-icons.ui name="building" class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400" />
                                    <span>{{ number_format($tpu->totalMakam(), 0, ',', '.') }} Makam</span>
                                </span>

                                {{-- Badge Vegetasi --}}
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-50 dark:bg-teal-950/60 border border-teal-200 dark:border-teal-800 text-teal-900 dark:text-teal-300 text-xs font-bold">
                                    <x-icons.ui name="seedling" class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400" />
                                    <span>{{ number_format($tpu->totalPohon(), 0, ',', '.') }} Pohon Pelindung</span>
                                </span>
                            </div>
                        </div>

                        {{-- Nav Tabs Card --}}
                        <div class="flex items-center gap-2 mt-6 pt-4 border-t border-slate-200 dark:border-slate-700/80 overflow-x-auto">
                            <button
                                @click="setTab({{ $tpu->id }}, 'overview')"
                                :class="getTab({{ $tpu->id }}) === 'overview' ? 'bg-emerald-700 text-white shadow-sm font-bold' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 font-semibold'"
                                class="px-4 py-2 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer"
                            >
                                Dokumentasi Foto ({{ count($photos) }})
                            </button>
                            <button
                                @click="setTab({{ $tpu->id }}, 'vegetasi')"
                                :class="getTab({{ $tpu->id }}) === 'vegetasi' ? 'bg-emerald-700 text-white shadow-sm font-bold' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 font-semibold'"
                                class="px-4 py-2 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer"
                            >
                                Vegetasi Pohon ({{ count($vegetasi) }})
                            </button>
                            <button
                                @click="setTab({{ $tpu->id }}, 'kapasitas')"
                                :class="getTab({{ $tpu->id }}) === 'kapasitas' ? 'bg-emerald-700 text-white shadow-sm font-bold' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 font-semibold'"
                                class="px-4 py-2 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer"
                            >
                                Kapasitas Blok Makam ({{ count($kapasitas) }})
                            </button>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-6 md:p-8">
                        {{-- Tab 1: Galeri Dokumentasi --}}
                        <div x-show="getTab({{ $tpu->id }}) === 'overview'" class="space-y-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                Dokumentasi {{ $tpu->nama_tpu }}
                            </h3>

                            @if(count($photos) > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    @foreach($photos as $idx => $p)
                                        <div class="space-y-1.5">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $p['label'] }}</span>
                                            <div
                                                class="aspect-video rounded-2xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 relative group cursor-pointer bg-slate-100 dark:bg-slate-800"
                                                @click="openLightbox({{ Js::from($photos) }}, {{ $idx }}, '{{ addslashes($tpu->nama_tpu) }}')"
                                            >
                                                <img src="{{ $p['url'] }}" alt="{{ $p['label'] }} - {{ $tpu->nama_tpu }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white text-sm font-semibold gap-1.5">
                                                    <x-icons.ui name="eye" class="w-4 h-4" />
                                                    <span>Lihat Foto</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-8 text-center bg-slate-50 dark:bg-slate-800/30 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 text-slate-500 text-sm">
                                    Belum ada foto dokumentasi untuk TPU ini.
                                </div>
                            @endif
                        </div>

                        {{-- Tab 2: Vegetasi Pohon Pelindung --}}
                        <div x-show="getTab({{ $tpu->id }}) === 'vegetasi'" class="space-y-4" style="display: none;">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                    Daftar Jenis Pohon Pelindung
                                </h3>
                                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950/60 px-3 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">
                                    Total: {{ number_format($tpu->totalPohon(), 0, ',', '.') }} Pohon
                                </span>
                            </div>

                            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                                <table class="w-full text-left text-sm divide-y divide-slate-200 dark:divide-slate-800">
                                    <thead class="bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">
                                        <tr>
                                            <th class="px-4 py-3 w-12 text-center">No.</th>
                                            <th class="px-4 py-3">Jenis Pohon Pelindung</th>
                                            <th class="px-4 py-3 text-right w-40">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        @forelse($vegetasi as $idx => $v)
                                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                                <td class="px-4 py-3 text-center text-xs text-slate-500 font-bold">{{ $idx + 1 }}</td>
                                                <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">
                                                    {{ $v['jenis_pohon'] ?? '-' }}
                                                </td>
                                                <td class="px-4 py-3 text-right font-black text-slate-900 dark:text-white">
                                                    <span class="inline-block px-2.5 py-0.5 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300">
                                                        {{ $v['jumlah'] ?? '-' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-4 py-6 text-center text-slate-500 text-xs italic">
                                                    Belum ada data rincian pohon pelindung.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab 3: Kapasitas Blok Makam --}}
                        <div x-show="getTab({{ $tpu->id }}) === 'kapasitas'" class="space-y-4" style="display: none;">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                    Daftar Kapasitas Blok & Daya Tampung Makam
                                </h3>
                                <span class="text-xs font-bold text-sky-800 dark:text-sky-300 bg-sky-100 dark:bg-sky-950/60 px-3 py-1 rounded-full border border-sky-200 dark:border-sky-800">
                                    Total: {{ number_format($tpu->totalMakam(), 0, ',', '.') }} Makam
                                </span>
                            </div>

                            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                                <table class="w-full text-left text-sm divide-y divide-slate-200 dark:divide-slate-800">
                                    <thead class="bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">
                                        <tr>
                                            <th class="px-4 py-3 w-12 text-center">No.</th>
                                            <th class="px-4 py-3">Agama</th>
                                            <th class="px-4 py-3 text-center w-36">Jumlah Blok</th>
                                            <th class="px-4 py-3 text-right w-44">Jumlah Makam</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        @forelse($kapasitas as $idx => $k)
                                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                                <td class="px-4 py-3 text-center text-xs text-slate-500 font-bold">{{ $idx + 1 }}</td>
                                                <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">
                                                    {{ $k['agama'] ?? '-' }}
                                                </td>
                                                <td class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">
                                                    <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-semibold">
                                                        {{ $k['jumlah_blok'] ?? '-' }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-right font-black text-slate-900 dark:text-white">
                                                    <span class="inline-block px-2.5 py-0.5 rounded-lg bg-sky-100 dark:bg-sky-900/40 text-sky-800 dark:text-sky-300">
                                                        {{ $k['jumlah_makam'] ?? '-' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-6 text-center text-slate-500 text-xs italic">
                                                    Belum ada data rincian kapasitas blok makam.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="p-12 text-center bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <x-icons.ui name="park" class="w-12 h-12 text-slate-400 dark:text-slate-600 mx-auto mb-3" />
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Belum Ada Data TPU</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Data Taman Pemakaman Umum akan segera dipublikasikan.</p>
                </div>
            @endforelse
        </div>

        {{-- Layanan Terkait Info Box: High Contrast, Solid Color --}}
        <div class="p-6 sm:p-8 rounded-3xl bg-slate-900 text-white shadow-xl border border-slate-800 space-y-4">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-bold">
                        <x-icons.ui name="park" class="w-3.5 h-3.5" />
                        <span>Layanan Terpadu</span>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black text-white">Informasi Pelayanan Ruang Terbuka Hijau & TPU</h3>
                    <p class="text-sm text-slate-300 leading-relaxed max-w-2xl font-normal">
                        Untuk permohonan penyewaan taman kota, pengaduan fasilitas taman, atau pertanyaan seputar TPU Kota Palu, silakan manfaatkan layanan online kami.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="/pinjam-taman" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm transition-colors shadow-sm inline-flex items-center gap-2">
                        <x-icons.ui name="tree" class="w-4 h-4" />
                        <span>Penyewaan Taman</span>
                    </a>
                    <a href="/pengaduan?bidang=rth" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-sm border border-slate-700 transition-colors inline-flex items-center gap-2">
                        <x-icons.ui name="chat" class="w-4 h-4" />
                        <span>Lapor / Pengaduan</span>
                    </a>
                </div>
            </div>
        </div>
    </main>

    {{-- Lightbox Modal (Teleported to Body to Escape Ancestor Transforms & Stacking Context) --}}
    <template x-teleport="body">
        <div
            x-show="lightboxOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-6 bg-slate-950/80 backdrop-blur-md"
            @click.self="closeLightbox()"
            role="dialog"
            aria-modal="true"
            aria-label="Dokumentasi TPU"
        >
            <div
                x-show="lightboxOpen"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full max-w-4xl max-h-[92vh] flex flex-col bg-slate-900 border border-slate-700/80 rounded-3xl shadow-2xl overflow-hidden"
            >
                {{-- Header Bar --}}
                <div class="flex items-center justify-between px-5 py-3.5 bg-slate-900/95 border-b border-slate-800 text-white select-none shrink-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/30">
                            <x-icons.ui name="image" class="w-4 h-4" />
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-white truncate" x-text="lightboxTpuName"></h4>
                            <p class="text-xs text-slate-400 font-medium truncate">
                                <span x-text="currentPhoto().label"></span>
                                <template x-if="lightboxPhotos.length > 1">
                                    <span>&bull; <span x-text="(lightboxIndex + 1) + ' dari ' + lightboxPhotos.length"></span></span>
                                </template>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <template x-if="currentPhoto().url">
                            <a
                                :href="currentPhoto().url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors"
                                title="Buka Gambar Resolusi Penuh"
                            >
                                <x-icons.ui name="external-link" class="w-4 h-4" />
                            </a>
                        </template>
                        <button
                            type="button"
                            @click="closeLightbox()"
                            class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors cursor-pointer"
                            aria-label="Tutup"
                        >
                            <x-icons.ui name="close" class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                {{-- Image Container with Navigation Arrows --}}
                <div class="relative flex-1 min-h-0 bg-black/50 p-3 sm:p-4 flex items-center justify-center overflow-hidden">
                    {{-- Previous button --}}
                    <button
                        type="button"
                        x-show="lightboxPhotos.length > 1"
                        @click="prevPhoto()"
                        class="absolute left-4 top-1/2 -translate-y-1/2 z-10 w-11 h-11 rounded-full bg-slate-900/85 hover:bg-slate-800 text-white border border-slate-700/80 flex items-center justify-center shadow-lg transition-all hover:scale-105 cursor-pointer backdrop-blur-sm focus:outline-none"
                        aria-label="Foto Sebelumnya"
                    >
                        <x-icons.ui name="chevron-left" class="w-5 h-5" />
                    </button>

                    <template x-if="lightboxOpen && currentPhoto()?.url">
                        <img
                            :src="currentPhoto().url"
                            :alt="currentPhoto().label + ' - ' + lightboxTpuName"
                            :key="currentPhoto().url"
                            class="max-h-[70vh] w-auto max-w-full object-contain rounded-xl shadow-2xl select-none"
                        />
                    </template>

                    {{-- Next button --}}
                    <button
                        type="button"
                        x-show="lightboxPhotos.length > 1"
                        @click="nextPhoto()"
                        class="absolute right-4 top-1/2 -translate-y-1/2 z-10 w-11 h-11 rounded-full bg-slate-900/85 hover:bg-slate-800 text-white border border-slate-700/80 flex items-center justify-center shadow-lg transition-all hover:scale-105 cursor-pointer backdrop-blur-sm focus:outline-none"
                        aria-label="Foto Berikutnya"
                    >
                        <x-icons.ui name="chevron-right" class="w-5 h-5" />
                    </button>
                </div>

                {{-- Thumbnail Strip if multiple photos --}}
                <template x-if="lightboxPhotos.length > 1">
                    <div class="px-4 py-2.5 bg-slate-950/90 border-t border-slate-800/80 flex items-center justify-center gap-2 overflow-x-auto">
                        <template x-for="(ph, idx) in lightboxPhotos" :key="idx">
                            <button
                                type="button"
                                @click="lightboxIndex = idx"
                                class="relative h-12 w-16 rounded-lg overflow-hidden border-2 transition-all cursor-pointer shrink-0"
                                :class="lightboxIndex === idx ? 'border-emerald-500 ring-2 ring-emerald-500/30' : 'border-slate-700 opacity-60 hover:opacity-100'"
                            >
                                <img :src="ph.url" :alt="ph.label" class="w-full h-full object-cover" />
                            </button>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
@endsection
