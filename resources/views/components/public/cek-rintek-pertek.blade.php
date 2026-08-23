<?php

use App\Livewire\Concerns\ThrottlesPublic;
use App\Livewire\Concerns\VerifiesGoogleRecaptcha;
use Livewire\Component;

new class extends Component
{
    use VerifiesGoogleRecaptcha;
    use ThrottlesPublic;

    public string $search = '';
    public ?\App\Models\PengajuanRintekPertek $pengajuan = null;

    private function normalizePhoneCandidates(string $digits): array
    {
        if ($digits === '') {
            return [];
        }

        $candidates = [$digits];

        if (str_starts_with($digits, '0')) {
            $candidates[] = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '62')) {
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

        $this->validate(['search' => 'required|string']);

        if ($this->hitRateLimit('cek-rintek-pertek:search', 20, 'form', __('Batas pencarian tercapai (maksimal 20 kali per jam).'))) {
            return;
        }

        $value = trim($this->search);
        $digits = preg_replace('/\D/', '', $value);
        $isPhone = $digits !== '' && preg_match('/^[\d\s\-+]+$/', $value);

        if ($isPhone) {
            $candidates = $this->normalizePhoneCandidates($digits);

            if (empty($candidates)) {
                $this->addError('search', __('Nomor telepon tidak valid.'));

                return;
            }

            $this->pengajuan = \App\Models\PengajuanRintekPertek::query()
                ->where(function ($query) use ($candidates): void {
                    foreach ($candidates as $candidate) {
                        $query->orWhere('nomor_telepon', $candidate)
                            ->orWhereRaw("REPLACE(REPLACE(REPLACE(nomor_telepon, '+', ''), ' ', ''), '-', '') = ?", [$candidate]);
                    }
                })
                ->latest()
                ->first();

            if (! $this->pengajuan) {
                $this->addError('search', __('Tidak ada pengajuan dengan nomor telepon tersebut.'));
            }
        } else {
            $this->pengajuan = \App\Models\PengajuanRintekPertek::query()
                ->whereRaw('UPPER(nomor_pengajuan) = ?', [strtoupper($value)])
                ->first();

            if (! $this->pengajuan) {
                $this->addError('search', __('Nomor pengajuan tidak ditemukan.'));
            }
        }
    }
};
?>

<div class="space-y-6 ck-wrap">
    <div class="ck-card max-w-4xl mx-auto">
        <div class="ck-card-head">
            <span class="ck-card-icon">
                <x-icons.ui name="search" />
            </span>
            <div class="flex-1">
                <h3 class="ck-card-title">{{ __('Cek Status Pengajuan RINTEK/PERTEK') }}</h3>
                <p class="ck-card-desc">{{ __('Masukkan nomor pengajuan atau nomor telepon yang Anda daftarkan untuk melihat status RINTEK/PERTEK.') }}</p>
            </div>
        </div>
        <form wire:submit="lookup" @if(\App\Support\Captcha::enabled()) data-dlh-recaptcha-action="lookup" @endif class="tracking-search-form">
            <div class="flex-1">
                <x-public.input
                    wire:model="search"
                    name="search"
                    placeholder="RPT-YYYY-XXXX atau 08123456789"
                    required
                />
            </div>

            <button type="submit" class="ck-search-btn md:w-auto">
                <x-icons.ui name="search" class="h-4 w-4" />
                {{ __('Cari Pengajuan') }}
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

    @if ($pengajuan)
        <div class="ck-result-card max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-6">
                <div class="flex items-center gap-3">
                    <span class="ck-result-icon">
                        <x-icons.ui name="document" />
                    </span>
                    <div>
                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 font-bold tracking-widest uppercase">{{ __('Nomor Pengajuan') }}</span>
                        <h2 class="text-2xl font-bold font-mono text-slate-900 dark:text-slate-100">{{ $pengajuan->nomor_pengajuan }}</h2>
                    </div>
                </div>
                @php
                    $statusColor = $pengajuan->status?->color() ?? 'gray';
                    $badgeMap = [
                        'warning' => 'ck-status-badge--pending',
                        'info' => 'ck-status-badge--info',
                        'success' => 'ck-status-badge--done',
                        'danger' => 'ck-status-badge--rejected',
                        'gray' => 'ck-status-badge--pending',
                    ];
                    $isDone = in_array($statusColor, ['success']);
                    $isProcessed = $statusColor !== 'warning' && $statusColor !== 'gray';
                @endphp
                <span class="ck-status-badge {{ $badgeMap[$statusColor] ?? 'ck-status-badge--pending' }}">
                    @if ($isDone)
                        <x-icons.ui name="check" class="h-3 w-3" />
                    @else
                        <span class="ck-status-dot"></span>
                    @endif
                    {{ $pengajuan->status?->label() ?? $pengajuan->status }}
                </span>
            </div>

            <x-public.ticket-feedback :ticket="$pengajuan" />

            <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($pengajuan)" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-6">
                @php
                    $infoItems = [
                        __('Nama Perusahaan') => $pengajuan->nama_perusahaan,
                        __('Jenis Pengajuan') => $pengajuan->jenis_pengajuan,
                        __('Nama Penanggung Jawab') => $pengajuan->nama_penanggung_jawab,
                        __('Jenis Usaha') => $pengajuan->jenis_usaha,
                        __('Tanggal Pengajuan') => $pengajuan->created_at->format('d M Y H:i'),
                    ];
                @endphp
                @foreach ($infoItems as $label => $value)
                    <div class="ck-info-tile">
                        <span class="ck-info-label">{{ $label }}</span>
                        <span class="ck-info-value">{{ $value }}</span>
                    </div>
                @endforeach
            </div>

            @if ($isProcessed && filled($pengajuan->catatan_verifikasi))
                <div class="ck-note-box mt-5">
                    <span class="ck-note-label">
                        <x-icons.ui name="message" class="h-4 w-4" />
                        {{ __('Catatan Admin') }}
                    </span>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $pengajuan->catatan_verifikasi }}</p>
                </div>
            @endif
        </div>
    @endif

    <style>

        .ck-wrap { font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif; }

        .ck-card {
            background: #fff;
            border: 1px solid #e8efe9;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(13,43,29,0.04), 0 8px 24px -12px rgba(13,43,29,0.08);
            transition: box-shadow .18s ease, border-color .18s ease;
        }
        .ck-card:hover {
            border-color: #c3d8cc;
            box-shadow: 0 4px 12px -4px rgba(13,43,29,0.08), 0 16px 36px -16px rgba(13,43,29,0.12);
        }

        .ck-card-head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px; }

        .ck-card-icon {
            flex-shrink: 0; width: 40px; height: 40px; border-radius: 12px;
            background: linear-gradient(135deg, #178a53, #146a44);
            color: #fff; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 10px -2px rgba(20, 106, 68, 0.35);
        }
        .ck-card-icon svg { width: 18px; height: 18px; }

        .ck-card-title { font-size: 15px; font-weight: 700; color: #12201a; line-height: 1.3; }
        .ck-card-desc { font-size: 12px; color: #5b6b63; margin-top: 2px; line-height: 1.4; }

        .ck-search-btn {
            height: 48px; padding: 0 24px; border: none; border-radius: 9999px;
            background: linear-gradient(180deg, #178a53, #146a44); color: #fff;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            font-size: 14px; font-weight: 700; cursor: pointer;
            box-shadow: 0 8px 20px -6px rgba(20, 106, 68, 0.5);
            transition: transform .12s ease, box-shadow .12s ease;
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            white-space: nowrap;
        }
        .ck-search-btn:hover { transform: translateY(-1px); box-shadow: 0 12px 24px -6px rgba(20, 106, 68, 0.55); }

        .ck-result-card {
            background: #fff; border: 1px solid #e8efe9; border-radius: 20px;
            padding: 24px; box-shadow: 0 1px 3px rgba(13,43,29,0.04), 0 8px 24px -12px rgba(13,43,29,0.08);
        }

        .ck-result-icon {
            flex-shrink: 0; width: 42px; height: 42px; border-radius: 12px;
            background: #e6f5ec; color: #146a44;
            display: flex; align-items: center; justify-content: center;
        }
        .ck-result-icon svg { width: 20px; height: 20px; }

        .ck-status-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 9999px;
            font-size: 12px; font-weight: 700; border: 1px solid transparent;
        }
        .ck-status-badge--done { background: #dcfce7; color: #166534; border-color: #86efac; }
        .ck-status-badge--pending { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .ck-status-badge--info { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
        .ck-status-badge--rejected { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .ck-status-dot {
            display: inline-block; width: 6px; height: 6px; border-radius: 9999px;
            background: currentColor; animation: ck-pulse 1.6s ease-in-out infinite;
        }
        @keyframes ck-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

        .ck-info-tile {
            background: #f8faf9; border: 1px solid #e8efe9; border-radius: 14px; padding: 14px 16px;
        }
        .ck-info-label {
            display: block; font-size: 11px; font-weight: 600; color: #5b6b63;
            text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;
        }
        .ck-info-value { display: block; font-size: 14px; font-weight: 600; color: #12201a; line-height: 1.4; }

        .ck-note-box {
            padding: 14px 16px; background: #f4faf6; border: 1px solid #d1e7da;
            border-radius: 14px; border-left: 3px solid #1ea567;
        }
        .ck-note-label {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 700; color: #146a44;
        }

        /* ── Dark mode ── */
        .dark .ck-card { background: #1e293b; border-color: #334155; }
        .dark .ck-card:hover { border-color: #475569; }
        .dark .ck-card-title { color: #e2e8f0; }
        .dark .ck-card-desc { color: #94a3b8; }
        .dark .ck-result-card { background: #1e293b; border-color: #334155; }
        .dark .ck-result-icon { background: rgba(30,165,103,0.15); color: #1ea567; }
        .dark .ck-status-badge--done { background: rgba(16,185,129,0.15); color: #6ee7b7; border-color: rgba(16,185,129,0.3); }
        .dark .ck-status-badge--pending { background: rgba(245,158,11,0.15); color: #fcd34d; border-color: rgba(245,158,11,0.3); }
        .dark .ck-status-badge--info { background: rgba(59,130,246,0.15); color: #93c5fd; border-color: rgba(59,130,246,0.3); }
        .dark .ck-status-badge--rejected { background: rgba(239,68,68,0.15); color: #fca5a5; border-color: rgba(239,68,68,0.3); }
        .dark .ck-info-tile { background: #0f172a; border-color: #334155; }
        .dark .ck-info-label { color: #94a3b8; }
        .dark .ck-info-value { color: #e2e8f0; }
        .dark .ck-note-box { background: rgba(30,165,103,0.08); border-color: rgba(30,165,103,0.25); }
        .dark .ck-note-label { color: #6ee7b7; }
    </style>
</div>
