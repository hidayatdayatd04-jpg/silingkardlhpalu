<?php

use App\Enums\Bidang;
use App\Models\Laporan;
use Livewire\Component;

new class extends Component
{
    public string $searchTicket = '';
    public string $searchPhone = '';
    public array $results = [];

    public function search(): void
    {
        $this->validate([
            'searchTicket' => 'nullable|string',
            'searchPhone' => 'nullable|string',
        ]);

        if (blank($this->searchTicket) && blank($this->searchPhone)) {
            $this->addError('searchTicket', __('Masukkan nomor tiket atau nomor HP.'));

            return;
        }

        $query = Laporan::with('fotos')
            ->where('bidang', Bidang::SAMPAH_LB3->value);

        if (filled($this->searchTicket)) {
            $query->where('nomor_tiket', trim($this->searchTicket));
        }

        if (filled($this->searchPhone)) {
            $query->where('nomor_hp', 'like', '%'.trim($this->searchPhone).'%');
        }

        $this->results = $query->latest()->get()->all();

        if (empty($this->results)) {
            $this->addError('searchTicket', __('Pengaduan tidak ditemukan.'));
        }
    }
};
?>

<div class="space-y-6 max-w-4xl mx-auto">
    <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="text-sm font-medium">{{ __('Nomor Tiket') }}</label>
                <input wire:model="searchTicket" type="text" placeholder="SMP-XXXX-XXXX" class="flex h-10 w-full rounded-md border border-slate-200 px-3 py-2 text-sm font-mono dark:border-slate-800 dark:bg-slate-950" />
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium">{{ __('Nomor HP') }}</label>
                <input wire:model="searchPhone" type="tel" placeholder="08123456789" class="flex h-10 w-full rounded-md border border-slate-200 px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-950" />
            </div>
        </div>
        @error('searchTicket') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
        <button wire:click="search" class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-white h-10 px-6 dark:bg-slate-50 dark:text-slate-900">{{ __('Cari Pengaduan') }}</button>
    </div>

    @foreach ($results as $laporan)
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
            <div class="flex flex-wrap justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                <div>
                    <div class="text-xs text-slate-500 uppercase tracking-wider">{{ __('Nomor Tiket') }}</div>
                    <div class="text-xl font-bold font-mono">{{ $laporan->nomor_tiket }}</div>
                </div>
                <span class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold">{{ $laporan->status }}</span>
            </div>
            <x-public.ticket-feedback :ticket="$laporan" />

            <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($laporan)" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div><span class="text-slate-500">{{ __('Pelapor:') }}</span> {{ $laporan->nama_pelapor }}</div>
                <div><span class="text-slate-500">{{ __('Jenis:') }}</span> {{ $laporan->jenis_pengaduan }}</div>
                <div><span class="text-slate-500">{{ __('Tanggal:') }}</span> {{ $laporan->created_at->format('d M Y H:i') }}</div>
                <div><span class="text-slate-500">{{ __('Lokasi:') }}</span> {{ $laporan->alamat }}</div>
            </div>
            @if ($laporan->catatan_admin)
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900 text-sm">
                    <span class="font-medium">{{ __('Catatan Admin:') }}</span> {{ $laporan->catatan_admin }}
                </div>
            @endif
        </div>
    @endforeach
</div>
