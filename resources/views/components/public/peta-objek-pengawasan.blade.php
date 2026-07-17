<?php

use App\Models\ObjekPengawasan;
use Livewire\Component;

new class extends Component
{
    public function getMapData(): array
    {
        return ObjekPengawasan::with('dokumens')
            ->get()
            ->map(function (ObjekPengawasan $objek) {
                $dokumenSummary = $objek->dokumens
                    ->map(fn ($d) => $d->jenis_dokumen?->label().': '.$d->status_dokumen?->label())
                    ->implode('<br>');

                return [
                    'id' => $objek->id,
                    'nama_perusahaan' => $objek->nama_perusahaan,
                    'nama_penanggung_jawab' => $objek->nama_penanggung_jawab,
                    'alamat' => $objek->alamat,
                    'latitude' => $objek->latitude,
                    'longitude' => $objek->longitude,
                    'dokumen_summary' => $dokumenSummary ?: __('Belum ada data dokumen'),
                ];
            })
            ->filter(fn ($o) => $o['latitude'] && $o['longitude'])
            ->values()
            ->toArray();
    }
};
?>

<div class="space-y-6">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 shadow-sm">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
            {{ __('Klik marker pada peta untuk melihat nama perusahaan dan ringkasan status dokumen lingkungan (AMDAL, UKL-UPL, SPPL.') }}
        </p>
        <div wire:ignore
             class="w-full h-[500px] rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800"
             x-data x-init="setTimeout(function(){dlhPetaObjekPengawasan('peta-objek-map',@js($this->getMapData()))},100)">
            <div id="peta-objek-map" style="width:100%;height:100%"></div>
        </div>
    </div>

    @if (count($this->getMapData()) === 0)
        <p class="text-sm text-slate-500 text-center">{{ __('Belum ada objek pengawasan dengan koordinat peta.') }}</p>
    @endif
</div>
