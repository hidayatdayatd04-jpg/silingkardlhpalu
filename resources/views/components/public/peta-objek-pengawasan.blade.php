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

<div class="space-y-6 po-wrap">
    <div class="po-card">
        <header class="po-card-head">
            <span class="po-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 20l-5.45-2.73A1 1 0 0 1 3 16.38V5.35a1 1 0 0 1 1.55-.83L9 7m0 13 6-3m-6 3V7m6 10 4.45 2.73A1 1 0 0 0 21 18.38V7.35a1 1 0 0 0-1.55-.83L15 7m0 10V7"/></svg>
            </span>
            <div class="flex-1">
                <h2 class="po-card-title">{{ __('Peta Objek Pengawasan') }}</h2>
                <p class="po-card-desc">
                    {{ __('Klik marker pada peta untuk melihat nama perusahaan dan ringkasan status dokumen lingkungan (AMDAL, UKL-UPL, SPPL).') }}
                </p>
            </div>
            <span class="po-count-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 8.5c0 5.5-8.4 11.5-8.4 11.5S3.6 14 3.6 8.5a8.4 8.4 0 1 1 16.8 0Z"/><circle cx="12" cy="8.5" r="2.6"/></svg>
                {{ count($this->getMapData()) }} {{ __('objek') }}
            </span>
        </header>

        <div wire:ignore
             class="po-map-container"
             x-data x-init="setTimeout(function(){window.ensureMaplibreLoaded(function(){dlhPetaObjekPengawasan('peta-objek-map',@js($this->getMapData()))})},100)">
            <div id="peta-objek-map" style="width:100%;height:100%"></div>
        </div>
    </div>

    @if (count($this->getMapData()) === 0)
        <div class="po-empty">
            <span class="po-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 20l-5.45-2.73A1 1 0 0 1 3 16.38V5.35a1 1 0 0 1 1.55-.83L9 7m0 13 6-3m-6 3V7m6 10 4.45 2.73A1 1 0 0 0 21 18.38V7.35a1 1 0 0 0-1.55-.83L15 7m0 10V7"/></svg>
            </span>
            <h3 class="po-empty-title">{{ __('Belum ada data') }}</h3>
            <p class="po-empty-desc">{{ __('Belum ada objek pengawasan dengan koordinat peta.') }}</p>
        </div>
    @endif

    <style>

        .po-wrap { font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif; }

        .po-card {
            background: #fff;
            border: 1px solid #e8efe9;
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(13,43,29,0.04), 0 12px 32px -12px rgba(13,43,29,0.1);
        }

        .po-card-head {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 18px;
        }

        .po-card-icon {
            flex-shrink: 0;
            width: 44px; height: 44px; border-radius: 13px;
            background: linear-gradient(135deg, #178a53, #146a44);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 6px 14px -2px rgba(20, 106, 68, 0.35);
        }
        .po-card-icon svg { width: 20px; height: 20px; }

        .po-card-title {
            font-size: 18px; font-weight: 700; color: #12201a; letter-spacing: -0.01em;
        }
        .po-card-desc {
            font-size: 13px; color: #5b6b63; margin-top: 4px; line-height: 1.55;
        }

        .po-count-badge {
            flex-shrink: 0;
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 12px; border-radius: 9999px;
            background: #e6f5ec; color: #146a44;
            font-size: 12.5px; font-weight: 700;
            border: 1px solid #d1e7da;
        }
        .po-count-badge svg { width: 14px; height: 14px; }

        .po-map-container {
            width: 100%;
            height: 540px;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #e8efe9;
            box-shadow: inset 0 0 0 1px rgba(13,43,29,0.02);
            position: relative;
        }

        /* ── Empty State ── */
        .po-empty {
            background: #fff;
            border: 1px solid #e8efe9;
            border-radius: 24px;
            padding: 48px 24px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(13,43,29,0.04), 0 12px 32px -12px rgba(13,43,29,0.1);
        }
        .po-empty-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 56px; height: 56px; border-radius: 16px;
            background: #f4faf6; color: #5b6b63;
            margin-bottom: 16px;
        }
        .po-empty-icon svg { width: 28px; height: 28px; }
        .po-empty-title { font-size: 16px; font-weight: 700; color: #12201a; margin-bottom: 4px; }
        .po-empty-desc { font-size: 13.5px; color: #5b6b63; }

        /* ── Dark mode ── */
        .dark .po-card { background: #1e293b; border-color: #334155; }
        .dark .po-card-title { color: #e2e8f0; }
        .dark .po-card-desc { color: #94a3b8; }
        .dark .po-count-badge { background: rgba(30,165,103,0.15); color: #6ee7b7; border-color: rgba(30,165,103,0.3); }
        .dark .po-map-container { border-color: #334155; }
        .dark .po-empty { background: #1e293b; border-color: #334155; }
        .dark .po-empty-icon { background: rgba(30,165,103,0.1); color: #94a3b8; }
        .dark .po-empty-title { color: #e2e8f0; }
        .dark .po-empty-desc { color: #94a3b8; }
    </style>
</div>
