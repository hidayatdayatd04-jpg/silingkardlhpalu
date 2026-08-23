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
        border-color: rgba(16, 76, 51, .12) !important;
        border-radius: 1.5rem !important;
        box-shadow: 0 1px 2px rgba(10, 48, 30, .04), 0 20px 38px -30px rgba(10, 48, 30, .3) !important;
    }
    .persampahan-stats-card::after {
        content: '';
        position: absolute;
        right: -3rem;
        bottom: -4rem;
        width: 13rem;
        height: 13rem;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(37, 167, 199, .12), transparent 67%);
        pointer-events: none;
    }
    .persampahan-stats-card > * {
        position: relative;
        z-index: 1;
    }
    .persampahan-stats-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.75rem;
        height: 2.75rem;
        border: 1px solid rgba(16, 137, 83, .16);
        border-radius: .9rem;
        color: #168953;
        background: linear-gradient(135deg, rgba(209, 247, 226, .88), rgba(233, 249, 243, .72));
    }
    .dark .persampahan-control-panel,
    .dark .persampahan-stats-card {
        border-color: rgba(110, 231, 183, .16) !important;
        box-shadow: 0 20px 42px -32px rgba(0, 0, 0, .68) !important;
    }
    .dark .persampahan-stats-icon {
        border-color: rgba(110, 231, 183, .18);
        color: #6ee7b7;
        background: rgba(16, 137, 83, .16);
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

        <div class="persampahan-stats-card reveal bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-5 sm:p-6">
            <div class="mb-5 flex items-center gap-3">
                <span class="persampahan-stats-icon">
                    <x-icons.ton-sampah class="h-5 w-5" />
                </span>
                <div>
                    <h3 class="text-base font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Statistik Timbulan Sampah') }}</h3>
                    <p class="mt-0.5 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ __('Ringkasan volume sampah berdasarkan data layanan yang tersedia.') }}</p>
                </div>
            </div>
            <div class="min-h-[13rem]">
                <canvas id="statistik-sampah-chart" height="200"
                    data-labels='@json($chartLabels)'
                    data-values='@json($chartValues)'></canvas>
            </div>
        </div>
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
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: JSON.parse(canvas.dataset.labels || '[]'),
                    datasets: [{
                        label: '{{ __("Volume (ton)") }}',
                        data: JSON.parse(canvas.dataset.values || '[]'),
                        backgroundColor: 'rgba(16,185,129,0.6)',
                        borderColor: 'rgb(16,185,129)',
                        borderWidth: 1,
                    }],
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } },
                },
            });
        }
    });
</script>
@endpush
@endsection
