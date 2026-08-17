@extends('layouts.app')

@section('title', 'Peta Persampahan - DLH Kota Palu')
@section('description', 'Peta interaktif titik TPA, TPST, Bank Sampah, TPS, pelacakan armada real-time, dan statistik timbulan sampah DLH Kota Palu.')

@push('styles')
<style>
    .map-container {
        position: relative;
        width: 100%;
        height: 550px;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    }
    .map-container .maplibregl-map {
        width: 100%;
        height: 100%;
    }
    .map-container .maplibregl-ctrl-group {
        border-radius: 10px !important;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.1) !important;
    }
    .map-container .maplibregl-ctrl {
        margin: 12px !important;
    }
    .map-container .maplibregl-ctrl-top-left {
        top: 12px !important;
        left: 12px !important;
    }
    .map-container .maplibregl-ctrl-bottom-right {
        bottom: 12px !important;
        right: 12px !important;
    }
    .map-container .maplibregl-popup-content {
        border-radius: 12px !important;
        box-shadow: 0 8px 32px rgba(0,0,0,0.12) !important;
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

    .custom-vehicle-icon{background:transparent!important;border:none!important;box-shadow:none!important;cursor:pointer}
    .custom-vehicle-icon img{filter:drop-shadow(0 2px 6px rgba(0,0,0,0.3));transition:transform .3s ease}
    .custom-vehicle-icon:hover img{filter:drop-shadow(0 3px 10px rgba(0,0,0,0.4))}
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
</style>
@endpush

@section('content')
<div class="space-y-6">
    <x-public.page-hero badge="{{ __('Sampah & LB3') }}" title="{{ __('Peta & Informasi Persampahan') }}" description="{{ __('Lihat lokasi fasilitas persampahan, lacak armada real-time, dan statistik timbulan sampah.') }}" />

    <div class="space-y-8">
        <div>
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-4 sm:p-5 space-y-4">
                {{-- Header panel filter --}}
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-900/25 border border-brand-200 dark:border-brand-800/60 flex items-center justify-center text-brand-600 dark:text-brand-300 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
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
                            @if ($vt['key'] === 'pickup')
                            <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8.5h10.5v7.5H3z"/><path d="M13.5 8.5h3l3.5 4v3.5h-6.5"/><path d="M3 12.25h10.5"/><circle cx="7.2" cy="17.2" r="1.3"/><circle cx="16.8" cy="17.2" r="1.3"/></svg>
                            @elseif ($vt['key'] === 'kaisar')
                            <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="7" width="11.5" height="8" rx="1"/><path d="M14 10.5h3.5l2.5 2.5v2h-6"/><circle cx="7.2" cy="17.2" r="1.3"/><circle cx="17.3" cy="17.2" r="1.3"/></svg>
                            @else
                            <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 8.5h11v8h-11z"/><path d="M13.5 11.5h4l2.5 2v3h-6.5"/><circle cx="7.2" cy="17.6" r="1.3"/><circle cx="17.5" cy="17.6" r="1.3"/></svg>
                            @endif
                        </span>
                        <span class="flex-1 min-w-0">
                            <span class="vehicle-name">{{ $vt['label'] }}</span>
                        </span>
                        <span class="vehicle-check">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
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
                                <svg class="fi-select-chevron" :class="{ 'fi-select-chevron--open': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
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
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                            </span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="filter-reset"
                        class="inline-flex h-12 items-center gap-2 px-4 rounded-xl bg-brand-50 dark:bg-brand-900/25 border border-brand-200 dark:border-brand-800/60 text-sm font-bold text-brand-700 dark:text-brand-300 hover:bg-brand-100 dark:hover:bg-brand-900/40 hover:border-brand-300 dark:hover:border-brand-700/70 hover:ring-2 hover:ring-brand-500/30 focus:outline-none transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M4.58 15.5A7.5 7.5 0 0018.9 17M5.1 7A7.5 7.5 0 0118.9 7.5M19.42 8.5A7.5 7.5 0 005.1 7"/></svg>
                        {{ __('Reset Filter') }}
                    </button>
                    <div class="ml-auto flex items-center gap-2.5 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-500 dark:text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"/><circle cx="6.5" cy="17.5" r="1.5"/><circle cx="17.5" cy="17.5" r="1.5"/></svg>
                        <span id="armada-toggle-label" class="text-[11px] font-bold text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ __('Sembunyikan Armada') }}</span>
                        <label class="ml-1 cursor-pointer flex-shrink-0">
                            <input type="checkbox" id="armada-toggle" class="sr-only" checked aria-label="{{ __('Sembunyikan Armada') }}">
                            <span class="dlh-switch" style="--switch-color:#10b981"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4 map-container">
                <div id="peta-persampahan-map" style="width:100%;height:100%"></div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-lg font-bold mb-4">{{ __('Statistik Timbulan Sampah') }}</h3>
            <canvas id="statistik-sampah-chart" height="200"
                data-labels='@json($chartLabels)'
                data-values='@json($chartValues)'></canvas>
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
                    label.textContent = this.checked ? '{{ __('Sembunyikan Armada') }}' : '{{ __('Tampilkan Armada') }}';
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
