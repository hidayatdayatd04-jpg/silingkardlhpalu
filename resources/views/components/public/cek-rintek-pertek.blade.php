<?php

use Livewire\Component;

new class extends Component
{
    public string $searchNomor = '';
    public string $searchNama = '';
    public ?\App\Models\PengajuanRintekPertek $pengajuan = null;

    public function searchByNomor()
    {
        $this->validate(['searchNomor' => 'required|string']);

        $this->pengajuan = \App\Models\PengajuanRintekPertek::query()
            ->where('nomor_pengajuan', trim($this->searchNomor))
            ->first();

        if (! $this->pengajuan) {
            $this->addError('searchNomor', __('Nomor pengajuan tidak ditemukan.'));
        } else {
            $this->resetErrorBag('searchNomor');
        }
    }

    public function searchByNama()
    {
        $this->validate(['searchNama' => 'required|string|min:3']);

        $this->pengajuan = \App\Models\PengajuanRintekPertek::query()
            ->where('nama_perusahaan', 'like', '%'.trim($this->searchNama).'%')
            ->latest()
            ->first();

        if (! $this->pengajuan) {
            $this->addError('searchNama', __('Pengajuan dengan nama perusahaan tersebut tidak ditemukan.'));
        } else {
            $this->resetErrorBag('searchNama');
        }
    }
};
?>

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto">
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
            <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('Cek via Nomor Pengajuan') }}</h3>
            <input wire:model="searchNomor" type="text" placeholder="RPT-YYYY-XXXX"
                class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm font-mono uppercase dark:border-slate-800" />
            @error('searchNomor') <span class="text-[0.8rem] font-medium text-red-500 block">{{ $message }}</span> @enderror
            <button wire:click="searchByNomor"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-slate-50 h-10 px-4 w-full dark:bg-slate-50 dark:text-slate-900">
                {{ __('Cari Pengajuan') }}
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

    @if ($pengajuan)
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm space-y-6 max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
                <div>
                    <span class="text-xs text-slate-500 font-medium tracking-wider uppercase">{{ __('Nomor Pengajuan') }}</span>
                    <h2 class="text-2xl font-bold font-mono mt-1">{{ $pengajuan->nomor_pengajuan }}</h2>
                </div>
                @php
                    $statusColor = $pengajuan->status?->color() ?? 'gray';
                    $badgeMap = [
                        'warning' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-400',
                        'info' => 'bg-blue-100 text-blue-900 dark:bg-blue-900/30 dark:text-blue-400',
                        'success' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-400',
                        'danger' => 'bg-red-100 text-red-900 dark:bg-red-900/30 dark:text-red-400',
                        'gray' => 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100',
                    ];
                @endphp
                <span class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold {{ $badgeMap[$statusColor] ?? $badgeMap['gray'] }}">
                    {{ $pengajuan->status?->label() ?? $pengajuan->status }}
                </span>
            </div>

            <x-public.ticket-feedback :ticket="$pengajuan" />

            <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($pengajuan)" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Nama Perusahaan') }}</span>
                    <span class="font-semibold">{{ $pengajuan->nama_perusahaan }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Jenis Pengajuan') }}</span>
                    <span class="font-semibold">{{ $pengajuan->jenis_pengajuan }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Nama Penanggung Jawab') }}</span>
                    <span class="font-semibold">{{ $pengajuan->nama_penanggung_jawab }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Jenis Usaha') }}</span>
                    <span class="font-semibold">{{ $pengajuan->jenis_usaha }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Tanggal Pengajuan') }}</span>
                    <span class="font-semibold">{{ $pengajuan->created_at->format('d M Y H:i') }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 font-medium">{{ __('Verifikasi Dokumen') }}</span>
                    <span class="font-semibold">{{ $pengajuan->documentVerificationSummary() }}</span>
                </div>
            </div>

            @if ($pengajuan->catatan_verifikasi)
                <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg">
                    <span class="block text-sm font-semibold text-emerald-800 dark:text-emerald-400">{{ __('Catatan Verifikasi') }}</span>
                    <p class="text-sm mt-1">{{ $pengajuan->catatan_verifikasi }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
