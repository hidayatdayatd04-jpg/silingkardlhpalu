<?php

use App\Http\Requests\StoreRegistrasiUsahaLb3Request;
use App\Livewire\Concerns\ThrottlesPublic;
use App\Livewire\Concerns\VerifiesGoogleRecaptcha;
use App\Models\RegistrasiUsahaLb3;
use Livewire\Component;

new class extends Component
{
    use VerifiesGoogleRecaptcha;
    use ThrottlesPublic;

    public ?string $nama_perusahaan = null;
    public ?string $nomor_telepon = null;
    public ?string $alamat = null;
    public ?string $jenis_lb3 = null;
    public ?string $jenis_lb3_lainnya = null;

    public ?string $successNumber = null;

    public function submit(): void
    {
        if (! $this->verifyCaptcha('submit')) {
            return;
        }

        $this->resetCaptcha();

        $validated = $this->validate((new StoreRegistrasiUsahaLb3Request())->rules());

        if ($this->hitRateLimit('registrasi-usaha-lb3', 10, 'form', __('Pengiriman dibatasi maksimal 10 per jam.'))) {
            return;
        }

        if ($this->jenis_lb3 === 'Lainnya' && blank($this->jenis_lb3_lainnya)) {
            $this->addError('jenis_lb3_lainnya', __('Mohon isi jenis LB3 lainnya.'));

            return;
        }

        $registrasi = RegistrasiUsahaLb3::create(array_merge($validated, [
            'jenis_lb3_lainnya' => ($this->jenis_lb3 === 'Lainnya') ? $this->jenis_lb3_lainnya : null,
        ]));

        $this->successNumber = $registrasi->nomor_registrasi;
        $this->reset(['nama_perusahaan', 'nomor_telepon', 'alamat', 'jenis_lb3', 'jenis_lb3_lainnya']);
    }

    public function getJenisOptions(): array
    {
        $options = [
            'Medis',
            'Oli Bekas',
            'Kimia',
            'Aki',
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
        <form wire:submit.prevent="submit" data-dlh-recaptcha-action="submit" class="space-y-5">
            <x-public.input
                wire:model="nama_perusahaan"
                name="nama_perusahaan"
                label="{{ __('Nama Perusahaan / Pelaku Usaha') }}"
                placeholder="{{ __('Nama lengkap perusahaan atau pelaku usaha') }}"
                required
                icon="building"
            />

            <x-public.input
                wire:model="nomor_telepon"
                name="nomor_telepon"
                type="tel"
                label="{{ __('Nomor Telepon') }}"
                placeholder="{{ __('Contoh: 08123456789') }}"
                required
                icon="phone"
            />

            <x-public.textarea
                wire:model="alamat"
                name="alamat"
                label="{{ __('Alamat') }}"
                placeholder="{{ __('Alamat lengkap perusahaan/pelaku usaha, sertakan patokan terdekat') }}"
                rows="3"
                required
                icon="map-pin"
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

            @error('form')
                <div class="dlh-limit-alert" role="alert">
                    <x-icons.ui name="alert" />
                    <span>{{ $message }}</span>
                </div>
            @enderror

        <x-google-recaptcha />

            <button type="submit" class="fi-submit-btn">
                {{ __('Kirim Registrasi') }}
                <x-icons.ui name="arrow-right" class="ml-2 h-4 w-4" />
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
