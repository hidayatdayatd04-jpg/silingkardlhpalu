<?php

use App\Enums\StatusPengaduanRth;
use App\Http\Requests\StorePermohonanPinjamTamanRequest;
use App\Livewire\Concerns\ThrottlesPublic;
use App\Livewire\Concerns\VerifiesGoogleRecaptcha;
use App\Models\PermohonanPinjamTaman;
use App\Services\FileUploadService;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;
    use VerifiesGoogleRecaptcha;
    use ThrottlesPublic;

    public string $nama_pemohon = '';
    public string $nomor_hp = '';
    public string $email = '';
    public string $nama_kegiatan = '';
    public string $nama_taman = '';
    public string $nama_taman_manual = '';
    public string $tanggal_kegiatan = '';
    public string $tanggal_selesai = '';
    // Properti file upload harus bertipe TemporaryUploadedFile agar Livewire
    // dapat meng-hydrate file sementara (tipe string membuat upload gagal 419).
    public ?TemporaryUploadedFile $surat_permohonan = null;
    public $jaminan_kebersihan = false;
    public ?TemporaryUploadedFile $surat_jaminan = null;
    public ?string $successTicket = null;
    public $conflictWarning = false;

    public function updatedNamaTaman(): void
    {
        $this->checkConflict();
    }

    public function checkConflict(): void
    {
        $namaTaman = $this->resolvedNamaTaman();

        if ($namaTaman && $this->tanggal_kegiatan) {
            $end = $this->tanggal_selesai ?: $this->tanggal_kegiatan;
            $this->conflictWarning = PermohonanPinjamTaman::hasConflict(
                $namaTaman,
                new \DateTime($this->tanggal_kegiatan),
                new \DateTime($end),
            );
        } else {
            $this->conflictWarning = false;
        }
    }

    // Nama taman final: nama resmi yang dipilih, atau nama manual saat opsi Lainnya.
    protected function resolvedNamaTaman(): ?string
    {
        if (! $this->nama_taman) {
            return null;
        }

        return $this->nama_taman === '__lainnya__'
            ? ($this->nama_taman_manual ?: null)
            : $this->nama_taman;
    }

    public function getCalendarDaysProperty(): array
    {
        $namaTaman = $this->resolvedNamaTaman();

        if (! $namaTaman) {
            return [];
        }

        $booked = PermohonanPinjamTaman::query()
            ->where('nama_taman', $namaTaman)
            ->where('status', StatusPengaduanRth::DITINJAU->value)
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
        if (! $this->verifyCaptcha('submit')) {
            return;
        }

        $this->resetCaptcha();

        $validated = $this->validate((new StorePermohonanPinjamTamanRequest())->rules());

        if ($this->hitRateLimit('pinjam-taman', 10, 'form', __('Pengiriman dibatasi maksimal 10 per jam.'))) {
            return;
        }

        $this->checkConflict();

        if ($this->conflictWarning) {
            $this->addError('tanggal_kegiatan', __('Tanggal bentrok dengan penyewaan yang sudah disetujui.'));

            return;
        }

        $isManualTaman = $validated['nama_taman'] === '__lainnya__';
        $namaTamanFinal = $isManualTaman ? $validated['nama_taman_manual'] : $validated['nama_taman'];

        $fileService = app(FileUploadService::class);

        $record = PermohonanPinjamTaman::create([
            'nama_pemohon' => $validated['nama_pemohon'],
            'nomor_hp' => $validated['nomor_hp'],
            'email' => $validated['email'],
            'nama_kegiatan' => $validated['nama_kegiatan'],
            'nama_taman' => $namaTamanFinal,
            'tanggal_kegiatan' => $validated['tanggal_kegiatan'],
            'tanggal_selesai' => $validated['tanggal_selesai'] ?: $validated['tanggal_kegiatan'],
            // Surat wajib PDF -> disimpan apa adanya (file sementara ikut dibersihkan).
            'surat_permohonan' => $fileService->store($this->surat_permohonan, 'pinjam-taman', 'public') ?: null,
            'jaminan_kebersihan' => true,
            'surat_jaminan' => $this->surat_jaminan ? ($fileService->store($this->surat_jaminan, 'pinjam-taman', 'public') ?: null) : null,
        ]);

        $this->successTicket = $record->nomor_tiket;
        $this->reset(['nama_pemohon', 'nomor_hp', 'email', 'nama_kegiatan', 'nama_taman', 'nama_taman_manual', 'tanggal_kegiatan', 'tanggal_selesai', 'surat_permohonan', 'jaminan_kebersihan', 'surat_jaminan']);
        $this->conflictWarning = false;
    }

    public function getTamansProperty()
    {
        $tamans = [
            'Taman Vatulemo' => 'Taman Vatulemo',
            'Taman Gor' => 'Taman Gor',
            'Taman Nasional' => 'Taman Nasional',
            'Taman Doyata' => 'Taman Doyata',
            'Taman Lasoso' => 'Taman Lasoso',
        ];

        $tamans['__lainnya__'] = 'Lainnya';

        return $tamans;
    }
};
?>

<div class="fi-form-card bg-white dark:bg-slate-950 rounded-2xl p-6 md:p-8 shadow-[0_1px_3px_rgba(13,43,29,0.06),0_12px_32px_-12px_rgba(13,43,29,0.10)] max-w-4xl mx-auto space-y-6">
    @if ($successTicket)
        <div class="space-y-6 text-center py-8">
            <div class="h-16 w-16 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-full flex items-center justify-center mx-auto"><x-icons.berhasil class="size-8" /></div>
            <div class="space-y-2">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ __('Permohonan Berhasil Terkirim') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">{{ __('Simpan nomor tiket di bawah untuk mengecek status penyewaan taman.') }}</p>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xs mx-auto">
                <span class="block text-[10px] text-brand-600 dark:text-brand-400 font-bold tracking-widest uppercase">{{ __('Nomor Tiket Anda') }}</span>
                <x-public.copy-ticket :ticket="$successTicket" class="block text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 select-all tracking-wider font-mono" />
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                <a href="{{ url('/cek-pinjam-taman') }}"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-slate-200 hover:bg-slate-100 h-10 py-2 px-4 dark:border-slate-800 dark:hover:bg-slate-800">
                    {{ __('Cek Status Penyewaan') }}
                </a>
                <button wire:click="$set('successTicket', null)"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-slate-900 text-slate-50 hover:bg-slate-900/90 h-10 py-2 px-4 dark:bg-slate-50 dark:text-slate-900">
                    {{ __('Ajukan Penyewaan Baru') }}
                </button>
            </div>
        </div>
    @else
        @if ($nama_taman && count($this->calendarDays))
            <div class="fi-cal-box">
                <div class="fi-cal-head">
                    <div class="flex items-center gap-2">
                        <span class="fi-cal-icon">
                            <x-icons.ui name="calendar" />
                        </span>
                        <p class="fi-cal-title">{{ __('Kalender Ketersediaan (30 Hari ke Depan)') }}</p>
                    </div>
                    <div class="fi-cal-legend">
                        <span class="fi-cal-legend-item"><span class="fi-cal-dot fi-cal-dot--available"></span> {{ __('Tersedia') }}</span>
                        <span class="fi-cal-legend-item"><span class="fi-cal-dot fi-cal-dot--booked"></span> {{ __('Terisi') }}</span>
                    </div>
                </div>
                <div class="fi-cal-grid">
                    @foreach ($this->calendarDays as $day)
                        <div @class([
                            'fi-cal-day',
                            'fi-cal-day--booked' => $day['isBooked'],
                            'fi-cal-day--available' => ! $day['isBooked'],
                            'fi-cal-day--today' => $day['isToday'],
                        ])>
                            <span class="fi-cal-day-name">{{ $day['dayName'] }}</span>
                            <span class="fi-cal-day-label">{{ $day['label'] }}</span>
                            <span class="fi-cal-day-month">{{ $day['monthLabel'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <form wire:submit.prevent="submit" data-dlh-recaptcha-action="submit" class="space-y-5">
            <div class="grid md:grid-cols-2 gap-5">
                <x-public.input
                    wire:model="nama_pemohon"
                    name="nama_pemohon"
                    label="{{ __('Nama Pemohon / Komunitas') }}"
                    placeholder="{{ __('Nama lengkap pemohon atau komunitas') }}"
                    required
                    icon="user"
                />

                <x-public.input
                    wire:model="nomor_hp"
                    name="nomor_hp"
                    type="tel"
                    label="{{ __('Nomor Telepon') }}"
                    placeholder="{{ __('Contoh: 08123456789') }}"
                    required
                    icon="phone"
                />

                <x-public.input
                    wire:model="email"
                    name="email"
                    type="email"
                    label="{{ __('Email') }}"
                    placeholder="{{ __('contoh@email.com') }}"
                    required
                    hint="{{ __('Untuk notifikasi update status permohonan') }}"
                    icon="mail"
                />

                <x-public.input
                    wire:model="nama_kegiatan"
                    name="nama_kegiatan"
                    label="{{ __('Nama Kegiatan') }}"
                    placeholder="{{ __('Contoh: Festival Musik Komunitas') }}"
                    required
                    icon="calendar"
                />
            </div>

            <x-public.select
                wire:model.live="nama_taman"
                name="nama_taman"
                label="{{ __('Taman') }}"
                :options="$this->tamans"
                :searchable="true"
                placeholder="{{ __('Pilih taman...') }}"
                required
            />

            @if ($nama_taman === '__lainnya__')
                <x-public.input
                    wire:model="nama_taman_manual"
                    name="nama_taman_manual"
                    label="{{ __('Nama Taman (Lainnya)') }}"
                    placeholder="{{ __('Tulis nama taman...') }}"
                    required
                />
            @endif

            <div class="grid md:grid-cols-2 gap-5">
                <x-admin.date-field
                    id="tanggal_kegiatan"
                    wire:model.live="tanggal_kegiatan"
                    wire:change="checkConflict"
                    name="tanggal_kegiatan"
                    type="datetime"
                    :label="__('Tanggal & Jam Mulai')"
                    :value="$tanggal_kegiatan"
                    :error="$errors->first('tanggal_kegiatan')"
                    required
                />

                <x-admin.date-field
                    id="tanggal_selesai"
                    wire:model.live="tanggal_selesai"
                    wire:change="checkConflict"
                    name="tanggal_selesai"
                    type="datetime"
                    :label="__('Tanggal & Jam Selesai')"
                    :value="$tanggal_selesai"
                    :error="$errors->first('tanggal_selesai')"
                />
            </div>

            @if ($conflictWarning)
                <div class="fi-conflict-alert">
                    <x-icons.ui name="alert" />
                    {{ __('Tanggal bentrok dengan jadwal yang sudah disetujui.') }}
                </div>
            @endif

            {{-- Surat Permohonan --}}
            <div class="fi-field">
                <label class="fi-label">
                    <span class="fi-icon-badge"><x-icons.ui name="document" /></span>
                    {{ __('Surat Permohonan') }} <span class="fi-required">*</span>
                    <span style="font-weight:400;color:#5b6b63;font-size:12.5px;">(PDF, max 5MB)</span>
                </label>
                <div class="fi-file-drop">
                    <button type="button" class="fi-file-btn" x-on:click="$refs.suratInput.click()">{{ __('Pilih File') }}</button>
                    <span class="fi-file-status">
                        @if ($surat_permohonan)
                            <span class="text-brand-600 dark:text-brand-400 font-medium">{{ $surat_permohonan->getClientOriginalName() }}</span>
                        @else
                            {{ __('Belum ada file dipilih') }}
                        @endif
                    </span>
                    <input wire:model="surat_permohonan" x-ref="suratInput" type="file" accept="application/pdf" required aria-label="{{ __('Surat Permohonan') }}"
                        style="position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;" />
                </div>
                @error('surat_permohonan') <p class="fi-error"><x-icons.ui name="alert" />{{ $message }}</p> @enderror
            </div>

            {{-- Checkbox jaminan kebersihan --}}
            <label class="fi-check-row">
                <input wire:model="jaminan_kebersihan" type="checkbox" class="fi-check-input" />
                <span class="fi-check-label">{{ __('Saya berjanji menjaga kebersihan taman') }}</span>
            </label>
            @error('jaminan_kebersihan') <p class="fi-error"><x-icons.ui name="alert" />{{ $message }}</p> @enderror

            {{-- Surat Jaminan (opsional) --}}
            <div class="fi-field">
                <label class="fi-label">
                    <span class="fi-icon-badge"><x-icons.ui name="shield" /></span>
                    {{ __('Surat Jaminan') }}
                    <span style="font-weight:400;color:#5b6b63;font-size:12.5px;">(opsional, PDF max 5MB)</span>
                </label>
                <div class="fi-file-drop">
                    <button type="button" class="fi-file-btn" x-on:click="$refs.jaminanInput.click()">{{ __('Pilih File') }}</button>
                    <span class="fi-file-status">
                        @if ($surat_jaminan)
                            <span class="text-brand-600 dark:text-brand-400 font-medium">{{ $surat_jaminan->getClientOriginalName() }}</span>
                        @else
                            {{ __('Belum ada file dipilih') }}
                        @endif
                    </span>
                    <input wire:model="surat_jaminan" x-ref="jaminanInput" type="file" accept="application/pdf"
                        style="position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;" />
                </div>
                @error('surat_jaminan') <p class="fi-error"><x-icons.ui name="alert" />{{ $message }}</p> @enderror
            </div>

            @error('form')
                <div class="dlh-limit-alert" role="alert">
                    <x-icons.ui name="alert" />
                    <span>{{ $message }}</span>
                </div>
            @enderror

        <x-google-recaptcha />

            <button type="submit" class="fi-submit-btn">
                {{ __('Ajukan Penyewaan') }}
                <x-icons.ui name="arrow-right" class="ml-2 h-4 w-4" />
            </button>
        </form>
    @endif

    <style>

        .fi-form-card { font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif; }

        .fi-field { position: relative; }
        .fi-label {
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; font-weight: 600; color: #12201a; margin-bottom: 8px; cursor: pointer;
        }
        .fi-required { color: #f43f5e; font-size: 14px; font-weight: 400; margin-left: 1px; }
        .fi-icon-badge {
            width: 26px; height: 26px; border-radius: 8px;
            background: #e6f5ec; color: #146a44;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .fi-icon-badge svg { width: 15px; height: 15px; }
        .fi-error {
            display: flex; align-items: center; gap: 5px;
            margin-top: 6px; font-size: 11.5px; font-weight: 500; color: #e0533d;
        }
        .fi-error svg { width: 13px; height: 13px; flex-shrink: 0; }

        /* ── Date input (pill style match) ── */
        /* ── File drop ── */
        .fi-file-drop {
            border: 1.5px dashed #a9dcc0; border-radius: 16px; background: #f4faf6;
            padding: 14px 16px; display: flex; align-items: center; gap: 14px;
            transition: border-color .18s ease, background .18s ease;
        }
        .fi-file-drop:hover { background: #eefaf3; border-color: #1ea567; }
        .fi-file-btn {
            flex-shrink: 0; height: 38px; padding: 0 20px; border-radius: 9999px; border: none;
            background: linear-gradient(180deg, #178a53, #146a44); color: #fff;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            font-size: 13px; font-weight: 600; cursor: pointer;
            box-shadow: 0 4px 10px -2px rgba(20, 106, 68, 0.4); transition: filter .15s ease;
        }
        .fi-file-btn:hover { filter: brightness(1.05); }
        .fi-file-status { font-size: 13px; color: #5f7268; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* ── Checkbox row ── */
        .fi-check-row {
            display: flex; align-items: center; gap: 10px;
            padding: 14px 16px; border-radius: 14px;
            background: #f4faf6; border: 1px solid #d1e7da;
            cursor: pointer;
        }
        .fi-check-input {
            width: 18px; height: 18px; accent-color: #178a53; cursor: pointer;
        }
        .fi-check-label { font-size: 13.5px; font-weight: 500; color: #12201a; }

        /* ── Conflict alert ── */
        .fi-conflict-alert {
            display: flex; align-items: center; gap: 8px;
            padding: 12px 16px; border-radius: 14px;
            background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
            font-size: 13px; font-weight: 600;
        }
        .fi-conflict-alert svg { width: 16px; height: 16px; flex-shrink: 0; }

        /* ── Calendar box ── */
        .fi-cal-box {
            background: #f8faf9; border: 1px solid #e8efe9; border-radius: 18px; padding: 18px 20px;
        }
        .fi-cal-head {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 14px;
        }
        .fi-cal-icon {
            width: 30px; height: 30px; border-radius: 9px;
            background: linear-gradient(135deg, #178a53, #146a44); color: #fff;
            display: flex; align-items: center; justify-content: center;
        }
        .fi-cal-icon svg { width: 15px; height: 15px; }
        .fi-cal-title { font-size: 13.5px; font-weight: 700; color: #12201a; }
        .fi-cal-legend { display: flex; align-items: center; gap: 12px; }
        .fi-cal-legend-item { display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; color: #5b6b63; font-weight: 500; }
        .fi-cal-dot { width: 10px; height: 10px; border-radius: 3px; }
        .fi-cal-dot--available { background: #e6f5ec; border: 1px solid #a9dcc0; }
        .fi-cal-dot--booked { background: #fef3c7; border: 1px solid #fde68a; }

        .fi-cal-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 6px; }
        .fi-cal-day {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 8px 4px; border-radius: 10px; border: 1px solid; text-align: center;
            transition: transform .12s ease;
        }
        .fi-cal-day--available {
            background: #e6f5ec; border-color: #a9dcc0; color: #146a44;
        }
        .fi-cal-day--booked {
            background: #fef3c7; border-color: #fde68a; color: #92400e;
        }
        .fi-cal-day--today { box-shadow: 0 0 0 2px #1ea567; }
        .fi-cal-day-name { font-size: 9px; text-transform: uppercase; color: inherit; opacity: 0.7; }
        .fi-cal-day-label { font-size: 14px; font-weight: 700; }
        .fi-cal-day-month { font-size: 9px; opacity: 0.6; }

        /* ── Submit Button ── */
        .fi-submit-btn {
            width: 100%; height: 52px; border: none; border-radius: 9999px;
            background: linear-gradient(180deg, #178a53, #146a44); color: #fff;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            font-size: 15px; font-weight: 700; letter-spacing: .2px; cursor: pointer;
            box-shadow: 0 10px 24px -8px rgba(20, 106, 68, 0.55);
            transition: transform .12s ease, box-shadow .12s ease;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .fi-submit-btn:hover { transform: translateY(-1px); box-shadow: 0 14px 28px -8px rgba(20, 106, 68, 0.6); }
        .fi-submit-btn:active { transform: translateY(0); }

        /* ── Dark mode ── */
        .dark .fi-label { color: #e2e8f0; }
        .dark .fi-icon-badge { background: rgba(30,165,103,0.15); color: #1ea567; }
        .dark .fi-file-drop { background: #0f172a; border-color: #334155; }
        .dark .fi-file-drop:hover { background: #1e293b; border-color: #1ea567; }
        .dark .fi-file-status { color: #64748b; }
        .dark .fi-check-row { background: rgba(30,165,103,0.08); border-color: rgba(30,165,103,0.25); }
        .dark .fi-check-label { color: #e2e8f0; }
        .dark .fi-conflict-alert { background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.3); color: #fca5a5; }
        .dark .fi-cal-box { background: #0f172a; border-color: #334155; }
        .dark .fi-cal-title { color: #e2e8f0; }
        .dark .fi-cal-legend-item { color: #94a3b8; }
        .dark .fi-cal-day--available { background: rgba(30,165,103,0.15); border-color: rgba(30,165,103,0.3); color: #6ee7b7; }
        .dark .fi-cal-day--booked { background: rgba(245,158,11,0.15); border-color: rgba(245,158,11,0.3); color: #fcd34d; }
        .dark .fi-submit-btn { background: linear-gradient(180deg, #1ea567, #178a53); box-shadow: 0 10px 24px -8px rgba(30, 165, 103, 0.5); }
    </style>
</div>
