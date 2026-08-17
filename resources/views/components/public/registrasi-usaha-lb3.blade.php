<?php

use App\Http\Requests\StoreRegistrasiUsahaLb3Request;
use App\Models\RegistrasiUsahaLb3;
use Livewire\Component;

new class extends Component
{
    public ?string $nama_perusahaan = null;
    public ?string $nomor_telepon = null;
    public ?string $email = null;
    public ?string $alamat = null;
    public ?string $jenis_lb3 = null;
    public ?string $jenis_lb3_lainnya = null;

    public ?string $successNumber = null;

    public function submit(): void
    {
        $validated = $this->validate((new StoreRegistrasiUsahaLb3Request())->rules());

        $ip = request()->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('registrasi-usaha-lb3:'.$ip, 3)) {
            $this->addError('email', __('Batas maksimal pengiriman tercapai (3 registrasi per jam).'));

            return;
        }

        \Illuminate\Support\Facades\RateLimiter::hit('registrasi-usaha-lb3:'.$ip, 3600);

        if ($this->jenis_lb3 === 'Lainnya' && blank($this->jenis_lb3_lainnya)) {
            $this->addError('jenis_lb3_lainnya', __('Mohon isi jenis LB3 lainnya.'));

            return;
        }

        $registrasi = RegistrasiUsahaLb3::create(array_merge($validated, [
            'jenis_lb3_lainnya' => ($this->jenis_lb3 === 'Lainnya') ? $this->jenis_lb3_lainnya : null,
        ]));

        $this->successNumber = $registrasi->nomor_registrasi;
        $this->reset(['nama_perusahaan', 'nomor_telepon', 'email', 'alamat', 'jenis_lb3', 'jenis_lb3_lainnya']);
    }

    public function getJenisOptions(): array
    {
        $options = [
            'Pengumpul LB3',
            'Pengangkut LB3',
            'Pemanfaat LB3',
            'Pengolah LB3',
            'Penimbun LB3',
            'Lainnya',
        ];

        return array_combine($options, $options);
    }
};
?>

<div class="fi-form-card bg-white dark:bg-slate-950 rounded-2xl p-6 md:p-8 shadow-[0_1px_3px_rgba(13,43,29,0.06),0_12px_32px_-12px_rgba(13,43,29,0.10)] max-w-2xl mx-auto">
    @if ($successNumber)
        <div class="space-y-6 text-center py-8">
            <div class="h-16 w-16 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-full flex items-center justify-center mx-auto"><x-icons.berhasil class="size-8" /></div>
            <div class="space-y-2">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ __('Registrasi Berhasil') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">{{ __('Simpan nomor registrasi LB3 Anda untuk referensi dan pengecekan status.') }}</p>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xs mx-auto">
                <span class="block text-[10px] text-brand-600 dark:text-brand-400 font-bold tracking-widest uppercase">{{ __('Nomor Registrasi') }}</span>
                <span class="block text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 select-all tracking-wider font-mono">{{ $successNumber }}</span>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                <a href="{{ url('/cek-registrasi-lb3') }}"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-slate-200 hover:bg-slate-100 h-10 px-4 dark:border-slate-800 dark:hover:bg-slate-800">
                    {{ __('Cek Status Registrasi') }}
                </a>
                <button wire:click="$set('successNumber', null)" type="button"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-white hover:bg-slate-800 h-10 px-4 dark:bg-slate-50 dark:text-slate-900">{{ __('Daftar Lagi') }}</button>
            </div>
        </div>
    @else
        <form wire:submit.prevent="submit" class="space-y-5">
            <x-public.input
                wire:model="nama_perusahaan"
                name="nama_perusahaan"
                label="{{ __('Nama Perusahaan / Pelaku Usaha') }}"
                placeholder="{{ __('Nama lengkap perusahaan atau pelaku usaha') }}"
                required
                icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>'
            />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <x-public.input
                    wire:model="nomor_telepon"
                    name="nomor_telepon"
                    type="tel"
                    label="{{ __('Nomor Telepon') }}"
                    placeholder="{{ __('Contoh: 08123456789') }}"
                    required
                    icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>'
                />

                <x-public.input
                    wire:model="email"
                    name="email"
                    type="email"
                    label="{{ __('Email') }}"
                    placeholder="contoh@email.com"
                    required
                    hint="{{ __('Untuk notifikasi update status registrasi') }}"
                    icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg>'
                />
            </div>

            <x-public.textarea
                wire:model="alamat"
                name="alamat"
                label="{{ __('Alamat') }}"
                placeholder="{{ __('Alamat lengkap perusahaan/pelaku usaha, sertakan patokan terdekat') }}"
                rows="3"
                required
                icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 8.5c0 5.5-8.4 11.5-8.4 11.5S3.6 14 3.6 8.5a8.4 8.4 0 1 1 16.8 0Z"/><circle cx="12" cy="8.5" r="2.6"/></svg>'
            />

            <x-public.select
                wire:model="jenis_lb3"
                name="jenis_lb3"
                label="{{ __('Jenis LB3') }}"
                :options="$this->getJenisOptions()"
                :searchable="true"
                placeholder="{{ __('-- Pilih Jenis LB3 --') }}"
                required
            />

            @if ($jenis_lb3 === 'Lainnya')
                <x-public.input
                    wire:model="jenis_lb3_lainnya"
                    name="jenis_lb3_lainnya"
                    label="{{ __('Sebutkan Jenis LB3 Lainnya') }}"
                    placeholder="{{ __('Tulis jenis LB3 secara manual...') }}"
                    required
                />
            @endif

            <button type="submit" class="fi-submit-btn">
                {{ __('Kirim Registrasi') }}
                <svg class="ml-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 14 0M12 5l7 7-7 7"/></svg>
            </button>
        </form>
    @endif

    <style>

        .fi-form-card {
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        .fi-submit-btn {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: 9999px;
            background: linear-gradient(180deg, #178a53, #146a44);
            color: #fff;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .2px;
            cursor: pointer;
            box-shadow: 0 10px 24px -8px rgba(20, 106, 68, 0.55);
            transition: transform .12s ease, box-shadow .12s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .fi-submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 28px -8px rgba(20, 106, 68, 0.6);
        }
        .fi-submit-btn:active { transform: translateY(0); }

        .dark .fi-submit-btn {
            background: linear-gradient(180deg, #1ea567, #178a53);
            box-shadow: 0 10px 24px -8px rgba(30, 165, 103, 0.5);
        }
    </style>
</div>
