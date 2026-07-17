<?php

use App\Enums\Bidang;
use App\Models\Laporan;
use Livewire\Component;

new class extends Component {
    public string $search = '';
    public array $results = [];

    public function searchRecords(): void
    {
        $this->validate(['search' => 'required|string|min:3']);
        $term = trim($this->search);

        $this->results = Laporan::with('fotos')
            ->where('bidang', Bidang::RTH->value)
            ->where(function ($q) use ($term) {
                $q->where('nomor_tiket', $term)
                    ->orWhere('nomor_hp', 'like', "%{$term}%");
            })
            ->latest()
            ->get()
            ->all();

        if (empty($this->results)) {
            $this->addError('search', __('Data tidak ditemukan.'));
        }
    }
};
?>

<div class="space-y-6 max-w-4xl mx-auto">
    <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <label class="text-sm font-medium">{{ __('Nomor Tiket atau Nomor HP') }}</label>
            <input wire:model="search" type="text" class="mt-1 w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-900 px-3 py-2 text-sm font-mono">
            @error('search') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <button wire:click="searchRecords" class="self-end bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 px-6 py-2 rounded-lg text-sm font-semibold">{{ __('Cari') }}</button>
    </div>

    @foreach ($results as $item)
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 space-y-3">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs text-slate-500 uppercase">{{ __('Nomor Tiket') }}</p>
                    <p class="text-xl font-mono font-bold">{{ $item->nomor_tiket }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-brand-100 text-brand-800">{{ $item->status }}</span>
            </div>
            <x-public.ticket-feedback :ticket="$item" />

            <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($item)" />

            <p><strong>{{ __('Jenis:') }}</strong> {{ $item->jenis_pengaduan ?? $item->kategori }}</p>
            <p><strong>{{ __('Tanggal:') }}</strong> {{ $item->created_at->format('d M Y H:i') }}</p>
            @if ($item->catatan_admin)
                <p><strong>{{ __('Catatan Admin:') }}</strong> {{ $item->catatan_admin }}</p>
            @endif
        </div>
    @endforeach
</div>
