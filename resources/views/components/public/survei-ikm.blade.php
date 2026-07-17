<?php

use Livewire\Component;
use App\Models\IkmResponse;
use App\Models\Setting;

new class extends Component
{
    public $indikator_1 = 3;
    public $indikator_2 = 3;
    public $indikator_3 = 3;
    public $indikator_4 = 3;
    public $indikator_5 = 3;
    public $indikator_6 = 3;
    public $indikator_7 = 3;
    public ?string $saran = null;
    public ?string $fingerprintHash = null;

    public $surveySuccess = false;

    protected $rules = [
        'indikator_1' => 'required|integer|between:1,4',
        'indikator_2' => 'required|integer|between:1,4',
        'indikator_3' => 'required|integer|between:1,4',
        'indikator_4' => 'required|integer|between:1,4',
        'indikator_5' => 'required|integer|between:1,4',
        'indikator_6' => 'required|integer|between:1,4',
        'indikator_7' => 'required|integer|between:1,4',
        'saran' => 'nullable|string|max:500',
    ];

    public function setFingerprint(string $hash): void
    {
        $this->fingerprintHash = $hash;
    }

    public function submit()
    {
        $this->validate();

        $fingerprintEnabled = Setting::get('ikm_fingerprint_enabled', true);

        // When fingerprint is ON: restrict to once per week per device
        if ($fingerprintEnabled && $this->fingerprintHash) {
            $oneWeekAgo = now()->subWeek();
            $exists = IkmResponse::where('fingerprint_hash', $this->fingerprintHash)
                ->where('created_at', '>=', $oneWeekAgo)
                ->exists();
            if ($exists) {
                $this->addError('general', __('Anda baru saja mengikuti survei ini minggu ini. Silakan kembali lagi minggu depan.'));
                return;
            }
        }

        // When fingerprint is OFF: no restriction (unlimited)

        IkmResponse::create([
            'indikator_1' => $this->indikator_1,
            'indikator_2' => $this->indikator_2,
            'indikator_3' => $this->indikator_3,
            'indikator_4' => $this->indikator_4,
            'indikator_5' => $this->indikator_5,
            'indikator_6' => $this->indikator_6,
            'indikator_7' => $this->indikator_7,
            'saran' => $this->saran,
            'fingerprint_hash' => $this->fingerprintHash,
        ]);

        $this->surveySuccess = true;
        $this->reset(['saran']);
    }
};
?>

<div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm max-w-4xl mx-auto"
     x-data x-init="$nextTick(() => {
         if (typeof FingerprintJS !== 'undefined') {
             FingerprintJS.load().then(fp => fp.get()).then(result => {
                 $wire.setFingerprint(result.visitorId);
             });
         }
     })">
    @if ($surveySuccess)
        <div class="space-y-6 text-center py-8">
            <div class="h-16 w-16 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto text-3xl font-bold">
                ✓
            </div>
            <div class="space-y-2">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ __('Terima Kasih Atas Partisipasi Anda') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">{{ __('Tanggapan Anda telah tersimpan dengan aman. Penilaian yang Anda berikan sangat membantu DLH Kota Palu meningkatkan mutu pelayanan kami.') }}</p>
            </div>
            <div class="pt-4">
                <a href="/" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none ring-offset-white bg-slate-900 text-slate-50 hover:bg-slate-900/90 h-10 py-2 px-4 dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-slate-50/90 dark:ring-offset-slate-950">
                    {{ __('Kembali ke Beranda') }}
                </a>
            </div>
        </div>
    @else
        <form wire:submit.prevent="submit" class="space-y-8">
            @error('general')
                <div class="p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900 rounded-2xl text-xs text-red-600 dark:text-red-400 font-bold">
                    {{ $message }}
                </div>
            @enderror

            @php
                $questions = [
                    'indikator_1' => __('1. Bagaimana kemudahan persyaratan pelayanan pengelolaan pohon pelindung?'),
                    'indikator_2' => __('2. Bagaimana kecepatan waktu petugas dalam menangani pengaduan/layanan?'),
                    'indikator_3' => __('3. Bagaimana transparansi biaya/tarif pelayanan (bebas pungli)?'),
                    'indikator_4' => __('4. Bagaimana kelayakan sarana, prasarana, dan alat keselamatan petugas?'),
                    'indikator_5' => __('5. Bagaimana keramahan, kesopanan, dan kompetensi petugas di lapangan?'),
                    'indikator_6' => __('6. Bagaimana penanganan pengaduan dan ketepatan respons kendala?'),
                    'indikator_7' => __('7. Bagaimana hasil pelayanan pemangkasan/penanganan pohon pelindung?'),
                ];
                $scales = [
                    1 => __('Sangat Tidak Puas'),
                    2 => __('Kurang Puas'),
                    3 => __('Puas'),
                    4 => __('Sangat Puas'),
                ];
            @endphp

            @foreach ($questions as $field => $label)
                <div class="space-y-3">
                    <span class="block text-sm font-semibold tracking-tight text-slate-900 dark:text-slate-100">{{ $label }}</span>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach ($scales as $val => $text)
                            <label class="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-800 rounded-md cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors select-none [&:has(:checked)]:border-brand-500 [&:has(:checked)]:bg-brand-50 dark:[&:has(:checked)]:bg-brand-900/20">
                                <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">{{ $text }}</span>
                                <input type="radio" wire:model="{{ $field }}" value="{{ $val }}" class="h-4 w-4 text-brand-600 border-slate-300 focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-950" />
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="space-y-2 border-t border-slate-200 dark:border-slate-800 pt-6">
                <label for="saran" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:text-slate-300">{{ __('Saran & Masukan (Opsional)') }}</label>
                <textarea wire:model="saran" id="saran" rows="3" placeholder="{{ __('Tuliskan saran perbaikan layanan di sini...') }}" class="flex min-h-[80px] w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-800 dark:ring-offset-slate-950 dark:focus-visible:ring-brand-500 dark:placeholder:text-slate-400"></textarea>
                @error('saran') <span class="text-[0.8rem] font-medium text-red-500 block">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none ring-offset-white bg-slate-900 text-slate-50 hover:bg-slate-900/90 h-10 py-2 px-4 w-full dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-slate-50/90 dark:ring-offset-slate-950 shadow-sm">
                {{ __('Kirim Survei IKM') }}
            </button>
        </form>
    @endif
</div>
