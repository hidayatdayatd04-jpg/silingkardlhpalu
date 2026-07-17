<?php

use App\Models\PerizinanTebangPohon;
use Livewire\Component;

new class extends Component
{
    public string $searchTicket = '';
    public string $searchNama = '';
    public ?PerizinanTebangPohon $perizinan = null;

    public function searchByTicket()
    {
        $this->validate(['searchTicket' => 'required|string']);

        $this->perizinan = PerizinanTebangPohon::query()
            ->where('nomor_tiket', trim($this->searchTicket))
            ->first();

        if (! $this->perizinan) {
            $this->addError('searchTicket', __('Nomor tiket tidak ditemukan.'));
        } else {
            $this->resetErrorBag('searchTicket');
        }
    }

    public function searchByNama()
    {
        $this->validate(['searchNama' => 'required|string|min:3']);

        $this->perizinan = PerizinanTebangPohon::query()
            ->where('nama_pemohon', 'like', '%'.trim($this->searchNama).'%')
            ->latest()
            ->first();

        if (! $this->perizinan) {
            $this->addError('searchNama', __('Permohonan dengan nama pemohon tersebut tidak ditemukan.'));
        } else {
            $this->resetErrorBag('searchNama');
        }
    }
};
?>

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto">
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
            <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('Cek via Nomor Tiket') }}</h3>
            <input wire:model="searchTicket" type="text" placeholder="{{ __('Nomor tiket') }}"
                class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm font-mono uppercase dark:border-slate-800" />
            @error('searchTicket') <span class="text-[0.8rem] font-medium text-red-500 block">{{ $message }}</span> @enderror
            <button wire:click="searchByTicket"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-slate-50 h-10 px-4 w-full dark:bg-slate-50 dark:text-slate-900">
                {{ __('Cari Tiket') }}
            </button>
        </div>

        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
            <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('Cek via Nama Pemohon') }}</h3>
            <input wire:model="searchNama" type="text" placeholder="{{ __('Nama pemohon') }}"
                class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800" />
            @error('searchNama') <span class="text-[0.8rem] font-medium text-red-500 block">{{ $message }}</span> @enderror
            <button wire:click="searchByNama"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-slate-50 h-10 px-4 w-full dark:bg-slate-50 dark:text-slate-900">
                {{ __('Cari via Nama') }}
            </button>
        </div>
    </div>

    @if ($perizinan)
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm space-y-8 max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
                <div>
                    <span class="text-xs text-slate-500 font-medium tracking-wider uppercase">{{ __('Nomor Tiket') }}</span>
                    <h2 class="text-2xl font-bold font-mono mt-1">{{ $perizinan->nomor_tiket }}</h2>
                </div>
                @php
                    $statusColor = $perizinan->status?->color() ?? 'gray';
                    $badgeMap = [
                        'gray' => 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100',
                        'success' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-400',
                    ];
                @endphp
                <span class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold {{ $badgeMap[$statusColor] ?? $badgeMap['gray'] }}">
                    {{ $perizinan->status?->label() ?? $perizinan->status }}
                </span>
            </div>

            <x-public.ticket-feedback :ticket="$perizinan" />

            <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($perizinan)" />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Nama Pemohon') }}</span>
                            <span class="font-semibold">{{ $perizinan->nama_pemohon }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Tanggal Pengajuan') }}</span>
                            <span class="font-semibold">{{ $perizinan->created_at->format('d M Y H:i') }}</span>
                        </div>
                        @if ($perizinan->keputusan)
                            <div>
                                <span class="block text-slate-500 font-medium">{{ __('Keputusan') }}</span>
                                <span class="font-semibold">{{ $perizinan->keputusan?->label() ?? $perizinan->keputusan }}</span>
                            </div>
                        @endif
                    </div>
                    <div>
                        <span class="block text-sm text-slate-500 font-medium">{{ __('Alasan Penebangan') }}</span>
                        <p class="text-sm text-slate-700 dark:text-slate-300 mt-1">{{ $perizinan->alasan_penebangan }}</p>
                    </div>
                    <div>
                        <span class="block text-sm text-slate-500 font-medium">{{ __('Rencana Ganti Tanam') }}</span>
                        <p class="text-sm text-slate-700 dark:text-slate-300 mt-1">{{ $perizinan->rencana_ganti_tanam }}</p>
                    </div>
                    @if ($perizinan->catatan_survei)
                        <div class="p-4 bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-800 rounded-lg">
                            <span class="block text-sm font-semibold text-brand-800 dark:text-brand-400">{{ __('Catatan Survei') }}</span>
                            <p class="text-sm mt-1">{{ $perizinan->catatan_survei }}</p>
                        </div>
                    @endif
                    @if ($perizinan->foto_pohon)
                        <div class="aspect-video rounded-md overflow-hidden border border-slate-200 dark:border-slate-800">
                            <img src="/storage/{{ $perizinan->foto_pohon }}" alt="{{ __('Foto pohon') }}" class="w-full h-full object-cover" />
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">{{ __('Lokasi Peta') }}</h3>
                    <div wire:ignore wire:key="map-{{ $perizinan->nomor_tiket }}"
                         x-data x-init="setTimeout(function(){dlhSimpleMap('cek-map-{{ $perizinan->nomor_tiket }}',{lat:@js($perizinan->latitude),lng:@js($perizinan->longitude),zoom:14,popupText:'{{ __('Lokasi Pohon') }}'})},100)">
                        <div id="cek-map-{{ $perizinan->nomor_tiket }}" class="w-full h-[300px] border border-slate-200 dark:border-slate-800 rounded-md z-0"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
