<?php

use App\Models\PermohonanRekomendasi;
use Livewire\Component;

new class extends Component
{
    public string $searchEmail = '';
    public string $searchPhone = '';
    public array $permohonans = [];

    public function searchByEmail()
    {
        $this->validate(['searchEmail' => 'required|email']);

        $this->permohonans = PermohonanRekomendasi::query()
            ->where('email', trim($this->searchEmail))
            ->latest()
            ->get()
            ->all();

        if (empty($this->permohonans)) {
            $this->addError('searchEmail', __('Tidak ada permohonan dengan email tersebut.'));
        } else {
            $this->resetErrorBag('searchEmail');
        }
    }

    public function searchByPhone()
    {
        $this->validate(['searchPhone' => 'required|string']);

        $this->permohonans = PermohonanRekomendasi::query()
            ->where('nomor_telepon', trim($this->searchPhone))
            ->latest()
            ->get()
            ->all();

        if (empty($this->permohonans)) {
            $this->addError('searchPhone', __('Tidak ada permohonan dengan nomor telepon tersebut.'));
        } else {
            $this->resetErrorBag('searchPhone');
        }
    }
};
?>

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto">
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
            <h3 class="font-semibold">{{ __('Cek via Email') }}</h3>
            <input wire:model="searchEmail" type="email" placeholder="email@perusahaan.com"
                class="flex h-10 w-full rounded-md border border-slate-200 px-3 text-sm dark:border-slate-800" />
            @error('searchEmail') <span class="text-[0.8rem] text-red-500 block">{{ $message }}</span> @enderror
            <button wire:click="searchByEmail" class="w-full h-10 rounded-md bg-slate-900 text-white text-sm font-medium dark:bg-slate-50 dark:text-slate-900">{{ __('Cari Riwayat') }}</button>
        </div>
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
            <h3 class="font-semibold">{{ __('Cek via Nomor Telepon') }}</h3>
            <input wire:model="searchPhone" type="tel" placeholder="08123456789"
                class="flex h-10 w-full rounded-md border border-slate-200 px-3 text-sm dark:border-slate-800" />
            @error('searchPhone') <span class="text-[0.8rem] text-red-500 block">{{ $message }}</span> @enderror
            <button wire:click="searchByPhone" class="w-full h-10 rounded-md bg-slate-900 text-white text-sm font-medium dark:bg-slate-50 dark:text-slate-900">{{ __('Cari Riwayat') }}</button>
        </div>
    </div>

    @if (! empty($permohonans))
        <div class="max-w-4xl mx-auto space-y-4">
            <h3 class="text-lg font-semibold">{{ __('Riwayat Permohonan') }} ({{ count($permohonans) }})</h3>
            @foreach ($permohonans as $permohonan)
                <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <span class="text-xs text-slate-500 uppercase tracking-wider">{{ __('Nomor Tiket') }}</span>
                            <p class="font-mono font-bold text-lg">{{ $permohonan->nomor_tiket }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $permohonan->nama_perusahaan }} - {{ $permohonan->jenis_usaha }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold
                            {{ $permohonan->status === 'Ditindaklanjuti' ? 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-900 dark:bg-slate-800' }}">
                            {{ $permohonan->status }}
                        </span>
                    </div>
                    <x-public.ticket-feedback :ticket="$permohonan" />

                    <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($permohonan)" />

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 text-sm">
                        <div>
                            <span class="block text-slate-500">{{ __('Jenis Pengajuan') }}</span>
                            <span class="font-medium">{{ $permohonan->jenis_pengajuan }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500">{{ __('Tanggal Pengajuan') }}</span>
                            <span class="font-medium">{{ $permohonan->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="block text-slate-500">{{ __('Catatan Verifikasi') }}</span>
                            <span class="font-medium">{{ $permohonan->catatan_verifikasi ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ url('/permohonan-rekomendasi/'.$permohonan->nomor_tiket.'/bukti-pdf') }}"
                            class="text-sm text-brand-600 hover:underline font-medium">{{ __('Unduh Bukti PDF') }}</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
