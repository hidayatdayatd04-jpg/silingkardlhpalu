<?php

use App\Models\PengajuanRintekPertek;
use App\Models\RegistrasiUsahaLb3;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public ?string $registrasi_usaha_lb3_id = null;
    public ?string $nama_perusahaan = null;
    public ?string $nama_penanggung_jawab = null;
    public ?string $nomor_nib = null;
    public ?string $npwp = null;
    public ?string $jenis_usaha = null;
    public ?string $alamat_lengkap = null;
    public ?string $nomor_telepon = null;
    public ?string $email = null;
    public ?string $jenis_pengajuan = null;
    public ?string $keterangan_tambahan = null;

    public ?string $surat_permohonan = null;
    public ?string $dplh_ukl_upl = null;
    public ?string $nib = null;
    public ?string $sppl = null;
    public ?string $denah_tps_lb3 = null;
    public ?string $sop_tanggap_darurat = null;

    public ?string $successNumber = null;
    public $isSubmitting = false;

    public function updatedRegistrasiUsahaLb3Id($value): void
    {
        if ($value) {
            $reg = RegistrasiUsahaLb3::find($value);
            if ($reg) {
                $this->nama_perusahaan = $reg->nama_perusahaan;
            }
        }
    }

    public function submit(): void
    {
        $validated = $this->validate((new \App\Http\Requests\StorePengajuanRintekPertekRequest())->rules());

        $ip = request()->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('pengajuan-rintek-pertek:'.$ip, 3)) {
            $this->addError('email', __('Batas maksimal pengajuan tercapai (3 pengajuan per jam). Silakan coba beberapa saat lagi.'));

            return;
        }

        $this->isSubmitting = true;
        \Illuminate\Support\Facades\RateLimiter::hit('pengajuan-rintek-pertek:'.$ip, 3600);

        $paths = [];
        foreach (array_keys(PengajuanRintekPertek::DOKUMEN_FIELDS) as $field) {
            $paths[$field] = $this->{$field}->store('rintek-pertek', 'public');
        }

        $pengajuan = PengajuanRintekPertek::create([
            'registrasi_usaha_lb3_id' => $validated['registrasi_usaha_lb3_id'] ?? null,
            'nama_perusahaan' => $validated['nama_perusahaan'],
            'nama_penanggung_jawab' => $validated['nama_penanggung_jawab'],
            'nomor_nib' => $validated['nomor_nib'],
            'npwp' => $validated['npwp'] ?? null,
            'jenis_usaha' => $validated['jenis_usaha'],
            'alamat_lengkap' => $validated['alamat_lengkap'],
            'nomor_telepon' => $validated['nomor_telepon'],
            'email' => $validated['email'],
            'jenis_pengajuan' => $validated['jenis_pengajuan'],
            'keterangan_tambahan' => $validated['keterangan_tambahan'] ?? null,
            ...$paths,
        ]);

        $this->successNumber = $pengajuan->nomor_pengajuan;
        $this->isSubmitting = false;

        $this->reset([
            'registrasi_usaha_lb3_id', 'nama_perusahaan', 'nama_penanggung_jawab',
            'nomor_nib', 'npwp', 'jenis_usaha', 'alamat_lengkap', 'nomor_telepon',
            'email', 'jenis_pengajuan', 'keterangan_tambahan',
            'surat_permohonan', 'dplh_ukl_upl', 'nib', 'sppl', 'denah_tps_lb3', 'sop_tanggap_darurat',
        ]);
    }

    public function getPerusahaanOptions(): array
    {
        return RegistrasiUsahaLb3::orderBy('nama_perusahaan')
            ->get(['id', 'nomor_registrasi', 'nama_perusahaan'])
            ->mapWithKeys(fn ($r) => [$r->id => $r->nomor_registrasi.' — '.$r->nama_perusahaan])
            ->all();
    }

    public function jenisPengajuanOptions(): array
    {
        return PengajuanRintekPertek::JENIS_PENGAJUAN_OPTIONS;
    }

    public function jenisUsahaOptions(): array
    {
        return [
            'Industri' => __('Industri'),
            'Perdagangan' => __('Perdagangan'),
            'Jasa' => __('Jasa'),
            'Pariwisata' => __('Pariwisata'),
            'Transportasi' => __('Transportasi'),
            'Pertanian/Perkebunan' => __('Pertanian/Perkebunan'),
            'Peternakan' => __('Peternakan'),
            'Perikanan' => __('Perikanan'),
            'Rumah Sakit/Klinik' => __('Rumah Sakit/Klinik'),
            'Laboratorium' => __('Laboratorium'),
            'Pergudangan' => __('Pergudangan'),
            'Lainnya' => __('Lainnya'),
        ];
    }

    public function getDocumentAccept(): string
    {
        return '.pdf,.jpg,.jpeg,.png';
    }
};
?>

<div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm max-w-4xl mx-auto">
    @if ($successNumber)
        <div class="space-y-6 text-center py-8">
            <div class="h-16 w-16 bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto text-3xl font-bold">✓</div>
            <div class="space-y-2">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ __('Pengajuan Berhasil Dikirim') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">{{ __('Simpan nomor pengajuan dan cetak bukti pengajuan untuk referensi Anda.') }}</p>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xs mx-auto">
                <span class="block text-[10px] text-emerald-600 dark:text-emerald-400 font-extrabold tracking-widest uppercase">{{ __('Nomor Pengajuan') }}</span>
                <span class="block text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 select-all tracking-wider">{{ $successNumber }}</span>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                <a href="{{ url('/pengajuan-rintek-pertek/'.$successNumber.'/bukti-pdf') }}"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-emerald-600 text-white hover:bg-emerald-700 h-10 px-4">
                    {{ __('Cetak Bukti Pengajuan') }}
                </a>
                <a href="{{ url('/cek-rintek-pertek') }}"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-slate-200 hover:bg-slate-100 h-10 px-4 dark:border-slate-800 dark:hover:bg-slate-800">
                    {{ __('Cek Status Pengajuan') }}
                </a>
            </div>
        </div>
    @else
        <form wire:submit.prevent="submit" class="space-y-8">
            {{-- Data Perusahaan --}}
            <div class="space-y-5">
                <div class="border-b border-slate-200 dark:border-slate-800 pb-2">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Data Perusahaan') }}</h3>
                    <p class="text-sm text-slate-500">{{ __('Lengkapi informasi perusahaan atau pelaku usaha.') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium">{{ __('Perusahaan Terdaftar LB3') }} <span class="text-slate-400 font-normal">({{ __('opsional') }})</span></label>
                        <select wire:model.live="registrasi_usaha_lb3_id" class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-950">
                            <option value="">{{ __('-- Isi manual / belum terdaftar --') }}</option>
                            @foreach ($this->getPerusahaanOptions() as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label for="nama_perusahaan" class="text-sm font-medium">{{ __('Nama Perusahaan') }} <span class="text-red-500">*</span></label>
                        <input wire:model="nama_perusahaan" id="nama_perusahaan" type="text" placeholder="{{ __('PT Contoh Indonesia') }}"
                            class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500" />
                        @error('nama_perusahaan') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="nama_penanggung_jawab" class="text-sm font-medium">{{ __('Nama Penanggung Jawab') }} <span class="text-red-500">*</span></label>
                        <input wire:model="nama_penanggung_jawab" id="nama_penanggung_jawab" type="text" placeholder="{{ __('Nama lengkap penanggung jawab') }}"
                            class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500" />
                        @error('nama_penanggung_jawab') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="nomor_nib" class="text-sm font-medium">NIB <span class="text-red-500">*</span></label>
                        <input wire:model="nomor_nib" id="nomor_nib" type="text" placeholder="{{ __('Nomor Induk Berusaha') }}"
                            class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500" />
                        @error('nomor_nib') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="npwp" class="text-sm font-medium">NPWP <span class="text-slate-400 font-normal">({{ __('opsional') }})</span></label>
                        <input wire:model="npwp" id="npwp" type="text" placeholder="12.345.678.9-012.345"
                            class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500" />
                        @error('npwp') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="jenis_usaha" class="text-sm font-medium">{{ __('Jenis Usaha') }} <span class="text-red-500">*</span></label>
                        <x-admin.select
                            wire:model="jenis_usaha"
                            id="jenis_usaha"
                            name="jenis_usaha"
                            :options="$this->jenisUsahaOptions()"
                            :searchable="true"
                            placeholder="{{ __('-- Pilih Jenis Usaha --') }}"
                        />
                        @error('jenis_usaha') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label for="alamat_lengkap" class="text-sm font-medium">{{ __('Alamat Lengkap') }} <span class="text-red-500">*</span></label>
                        <textarea wire:model="alamat_lengkap" id="alamat_lengkap" rows="3" placeholder="{{ __('Alamat lengkap perusahaan') }}"
                            class="flex w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"></textarea>
                        @error('alamat_lengkap') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="nomor_telepon" class="text-sm font-medium">{{ __('Nomor Telepon') }} <span class="text-red-500">*</span></label>
                        <input wire:model="nomor_telepon" id="nomor_telepon" type="tel" placeholder="08123456789"
                            class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500" />
                        @error('nomor_telepon') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium">{{ __('Email') }} <span class="text-red-500">*</span></label>
                        <input wire:model="email" id="email" type="email" placeholder="email@perusahaan.com"
                            class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500" />
                        @error('email') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Data Pengajuan --}}
            <div class="space-y-5">
                <div class="border-b border-slate-200 dark:border-slate-800 pb-2">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Data Pengajuan') }}</h3>
                    <p class="text-sm text-slate-500">{{ __('Pilih jenis pengajuan dan unggah dokumen persyaratan.') }}</p>
                </div>

                <div class="space-y-2">
                    <label for="jenis_pengajuan" class="text-sm font-medium">{{ __('Jenis Pengajuan') }} <span class="text-red-500">*</span></label>
                    <x-admin.select
                        wire:model="jenis_pengajuan"
                        id="jenis_pengajuan"
                        name="jenis_pengajuan"
                        :options="$this->jenisPengajuanOptions()"
                        :searchable="false"
                        placeholder="{{ __('-- Pilih Jenis Pengajuan --') }}"
                    />
                    @error('jenis_pengajuan') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @foreach ([
                        'surat_permohonan' => __('Surat Permohonan'),
                        'dplh_ukl_upl' => __('DPLH / UKL-UPL'),
                        'nib' => __('Dokumen NIB'),
                        'sppl' => __('SPPL'),
                        'denah_tps_lb3' => __('Denah TPS LB3'),
                        'sop_tanggap_darurat' => __('SOP Tanggap Darurat'),
                    ] as $field => $label)
                        <div class="space-y-2" x-data="{ dragging: false }">
                            <label for="{{ $field }}" class="text-sm font-medium">{{ $label }} <span class="text-red-500">*</span></label>
                            <div
                                x-on:dragover.prevent="dragging = true"
                                x-on:dragleave.prevent="dragging = false"
                                x-on:drop.prevent="dragging = false"
                                :class="dragging ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-800'"
                                class="relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed px-4 py-6 transition-colors"
                            >
                                <input
                                    wire:model="{{ $field }}"
                                    id="{{ $field }}"
                                    type="file"
                                    accept="{{ $this->getDocumentAccept() }}"
                                    class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                />
                                <div class="pointer-events-none text-center">
                                    @if (${$field})
                                        <div class="text-sm font-medium text-emerald-600 dark:text-emerald-400">
                                            {{ ${$field}->getClientOriginalName() }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ round(${$field}->getSize() / 1024 / 1024, 2) }} MB
                                        </div>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0-3 3m3-3 3 3M6.75 19.5h10.5a2.25 2.25 0 0 0 2.25-2.25v-10.5a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v10.5a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ __('Klik atau seret file ke sini') }}</p>
                                        <p class="text-xs text-slate-400">{{ __('PDF, JPG, JPEG, PNG (max 5MB)') }}</p>
                                    @endif
                                </div>
                            </div>

                            <div wire:loading wire:target="{{ $field }}" class="w-full">
                                <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                    <div class="h-full animate-pulse rounded-full bg-emerald-500" style="width: 100%"></div>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">{{ __('Mengunggah...') }}</p>
                            </div>

                            @error($field) <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @endforeach
                </div>

                <div class="space-y-2">
                    <label for="keterangan_tambahan" class="text-sm font-medium">{{ __('Keterangan Tambahan') }} <span class="text-slate-400 font-normal">({{ __('opsional') }})</span></label>
                    <textarea wire:model="keterangan_tambahan" id="keterangan_tambahan" rows="3" placeholder="{{ __('Tambahkan keterangan jika diperlukan') }}"
                        class="flex w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"></textarea>
                    @error('keterangan_tambahan') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 p-4 text-sm text-slate-600 dark:text-slate-400 space-y-1">
                <p class="font-medium text-slate-900 dark:text-slate-100">{{ __('Ketentuan Upload Dokumen:') }}</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>{!! __('Semua field bertanda (:asterisk:) wajib diisi.', ['asterisk' => '<span class="text-red-500">*</span>']) !!}</li>
                    <li>{{ __('Ukuran file maksimal 5 MB per file.') }}</li>
                    <li>{{ __('Hanya menerima file dengan format PDF, JPG, JPEG, dan PNG.') }}</li>
                    <li>{{ __('Pastikan dokumen yang diunggah dapat dibaca dengan jelas.') }}</li>
                </ul>
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-slate-900 text-white hover:bg-slate-800 h-11 w-full dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-slate-200">
                <span wire:loading wire:target="submit" class="mr-2 inline-block h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
                <span wire:loading wire:target="submit">{{ __('Mengirim...') }}</span>
                <span wire:loading.remove wire:target="submit">{{ __('Kirim Pengajuan') }}</span>
            </button>
        </form>
    @endif
</div>
