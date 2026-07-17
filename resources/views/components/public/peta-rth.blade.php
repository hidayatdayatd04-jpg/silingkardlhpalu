<?php

use Livewire\Component;

new class extends Component {
    protected $mapData = null;

    public function getMapData(): array
    {
        if ($this->mapData !== null) {
            return $this->mapData;
        }

        $this->mapData = [
            'gis_layers' => \App\Models\GisDataLayer::where('bidang', 'rth')
                ->visible()
                ->public()
                ->orderBy('z_index')
                ->orderBy('created_at')
                ->get()
                ->map(fn ($layer) => [
                    'id' => $layer->id,
                    'nama_layer' => $layer->nama_layer,
                    'deskripsi' => $layer->deskripsi,
                    'jenis_geometri' => $layer->jenis_geometri,
                    'metadata' => $layer->metadata ?? ['color' => \App\Models\GisDataLayer::defaultColor($layer->bidang)],
                    'geojson' => $layer->toGeoJson(),
                ])->toArray(),
        ];

        return $this->mapData;
    }
};
?>

@php
    $mapData = $this->getMapData();
@endphp

<style>
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
    .gis-layer-toggle:checked + .custom-layer-chip {
        background-color: #ffffff;
        color: #0f172a;
        border-color: #e2e8f0;
        box-shadow: 0 4px 12px rgba(15,23,42,0.04), 0 0 0 1px rgba(15,23,42,0.02);
    }
    .dark .gis-layer-toggle:checked + .custom-layer-chip {
        background-color: #1e293b;
        color: #f8fafc;
        border-color: #334155;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .gis-layer-toggle:checked + .custom-layer-chip .dot {
        transform: scale(1.2);
        background-color: var(--active-color);
    }
</style>

<div class="space-y-6">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 shadow-sm">
        <div class="custom-layer-container">
            <!-- Custom GIS Layers from Admin -->
            @foreach ($mapData['gis_layers'] as $gl)
                @php
                    $color = $gl['metadata']['color'] ?? '#22c55e';
                @endphp
                <label class="cursor-pointer">
                    <input type="checkbox" class="gis-layer-toggle sr-only" data-layer="{{ $gl['id'] }}" checked>
                    <div class="custom-layer-chip" style="--active-color: {{ $color }};">
                        <span class="dot"></span>
                        <span>{{ $gl['nama_layer'] }}</span>
                        <span class="text-xs text-slate-400 font-semibold">({{ count($gl['geojson']['features'] ?? []) }})</span>
                    </div>
                </label>
            @endforeach
        </div>
        <div wire:ignore class="w-full h-[500px] rounded-2xl overflow-hidden"
             x-data x-init="setTimeout(function(){dlhPetaRth('peta-rth-map',@js($mapData))},100)">
            <div id="peta-rth-map" style="width:100%;height:100%"></div>
        </div>
    </div>
</div>
