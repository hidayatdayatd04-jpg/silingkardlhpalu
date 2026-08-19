<div class="col-span-12">
    {{-- Header Section --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <span class="grid size-10 place-items-center rounded-2xl bg-brand-500/10 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
                <x-admin.icon name="map-pin" :size="22" />
            </span>
            <div>
                <h2 class="text-xl font-extrabold tracking-tight text-ink-900 dark:text-white sm:text-2xl">
                    Sebaran Pengaduan Masyarakat
                </h2>
                <p class="text-xs text-ink-500 dark:text-ink-400">
                    Visualisasi spasial lokasi laporan & pengaduan lingkungan hidup Kota Palu
                </p>
            </div>
        </div>

        {{-- Live indicator & summary counters --}}
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1.5 font-medium text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                <span class="relative flex size-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                </span>
                <span>Pembaruan Real-time</span>
            </div>
            <div class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                <span class="size-2 rounded-full bg-brand-500"></span>
                <span>Terpetakan: <b id="sp-total-badge" class="text-brand-600 dark:text-brand-400">{{ count($mapReports ?? []) }}</b></span>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <x-admin.card :padding="false" class="overflow-hidden border border-slate-200/90 shadow-xl shadow-slate-900/5 dark:border-slate-800 dark:shadow-black/20">
        {{-- Advanced Filter Toolbar --}}
        <div class="border-b border-slate-100 bg-slate-50/70 p-4 sm:p-5 dark:border-slate-800 dark:bg-slate-900/60">
            <div class="flex flex-col gap-4">
                {{-- Baris 1: Filter Bidang, Status & Pencarian --}}
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
                    {{-- Pencarian --}}
                    <div class="lg:col-span-4">
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Cari Laporan
                        </label>
                        <div class="relative flex items-center">
                            <x-admin.icon name="search" :size="16" class="pointer-events-none absolute left-3 text-slate-400" />
                            <input
                                type="text"
                                id="sp-search"
                                placeholder="Cari tiket, pelapor, alamat..."
                                class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-3 text-xs font-medium text-slate-800 transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            />
                        </div>
                    </div>

                    {{-- Filter Bidang --}}
                    <div class="lg:col-span-3">
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Bidang Dinas
                        </label>
                        <div class="relative">
                            <select id="sp-bidang" class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                <option value="all">Semua Bidang (4 Bidang)</option>
                                <option value="pengendalian">🔴 Pengendalian Dampak Lingkungan</option>
                                <option value="sampah-lb3">🔵 Pengelolaan Sampah & LB3</option>
                                <option value="rth">🟢 Ruang Terbuka Hijau (RTH)</option>
                                <option value="tata-penataan">🟣 Tata Penataan Lingkungan</option>
                            </select>
                            <x-admin.icon name="chevron-down" :size="14" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>

                    {{-- Filter Status --}}
                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Status
                        </label>
                        <div class="relative">
                            <select id="sp-status" class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                <option value="all">Semua Status</option>
                                <option value="Belum Ditindaklanjuti">⏳ Belum Ditindaklanjuti</option>
                                <option value="Ditindaklanjuti">✅ Ditindaklanjuti</option>
                            </select>
                            <x-admin.icon name="chevron-down" :size="14" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>

                    {{-- Date Pickers --}}
                    <div class="lg:col-span-3">
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Rentang Tanggal
                        </label>
                        <div class="flex items-center gap-1.5">
                            <div class="relative flex-1">
                                <input type="text" id="sp-from" value="{{ $from ?? now()->startOfMonth()->toDateString() }}" class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-2.5 pr-7 text-[11px] font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" readonly />
                                <x-admin.icon name="calendar" :size="13" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-slate-400" />
                            </div>
                            <span class="text-xs text-slate-400">-</span>
                            <div class="relative flex-1">
                                <input type="text" id="sp-to" value="{{ $to ?? now()->endOfMonth()->toDateString() }}" class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-2.5 pr-7 text-[11px] font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" readonly />
                                <x-admin.icon name="calendar" :size="13" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-slate-400" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Baris 2: Preset Chips & Reset Action --}}
                <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-200/60 dark:border-slate-800/80">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mr-1">Preset:</span>
                        <button type="button" class="sp-chip" data-preset="today">Hari Ini</button>
                        <button type="button" class="sp-chip" data-preset="7">7 Hari</button>
                        <button type="button" class="sp-chip" data-preset="30">30 Hari</button>
                        <button type="button" class="sp-chip is-active" data-preset="month">Bulan Ini</button>
                        <button type="button" class="sp-chip" data-preset="all">Semua Waktu</button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button id="sp-refresh" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                            <x-admin.icon name="rotate-cw" :size="13" />
                            <span>Refresh</span>
                        </button>
                        <button id="sp-reset" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-500 transition hover:bg-slate-50 hover:text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700">
                            <span>Reset Filter</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Map + Interactive Sidebar Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-100 dark:divide-slate-800">
            {{-- Map Viewport (8 Kolom) --}}
            <div class="relative lg:col-span-8 h-[540px] sm:h-[600px] w-full bg-slate-100 dark:bg-slate-950 overflow-hidden">
                <div id="sebaran-pengaduan-map" class="w-full h-full"></div>

                {{-- Floating Legend Overlay --}}
                <div class="sp-map-legend">
                    <p class="sp-legend-heading">Kategori Bidang</p>
                    <div class="space-y-1.5">
                        <div class="sp-legend-item" data-filter-bidang="pengendalian">
                            <span class="size-3 rounded-full bg-[#ef4444] shadow-sm shadow-red-500/40"></span>
                            <span class="sp-legend-text">Pengendalian</span>
                            <span id="sp-count-pdl" class="sp-legend-badge">0</span>
                        </div>
                        <div class="sp-legend-item" data-filter-bidang="sampah-lb3">
                            <span class="size-3 rounded-full bg-[#0284c7] shadow-sm shadow-sky-500/40"></span>
                            <span class="sp-legend-text">Sampah & LB3</span>
                            <span id="sp-count-smp" class="sp-legend-badge">0</span>
                        </div>
                        <div class="sp-legend-item" data-filter-bidang="rth">
                            <span class="size-3 rounded-full bg-[#10b981] shadow-sm shadow-emerald-500/40"></span>
                            <span class="sp-legend-text">RTH</span>
                            <span id="sp-count-rth" class="sp-legend-badge">0</span>
                        </div>
                        <div class="sp-legend-item" data-filter-bidang="tata-penataan">
                            <span class="size-3 rounded-full bg-[#8b5cf6] shadow-sm shadow-purple-500/40"></span>
                            <span class="sp-legend-text">Tata Penataan</span>
                            <span id="sp-count-ttp" class="sp-legend-badge">0</span>
                        </div>
                    </div>
                </div>

                {{-- Map Quick Actions Overlay (Center on Palu, Reset Zoom) --}}
                <div class="sp-map-actions">
                    <button id="sp-btn-fit" title="Fokuskan Semua Marker" class="sp-map-action-btn">
                        <x-admin.icon name="maximize" :size="16" />
                    </button>
                    <button id="sp-btn-palu" title="Pusat Kota Palu" class="sp-map-action-btn">
                        <x-admin.icon name="map-pin" :size="16" />
                    </button>
                </div>
            </div>

            {{-- Interactive Report Feed / List (4 Kolom) --}}
            <div class="lg:col-span-4 flex flex-col h-[540px] sm:h-[600px] bg-white dark:bg-slate-900">
                {{-- List Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3.5 bg-slate-50/50 dark:border-slate-800 dark:bg-slate-800/40">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                            Daftar Laporan Terpetakan
                        </h3>
                        <p id="sp-feed-subtitle" class="text-[11px] text-slate-500 dark:text-slate-400">
                            Klik item untuk menuju ke titik lokasi
                        </p>
                    </div>
                    <span id="sp-feed-count" class="rounded-full bg-brand-500/10 px-2.5 py-0.5 text-xs font-bold text-brand-600 dark:bg-brand-400/10 dark:text-brand-400">
                        {{ count($mapReports ?? []) }}
                    </span>
                </div>

                {{-- Scrollable List Container --}}
                <div id="sp-report-list" class="flex-1 overflow-y-auto divide-y divide-slate-100 p-2 dark:divide-slate-800">
                    {{-- Dynamically rendered via JS --}}
                </div>

                {{-- List Footer Status --}}
                <div class="border-t border-slate-100 p-3 bg-slate-50/70 text-[11px] text-slate-500 flex items-center justify-between dark:border-slate-800 dark:bg-slate-900">
                    <span id="sp-range-display">Bulan Ini</span>
                    <span class="text-slate-400">Peta GIS DLH Palu</span>
                </div>
            </div>
        </div>
    </x-admin.card>
</div>

@push('styles')
<style>
    /* Custom Chip Button */
    .sp-chip {
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 9999px;
        padding: 4px 12px;
        cursor: pointer;
        transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .sp-chip:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        color: #0f172a;
    }
    .dark .sp-chip {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
    }
    .dark .sp-chip:hover {
        background: #334155;
        color: #f1f5f9;
    }
    .sp-chip.is-active {
        background: #059669 !important;
        border-color: #059669 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px -2px rgba(5, 150, 105, 0.4);
    }

    /* Floating Legend */
    .sp-map-legend {
        position: absolute;
        bottom: 10px;
        left: 10px;
        z-index: 20;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 16px;
        padding: 12px 14px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.18);
        min-width: 175px;
    }
    .dark .sp-map-legend {
        background: rgba(15, 23, 42, 0.92);
        border-color: rgba(51, 65, 85, 0.8);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
    }
    .sp-legend-heading {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        margin-bottom: 8px;
    }
    .dark .sp-legend-heading { color: #94a3b8; }
    .sp-legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 3px 6px;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.12s ease;
    }
    .sp-legend-item:hover { background: rgba(0, 0, 0, 0.05); }
    .dark .sp-legend-item:hover { background: rgba(255, 255, 255, 0.08); }
    .sp-legend-text {
        font-size: 11px;
        font-weight: 600;
        color: #334155;
        flex: 1;
    }
    .dark .sp-legend-text { color: #cbd5e1; }
    .sp-legend-badge {
        font-size: 10px;
        font-weight: 800;
        background: #f1f5f9;
        color: #475569;
        padding: 1px 6px;
        border-radius: 9999px;
    }
    .dark .sp-legend-badge { background: #334155; color: #94a3b8; }

    /* Map Quick Actions Overlay */
    .sp-map-actions {
        position: absolute;
        top: 57px;
        left: 10px;
        z-index: 20;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .sp-map-action-btn {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(226, 232, 240, 0.9);
        color: #334155;
        display: grid;
        place-items: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .sp-map-action-btn:hover {
        background: #ffffff;
        color: #059669;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    }
    .dark .sp-map-action-btn {
        background: rgba(15, 23, 42, 0.92);
        border-color: rgba(51, 65, 85, 0.8);
        color: #cbd5e1;
    }
    .dark .sp-map-action-btn:hover {
        background: #1e293b;
        color: #34d399;
    }

    /* Custom Popup Styles */
    .sp-popup-card {
        padding: 4px;
        font-family: inherit;
        max-width: 290px;
    }
    .maplibregl-popup-content {
        padding: 38px 14px 14px 14px !important;
        border-radius: 18px !important;
        box-shadow: 0 18px 40px -12px rgba(15, 23, 42, 0.3) !important;
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
        position: relative !important;
    }
    .maplibregl-popup-close-button {
        position: absolute !important;
        top: 10px !important;
        right: 10px !important;
        width: 24px !important;
        height: 24px !important;
        border-radius: 50% !important;
        background: #f1f5f9 !important;
        border: 1px solid #e2e8f0 !important;
        color: #64748b !important;
        font-size: 14px !important;
        line-height: 1 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: background 0.15s ease, color 0.15s ease !important;
        padding: 0 !important;
    }
    .maplibregl-popup-close-button:hover {
        background: #e2e8f0 !important;
        color: #0f172a !important;
    }
    .dark .maplibregl-popup-content {
        background: #0f172a !important;
        color: #f1f5f9 !important;
        border-color: #334155 !important;
    }
    .dark .maplibregl-popup-close-button {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #94a3b8 !important;
    }
    .dark .maplibregl-popup-close-button:hover {
        background: #334155 !important;
        color: #f1f5f9 !important;
    }


    /* Pulse animation for pending marker */
    .sp-marker-pulse {
        position: absolute;
        inset: -4px;
        border-radius: 9999px;
        opacity: 0.6;
        animation: sp-ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
    }
    @keyframes sp-ping {
        75%, 100% {
            transform: scale(1.6);
            opacity: 0;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!document.getElementById('sebaran-pengaduan-map')) return;

    var initialReports = {!! json_encode($mapReports ?? []) !!};
    var currentReports = [].concat(initialReports);
    var currentFrom = '{{ $from ?? now()->startOfMonth()->toDateString() }}';
    var currentTo = '{{ $to ?? now()->endOfMonth()->toDateString() }}';

    var BIDANG_COLORS = {
        'pengendalian': '#ef4444',
        'sampah-lb3': '#0284c7',
        'rth': '#10b981',
        'tata-penataan': '#8b5cf6'
    };

    var BIDANG_SVGS = {
        'pengendalian': '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        'sampah-lb3': '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>',
        'rth': '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22v-8"/><path d="M8 14l4-10 4 10"/><path d="M5 18l7-6 7 6"/></svg>',
        'tata-penataan': '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>'
    };

    var map = null;
    var markersMap = {}; // id -> MapLibre marker
    var activeMarkers = [];

    // Helper: Algoritma dispersi spiral untuk koordinat bertumpuk identik
    function disperseCoordinates(reports) {
        var coordGroups = {};
        reports.forEach(function (r) {
            var key = parseFloat(r.latitude).toFixed(5) + ',' + parseFloat(r.longitude).toFixed(5);
            if (!coordGroups[key]) coordGroups[key] = [];
            coordGroups[key].push(r);
        });

        var processed = [];
        Object.keys(coordGroups).forEach(function (key) {
            var list = coordGroups[key];
            if (list.length === 1) {
                processed.push(list[0]);
            } else {
                // Ada lebih dari 1 laporan di titik yang sama -> sebar perlahan membentuk lingkaran
                var angleStep = (2 * Math.PI) / list.length;
                var radius = 0.00032; // ~35 meter radius
                list.forEach(function (item, idx) {
                    var angle = idx * angleStep;
                    var copy = Object.assign({}, item);
                    copy.displayLat = parseFloat(item.latitude) + (radius * Math.sin(angle));
                    copy.displayLng = parseFloat(item.longitude) + (radius * Math.cos(angle));
                    processed.push(copy);
                });
            }
        });
        return processed;
    }

    function createMarkerElement(report) {
        var color = BIDANG_COLORS[report.bidang] || '#10b981';
        var svg = BIDANG_SVGS[report.bidang] || '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>';
        var isPending = report.status === 'Belum Ditindaklanjuti';

        var el = document.createElement('div');
        el.className = 'sp-marker-node relative cursor-pointer';
        el.style.width = '34px';
        el.style.height = '34px';

        var pulseHtml = isPending ? '<span class="sp-marker-pulse" style="background: ' + color + ';"></span>' : '';

        el.innerHTML = [
            pulseHtml,
            '<div class="relative w-full h-full rounded-full flex items-center justify-center text-white shadow-lg transition-transform duration-200 hover:scale-110" style="background: ' + color + '; border: 2.5px solid white; box-shadow: 0 4px 12px ' + color + '66;">',
            svg,
            '</div>'
        ].join('');

        return el;
    }

    function createPopupHTML(r) {
        var color = BIDANG_COLORS[r.bidang] || '#10b981';
        var statusBg = r.status === 'Ditindaklanjuti' ? 'background: rgba(16, 185, 129, 0.15); color: #059669;' : 'background: rgba(245, 158, 11, 0.15); color: #d97706;';

        var waButton = r.wa_url ? [
            '<a href="' + r.wa_url + '" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 hover:underline">',
            '<span>No Telepon: ' + (r.nomor_hp || '') + '</span>',
            '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>',
            '</a>'
        ].join('') : '';

        var thumbHtml = r.foto_thumb ? [
            '<div class="mt-2 mb-2 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 max-h-24 bg-slate-100">',
            '<img src="' + r.foto_thumb + '" alt="Foto Pengaduan" class="w-full h-24 object-cover cursor-pointer" onclick="window.open(\'' + r.foto_thumb + '\', \'_blank\')" />',
            '</div>'
        ].join('') : '';

        return [
            '<div class="sp-popup-card">',
            '  <div class="flex items-center justify-between gap-2 mb-1.5">',
            '    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md text-white" style="background: ' + color + ';">' + (r.bidang_label || r.bidang) + '</span>',
            '    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="' + statusBg + '">' + (r.status_label || r.status) + '</span>',
            '  </div>',
            '  <h4 class="font-mono text-xs font-extrabold text-slate-900 dark:text-white">' + r.nomor_tiket + '</h4>',
            '  <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 mt-0.5">' + (r.jenis_label || r.jenis_pengaduan || '-') + '</p>',
            thumbHtml,
            '  <div class="mt-1.5 space-y-1 text-[11px] text-slate-600 dark:text-slate-300">',
            '    <div class="flex items-start gap-1.5">',
            '      <span class="flex-shrink-0 mt-0.5 text-slate-400"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>',
            '      <span>' + (r.nama_pelapor || 'Masyarakat') + ' ' + waButton + '</span>',
            '    </div>',
            '    <div class="flex items-start gap-1.5">',
            '      <span class="flex-shrink-0 mt-0.5 text-slate-400"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></span>',
            '      <span class="line-clamp-2">' + (r.alamat || 'Alamat tidak diisi') + '</span>',
            '    </div>',
            '    <div class="flex items-start gap-1.5">',
            '      <span class="flex-shrink-0 mt-0.5 text-slate-400"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></span>',
            '      <span>' + (r.tanggal || '-') + '</span>',
            '    </div>',
            '  </div>',
            r.deskripsi ? '<p class="mt-2 text-[11px] text-slate-500 italic bg-slate-50 dark:bg-slate-800/80 p-2 rounded-lg border border-slate-100 dark:border-slate-800 line-clamp-2">“' + r.deskripsi + '”</p>' : '',
            '  <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end">',
            '    <a href="' + (r.detail_url || '#') + '" class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-lg text-white transition hover:opacity-90" style="background: ' + color + ';">',
            '      <span>Tindaklanjuti Laporan</span>',
            '      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>',
            '    </a>',
            '  </div>',
            '</div>'
        ].join('');
    }

    function renderFeedList(reports) {
        var listEl = document.getElementById('sp-report-list');
        var feedCountEl = document.getElementById('sp-feed-count');
        if (!listEl) return;

        if (feedCountEl) feedCountEl.textContent = reports.length;

        if (!reports || !reports.length) {
            listEl.innerHTML = [
                '<div class="flex flex-col items-center justify-center h-64 text-center p-6 text-slate-400">',
                '  <span class="grid size-12 place-items-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 mb-3">',
                '    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>',
                '  </span>',
                '  <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Tidak ada pengaduan terpetakan</p>',
                '  <p class="text-[11px] text-slate-400 mt-1">Coba sesuaikan filter bidang, status, atau rentang tanggal.</p>',
                '</div>'
            ].join('');
            return;
        }

        var html = reports.map(function (r) {
            var color = BIDANG_COLORS[r.bidang] || '#10b981';
            var isPending = r.status === 'Belum Ditindaklanjuti';
            var statusBadge = isPending
                ? '<span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-600 dark:text-amber-400"><span class="size-1.5 rounded-full bg-amber-500"></span>Belum Ditindak</span>'
                : '<span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400"><span class="size-1.5 rounded-full bg-emerald-500"></span>Selesai</span>';

            return [
                '<div class="sp-report-item p-3 transition rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer group" data-report-id="' + r.id + '">',
                '  <div class="flex items-start justify-between gap-2">',
                '    <div class="min-w-0 flex-1">',
                '      <div class="flex items-center gap-2 mb-1">',
                '        <span class="size-2 rounded-full" style="background: ' + color + ';"></span>',
                '        <span class="font-mono text-xs font-bold text-slate-900 group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-400">' + r.nomor_tiket + '</span>',
                '        <span class="text-[10px] text-slate-400">• ' + (r.bidang_label || r.bidang) + '</span>',
                '      </div>',
                '      <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">' + (r.jenis_label || r.jenis_pengaduan || '-') + '</p>',
                '      <p class="flex items-center gap-1 text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>' + (r.alamat || 'Alamat tidak diisi') + '</p>',
                '    </div>',
                '    <div class="shrink-0 flex flex-col items-end gap-1.5">',
                statusBadge,
                '      <span class="text-[10px] text-slate-400">' + (r.tanggal || '').split(' ')[0] + '</span>',
                '    </div>',
                '  </div>',
                '</div>'
            ].join('');
        }).join('');

        listEl.innerHTML = html;

        // Pasang event klik pada list item untuk flyTo map marker
        listEl.querySelectorAll('.sp-report-item').forEach(function (item) {
            item.addEventListener('click', function () {
                var reportId = item.getAttribute('data-report-id');
                var marker = markersMap[reportId];
                if (marker && map) {
                    var lngLat = marker.getLngLat();
                    map.flyTo({ center: [lngLat.lng, lngLat.lat], zoom: 15, duration: 1200 });
                    marker.togglePopup();
                }
            });
        });
    }

    function updateLegendCounters(reports) {
        var counts = { pengendalian: 0, 'sampah-lb3': 0, rth: 0, 'tata-penataan': 0 };
        (reports || []).forEach(function (r) {
            if (counts[r.bidang] !== undefined) counts[r.bidang]++;
        });

        var elPdl = document.getElementById('sp-count-pdl');
        var elSmp = document.getElementById('sp-count-smp');
        var elRth = document.getElementById('sp-count-rth');
        var elTtp = document.getElementById('sp-count-ttp');
        var elTot = document.getElementById('sp-total-badge');

        if (elPdl) elPdl.textContent = counts.pengendalian;
        if (elSmp) elSmp.textContent = counts['sampah-lb3'];
        if (elRth) elRth.textContent = counts.rth;
        if (elTtp) elTtp.textContent = counts['tata-penataan'];
        if (elTot) elTot.textContent = reports.length;
    }

    function renderMarkersOnMap(reports, fit) {
        // Hapus marker lama
        activeMarkers.forEach(function (m) { if (m && m.remove) m.remove(); });
        activeMarkers = [];
        markersMap = {};

        updateLegendCounters(reports);
        renderFeedList(reports);

        if (!map || !reports || !reports.length) return;

        var dispersedReports = disperseCoordinates(reports);
        var bounds = new maplibregl.LngLatBounds();

        dispersedReports.forEach(function (r) {
            var lat = r.displayLat || parseFloat(r.latitude);
            var lng = r.displayLng || parseFloat(r.longitude);

            if (isNaN(lat) || isNaN(lng)) return;

            var el = createMarkerElement(r);
            var popup = new maplibregl.Popup({ offset: 25, closeButton: true, maxWidth: '320px' })
                .setHTML(createPopupHTML(r));

            var marker = new maplibregl.Marker({ element: el, anchor: 'center' })
                .setLngLat([lng, lat])
                .setPopup(popup)
                .addTo(map);

            activeMarkers.push(marker);
            markersMap[r.id] = marker;
            bounds.extend([lng, lat]);
        });

        if (fit && !bounds.isEmpty()) {
            map.fitBounds(bounds, { padding: { top: 60, bottom: 60, left: 60, right: 60 }, maxZoom: 15 });
        }
    }

    function fetchReports(fit) {
        var bidang = document.getElementById('sp-bidang').value;
        var status = document.getElementById('sp-status').value;
        var from = document.getElementById('sp-from').value;
        var to = document.getElementById('sp-to').value;
        var search = document.getElementById('sp-search').value;

        var url = '{{ route("admin.peta-laporan.data") }}?' + new URLSearchParams({
            from: from,
            to: to,
            bidang: bidang === 'all' ? '' : bidang,
            status: status === 'all' ? '' : status,
            search: search
        }).toString();

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                currentReports = data.reports || [];
                renderMarkersOnMap(currentReports, fit);
            })
            .catch(function (err) {
                console.error('[SebaranPengaduan] Fetch error:', err);
            });
    }

    function initMap() {
        if (!window.maplibregl) {
            return setTimeout(initMap, 80);
        }

        map = new maplibregl.Map({
            container: 'sebaran-pengaduan-map',
            style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
            center: [119.8707, -0.8917], // Pusat Kota Palu
            zoom: 12.5,
            attributionControl: false
        });

        map.addControl(new maplibregl.NavigationControl({ showCompass: true }), 'top-right');
        if (window.DlhFullscreenControl) map.addControl(new window.DlhFullscreenControl(), 'top-left');
        if (window.DlhBasemapSwitcher) map.addControl(new window.DlhBasemapSwitcher(), 'bottom-right');

        map.on('load', function () {
            renderMarkersOnMap(initialReports, true);
        });

        // Event Tombol Fit Bounds
        var btnFit = document.getElementById('sp-btn-fit');
        if (btnFit) {
            btnFit.addEventListener('click', function () {
                if (activeMarkers.length) {
                    var b = new maplibregl.LngLatBounds();
                    activeMarkers.forEach(function (m) { b.extend(m.getLngLat()); });
                    if (!b.isEmpty()) map.fitBounds(b, { padding: 60, maxZoom: 15 });
                } else {
                    map.flyTo({ center: [119.8707, -0.8917], zoom: 12.5 });
                }
            });
        }

        var btnPalu = document.getElementById('sp-btn-palu');
        if (btnPalu) {
            btnPalu.addEventListener('click', function () {
                map.flyTo({ center: [119.8707, -0.8917], zoom: 13, essential: true });
            });
        }

        // Auto Refresh tiap 45 detik
        setInterval(function () {
            fetchReports(false);
        }, 45000);
    }

    // Flatpickr & Controls Init
    function initFilters() {
        var fromEl = document.getElementById('sp-from');
        var toEl = document.getElementById('sp-to');
        var bidangEl = document.getElementById('sp-bidang');
        var statusEl = document.getElementById('sp-status');
        var searchEl = document.getElementById('sp-search');

        if (window.flatpickr) {
            flatpickr('#sp-from', { dateFormat: 'Y-m-d', allowInput: false, onChange: function () { fetchReports(true); } });
            flatpickr('#sp-to', { dateFormat: 'Y-m-d', allowInput: false, onChange: function () { fetchReports(true); } });
        }

        if (bidangEl) bidangEl.addEventListener('change', function () { fetchReports(true); });
        if (statusEl) statusEl.addEventListener('change', function () { fetchReports(true); });

        var searchTimeout = null;
        if (searchEl) {
            searchEl.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () { fetchReports(false); }, 350);
            });
        }

        document.querySelectorAll('.sp-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                document.querySelectorAll('.sp-chip').forEach(function (c) { c.classList.remove('is-active'); });
                chip.classList.add('is-active');

                var preset = chip.getAttribute('data-preset');
                var now = new Date();
                var pad = function (n) { return (n < 10 ? '0' : '') + n; };
                var ymd = function (d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); };

                if (preset === 'today') {
                    var t = ymd(now);
                    fromEl.value = t; toEl.value = t;
                } else if (preset === 'month') {
                    var y = now.getFullYear(), m = now.getMonth();
                    var last = new Date(y, m + 1, 0).getDate();
                    fromEl.value = y + '-' + pad(m + 1) + '-01';
                    toEl.value = y + '-' + pad(m + 1) + '-' + pad(last);
                } else if (preset === 'all') {
                    fromEl.value = '2020-01-01';
                    toEl.value = ymd(now);
                } else {
                    var days = parseInt(preset, 10);
                    var f = new Date(now);
                    f.setDate(now.getDate() - (days - 1));
                    fromEl.value = ymd(f);
                    toEl.value = ymd(now);
                }

                fetchReports(true);
            });
        });

        var btnRefresh = document.getElementById('sp-refresh');
        if (btnRefresh) btnRefresh.addEventListener('click', function () { fetchReports(true); });

        var btnReset = document.getElementById('sp-reset');
        if (btnReset) {
            btnReset.addEventListener('click', function () {
                if (bidangEl) bidangEl.value = 'all';
                if (statusEl) statusEl.value = 'all';
                if (searchEl) searchEl.value = '';
                var monthChip = document.querySelector('.sp-chip[data-preset="month"]');
                if (monthChip) monthChip.click();
            });
        }
    }

    initFilters();
    initMap();
});
</script>
@endpush
