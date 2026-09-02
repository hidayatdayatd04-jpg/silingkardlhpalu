@extends('layouts.app')

@section('title', 'Monitoring Armada Persampahan - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Peta pelacakan GPS armada pengangkut sampah secara real-time dan transparansi data sarana operasional kebersihan Dinas Lingkungan Hidup Kota Palu.')

@php
    $sheetConfig = [
        'Kendaraan Roda 2' => [
            'icon' => 'truck',
            'color' => 'sky',
            'bg' => 'bg-sky-50 dark:bg-sky-950/40',
            'text' => 'text-sky-700 dark:text-sky-300',
            'border' => 'border-sky-200 dark:border-sky-800/60',
            'badge' => 'bg-sky-100 dark:bg-sky-900/40 text-sky-800 dark:text-sky-300',
        ],
        'Kendaraan Roda 4' => [
            'icon' => 'truck',
            'color' => 'emerald',
            'bg' => 'bg-emerald-50 dark:bg-emerald-950/40',
            'text' => 'text-emerald-700 dark:text-emerald-300',
            'border' => 'border-emerald-200 dark:border-emerald-800/60',
            'badge' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300',
        ],
        'Kendaraan Roda 6' => [
            'icon' => 'truck',
            'color' => 'amber',
            'bg' => 'bg-amber-50 dark:bg-amber-950/40',
            'text' => 'text-amber-700 dark:text-amber-300',
            'border' => 'border-amber-200 dark:border-amber-800/60',
            'badge' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300',
        ],
        'Alat Berat' => [
            'icon' => 'excavator',
            'color' => 'rose',
            'bg' => 'bg-rose-50 dark:bg-rose-950/40',
            'text' => 'text-rose-700 dark:text-rose-300',
            'border' => 'border-rose-200 dark:border-rose-800/60',
            'badge' => 'bg-rose-100 dark:bg-rose-900/40 text-rose-800 dark:text-rose-300',
        ],
    ];

    $totalArmada = $totalKeseluruhan ?? 0;

    $allCategoriesData = [];
    foreach ($categories as $catName => $catData) {
        $allCategoriesData[] = [
            'name' => $catName,
            'count' => $catData['count'],
            'items' => $catData['items'],
        ];
    }
@endphp

@push('styles')
<style>
    .monitoring-armada-page {
        position: relative;
        overflow-x: clip;
    }
    .monitoring-armada-page::before {
        content: '';
        position: absolute;
        z-index: -1;
        top: 12rem;
        right: -10rem;
        width: 24rem;
        height: 24rem;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(16, 185, 129, .1), transparent 68%);
        filter: blur(5px);
        pointer-events: none;
    }
    .persampahan-map-frame {
        position: relative;
        border-radius: 1.5rem;
    }
    .persampahan-map-frame::before {
        content: '';
        position: absolute;
        z-index: 1;
        inset: 1px 1px auto;
        height: 4.5rem;
        border-radius: 1.45rem 1.45rem 0 0;
        background: linear-gradient(180deg, rgba(255, 255, 255, .22), transparent);
        pointer-events: none;
    }
    .monitoring-armada-page .map-container {
        position: relative;
        width: 100%;
        height: clamp(30rem, 65vw, 46rem);
        border-radius: 1.5rem;
        overflow: hidden;
        isolation: isolate;
        border: 1px solid rgba(16, 76, 51, .14);
        background: #e7f2eb;
        box-shadow: 0 1px 2px rgba(10, 48, 30, .05), 0 24px 48px -30px rgba(10, 48, 30, .38);
    }
    .dark .monitoring-armada-page .map-container {
        border-color: rgba(110, 231, 183, .2);
        background: #0b241a;
    }
    .monitoring-armada-page .map-container .maplibregl-map {
        width: 100%;
        height: 100%;
    }
    .monitoring-armada-page .map-container .maplibregl-ctrl-group {
        border: 1px solid rgba(16, 76, 51, .12) !important;
        border-radius: 12px !important;
        overflow: hidden;
        box-shadow: 0 10px 24px -12px rgba(10, 48, 30, .35) !important;
    }
    .monitoring-armada-page .map-container .maplibregl-ctrl:not(.dlh-tools-ctrl):not(.dlh-tools-ctrl__item) {
        margin: 12px !important;
    }
    .monitoring-armada-page .map-container .maplibregl-ctrl-top-right {
        top: 12px !important;
        right: 12px !important;
    }
    .monitoring-armada-page .map-container .maplibregl-ctrl-bottom-right {
        bottom: 12px !important;
        right: 12px !important;
    }
    .monitoring-armada-page .map-container .maplibregl-popup-content {
        border: 1px solid rgba(16, 76, 51, .12) !important;
        border-radius: 16px !important;
        box-shadow: 0 18px 40px -16px rgba(10, 48, 30, .34) !important;
        padding: 0 !important;
        overflow: hidden;
    }

    /* Custom vehicle marker icon — persis Gambar 2 (clean floating icon) */
    .custom-vehicle-icon {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        cursor: pointer;
    }
    .custom-vehicle-icon img {
        filter: drop-shadow(0 3px 8px rgba(15, 23, 42, 0.42));
        transition: transform .25s ease, filter .25s ease;
    }
    .custom-vehicle-icon:hover img {
        filter: drop-shadow(0 6px 16px rgba(15, 23, 42, 0.6));
    }

    /* Overlay panel filter di dalam peta */
    .map-floating-overlay {
        position: absolute;
        top: 14px;
        left: 14px;
        z-index: 10;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 16px;
        padding: 6px 10px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 10px -3px rgba(0, 0, 0, 0.05);
        max-width: calc(100% - 80px);
    }
    .dark .map-floating-overlay {
        background: rgba(15, 23, 42, 0.92);
        border-color: rgba(51, 65, 85, 0.8);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
    }
    .map-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        border: 1px solid transparent;
    }
    .map-status-pill.active {
        background: #059669;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(5, 150, 105, 0.3);
    }
    .map-status-pill:not(.active) {
        background: rgba(241, 245, 249, 0.8);
        color: #475569;
        border-color: rgba(226, 232, 240, 0.8);
    }
    .dark .map-status-pill:not(.active) {
        background: rgba(30, 41, 59, 0.8);
        color: #cbd5e1;
        border-color: rgba(51, 65, 85, 0.6);
    }
    .map-status-pill:not(.active):hover {
        background: rgba(226, 232, 240, 1);
        color: #0f172a;
    }
    .dark .map-status-pill:not(.active):hover {
        background: rgba(51, 65, 85, 1);
        color: #ffffff;
    }

    /* Toggle switch armada custom */
    .armada-switch-track {
        width: 36px;
        height: 20px;
        background-color: #10b981;
        border-radius: 9999px;
        position: relative;
        cursor: pointer;
        transition: background-color 0.25s ease;
        display: inline-block;
        flex-shrink: 0;
    }
    .armada-switch-track.off {
        background-color: #cbd5e1 !important;
    }
    .dark .armada-switch-track.off {
        background-color: #475569 !important;
    }
    .armada-switch-thumb {
        width: 16px;
        height: 16px;
        background-color: #ffffff;
        border-radius: 9999px;
        position: absolute;
        top: 2px;
        left: 2px;
        transform: translateX(16px);
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
    }
    .armada-switch-track.off .armada-switch-thumb {
        transform: translateX(0px) !important;
    }

    @media (max-width: 640px) {
        .monitoring-armada-page::before { right: -13rem; }
        .monitoring-armada-page .map-container { height: 26rem; border-radius: 1.25rem; }
        .map-floating-overlay { top: 10px; left: 10px; padding: 5px 8px; max-width: calc(100% - 60px); }
        .map-status-pill { padding: 4px 8px; font-size: 10.5px; }
    }
</style>
@endpush

@section('content')
<div class="monitoring-armada-page space-y-8">
    <x-public.page-hero
        badge="{{ __('Sampah & LB3') }}"
        icon="truck"
        title="{{ __('Monitoring Armada Persampahan') }}"
        description="{{ __('Peta pemantauan GPS armada pengangkut sampah secara real-time dan transparansi data sarana operasional kebersihan Dinas Lingkungan Hidup Kota Palu.') }}"
    />

    {{-- Map Container with Floating Status Filters & Toggle --}}
    <div class="space-y-6">
        <div class="persampahan-map-frame reveal reveal-scale">
            <div class="map-container" role="region" aria-label="Peta pemantauan GPS armada persampahan DLH Kota Palu">
                {{-- Floating Overlay Bar Inside Map (Semua Status, Bergerak, Parkir, Toggle Armada) --}}
                <div class="map-floating-overlay">
                    <button type="button" data-status="all" class="map-status-pill active armada-status-btn">
                        {{ __('Semua Status') }}
                    </button>
                    <button type="button" data-status="active" class="map-status-pill armada-status-btn">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>{{ __('Bergerak / Aktif') }} (<span id="map-total-active">0</span>)</span>
                    </button>
                    <button type="button" data-status="parked" class="map-status-pill armada-status-btn">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>{{ __('Parkir / Standby') }} (<span id="map-total-parked">0</span>)</span>
                    </button>

                    <div class="hidden md:block h-4 w-px bg-slate-200 dark:bg-slate-700 mx-1"></div>

                    {{-- Toggle Sembunyikan/Munculkan Armada Button --}}
                    <button type="button" id="armada-toggle-btn"
                        class="inline-flex items-center gap-2 px-2.5 py-1 rounded-xl bg-slate-100/90 hover:bg-slate-200 dark:bg-slate-800/90 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-[11px] font-bold transition-all cursor-pointer select-none">
                        <span id="armada-toggle-label">{{ __('Sembunyikan Armada') }}</span>
                        <span id="armada-toggle-track" class="armada-switch-track">
                            <span class="armada-switch-thumb"></span>
                        </span>
                    </button>
                </div>

                {{-- MapLibre Container --}}
                <div id="monitoring-armada-map" style="width:100%;height:100%"></div>
            </div>
        </div>
    </div>

    {{-- Data Operasional Sarana & Armada Persampahan --}}
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
        class="space-y-8"
    >
        {{-- KPI Cards Section --}}
        <div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                @foreach($categories as $catName => $catData)
                    @php $cfg = $sheetConfig[$catName] ?? $sheetConfig['Kendaraan Roda 4']; @endphp
                    <div
                        @click="activeCategory = (activeCategory === '{{ $catName }}' ? 'all' : '{{ $catName }}')"
                        :class="activeCategory === '{{ $catName }}' ? 'ring-2 ring-emerald-500 shadow-md transform -translate-y-1' : 'hover:shadow-sm'"
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 flex flex-col items-center text-center cursor-pointer transition-all duration-200"
                    >
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl {{ $cfg['bg'] }} flex items-center justify-center text-{{ $cfg['color'] }}-600 dark:text-{{ $cfg['color'] }}-400 mb-3">
                            <x-icons.ui :name="$cfg['icon']" class="w-5 h-5 sm:w-6 sm:h-6" />
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-white leading-none mb-1">
                            {{ $catData['count'] }}
                        </div>
                        <div class="text-[11px] sm:text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            {{ $catName }}
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Total Bar --}}
            <div class="mt-4 bg-slate-900 dark:bg-slate-800 text-white rounded-2xl p-3.5 sm:p-4 flex items-center justify-center gap-3 shadow-sm border border-slate-800">
                <x-icons.ui name="truck" class="w-5 h-5 text-emerald-400" />
                <span class="text-xs sm:text-sm font-semibold tracking-wide">TOTAL KESELURUHAN ARMADA:</span>
                <span class="text-base sm:text-lg font-black text-emerald-400">{{ $totalArmada }} Unit</span>
            </div>
        </div>

        {{-- Search & Category Filter Section (Persis Gambar 3) --}}
        <div class="space-y-4">
            {{-- Search Bar Rounded-Full Pill Sesuai Gambar 3 --}}
            <div class="relative w-full">
                <div class="relative flex items-center">
                    <div class="absolute left-4 sm:left-5 pointer-events-none text-slate-400 dark:text-slate-500 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <input
                        x-model="search"
                        type="text"
                        placeholder="Cari merek, tipe armada, atau tahun perolehan..."
                        class="w-full h-12 sm:h-14 pl-11 sm:pl-13 pr-10 rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-sm sm:text-base text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                    />
                    <button
                        x-show="search.length > 0"
                        @click="search = ''"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5"
                    >
                        <x-icons.ui name="x" class="w-4 h-4" />
                    </button>
                </div>
            </div>

            {{-- Category Filter Badges & Count Display --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                    <button
                        @click="activeCategory = 'all'"
                        :class="activeCategory === 'all' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800'"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap cursor-pointer"
                    >
                        Semua ({{ $totalArmada }})
                    </button>
                    @foreach($categories as $catName => $catData)
                        <button
                            @click="activeCategory = '{{ $catName }}'"
                            :class="activeCategory === '{{ $catName }}' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap cursor-pointer"
                        >
                            {{ $catName }} ({{ $catData['count'] }})
                        </button>
                    @endforeach
                </div>

                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 self-end sm:self-center">
                    <x-icons.ui name="info-circle" class="w-3.5 h-3.5 text-emerald-500" />
                    <span>Menampilkan <strong class="text-slate-800 dark:text-slate-200 font-bold" x-text="totalFilteredAll()"></strong> unit armada</span>
                </div>
            </div>
        </div>

        {{-- Tables by Category --}}
        <div class="space-y-6">
            @foreach($categories as $catName => $catData)
                @php
                    $cfg = $sheetConfig[$catName] ?? $sheetConfig['Kendaraan Roda 4'];
                    $items = $catData['items'];
                @endphp

                <div
                    x-show="categoryHasMatches(categories.find(c => c.name === '{{ $catName }}'))"
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm transition-all"
                >
                    {{-- Header Table --}}
                    <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ $cfg['bg'] }}">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-white dark:bg-slate-900 flex items-center justify-center text-{{ $cfg['color'] }}-600 dark:text-{{ $cfg['color'] }}-400 shadow-sm">
                                <x-icons.ui :name="$cfg['icon']" class="w-4 h-4 sm:w-5 sm:h-5" />
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    {{ $catName }}
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $cfg['badge'] }} font-semibold">
                                        {{ $catData['count'] }} Unit
                                    </span>
                                </h3>
                            </div>
                        </div>
                    </div>

                    {{-- Table Content --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    <th class="py-3 px-4 w-16 text-center">No</th>
                                    <th class="py-3 px-4">Merk / Tipe Armada</th>
                                    <th class="py-3 px-4 w-40 text-center">Tahun Perolehan</th>
                                    <th class="py-3 px-4 w-32 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                                @forelse($items as $idx => $item)
                                    <tr
                                        x-show="matchesSearch({{ Js::from($item) }})"
                                        class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors"
                                    >
                                        <td class="py-3.5 px-4 text-center text-slate-400 dark:text-slate-500 font-mono text-xs">
                                            {{ $idx + 1 }}
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-100">
                                            {{ $item['merk_type'] ?? '-' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center text-slate-600 dark:text-slate-400">
                                            @if(!empty($item['tahun_perolehan']))
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-mono font-medium">
                                                    <x-icons.ui name="calendar" class="w-3 h-3 text-slate-400" />
                                                    {{ $item['tahun_perolehan'] }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40 text-[11px] font-bold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Operasional
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-slate-400 text-xs sm:text-sm">
                                            Belum ada data armada untuk kategori ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            {{-- Empty Search State --}}
            <div
                x-show="totalFilteredAll() === 0"
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-10 text-center space-y-3"
            >
                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto text-slate-400">
                    <x-icons.ui name="search" class="w-6 h-6" />
                </div>
                <h4 class="text-base font-bold text-slate-800 dark:text-white">Tidak ada data armada yang sesuai</h4>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                    Pencarian untuk "<span x-text="search" class="font-bold text-slate-700 dark:text-slate-300"></span>" tidak menemukan hasil. Coba gunakan kata kunci yang lain.
                </p>
                <button
                    @click="search = ''; activeCategory = 'all'"
                    class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-50 text-emerald-700 font-bold text-xs hover:bg-emerald-100 transition-colors"
                >
                    Reset Filter Pencarian
                </button>
            </div>
        </div>

        {{-- Bottom Information Banner --}}
        <div class="bg-gradient-to-br from-emerald-900 via-teal-900 to-slate-950 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden shadow-sm">
            <div class="relative z-10 max-w-2xl space-y-3">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold backdrop-blur-sm border border-emerald-500/30">
                    <x-icons.ui name="shield-check" class="w-3.5 h-3.5" />
                    Transparansi Pelayanan Publik
                </div>
                <h3 class="text-xl sm:text-2xl font-bold tracking-tight">Kesiapsiagaan Sarana Kebersihan Kota Palu</h3>
                <p class="text-xs sm:text-sm text-emerald-100/80 leading-relaxed">
                    Data armada di atas mencakup unit yang dioperasikan secara berkala oleh Dinas Lingkungan Hidup Kota Palu untuk menjamin kebersihan lingkungan dan kelancaran alur pembuangan sampah dari perumahan hingga TPA Kawatuna.
                </p>
            </div>
            <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none text-slate-600">
                <x-icons.ui name="truck" class="w-72 h-72 text-white" />
            </div>
        </div>
    </div>
</div>

@push('scripts')
@vite('resources/js/map-bundle.js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var initialArmada = @json($gpsArmada ?? []);
        var currentStatus = 'all'; // 'all', 'active', 'parked'
        var activeMarkers = [];
        var mapInstance = null;
        var isArmadaVisible = true;
        var lastVehicleData = initialArmada;

        window.ensureMaplibreLoaded(function () {
            var defaultCenter = [119.8707, -0.9003];
            mapInstance = new maplibregl.Map({
                container: 'monitoring-armada-map',
                style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
                center: defaultCenter,
                zoom: 12.8,
                attributionControl: false
            });

            mapInstance.addControl(new DlhZoomControl(), 'top-right');
            if (window.DlhToolsControl && window.dlhToolsDropdown) mapInstance.addControl(window.dlhToolsDropdown(), 'bottom-left');
            if (window.DlhBasemapSwitcher) mapInstance.addControl(new DlhBasemapSwitcher(), 'bottom-right');

            mapInstance.on('load', function () {
                renderArmada(initialArmada);
                // Polling live GPS armada data every 12 seconds
                setInterval(fetchArmadaLive, 12000);
            });
        });

        function fetchArmadaLive() {
            fetch('/api/armada-aktif')
                .then(function (res) { return res.json(); })
                .then(function (res) {
                    var data = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
                    lastVehicleData = data;
                    renderArmada(data);
                })
                .catch(function (err) {
                    console.debug('Polling armada GPS silent:', err);
                });
        }

        function renderArmada(vehicleData) {
            if (!mapInstance) return;
            var list = Array.isArray(vehicleData) ? vehicleData : [];
            lastVehicleData = list;

            var totalAll = list.length;
            var totalActive = list.filter(function (v) { return parseInt(v.acc) === 1; }).length;
            var totalParked = totalAll - totalActive;

            var elActive = document.getElementById('map-total-active');
            var elParked = document.getElementById('map-total-parked');
            if (elActive) elActive.textContent = totalActive;
            if (elParked) elParked.textContent = totalParked;

            if (!isArmadaVisible) {
                activeMarkers.forEach(function (m) { m.remove(); });
                activeMarkers = [];
                return;
            }

            var filtered = list.filter(function (v) {
                var isActive = (parseInt(v.acc) === 1);
                var statusStr = isActive ? 'active' : 'parked';
                if (currentStatus !== 'all' && currentStatus !== statusStr) return false;
                return true;
            });

            var newImeis = new Set(filtered.map(function (v) { return v.imei; }));

            activeMarkers = activeMarkers.filter(function (m) {
                if (!newImeis.has(m._imei)) {
                    m.remove();
                    return false;
                }
                return true;
            });

            var esc = function (s) { return String(s == null ? '' : s).replace(/</g, '&lt;').replace(/>/g, '&gt;'); };

            filtered.forEach(function (v) {
                var lat = parseFloat(v.latitude), lng = parseFloat(v.longitude);
                if (isNaN(lat) || isNaN(lng)) return;

                var isTruck = (parseInt(v.veh_type) === 4);
                var iconUrl = isTruck ? '/assets/tracking/truck_blue.png' : '/assets/tracking/car_blue.png';
                var angle = parseFloat(v.angle) || 0;
                var isActive = (parseInt(v.acc) === 1);
                var statusText = isActive ? 'Aktif Melayani' : 'Parkir / Mesin Mati';
                var statusColor = isActive ? '#059669' : '#d97706';
                var statusBg = isActive ? '#ecfdf5' : '#fffbeb';

                var popupHtml = '<div style="min-width:210px;padding:14px;font-family:system-ui,-apple-system,sans-serif">'
                    + '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">'
                    + '<div style="width:8px;height:8px;border-radius:50%;background:' + statusColor + '"></div>'
                    + '<h4 style="font-weight:700;font-size:13px;color:#0f172a;margin:0">' + esc(v.title || 'Armada DLH') + '</h4>'
                    + '</div>'
                    + '<div style="margin-bottom:8px;display:inline-block;padding:2px 8px;border-radius:6px;background:' + statusBg + ';color:' + statusColor + ';font-size:11px;font-weight:700">' + statusText + '</div>'
                    + '<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;font-size:11px;color:#475569;border-top:1px solid #f1f5f9;padding-top:6px">'
                    + '<div><span style="color:#94a3b8;font-size:10px;display:block">Kecepatan</span><strong>' + esc(v.speed || '0') + ' km/h</strong></div>'
                    + '<div><span style="color:#94a3b8;font-size:10px;display:block">Tipe</span><strong>' + (isTruck ? 'Truk R6' : 'Pickup R4') + '</strong></div>'
                    + '</div>'
                    + '<div style="margin-top:6px;font-size:10px;color:#94a3b8;border-top:1px solid #f1f5f9;padding-top:4px">Update: ' + esc(v.server_time || '-') + '</div>'
                    + '</div>';

                var existing = activeMarkers.find(function (m) { return m._imei === v.imei; });
                if (existing) {
                    existing.setLngLat([lng, lat]);
                    var img = existing.getElement().querySelector('img');
                    if (img) {
                        img.src = iconUrl;
                        img.style.transform = 'rotate(' + angle + 'deg)';
                    }
                    existing.setPopup(new maplibregl.Popup({ offset: [0, -20], closeButton: true }).setHTML(popupHtml));
                } else {
                    var el = document.createElement('div');
                    el.className = 'custom-vehicle-icon';
                    el.innerHTML = '<img src="' + iconUrl + '" alt="" style="width:36px;height:36px;transform:rotate(' + angle + 'deg);transition:transform 0.3s ease;cursor:pointer" />';

                    var marker = new maplibregl.Marker({ element: el, anchor: 'center' })
                        .setLngLat([lng, lat])
                        .setPopup(new maplibregl.Popup({ offset: [0, -20], closeButton: true }).setHTML(popupHtml))
                        .addTo(mapInstance);
                    marker._imei = v.imei;
                    activeMarkers.push(marker);
                }
            });
        }

        // Status pill button clicks (Semua Status, Bergerak, Parkir)
        document.querySelectorAll('.armada-status-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var st = btn.getAttribute('data-status');
                currentStatus = st;
                document.querySelectorAll('.armada-status-btn').forEach(function (b) {
                    b.classList.toggle('active', b.getAttribute('data-status') === st);
                });
                renderArmada(lastVehicleData);
            });
        });

        // Toggle Armada switch button
        var toggleBtn = document.getElementById('armada-toggle-btn');
        var toggleLabel = document.getElementById('armada-toggle-label');
        var toggleTrack = document.getElementById('armada-toggle-track');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                isArmadaVisible = !isArmadaVisible;

                if (isArmadaVisible) {
                    if (toggleLabel) toggleLabel.textContent = 'Sembunyikan Armada';
                    if (toggleTrack) toggleTrack.classList.remove('off');
                } else {
                    if (toggleLabel) toggleLabel.textContent = 'Munculkan Armada';
                    if (toggleTrack) toggleTrack.classList.add('off');
                }

                renderArmada(lastVehicleData);
            });
        }
    });
</script>
@endpush
@endsection
