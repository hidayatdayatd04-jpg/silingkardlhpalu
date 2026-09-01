@extends('layouts.app')

@section('title', 'Taman Pemakaman Umum (TPU) Kota Palu - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Informasi resmi data Taman Pemakaman Umum (TPU), kapasitas daya tampung blok makam, serta vegetasi pohon pelindung di wilayah Kota Palu.')

@section('content')
<div
    x-data="{
        search: '',
        activeTab: {},
        lightboxOpen: false,
        lightboxImage: '',
        lightboxTitle: '',
        openLightbox(img, title) {
            this.lightboxImage = img;
            this.lightboxTitle = title;
            this.lightboxOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        closeLightbox() {
            this.lightboxOpen = false;
            document.body.classList.remove('overflow-hidden');
        },
        getTab(tpuId) {
            return this.activeTab[tpuId] || 'overview';
        },
        setTab(tpuId, tab) {
            this.activeTab[tpuId] = tab;
        }
    }"
    x-on:keydown.escape.window="closeLightbox()"
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
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ $totalTpu }}</p>
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
                    <p class="text-2xl sm:text-3xl font-black text-sky-700 dark:text-sky-400 tracking-tight">{{ number_format($totalMakam) }}</p>
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mt-1">Total Makam</p>
                </div>

                <div class="p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-800/80 border border-slate-200/90 dark:border-slate-700 shadow-sm text-center">
                    <div class="w-11 h-11 mx-auto rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 flex items-center justify-center mb-2.5">
                        <x-icons.ui name="seedling" class="w-5 h-5" />
                    </div>
                    <p class="text-2xl sm:text-3xl font-black text-amber-700 dark:text-amber-400 tracking-tight">{{ number_format($totalPohon) }}</p>
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mt-1">Pohon Pelindung</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Content Section --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 space-y-8">
        {{-- Search Input --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="relative w-full sm:w-96">
                <input
                    type="text"
                    x-model="search"
                    placeholder="Cari nama TPU (contoh: Lambara, Poboya)..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl text-sm border border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-medium"
                />
                <x-icons.ui name="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
            </div>

            <div class="text-xs font-semibold text-slate-600 dark:text-slate-400 flex items-center gap-2 self-start sm:self-auto">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span>Data resmi dikelola oleh Bidang Ruang Terbuka Hijau (RTH)</span>
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
                                    <span>{{ number_format($tpu->totalMakam()) }} Makam</span>
                                </span>

                                {{-- Badge Vegetasi --}}
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-50 dark:bg-teal-950/60 border border-teal-200 dark:border-teal-800 text-teal-900 dark:text-teal-300 text-xs font-bold">
                                    <x-icons.ui name="seedling" class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400" />
                                    <span>{{ $tpu->totalPohon() }} Pohon Pelindung</span>
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
                                Dokumentasi Lapangan {{ $tpu->nama_tpu }}
                            </h3>

                            @if(count($photos) > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    @foreach($photos as $idx => $p)
                                        <div class="space-y-1.5">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $p['label'] }}</span>
                                            <div
                                                class="aspect-video rounded-2xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 relative group cursor-pointer bg-slate-100 dark:bg-slate-800"
                                                @click="openLightbox('{{ $p['url'] }}', '{{ $p['label'] }} - {{ $tpu->nama_tpu }}')"
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
                                    Total: {{ $tpu->totalPohon() }} Pohon
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
                                    Total: {{ number_format($tpu->totalMakam()) }} Makam
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
            <div class="flex items-center justify-between px-4 py-3 bg-slate-800 text-white border-b border-slate-700">
                <span class="text-sm font-bold truncate" x-text="lightboxTitle"></span>
                <button @click="closeLightbox()" class="p-1.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white transition-colors cursor-pointer">
                    <x-icons.ui name="close" class="w-5 h-5" />
                </button>
            </div>
            <div class="p-2 flex items-center justify-center max-h-[calc(90vh-60px)]">
                <img :src="lightboxImage" alt="Preview Foto Dokumentasi TPU" class="max-h-[calc(90vh-80px)] max-w-full object-contain rounded-lg" />
            </div>
        </div>
    </div>
</div>
@endsection
