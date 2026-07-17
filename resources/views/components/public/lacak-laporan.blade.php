<?php

use Livewire\Component;
use App\Models\Laporan;
use App\Models\PengaduanTataPenataan;

new class extends Component
{
    public string $searchTicket = '';
    public ?Laporan $laporan = null;
    public ?PengaduanTataPenataan $pengaduanTataPenataan = null;

    public function search()
    {
        $this->validate([
            'searchTicket' => 'required|string',
        ]);

        $ticket = trim($this->searchTicket);

        // Tata penataan tickets use TTP prefix
        if (str_starts_with(strtoupper($ticket), 'TTP')) {
            $this->pengaduanTataPenataan = PengaduanTataPenataan::with('fotos')
                ->where('nomor_tiket', $ticket)
                ->first();

            if ($this->pengaduanTataPenataan) {
                $this->laporan = null;
                return;
            }
        }

        // Default: search in Laporan
        $this->laporan = Laporan::with('fotos')
            ->where('nomor_tiket', $ticket)
            ->first();

        $this->pengaduanTataPenataan = null;

        if (!$this->laporan && !$this->pengaduanTataPenataan) {
            $this->addError('searchTicket', __('Nomor tiket tidak ditemukan.'));
        }
    }
};
?>

<div class="space-y-6">
    <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm flex flex-col md:flex-row items-end gap-4 max-w-4xl mx-auto">
        <div class="flex-1 w-full space-y-2">
            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:text-slate-300">{{ __('Nomor Tiket Laporan') }}</label>
            <input wire:model="searchTicket" type="text" placeholder="{{ __('Contoh: TK-XXXXXX atau TTP-XXXX-XXXX') }}" class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-800 dark:ring-offset-slate-950 dark:focus-visible:ring-brand-500 font-mono tracking-widest uppercase" />
            @error('searchTicket') <span class="text-[0.8rem] font-medium text-red-500 block">{{ $message }}</span> @enderror
        </div>
        <button wire:click="search" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none ring-offset-white bg-slate-900 text-slate-50 hover:bg-slate-900/90 h-10 py-2 px-6 w-full md:w-auto dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-slate-50/90 dark:ring-offset-slate-950 whitespace-nowrap">
            {{ __('Cari Laporan') }}
        </button>
    </div>

    @if ($laporan)
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm space-y-8 max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium tracking-wider uppercase">{{ __('Nomor Tiket') }}</span>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100 font-mono mt-1">{{ $laporan->nomor_tiket }}</h2>
                </div>
                <div class="flex items-center gap-2">
                    @php
                        $badgeColors = [
                            'Belum Ditinjau' => 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100',
                            'Ditinjau' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200 dark:border-amber-800',
                            'Selesai' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                            'Ditolak' => 'bg-red-100 text-red-900 dark:bg-red-900/30 dark:text-red-400 border-red-200 dark:border-red-800',
                            'Belum Ditindaklanjuti' => 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100',
                            'Ditindaklanjuti' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200 dark:border-amber-800',
                        ];
                    @endphp
                    <span class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-slate-950 focus:ring-offset-2 dark:border-slate-800 dark:focus:ring-slate-300 {{ $badgeColors[$laporan->status_label] ?? 'bg-slate-100 text-slate-900 border-slate-200' }}">
                        {{ $laporan->status_label }}
                    </span>
                </div>
            </div>

            <div class="flex w-full relative z-0 mt-4">
                @php
                    $statusStr = $laporan->status;
                    $steps = [__('Menunggu'), __('Diproses'), __('Selesai')];
                    $statusToStep = [
                        'Belum Ditinjau' => 0,
                        'Ditinjau' => 1,
                        'Selesai' => 2,
                        'Ditolak' => 1,
                        'Belum Ditindaklanjuti' => 0,
                        'Ditindaklanjuti' => 1,
                    ];
                    $currentIdx = $statusToStep[$statusStr] ?? 0;
                    $isRejected = $statusStr === 'Ditolak';
                    if ($isRejected) {
                        $steps = [__('Menunggu'), __('Ditolak')];
                    }
                @endphp
                @foreach ($steps as $idx => $step)
                    <div class="relative flex-1 text-center">
                        @if ($idx < count($steps) - 1)
                            <div class="absolute top-4 left-1/2 w-full h-[2px] bg-slate-200 dark:bg-slate-800 -z-10"></div>
                            @if ($idx < $currentIdx)
                                <div class="absolute top-4 left-1/2 w-full h-[2px] -z-10 {{ $isRejected ? 'bg-red-500' : 'bg-brand-500' }}"></div>
                            @endif
                        @endif

                        <div class="mx-auto h-8 w-8 rounded-full flex items-center justify-center font-bold text-xs ring-8 ring-white dark:ring-slate-950
                            {{ $idx <= $currentIdx 
                                ? ($isRejected ? 'bg-red-500 text-white shadow shadow-red-500/20' : 'bg-brand-500 text-white shadow shadow-brand-500/20')
                                : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }}">
                            {{ $idx + 1 }}
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider block mt-4">{{ $step }}</span>
                    </div>
                @endforeach
            </div>

            <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($laporan)" />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 border-t border-slate-200 dark:border-slate-800 pt-8">
                <div class="space-y-6">
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold tracking-tight">{{ __('Rincian Aduan') }}</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="block text-slate-500 dark:text-slate-400 font-medium">{{ __('Kategori') }}</span>
                                <span class="text-slate-900 dark:text-slate-100 font-semibold">{{ $laporan->kategori }}</span>
                            </div>
                            <div>
                                <span class="block text-slate-500 dark:text-slate-400 font-medium">{{ __('Tanggal Masuk') }}</span>
                                <span class="text-slate-900 dark:text-slate-100 font-semibold">{{ $laporan->created_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                        <div>
                            <span class="block text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('Deskripsi') }}</span>
                            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed mt-1">{{ $laporan->deskripsi }}</p>
                        </div>
                    </div>

                    @if ($statusStr === 'Ditolak')
                        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900 rounded-lg">
                            <span class="block text-sm font-semibold text-red-800 dark:text-red-400">{{ __('Alasan Penolakan') }}</span>
                            <p class="text-sm text-red-700 dark:text-red-300 mt-1">{{ $laporan->alasan_penolakan ?? __('Tidak ada alasan penolakan yang ditulis.') }}</p>
                        </div>
                    @endif

                    @if ($statusStr === 'Selesai' && $laporan->bukti_foto_selesai)
                        <div class="space-y-2">
                            <span class="block text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('Bukti Foto Selesai') }}</span>
                            <div class="rounded-md overflow-hidden border border-slate-200 dark:border-slate-800 aspect-video relative">
                                <img src="/storage/{{ $laporan->bukti_foto_selesai }}" alt="{{ __('Foto bukti penyelesaian laporan oleh petugas') }}" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    @endif

                    @if ($laporan->fotos->isNotEmpty())
                        <div class="space-y-2">
                            <span class="block text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('Foto Lampiran Pengaduan') }}</span>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ($laporan->fotos as $foto)
                                    <div class="aspect-square rounded-md overflow-hidden border border-slate-200 dark:border-slate-800">
                                        <img src="/storage/{{ $foto->path_foto }}" alt="{{ __('Foto lampiran pengaduan kondisi pohon') }}" class="w-full h-full object-cover" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold tracking-tight">{{ __('Lokasi Peta') }}</h3>
                    <div wire:ignore wire:key="map-{{ $laporan->nomor_tiket }}"
                         x-data x-init="setTimeout(function(){dlhSimpleMap('cek-map-laporan-{{ $laporan->nomor_tiket }}',{lat:@js($laporan->latitude),lng:@js($laporan->longitude),zoom:14,popupText:'{{ __('Lokasi Laporan') }}'})},100)">
                        <div id="cek-map-laporan-{{ $laporan->nomor_tiket }}" class="w-full h-[300px] border border-slate-200 dark:border-slate-800 rounded-md overflow-hidden z-0 relative"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($pengaduanTataPenataan)
        @php
            $statusColor = $pengaduanTataPenataan->status?->color() ?? 'gray';
            $badgeMap = [
                'gray' => 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100',
                'warning' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-400',
                'success' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-400',
            ];
        @endphp
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm space-y-8 max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
                <div>
                    <span class="text-xs text-slate-500 font-medium tracking-wider uppercase">{{ __('Nomor Tiket') }}</span>
                    <h2 class="text-2xl font-bold font-mono mt-1">{{ $pengaduanTataPenataan->nomor_tiket }}</h2>
                </div>
                <span class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold {{ $badgeMap[$statusColor] ?? $badgeMap['gray'] }}">
                    {{ $pengaduanTataPenataan->status?->label() ?? $pengaduanTataPenataan->status }}
                </span>
            </div>

            <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($pengaduanTataPenataan)" />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Nama Pelapor') }}</span>
                            <span class="font-semibold">{{ $pengaduanTataPenataan->nama_pelapor }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Jenis Pengaduan') }}</span>
                            <span class="font-semibold">{{ $pengaduanTataPenataan->jenis_pengaduan?->label() ?? $pengaduanTataPenataan->jenis_pengaduan }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Tanggal Lapor') }}</span>
                            <span class="font-semibold">{{ $pengaduanTataPenataan->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">{{ __('Alamat') }}</span>
                            <span class="font-semibold">{{ $pengaduanTataPenataan->alamat }}</span>
                        </div>
                        @if ($pengaduanTataPenataan->nama_terlapor)
                            <div>
                                <span class="block text-slate-500 font-medium">{{ __('Nama Terlapor') }}</span>
                                <span class="font-semibold">{{ $pengaduanTataPenataan->nama_terlapor }}</span>
                            </div>
                        @endif
                        @if ($pengaduanTataPenataan->nama_perusahaan_terlapor)
                            <div>
                                <span class="block text-slate-500 font-medium">{{ __('Perusahaan Terlapor') }}</span>
                                <span class="font-semibold">{{ $pengaduanTataPenataan->nama_perusahaan_terlapor }}</span>
                            </div>
                        @endif
                    </div>
                    <div>
                        <span class="block text-sm text-slate-500 font-medium">{{ __('Deskripsi') }}</span>
                        <p class="text-sm text-slate-700 dark:text-slate-300 mt-1">{{ $pengaduanTataPenataan->deskripsi }}</p>
                    </div>
                    @if ($pengaduanTataPenataan->catatan_admin)
                        <div class="p-4 bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-800 rounded-lg">
                            <span class="block text-sm font-semibold text-brand-800 dark:text-brand-400">{{ __('Catatan Admin') }}</span>
                            <p class="text-sm mt-1">{{ $pengaduanTataPenataan->catatan_admin }}</p>
                        </div>
                    @endif
                    @if ($pengaduanTataPenataan->fotos->isNotEmpty())
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($pengaduanTataPenataan->fotos as $foto)
                                <div class="aspect-square rounded-md overflow-hidden border border-slate-200 dark:border-slate-800">
                                    <img src="/storage/{{ $foto->path_foto }}" alt="{{ __('Foto bukti pengaduan') }}" class="w-full h-full object-cover" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">{{ __('Lokasi Peta') }}</h3>
                    <div wire:ignore wire:key="map-ttp-{{ $pengaduanTataPenataan->nomor_tiket }}"
                         x-data x-init="setTimeout(function(){dlhSimpleMap('cek-map-ttp-{{ $pengaduanTataPenataan->nomor_tiket }}',{lat:@js($pengaduanTataPenataan->latitude),lng:@js($pengaduanTataPenataan->longitude),zoom:14,popupText:'{{ __('Lokasi Pengaduan') }}'})},100)">
                        <div id="cek-map-ttp-{{ $pengaduanTataPenataan->nomor_tiket }}" class="w-full h-[300px] border border-slate-200 dark:border-slate-800 rounded-md z-0"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>