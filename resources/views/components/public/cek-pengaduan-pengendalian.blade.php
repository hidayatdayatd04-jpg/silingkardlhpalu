<?php

use App\Enums\Bidang;
use App\Enums\PengaduanStatus;
use App\Models\Laporan;
use Livewire\Component;

new class extends Component
{
    public string $searchTicket = '';
    public string $searchPhone = '';
    public ?Laporan $laporan = null;
    public $laporanList = null;

    public function searchByTicket()
    {
        $this->validate(['searchTicket' => 'required|string']);

        $this->laporan = Laporan::with('fotos')
            ->where('bidang', Bidang::PENGENDALIAN->value)
            ->where('nomor_tiket', trim($this->searchTicket))
            ->first();

        $this->laporanList = null;

        if (! $this->laporan) {
            $this->addError('searchTicket', __('Nomor tiket tidak ditemukan.'));
        } else {
            $this->resetErrorBag('searchTicket');
        }
    }

    public function searchByPhone()
    {
        $this->validate(['searchPhone' => 'required|string']);

        $this->laporanList = Laporan::with('fotos')
            ->where('bidang', Bidang::PENGENDALIAN->value)
            ->where('nomor_hp', trim($this->searchPhone))
            ->latest()
            ->get();

        $this->laporan = null;

        if ($this->laporanList->isEmpty()) {
            $this->addError('searchPhone', __('Pengaduan dengan nomor HP tersebut tidak ditemukan.'));
        } else {
            $this->resetErrorBag('searchPhone');
        }
    }
};
?>

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto">
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
            <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('Cek via Nomor Tiket') }}</h3>
            <input wire:model="searchTicket" type="text" placeholder="PDL-XXXX-XXXX"
                class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm font-mono uppercase dark:border-slate-800" />
            @error('searchTicket') <span class="text-[0.8rem] font-medium text-red-500 block">{{ $message }}</span> @enderror
            <button wire:click="searchByTicket"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-slate-50 h-10 px-4 w-full dark:bg-slate-50 dark:text-slate-900">
                {{ __('Cari Tiket') }}
            </button>
        </div>

        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
            <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('Cek via Nomor HP') }}</h3>
            <input wire:model="searchPhone" type="tel" placeholder="08123456789"
                class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800" />
            @error('searchPhone') <span class="text-[0.8rem] font-medium text-red-500 block">{{ $message }}</span> @enderror
            <button wire:click="searchByPhone"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-slate-50 h-10 px-4 w-full dark:bg-slate-50 dark:text-slate-900">
                {{ __('Cari via HP') }}
            </button>
        </div>
    </div>

    @if ($laporan)
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm space-y-8 max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
                <div>
                    <span class="text-xs text-slate-500 font-medium tracking-wider uppercase">{{ __('Nomor Tiket') }}</span>
                    <h2 class="text-2xl font-bold font-mono mt-1">{{ $laporan->nomor_tiket }}</h2>
                </div>
                @php
                    $statusColor = $laporan->status_color ?? 'gray';
                    $badgeMap = [
                        'gray' => 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100',
                        'amber' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-400',
                        'success' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-400',
                    ];
                @endphp
                <span class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold {{ $badgeMap[$statusColor] ?? $badgeMap['gray'] }}">
                    {{ $laporan->status_label }}
                </span>
            </div>

            <x-public.ticket-feedback :ticket="$laporan" />

            <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($laporan)" />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Nama Pelapor') }}</span>
                            <span class="font-semibold">{{ $laporan->nama_pelapor }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Jenis Pengaduan') }}</span>
                            <span class="font-semibold">{{ $laporan->jenis_pengaduan }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Tanggal Lapor') }}</span>
                            <span class="font-semibold">{{ $laporan->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Alamat') }}</span>
                            <span class="font-semibold">{{ $laporan->alamat }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="block text-sm text-slate-500 font-medium">{{ __('Deskripsi') }}</span>
                        <p class="text-sm text-slate-700 dark:text-slate-300 mt-1">{{ $laporan->deskripsi }}</p>
                    </div>
                    @if ($laporan->catatan_admin)
                        <div class="p-4 bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-800 rounded-lg">
                            <span class="block text-sm font-semibold text-brand-800 dark:text-brand-400">{{ __('Catatan Admin') }}</span>
                            <p class="text-sm mt-1">{{ $laporan->catatan_admin }}</p>
                        </div>
                    @endif
                    @if ($laporan->status === 'Selesai' && $laporan->bukti_foto_selesai)
                        <div class="space-y-2">
                            <span class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Bukti Foto Selesai') }}</span>
                            <div class="rounded-md overflow-hidden border border-slate-200 dark:border-slate-800 aspect-video relative">
                                <img src="/storage/{{ $laporan->bukti_foto_selesai }}" alt="{{ __('Foto bukti penyelesaian laporan oleh petugas') }}" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    @endif
                    @if ($laporan->fotos->isNotEmpty())
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($laporan->fotos as $foto)
                                <div class="aspect-square rounded-md overflow-hidden border border-slate-200 dark:border-slate-800">
                                    <img src="/storage/{{ $foto->path_foto }}" alt="{{ __('Foto bukti pengaduan') }}" class="w-full h-full object-cover" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">{{ __('Lokasi Peta') }}</h3>
                    <div wire:ignore wire:key="map-{{ $laporan->nomor_tiket }}"
                         x-data x-init="setTimeout(function(){dlhSimpleMap('cek-map-{{ $laporan->nomor_tiket }}',{lat:@js($laporan->latitude),lng:@js($laporan->longitude),zoom:14,popupText:'{{ __('Lokasi Pengaduan') }}'})},100)">
                        <div id="cek-map-{{ $laporan->nomor_tiket }}" class="w-full h-[300px] border border-slate-200 dark:border-slate-800 rounded-md z-0"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($laporanList && $laporanList->isNotEmpty())
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-800 rounded-lg p-4">
                <p class="text-sm font-semibold text-brand-800 dark:text-brand-400">
                    {{ __('Ditemukan :count pengaduan dengan nomor HP tersebut', ['count' => $laporanList->count()]) }}
                </p>
            </div>

            @foreach ($laporanList as $item)
                <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <span class="text-xs text-slate-500 font-medium tracking-wider uppercase">{{ __('Nomor Tiket') }}</span>
                            <h3 class="text-xl font-bold font-mono mt-1">{{ $item->nomor_tiket }}</h3>
                        </div>
                        @php
                            $statusColor = $item->status_color ?? 'gray';
                            $badgeMap = [
                                'gray' => 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100',
                                'amber' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-400',
                                'success' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-400',
                            ];
                        @endphp
                        <span class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold {{ $badgeMap[$statusColor] ?? $badgeMap['gray'] }}">
                            {{ $item->status_label }}
                        </span>
                    </div>

                    <x-public.ticket-feedback :ticket="$item" />

                    <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($item)" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Nama Pelapor') }}</span>
                            <span class="font-semibold">{{ $item->nama_pelapor }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Jenis Pengaduan') }}</span>
                            <span class="font-semibold">{{ $item->jenis_pengaduan }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Tanggal Lapor') }}</span>
                            <span class="font-semibold">{{ $item->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Alamat') }}</span>
                            <span class="font-semibold">{{ $item->alamat }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="block text-sm text-slate-500 font-medium mb-1">{{ __('Deskripsi') }}</span>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $item->deskripsi }}</p>
                    </div>

                    @if ($item->catatan_admin)
                        <div class="p-4 bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-800 rounded-lg">
                            <span class="block text-sm font-semibold text-brand-800 dark:text-brand-400">{{ __('Catatan Admin') }}</span>
                            <p class="text-sm mt-1">{{ $item->catatan_admin }}</p>
                        </div>
                    @endif

                    @if ($item->fotos->isNotEmpty())
                        <div>
                            <span class="block text-sm text-slate-500 font-medium mb-2">{{ __('Foto Bukti') }}</span>
                            <div class="grid grid-cols-3 md:grid-cols-4 gap-2">
                                @foreach ($item->fotos as $foto)
                                    <div class="aspect-square rounded-md overflow-hidden border border-slate-200 dark:border-slate-800">
                                        <img src="/storage/{{ $foto->path_foto }}" alt="{{ __('Foto bukti pengaduan') }}" class="w-full h-full object-cover" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
