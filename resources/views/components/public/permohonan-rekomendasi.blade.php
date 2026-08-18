<?php

use App\Http\Requests\StorePermohonanRekomendasiRequest;
use App\Http\Requests\StorePermohonanRekomendasiStep1Request;
use App\Livewire\Concerns\ThrottlesPublic;
use App\Livewire\Concerns\VerifiesGoogleRecaptcha;
use App\Models\PermohonanDokumen;
use App\Models\PermohonanRekomendasi;
use App\Services\FileUploadService;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;
    use VerifiesGoogleRecaptcha;
    use ThrottlesPublic;

    public int $step = 1;

    public ?string $nama_perusahaan = null;
    public ?string $nama_pemilik = null;
    public ?string $npwp = null;
    public ?string $jenis_usaha = null;
    public ?string $jenis_usaha_lainnya = null;
    public ?string $alamat_lengkap = null;
    public ?string $nomor_telepon = null;
    public ?string $email = null;

    public ?TemporaryUploadedFile $surat_permohonan = null;
    /** @var TemporaryUploadedFile[] */
    public array $dokumen_pendukung = [];

    public ?string $successTicket = null;

    public function nextStep()
    {
        $this->validate((new StorePermohonanRekomendasiStep1Request())->rules(), (new StorePermohonanRekomendasiStep1Request())->messages());

        $this->step = 2;
    }

    public function previousStep()
    {
        $this->step = 1;
    }

    public function submit()
    {
        if (! $this->verifyCaptcha('submit')) {
            return;
        }

        $this->resetCaptcha();

        $validated = $this->validate((new StorePermohonanRekomendasiRequest())->rules());

        if ($this->hitRateLimit('permohonan-rekomendasi', 10, 'form', __('Pengajuan dibatasi maksimal 10 per jam.'))) {
            return;
        }

        $fileService = app(FileUploadService::class);

        // Surat permohonan wajib PDF -> disimpan apa adanya.
        $suratPath = $fileService->store($this->surat_permohonan, 'permohonan-rekomendasi/surat', 'public') ?: null;

        $permohonan = PermohonanRekomendasi::create([
            'nama_perusahaan' => $validated['nama_perusahaan'],
            'nama_pemilik' => $validated['nama_pemilik'],
            'npwp' => $validated['npwp'],
            'jenis_usaha' => $validated['jenis_usaha'] === 'Lainnya'
                ? trim($validated['jenis_usaha_lainnya'])
                : $validated['jenis_usaha'],
            'alamat_lengkap' => $validated['alamat_lengkap'],
            'nomor_telepon' => $validated['nomor_telepon'],
            'email' => $validated['email'],
            'surat_permohonan' => $suratPath,
        ]);

        foreach ($this->dokumen_pendukung as $index => $file) {
            // Dokumen pendukung boleh PDF atau gambar; gambar otomatis
            // dikompres & dikonversi ke WebP, PDF disimpan apa adanya.
            $path = $fileService->store($file, 'permohonan-rekomendasi/dokumen', 'public');

            if ($path === false) {
                continue;
            }

            PermohonanDokumen::create([
                'permohonan_rekomendasi_id' => $permohonan->id,
                'path_dokumen' => $path,
                'nama_dokumen' => __('Dokumen Pendukung ') . ($index + 1),
            ]);
        }

        $this->successTicket = $permohonan->nomor_tiket;
        $this->reset([
            'step', 'nama_perusahaan', 'nama_pemilik', 'npwp', 'jenis_usaha', 'jenis_usaha_lainnya',
            'alamat_lengkap', 'nomor_telepon', 'email',
            'surat_permohonan', 'dokumen_pendukung',
        ]);
        $this->step = 1;
    }

    public function removeDokumen(int $index): void
    {
        if (isset($this->dokumen_pendukung[$index])) {
            unset($this->dokumen_pendukung[$index]);
            $this->dokumen_pendukung = array_values($this->dokumen_pendukung);
        }
    }

    public function jenisUsahaOptions(): array
    {
        return [
            'Rumah Makan' => __('Rumah Makan'),
            'Restoran/Kafe' => __('Restoran/Kafe'),
            'Bengkel' => __('Bengkel'),
            'Pabrik' => __('Pabrik'),
            'Perkebunan' => __('Perkebunan'),
            'Hotel' => __('Hotel'),
            'Laundry' => __('Laundry'),
            'Depot Air Minum' => __('Depot Air Minum'),
            'Toko/Swalayan' => __('Toko/Swalayan'),
            'Klinik/Rumah Sakit' => __('Klinik/Rumah Sakit'),
            'Gudang' => __('Gudang'),
            'Jasa Konstruksi' => __('Jasa Konstruksi'),
            'Peternakan' => __('Peternakan'),
            'Lainnya' => __('Lainnya'),
        ];
    }
};
?>

<div class="fi-form-card bg-white dark:bg-slate-950 rounded-2xl p-6 md:p-8 shadow-[0_1px_3px_rgba(13,43,29,0.06),0_12px_32px_-12px_rgba(13,43,29,0.10)] max-w-4xl mx-auto">
    @if ($successTicket)
        <div class="space-y-6 text-center py-8">
            <div
                class="h-16 w-16 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-full flex items-center justify-center mx-auto">
                <x-icons.berhasil class="size-8" />
            </div>
            <div class="space-y-2">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ __('Permohonan Berhasil Diajukan') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">{{ __('Simpan nomor tiket dan unduh bukti pengajuan untuk referensi Anda.') }}</p>
            </div>
            <div
                class="p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xs mx-auto">
                <span class="block text-[10px] text-brand-600 dark:text-brand-400 font-bold tracking-widest uppercase">{{ __('Nomor Tiket') }}</span>
                <x-public.copy-ticket :ticket="$successTicket" class="block text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 select-all tracking-wider" />
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                <a href="{{ url('/permohonan-rekomendasi/'.$successTicket.'/bukti-pdf') }}"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-brand-600 text-white hover:bg-brand-700 h-10 px-4">
                    {{ __('Unduh Bukti PDF') }}
                </a>
                <a href="{{ url('/cek-permohonan-rekomendasi') }}"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-slate-200 hover:bg-slate-100 h-10 px-4 dark:border-slate-800 dark:hover:bg-slate-800">
                    {{ __('Cek Status Permohonan') }}
                </a>
            </div>
        </div>
    @else
        {{-- Stepper --}}
        <div class="mb-8">
            <div class="flex items-center justify-center gap-3 sm:gap-4">
                <div class="flex items-center gap-2.5">
                    <span class="h-9 w-9 rounded-full flex items-center justify-center text-sm font-bold transition-all
                        {{ $step >= 1 ? 'bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-md shadow-brand-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }}">1</span>
                    <span class="text-sm font-semibold {{ $step >= 1 ? 'text-slate-900 dark:text-slate-100' : 'text-slate-400' }}">{{ __('Data Perusahaan') }}</span>
                </div>
                <div class="h-0.5 w-10 sm:w-16 rounded-full {{ $step >= 2 ? 'bg-brand-500' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
                <div class="flex items-center gap-2.5">
                    <span class="h-9 w-9 rounded-full flex items-center justify-center text-sm font-bold transition-all
                        {{ $step >= 2 ? 'bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-md shadow-brand-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }}">2</span>
                    <span class="text-sm font-semibold {{ $step >= 2 ? 'text-slate-900 dark:text-slate-100' : 'text-slate-400' }}">{{ __('Data Pengajuan') }}</span>
                </div>
            </div>
        </div>

        @if ($step === 1)
            <form wire:submit.prevent="nextStep" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <x-public.input
                            wire:model="nama_perusahaan"
                            name="nama_perusahaan"
                            label="{{ __('Nama Perusahaan') }}"
                            placeholder="{{ __('Nama lengkap perusahaan/instansi') }}"
                            required
                            icon="building"
                        />
                    </div>

                    <x-public.input
                        wire:model="nama_pemilik"
                        name="nama_pemilik"
                        label="{{ __('Nama Pemilik / Penanggung Jawab') }}"
                        placeholder="{{ __('Nama lengkap pemilik/PIC') }}"
                        required
                        icon="user"
                    />

                    <x-public.input
                        wire:model="npwp"
                        name="npwp"
                        label="{{ __('NPWP') }}"
                        placeholder="12.345.678.9-012.345"
                        required
                        icon="id-card"
                    />

                    <x-public.select
                        wire:model.live="jenis_usaha"
                        name="jenis_usaha"
                        label="{{ __('Jenis Usaha') }}"
                        :options="$this->jenisUsahaOptions()"
                        :searchable="true"
                        placeholder="{{ __('-- Pilih Jenis Usaha --') }}"
                        required
                    />

                    @if ($jenis_usaha === 'Lainnya')
                        <x-public.input
                            wire:model="jenis_usaha_lainnya"
                            name="jenis_usaha_lainnya"
                            label="{{ __('Sebutkan Jenis Usaha') }}"
                            placeholder="{{ __('Contoh: Laundry Kiloan') }}"
                            required
                        />
                    @endif

                    <x-public.input
                        wire:model="nomor_telepon"
                        name="nomor_telepon"
                        type="tel"
                        label="{{ __('Nomor Telepon') }}"
                        placeholder="{{ __('Contoh: 08123456789') }}"
                        required
                        icon="phone"
                    />

                    <div class="md:col-span-2">
                        <x-public.input
                            wire:model="email"
                            name="email"
                            type="email"
                            label="{{ __('Email') }}"
                            placeholder="contoh@gmail.com"
                            required
                            hint="{{ __('Email untuk menerima notifikasi update status permohonan') }}"
                            icon="mail"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <x-public.textarea
                            wire:model="alamat_lengkap"
                            name="alamat_lengkap"
                            label="{{ __('Alamat Lengkap') }}"
                            placeholder="{{ __('Alamat lengkap lokasi perusahaan/usaha, sertakan patokan terdekat') }}"
                            rows="3"
                            required
                            icon="map-pin"
                        />
                    </div>
                </div>

                <button type="submit" class="fi-submit-btn">
                    {{ __('Lanjut ke Step 2') }}
                    <x-icons.ui name="arrow-right" class="ml-2 h-4 w-4" />
                </button>
            </form>
        @else
            <form wire:submit.prevent="submit" data-dlh-recaptcha-action="submit" class="space-y-6">
                <div class="fi-field">
                    <label class="fi-label">
                        <span class="fi-icon-badge"><x-icons.ui name="document" /></span>
                        {{ __('Surat Permohonan') }} <span class="fi-required">*</span>
                        <span style="font-weight:400;color:#5b6b63;font-size:12.5px;">(PDF, max 5MB)</span>
                    </label>
                    <div class="fi-file-drop">
                        <button type="button" class="fi-file-btn" x-on:click="$refs.suratInput.click()">{{ __('Pilih File') }}</button>
                        <span class="fi-file-status">
                            <span x-data="{ name: '' }" x-init="$watch('$store.suratName', v => name = v)">
                                @if ($surat_permohonan)
                                    <span class="text-brand-600 dark:text-brand-400 font-medium">{{ $surat_permohonan->getClientOriginalName() }}</span>
                                @else
                                    {{ __('Belum ada file dipilih') }}
                                @endif
                            </span>
                        </span>
                        <input wire:model="surat_permohonan" x-ref="suratInput" type="file" accept="application/pdf" required
                            style="position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;" />
                    </div>
                    @error('surat_permohonan') <p class="fi-error"><x-icons.ui name="alert" />{{ $message }}</p> @enderror
                </div>

                <div class="fi-field">
                    <label class="fi-label">
                        <span class="fi-icon-badge"><x-icons.ui name="upload" /></span>
                        {{ __('Dokumen Pendukung') }}
                        <span style="font-weight:400;color:#5b6b63;font-size:12.5px;">(PDF/Gambar, min 1, max 5, max 5MB/file)</span>
                    </label>
                    <div class="fi-file-drop">
                        <button type="button" class="fi-file-btn" x-on:click="$refs.dokumenInput.click()">{{ __('Pilih File') }}</button>
                        <span class="fi-file-status">
                            @if (count($dokumen_pendukung))
                                <span class="text-brand-600 dark:text-brand-400 font-medium">{{ count($dokumen_pendukung) }} file terpilih</span>
                            @else
                                {{ __('Belum ada file dipilih') }}
                            @endif
                        </span>
                        <input wire:model="dokumen_pendukung" x-ref="dokumenInput" type="file" multiple accept="application/pdf,image/jpeg,image/png,image/jpg"
                            style="position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;" />
                    </div>
                    @error('dokumen_pendukung') <p class="fi-error"><x-icons.ui name="alert" />{{ $message }}</p> @enderror
                    @error('dokumen_pendukung.*') <p class="fi-error"><x-icons.ui name="alert" />{{ $message }}</p> @enderror

                    @if (count($dokumen_pendukung))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-3">
                            @foreach ($dokumen_pendukung as $index => $dok)
                                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                                    <span class="flex-shrink-0 h-8 w-8 rounded-lg bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 flex items-center justify-center">
                                        <x-icons.ui name="document" class="h-4 w-4" />
                                    </span>
                                    <span class="flex-1 text-xs font-medium text-slate-700 dark:text-slate-300 truncate">{{ $dok->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="removeDokumen({{ $index }})"
                                        class="flex-shrink-0 h-6 w-6 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600 transition-colors">
                                        <x-icons.ui name="close" class="h-3 w-3" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="previousStep"
                        class="inline-flex items-center justify-center rounded-full text-sm font-semibold border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 h-12 px-6 transition-colors">
                        <x-icons.ui name="chevron-left" class="mr-2 h-4 w-4" />
                        {{ __('Kembali') }}
                    </button>
                </div>

                @error('form')
                    <div class="dlh-limit-alert" role="alert">
                        <x-icons.ui name="alert" />
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="fi-submit-btn flex-1">
                        {{ __('Kirim Permohonan') }}
                        <x-icons.ui name="arrow-right" class="ml-2 h-4 w-4" />
                    </button>
                </div>
            </form>
        @endif

        <x-google-recaptcha />
    @endif

    <style>

        .fi-form-card {
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        .fi-field {
            position: relative;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        .fi-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #12201a;
            margin-bottom: 8px;
            cursor: pointer;
        }

        .dark .fi-label { color: #e2e8f0; }

        .fi-required {
            color: #f43f5e;
            font-size: 14px;
            font-weight: 400;
            margin-left: 1px;
        }

        .fi-icon-badge {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: #e6f5ec;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #146a44;
            flex-shrink: 0;
        }

        .dark .fi-icon-badge { background: rgba(30,165,103,0.15); color: #1ea567; }

        .fi-icon-badge svg {
            width: 15px;
            height: 15px;
        }

        /* ── File Upload ── */
        .fi-file-drop {
            border: 1.5px dashed #a9dcc0;
            border-radius: 16px;
            background: #f4faf6;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: border-color .18s ease, background .18s ease;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        .fi-file-drop:hover {
            background: #eefaf3;
            border-color: #1ea567;
        }

        .fi-file-btn {
            flex-shrink: 0;
            height: 38px;
            padding: 0 20px;
            border-radius: 9999px;
            border: none;
            background: linear-gradient(180deg, #178a53, #146a44);
            color: #fff;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 10px -2px rgba(20, 106, 68, 0.4);
            transition: filter .15s ease;
        }

        .fi-file-btn:hover {
            filter: brightness(1.05);
        }

        .fi-file-status {
            font-size: 13px;
            color: #5f7268;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ── Submit Button ── */
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

        .fi-submit-btn:active {
            transform: translateY(0);
        }

        /* ── Dark mode ── */
        .dark .fi-file-drop {
            background: #0f172a;
            border-color: #334155;
        }
        .dark .fi-file-drop:hover {
            background: #1e293b;
            border-color: #1ea567;
        }
        .dark .fi-file-status { color: #64748b; }
        .dark .fi-submit-btn {
            background: linear-gradient(180deg, #1ea567, #178a53);
            box-shadow: 0 10px 24px -8px rgba(30, 165, 103, 0.5);
        }
    </style>
</div>
