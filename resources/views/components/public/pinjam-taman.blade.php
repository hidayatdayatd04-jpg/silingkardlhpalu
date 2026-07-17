<?php

use App\Enums\PengaduanStatus;
use App\Http\Requests\StorePermohonanPinjamTamanRequest;
use App\Models\PermohonanPinjamTaman;
use App\Models\TamanKota;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $nama_pemohon = '';
    public string $nomor_hp = '';
    public string $email = '';
    public string $nama_kegiatan = '';
    public string $taman_kota_id = '';
    public string $tanggal_kegiatan = '';
    public string $tanggal_selesai = '';
    public ?string $surat_permohonan = null;
    public $jaminan_kebersihan = false;
    public ?string $surat_jaminan = null;
    public ?string $successTicket = null;
    public $conflictWarning = false;

    public function updatedTamanKotaId(): void
    {
        $this->checkConflict();
    }

    public function checkConflict(): void
    {
        if ($this->taman_kota_id && $this->tanggal_kegiatan) {
            $end = $this->tanggal_selesai ?: $this->tanggal_kegiatan;
            $this->conflictWarning = PermohonanPinjamTaman::hasConflict(
                (int) $this->taman_kota_id,
                new \DateTime($this->tanggal_kegiatan),
                new \DateTime($end),
            );
        }
    }

    public function getCalendarDaysProperty(): array
    {
        if (! $this->taman_kota_id) {
            return [];
        }

        $booked = PermohonanPinjamTaman::query()
            ->where('taman_kota_id', $this->taman_kota_id)
            ->where('status', PengaduanStatus::DITINJAU->value)
            ->get(['tanggal_kegiatan', 'tanggal_selesai']);

        $days = [];
        for ($i = 0; $i < 30; $i++) {
            $date = now()->addDays($i)->startOfDay();
            $isBooked = $booked->contains(function ($booking) use ($date) {
                $start = $booking->tanggal_kegiatan->copy()->startOfDay();
                $end = ($booking->tanggal_selesai ?? $booking->tanggal_kegiatan)->copy()->endOfDay();

                return $date->between($start, $end);
            });

            $days[] = [
                'label' => $date->format('d'),
                'dayName' => $date->translatedFormat('D'),
                'monthLabel' => $date->format('M'),
                'isBooked' => $isBooked,
                'isToday' => $date->isToday(),
            ];
        }

        return $days;
    }

    public function submit(): void
    {
        $validated = $this->validate((new StorePermohonanPinjamTamanRequest())->rules());
        $this->checkConflict();

        if ($this->conflictWarning) {
            $this->addError('tanggal_kegiatan', __('Tanggal bentrok dengan peminjaman yang sudah disetujui.'));

            return;
        }

        $record = PermohonanPinjamTaman::create([
            'nama_pemohon' => $validated['nama_pemohon'],
            'nomor_hp' => $validated['nomor_hp'],
            'email' => $validated['email'],
            'nama_kegiatan' => $validated['nama_kegiatan'],
            'taman_kota_id' => $validated['taman_kota_id'],
            'tanggal_kegiatan' => $validated['tanggal_kegiatan'],
            'tanggal_selesai' => $validated['tanggal_selesai'] ?: $validated['tanggal_kegiatan'],
            'surat_permohonan' => $this->surat_permohonan->store('pinjam-taman', 'public'),
            'jaminan_kebersihan' => true,
            'surat_jaminan' => $this->surat_jaminan?->store('pinjam-taman', 'public'),
        ]);

        $this->successTicket = $record->nomor_tiket;
        $this->reset(['nama_pemohon', 'nomor_hp', 'email', 'nama_kegiatan', 'taman_kota_id', 'tanggal_kegiatan', 'tanggal_selesai', 'surat_permohonan', 'jaminan_kebersihan', 'surat_jaminan']);
        $this->conflictWarning = false;
    }

    public function getTamansProperty()
    {
        return TamanKota::orderBy('nama')->pluck('nama', 'id');
    }
};
?>

<div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm max-w-4xl mx-auto space-y-6">
    @if ($successTicket)
        <div class="space-y-6 text-center py-8">
            <div
                class="h-16 w-16 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-full flex items-center justify-center mx-auto text-3xl font-bold">
                ✓
            </div>
            <div class="space-y-2">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ __('Permohonan Berhasil Terkirim') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">{{ __('Simpan nomor tiket di bawah untuk mengecek status peminjaman taman.') }}</p>
            </div>
            <div
                class="p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xs mx-auto">
                <span class="block text-[10px] text-brand-600 dark:text-brand-400 font-extrabold tracking-widest uppercase">{{ __('Nomor Tiket Anda') }}</span>
                <span class="block text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 select-all tracking-wider font-mono">{{ $successTicket }}</span>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                <a href="{{ url('/cek-pinjam-taman') }}"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-slate-200 hover:bg-slate-100 h-10 py-2 px-4 dark:border-slate-800 dark:hover:bg-slate-800">
                    {{ __('Cek Status Peminjaman') }}
                </a>
                <button wire:click="$set('successTicket', null)"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-slate-900 text-slate-50 hover:bg-slate-900/90 h-10 py-2 px-4 dark:bg-slate-50 dark:text-slate-900">
                    {{ __('Ajukan Peminjaman Baru') }}
                </button>
            </div>
        </div>
    @else
        @if ($taman_kota_id && count($this->calendarDays))
            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('Kalender Ketersediaan (30 Hari ke Depan)') }}</p>
                    <div class="flex items-center gap-3 text-xs text-slate-500">
                        <span class="flex items-center gap-1"><span class="h-3 w-3 rounded bg-emerald-100 dark:bg-emerald-900/40 border border-emerald-300"></span> {{ __('Tersedia') }}</span>
                        <span class="flex items-center gap-1"><span class="h-3 w-3 rounded bg-amber-200 dark:bg-amber-900/50 border border-amber-400"></span> {{ __('Terisi') }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-1.5">
                    @foreach ($this->calendarDays as $day)
                        <div @class([
                            'flex flex-col items-center justify-center rounded-md p-1.5 text-center border text-xs',
                            'bg-amber-200 dark:bg-amber-900/50 border-amber-400 text-amber-900 dark:text-amber-200' => $day['isBooked'],
                            'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300' => ! $day['isBooked'],
                            'ring-2 ring-brand-500 ring-offset-1' => $day['isToday'],
                        ])>
                            <span class="text-[9px] uppercase text-slate-500">{{ $day['dayName'] }}</span>
                            <span class="font-bold text-sm">{{ $day['label'] }}</span>
                            <span class="text-[9px] text-slate-400">{{ $day['monthLabel'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <form wire:submit.prevent="submit" class="space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium dark:text-slate-300">{{ __('Nama Pemohon/Komunitas') }}</label>
                    <input wire:model="nama_pemohon" type="text"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" />
                    @error('nama_pemohon') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label for="nomor_hp" class="text-sm font-medium dark:text-slate-300">{{ __('Nomor Telepon') }}</label>
                    <input wire:model="nomor_hp" id="nomor_hp" type="tel" placeholder="{{ __('Contoh: 08123456789') }}"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" />
                    @error('nomor_hp') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium dark:text-slate-300">{{ __('Email') }} <span class="text-red-500">*</span></label>
                    <input wire:model="email" id="email" type="email" placeholder="{{ __('contoh@email.com') }}"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" required />
                    @error('email') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                    <p class="text-xs text-slate-500">{{ __('Email untuk menerima notifikasi update status permohonan') }}</p>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium dark:text-slate-300">{{ __('Nama Kegiatan') }}</label>
                    <input wire:model="nama_kegiatan" type="text"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" />
                    @error('nama_kegiatan') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium dark:text-slate-300">{{ __('Taman') }}</label>
                <x-admin.select
                    wire:model.live="taman_kota_id"
                    name="taman_kota_id"
                    :options="$this->tamans"
                    :searchable="true"
                    placeholder="{{ __('Pilih taman...') }}"
                />
                @error('taman_kota_id') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium dark:text-slate-300">{{ __('Tanggal & Jam Mulai') }}</label>
                    <input wire:model.live="tanggal_kegiatan" wire:change="checkConflict" type="datetime-local"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" />
                    @error('tanggal_kegiatan') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium dark:text-slate-300">{{ __('Tanggal & Jam Selesai') }}</label>
                    <input wire:model.live="tanggal_selesai" wire:change="checkConflict" type="datetime-local"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" />
                    @error('tanggal_selesai') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>
            @if ($conflictWarning)
                <p class="text-red-600 text-sm font-medium">⚠ {{ __('Tanggal bentrok dengan jadwal yang sudah disetujui.') }}</p>
            @endif
            <div class="space-y-2">
                <label class="text-sm font-medium dark:text-slate-300">{{ __('Surat Permohonan (PDF, max 5MB)') }}</label>
                <input wire:model="surat_permohonan" type="file" accept="application/pdf"
                    class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1.5 text-sm dark:border-slate-800" />
                @error('surat_permohonan') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm dark:text-slate-300">
                <input wire:model="jaminan_kebersihan" type="checkbox" class="rounded" />
                {{ __('Saya berjanji menjaga kebersihan taman') }}
            </label>
            @error('jaminan_kebersihan') <span class="text-[0.8rem] font-medium text-red-500 block">{{ $message }}</span> @enderror
            <div class="space-y-2">
                <label class="text-sm font-medium dark:text-slate-300">{{ __('Surat Jaminan (opsional, PDF max 5MB)') }}</label>
                <input wire:model="surat_jaminan" type="file" accept="application/pdf"
                    class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1.5 text-sm dark:border-slate-800" />
                @error('surat_jaminan') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
            </div>
            <button type="submit"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-slate-50 hover:bg-slate-900/90 h-10 py-2 px-8 dark:bg-slate-50 dark:text-slate-900 shadow-sm">
                {{ __('Ajukan Peminjaman') }}
            </button>
        </form>
    @endif
</div>
