@extends('layouts.app')

@section('title', 'Statistik Timbulan Sampah - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Grafik statistik volume timbulan sampah resmi di Kota Palu berdasarkan pencatatan periodik Dinas Lingkungan Hidup.')

@push('styles')
<style>
    .statistik-sampah-page {
        position: relative;
        overflow-x: clip;
    }
    .statistik-sampah-page::before {
        content: '';
        position: absolute;
        z-index: -1;
        top: 6rem;
        right: -10rem;
        width: 26rem;
        height: 26rem;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(16, 185, 129, .12), transparent 68%);
        filter: blur(8px);
        pointer-events: none;
    }
    .persampahan-stats-card {
        position: relative;
        overflow: hidden;
        border-radius: 1.5rem;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.03);
        transition: box-shadow .2s ease;
    }
    .dark .persampahan-stats-card {
        background: #020617;
        border-color: rgba(30, 41, 59, 0.85);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important;
    }
    .persampahan-stats-overview {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        padding: 1.75rem 2rem;
        color: #fff;
        background:
            radial-gradient(circle at 90% 15%, rgba(52, 211, 153, 0.25), transparent 45%),
            radial-gradient(circle at 10% 85%, rgba(20, 184, 166, 0.2), transparent 40%),
            linear-gradient(135deg, #064e3b 0%, #065f46 50%, #0f766e 100%);
    }
    .persampahan-stats-overview::before,
    .persampahan-stats-overview::after {
        content: '';
        position: absolute;
        z-index: -1;
        border-radius: 50%;
        pointer-events: none;
    }
    .persampahan-stats-overview::before {
        top: -6rem;
        right: 5%;
        width: 18rem;
        height: 18rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 70%);
    }
    .persampahan-stats-overview::after {
        right: -3rem;
        bottom: -6rem;
        width: 15rem;
        height: 15rem;
        background: rgba(255, 255, 255, 0.05);
    }
    .persampahan-stats-overview > * { position: relative; z-index: 1; }
    .persampahan-stats-heading {
        color: #ffffff;
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -.025em;
        line-height: 1.2;
    }
    .persampahan-stats-description {
        max-width: 46rem;
        margin-top: .5rem;
        color: rgba(236, 253, 245, 0.9);
        font-size: 0.875rem;
        line-height: 1.6;
    }
    .persampahan-stats-icon {
        display: inline-grid;
        place-items: center;
        width: 3rem;
        height: 3rem;
        border-radius: 1rem;
        color: #ffffff;
        background: rgba(255, 255, 255, 0.16);
        backdrop-filter: blur(8px);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.22), 0 4px 12px rgba(6, 78, 59, 0.2);
    }
    .persampahan-stats-source {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border-radius: 999px;
        padding: .45rem .875rem;
        color: #ffffff;
        background: rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(6px);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.2);
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .dark .persampahan-stats-source {
        color: #fff;
        background: rgba(0, 0, 0, 0.4);
    }
    .persampahan-stats-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.875rem;
        margin-top: 1.5rem;
    }
    .persampahan-summary-card {
        padding: 1rem 1.125rem;
        border-radius: 1rem;
        background: rgba(2, 44, 34, 0.45);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        transition: transform .18s ease, background-color .18s ease;
    }
    .persampahan-summary-card:hover {
        background: rgba(2, 44, 34, 0.6);
        transform: translateY(-2px);
    }
    .persampahan-summary-card dt {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        color: rgba(209, 250, 229, 0.85);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .persampahan-summary-card dd {
        margin: 0.4rem 0 0;
        color: #ffffff;
        font-size: 1.25rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }
    .persampahan-summary-card .persampahan-summary-sub {
        margin-top: 0.25rem;
        color: rgba(167, 243, 208, 0.75);
        font-size: 0.75rem;
        font-weight: 500;
    }
    .persampahan-stats-content {
        padding: 1.5rem;
    }
    .persampahan-stats-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.875rem;
    }
    .persampahan-period-tabs {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem;
        border-radius: 0.875rem;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }
    .dark .persampahan-period-tabs {
        background: #0f172a;
        border-color: #1e293b;
    }
    .persampahan-period-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        border-radius: 0.65rem;
        padding: 0.5rem 0.95rem;
        color: #475569;
        font-size: 0.8125rem;
        font-weight: 700;
        line-height: 1.2;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    .persampahan-period-tab:hover {
        color: #065f46;
        background: rgba(16, 185, 129, 0.08);
    }
    .dark .persampahan-period-tab { color: #94a3b8; }
    .dark .persampahan-period-tab:hover { color: #6ee7b7; background: rgba(16, 185, 129, 0.12); }
    .persampahan-period-tab[aria-selected="true"] {
        color: #ffffff;
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        box-shadow: 0 4px 10px -2px rgba(5, 150, 105, 0.45);
    }
    .dark .persampahan-period-tab[aria-selected="true"] {
        color: #ffffff;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 4px 12px -2px rgba(16, 185, 129, 0.4);
    }
    .persampahan-period-tab .tab-badge {
        display: inline-block;
        padding: 0.1rem 0.45rem;
        font-size: 0.6875rem;
        font-weight: 800;
        border-radius: 999px;
        background: rgba(0, 0, 0, 0.08);
    }
    .persampahan-period-tab[aria-selected="true"] .tab-badge {
        background: rgba(255, 255, 255, 0.24);
        color: #fff;
    }
    .persampahan-chart-type-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        padding: 0.2rem;
        border-radius: 0.75rem;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }
    .dark .persampahan-chart-type-toggle {
        background: #0f172a;
        border-color: #1e293b;
    }
    .persampahan-type-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.45rem 0.75rem;
        border-radius: 0.55rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        transition: all .15s ease;
        cursor: pointer;
    }
    .dark .persampahan-type-btn { color: #94a3b8; }
    .persampahan-type-btn.active {
        color: #065f46;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .dark .persampahan-type-btn.active {
        color: #6ee7b7;
        background: #1e293b;
    }
    .persampahan-stats-series-key {
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        gap: .5rem;
        color: #475569;
        font-size: .8125rem;
        font-weight: 700;
    }
    .dark .persampahan-stats-series-key { color: #cbd5e1; }
    .persampahan-stats-status {
        color: #64748b;
        font-size: .8125rem;
        font-weight: 700;
        line-height: 1.25;
        text-align: right;
    }
    .dark .persampahan-stats-status { color: #cbd5e1; }
    .persampahan-chart-stage {
        position: relative;
        min-height: 22rem;
        margin-top: 1.25rem;
        padding: 1.5rem 1.5rem 1rem;
        border-radius: 1.25rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
    }
    .dark .persampahan-chart-stage {
        background: #090d16;
        border-color: #1e293b;
    }
    .persampahan-chart-canvas-box {
        position: relative;
        width: 100%;
        height: 320px;
    }
    .persampahan-chart-empty {
        display: grid;
        position: absolute;
        inset: 0;
        place-items: center;
        padding: 2rem;
        text-align: center;
    }
    .persampahan-chart-empty[hidden] { display: none; }
    .persampahan-breakdown-box {
        margin-top: 1.5rem;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        overflow: hidden;
    }
    .dark .persampahan-breakdown-box {
        background: #0b1120;
        border-color: #1e293b;
    }
    .persampahan-stats-footer {
        display: flex;
        align-items: flex-start;
        gap: .5rem;
        margin: 1.25rem .125rem 0;
        color: #64748b;
        font-size: .8125rem;
        line-height: 1.5;
    }
    .persampahan-stats-footer svg { flex: 0 0 auto; margin-top: .0625rem; }
    .dark .persampahan-stats-footer { color: #94a3b8; }
    @media (max-width: 768px) {
        .persampahan-stats-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .persampahan-stats-overview { padding: 1.25rem; }
        .persampahan-stats-content { padding: 1.125rem; }
        .persampahan-stats-toolbar { align-items: flex-start; flex-direction: column; gap: .75rem; }
        .persampahan-stats-status { text-align: left; }
        .persampahan-stats-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; }
        .persampahan-summary-card { padding: .75rem .625rem; }
        .persampahan-chart-stage { min-height: 18rem; padding: 1rem .5rem .5rem; }
        .persampahan-chart-canvas-box { height: 260px; }
    }
</style>
@endpush

@section('content')
<div class="statistik-sampah-page space-y-8">
    <x-public.page-hero
        badge="{{ __('Sampah & LB3') }}"
        icon="chart-bar"
        title="{{ __('Statistik Timbulan Sampah') }}"
        description="{{ __('Pantau volume dan data timbulan sampah Kota Palu secara transparan dan akurat berdasarkan catatan resmi Dinas Lingkungan Hidup.') }}"
    />

    <div class="space-y-6">
        <section class="persampahan-stats-card reveal" aria-labelledby="statistik-sampah-title">
            <div class="persampahan-stats-overview">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex min-w-0 items-start gap-3.5">
                        <span class="persampahan-stats-icon">
                            <x-icons.ton-sampah class="h-6 w-6" />
                        </span>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h2 id="statistik-sampah-title" class="persampahan-stats-heading">{{ __('Statistik Timbulan Sampah Kota Palu') }}</h2>
                            </div>
                            <p class="persampahan-stats-description">{{ __('Pantau volume sampah yang dicatat Dinas Lingkungan Hidup Kota Palu secara transparan dan akurat. Pilih periode untuk melihat catatan volume sampah harian, mingguan, bulanan, atau tahunan.') }}</p>
                        </div>
                    </div>
                    <span class="persampahan-stats-source flex-shrink-0 self-start">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <x-icons.ui name="shield" class="h-3.5 w-3.5" />
                        {{ __('Data Layanan Resmi DLH') }}
                    </span>
                </div>

                <dl class="persampahan-stats-summary" aria-live="polite">
                    <div class="persampahan-summary-card">
                        <dt>
                            <span>{{ __('Catatan Tersedia') }}</span>
                            <x-icons.ui name="document" class="h-3.5 w-3.5 text-emerald-300 opacity-80" />
                        </dt>
                        <dd id="statistik-sampah-count">—</dd>
                        <p class="persampahan-summary-sub">{{ __('Total entri data') }}</p>
                    </div>
                    <div class="persampahan-summary-card">
                        <dt>
                            <span id="statistik-sampah-total-label">{{ __('Akumulasi Volume') }}</span>
                            <x-icons.ton-sampah class="h-3.5 w-3.5 text-emerald-300 opacity-80" />
                        </dt>
                        <dd id="statistik-sampah-total">—</dd>
                        <p class="persampahan-summary-sub">{{ __('Total tonase periode') }}</p>
                    </div>
                    <div class="persampahan-summary-card">
                        <dt>
                            <span>{{ __('Rata-rata / Entri') }}</span>
                            <x-icons.ui name="trending-up" class="h-3.5 w-3.5 text-emerald-300 opacity-80" />
                        </dt>
                        <dd id="statistik-sampah-average">—</dd>
                        <p class="persampahan-summary-sub">{{ __('Rerata volume per data') }}</p>
                    </div>
                    <div class="persampahan-summary-card">
                        <dt>
                            <span>{{ __('Pencatatan Terbaru') }}</span>
                            <x-icons.ui name="calendar" class="h-3.5 w-3.5 text-emerald-300 opacity-80" />
                        </dt>
                        <dd id="statistik-sampah-latest">—</dd>
                        <p id="statistik-sampah-latest-val" class="persampahan-summary-sub">{{ __('Update mutakhir') }}</p>
                    </div>
                </dl>
            </div>

            <div class="persampahan-stats-content">
                <div class="persampahan-stats-toolbar">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="persampahan-period-tabs" role="tablist" aria-label="{{ __('Pilih periode statistik sampah') }}">
                            @foreach ($chartPeriodLabels as $periodKey => $periodLabel)
                                <button type="button" class="persampahan-period-tab" role="tab"
                                    id="statistik-tab-{{ $periodKey }}" aria-controls="statistik-sampah-chart"
                                    aria-selected="{{ $periodKey === $chartDefaultPeriod ? 'true' : 'false' }}"
                                    data-stat-period="{{ $periodKey }}">
                                    <span>{{ $periodLabel }}</span>
                                    <span class="tab-badge" id="statistik-badge-{{ $periodKey }}">{{ count($chartSeries[$periodKey] ?? []) }}</span>
                                </button>
                            @endforeach
                        </div>

                        {{-- Toggle Jenis Visualisasi Grafik --}}
                        <div class="persampahan-chart-type-toggle" role="group" aria-label="{{ __('Pilih jenis visualisasi grafik') }}">
                            <button type="button" class="persampahan-type-btn active" data-chart-type="bar" title="{{ __('Grafik Batang') }}">
                                <x-icons.ui name="chart-bar" class="h-3.5 w-3.5" />
                                <span>{{ __('Batang') }}</span>
                            </button>
                            <button type="button" class="persampahan-type-btn" data-chart-type="line" title="{{ __('Grafik Garis Area') }}">
                                <x-icons.ui name="trending-up" class="h-3.5 w-3.5" />
                                <span>{{ __('Garis') }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 sm:justify-end">
                        <span class="persampahan-stats-series-key">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-500/20"></span>
                            {{ __('Volume dalam Ton') }}
                        </span>
                        <p id="statistik-sampah-status" class="persampahan-stats-status" aria-live="polite"></p>
                    </div>
                </div>

                <div class="persampahan-chart-stage">
                    <div class="persampahan-chart-canvas-box">
                        <canvas id="statistik-sampah-chart" height="320" role="img"
                            aria-label="{{ __('Grafik statistik timbulan sampah') }}"
                            data-series='@json($chartSeries)'
                            data-period-labels='@json($chartPeriodLabels)'
                            data-default-period="{{ $chartDefaultPeriod }}"></canvas>
                    </div>
                    <div id="statistik-sampah-empty" class="persampahan-chart-empty" hidden>
                        <div class="max-w-sm mx-auto text-center py-8">
                            <span class="w-14 h-14 rounded-2xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-3 shadow-inner">
                                <x-icons.ui name="document" class="h-7 w-7" />
                            </span>
                            <p class="font-bold text-slate-800 dark:text-slate-100 text-sm">{{ __('Belum ada data untuk periode ini') }}</p>
                            <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ __('Admin DLH Kota Palu belum menambahkan catatan statistik untuk periode ini.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Tabel Rincian Catatan Periode --}}
                <div id="statistik-breakdown-wrapper" class="persampahan-breakdown-box">
                    <div class="flex items-center justify-between px-4 py-3.5 bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-200/80 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <x-icons.ui name="table" class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ __('Rincian Catatan Volume Sampah') }}</h3>
                        </div>
                        <span id="statistik-breakdown-count" class="text-[11px] font-semibold px-2.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60">0 Catatan</span>
                    </div>
                    <div class="overflow-x-auto max-h-64 overflow-y-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100/60 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200/60 dark:border-slate-800/60 sticky top-0 backdrop-blur-sm">
                                <tr>
                                    <th class="py-2.5 px-4 w-12 text-center">#</th>
                                    <th class="py-2.5 px-4">{{ __('Tanggal Pencatatan') }}</th>
                                    <th class="py-2.5 px-4">{{ __('Periode') }}</th>
                                    <th class="py-2.5 px-4 text-right">{{ __('Volume Sampah') }}</th>
                                    <th class="py-2.5 px-4 w-36 hidden sm:table-cell">{{ __('Porsi') }}</th>
                                </tr>
                            </thead>
                            <tbody id="statistik-breakdown-tbody" class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300 font-medium">
                                {{-- Diisi via Javascript --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                <p class="persampahan-stats-footer">
                    <x-icons.ui name="info" class="h-4 w-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
                    <span>{{ __('Grafik menampilkan seluruh data timbulan sampah resmi yang tercatat di Dinas Lingkungan Hidup Kota Palu dalam satuan Tonase (Ton).') }}</span>
                </p>
            </div>
        </section>
    </div>
</div>

@push('scripts')
@vite('resources/js/dashboard-charts.js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var canvas = document.getElementById('statistik-sampah-chart');
        if (canvas && typeof Chart !== 'undefined') {
            var chartSeries = JSON.parse(canvas.dataset.series || '{}');
            var chartPeriodLabels = JSON.parse(canvas.dataset.periodLabels || '{}');
            var chartDefaultPeriod = canvas.dataset.defaultPeriod || 'harian';
            var chartEmpty = document.getElementById('statistik-sampah-empty');
            var chartStatus = document.getElementById('statistik-sampah-status');
            var countOutput = document.getElementById('statistik-sampah-count');
            var totalOutput = document.getElementById('statistik-sampah-total');
            var totalLabel = document.getElementById('statistik-sampah-total-label');
            var avgOutput = document.getElementById('statistik-sampah-average');
            var latestOutput = document.getElementById('statistik-sampah-latest');
            var latestValOutput = document.getElementById('statistik-sampah-latest-val');
            var breakdownWrapper = document.getElementById('statistik-breakdown-wrapper');
            var breakdownTbody = document.getElementById('statistik-breakdown-tbody');
            var breakdownCount = document.getElementById('statistik-breakdown-count');

            var currentPeriod = chartDefaultPeriod;
            var currentChartType = 'bar'; // 'bar' | 'line'
            var numberFormat = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            var dateFormat = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            var statisticsChart = null;

            function recordsForPeriod(period) {
                return (Array.isArray(chartSeries[period]) ? chartSeries[period] : []).filter(function (record) {
                    return record && typeof record.date === 'string' && Number.isFinite(Number(record.value)) && Number(record.value) >= 0;
                });
            }

            function formatDate(value) {
                var date = new Date(value + 'T00:00:00');
                return Number.isNaN(date.getTime()) ? '—' : dateFormat.format(date);
            }

            function chartPalette() {
                var dark = document.documentElement.classList.contains('dark');
                return {
                    grid: dark ? 'rgba(148, 163, 184, .12)' : 'rgba(100, 116, 139, .12)',
                    tick: dark ? '#94a3b8' : '#64748b',
                    tooltipBg: dark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(6, 40, 26, 0.95)',
                    barTop: dark ? '#34d399' : '#10b981',
                    barBottom: dark ? '#059669' : '#047857',
                    line: dark ? '#34d399' : '#059669',
                    pointBg: dark ? '#064e3b' : '#ffffff',
                    pointBorder: dark ? '#34d399' : '#059669',
                    pillBg: dark ? '#0f172a' : '#064e3b',
                };
            }

            function renderBreakdown(records, period, total) {
                if (!breakdownTbody || !breakdownWrapper) return;
                if (!records.length) {
                    breakdownWrapper.hidden = true;
                    return;
                }
                breakdownWrapper.hidden = false;
                if (breakdownCount) breakdownCount.textContent = records.length + ' {{ __('Catatan') }}';

                var maxVal = Math.max.apply(Math, records.map(function (r) { return Number(r.value); })) || 1;
                var periodLabel = chartPeriodLabels[period] || period;

                var rowsHtml = '';
                records.slice().reverse().forEach(function (rec, idx) {
                    var num = records.length - idx;
                    var val = Number(rec.value);
                    var pct = total > 0 ? Math.round((val / total) * 100) : 0;
                    var barPct = Math.min(100, Math.round((val / maxVal) * 100));
                    var formattedDate = rec.label || formatDate(rec.date);

                    rowsHtml += '<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">'
                        + '<td class="py-2.5 px-4 text-center text-slate-400 font-mono text-[11px]">' + num + '</td>'
                        + '<td class="py-2.5 px-4 font-semibold text-slate-800 dark:text-slate-200">'
                        +   '<div class="flex items-center gap-1.5">'
                        +     '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>'
                        +     '<span>' + formattedDate + '</span>'
                        +   '</div>'
                        + '</td>'
                        + '<td class="py-2.5 px-4">'
                        +   '<span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">'
                        +     periodLabel
                        +   '</span>'
                        + '</td>'
                        + '<td class="py-2.5 px-4 text-right font-extrabold text-emerald-700 dark:text-emerald-400">'
                        +   numberFormat.format(val) + ' <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">{{ __('ton') }}</span>'
                        + '</td>'
                        + '<td class="py-2.5 px-4 hidden sm:table-cell">'
                        +   '<div class="flex items-center gap-2">'
                        +     '<div class="flex-1 bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">'
                        +       '<div class="bg-emerald-500 h-full rounded-full" style="width: ' + barPct + '%"></div>'
                        +     '</div>'
                        +     '<span class="text-[10px] text-slate-400 font-mono w-7 text-right">' + pct + '%</span>'
                        +   '</div>'
                        + '</td>'
                        + '</tr>';
                });
                breakdownTbody.innerHTML = rowsHtml;
            }

            function updateSummary(records, period) {
                var total = records.reduce(function (sum, record) { return sum + Number(record.value); }, 0);
                var count = records.length;
                var avg = count > 0 ? (total / count) : 0;
                var latest = count ? records[records.length - 1] : null;
                var periodLabel = chartPeriodLabels[period] || period;

                if (countOutput) countOutput.textContent = numberFormat.format(count) + ' {{ __('entri') }}';
                if (totalLabel) totalLabel.textContent = '{{ __('Akumulasi') }} ' + periodLabel.toLowerCase();
                if (totalOutput) totalOutput.textContent = numberFormat.format(total) + ' {{ __('ton') }}';
                if (avgOutput) avgOutput.textContent = numberFormat.format(avg) + ' {{ __('ton') }}';
                if (latestOutput) latestOutput.textContent = latest ? formatDate(latest.date) : '—';
                if (latestValOutput) {
                    latestValOutput.textContent = latest ? numberFormat.format(Number(latest.value)) + ' {{ __('ton') }}' : '{{ __('Belum ada data') }}';
                }

                if (chartStatus) {
                    chartStatus.textContent = count
                        ? numberFormat.format(count) + ' {{ __('catatan') }} · ' + periodLabel
                        : '{{ __('Tidak ada catatan') }} · ' + periodLabel;
                }

                renderBreakdown(records, period, total);
            }

            var barFloatingPlugin = {
                id: 'dlhBarFloatingValues',
                afterDatasetsDraw: function (chart) {
                    var meta = chart.getDatasetMeta(0);
                    if (!meta || !meta.data) return;
                    var ctx = chart.ctx;
                    var dark = document.documentElement.classList.contains('dark');

                    meta.data.forEach(function (bar, idx) {
                        var val = chart.data.datasets[0].data[idx];
                        if (val === null || val === undefined) return;
                        var text = numberFormat.format(val) + ' ton';

                        ctx.save();
                        ctx.font = '700 11px Inter Variable, ui-sans-serif, system-ui, sans-serif';
                        var textWidth = ctx.measureText(text).width;
                        var pillW = textWidth + 14;
                        var pillH = 22;
                        var x = bar.x - pillW / 2;
                        var y = Math.max(chart.chartArea.top + 2, bar.y - pillH - 6);

                        ctx.fillStyle = dark ? 'rgba(15, 23, 42, 0.92)' : 'rgba(6, 40, 26, 0.88)';
                        ctx.beginPath();
                        if (ctx.roundRect) {
                            ctx.roundRect(x, y, pillW, pillH, 6);
                        } else {
                            ctx.rect(x, y, pillW, pillH);
                        }
                        ctx.fill();

                        ctx.fillStyle = '#ffffff';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(text, bar.x, y + (pillH / 2));
                        ctx.restore();
                    });
                }
            };

            var lineLatestPlugin = {
                id: 'dlhLineLatestValue',
                afterDatasetsDraw: function (chart) {
                    var meta = chart.getDatasetMeta(0);
                    if (!meta || !meta.data || !meta.data.length) return;
                    var dark = document.documentElement.classList.contains('dark');
                    var ctx = chart.ctx;
                    var chartArea = chart.chartArea;

                    if (meta.data.length === 1) {
                        var singlePoint = meta.data[0];
                        ctx.save();
                        ctx.strokeStyle = dark ? 'rgba(52, 211, 153, 0.45)' : 'rgba(5, 150, 105, 0.45)';
                        ctx.lineWidth = 2;
                        ctx.setLineDash([4, 4]);
                        ctx.beginPath();
                        ctx.moveTo(singlePoint.x, singlePoint.y);
                        ctx.lineTo(singlePoint.x, chartArea.bottom);
                        ctx.stroke();

                        ctx.beginPath();
                        ctx.arc(singlePoint.x, singlePoint.y, 14, 0, Math.PI * 2);
                        ctx.fillStyle = dark ? 'rgba(52, 211, 153, 0.18)' : 'rgba(16, 185, 129, 0.18)';
                        ctx.fill();
                        ctx.restore();
                    }

                    meta.data.forEach(function (point, idx) {
                        var val = chart.data.datasets[0].data[idx];
                        if (val === null || val === undefined) return;

                        if (meta.data.length > 5 && idx !== meta.data.length - 1) return;

                        var text = numberFormat.format(val) + ' ton';
                        ctx.save();
                        ctx.font = '700 11px Inter Variable, ui-sans-serif, system-ui, sans-serif';
                        var textWidth = ctx.measureText(text).width;
                        var pillW = textWidth + 14;
                        var pillH = 22;
                        var x = Math.min(Math.max(point.x - pillW / 2, chartArea.left + 2), chartArea.right - pillW - 2);
                        var y = Math.max(point.y - pillH - 8, chartArea.top + 4);

                        ctx.fillStyle = dark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(6, 40, 26, 0.92)';
                        ctx.beginPath();
                        if (ctx.roundRect) {
                            ctx.roundRect(x, y, pillW, pillH, 6);
                        } else {
                            ctx.rect(x, y, pillW, pillH);
                        }
                        ctx.fill();

                        ctx.fillStyle = '#ffffff';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(text, x + (pillW / 2), y + (pillH / 2));
                        ctx.restore();
                    });
                }
            };

            function drawChart(period, type) {
                if (period) currentPeriod = period;
                if (type) currentChartType = type;

                var records = recordsForPeriod(currentPeriod);
                var palette = chartPalette();
                updateSummary(records, currentPeriod);

                // Update tab active states
                document.querySelectorAll('[data-stat-period]').forEach(function (button) {
                    var selected = button.dataset.statPeriod === currentPeriod;
                    button.setAttribute('aria-selected', selected ? 'true' : 'false');
                });

                // Update type toggle button states
                document.querySelectorAll('.persampahan-type-btn').forEach(function (btn) {
                    var active = btn.dataset.chartType === currentChartType;
                    btn.classList.toggle('active', active);
                });

                if (statisticsChart) {
                    statisticsChart.destroy();
                    statisticsChart = null;
                }

                canvas.hidden = !records.length;
                chartEmpty.hidden = records.length !== 0;
                if (!records.length) return;

                var context = canvas.getContext('2d');
                var labels = records.map(function (record) { return record.label || formatDate(record.date); });
                var dataValues = records.map(function (record) { return Number(record.value); });
                var isSingle = records.length === 1;

                if (currentChartType === 'bar') {
                    var barGradient = context.createLinearGradient(0, 0, 0, canvas.clientHeight || 320);
                    barGradient.addColorStop(0, palette.barTop);
                    barGradient.addColorStop(1, palette.barBottom);

                    statisticsChart = new Chart(context, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: '{{ __('Volume Sampah') }}',
                                data: dataValues,
                                backgroundColor: barGradient,
                                borderColor: palette.barTop,
                                borderWidth: 1,
                                borderRadius: { topLeft: 8, topRight: 8, bottomLeft: 0, bottomRight: 0 },
                                borderSkipped: false,
                                maxBarThickness: isSingle ? 56 : (records.length <= 4 ? 44 : 32),
                                barPercentage: isSingle ? 0.25 : (records.length <= 4 ? 0.45 : 0.65),
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 350 },
                            interaction: { mode: 'index', intersect: false },
                            layout: { padding: { top: 24, right: 16, bottom: 4, left: 4 } },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    displayColors: false,
                                    backgroundColor: palette.tooltipBg,
                                    titleFont: { family: 'inherit', weight: '700', size: 12 },
                                    bodyFont: { family: 'inherit', size: 12 },
                                    padding: 10,
                                    cornerRadius: 8,
                                    callbacks: {
                                        title: function (items) { return items[0] ? items[0].label : ''; },
                                        label: function (item) { return '{{ __('Volume') }}: ' + numberFormat.format(item.parsed.y) + ' {{ __('ton') }}'; },
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    border: { display: false },
                                    ticks: {
                                        color: palette.tick,
                                        font: { family: 'inherit', size: 11, weight: '600' },
                                        maxRotation: 0,
                                        autoSkip: true,
                                        maxTicksLimit: 8,
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                    grace: '20%',
                                    border: { display: false },
                                    grid: {
                                        color: palette.grid,
                                        drawBorder: false,
                                    },
                                    ticks: {
                                        color: palette.tick,
                                        font: { family: 'inherit', size: 11 },
                                        padding: 8,
                                        callback: function (value) { return numberFormat.format(value) + ' t'; },
                                    },
                                },
                            },
                        },
                        plugins: [barFloatingPlugin],
                    });
                } else {
                    var lineFill = context.createLinearGradient(0, 0, 0, canvas.clientHeight || 320);
                    lineFill.addColorStop(0, 'rgba(16, 185, 129, 0.32)');
                    lineFill.addColorStop(0.7, 'rgba(16, 185, 129, 0.08)');
                    lineFill.addColorStop(1, 'rgba(16, 185, 129, 0.00)');

                    statisticsChart = new Chart(context, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: '{{ __('Volume Sampah') }}',
                                data: dataValues,
                                fill: true,
                                backgroundColor: lineFill,
                                borderColor: palette.line,
                                borderWidth: 3.5,
                                tension: 0.36,
                                pointRadius: isSingle ? 8 : (function (ctx) { return ctx.dataIndex === records.length - 1 ? 6 : 4; }),
                                pointHoverRadius: isSingle ? 11 : 7,
                                pointBackgroundColor: palette.pointBg,
                                pointBorderColor: palette.pointBorder,
                                pointBorderWidth: 3.5,
                                pointHitRadius: 16,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 350 },
                            interaction: { mode: 'index', intersect: false },
                            layout: { padding: { top: 24, right: 16, bottom: 4, left: 4 } },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    displayColors: false,
                                    backgroundColor: palette.tooltipBg,
                                    titleFont: { family: 'inherit', weight: '700', size: 12 },
                                    bodyFont: { family: 'inherit', size: 12 },
                                    padding: 10,
                                    cornerRadius: 8,
                                    callbacks: {
                                        title: function (items) { return items[0] ? items[0].label : ''; },
                                        label: function (item) { return '{{ __('Volume') }}: ' + numberFormat.format(item.parsed.y) + ' {{ __('ton') }}'; },
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    border: { display: false },
                                    ticks: {
                                        color: palette.tick,
                                        font: { family: 'inherit', size: 11, weight: '600' },
                                        maxRotation: 0,
                                        autoSkip: true,
                                        maxTicksLimit: 8,
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                    grace: '18%',
                                    border: { display: false },
                                    grid: {
                                        color: palette.grid,
                                        drawBorder: false,
                                    },
                                    ticks: {
                                        color: palette.tick,
                                        font: { family: 'inherit', size: 11 },
                                        padding: 8,
                                        callback: function (value) { return numberFormat.format(value) + ' t'; },
                                    },
                                },
                            },
                        },
                        plugins: [lineLatestPlugin],
                    });
                }
            }

            document.querySelectorAll('[data-stat-period]').forEach(function (button) {
                button.addEventListener('click', function () {
                    drawChart(button.dataset.statPeriod, null);
                });
            });

            document.querySelectorAll('.persampahan-type-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    drawChart(null, btn.dataset.chartType);
                });
            });

            // Update badge counts for each period
            Object.keys(chartSeries).forEach(function (p) {
                var badge = document.getElementById('statistik-badge-' + p);
                if (badge && Array.isArray(chartSeries[p])) {
                    badge.textContent = chartSeries[p].length;
                }
            });

            drawChart(chartDefaultPeriod, 'bar');
        }
    });
</script>
@endpush
@endsection
