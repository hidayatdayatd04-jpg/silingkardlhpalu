<?php

use App\Models\RegistrasiUsahaLb3;
use Livewire\Component;

new class extends Component
{
    public string $searchNomor = '';
    public string $searchNama = '';
    public ?RegistrasiUsahaLb3 $registrasi = null;

    public function searchByNomor()
    {
        $this->validate(['searchNomor' => 'required|string']);

        $this->registrasi = RegistrasiUsahaLb3::with('jenisLb3')
            ->where('nomor_registrasi', trim($this->searchNomor))
            ->first();

        if (! $this->registrasi) {
            $this->addError('searchNomor', __('Nomor registrasi tidak ditemukan.'));
        } else {
            $this->resetErrorBag('searchNomor');
        }
    }

    public function searchByNama()
    {
        $this->validate(['searchNama' => 'required|string|min:3']);

        $this->registrasi = RegistrasiUsahaLb3::with('jenisLb3')
            ->where('nama_perusahaan', 'like', '%'.trim($this->searchNama).'%')
            ->latest()
            ->first();

        if (! $this->registrasi) {
            $this->addError('searchNama', __('Registrasi dengan nama perusahaan tersebut tidak ditemukan.'));
        } else {
            $this->resetErrorBag('searchNama');
        }
    }
};
?>

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto">
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
            <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('Cek via Nomor Registrasi') }}</h3>
            <input wire:model="searchNomor" type="text" placeholder="LB3-XXXX-XXXX"
                class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm font-mono uppercase dark:border-slate-800" />
            @error('searchNomor') <span class="text-[0.8rem] font-medium text-red-500 block">{{ $message }}</span> @enderror
            <button wire:click="searchByNomor"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-slate-50 h-10 px-4 w-full dark:bg-slate-50 dark:text-slate-900">
                {{ __('Cari Registrasi') }}
            </button>
        </div>

        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
            <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('Cek via Nama Perusahaan') }}</h3>
            <input wire:model="searchNama" type="text" placeholder="{{ __('Nama perusahaan') }}"
                class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800" />
            @error('searchNama') <span class="text-[0.8rem] font-medium text-red-500 block">{{ $message }}</span> @enderror
            <button wire:click="searchByNama"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-slate-50 h-10 px-4 w-full dark:bg-slate-50 dark:text-slate-900">
                {{ __('Cari via Nama') }}
            </button>
        </div>
    </div>

    @if ($registrasi)
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm space-y-6 max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
                <div>
                    <span class="text-xs text-slate-500 font-medium tracking-wider uppercase">{{ __('Nomor Registrasi') }}</span>
                    <h2 class="text-2xl font-bold font-mono mt-1">{{ $registrasi->nomor_registrasi }}</h2>
                </div>
                @php
                    $statusColor = $registrasi->status?->color() ?? 'gray';
                    $badgeMap = [
                        'warning' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-400',
                        'success' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-400',
                        'danger' => 'bg-red-100 text-red-900 dark:bg-red-900/30 dark:text-red-400',
                        'gray' => 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100',
                    ];
                @endphp
                <span class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold {{ $badgeMap[$statusColor] ?? $badgeMap['gray'] }}">
                    {{ $registrasi->status?->label() ?? $registrasi->status }}
                </span>
            </div>

            <x-public.ticket-feedback :ticket="$registrasi" />

            <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($registrasi)" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Nama Perusahaan') }}</span>
                    <span class="font-semibold">{{ $registrasi->nama_perusahaan }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Jenis LB3') }}</span>
                    <span class="font-semibold">{{ $registrasi->jenisLb3?->nama ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Tanggal Registrasi') }}</span>
                    <span class="font-semibold">{{ $registrasi->created_at->format('d M Y H:i') }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Alamat') }}</span>
                    <span class="font-semibold">{{ $registrasi->alamat }}</span>
                </div>
            </div>

            @if ($registrasi->catatan)
                <div class="p-4 bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-800 rounded-lg">
                    <span class="block text-sm font-semibold text-brand-800 dark:text-brand-400">{{ __('Catatan Admin') }}</span>
                    <p class="text-sm mt-1">{{ $registrasi->catatan }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
