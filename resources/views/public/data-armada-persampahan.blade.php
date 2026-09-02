@extends('layouts.app')

@section('title', 'Data Armada Persampahan - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Informasi resmi data armada dan sarana pengangkutan persampahan serta alat berat operasional Dinas Lingkungan Hidup Kota Palu.')

@php
    $sheetConfig = [
        'Kendaraan Roda 2' => [
            'icon' => 'truck',
            'color' => 'sky',
            'bg' => 'bg-sky-50 dark:bg-sky-950/40',
            'text' => 'text-sky-700 dark:text-sky-300',
            'border' => 'border-sky-200 dark:border-sky-800/60',
            'badge' => 'bg-sky-100 dark:bg-sky-900/40 text-sky-800 dark:text-sky-300',
            'desc' => 'Armada roda dua untuk pengangkutan sampah lorong dan penjangkauan jalan lingkungan sempit.',
        ],
        'Kendaraan Roda 4' => [
            'icon' => 'truck',
            'color' => 'emerald',
            'bg' => 'bg-emerald-50 dark:bg-emerald-950/40',
            'text' => 'text-emerald-700 dark:text-emerald-300',
            'border' => 'border-emerald-200 dark:border-emerald-800/60',
            'badge' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300',
            'desc' => 'Armada pick-up dan kendaraan operasional pengumpulan sampah pada rute pemukiman perkotaan.',
        ],
        'Kendaraan Roda 6' => [
            'icon' => 'truck',
            'color' => 'amber',
            'bg' => 'bg-amber-50 dark:bg-amber-950/40',
            'text' => 'text-amber-700 dark:text-amber-300',
            'border' => 'border-amber-200 dark:border-amber-800/60',
            'badge' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300',
            'desc' => 'Dump truck dan truk arm-roll untuk pengangkutan sampah volume besar menuju TPA Kawatuna.',
        ],
        'Alat Berat' => [
            'icon' => 'excavator',
            'color' => 'rose',
            'bg' => 'bg-rose-50 dark:bg-rose-950/40',
            'text' => 'text-rose-700 dark:text-rose-300',
            'border' => 'border-rose-200 dark:border-rose-800/60',
            'badge' => 'bg-rose-100 dark:bg-rose-900/40 text-rose-800 dark:text-rose-300',
            'desc' => 'Ekskavator dan loader pendukung perataan, pemadatan, serta pengelolaan sampah di TPA.',
        ],
    ];

    $allCategoriesData = [];
    foreach ($categories as $catName => $catData) {
        $allCategoriesData[] = [
            'name' => $catName,
            'count' => $catData['count'],
            'items' => $catData['items'],
        ];
    }
@endphp

@section('content')
<div
    x-data="{
        search: '',
        activeCategory: 'all',
        categories: {{ Js::from($allCategoriesData) }},
        matchesSearch(item) {
            if (!this.search.trim()) return true;
            const q = this.search.toLowerCase().trim();
            const merk = (item.merk_type || '').toLowerCase();
            const tahun = (item.tahun_perolehan || '').toLowerCase();
            return merk.includes(q) || tahun.includes(q);
        },
        categoryHasMatches(cat) {
            if (this.activeCategory !== 'all' && this.activeCategory !== cat.name) return false;
            if (!this.search.trim()) return true;
            return (cat.items || []).some(item => this.matchesSearch(item));
        },
        filteredCount(cat) {
            return (cat.items || []).filter(item => this.matchesSearch(item)).length;
        },
        totalFilteredAll() {
            let total = 0;
            this.categories.forEach(cat => {
                if (this.activeCategory === 'all' || this.activeCategory === cat.name) {
                    total += this.filteredCount(cat);
                }
            });
            return total;
        }
    }"
    class="min-h-screen bg-slate-50/70 dark:bg-slate-950 pb-20 antialiased"
>
    {{-- Hero Section --}}
    <section class="relative pt-12 pb-16 md:pt-16 md:pb-20 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-4">
                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold tracking-wide">
                    <x-icons.ui name="recycle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                    <span>Bidang Pengelolaan Sampah & LB3</span>
                </div>

                {{-- Heading --}}
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                    Data Armada Persampahan <span class="text-emerald-700 dark:text-emerald-400">Kota Palu</span>
                </h1>

                {{-- Subtitle --}}
                <p class="text-base sm:text-lg text-slate-700 dark:text-slate-300 leading-relaxed font-normal">
                    Keterbukaan data dan transparansi sarana armada operasional pengangkutan sampah serta alat berat Dinas Lingkungan Hidup Kota Palu.
                </p>
            </div>

            {{-- Stat Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-10 md:mt-12">
                @foreach($categories as $catName => $catData)
                    @php
                        $cfg = $sheetConfig[$catName] ?? ['icon' => 'truck', 'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'];
                    @endphp
                    <button
                        type="button"
                        @click="activeCategory = '{{ $catName }}'"
                        class="p-5 rounded-2xl bg-slate-50/90 dark:bg-slate-800/80 border border-slate-200/90 dark:border-slate-700 shadow-xs text-center transition-all duration-200 hover:-translate-y-1 hover:shadow-md hover:border-emerald-500/50 cursor-pointer focus:outline-none"
                        :class="activeCategory === '{{ $catName }}' ? 'ring-2 ring-emerald-500 border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/30' : ''"
                    >
                        <div class="w-11 h-11 mx-auto rounded-xl {{ $cfg['bg'] }} {{ $cfg['text'] }} flex items-center justify-center mb-2.5">
                            <x-icons.ui :name="$cfg['icon']" class="w-5 h-5" />
                        </div>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ number_format($catData['count']) }}</p>
                        <p class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mt-1">{{ $catName }}</p>
                    </button>
                @endforeach
            </div>

            {{-- Total Keseluruhan Banner (High Contrast) --}}
            <div class="mt-8 flex justify-center">
                <div class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-slate-900 dark:bg-slate-800 text-white shadow-lg border border-slate-800 dark:border-slate-700">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <x-icons.ui name="truck" class="w-4 h-4 text-emerald-400" />
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Total Keseluruhan Armada:</span>
                        <span class="text-lg font-black text-emerald-400">{{ number_format($totalKeseluruhan) }} Unit</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 space-y-8">
        {{-- Search & Filter Tabs --}}
        <div class="space-y-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                {{-- Search Bar --}}
                <div class="relative flex-1 w-full max-w-xl">
                    <div class="relative flex items-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all focus-within:border-emerald-500 focus-within:ring-4 focus-within:ring-emerald-500/10 hover:border-slate-300 dark:hover:border-slate-700">
                        <div class="flex items-center justify-center pl-4 pr-2 text-slate-400 dark:text-slate-500 pointer-events-none">
                            <x-icons.ui name="search" class="w-5 h-5 text-slate-400 dark:text-slate-500" />
                        </div>
                        <input
                            type="text"
                            x-model="search"
                            placeholder="Cari merek, tipe armada, atau tahun perolehan..."
                            class="w-full py-3.5 pr-10 bg-transparent text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none font-medium"
                        >
                        <button
                            x-show="search.length > 0"
                            @click="search = ''"
                            type="button"
                            class="absolute right-3 p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                            title="Hapus pencarian"
                        >
                            <x-icons.ui name="close" class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                {{-- Quick Filter Scope Note --}}
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                    <x-icons.ui name="info-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                    <span>Menampilkan <strong class="text-slate-800 dark:text-slate-100" x-text="totalFilteredAll()"></strong> unit armada</span>
                </div>
            </div>

            {{-- Category Filter Pills --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
                <button
                    type="button"
                    @click="activeCategory = 'all'"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap cursor-pointer focus:outline-none"
                    :class="activeCategory === 'all'
                        ? 'bg-emerald-600 text-white shadow-sm'
                        : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-emerald-500/50'"
                >
                    <span>Semua Kategori</span>
                    <span
                        class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                        :class="activeCategory === 'all' ? 'bg-emerald-700 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                    >
                        {{ $totalKeseluruhan }}
                    </span>
                </button>

                @foreach($categories as $catName => $catData)
                    <button
                        type="button"
                        @click="activeCategory = '{{ $catName }}'"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap cursor-pointer focus:outline-none"
                        :class="activeCategory === '{{ $catName }}'
                            ? 'bg-emerald-600 text-white shadow-sm'
                            : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-emerald-500/50'"
                    >
                        <span>{{ $catName }}</span>
                        <span
                            class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                            :class="activeCategory === '{{ $catName }}' ? 'bg-emerald-700 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                        >
                            {{ $catData['count'] }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Category Card Sections --}}
        <div class="space-y-8">
            @foreach($categories as $catName => $catData)
                @php
                    $cfg = $sheetConfig[$catName] ?? ['icon' => 'truck', 'color' => 'emerald', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'desc' => ''];
                    $items = $catData['items'];
                    $unitCount = $catData['count'];
                @endphp

                <div
                    x-show="categoryHasMatches(categories.find(c => c.name === '{{ $catName }}'))"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm overflow-hidden"
                >
                    {{-- Card Header --}}
                    <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-12 h-12 rounded-2xl {{ $cfg['bg'] }} {{ $cfg['text'] }} flex items-center justify-center shadow-xs shrink-0">
                                    <x-icons.ui :name="$cfg['icon']" class="w-6 h-6" />
                                </div>
                                <div>
                                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        {{ $catName }}
                                    </h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-normal">
                                        {{ $cfg['desc'] }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-start sm:self-auto">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl {{ $cfg['bg'] }} {{ $cfg['text'] }} text-xs font-extrabold border {{ $cfg['border'] ?? 'border-slate-200 dark:border-slate-700' }}">
                                    <x-icons.ui :name="$cfg['icon']" class="w-4 h-4" />
                                    Total {{ $catName }}: {{ number_format($unitCount) }} Unit
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Data Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    <th class="w-16 px-5 py-3.5 text-center">NO</th>
                                    <th class="px-5 py-3.5">MEREK / TYPE</th>
                                    <th class="w-52 px-5 py-3.5">TAHUN PEROLEHAN</th>
                                    <th class="w-48 px-5 py-3.5 text-center">STATUS OPERASIONAL</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                @forelse($items as $i => $item)
                                    <tr
                                        x-show="matchesSearch({{ Js::from($item) }})"
                                        class="transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-800/30"
                                    >
                                        <td class="px-5 py-3.5 text-center font-bold text-slate-400 text-xs">
                                            {{ $i + 1 }}
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                                <span class="font-bold text-slate-900 dark:text-slate-100">
                                                    {{ $item['merk_type'] ?? '-' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300 font-medium text-xs">
                                                <x-icons.ui name="calendar" class="w-4 h-4 text-slate-400" />
                                                {{ $item['tahun_perolehan'] ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3.5 text-center">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold">
                                                <x-icons.ui name="check-circle" class="w-3.5 h-3.5" />
                                                Operasional DLH
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400">
                                            Belum ada data unit armada yang tercatat pada kategori ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Card Footer --}}
                    <div class="px-5 py-3.5 bg-slate-50/80 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <span>Unit armada siap melayani kebersihan dan pengangkutan wilayah Kota Palu.</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">Total {{ $catName }}: {{ number_format($unitCount) }} Unit</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- No Search Results State --}}
        <div
            x-show="totalFilteredAll() === 0 && search.trim().length > 0"
            x-cloak
            class="p-12 text-center rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3"
        >
            <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center">
                <x-icons.ui name="search" class="w-7 h-7" />
            </div>
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Tidak ada armada ditemukan</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                Tidak ada data armada yang cocok dengan kata kunci "<span class="font-bold text-slate-700 dark:text-slate-300" x-text="search"></span>". Coba kata kunci lainnya.
            </p>
            <button
                type="button"
                @click="search = ''; activeCategory = 'all'"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold shadow-xs hover:bg-emerald-700 transition-colors"
            >
                Reset Pencarian
            </button>
        </div>

        {{-- Bottom Public CTA & Service Links (Solid High Contrast) --}}
        <div class="p-6 sm:p-10 rounded-3xl bg-slate-900 text-white shadow-xl border border-slate-800 relative overflow-hidden space-y-4">
            <div class="relative z-10 max-w-2xl space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-bold">
                    <x-icons.ui name="truck" class="w-3.5 h-3.5" />
                    <span>Layanan Kebersihan Kota Palu</span>
                </div>
                <h3 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    Wujudkan Kota Palu Bersih, Sehat, dan Berkelanjutan
                </h3>
                <p class="text-sm sm:text-base text-slate-300 leading-relaxed font-normal">
                    Masyarakat dapat memantau sebaran titik TPS atau menyampaikan laporan dan pengaduan timbunan sampah liar melalui kanal resmi SILINGKAR DLH Kota Palu.
                </p>
                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <a
                        href="/peta-persampahan"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm transition-colors shadow-sm inline-flex items-center gap-2"
                    >
                        <x-icons.ui name="route" class="w-4 h-4 text-white" />
                        <span>Jalur Angkut</span>
                    </a>
                    <a
                        href="/pengaduan?bidang=sampah"
                        class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-sm border border-slate-700 transition-colors inline-flex items-center gap-2"
                    >
                        <x-icons.ui name="megaphone" class="w-4 h-4 text-slate-300" />
                        <span>Pengaduan Sampah</span>
                    </a>
                </div>
            </div>

            <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none text-slate-600">
                <x-icons.ui name="truck" class="w-72 h-72 text-white" />
            </div>
        </div>
    </main>
</div>
@endsection
