@extends('layouts.app')

@section('title', 'Peta Persampahan - DLH Kota Palu')
@section('description', 'Peta interaktif titik TPA, TPST, Bank Sampah, TPS, pelacakan armada real-time, statistik timbulan sampah, dan jadwal armada DLH Kota Palu.')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.1.1/dist/maplibre-gl.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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
    .custom-layer-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 8px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        margin-bottom: 16px;
    }
    .dark .custom-layer-container {
        background: rgba(15, 23, 42, 0.3);
        border-color: #1e293b;
    }
    .custom-layer-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        border: 1.5px solid transparent;
        background: transparent;
        color: #64748b;
        user-select: none;
    }
    .custom-layer-chip:hover {
        color: #334155;
        background: rgba(15, 23, 42, 0.03);
    }
    .dark .custom-layer-chip:hover {
        color: #cbd5e1;
        background: rgba(255,255,255,0.03);
    }
    .custom-layer-chip .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #cbd5e1;
        transition: transform 0.2s ease, background-color 0.2s ease;
    }
    .dark .custom-layer-chip .dot {
        background-color: #475569;
    }
    .layer-toggle:checked + .custom-layer-chip {
        background-color: #ffffff;
        color: #0f172a;
        border-color: #e2e8f0;
        box-shadow: 0 4px 12px rgba(15,23,42,0.04), 0 0 0 1px rgba(15,23,42,0.02);
    }
    .dark .layer-toggle:checked + .custom-layer-chip {
        background-color: #1e293b;
        color: #f8fafc;
        border-color: #334155;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .layer-toggle:checked + .custom-layer-chip .dot {
        transform: scale(1.2);
        background-color: var(--active-color);
    }
    .custom-vehicle-icon{background:transparent!important;border:none!important;box-shadow:none!important;cursor:pointer}
    .custom-vehicle-icon img{filter:drop-shadow(0 2px 6px rgba(0,0,0,0.3));transition:transform .3s ease}
    .custom-vehicle-icon:hover img{filter:drop-shadow(0 3px 10px rgba(0,0,0,0.4))}
    .armada-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 2px 10px;
        background: rgba(16,185,129,0.1);
        border: 1px solid rgba(16,185,129,0.2);
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        color: #059669;
    }
    .dark .armada-count-badge {
        background: rgba(16,185,129,0.15);
        border-color: rgba(16,185,129,0.3);
        color: #34d399;
    }
    .armada-count-badge .pulse-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.3); }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <x-public.page-hero badge="{{ __('Sampah & LB3') }}" title="{{ __('Peta & Informasi Persampahan') }}" description="{{ __('Lihat lokasi fasilitas persampahan, lacak armada real-time, statistik timbulan sampah, dan jadwal armada.') }}" />

    <div class="space-y-8">
        <div>
            <div class="custom-layer-container" id="layer-chips">
                @foreach($layers as $layer)
                @php
                    $color = $layer['metadata']['color'] ?? '#6b7280';
                @endphp
                <label class="cursor-pointer">
                    <input type="checkbox" checked data-layer="{{ $layer['id'] }}" class="layer-toggle sr-only" />
                    <div class="custom-layer-chip" style="--active-color: {{ $color }};">
                        <span class="dot"></span>
                        <span>{{ $layer['nama_layer'] }}</span>
                        <span class="text-xs text-slate-400 font-semibold">({{ count($layer['geojson']['features'] ?? []) }})</span>
                    </div>
                </label>
                @endforeach

                <label class="cursor-pointer">
                    <input type="checkbox" checked data-layer="armada" class="layer-toggle sr-only" />
                    <div class="custom-layer-chip" style="--active-color: #10b981;">
                        <span class="dot"></span>
                        <span>{{ __('Pelacakan Armada') }}</span>
                        <span class="armada-count-badge" id="armada-count-badge"><span class="pulse-dot"></span><span id="armada-count-num">{{ $armada->count() }}</span></span>
                    </div>
                </label>
            </div>

            <div class="map-container">
                <div id="peta-persampahan-map" style="width:100%;height:100%"></div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-6 mt-4 py-3 px-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl">
                <div class="flex items-center gap-2.5">
                    <img src="/assets/tracking/car_acc_on.png" class="w-8 h-8 object-contain" alt="Pickup">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ __('Mobil Pickup (Roda 4)') }}</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <img src="/assets/tracking/truck_acc_on.png" class="w-8 h-8 object-contain" alt="Dump Truck">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ __('Dump Truck (Roda 6)') }}</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <img src="/assets/tracking/car_parking.png" class="w-8 h-8 object-contain" alt="Parkir">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ __('Parkir / Mesin Mati') }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm">
                <h3 class="text-lg font-bold mb-4">{{ __('Statistik Timbulan Sampah') }}</h3>
                <canvas id="statistik-sampah-chart" height="200"
                    data-labels='@json($chartLabels)'
                    data-values='@json($chartValues)'></canvas>
            </div>

            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">{{ __('Jadwal & Rute Armada') }}</h3>
                    <div class="armada-count-badge"><span class="pulse-dot"></span>{{ $armada->count() }} {{ __('unit aktif') }}</div>
                </div>
                <div class="space-y-3 max-h-[280px] overflow-y-auto">
                    @forelse ($jadwal as $j)
                        <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                            <div class="font-semibold text-sm">{{ $j->nama_rute }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ $j->hari }} · {{ $j->jam }}</div>
                            @if ($j->wilayah_dilalui)
                                <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">{{ $j->wilayah_dilalui }}</div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('Belum ada jadwal armada.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var mapLayers = @json($layers);
        var initialArmada = @json($armada);
        var armadaVisible = true;

        function initMap() {
            if (typeof dlhPetaPersampahan === 'function' && mapLayers.length > 0) {
                dlhPetaPersampahan('peta-persampahan-map', mapLayers, initialArmada);
            } else {
                setTimeout(initMap, 100);
            }
        }
        initMap();

        // Armada toggle chip listener
        var armadaToggle = document.querySelector('[data-layer="armada"]');
        if (armadaToggle) {
            armadaToggle.addEventListener('change', function () {
                armadaVisible = this.checked;
                if (window._dlhArmadaMarkers) {
                    window._dlhArmadaMarkers.forEach(function (mk) {
                        armadaVisible ? mk.addTo(window._dlhMap) : mk.remove();
                    });
                }
            });
        }

        // Polling armada setiap 30 detik
        setInterval(function () {
            fetch('/api/armada-aktif')
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.status && res.data) {
                        window.dlhPetaPersampahanDrawArmada(res.data);
                        var countEl = document.getElementById('armada-count-num');
                        if (countEl) countEl.textContent = res.data.length;
                    }
                })
                .catch(function () {});
        }, 30000);

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
