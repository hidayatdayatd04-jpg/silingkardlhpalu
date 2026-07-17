<?php

use App\Http\Requests\StoreRegistrasiUsahaLb3Request;
use App\Models\JenisLb3;
use App\Models\RegistrasiUsahaLb3;
use Livewire\Component;

new class extends Component
{
    public ?string $nama_perusahaan = null;
    public ?string $nomor_telepon = null;
    public ?string $email = null;
    public ?string $alamat = null;
    public ?string $jenis_lb3_id = null;

    public ?string $successNumber = null;

    public function submit(): void
    {
        $validated = $this->validate((new StoreRegistrasiUsahaLb3Request())->rules());

        $registrasi = RegistrasiUsahaLb3::create($validated);

        $this->successNumber = $registrasi->nomor_registrasi;
        $this->reset(['nama_perusahaan', 'nomor_telepon', 'email', 'alamat', 'jenis_lb3_id']);
    }

    public function getJenisOptions(): array
    {
        return JenisLb3::orderBy('nama')->pluck('nama', 'id')->all();
    }
};
?>

<div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm max-w-2xl mx-auto">
    @if ($successNumber)
        <div class="space-y-6 text-center py-8">
            <div class="h-16 w-16 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center mx-auto text-3xl font-bold">✓</div>
            <h3 class="text-2xl font-bold">{{ __('Registrasi Berhasil') }}</h3>
            <p class="text-sm text-slate-500">{{ __('Nomor registrasi LB3 Anda:') }}</p>
            <div class="p-4 bg-slate-50 dark:bg-slate-900 border rounded-lg max-w-xs mx-auto">
                <span class="block text-2xl font-bold font-mono select-all">{{ $successNumber }}</span>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                <a href="{{ url('/cek-registrasi-lb3') }}"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-slate-200 hover:bg-slate-100 h-10 px-4 dark:border-slate-800 dark:hover:bg-slate-800">
                    {{ __('Cek Status Registrasi') }}
                </a>
                <button wire:click="$set('successNumber', null)" type="button" class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-white h-10 px-4 dark:bg-slate-50 dark:text-slate-900">{{ __('Daftar Lagi') }}</button>
            </div>
        </div>
    @else
        <form wire:submit.prevent="submit" class="space-y-4">
            <div class="space-y-2">
                <label class="text-sm font-medium">{{ __('Nama Perusahaan / Pelaku Usaha') }}</label>
                <input wire:model="nama_perusahaan" type="text" class="flex h-10 w-full rounded-md border border-slate-200 px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-950" />
                @error('nama_perusahaan') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium">{{ __('Nomor Telepon') }}</label>
                <input wire:model="nomor_telepon" type="tel" placeholder="{{ __('Contoh: 08123456789') }}" class="flex h-10 w-full rounded-md border border-slate-200 px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-950" />
                @error('nomor_telepon') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium">{{ __('Email') }} <span class="text-red-500">*</span></label>
                <input wire:model="email" type="email" placeholder="{{ __('contoh@email.com') }}" class="flex h-10 w-full rounded-md border border-slate-200 px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-950" required />
                @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                <p class="text-xs text-slate-500">{{ __('Email untuk menerima notifikasi update status registrasi') }}</p>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium">{{ __('Alamat') }}</label>
                <textarea wire:model="alamat" rows="3" class="flex w-full rounded-md border border-slate-200 px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-950"></textarea>
                @error('alamat') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium">{{ __('Jenis LB3') }}</label>
                <x-admin.select
                    wire:model="jenis_lb3_id"
                    name="jenis_lb3_id"
                    :options="$this->getJenisOptions()"
                    placeholder="{{ __('-- Pilih Jenis LB3 --') }}"
                    :searchable="true"
                />
                @error('jenis_lb3_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-white h-10 w-full dark:bg-slate-50 dark:text-slate-900">{{ __('Kirim Registrasi') }}</button>
        </form>
    @endif
</div>
