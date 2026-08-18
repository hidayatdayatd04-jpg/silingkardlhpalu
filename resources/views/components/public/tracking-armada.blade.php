<?php
use Livewire\Component;
use App\Models\GpsVehicleCache;

new class extends Component
{
    public string $search = '';
    public bool $filterChanged = false;
    public function updatedSearch() { $this->filterChanged = true; }
    public function getVehicles(): array
    {
        // Hanya kolom publik yang dikirim ke pengunjung — 'raw_data' berisi
        // payload mentah GPS tracker dan tidak boleh bocor ke halaman publik.
        $query = GpsVehicleCache::query()->select(GpsVehicleCache::PUBLIC_COLUMNS);
        if (filled($this->search)) $query->where('title', 'like', '%' . $this->search . '%');
        $vehicles = $query->get()->toArray();
        // JSON_HEX_* mencegah breakout </script> saat payload disisipkan via $this->js().
        $this->js('window.dispatchEvent(new CustomEvent("guest-map-vehicles-updated",{detail:{vehicles:' . json_encode($vehicles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ',fitBounds:' . ($this->filterChanged?'true':'false') . '}}))');
        $this->filterChanged = false;
        return $vehicles;
    }
    public function getActiveCount(): int { return GpsVehicleCache::count(); }
    public function getLastSync(): ?string { $l=GpsVehicleCache::max('updated_at'); return $l?\Carbon\Carbon::parse($l)->timezone('Asia/Makassar')->translatedFormat('d F Y, H:i').' WITA':null; }
};
?>
<div class="space-y-6" wire:poll.30s>
    <style>
        .maplibregl-canvas{image-rendering:-webkit-optimize-contrast;image-rendering:crisp-edges}
        .custom-vehicle-icon{background:transparent!important;border:none!important;box-shadow:none!important}
        .custom-vehicle-icon img{filter:drop-shadow(0 2px 6px rgba(0,0,0,0.3));transition:transform .3s ease,filter .2s ease}
        .custom-vehicle-icon:hover img{filter:drop-shadow(0 4px 12px rgba(0,0,0,0.4));transform:scale(1.1)}
        .maplibregl-popup-content{border-radius:14px!important;padding:0!important;box-shadow:0 12px 28px -4px rgba(0,0,0,.12),0 4px 12px -2px rgba(0,0,0,.08)!important;border:1px solid #e2e8f0!important}
        .maplibregl-popup-tip{border-top-color:white!important}
        .maplibregl-ctrl-logo{display:none!important}
        .maplibregl-ctrl-group{border-radius:10px!important;overflow:hidden!important;box-shadow:0 2px 12px rgba(0,0,0,.1)!important;border:1px solid rgba(0,0,0,.06)!important;background:rgba(255,255,255,.95)!important;backdrop-filter:blur(8px)!important}
        .maplibregl-ctrl-group button{width:34px!important;height:34px!important;border:none!important;border-bottom:1px solid rgba(0,0,0,.04)!important}
        .maplibregl-ctrl-group button:hover{background-color:rgba(0,0,0,.03)!important}
        .maplibregl-ctrl-group button:last-child{border-bottom:none!important}
        .maplibregl-ctrl-compass{border-radius:10px!important;overflow:hidden!important;box-shadow:0 2px 12px rgba(0,0,0,.1)!important;border:1px solid rgba(0,0,0,.06)!important;background:rgba(255,255,255,.95)!important;backdrop-filter:blur(8px)!important}
        .maplibregl-ctrl-scale{font-size:10px!important;color:#94a3b8!important;background:rgba(255,255,255,.85)!important;border:1px solid rgba(0,0,0,.04)!important;border-radius:20px!important;padding:2px 10px!important;box-shadow:0 1px 4px rgba(0,0,0,.06)!important;backdrop-filter:blur(8px)!important;font-weight:500!important}
        .maplibregl-ctrl-attrib{display:none!important}
        .dlh-attr{position:absolute;bottom:0;left:0;background:rgba(255,255,255,.92);backdrop-filter:blur(8px);border-radius:0 8px 0 0;padding:3px 10px;font-size:10px;color:#94a3b8;border:1px solid rgba(0,0,0,.04);border-right:none;border-bottom:none;font-weight:500;letter-spacing:.3px;z-index:1;pointer-events:none}
    </style>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="font-bold text-slate-800 dark:text-slate-200">{{ __('Daftar Armada') }}</h2>
                <span class="flex items-center gap-1.5 px-2.5 py-0.5 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 rounded-full text-xs font-bold"><span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>{{ $this->getActiveCount() }} {{ __('unit') }}</span>
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ __('Menampilkan seluruh armada beserta status mesin (menyala / parkir).') }}</p>
            @if($this->getLastSync())<p class="text-xs text-slate-400 dark:text-slate-500 mt-1"><svg xmlns="http://www.w3.org/2000/svg" style="display:inline;width:14px;height:14px;vertical-align:middle" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ __('Sinkronisasi terakhir:') }} {{ $this->getLastSync() }}</p>@endif
        </div>
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Cari nama armada...') }}" class="w-full md:w-64 px-4 py-2 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-sm"/>
    </div>
    <div class="w-full rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-2 shadow-sm relative">
        <div wire:ignore x-data
             x-init="setTimeout(function(){dlhMapInit('dlh-tracking-map');dlhMapDrawMarkers(@js($this->getVehicles()),true)},100)">
            <div id="dlh-tracking-map" style="height:550px;width:100%;z-index:1" class="rounded-t-2xl"></div>
            <div class="dlh-attr">Maps DLH - Palu Dev Custom</div>
        </div>
        <div class="p-4 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 rounded-b-2xl">
            <h3 class="font-bold text-xs text-slate-500 dark:text-slate-400 mb-4 uppercase tracking-wider text-center md:text-left">{{ __('Legenda Armada') }}</h3>
            <div class="flex flex-col md:flex-row items-center justify-center md:justify-start gap-8">
                <div class="flex items-center gap-3"><div class="w-12 h-12 flex items-center justify-center bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"><img src="/assets/tracking/car_acc_on.png" class="w-7 h-7 object-contain" alt="Pickup"></div><span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Mobil Pickup (Roda 4)') }}</span></div>
                <div class="flex items-center gap-3"><div class="w-12 h-12 flex items-center justify-center bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"><img src="/assets/tracking/truck_acc_on.png" class="w-7 h-7 object-contain" alt="Dump Truck"></div><span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Dump Truck (Roda 6)') }}</span></div>
                <div class="flex items-center gap-3"><div class="w-12 h-12 flex items-center justify-center bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"><img src="/assets/tracking/car_parking.png" class="w-7 h-7 object-contain" alt="Parkir"></div><span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Parkir / Mesin Mati') }}</span></div>
            </div>
        </div>
    </div>
</div>