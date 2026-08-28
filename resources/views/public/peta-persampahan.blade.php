@extends('layouts.app')

@section('title', 'Peta Persampahan - DLH Kota Palu')
@section('description', 'Peta interaktif titik TPA, TPST, Bank Sampah, TPS, pelacakan armada real-time, dan statistik timbulan sampah DLH Kota Palu.')

@push('styles')
<style>
    .peta-persampahan-page {
        position: relative;
        overflow-x: clip;
    }
    .peta-persampahan-page::before {
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
    .persampahan-control-panel {
        position: relative;
        /* Menu select perlu melampaui kartu tanpa terpotong. */
        overflow: visible;
        z-index: 12;
        border-color: rgba(16, 76, 51, .12) !important;
        border-radius: 1.5rem !important;
        box-shadow: 0 1px 2px rgba(10, 48, 30, .04), 0 22px 42px -30px rgba(10, 48, 30, .34) !important;
    }
    .persampahan-control-panel::before {
        content: '';
        position: absolute;
        /* Tetap berada di sisi dalam border dan mengikuti lengkungan kartu.
           Panel harus overflow-visible untuk dropdown, jadi garis aksen ini
           perlu membentuk radiusnya sendiri agar tidak keluar di sudut atas. */
        /* Sisakan ruang di kedua sisi: garis tidak memasuki area lengkung
           sudut kartu meski panel perlu overflow-visible untuk dropdown. */
        inset: 1px 1.5rem auto;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, #168953 0%, #22b78a 48%, #25a7c7 100%);
    }
    .persampahan-control-panel > * {
        position: relative;
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
    .peta-persampahan-page .map-container {
        position: relative;
        width: 100%;
        height: clamp(23rem, 55vw, 37rem);
        border-radius: 1.5rem;
        overflow: hidden;
        isolation: isolate;
        border: 1px solid rgba(16, 76, 51, .14);
        background: #e7f2eb;
        box-shadow: 0 1px 2px rgba(10, 48, 30, .05), 0 24px 48px -30px rgba(10, 48, 30, .38);
    }
    .dark .peta-persampahan-page .map-container {
        border-color: rgba(110, 231, 183, .2);
        background: #0b241a;
    }
    .peta-persampahan-page .map-container .maplibregl-map {
        width: 100%;
        height: 100%;
    }
    .peta-persampahan-page .map-container .maplibregl-ctrl-group {
        border: 1px solid rgba(16, 76, 51, .12) !important;
        border-radius: 12px !important;
        overflow: hidden;
        box-shadow: 0 10px 24px -12px rgba(10, 48, 30, .35) !important;
    }
    .peta-persampahan-page .map-container .maplibregl-ctrl:not(.dlh-tools-ctrl):not(.dlh-tools-ctrl__item) {
        margin: 12px !important;
    }
    .peta-persampahan-page .map-container .maplibregl-ctrl-top-left {
        top: 12px !important;
        left: 12px !important;
    }
    .peta-persampahan-page .map-container .maplibregl-ctrl-bottom-right {
        bottom: 12px !important;
        right: 12px !important;
    }
    .peta-persampahan-page .map-container .maplibregl-popup-content {
        border: 1px solid rgba(16, 76, 51, .12) !important;
        border-radius: 16px !important;
        box-shadow: 0 18px 40px -16px rgba(10, 48, 30, .34) !important;
        padding: 0 !important;
        overflow: hidden;
    }
    /* Toggle switch premium */
    .dlh-switch {
        position: relative;
        display: inline-block;
        width: 38px;
        height: 22px;
        border-radius: 999px;
        background: #cbd5e1;
        transition: background .25s ease;
        flex-shrink: 0;
    }
    .dlh-switch::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
        transition: transform .25s ease;
    }
    .dark .dlh-switch { background: #475569; }
    input:checked + .dlh-switch { background: var(--switch-color, #10b981); }
    input:checked + .dlh-switch::after { transform: translateX(16px); }
    @media (max-width: 639px) {
        .dlh-switch { width: 28px; height: 16px; }
        .dlh-switch::after { top: 2px; left: 2px; width: 11px; height: 11px; }
        input:checked + .dlh-switch::after { transform: translateX(12px); }
    }

    /* Kartu filter tipe kendaraan — premium */
    .vehicle-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 86px;
        padding: 16px 18px;
        border-radius: 18px;
        background: #fff;
        border: 1.5px solid rgba(15, 23, 42, .08);
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 8px 24px -14px rgba(15, 23, 42, .14);
        cursor: pointer;
        text-align: left;
        transition: transform .25s cubic-bezier(.4, 0, .2, 1), box-shadow .25s ease, border-color .25s ease, background .25s ease;
    }
    .dark .vehicle-card { background: #020617; border-color: rgba(148, 163, 184, .16); }
    .vehicle-card:hover {
        transform: translateY(-2px);
        border-color: rgba(15, 23, 42, .18);
        box-shadow: 0 14px 32px -12px rgba(15, 23, 42, .2);
    }
    .vehicle-card:active { transform: translateY(0) scale(.985); }
    .vehicle-card:focus-visible { outline: 2px solid var(--vc); outline-offset: 2px; }
    .vehicle-card-active {
        border-color: var(--vc);
        background:
            radial-gradient(130% 170% at 0% 0%, color-mix(in srgb, var(--vc) 11%, transparent), transparent 55%),
            #ffffff;
        box-shadow: 0 0 0 1px var(--vc), 0 12px 32px -12px color-mix(in srgb, var(--vc) 45%, transparent);
    }
    .dark .vehicle-card-active {
        background:
            radial-gradient(130% 170% at 0% 0%, color-mix(in srgb, var(--vc) 24%, transparent), transparent 55%),
            #020617;
    }
    .vehicle-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--vc);
        background: color-mix(in srgb, var(--vc) 10%, transparent);
        border: 1px solid color-mix(in srgb, var(--vc) 20%, transparent);
        transition: background .25s ease, color .25s ease, box-shadow .25s ease;
    }
    .vehicle-card-active .vehicle-icon {
        background: var(--vc);
        color: #fff;
        box-shadow: 0 8px 18px -8px color-mix(in srgb, var(--vc) 75%, transparent);
    }
    .vehicle-name {
        display: block;
        font-weight: 800;
        font-size: 15px;
        letter-spacing: -.01em;
        color: #0f172a;
        line-height: 1.2;
    }
    .dark .vehicle-name { color: #f1f5f9; }
    .vehicle-check {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: var(--vc);
        box-shadow: 0 4px 10px -2px color-mix(in srgb, var(--vc) 65%, transparent);
        opacity: 0;
        transform: scale(.4) rotate(-30deg);
        transition: opacity .25s ease, transform .3s cubic-bezier(.34, 1.56, .64, 1);
    }
    .vehicle-card-active .vehicle-check { opacity: 1; transform: scale(1) rotate(0); }

    .peta-persampahan-page .custom-vehicle-icon{background:transparent!important;border:none!important;box-shadow:none!important;cursor:pointer}
    .peta-persampahan-page .custom-vehicle-icon img{filter:drop-shadow(0 2px 6px rgba(0,0,0,0.3));transition:transform .3s ease}
    .peta-persampahan-page .custom-vehicle-icon:hover img{filter:drop-shadow(0 3px 10px rgba(0,0,0,0.4))}
    .filter-label {
        display: block;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #475569;
        margin-bottom: 6px;
    }
    .dark .filter-label { color: #94a3b8; }

    .persampahan-stats-card {
        position: relative;
        overflow: hidden;
        border-radius: 1.25rem;
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
        padding: 1.5rem 1.75rem;
        color: #fff;
        background:
            radial-gradient(circle at 90% 15%, rgba(52, 211, 153, 0.22), transparent 45%),
            radial-gradient(circle at 10% 85%, rgba(20, 184, 166, 0.18), transparent 40%),
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
        width: 16rem;
        height: 16rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 70%);
    }
    .persampahan-stats-overview::after {
        right: -3rem;
        bottom: -6rem;
        width: 14rem;
        height: 14rem;
        background: rgba(255, 255, 255, 0.05);
    }
    .persampahan-stats-overview > * { position: relative; z-index: 1; }
    .persampahan-stats-heading {
        color: #ffffff;
        font-size: 1.25rem;
        font-weight: 800;
        letter-spacing: -.025em;
        line-height: 1.2;
    }
    .persampahan-stats-description {
        max-width: 44rem;
        margin-top: .375rem;
        color: rgba(236, 253, 245, 0.85);
        font-size: 0.8125rem;
        line-height: 1.55;
    }
    .persampahan-stats-icon {
        display: inline-grid;
        place-items: center;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.875rem;
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
        padding: .4rem .75rem;
        color: #ffffff;
        background: rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(6px);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.2);
        font-size: .6875rem;
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
        gap: 0.75rem;
        margin-top: 1.35rem;
    }
    .persampahan-summary-card {
        padding: 0.875rem 1rem;
        border-radius: 0.875rem;
        background: rgba(2, 44, 34, 0.45);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        transition: transform .18s ease, background-color .18s ease;
    }
    .persampahan-summary-card:hover {
        background: rgba(2, 44, 34, 0.6);
        transform: translateY(-1px);
    }
    .persampahan-summary-card dt {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        color: rgba(209, 250, 229, 0.85);
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .persampahan-summary-card dd {
        margin: 0.35rem 0 0;
        color: #ffffff;
        font-size: 1.125rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }
    .persampahan-summary-card .persampahan-summary-sub {
        margin-top: 0.25rem;
        color: rgba(167, 243, 208, 0.75);
        font-size: 0.6875rem;
        font-weight: 500;
    }
    .persampahan-stats-content {
        padding: 1.35rem;
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
        border-radius: 0.75rem;
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
        border-radius: 0.55rem;
        padding: 0.45rem 0.875rem;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 700;
        line-height: 1.2;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
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
        padding: 0.1rem 0.375rem;
        font-size: 0.625rem;
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
        border-radius: 0.6rem;
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
        padding: 0.35rem 0.625rem;
        border-radius: 0.45rem;
        font-size: 0.6875rem;
        font-weight: 700;
        color: #64748b;
        transition: all .15s ease;
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
        font-size: .75rem;
        font-weight: 700;
    }
    .dark .persampahan-stats-series-key { color: #cbd5e1; }
    .persampahan-stats-status {
        color: #64748b;
        font-size: .75rem;
        font-weight: 700;
        line-height: 1.25;
        text-align: right;
    }
    .dark .persampahan-stats-status { color: #cbd5e1; }
    .persampahan-chart-stage {
        position: relative;
        min-height: 20rem;
        margin-top: 1.15rem;
        padding: 1.25rem 1.25rem 0.75rem;
        border-radius: 1rem;
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
        height: 280px;
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
        margin-top: 1.25rem;
        border-radius: 0.875rem;
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
        margin: 1rem .125rem 0;
        color: #64748b;
        font-size: .75rem;
        line-height: 1.45;
    }
    .persampahan-stats-footer svg { flex: 0 0 auto; margin-top: .0625rem; }
    .dark .persampahan-stats-footer { color: #94a3b8; }
    @media (max-width: 768px) {
        .persampahan-stats-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .persampahan-stats-overview { padding: 1.125rem; }
        .persampahan-stats-content { padding: 1rem; }
        .persampahan-stats-toolbar { align-items: flex-start; flex-direction: column; gap: .75rem; }
        .persampahan-stats-status { text-align: left; }
        .persampahan-stats-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; }
        .persampahan-summary-card { padding: .75rem .625rem; }
        .persampahan-chart-stage { min-height: 18rem; padding: 1rem .5rem .5rem; }
        .persampahan-chart-canvas-box { height: 250px; }
    }
    .dark .persampahan-control-panel,
    .dark .persampahan-stats-card {
        box-shadow: 0 8px 8px -8px rgba(0, 0, 0, .72) !important;
    }
    @media (max-width: 640px) {
        .peta-persampahan-page::before { right: -13rem; }
        .peta-persampahan-page .map-container { height: 25rem; border-radius: 1.25rem; }
        .persampahan-map-frame::before { border-radius: 1.2rem 1.2rem 0 0; }
    }
    @media (prefers-reduced-motion: reduce) {
        .peta-persampahan-page *,
        .peta-persampahan-page *::before,
        .peta-persampahan-page *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="peta-persampahan-page space-y-7">
    <x-public.page-hero badge="{{ __('Sampah & LB3') }}" icon="map-pin" title="{{ __('Peta & Informasi Persampahan') }}" description="{{ __('Lihat lokasi fasilitas persampahan, lacak armada real-time, dan statistik timbulan sampah.') }}" />

    <div class="space-y-6">
        <div class="reveal">
            <div class="persampahan-control-panel bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-4 sm:p-5 space-y-4">
                {{-- Header panel filter --}}
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-900/25 border border-brand-200 dark:border-brand-800/60 flex items-center justify-center text-brand-600 dark:text-brand-300 flex-shrink-0">
                        <x-icons.titik-tps class="h-[1.125rem] w-[1.125rem]" />
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ __('Pilih Tipe Kendaraan') }}</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-snug">{{ __('Pilih tipe kendaraan untuk menampilkan jalur angkut sampah di peta.') }}</p>
                    </div>
                </div>

                {{-- Kartu filter tipe kendaraan (single-select) --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach ($vehicleTypes as $vt)
                    <button type="button" data-type="{{ $vt['key'] }}" role="radio" aria-pressed="{{ $vt['key'] === $defaultType ? 'true' : 'false' }}"
                        style="--vc: {{ $vt['color'] }}"
                        class="vehicle-card {{ $vt['key'] === $defaultType ? 'vehicle-card-active' : '' }}">
                        <span class="vehicle-icon">
                            <x-icons.sampah class="h-[23px] w-[23px]" />
                        </span>
                        <span class="flex-1 min-w-0">
                            <span class="vehicle-name">{{ $vt['label'] }}</span>
                        </span>
                        <span class="vehicle-check">
                            <x-icons.ui name="check" class="h-3 w-3" />
                        </span>
                    </button>
                    @endforeach
                </div>

                {{-- Baris filter: dropdown tipe kendaraan, kelurahan, reset & jumlah jalur --}}
                <div class="border-t border-slate-100 dark:border-slate-800 pt-4 flex flex-wrap items-end gap-3">
                    <div class="w-full sm:w-44">
                        <x-admin.select
                            id="filter-type"
                            name="filter_tipe_kendaraan"
                            :label="__('Tipe Kendaraan')"
                            :placeholder="__('Pilih Tipe Kendaraan')"
                            :options="collect($vehicleTypes)->mapWithKeys(fn ($vt) => [$vt['key'] => $vt['label']])->all()"
                            :selected="$defaultType"
                        />
                    </div>

                    <div class="w-full sm:w-60" x-data="{
                        open: false,
                        value: '',
                        selectedLabel: '',
                        options: @js($vehicleTypes[$defaultType]['kelurahans'] ?? []),
                        get disabled() { return this.options.length <= 1; },
                        selectOption(id, label) {
                            this.value = id ? String(id) : '';
                            this.selectedLabel = label || '';
                            this.open = false;
                            this.$refs.hiddenInput.value = this.value;
                            this.$refs.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }">
                        <label for="filter-kelurahan" class="fi-label">{{ __('Kelurahan') }}</label>
                        <div class="fi-select-shell" :class="{ 'fi-select-shell--open': open }">
                            <input
                                type="hidden"
                                id="filter-kelurahan"
                                name="filter_kelurahan"
                                x-ref="hiddenInput"
                                :value="value"
                            >
                            <button
                                type="button"
                                x-on:click="open = !open"
                                :disabled="disabled"
                                class="fi-select-trigger"
                                :class="{ 'fi-select-trigger--disabled': disabled }"
                            >
                                <span x-show="!value" class="fi-select-placeholder">{{ __('Semua Kelurahan') }}</span>
                                <span x-show="value" x-text="selectedLabel" class="fi-select-value"></span>
                                <x-icons.ui name="chevron-down" class="fi-select-chevron" x-bind:class="{ 'fi-select-chevron--open': open }" />
                            </button>
                            <div
                                x-show="open"
                                x-transition:enter="fi-select-enter"
                                x-transition:enter-start="fi-select-enter-start"
                                x-transition:enter-end="fi-select-enter-end"
                                x-transition:leave="fi-select-leave"
                                x-transition:leave-start="fi-select-leave-start"
                                x-transition:leave-end="fi-select-leave-end"
                                x-on:click.outside="open = false"
                                class="fi-select-panel"
                            >
                                <div x-on:click="selectOption('')" class="fi-select-option" :class="{ 'fi-select-option--active': value === '' }">
                                    <span>{{ __('Semua Kelurahan') }}</span>
                                </div>
                                <div class="fi-select-options-scroll">
                        <template x-for="option in options" :key="option.id">
                            <div x-on:click="selectOption(option.id, option.nama)" class="fi-select-option" :class="{ 'fi-select-option--active': value === String(option.id) }">
                                <span x-text="option.nama" class="fi-select-option-text"></span>
                                            <span x-show="value === String(option.id)" class="fi-select-check">
                                                <x-icons.ui name="check" />
                                            </span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full sm:w-auto sm:ml-auto flex flex-wrap items-center justify-between gap-2">
                        <button type="button" id="filter-reset"
                            class="inline-flex h-10 sm:h-12 items-center gap-2 px-2.5 sm:px-4 rounded-xl bg-brand-50 dark:bg-brand-900/25 border border-brand-200 dark:border-brand-800/60 text-[11px] sm:text-sm font-bold text-brand-700 dark:text-brand-300 hover:bg-brand-100 dark:hover:bg-brand-900/40 hover:border-brand-300 dark:hover:border-brand-700/70 hover:ring-2 hover:ring-brand-500/30 focus:outline-none transition whitespace-nowrap">
                            <x-icons.ui name="refresh" class="h-4 w-4" />
                            {{ __('Reset Filter') }}
                        </button>
                        <div class="flex h-10 sm:h-auto sm:py-2 items-center gap-1.5 sm:gap-2.5 px-2 sm:px-3.5 rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                            <x-icons.sampah class="h-3.5 w-3.5 sm:h-5 sm:w-5 shrink-0 text-slate-500 dark:text-slate-400" />
                            <span id="armada-toggle-label" class="text-[9.5px] sm:text-[11px] font-bold text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ __('Sembunyikan Armada') }}</span>
                            <label class="ml-0.5 sm:ml-1 cursor-pointer flex-shrink-0">
                                <input type="checkbox" id="armada-toggle" class="sr-only" checked aria-label="{{ __('Sembunyikan Armada') }}">
                                <span class="dlh-switch" style="--switch-color:#10b981"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="persampahan-map-frame mt-4 reveal reveal-scale">
                <div class="map-container" role="region" aria-label="Peta fasilitas persampahan dan armada DLH Kota Palu">
                <div id="peta-persampahan-map" style="width:100%;height:100%"></div>
                </div>
            </div>
        </div>

        <section class="persampahan-stats-card reveal" aria-labelledby="statistik-sampah-title">
            <div class="persampahan-stats-overview">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex min-w-0 items-start gap-3.5">
                        <span class="persampahan-stats-icon">
                            <x-icons.ton-sampah class="h-6 w-6" />
                        </span>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h2 id="statistik-sampah-title" class="persampahan-stats-heading">{{ __('Statistik Timbulan Sampah') }}</h2>
                            </div>
                            <p class="persampahan-stats-description">{{ __('Pantau volume sampah yang dicatat admin DLH Kota Palu secara transparan dan akurat. Pilih periode untuk melihat seluruh catatan yang tersedia tanpa mencampurkan periode pengukuran.') }}</p>
                        </div>
                    </div>
                    <span class="persampahan-stats-source flex-shrink-0 self-start">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <x-icons.ui name="shield" class="h-3.5 w-3.5" />
                        {{ __('Data Layanan DLH') }}
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

                        {{-- Tampilan Toggle Tipe Chart --}}
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
                        <canvas id="statistik-sampah-chart" height="280" role="img"
                            aria-label="{{ __('Grafik statistik timbulan sampah') }}"
                            data-series='@json($chartSeries)'
                            data-period-labels='@json($chartPeriodLabels)'
                            data-default-period="{{ $chartDefaultPeriod }}"></canvas>
                    </div>
                    <div id="statistik-sampah-empty" class="persampahan-chart-empty" hidden>
                        <div class="max-w-sm mx-auto text-center py-6">
                            <span class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-3 shadow-inner">
                                <x-icons.ui name="document" class="h-6 w-6" />
                            </span>
                            <p class="font-bold text-slate-800 dark:text-slate-100 text-sm">{{ __('Belum ada data untuk periode ini') }}</p>
                            <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ __('Admin DLH dapat menambahkan catatan statistik melalui menu Manajemen Statistik Sampah.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Tabel / Rincian Catatan Periode --}}
                <div id="statistik-breakdown-wrapper" class="persampahan-breakdown-box">
                    <div class="flex items-center justify-between px-4 py-3 bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-200/80 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <x-icons.ui name="table" class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ __('Rincian Catatan Volume Sampah') }}</h3>
                        </div>
                        <span id="statistik-breakdown-count" class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60">0 Catatan</span>
                    </div>
                    <div class="overflow-x-auto max-h-56 overflow-y-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100/60 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200/60 dark:border-slate-800/60 sticky top-0 backdrop-blur-sm">
                                <tr>
                                    <th class="py-2 px-4 w-12 text-center">#</th>
                                    <th class="py-2 px-4">{{ __('Tanggal Pencatatan') }}</th>
                                    <th class="py-2 px-4">{{ __('Periode') }}</th>
                                    <th class="py-2 px-4 text-right">{{ __('Volume Sampah') }}</th>
                                    <th class="py-2 px-4 w-32 hidden sm:table-cell">{{ __('Porsi') }}</th>
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
                    <span>{{ __('Grafik menampilkan seluruh data timbulan sampah resmi yang tercatat di DLH Kota Palu dalam satuan Tonase (Ton).') }}</span>
                </p>
            </div>
        </section>
    </div>
</div>

@push('scripts')
{{-- Task 5: peta persampahan lazy-load map via ensureMaplibreLoaded; charts tetap eager --}}
@vite('resources/js/dashboard-charts.js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var mapLayers = @json($layers);
        var initialArmada = @json($armada);
        var vehicleTypes = @json(array_values($vehicleTypes));
        var defaultType = @json($defaultType);

        // State filter armada (default: semua tampil)
        window._dlhArmadaFilter = {
            types: new Set(['pickup', 'truck']),
            statuses: new Set(['active', 'parked'])
        };
        window._dlhArmadaVisible = true;
        window._dlhLastArmada = initialArmada || [];

        var activeType = defaultType;
        var activeKelurahan = null;

        // Selalu inisialisasi peta walau tidak ada data layer
        function initMap() {
            window.ensureMaplibreLoaded(function () {
                dlhPetaPersampahan('peta-persampahan-map', mapLayers, initialArmada, {
                    vehicleTypes: vehicleTypes,
                    defaultType: defaultType
                });
            });
        }
        initMap();

        function typeById(key) {
            return vehicleTypes.find(function (t) { return t.key === key; }) || null;
        }

        // Akses data Alpine dengan aman (Alpine boot saat DOMContentLoaded)
        function alpineData(el) {
            if (!window.Alpine || !el) return null;
            try { return Alpine.$data(el); } catch (e) { return null; }
        }

        // Sinkronkan dropdown "Tipe Kendaraan" (komponen x-admin.select / Alpine)
        function syncTypeSelect(key) {
            var input = document.getElementById('filter-type');
            var root = input ? input.closest('.fi-field') : null;
            var data = alpineData(root);
            if (data && String(data.selected) !== String(key)) {
                data.selected = String(key);
            }
        }

        // Isi opsi dropdown kelurahan berdasarkan tipe kendaraan terpilih
        function populateKelurahan(t) {
            var input = document.getElementById('filter-kelurahan');
            var root = input ? input.closest('[x-data]') : null;
            var data = alpineData(root);
            if (!data) return;
            data.options = (t && t.kelurahans) ? t.kelurahans.slice() : [];
            data.value = '';
            data.selectedLabel = '';
            data.open = false;
        }

        // Set tipe kendaraan aktif (kartu & dropdown), reset kelurahan
        function setActiveType(key) {
            var t = typeById(key);
            if (!t) {
                syncTypeSelect(activeType);
                return;
            }
            activeType = key;
            activeKelurahan = null;

            document.querySelectorAll('.vehicle-card').forEach(function (card) {
                var active = card.dataset.type === key;
                card.classList.toggle('vehicle-card-active', active);
                card.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            syncTypeSelect(key);

            populateKelurahan(t);

            if (window.dlhPetaPersampahanSelectType) window.dlhPetaPersampahanSelectType(key);
            if (window.dlhPetaPersampahanSelectKelurahan) window.dlhPetaPersampahanSelectKelurahan(null);
        }

        // Set kelurahan aktif
        function setActiveKelurahan(layerId) {
            activeKelurahan = layerId ? String(layerId) : null;
            if (window.dlhPetaPersampahanSelectKelurahan) {
                window.dlhPetaPersampahanSelectKelurahan(activeKelurahan);
            }
        }

        // Klik kartu tipe kendaraan
        document.querySelectorAll('.vehicle-card').forEach(function (card) {
            card.addEventListener('click', function () {
                setActiveType(card.dataset.type);
            });
        });

        // Dropdown tipe kendaraan
        var typeSelect = document.getElementById('filter-type');
        if (typeSelect) {
            typeSelect.addEventListener('change', function () {
                setActiveType(this.value);
            });
        }

        // Dropdown kelurahan
        var kelurahanSelect = document.getElementById('filter-kelurahan');
        if (kelurahanSelect) {
            kelurahanSelect.addEventListener('change', function () {
                setActiveKelurahan(this.value || null);
            });
        }

        // Reset filter -> kembali ke kondisi default
        var resetBtn = document.getElementById('filter-reset');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                setActiveType(defaultType);
            });
        }

        // Toggle armada (tampilkan/sembunyikan armada di peta)
        var armadaToggle = document.getElementById('armada-toggle');
        if (armadaToggle) {
            armadaToggle.addEventListener('change', function () {
                window._dlhArmadaVisible = this.checked;
                if (window.dlhPetaPersampahanSetArmada) {
                    window.dlhPetaPersampahanSetArmada(this.checked);
                }
                var label = document.getElementById('armada-toggle-label');
                if (label) {
                    label.textContent = this.checked
                        ? '{{ __('Sembunyikan Armada') }}'
                        : '{{ __('Munculkan Armada') }}';
                }
            });
        }

        // Polling armada setiap 30 detik
        setInterval(function () {
            fetch('/api/armada-aktif')
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.status && res.data) {
                        window._dlhLastArmada = res.data;
                        window.dlhPetaPersampahanDrawArmada(res.data);
                    }
                })
                .catch(function () {});
        }, 30000);

        // Dropdown tipe kendaraan (komponen x-public.select): buang opsi kosong
        // agar single-select selalu memiliki nilai terpilih.
        (function () {
            var input = document.getElementById('filter-type');
            var field = input ? input.closest('.fi-field') : null;
            if (!field) return;
            var panel = field.querySelector('.fi-select-panel');
            if (!panel) return;
            Array.prototype.forEach.call(panel.children, function (child) {
                if (child.classList.contains('fi-select-option')) child.remove();
            });
        })();

        // Inisialisasi awal tampilan filter (defer agar Alpine sudah boot)
        setTimeout(function () {
            setActiveType(defaultType);
        }, 0);

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

                    // If only 1 data point, draw a neat dashed drop-line to baseline and a halo around the point
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

                        // Soft halo around the single point
                        ctx.beginPath();
                        ctx.arc(singlePoint.x, singlePoint.y, 14, 0, Math.PI * 2);
                        ctx.fillStyle = dark ? 'rgba(52, 211, 153, 0.18)' : 'rgba(16, 185, 129, 0.18)';
                        ctx.fill();
                        ctx.restore();
                    }

                    meta.data.forEach(function (point, idx) {
                        var val = chart.data.datasets[0].data[idx];
                        if (val === null || val === undefined) return;

                        // On multi points, draw floating pill on latest point; on few points, draw on all
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
                    var barGradient = context.createLinearGradient(0, 0, 0, canvas.clientHeight || 280);
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
                    var lineFill = context.createLinearGradient(0, 0, 0, canvas.clientHeight || 280);
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
