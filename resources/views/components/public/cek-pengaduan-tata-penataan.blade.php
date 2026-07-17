<?php

use App\Enums\StatusPengaduanTataPenataan;
use App\Models\PengaduanTataPenataan;
use Livewire\Component;

new class extends Component
{
    public string $searchTicket = '';
    public string $searchPhone = '';
    public ?PengaduanTataPenataan $pengaduan = null;

    public function searchByTicket()
    {
        $this->validate(['searchTicket' => 'required|string']);

        $this->pengaduan = PengaduanTataPenataan::with('fotos')
            ->where('nomor_tiket', trim($this->searchTicket))
            ->first();

        if (! $this->pengaduan) {
            $this->addError('searchTicket', __('Nomor tiket tidak ditemukan.'));
        } else {
            $this->resetErrorBag('searchTicket');
        }
    }

    public function searchByPhone()
    {
        $this->validate(['searchPhone' => 'required|string']);

        $this->pengaduan = PengaduanTataPenataan::with('fotos')
            ->where('no_hp', trim($this->searchPhone))
            ->latest()
            ->first();

        if (! $this->pengaduan) {
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
            <input wire:model="searchTicket" type="text" placeholder="TTP-XXXX-XXXX"
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

    @if ($pengaduan)
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm space-y-8 max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
                <div>
                    <span class="text-xs text-slate-500 font-medium tracking-wider uppercase">{{ __('Nomor Tiket') }}</span>
                    <h2 class="text-2xl font-bold font-mono mt-1">{{ $pengaduan->nomor_tiket }}</h2>
                </div>
                @php
                    $statusColor = $pengaduan->status?->color() ?? 'gray';
                    $badgeMap = [
                        'gray' => 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100',
                        'warning' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-400',
                        'success' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-400',
                    ];
                @endphp
                <span class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold {{ $badgeMap[$statusColor] ?? $badgeMap['gray'] }}">
                    {{ $pengaduan->status?->label() ?? $pengaduan->status }}
                </span>
            </div>

            <x-public.ticket-feedback :ticket="$pengaduan" />

            <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($pengaduan)" />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Nama Pelapor') }}</span>
                            <span class="font-semibold">{{ $pengaduan->nama_pelapor }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Jenis Pengaduan') }}</span>
                            <span class="font-semibold">{{ $pengaduan->jenis_pengaduan?->label() ?? $pengaduan->jenis_pengaduan }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Tanggal Lapor') }}</span>
                            <span class="font-semibold">{{ $pengaduan->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Alamat') }}</span>
                            <span class="font-semibold">{{ $pengaduan->alamat }}</span>
                        </div>
                        @if ($pengaduan->nama_terlapor)
                            <div>
                                <span class="block text-slate-500 font-medium">{{ __('Nama Terlapor') }}</span>
                                <span class="font-semibold">{{ $pengaduan->nama_terlapor }}</span>
                            </div>
                        @endif
                        @if ($pengaduan->nama_perusahaan_terlapor)
                            <div>
                                <span class="block text-slate-500 font-medium">{{ __('Perusahaan Terlapor') }}</span>
                                <span class="font-semibold">{{ $pengaduan->nama_perusahaan_terlapor }}</span>
                            </div>
                        @endif
                    </div>
                    <div>
                        <span class="block text-sm text-slate-500 font-medium">{{ __('Deskripsi') }}</span>
                        <p class="text-sm text-slate-700 dark:text-slate-300 mt-1">{{ $pengaduan->deskripsi }}</p>
                    </div>
                    @if ($pengaduan->catatan_admin)
                        <div class="p-4 bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-800 rounded-lg">
                            <span class="block text-sm font-semibold text-brand-800 dark:text-brand-400">{{ __('Catatan Admin') }}</span>
                            <p class="text-sm mt-1">{{ $pengaduan->catatan_admin }}</p>
                        </div>
                    @endif
                    @if ($pengaduan->fotos->isNotEmpty())
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($pengaduan->fotos as $foto)
                                <div class="aspect-square rounded-md overflow-hidden border border-slate-200 dark:border-slate-800">
                                    <img src="/storage/{{ $foto->path_foto }}" alt="{{ __('Foto bukti pengaduan') }}" class="w-full h-full object-cover" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">{{ __('Lokasi Peta') }}</h3>
                    <div wire:ignore wire:key="map-{{ $pengaduan->nomor_tiket }}"
                         x-data x-init="setTimeout(function(){dlhSimpleMap('cek-map-{{ $pengaduan->nomor_tiket }}',{lat:@js($pengaduan->latitude),lng:@js($pengaduan->longitude),zoom:14,popupText:'{{ __('Lokasi Pengaduan') }}'})},100)">
                        <div id="cek-map-{{ $pengaduan->nomor_tiket }}" class="w-full h-[300px] border border-slate-200 dark:border-slate-800 rounded-md z-0"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
