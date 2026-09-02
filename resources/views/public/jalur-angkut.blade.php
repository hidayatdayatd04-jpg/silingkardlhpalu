@extends('layouts.app')

@section('title', 'Peta Jalur Angkut Persampahan - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Peta interaktif titik fasilitas persampahan dan rute jalur pengangkutan sampah per kelurahan di Kota Palu.')

@push('styles')
<style>
    .jalur-angkut-page {
        position: relative;
        overflow-x: clip;
    }
    .jalur-angkut-page::before {
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
    .jalur-angkut-page .map-container {
        position: relative;
        width: 100%;
        height: clamp(26rem, 60vw, 42rem);
        border-radius: 1.5rem;
        overflow: hidden;
        isolation: isolate;
        border: 1px solid rgba(16, 76, 51, .14);
        background: #e7f2eb;
        box-shadow: 0 1px 2px rgba(10, 48, 30, .05), 0 24px 48px -30px rgba(10, 48, 30, .38);
    }
    .dark .jalur-angkut-page .map-container {
        border-color: rgba(110, 231, 183, .2);
        background: #0b241a;
    }
    .jalur-angkut-page .map-container .maplibregl-map {
        width: 100%;
        height: 100%;
    }
    .jalur-angkut-page .map-container .maplibregl-ctrl-group {
        border: 1px solid rgba(16, 76, 51, .12) !important;
        border-radius: 12px !important;
        overflow: hidden;
        box-shadow: 0 10px 24px -12px rgba(10, 48, 30, .35) !important;
    }
    .jalur-angkut-page .map-container .maplibregl-ctrl:not(.dlh-tools-ctrl):not(.dlh-tools-ctrl__item) {
        margin: 12px !important;
    }
    .jalur-angkut-page .map-container .maplibregl-ctrl-top-left {
        top: 12px !important;
        left: 12px !important;
    }
    .jalur-angkut-page .map-container .maplibregl-ctrl-bottom-right {
        bottom: 12px !important;
        right: 12px !important;
    }
    .jalur-angkut-page .map-container .maplibregl-popup-content {
        border: 1px solid rgba(16, 76, 51, .12) !important;
        border-radius: 16px !important;
        box-shadow: 0 18px 40px -16px rgba(10, 48, 30, .34) !important;
        padding: 0 !important;
        overflow: hidden;
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
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, background-color .2s ease;
        overflow: hidden;
    }
    .vehicle-card:hover {
        transform: translateY(-2px);
        border-color: rgba(15, 23, 42, .18);
        box-shadow: 0 12px 28px -12px rgba(15, 23, 42, .2);
    }
    .vehicle-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background: radial-gradient(circle at 100% 0%, var(--vc, #10b981), transparent 70%);
        opacity: 0;
        transition: opacity .25s ease;
        pointer-events: none;
    }
    .vehicle-card-active {
        border-color: var(--vc, #10b981) !important;
        background: #fff;
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--vc, #10b981) 18%, transparent),
                    0 14px 32px -12px color-mix(in srgb, var(--vc, #10b981) 35%, transparent) !important;
    }
    .vehicle-card-active::before { opacity: .12; }
    .vehicle-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: color-mix(in srgb, var(--vc, #10b981) 12%, #fff);
        color: var(--vc, #10b981);
        flex-shrink: 0;
        transition: transform .2s ease, background-color .2s ease;
    }
    .vehicle-card:hover .vehicle-icon { transform: scale(1.06); }
    .vehicle-card-active .vehicle-icon {
        background: var(--vc, #10b981);
        color: #fff;
        box-shadow: 0 6px 16px -4px color-mix(in srgb, var(--vc, #10b981) 55%, transparent);
    }
    .vehicle-name {
        font-size: .875rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.25;
        display: block;
    }
    .vehicle-check {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1.5px solid #cbd5e1;
        background: #fff;
        color: transparent;
        flex-shrink: 0;
        transition: all .2s ease;
    }
    .vehicle-card-active .vehicle-check {
        border-color: var(--vc, #10b981);
        background: var(--vc, #10b981);
        color: #fff;
        box-shadow: 0 2px 6px color-mix(in srgb, var(--vc, #10b981) 40%, transparent);
    }

    .dark .vehicle-card {
        background: #0b1120;
        border-color: rgba(255, 255, 255, .08);
        box-shadow: 0 4px 20px -8px rgba(0, 0, 0, .5);
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
    .dark .vehicle-check { border-color: #475569; background: #1e293b; }

    @media (max-width: 640px) {
        .jalur-angkut-page::before { right: -13rem; }
        .jalur-angkut-page .map-container { height: 26rem; border-radius: 1.25rem; }
    }
</style>
@endpush

@section('content')
<div class="jalur-angkut-page space-y-7">
    <x-public.page-hero
        badge="{{ __('Sampah & LB3') }}"
        icon="map-pin"
        title="{{ __('Peta Jalur Angkut Persampahan') }}"
        description="{{ __('Lihat titik fasilitas persampahan dan jalur rute pengangkutan sampah di setiap kelurahan wilayah Kota Palu.') }}"
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
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ __('Pilih Tipe Kendaraan') }}</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-snug">{{ __('Pilih tipe armada untuk menampilkan rute jalur angkut sampah pada peta.') }}</p>
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

                {{-- Baris filter: dropdown tipe kendaraan, kelurahan & reset --}}
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

                    <div class="w-full sm:w-auto sm:ml-auto flex items-center justify-end">
                        <button type="button" id="filter-reset"
                            class="inline-flex h-10 sm:h-12 items-center gap-2 px-3 sm:px-4 rounded-xl bg-brand-50 dark:bg-brand-900/25 border border-brand-200 dark:border-brand-800/60 text-[11px] sm:text-sm font-bold text-brand-700 dark:text-brand-300 hover:bg-brand-100 dark:hover:bg-brand-900/40 hover:border-brand-300 dark:hover:border-brand-700/70 hover:ring-2 hover:ring-brand-500/30 focus:outline-none transition whitespace-nowrap cursor-pointer">
                            <x-icons.ui name="refresh" class="h-4 w-4" />
                            {{ __('Reset Filter') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="persampahan-map-frame mt-4 reveal reveal-scale">
                <div class="map-container" role="region" aria-label="Peta jalur rute pengangkutan persampahan DLH Kota Palu">
                    <div id="jalur-angkut-map" style="width:100%;height:100%"></div>
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
                dlhPetaPersampahan('jalur-angkut-map', mapLayers, null, {
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
