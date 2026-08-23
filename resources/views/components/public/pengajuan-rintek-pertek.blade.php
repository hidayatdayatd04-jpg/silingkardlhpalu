<?php

use App\Livewire\Concerns\ThrottlesPublic;
use App\Livewire\Concerns\VerifiesGoogleRecaptcha;
use App\Models\PengajuanRintekPertek;
use App\Models\RegistrasiUsahaLb3;
use App\Services\FileUploadService;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;
    use VerifiesGoogleRecaptcha;
    use ThrottlesPublic;

    public ?string $registrasi_usaha_lb3_id = null;
    public ?string $nama_perusahaan = null;
    public ?string $nama_perusahaan_lainnya = null;
    public ?string $nama_penanggung_jawab = null;
    public ?string $nomor_nib = null;
    public ?string $npwp = null;
    public ?string $jenis_usaha = null;
    public ?string $alamat_lengkap = null;
    public ?string $nomor_telepon = null;
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
        if ($value && $value !== '__lainnya__' && is_numeric($value)) {
            $reg = RegistrasiUsahaLb3::find($value);
            if ($reg) {
                $this->nama_perusahaan = $reg->nama_perusahaan;
            }
        } elseif ($value === '__lainnya__') {
            $this->nama_perusahaan = null;
        } else {
            $this->nama_perusahaan = null;
        }
    }

    public function submit(): void
    {
        if (! $this->verifyCaptcha('submit')) {
            return;
        }

        $this->resetCaptcha();

        $validated = $this->validate((new \App\Http\Requests\StorePengajuanRintekPertekRequest())->rules());

        if ($this->hitRateLimit('pengajuan-rintek-pertek', 10, 'form', __('Pengajuan dibatasi maksimal 10 per jam.'))) {
            return;
        }

        $this->isSubmitting = true;

        $paths = [];
        $fileService = app(FileUploadService::class);
        foreach (array_keys(PengajuanRintekPertek::DOKUMEN_FIELDS) as $field) {
            // Gambar raster otomatis dikompres & dikonversi ke WebP; PDF disimpan apa adanya.
                $paths[$field] = $fileService->store($this->{$field}, 'pengajuan-rintek-pertek', 'public') ?: null;
        }

        $registrasiId = $validated['registrasi_usaha_lb3_id'] ?? null;
        $namaPerusahaan = $validated['nama_perusahaan'] ?? null;
        if ($registrasiId === '__lainnya__' || empty($registrasiId)) {
            $registrasiId = null;
            $namaPerusahaan = trim($validated['nama_perusahaan_lainnya'] ?? $namaPerusahaan ?? '');
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
            'jenis_pengajuan' => $validated['jenis_pengajuan'],
            'keterangan_tambahan' => $validated['keterangan_tambahan'] ?? null,
            ...$paths,
        ]);

        $this->successNumber = $pengajuan->nomor_pengajuan;
        $this->isSubmitting = false;

        $this->reset([
            'registrasi_usaha_lb3_id', 'nama_perusahaan', 'nama_perusahaan_lainnya', 'nama_penanggung_jawab',
            'nomor_nib', 'npwp', 'jenis_usaha', 'alamat_lengkap', 'nomor_telepon',
            'jenis_pengajuan', 'keterangan_tambahan',
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
        return '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif';
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
        <form wire:submit.prevent="submit" @if(\App\Support\Captcha::enabled()) data-dlh-recaptcha-action="submit" @endif class="space-y-8">
            {{-- Section: Data Perusahaan --}}
            <section class="space-y-5">
                <header class="fi-section-head">
                    <span class="fi-section-icon">
                        <x-icons.ui name="building" />
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
                            placeholder="{{ __('Pilih perusahaan / Lainnya') }}"
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
                                icon="building"
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
                                icon="building"
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
                        icon="user"
                    />

                    <x-public.input
                        wire:model="nomor_nib"
                        name="nomor_nib"
                        label="NIB"
                        placeholder="{{ __('Nomor Induk Berusaha') }}"
                        required
                        hint="{{ __('Nomor Induk Berusaha dari sistem OSS.') }}"
                        icon="id-card"
                    />

                    <x-public.input
                        wire:model="npwp"
                        name="npwp"
                        label="NPWP"
                        placeholder="12.345.678.9-012.345"
                        hint="{{ __('Opsional — Nomor Pokok Wajib Pajak perusahaan.') }}"
                        icon="id-card"
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
                            icon="map-pin"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <x-public.input
                            wire:model="nomor_telepon"
                            name="nomor_telepon"
                            type="tel"
                            label="{{ __('Nomor Telepon') }}"
                            placeholder="08123456789"
                            required
                            hint="{{ __('Nomor yang aktif dan bisa dihubungi (WhatsApp).') }}"
                            icon="phone"
                        />
                    </div>
                </div>
            </section>

            {{-- Section: Data Pengajuan --}}
            <section class="space-y-5">
                <header class="fi-section-head">
                    <span class="fi-section-icon">
                        <x-icons.ui name="document" />
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
                                <span class="fi-icon-badge"><x-icons.ui name="document" /></span>
                                {{ $label }} <span class="fi-required">*</span>
                            </label>
                            <div
                                x-on:dragover.prevent="dragging = true"
                                x-on:dragleave.prevent="dragging = false"
                                x-on:drop.prevent="dragging = false"
                                x-on:change.capture="dlhFileGuard($event, { label: '{{ $label }}', exts: ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png','webp','avif','heic','heif'], maxSizeMB: 5 })"
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
                                            <x-icons.ui name="check" />
                                        </span>
                                        <div class="fi-file-meta">
                                            <span class="fi-file-name">{{ ${$field}->getClientOriginalName() }}</span>
                                            <span class="fi-file-size">{{ round(${$field}->getSize() / 1024 / 1024, 2) }} MB</span>
                                        </div>
                                    @else
                                        <span class="fi-file-upload-icon">
                                            <x-icons.ui name="upload" />
                                        </span>
                                        <div class="fi-file-meta">
                                            <span class="fi-file-hint">{{ __('Klik atau seret file ke sini') }}</span>
                                            <span class="fi-file-size">{{ __('PDF, Word, Excel, JPG, PNG, WEBP, AVIF, HEIC • max 5MB') }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div wire:loading wire:target="{{ $field }}" class="fi-file-loading">
                                    <div class="fi-file-progress"><div></div></div>
                                    <span>{{ __('Mengunggah...') }}</span>
                                </div>
                            </div>

                            @error($field) <p class="fi-error"><x-icons.ui name="alert" />{{ $message }}</p> @enderror
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
                    icon="message"
                />
            </section>

            {{-- Info box --}}
            <div class="fi-info-box">
                <span class="fi-info-title">
                    <x-icons.ui name="alert" />
                    {{ __('Ketentuan Upload Dokumen') }}
                </span>
                <ul>
                    <li>{!! __('Semua field bertanda (:asterisk:) wajib diisi.', ['asterisk' => '<span class="fi-required">*</span>']) !!}</li>
                    <li>{{ __('Ukuran file maksimal 5 MB per file.') }}</li>
                    <li>{{ __('Hanya menerima file dengan format PDF, Word (DOC/DOCX), Excel (XLS/XLSX), JPG, JPEG, PNG, WEBP, AVIF, HEIC, dan HEIF.') }}</li>
                    <li>{{ __('Pastikan dokumen yang diunggah dapat dibaca dengan jelas.') }}</li>
                </ul>
            </div>

            @error('form')
                <div class="dlh-limit-alert" role="alert">
                    <x-icons.ui name="alert" />
                    <span>{{ $message }}</span>
                </div>
            @enderror

        <x-google-recaptcha />

            <button type="submit" class="fi-submit-btn">
                {{ __('Kirim Pengajuan') }}
                <x-icons.ui name="arrow-right" class="ml-2 h-4 w-4" />
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .fi-submit-btn:hover { transform: translateY(-1px); box-shadow: 0 14px 28px -8px rgba(20, 106, 68, 0.6); }
        .fi-submit-btn:active { transform: translateY(0); }
        .fi-submit-btn:disabled { opacity: 0.7; cursor: wait; }

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
