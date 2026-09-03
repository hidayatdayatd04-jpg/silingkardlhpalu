@extends('layouts.app')

@section('title', 'Peta Persampahan - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Peta interaktif titik fasilitas persampahan (TPA, TPST, TPS3R) dan rute jalur pengangkutan sampah per kelurahan di Kota Palu.')

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
        overflow: visible;
        z-index: 12;
        border-color: rgba(16, 76, 51, .12) !important;
        border-radius: 1.5rem !important;
        box-shadow: 0 1px 2px rgba(10, 48, 30, .04), 0 22px 42px -30px rgba(10, 48, 30, .34) !important;
    }
    .persampahan-control-panel::before {
        content: '';
        position: absolute;
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
        height: clamp(28rem, 62vw, 44rem);
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

    /* Switch toggle style */
    .dlh-switch {
        display: inline-block;
        width: 36px;
        height: 20px;
        border-radius: 999px;
        background: #cbd5e1;
        position: relative;
        transition: background .2s ease;
    }
    .dark .dlh-switch {
        background: #334155;
    }
    .dlh-switch::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 16px;
        height: 16px;
        border-radius: 999px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,.2);
        transition: transform .2s ease;
    }
    input:checked + .dlh-switch {
        background: var(--switch-color, #10b981);
    }
    input:checked + .dlh-switch::after {
        transform: translateX(16px);
    }

    /* Kartu Kendaraan */
    .vehicle-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .75rem 1rem;
        border-radius: 1rem;
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        cursor: pointer;
        transition: border-color .18s ease, box-shadow .18s ease, transform .15s ease;
        text-align: left;
    }
    .vehicle-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 6px 20px -6px rgba(0, 0, 0, .08);
        transform: translateY(-1px);
    }
    .vehicle-card:active {
        transform: translateY(0);
    }
    .vehicle-card-active {
        border-color: var(--vc, #10b981) !important;
        background: #ffffff;
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--vc, #10b981) 18%, transparent),
                    0 10px 25px -8px color-mix(in srgb, var(--vc, #10b981) 35%, transparent) !important;
    }
    .vehicle-icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: .75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: color-mix(in srgb, var(--vc, #10b981) 12%, white);
        color: var(--vc, #10b981);
        transition: background .18s ease, color .18s ease;
    }
    .vehicle-card-active .vehicle-icon {
        background: var(--vc, #10b981);
        color: #ffffff;
    }
    .vehicle-name {
        font-size: .8125rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.25;
        display: block;
    }
    .vehicle-routes {
        font-size: .6875rem;
        color: #64748b;
        margin-top: 1px;
        display: block;
    }
    .vehicle-check {
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 999px;
        border: 1.5px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: transparent;
        transition: all .18s ease;
    }
    .vehicle-card-active .vehicle-check {
        background: var(--vc, #10b981);
        border-color: var(--vc, #10b981);
        color: #ffffff;
    }
    .dark .vehicle-card {
        background: #0b1120;
        border-color: #1e293b;
    }
    .dark .vehicle-card:hover {
        border-color: rgba(255, 255, 255, .18);
        box-shadow: 0 12px 30px -10px rgba(0, 0, 0, .7);
    }
    .dark .vehicle-card-active {
        background: #0f172a;
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--vc, #10b981) 25%, transparent),
                    0 14px 32px -10px color-mix(in srgb, var(--vc, #10b981) 45%, transparent) !important;
    }
    .dark .vehicle-icon { background: color-mix(in srgb, var(--vc, #10b981) 20%, #0b1120); }
    .dark .vehicle-card-active .vehicle-icon { background: var(--vc, #10b981); }
    .dark .vehicle-name { color: #f1f5f9; }
    .dark .vehicle-routes { color: #94a3b8; }
    .dark .vehicle-check { border-color: #475569; background: #1e293b; }

    @media (max-width: 640px) {
        .peta-persampahan-page::before { right: -13rem; }
        .peta-persampahan-page .map-container { height: 26rem; border-radius: 1.25rem; }
    }
</style>
@endpush

@section('content')
<div class="peta-persampahan-page space-y-7">
    <x-public.page-hero
        badge="{{ __('Sampah & LB3') }}"
        icon="map-pin"
        title="{{ __('Peta Persampahan') }}"
        description="{{ __('Peta interaktif terpadu sebaran fasilitas Tempat Pemrosesan Akhir (TPA), TPS3R, serta rute jalur pengangkutan sampah di wilayah Kota Palu.') }}"
    />

    <div class="space-y-6">
        <div class="reveal">
            <div class="persampahan-control-panel bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-4 sm:p-5 space-y-4">
                {{-- Header panel filter --}}
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-900/25 border border-brand-200 dark:border-brand-800/60 flex items-center justify-center text-brand-600 dark:text-brand-300 flex-shrink-0">
                        <x-icons.titik-tps class="h-[1.125rem] w-[1.125rem]" />
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ __('Pilih Tipe Armada & Rute') }}</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-snug">{{ __('Pilih tipe kendaraan untuk menampilkan rute jalur angkut sampah per kelurahan serta lokasi fasilitas persampahan di Kota Palu.') }}</p>
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
                            <span class="vehicle-routes">{{ $vt['total'] }} {{ __('jalur rute') }}</span>
                        </span>
                        <span class="vehicle-check">
                            <x-icons.ui name="check" class="h-3 w-3" />
                        </span>
                    </button>
                    @endforeach
                </div>

                {{-- Baris filter: dropdown tipe kendaraan, kelurahan, toggle fasilitas TPA & reset --}}
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

                    {{-- Toggle Fasilitas TPA & TPS3R --}}
                    <div class="flex h-10 sm:h-12 items-center gap-2 px-3 sm:px-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ __('Fasilitas TPA & TPS3R') }}</span>
                        <label class="ml-1 cursor-pointer flex-shrink-0" title="{{ __('Tampilkan atau sembunyikan titik TPA & fasilitas') }}">
                            <input type="checkbox" id="fasilitas-toggle" class="sr-only" checked aria-label="{{ __('Fasilitas TPA & TPS3R') }}">
                            <span class="dlh-switch" style="--switch-color:#b45309"></span>
                        </label>
                    </div>

                    {{-- Tombol Reset Filter --}}
                    <div class="w-full sm:w-auto sm:ml-auto flex items-center justify-end">
                        <button type="button" id="filter-reset"
                            class="inline-flex h-10 sm:h-12 items-center gap-2 px-3 sm:px-4 rounded-xl bg-brand-50 dark:bg-brand-900/25 border border-brand-200 dark:border-brand-800/60 text-[11px] sm:text-sm font-bold text-brand-700 dark:text-brand-300 hover:bg-brand-100 dark:hover:bg-brand-900/40 hover:border-brand-300 dark:hover:border-brand-700/70 hover:ring-2 hover:ring-brand-500/30 focus:outline-none transition whitespace-nowrap cursor-pointer">
                            <x-icons.ui name="refresh" class="h-4 w-4" />
                            {{ __('Reset Filter') }}
                        </button>
                    </div>
                </div>

                {{-- Baris Legenda Terpadu --}}
                <div class="border-t border-slate-100 dark:border-slate-800/80 pt-3 flex flex-wrap items-center gap-3 text-xs text-slate-600 dark:text-slate-400">
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ __('Legenda Peta:') }}</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                        <span class="w-3.5 h-1 rounded-full" id="legend-route-bar" style="background: {{ $vehicleTypes[$defaultType]['color'] ?? '#ef4444' }}"></span>
                        <span id="legend-route-text">{{ __('Rute') }} {{ $vehicleTypes[$defaultType]['label'] ?? 'Armada' }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-amber-800 dark:text-amber-300">
                        <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                        <span>{{ __('TPA Kawatuna & TPS3R') }}</span>
                    </span>
                </div>
            </div>

            {{-- Map Container --}}
            <div class="persampahan-map-frame mt-4 reveal reveal-scale">
                <div class="map-container" role="region" aria-label="Peta terpadu jalur angkut sampah dan fasilitas TPA DLH Kota Palu">
                    <div id="peta-persampahan-map" style="width:100%;height:100%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@vite('resources/js/map-bundle.js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var mapLayers = @json($layers);
        var vehicleTypes = @json(array_values($vehicleTypes));
        var defaultType = @json($defaultType);

        var activeType = defaultType;
        var activeKelurahan = null;

        function initMap() {
            window.ensureMaplibreLoaded(function () {
                dlhPetaPersampahan('peta-persampahan-map', mapLayers, null, {
                    vehicleTypes: vehicleTypes,
                    defaultType: defaultType
                });
            });
        }
        initMap();

        function typeById(key) {
            return vehicleTypes.find(function (t) { return t.key === key; }) || null;
        }

        function alpineData(el) {
            if (!window.Alpine || !el) return null;
            try { return Alpine.$data(el); } catch (e) { return null; }
        }

        function syncTypeSelect(key) {
            var input = document.getElementById('filter-type');
            var root = input ? input.closest('.fi-field') : null;
            var data = alpineData(root);
            if (data && String(data.selected) !== String(key)) {
                data.selected = String(key);
            }
        }

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

        function updateLegend(t) {
            var bar = document.getElementById('legend-route-bar');
            var txt = document.getElementById('legend-route-text');
            if (bar && t) bar.style.background = t.color;
            if (txt && t) txt.textContent = 'Rute ' + t.label;
        }

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
            updateLegend(t);

            if (window.dlhPetaPersampahanSelectType) window.dlhPetaPersampahanSelectType(key);
            if (window.dlhPetaPersampahanSelectKelurahan) window.dlhPetaPersampahanSelectKelurahan(null);
        }

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

        // Toggle Fasilitas TPA & TPS3R
        var fasilitasToggle = document.getElementById('fasilitas-toggle');
        if (fasilitasToggle) {
            fasilitasToggle.addEventListener('change', function () {
                if (window.dlhPetaPersampahanToggleFasilitas) {
                    window.dlhPetaPersampahanToggleFasilitas(this.checked);
                }
            });
        }

        // Reset filter -> kembali ke kondisi default & tampilkan fasilitas
        var resetBtn = document.getElementById('filter-reset');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                if (fasilitasToggle) {
                    fasilitasToggle.checked = true;
                    if (window.dlhPetaPersampahanToggleFasilitas) {
                        window.dlhPetaPersampahanToggleFasilitas(true);
                    }
                }
                setActiveType(defaultType);
            });
        }

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

        setTimeout(function () {
            setActiveType(defaultType);
        }, 0);
    });
</script>
@endpush
@endsection
