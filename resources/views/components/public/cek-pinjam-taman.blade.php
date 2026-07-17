<?php

use App\Models\PermohonanPinjamTaman;
use Livewire\Component;

new class extends Component
{
    public string $searchTicket = '';
    public string $searchNama = '';
    public ?PermohonanPinjamTaman $permohonan = null;

    public function searchByTicket()
    {
        $this->validate(['searchTicket' => 'required|string']);

        $this->permohonan = PermohonanPinjamTaman::with('tamanKota')
            ->where('nomor_tiket', trim($this->searchTicket))
            ->first();

        if (! $this->permohonan) {
            $this->addError('searchTicket', __('Nomor tiket tidak ditemukan.'));
        } else {
            $this->resetErrorBag('searchTicket');
        }
    }

    public function searchByNama()
    {
        $this->validate(['searchNama' => 'required|string|min:3']);

        $this->permohonan = PermohonanPinjamTaman::with('tamanKota')
            ->where('nama_pemohon', 'like', '%'.trim($this->searchNama).'%')
            ->latest()
            ->first();

        if (! $this->permohonan) {
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
            <input wire:model="searchNama" type="text" placeholder="{{ __('Nama pemohon/komunitas') }}"
                class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800" />
            @error('searchNama') <span class="text-[0.8rem] font-medium text-red-500 block">{{ $message }}</span> @enderror
            <button wire:click="searchByNama"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-slate-50 h-10 px-4 w-full dark:bg-slate-50 dark:text-slate-900">
                {{ __('Cari via Nama') }}
            </button>
        </div>
    </div>

    @if ($permohonan)
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm space-y-6 max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
                <div>
                    <span class="text-xs text-slate-500 font-medium tracking-wider uppercase">{{ __('Nomor Tiket') }}</span>
                    <h2 class="text-2xl font-bold font-mono mt-1">{{ $permohonan->nomor_tiket }}</h2>
                </div>
                @php
                    $statusColor = $permohonan->status?->color() ?? 'gray';
                    $badgeMap = [
                        'gray' => 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100',
                        'success' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-400',
                    ];
                @endphp
                <span class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold {{ $badgeMap[$statusColor] ?? $badgeMap['gray'] }}">
                    {{ $permohonan->status?->label() ?? $permohonan->status }}
                </span>
            </div>

            <x-public.ticket-feedback :ticket="$permohonan" />

            <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($permohonan)" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Nama Pemohon') }}</span>
                    <span class="font-semibold">{{ $permohonan->nama_pemohon }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Nama Kegiatan') }}</span>
                    <span class="font-semibold">{{ $permohonan->nama_kegiatan }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Taman') }}</span>
                    <span class="font-semibold">{{ $permohonan->tamanKota?->nama ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Tanggal Pengajuan') }}</span>
                    <span class="font-semibold">{{ $permohonan->created_at->format('d M Y H:i') }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Tanggal Kegiatan') }}</span>
                    <span class="font-semibold">{{ $permohonan->tanggal_kegiatan->format('d M Y H:i') }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Tanggal Selesai') }}</span>
                    <span class="font-semibold">{{ $permohonan->tanggal_selesai?->format('d M Y H:i') ?? '-' }}</span>
                </div>
            </div>

            @if ($permohonan->catatan_admin)
                <div class="p-4 bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-800 rounded-lg">
                    <span class="block text-sm font-semibold text-brand-800 dark:text-brand-400">{{ __('Catatan Admin') }}</span>
                    <p class="text-sm mt-1">{{ $permohonan->catatan_admin }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
