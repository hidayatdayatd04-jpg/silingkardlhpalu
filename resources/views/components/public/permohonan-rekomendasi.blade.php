<?php

use App\Enums\JenisPengaduanPengendalian;
use App\Http\Requests\StorePermohonanRekomendasiRequest;
use App\Http\Requests\StorePermohonanRekomendasiStep1Request;
use App\Models\PermohonanDokumen;
use App\Models\PermohonanRekomendasi;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public int $step = 1;

    public ?string $nama_perusahaan = null;
    public ?string $nama_pemilik = null;
    public ?string $npwp = null;
    public ?string $jenis_usaha = null;
    public ?string $jenis_usaha_lainnya = null;
    public ?string $alamat_lengkap = null;
    public ?string $nomor_telepon = null;
    public ?string $email = null;

    public ?string $jenis_pengajuan = null;
    public ?string $surat_permohonan = null;
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
        $validated = $this->validate((new StorePermohonanRekomendasiRequest())->rules());

        $ip = request()->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('permohonan-rekomendasi:'.$ip, 3)) {
            $this->addError('email', __('Batas maksimal pengajuan tercapai (3 pengajuan per jam). Silakan coba beberapa saat lagi.'));

            return;
        }

        \Illuminate\Support\Facades\RateLimiter::hit('permohonan-rekomendasi:'.$ip, 3600);

        $suratPath = $this->surat_permohonan->store('permohonan-rekomendasi/surat', 'public');

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
            'jenis_pengajuan' => $validated['jenis_pengajuan'],
            'surat_permohonan' => $suratPath,
        ]);

        foreach ($this->dokumen_pendukung as $index => $file) {
            $path = $file->store('permohonan-rekomendasi/dokumen', 'public');
            PermohonanDokumen::create([
                'permohonan_rekomendasi_id' => $permohonan->id,
                'path_dokumen' => $path,
                'nama_dokumen' => __('Dokumen Pendukung ') . ($index + 1),
            ]);
        }

        $this->successTicket = $permohonan->nomor_tiket;
        $this->reset([
            'step', 'nama_perusahaan', 'nama_pemilik', 'npwp', 'jenis_usaha', 'jenis_usaha_lainnya',
            'alamat_lengkap', 'nomor_telepon', 'email', 'jenis_pengajuan',
            'surat_permohonan', 'dokumen_pendukung',
        ]);
        $this->step = 1;
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

    public function jenisPengajuanOptions(): array
    {
        return JenisPengaduanPengendalian::options();
    }
};
?>

<div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm max-w-3xl mx-auto">
    @if ($successTicket)
        <div class="space-y-6 text-center py-8">
            <div class="h-16 w-16 bg-brand-100 dark:bg-brand-900/30 text-brand-600 rounded-full flex items-center justify-center mx-auto text-3xl font-bold">✓</div>
            <h3 class="text-2xl font-bold">{{ __('Permohonan Berhasil Diajukan') }}</h3>
            <p class="text-sm text-slate-500 max-w-md mx-auto">{{ __('Simpan nomor tiket dan unduh bukti pengajuan untuk referensi Anda.') }}</p>
            <div class="p-4 bg-slate-50 dark:bg-slate-900 border rounded-lg max-w-xs mx-auto">
                <span class="block text-[10px] text-brand-600 font-extrabold tracking-widest uppercase">{{ __('Nomor Tiket') }}</span>
                <span class="block text-2xl font-bold font-mono mt-1 select-all">{{ $successTicket }}</span>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                <a href="{{ url('/permohonan-rekomendasi/'.$successTicket.'/bukti-pdf') }}"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-brand-600 text-white h-10 px-4 hover:bg-brand-700">
                    {{ __('Unduh Bukti PDF') }}
                </a>
                <a href="{{ url('/cek-permohonan-rekomendasi') }}"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-slate-200 h-10 px-4 dark:border-slate-800">
                    {{ __('Cek Status Permohonan') }}
                </a>
            </div>
        </div>
    @else
        <div class="mb-8">
            <div class="flex items-center justify-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="h-8 w-8 rounded-full flex items-center justify-center text-sm font-bold {{ $step >= 1 ? 'bg-brand-500 text-white' : 'bg-slate-200 text-slate-500' }}">1</span>
                    <span class="text-sm font-medium">{{ __('Data Perusahaan') }}</span>
                </div>
                <div class="h-px w-12 bg-slate-200 dark:bg-slate-700"></div>
                <div class="flex items-center gap-2">
                    <span class="h-8 w-8 rounded-full flex items-center justify-center text-sm font-bold {{ $step >= 2 ? 'bg-brand-500 text-white' : 'bg-slate-200 text-slate-500' }}">2</span>
                    <span class="text-sm font-medium">{{ __('Data Pengajuan') }}</span>
                </div>
            </div>
        </div>

        @if ($step === 1)
            <form wire:submit.prevent="nextStep" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium">{{ __('Nama Perusahaan') }}</label>
                        <input wire:model="nama_perusahaan" type="text" class="flex h-10 w-full rounded-md border border-slate-200 px-3 text-sm dark:border-slate-800" />
                        @error('nama_perusahaan') <span class="text-[0.8rem] text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">{{ __('Nama Pemilik/Penanggung Jawab') }}</label>
                        <input wire:model="nama_pemilik" type="text" class="flex h-10 w-full rounded-md border border-slate-200 px-3 text-sm dark:border-slate-800" />
                        @error('nama_pemilik') <span class="text-[0.8rem] text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">{{ __('NPWP') }}</label>
                        <input wire:model="npwp" type="text" placeholder="12.345.678.9-012.345" class="flex h-10 w-full rounded-md border border-slate-200 px-3 text-sm dark:border-slate-800" />
                        @error('npwp') <span class="text-[0.8rem] text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">{{ __('Jenis Usaha') }}</label>
                        <x-admin.select
                            wire:model.live="jenis_usaha"
                            name="jenis_usaha"
                            :options="$this->jenisUsahaOptions()"
                            :searchable="true"
                            placeholder="{{ __('-- Pilih Jenis Usaha --') }}"
                        />
                        @error('jenis_usaha') <span class="text-[0.8rem] text-red-500">{{ $message }}</span> @enderror
                    </div>
                    @if ($jenis_usaha === 'Lainnya')
                        <div class="space-y-2">
                            <label class="text-sm font-medium">{{ __('Nama Jenis Usaha') }}</label>
                            <input wire:model="jenis_usaha_lainnya" type="text" placeholder="{{ __('Contoh: Laundry Kiloan') }}" class="flex h-10 w-full rounded-md border border-slate-200 px-3 text-sm dark:border-slate-800" />
                            @error('jenis_usaha_lainnya') <span class="text-[0.8rem] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @endif
                    <div class="space-y-2">
                        <label class="text-sm font-medium">{{ __('Nomor Telepon') }}</label>
                        <input wire:model="nomor_telepon" type="tel" class="flex h-10 w-full rounded-md border border-slate-200 px-3 text-sm dark:border-slate-800" />
                        @error('nomor_telepon') <span class="text-[0.8rem] text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium">{{ __('Email') }} <span class="text-red-500">*</span></label>
                        <input wire:model="email" type="email" placeholder="{{ __('contoh@email.com') }}" class="flex h-10 w-full rounded-md border border-slate-200 px-3 text-sm dark:border-slate-800" required />
                        @error('email') <span class="text-[0.8rem] text-red-500">{{ $message }}</span> @enderror
                        <p class="text-xs text-slate-500">{{ __('Email untuk menerima notifikasi update status permohonan') }}</p>
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium">{{ __('Alamat Lengkap') }}</label>
                        <textarea wire:model="alamat_lengkap" rows="3" class="flex w-full rounded-md border border-slate-200 px-3 py-2 text-sm dark:border-slate-800"></textarea>
                        @error('alamat_lengkap') <span class="text-[0.8rem] text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
                <button type="submit" class="w-full h-10 rounded-md bg-slate-900 text-white text-sm font-medium dark:bg-slate-50 dark:text-slate-900 mt-4">{{ __('Lanjut ke Step 2') }}</button>
            </form>
        @else
            <form wire:submit.prevent="submit" class="space-y-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium">{{ __('Jenis Pengajuan') }}</label>
                    <x-admin.select
                        wire:model="jenis_pengajuan"
                        name="jenis_pengajuan"
                        :options="$this->jenisPengajuanOptions()"
                        :searchable="false"
                        placeholder="{{ __('-- Pilih Jenis Pengajuan --') }}"
                    />
                    @error('jenis_pengajuan') <span class="text-[0.8rem] text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">{{ __('Surat Permohonan (PDF, max 5MB)') }}</label>
                    <input wire:model="surat_permohonan" type="file" accept="application/pdf" class="flex h-10 w-full rounded-md border border-slate-200 px-3 text-sm dark:border-slate-800" />
                    @error('surat_permohonan') <span class="text-[0.8rem] text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">{{ __('Dokumen Pendukung (PDF/Gambar, min 1, max 5MB/file)') }}</label>
                    <input wire:model="dokumen_pendukung" type="file" multiple accept="application/pdf,image/jpeg,image/png,image/jpg" class="flex h-10 w-full rounded-md border border-slate-200 px-3 text-sm dark:border-slate-800" />
                    @error('dokumen_pendukung') <span class="text-[0.8rem] text-red-500">{{ $message }}</span> @enderror
                    @error('dokumen_pendukung.*') <span class="text-[0.8rem] text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" wire:click="previousStep" class="flex-1 h-10 rounded-md border border-slate-200 text-sm font-medium dark:border-slate-800">{{ __('Kembali') }}</button>
                    <button type="submit" class="flex-1 h-10 rounded-md bg-slate-900 text-white text-sm font-medium dark:bg-slate-50 dark:text-slate-900">{{ __('Kirim Permohonan') }}</button>
                </div>
            </form>
        @endif
    @endif
</div>
