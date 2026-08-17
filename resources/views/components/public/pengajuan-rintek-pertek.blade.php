<?php

use App\Models\PengajuanRintekPertek;
use App\Models\RegistrasiUsahaLb3;
use App\Services\FileUploadService;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public ?string $registrasi_usaha_lb3_id = null;
    public ?string $nama_perusahaan = null;
    public ?string $nama_perusahaan_lainnya = null;
    public ?string $nama_penanggung_jawab = null;
    public ?string $nomor_nib = null;
    public ?string $npwp = null;
    public ?string $jenis_usaha = null;
    public ?string $alamat_lengkap = null;
    public ?string $nomor_telepon = null;
    public ?string $email = null;
    public ?string $jenis_pengajuan = null;
    public ?string $keterangan_tambahan = null;

    // Properti file upload harus bertipe TemporaryUploadedFile agar Livewire
    // dapat meng-hydrate file sementara (tipe string membuat upload gagal 419).
    public ?TemporaryUploadedFile $surat_permohonan = null;
    public ?TemporaryUploadedFile $dplh_ukl_upl = null;
    public ?TemporaryUploadedFile $nib = null;
    public ?TemporaryUploadedFile $sppl = null;
    public ?TemporaryUploadedFile $denah_tps_lb3 = null;
    public ?TemporaryUploadedFile $sop_tanggap_darurat = null;

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
        $fileService = app(FileUploadService::class);
        foreach (array_keys(PengajuanRintekPertek::DOKUMEN_FIELDS) as $field) {
            // Gambar raster otomatis dikompres & dikonversi ke WebP; PDF disimpan apa adanya.
            $paths[$field] = $fileService->store($this->{$field}, 'rintek-pertek', 'public') ?: null;
        }

        $registrasiId = $validated['registrasi_usaha_lb3_id'] ?? null;
        $namaPerusahaan = $validated['nama_perusahaan'];
        if ($registrasiId === '__lainnya__') {
            $registrasiId = null;
            $namaPerusahaan = trim($validated['nama_perusahaan_lainnya'] ?? '');
        }

        $pengajuan = PengajuanRintekPertek::create([
            'registrasi_usaha_lb3_id' => $registrasiId,
            'nama_perusahaan' => $namaPerusahaan,
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
            'registrasi_usaha_lb3_id', 'nama_perusahaan', 'nama_perusahaan_lainnya', 'nama_penanggung_jawab',
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
            ->put('__lainnya__', 'Lainnya')
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
        return '.pdf,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif';
    }
};
?>

<div class="fi-form-card bg-white dark:bg-slate-950 rounded-2xl p-6 md:p-8 shadow-[0_1px_3px_rgba(13,43,29,0.06),0_12px_32px_-12px_rgba(13,43,29,0.10)] max-w-4xl mx-auto">
    @if ($successNumber)
        <div class="space-y-6 text-center py-8">
            <div class="h-16 w-16 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-full flex items-center justify-center mx-auto"><x-icons.berhasil class="size-8" /></div>
            <div class="space-y-2">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ __('Pengajuan Berhasil Dikirim') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">{{ __('Simpan nomor pengajuan dan cetak bukti pengajuan untuk referensi Anda.') }}</p>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xs mx-auto">
                <span class="block text-[10px] text-brand-600 dark:text-brand-400 font-bold tracking-widest uppercase">{{ __('Nomor Pengajuan') }}</span>
                <span class="block text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 select-all tracking-wider">{{ $successNumber }}</span>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                <a href="{{ url('/pengajuan-rintek-pertek/'.$successNumber.'/bukti-pdf') }}"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-brand-600 text-white hover:bg-brand-700 h-10 px-4">
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
            {{-- Section: Data Perusahaan --}}
            <section class="space-y-5">
                <header class="fi-section-head">
                    <span class="fi-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
                    </span>
                    <div>
                        <h3 class="fi-section-title">{{ __('Data Perusahaan') }}</h3>
                        <p class="fi-section-sub">{{ __('Lengkapi informasi perusahaan atau pelaku usaha.') }}</p>
                    </div>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <x-public.select
                            wire:model.live="registrasi_usaha_lb3_id"
                            name="registrasi_usaha_lb3_id"
                            label="{{ __('Perusahaan Terdaftar LB3') }}"
                            :options="$this->getPerusahaanOptions()"
                            :searchable="true"
                            placeholder="{{ __('-- Pilih perusahaan terdaftar LB3 / Lainnya --') }}"
                            hint="{{ __('Pilih perusahaan jika sudah terdaftar LB3. Jika belum terdaftar, pilih “Lainnya” lalu tulis nama perusahaan secara manual.') }}"
                        />
                        @if($registrasi_usaha_lb3_id === '__lainnya__')
                            <x-public.input
                                wire:model="nama_perusahaan_lainnya"
                                name="nama_perusahaan_lainnya"
                                label="{{ __('Nama Perusahaan (Lainnya)') }}"
                                placeholder="{{ __('PT Contoh Indonesia') }}"
                                required
                                hint="{{ __('Tulis nama lengkap badan usaha secara manual karena belum terdaftar LB3.') }}"
                                icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>'
                            />
                        @endif
                    </div>

                    @if($registrasi_usaha_lb3_id !== '__lainnya__')
                        <div class="md:col-span-2">
                            <x-public.input
                                wire:model="nama_perusahaan"
                                name="nama_perusahaan"
                                label="{{ __('Nama Perusahaan') }}"
                                placeholder="{{ __('PT Contoh Indonesia') }}"
                                required
                                hint="{{ __('Nama lengkap badan usaha sesuai izin/akta pendirian.') }}"
                                icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>'
                            />
                        </div>
                    @endif

                    <x-public.input
                        wire:model="nama_penanggung_jawab"
                        name="nama_penanggung_jawab"
                        label="{{ __('Nama Penanggung Jawab') }}"
                        placeholder="{{ __('Nama lengkap penanggung jawab') }}"
                        required
                        hint="{{ __('Orang yang bertanggung jawab atas pengelolaan LB3 di perusahaan.') }}"
                        icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'
                    />

                    <x-public.input
                        wire:model="nomor_nib"
                        name="nomor_nib"
                        label="NIB"
                        placeholder="{{ __('Nomor Induk Berusaha') }}"
                        required
                        hint="{{ __('Nomor Induk Berusaha dari sistem OSS.') }}"
                        icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 9h6M9 13h6M9 17h3"/></svg>'
                    />

                    <x-public.input
                        wire:model="npwp"
                        name="npwp"
                        label="NPWP"
                        placeholder="12.345.678.9-012.345"
                        hint="{{ __('Opsional — Nomor Pokok Wajib Pajak perusahaan.') }}"
                        icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 9h6M9 13h6M9 17h3"/></svg>'
                    />

                    <x-public.select
                        wire:model="jenis_usaha"
                        name="jenis_usaha"
                        label="{{ __('Jenis Usaha') }}"
                        :options="$this->jenisUsahaOptions()"
                        :searchable="true"
                        placeholder="{{ __('-- Pilih Jenis Usaha --') }}"
                        required
                        hint="{{ __('Pilih kategori yang paling sesuai dengan kegiatan usaha Anda.') }}"
                    />

                    <div class="md:col-span-2">
                        <x-public.textarea
                            wire:model="alamat_lengkap"
                            name="alamat_lengkap"
                            label="{{ __('Alamat Lengkap') }}"
                            placeholder="{{ __('Alamat lengkap perusahaan, sertakan patokan terdekat') }}"
                            rows="3"
                            required
                            hint="{{ __('Sertakan patokan/landmark terdekat agar lokasi mudah ditemukan petugas.') }}"
                            icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 8.5c0 5.5-8.4 11.5-8.4 11.5S3.6 14 3.6 8.5a8.4 8.4 0 1 1 16.8 0Z"/><circle cx="12" cy="8.5" r="2.6"/></svg>'
                        />
                    </div>

                    <x-public.input
                        wire:model="nomor_telepon"
                        name="nomor_telepon"
                        type="tel"
                        label="{{ __('Nomor Telepon') }}"
                        placeholder="08123456789"
                        required
                        hint="{{ __('Nomor yang aktif dan bisa dihubungi (WhatsApp).') }}"
                        icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>'
                    />

                    <x-public.input
                        wire:model="email"
                        name="email"
                        type="email"
                        label="{{ __('Email') }}"
                        placeholder="email@perusahaan.com"
                        required
                        hint="{{ __('Email aktif untuk menerima nomor pengajuan & informasi status.') }}"
                        icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg>'
                    />
                </div>
            </section>

            {{-- Section: Data Pengajuan --}}
            <section class="space-y-5">
                <header class="fi-section-head">
                    <span class="fi-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M9 13h6M9 17h3"/></svg>
                    </span>
                    <div>
                        <h3 class="fi-section-title">{{ __('Data Pengajuan') }}</h3>
                        <p class="fi-section-sub">{{ __('Pilih jenis pengajuan dan unggah dokumen persyaratan.') }}</p>
                    </div>
                </header>

                <x-public.select
                    wire:model="jenis_pengajuan"
                    name="jenis_pengajuan"
                    label="{{ __('Jenis Pengajuan') }}"
                    :options="$this->jenisPengajuanOptions()"
                    :searchable="false"
                    placeholder="{{ __('-- Pilih Jenis Pengajuan --') }}"
                    required
                    hint="{{ __('Pilih jenis layanan yang diajukan. Pilih “Lainnya” bila tidak tersedia di daftar.') }}"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ([
                        'surat_permohonan' => __('Surat Permohonan'),
                        'dplh_ukl_upl' => __('DPLH / UKL-UPL'),
                        'nib' => __('Dokumen NIB'),
                        'sppl' => __('SPPL'),
                        'denah_tps_lb3' => __('Denah TPS LB3'),
                        'sop_tanggap_darurat' => __('SOP Tanggap Darurat'),
                    ] as $field => $label)
                        <div class="fi-field" x-data="{ dragging: false }">
                            <label class="fi-label">
                                <span class="fi-icon-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg></span>
                                {{ $label }} <span class="fi-required">*</span>
                            </label>
                            <div
                                x-on:dragover.prevent="dragging = true"
                                x-on:dragleave.prevent="dragging = false"
                                x-on:drop.prevent="dragging = false"
                                :class="dragging ? 'fi-file-drop--drag' : ''"
                                class="fi-file-drop fi-file-drop--col"
                            >
                                <input
                                    wire:model="{{ $field }}"
                                    id="{{ $field }}"
                                    type="file"
                                    accept="{{ $this->getDocumentAccept() }}"
                                    class="fi-file-overlay"
                                    x-on:change="$el.files.length ? $el.parentElement.setAttribute('data-file', $el.files[0].name) : $el.parentElement.removeAttribute('data-file')"
                                />
                                <div class="fi-file-content">
                                    @if (${$field})
                                        <span class="fi-file-done">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                        </span>
                                        <div class="fi-file-meta">
                                            <span class="fi-file-name">{{ ${$field}->getClientOriginalName() }}</span>
                                            <span class="fi-file-size">{{ round(${$field}->getSize() / 1024 / 1024, 2) }} MB</span>
                                        </div>
                                    @else
                                        <span class="fi-file-upload-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16.5V9.75m0 0-3 3m3-3 3 3M6.75 19.5h10.5a2.25 2.25 0 0 0 2.25-2.25v-10.5a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v10.5a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                                        </span>
                                        <div class="fi-file-meta">
                                            <span class="fi-file-hint">{{ __('Klik atau seret file ke sini') }}</span>
                                            <span class="fi-file-size">{{ __('PDF, JPG, PNG, WEBP, AVIF, HEIC • max 5MB') }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div wire:loading wire:target="{{ $field }}" class="fi-file-loading">
                                    <div class="fi-file-progress"><div></div></div>
                                    <span>{{ __('Mengunggah...') }}</span>
                                </div>
                            </div>

                            @error($field) <p class="fi-error"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>

                <x-public.textarea
                    wire:model="keterangan_tambahan"
                    name="keterangan_tambahan"
                    label="{{ __('Keterangan Tambahan') }}"
                    placeholder="{{ __('Tambahkan keterangan jika diperlukan') }}"
                    rows="3"
                    hint="{{ __('Opsional — tambahkan informasi bila diperlukan.') }}"
                    icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M4 12h16M4 19h10"/></svg>'
                />
            </section>

            {{-- Info box --}}
            <div class="fi-info-box">
                <span class="fi-info-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 16v-5M12 8h.01"/></svg>
                    {{ __('Ketentuan Upload Dokumen') }}
                </span>
                <ul>
                    <li>{!! __('Semua field bertanda (:asterisk:) wajib diisi.', ['asterisk' => '<span class="fi-required">*</span>']) !!}</li>
                    <li>{{ __('Ukuran file maksimal 5 MB per file.') }}</li>
                    <li>{{ __('Hanya menerima file dengan format PDF, JPG, JPEG, PNG, WEBP, AVIF, HEIC, dan HEIF.') }}</li>
                    <li>{{ __('Pastikan dokumen yang diunggah dapat dibaca dengan jelas.') }}</li>
                </ul>
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="submit" class="fi-submit-btn">
                <span wire:loading.remove wire:target="submit" class="inline-flex items-center justify-center gap-2">
                    {{ __('Kirim Pengajuan') }}
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 14 0M12 5l7 7-7 7"/></svg>
                </span>
                <span wire:loading wire:target="submit" class="inline-flex items-center justify-center gap-2">
                    <span class="fi-spinner"></span>
                    {{ __('Mengirim...') }}
                </span>
            </button>
        </form>
    @endif

    <style>

        .fi-form-card { font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif; }

        /* ── Section header ── */
        .fi-section-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e8efe9;
        }
        .fi-section-icon {
            flex-shrink: 0;
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: linear-gradient(135deg, #178a53, #146a44);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px -2px rgba(20, 106, 68, 0.35);
        }
        .fi-section-icon svg { width: 18px; height: 18px; }
        .fi-section-title {
            font-size: 16px;
            font-weight: 700;
            color: #12201a;
            line-height: 1.2;
        }
        .fi-section-sub {
            font-size: 12.5px;
            color: #5b6b63;
            margin-top: 2px;
        }

        /* ── Field ── */
        .fi-field { position: relative; }
        .fi-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #12201a;
            margin-bottom: 8px;
        }
        .fi-required { color: #f43f5e; font-size: 14px; font-weight: 400; margin-left: 1px; }
        .fi-icon-badge {
            width: 26px; height: 26px; border-radius: 8px;
            background: #e6f5ec; color: #146a44;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .fi-icon-badge svg { width: 15px; height: 15px; }

        /* ── File drop (vertical) ── */
        .fi-file-drop {
            position: relative;
            border: 1.5px dashed #a9dcc0;
            border-radius: 16px;
            background: #f4faf6;
            padding: 18px 16px;
            transition: border-color .18s ease, background .18s ease;
        }
        .fi-file-drop--col { display: flex; align-items: center; justify-content: center; min-height: 110px; }
        .fi-file-drop:hover, .fi-file-drop--drag {
            background: #eefaf3;
            border-color: #1ea567;
        }
        .fi-file-overlay {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            opacity: 0;
        }
        .fi-file-content {
            display: flex;
            align-items: center;
            gap: 12px;
            pointer-events: none;
            text-align: left;
        }
        .fi-file-upload-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: #fff;
            color: #5b6b63;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .fi-file-upload-icon svg { width: 18px; height: 18px; }
        .fi-file-done {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #178a53, #146a44);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 10px -2px rgba(20, 106, 68, 0.4);
        }
        .fi-file-done svg { width: 18px; height: 18px; }
        .fi-file-meta { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
        .fi-file-name { font-size: 13px; font-weight: 600; color: #12201a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 220px; }
        .fi-file-hint { font-size: 13px; font-weight: 600; color: #5b6b63; }
        .fi-file-size { font-size: 11.5px; color: #5f7268; }

        .fi-file-loading {
            position: absolute;
            inset: 0;
            background: rgba(244, 250, 246, 0.92);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 12.5px;
            color: #146a44;
            font-weight: 600;
        }
        .fi-file-progress { width: 80px; height: 4px; background: #d1e7da; border-radius: 9999px; overflow: hidden; }
        .fi-file-progress > div { height: 100%; width: 40%; background: #1ea567; border-radius: 9999px; animation: fi-slide 1.2s ease-in-out infinite; }
        @keyframes fi-slide { 0% { transform: translateX(-100%); } 100% { transform: translateX(350%); } }

        /* ── Error ── */
        .fi-error {
            display: flex; align-items: center; gap: 5px;
            margin-top: 6px; font-size: 11.5px; font-weight: 500; color: #e0533d;
        }
        .fi-error svg { width: 13px; height: 13px; flex-shrink: 0; }

        /* ── Info box ── */
        .fi-info-box {
            background: #f4faf6;
            border: 1px solid #d1e7da;
            border-left: 3px solid #1ea567;
            border-radius: 14px;
            padding: 16px 18px;
        }
        .fi-info-title {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13.5px; font-weight: 700; color: #146a44; margin-bottom: 8px;
        }
        .fi-info-title svg { width: 16px; height: 16px; }
        .fi-info-box ul { list-style: disc; padding-left: 22px; font-size: 12.5px; color: #5b6b63; line-height: 1.7; }

        /* ── Submit Button ── */
        .fi-submit-btn {
            width: 100%; height: 52px; border: none; border-radius: 9999px;
            background: linear-gradient(180deg, #178a53, #146a44); color: #fff;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            font-size: 15px; font-weight: 700; letter-spacing: .2px; cursor: pointer;
            box-shadow: 0 10px 24px -8px rgba(20, 106, 68, 0.55);
            transition: transform .12s ease, box-shadow .12s ease;
        }
        .fi-submit-btn:hover { transform: translateY(-1px); box-shadow: 0 14px 28px -8px rgba(20, 106, 68, 0.6); }
        .fi-submit-btn:active { transform: translateY(0); }
        .fi-submit-btn:disabled { opacity: 0.7; cursor: wait; }

        .fi-spinner {
            width: 16px; height: 16px; border: 2px solid currentColor; border-top-color: transparent;
            border-radius: 9999px; display: inline-block; animation: fi-spin 0.8s linear infinite;
        }
        @keyframes fi-spin { to { transform: rotate(360deg); } }

        /* ── Dark mode ── */
        .dark .fi-section-head { border-color: #334155; }
        .dark .fi-section-title { color: #e2e8f0; }
        .dark .fi-section-sub { color: #94a3b8; }
        .dark .fi-label { color: #e2e8f0; }
        .dark .fi-icon-badge { background: rgba(30,165,103,0.15); color: #1ea567; }
        .dark .fi-file-drop { background: #0f172a; border-color: #334155; }
        .dark .fi-file-drop:hover, .dark .fi-file-drop--drag { background: #1e293b; border-color: #1ea567; }
        .dark .fi-file-upload-icon { background: #1e293b; color: #94a3b8; }
        .dark .fi-file-name { color: #e2e8f0; }
        .dark .fi-file-hint { color: #94a3b8; }
        .dark .fi-file-size { color: #64748b; }
        .dark .fi-file-loading { background: rgba(15,23,42,0.92); color: #6ee7b7; }
        .dark .fi-info-box { background: rgba(30,165,103,0.08); border-color: rgba(30,165,103,0.25); }
        .dark .fi-info-title { color: #6ee7b7; }
        .dark .fi-info-box ul { color: #94a3b8; }
        .dark .fi-submit-btn {
            background: linear-gradient(180deg, #1ea567, #178a53);
            box-shadow: 0 10px 24px -8px rgba(30, 165, 103, 0.5);
        }
    </style>
</div>
