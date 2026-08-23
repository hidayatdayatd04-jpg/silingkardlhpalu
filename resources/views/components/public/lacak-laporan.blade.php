<?php

use App\Livewire\Concerns\ThrottlesPublic;
use App\Livewire\Concerns\VerifiesGoogleRecaptcha;
use Livewire\Component;
use App\Models\PengaduanPengendalian;
use App\Models\PengaduanSampah;
use App\Models\PengaduanRth;
use App\Models\PengaduanTataPenataan;

new class extends Component
{
    use VerifiesGoogleRecaptcha;
    use ThrottlesPublic;

    public string $search = '';
    /** @var PengaduanPengendalian|PengaduanSampah|PengaduanRth|null */
    public $pengaduan = null;
    public ?PengaduanTataPenataan $pengaduanTataPenataan = null;

    private const TICKET_MODELS = [
        'PDL' => PengaduanPengendalian::class,
        'SMP' => PengaduanSampah::class,
        'RTH' => PengaduanRth::class,
        'TTP' => PengaduanTataPenataan::class,
    ];

    /**
     * Normalisasi nomor telepon ke bentuk baku agar pencarian cocok meski
     * pengguna mengetik 08xxx, 62xxx, atau +62xxx.
     *
     * @return string[]
     */
    private function normalizePhoneCandidates(string $digits): array
    {
        if ($digits === '') {
            return [];
        }

        $candidates = [$digits];

        if (str_starts_with($digits, '0')) {
            // 08xxx -> 628xxx
            $candidates[] = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '62')) {
            // 628xxx -> 08xxx
            $candidates[] = '0'.substr($digits, 2);
        }

        return array_values(array_unique($candidates));
    }

    public function lookup()
    {
        if (! $this->verifyCaptcha('lookup')) {
            return;
        }

        $this->resetCaptcha();

        $this->validate([
            'search' => 'required|string',
        ]);

        if ($this->hitRateLimit('lacak-laporan:search', 20, 'form', __('Batas pencarian tercapai (maksimal 20 kali per jam).'))) {
            return;
        }

        $value = trim($this->search);

        // Deteksi nomor telepon: hanya terdiri dari angka, spasi, tanda hubung,
        // atau diawali '+'. Nomor tiket mengandung huruf sehingga tidak akan cocok.
        $digits = preg_replace('/\D/', '', $value);
        $isPhone = $digits !== '' && preg_match('/^[\d\s\-+]+$/', $value);

        $this->pengaduan = null;
        $this->pengaduanTataPenataan = null;

        if ($isPhone) {
            $candidates = $this->normalizePhoneCandidates($digits);

            if (empty($candidates)) {
                $this->addError('search', __('Nomor telepon tidak valid.'));

                return;
            }

            foreach (self::TICKET_MODELS as $modelClass) {
                $found = $modelClass::query()
                    ->with('fotos')
                    ->where(function ($query) use ($candidates): void {
                        foreach ($candidates as $candidate) {
                            $query->orWhere('nomor_hp', $candidate)
                                // Cocokkan meski tersimpan dengan '+', spasi, atau '-'.
                                ->orWhereRaw("REPLACE(REPLACE(REPLACE(nomor_hp, '+', ''), ' ', ''), '-', '') = ?", [$candidate]);
                        }
                    })
                    ->first();

                if ($found) {
                    if ($modelClass === PengaduanTataPenataan::class) {
                        $this->pengaduanTataPenataan = $found;
                    } else {
                        $this->pengaduan = $found;
                    }

                    return;
                }
            }

            $this->addError('search', __('Nomor telepon tidak ditemukan.'));

            return;
        }

        $ticket = strtoupper($value);

        // Shortcut: cari di tabel sesuai prefix tiket.
        $prefix = substr($ticket, 0, 3);
        $ordered = isset(self::TICKET_MODELS[$prefix])
            ? [self::TICKET_MODELS[$prefix]] + self::TICKET_MODELS
            : self::TICKET_MODELS;

        foreach ($ordered as $modelClass) {
            $found = $modelClass::with('fotos')
                ->whereRaw('UPPER(nomor_tiket) = ?', [$ticket])
                ->first();

            if ($found) {
                if ($modelClass === PengaduanTataPenataan::class) {
                    $this->pengaduanTataPenataan = $found;
                } else {
                    $this->pengaduan = $found;
                }

                return;
            }
        }

        $this->addError('search', __('Nomor tiket tidak ditemukan.'));
    }
};
?>

<div class="space-y-6 lc-wrap">
    {{-- Search Bar --}}
    <div class="lc-search-card max-w-4xl mx-auto">
        <div class="lc-search-head">
            <span class="lc-search-icon">
                <x-icons.ui name="search" />
            </span>
            <div class="flex-1">
                <h3 class="lc-search-title">{{ __('Lacak Laporan') }}</h3>
                <p class="lc-search-desc">{{ __('Gunakan nomor tiket atau nomor telepon yang tercantum pada bukti laporan.') }}</p>
            </div>
        </div>
        <form wire:submit="lookup" @if(\App\Support\Captcha::enabled()) data-dlh-recaptcha-action="lookup" @endif class="tracking-search-form">
            <div class="flex-1">
                <x-public.input
                    wire:model="search"
                    name="search"
                    placeholder="{{ __('Nomor tiket (PDL/SMP/RTH/TTP) atau nomor telepon (08xxx / 62xxx / +62xxx)') }}"
                    required
                />
            </div>

            <button type="submit" class="lc-search-btn">
                <x-icons.ui name="search" class="h-4 w-4" />
                {{ __('Cari Laporan') }}
            </button>
        </form>

        @error('form')
            <div class="dlh-limit-alert" role="alert">
                <x-icons.ui name="alert" />
                <span>{{ $message }}</span>
            </div>
        @enderror

        <x-google-recaptcha />
    </div>

    @if ($pengaduan)
        <div class="lc-result-card max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-6">
                <div class="flex items-center gap-3">
                    <span class="lc-result-icon">
                        <x-icons.ui name="document" />
                    </span>
                    <div>
                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 font-bold tracking-widest uppercase">{{ __('Nomor Tiket') }}</span>
                        <x-public.copy-ticket :ticket="$pengaduan->nomor_tiket" class="text-2xl font-bold font-mono text-slate-900 dark:text-slate-100" />
                    </div>
                </div>
                @php
                    $badgeColors = [
                        'Belum Ditinjau' => 'lc-status--pending',
                        'Ditinjau' => 'lc-status--info',
                        'Selesai' => 'lc-status--done',
                        'Ditolak' => 'lc-status--rejected',
                        'Belum Ditindaklanjuti' => 'lc-status--pending',
                        'Ditindaklanjuti' => 'lc-status--info',
                    ];
                    $statusStr = $pengaduan->status instanceof \BackedEnum ? $pengaduan->status->value : $pengaduan->status;
                    $isDone = in_array($statusStr, ['Selesai', 'Ditindaklanjuti'], true);
                    $isRejected = $statusStr === 'Ditolak';
                @endphp
                <span class="lc-status-badge {{ $badgeColors[$pengaduan->status_label] ?? 'lc-status--pending' }}">
                    @if ($isDone)
                        <x-icons.ui name="check" class="h-3 w-3" />
                    @elseif ($isRejected)
                        <x-icons.ui name="close" class="h-3 w-3" />
                    @else
                        <span class="lc-status-dot"></span>
                    @endif
                    {{ $pengaduan->status_label }}
                </span>
            </div>

            {{-- Ulasan Masyarakat --}}
            <x-public.ticket-feedback :ticket="$pengaduan" />

            {{-- Mini Stepper --}}
            <div class="lc-stepper-wrap">
                @php
                    $statusStr = $pengaduan->status instanceof \BackedEnum ? $pengaduan->status->value : $pengaduan->status;
                    $steps = [__('Menunggu'), __('Selesai')];
                    $statusToStep = [
                        'Belum Ditinjau' => 0,
                        'Ditinjau' => 0,
                        'Selesai' => 1,
                        'Ditolak' => 1,
                        'Belum Ditindaklanjuti' => 0,
                        'Ditindaklanjuti' => 1,
                    ];
                    $currentIdx = $statusToStep[$statusStr] ?? 0;
                    $isRejected = $statusStr === 'Ditolak';
                    if ($isRejected) { $steps = [__('Menunggu'), __('Ditolak')]; }
                @endphp
                @foreach ($steps as $idx => $step)
                    <div class="lc-step">
                        @if ($idx < count($steps) - 1)
                            <div class="lc-step-line lc-step-line--bg"></div>
                            @if ($idx < $currentIdx)
                                <div class="lc-step-line {{ $isRejected ? 'lc-step-line--rejected' : 'lc-step-line--done' }}"></div>
                            @endif
                        @endif
                        <div @class([
                            'lc-step-dot',
                            'lc-step-dot--done' => $idx <= $currentIdx && !$isRejected,
                            'lc-step-dot--rejected' => $idx <= $currentIdx && $isRejected,
                            'lc-step-dot--pending' => $idx > $currentIdx,
                        ])>{{ $idx + 1 }}</div>
                        <span class="lc-step-label">{{ $step }}</span>
                    </div>
                @endforeach
            </div>

            <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($pengaduan)" />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 border-t border-slate-100 dark:border-slate-800 pt-8">
                <div class="space-y-6">
                    <div class="space-y-4">
                        <h3 class="lc-section-title">
                            <x-icons.ui name="document" />
                            {{ __('Rincian Aduan') }}
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="lc-info-tile">
                                <span class="lc-info-label">{{ __('Jenis Pengaduan') }}</span>
                                <span class="lc-info-value">{{ $pengaduan->jenis_pengaduan }}</span>
                            </div>
                            <div class="lc-info-tile">
                                <span class="lc-info-label">{{ __('Tanggal Masuk') }}</span>
                                <span class="lc-info-value">{{ $pengaduan->created_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                        <div class="lc-desc-box">
                            <span class="lc-info-label">{{ __('Deskripsi') }}</span>
                            <p class="lc-desc-text">{{ $pengaduan->deskripsi }}</p>
                        </div>
                    </div>

                    @if ($statusStr === 'Ditolak')
                        <div class="lc-reject-box">
                            <span class="lc-reject-label">
                                <x-icons.ui name="close" class="h-4 w-4" />
                                {{ __('Alasan Penolakan') }}
                            </span>
                            <p class="text-sm mt-1.5">{{ $pengaduan->alasan_penolakan ?? __('Tidak ada alasan penolakan yang ditulis.') }}</p>
                        </div>
                    @endif

                    @if (in_array($statusStr, ['Ditindaklanjuti', 'Selesai', 'Ditolak'], true) && filled($pengaduan->catatan_admin))
                        <div class="lc-note-box">
                            <span class="lc-note-label">
                                <x-icons.ui name="message" class="h-4 w-4" />
                                {{ __('Catatan Admin') }}
                            </span>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $pengaduan->catatan_admin }}</p>
                        </div>
                    @endif

                    @if ($pengaduan->fotos->isNotEmpty())
                        <div class="space-y-2">
                            <span class="lc-info-label">{{ __('Foto Lampiran Pengaduan') }}</span>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ($pengaduan->fotos as $foto)
                                    <div class="lc-photo-thumb">
                                        <img src="{{ $foto->fullUrl() }}" alt="{{ __('Foto lampiran pengaduan') }}" class="w-full h-full object-cover" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <h3 class="lc-section-title">
                        <x-icons.ui name="map-pin" />
                        {{ __('Lokasi Peta') }}
                    </h3>
                    <div wire:ignore wire:key="map-{{ $pengaduan->nomor_tiket }}"
                         x-data x-init="setTimeout(function(){window.ensureMapComponents().then(function(){window.dlhSimpleMap('cek-map-laporan-{{ $pengaduan->nomor_tiket }}',{lat:@js($pengaduan->latitude),lng:@js($pengaduan->longitude),zoom:14,popupText:@js(__('Lokasi Laporan'))})})},100)">
                        <div id="cek-map-laporan-{{ $pengaduan->nomor_tiket }}" class="lc-map"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($pengaduanTataPenataan)
        @php
            $statusColor = $pengaduanTataPenataan->status?->color() ?? 'gray';
            $badgeMap = [
                'gray' => 'lc-status--pending',
                'warning' => 'lc-status--info',
                'success' => 'lc-status--done',
            ];
            $isDone = in_array($statusColor, ['success']);
        @endphp
        <div class="lc-result-card max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-6">
                <div class="flex items-center gap-3">
                    <span class="lc-result-icon">
                        <x-icons.ui name="document" />
                    </span>
                    <div>
                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 font-bold tracking-widest uppercase">{{ __('Nomor Tiket') }}</span>
                        <x-public.copy-ticket :ticket="$pengaduanTataPenataan->nomor_tiket" class="text-2xl font-bold font-mono text-slate-900 dark:text-slate-100" />
                    </div>
                </div>
                <span class="lc-status-badge {{ $badgeMap[$statusColor] ?? 'lc-status--pending' }}">
                    @if ($isDone)
                        <x-icons.ui name="check" class="h-3 w-3" />
                    @else
                        <span class="lc-status-dot"></span>
                    @endif
                    {{ $pengaduanTataPenataan->status?->label() ?? $pengaduanTataPenataan->status }}
                </span>
            </div>

            {{-- Ulasan Masyarakat --}}
            <x-public.ticket-feedback :ticket="$pengaduanTataPenataan" />

            <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($pengaduanTataPenataan)" />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        @php
                            // Data pribadi pelapor (nama, alamat, nama terlapor) dan catatan
                            // internal admin sengaja TIDAK ditampilkan di halaman publik —
                            // disamakan dengan cabang pengaduan lain yang hanya menampilkan
                            // informasi non-personal.
                            $items = [
                                __('Jenis Pengaduan') => \App\Enums\JenisPengaduanTataPenataan::tryFrom($pengaduanTataPenataan->jenis_pengaduan)?->label() ?? $pengaduanTataPenataan->jenis_pengaduan,
                                __('Tanggal Lapor') => $pengaduanTataPenataan->created_at->format('d M Y H:i'),
                            ];
                            if ($pengaduanTataPenataan->nama_perusahaan_terlapor) $items[__('Perusahaan Terlapor')] = $pengaduanTataPenataan->nama_perusahaan_terlapor;
                        @endphp
                        @foreach ($items as $label => $value)
                            <div class="lc-info-tile">
                                <span class="lc-info-label">{{ $label }}</span>
                                <span class="lc-info-value">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="lc-desc-box">
                        <span class="lc-info-label">{{ __('Deskripsi') }}</span>
                        <p class="lc-desc-text">{{ $pengaduanTataPenataan->deskripsi }}</p>
                    </div>
                    @if ($isDone && filled($pengaduanTataPenataan->catatan_admin))
                        <div class="lc-note-box">
                            <span class="lc-note-label">
                                <x-icons.ui name="message" class="h-4 w-4" />
                                {{ __('Catatan Admin') }}
                            </span>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $pengaduanTataPenataan->catatan_admin }}</p>
                        </div>
                    @endif
                    @if ($pengaduanTataPenataan->fotos->isNotEmpty())
                        <div class="space-y-2">
                            <span class="lc-info-label">{{ __('Foto Bukti Pengaduan') }}</span>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ($pengaduanTataPenataan->fotos as $foto)
                                    <div class="lc-photo-thumb">
                                        <img src="{{ $foto->fullUrl() }}" alt="{{ __('Foto bukti pengaduan') }}" class="w-full h-full object-cover" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <h3 class="lc-section-title">
                        <x-icons.ui name="map-pin" />
                        {{ __('Lokasi Peta') }}
                    </h3>
                    <div wire:ignore wire:key="map-ttp-{{ $pengaduanTataPenataan->nomor_tiket }}"
                         x-data x-init="setTimeout(function(){window.ensureMapComponents().then(function(){window.dlhSimpleMap('cek-map-ttp-{{ $pengaduanTataPenataan->nomor_tiket }}',{lat:@js($pengaduanTataPenataan->latitude),lng:@js($pengaduanTataPenataan->longitude),zoom:14,popupText:@js(__('Lokasi Pengaduan'))})})},100)">
                        <div id="cek-map-ttp-{{ $pengaduanTataPenataan->nomor_tiket }}" class="lc-map"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>

        .lc-wrap { font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif; }

        /* ── Search Card ── */
        .lc-search-card {
            background: #fff; border: 1px solid #e8efe9; border-radius: 24px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(13,43,29,0.04), 0 12px 32px -12px rgba(13,43,29,0.1);
        }
        .lc-search-head { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px; }
        .lc-search-icon {
            flex-shrink: 0; width: 44px; height: 44px; border-radius: 13px;
            background: linear-gradient(135deg, #178a53, #146a44); color: #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 6px 14px -2px rgba(20, 106, 68, 0.35);
        }
        .lc-search-icon svg { width: 20px; height: 20px; }
        .lc-search-title { font-size: 17px; font-weight: 700; color: #12201a; letter-spacing: -0.01em; }
        .lc-search-desc { font-size: 13px; color: #5b6b63; margin-top: 4px; line-height: 1.55; }

        .lc-search-btn {
            height: 48px; padding: 0 24px; border: none; border-radius: 9999px;
            background: linear-gradient(180deg, #178a53, #146a44); color: #fff;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            font-size: 14px; font-weight: 700; cursor: pointer;
            box-shadow: 0 8px 20px -6px rgba(20, 106, 68, 0.5);
            transition: transform .12s ease, box-shadow .12s ease;
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            white-space: nowrap;
        }
        .lc-search-btn:hover { transform: translateY(-1px); box-shadow: 0 12px 24px -6px rgba(20, 106, 68, 0.55); }

        /* ── Result Card ── */
        .lc-result-card {
            background: #fff; border: 1px solid #e8efe9; border-radius: 24px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(13,43,29,0.04), 0 12px 32px -12px rgba(13,43,29,0.1);
        }
        .lc-result-icon {
            flex-shrink: 0; width: 44px; height: 44px; border-radius: 13px;
            background: #e6f5ec; color: #146a44;
            display: flex; align-items: center; justify-content: center;
        }
        .lc-result-icon svg { width: 20px; height: 20px; }

        /* ── Status Badge ── */
        .lc-status-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 9999px;
            font-size: 12px; font-weight: 700; border: 1px solid transparent;
        }
        .lc-status--done { background: #dcfce7; color: #166534; border-color: #86efac; }
        .lc-status--pending { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .lc-status--info { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
        .lc-status--rejected { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .lc-status-dot {
            display: inline-block; width: 6px; height: 6px; border-radius: 9999px;
            background: currentColor; animation: lc-pulse 1.6s ease-in-out infinite;
        }
        @keyframes lc-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

        /* ── Stepper ── */
        .lc-stepper-wrap {
            display: flex; width: 100%; position: relative; z-index: 0; margin: 20px 0;
        }
        .lc-step { position: relative; flex: 1; text-align: center; }
        .lc-step-line {
            position: absolute; top: 16px; left: 50%; width: 100%; height: 2px; z-index: -1;
        }
        .lc-step-line--bg { background: #e8efe9; }
        .lc-step-line--done { background: #1ea567; }
        .lc-step-line--rejected { background: #ef4444; }
        .lc-step-dot {
            width: 32px; height: 32px; margin: 0 auto; border-radius: 9999px;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
            box-shadow: 0 0 0 6px #fff;
        }
        .lc-step-dot--done { background: linear-gradient(135deg, #1ea567, #146a44); color: #fff; box-shadow: 0 0 0 6px #fff, 0 4px 10px -2px rgba(20, 106, 68, 0.4); }
        .lc-step-dot--rejected { background: #ef4444; color: #fff; box-shadow: 0 0 0 6px #fff, 0 4px 10px -2px rgba(239, 68, 68, 0.4); }
        .lc-step-dot--pending { background: #f1f5f3; color: #94a3b8; }
        .lc-step-label {
            display: block; margin-top: 12px;
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #5b6b63;
        }

        /* ── Section title ── */
        .lc-section-title {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 16px; font-weight: 700; color: #12201a;
        }
        .lc-section-title svg { width: 18px; height: 18px; color: #146a44; }

        /* ── Info tile ── */
        .lc-info-tile {
            background: #f8faf9; border: 1px solid #e8efe9; border-radius: 12px; padding: 12px 14px;
        }
        .lc-info-label {
            display: block; font-size: 11px; font-weight: 600; color: #5b6b63;
            text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;
        }
        .lc-info-value { display: block; font-size: 14px; font-weight: 600; color: #12201a; line-height: 1.4; }

        .lc-desc-box {
            background: #f8faf9; border: 1px solid #e8efe9; border-radius: 12px; padding: 14px;
        }
        .lc-desc-text { font-size: 13.5px; color: #475569; line-height: 1.6; margin-top: 6px; }

        .lc-reject-box {
            padding: 14px 16px; background: #fef2f2; border: 1px solid #fecaca;
            border-radius: 14px; border-left: 3px solid #ef4444;
        }
        .lc-reject-label {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 700; color: #991b1b;
        }

        .lc-note-box {
            padding: 14px 16px; background: #f4faf6; border: 1px solid #d1e7da;
            border-radius: 14px; border-left: 3px solid #1ea567;
        }
        .lc-note-label {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 700; color: #146a44;
        }

        .lc-photo-thumb {
            aspect-ratio: 1; border-radius: 12px; overflow: hidden;
            border: 1px solid #e8efe9;
            transition: transform .15s ease;
        }
        .lc-photo-thumb:hover { transform: scale(1.04); }

        .lc-map {
            width: 100%; height: 300px; border-radius: 16px; overflow: hidden;
            border: 1px solid #e8efe9; position: relative; z-index: 0;
        }

        /* ── Dark mode ── */
        .dark .lc-search-card { background: #1e293b; border-color: #334155; }
        .dark .lc-search-title { color: #e2e8f0; }
        .dark .lc-search-desc { color: #94a3b8; }
        .dark .lc-result-card { background: #1e293b; border-color: #334155; }
        .dark .lc-result-icon { background: rgba(30,165,103,0.15); color: #1ea567; }
        .dark .lc-status--done { background: rgba(16,185,129,0.15); color: #6ee7b7; border-color: rgba(16,185,129,0.3); }
        .dark .lc-status--pending { background: rgba(245,158,11,0.15); color: #fcd34d; border-color: rgba(245,158,11,0.3); }
        .dark .lc-status--info { background: rgba(59,130,246,0.15); color: #93c5fd; border-color: rgba(59,130,246,0.3); }
        .dark .lc-status--rejected { background: rgba(239,68,68,0.15); color: #fca5a5; border-color: rgba(239,68,68,0.3); }
        .dark .lc-step-line--bg { background: #334155; }
        .dark .lc-step-dot--done { box-shadow: 0 0 0 6px #1e293b, 0 4px 10px -2px rgba(20, 106, 68, 0.4); }
        .dark .lc-step-dot--rejected { box-shadow: 0 0 0 6px #1e293b, 0 4px 10px -2px rgba(239, 68, 68, 0.4); }
        .dark .lc-step-dot--pending { background: #0f172a; color: #64748b; }
        .dark .lc-step-label { color: #94a3b8; }
        .dark .lc-section-title { color: #e2e8f0; }
        .dark .lc-section-title svg { color: #6ee7b7; }
        .dark .lc-info-tile { background: #0f172a; border-color: #334155; }
        .dark .lc-info-label { color: #94a3b8; }
        .dark .lc-info-value { color: #e2e8f0; }
        .dark .lc-desc-box { background: #0f172a; border-color: #334155; }
        .dark .lc-desc-text { color: #cbd5e1; }
        .dark .lc-reject-box { background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.3); }
        .dark .lc-reject-label { color: #fca5a5; }
        .dark .lc-note-box { background: rgba(30,165,103,0.08); border-color: rgba(30,165,103,0.25); }
        .dark .lc-note-label { color: #6ee7b7; }
        .dark .lc-photo-thumb { border-color: #334155; }
        .dark .lc-map { border-color: #334155; }
        .dark .lc-search-btn { background: linear-gradient(180deg, #1ea567, #178a53); }
    </style>
</div>
