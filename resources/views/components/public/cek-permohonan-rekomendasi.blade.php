<?php

use App\Livewire\Concerns\ThrottlesPublic;
use App\Livewire\Concerns\VerifiesGoogleRecaptcha;
use App\Models\PermohonanRekomendasi;
use Livewire\Component;

new class extends Component
{
    use VerifiesGoogleRecaptcha;
    use ThrottlesPublic;

    public string $search = '';
    public ?PermohonanRekomendasi $permohonan = null;

    public function lookup()
    {
        if (! $this->verifyCaptcha('lookup')) {
            return;
        }

        $this->resetCaptcha();

        $this->validate(['search' => 'required|string']);

        if ($this->hitRateLimit('cek-permohonan-rekomendasi:search', 20, 'form', __('Batas pencarian tercapai (maksimal 20 kali per jam).'))) {
            return;
        }

        $value = trim($this->search);
        $isEmail = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;

        if ($isEmail) {
            $this->permohonan = PermohonanRekomendasi::query()
                ->whereRaw('LOWER(email) = ?', [mb_strtolower($value)])
                ->latest()
                ->first();

            if (! $this->permohonan) {
                $this->addError('search', __('Tidak ada permohonan dengan email tersebut.'));
            }
        } else {
            $this->permohonan = PermohonanRekomendasi::query()
                ->where('nomor_tiket', $value)
                ->first();

            if (! $this->permohonan) {
                $this->addError('search', __('Nomor tiket tidak ditemukan.'));
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
                <h3 class="ck-card-title">{{ __('Cek Status Permohonan Rekomendasi') }}</h3>
                <p class="ck-card-desc">{{ __('Masukkan nomor tiket atau email yang Anda daftarkan untuk melihat status permohonan.') }}</p>
            </div>
        </div>
        <form data-dlh-recaptcha-action="lookup" class="tracking-search-form">
            <div class="flex-1">
                <x-public.input
                    wire:model.live.debounce.250ms="search"
                    name="search"
                    placeholder="{{ __('Nomor tiket atau email') }}"
                    required
                />
            </div>

            <button type="submit" class="ck-search-btn md:w-auto">
                <x-icons.ui name="search" class="h-4 w-4" />
                {{ __('Cari Permohonan') }}
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

    @if ($permohonan)
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="ck-result-card">
                <div class="flex flex-wrap items-start justify-between gap-4 pb-5 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-start gap-3">
                        <span class="ck-result-icon">
                            <x-icons.ui name="document" />
                        </span>
                        <div>
                            <span class="block text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-widest font-bold">{{ __('Nomor Tiket') }}</span>
                            <x-public.copy-ticket :ticket="$permohonan->nomor_tiket" class="font-mono font-bold text-lg text-slate-900 dark:text-slate-100" />
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-0.5">{{ $permohonan->nama_perusahaan }} <span class="text-slate-300 dark:text-slate-600">•</span> {{ $permohonan->jenis_usaha }}</p>
                        </div>
                    </div>
                    @php
                        $statusValue = $permohonan->status instanceof \BackedEnum ? $permohonan->status->value : $permohonan->status;
                        $isDone = in_array($statusValue, ['Ditindaklanjuti', 'Selesai'], true);
                    @endphp
                    <span class="ck-status-badge {{ $isDone ? 'ck-status-badge--done' : 'ck-status-badge--pending' }}">
                        @if ($isDone)
                            <x-icons.ui name="check" class="h-3 w-3" />
                        @else
                            <span class="ck-status-dot"></span>
                        @endif
                        {{ $permohonan->status }}
                    </span>
                </div>

                <x-public.ticket-feedback :ticket="$permohonan" />

                <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($permohonan)" />

                @if ($isDone && filled($permohonan->catatan_verifikasi))
                    <div class="ck-note-box mt-5">
                        <span class="ck-note-label">
                            <x-icons.ui name="message" class="h-4 w-4" />
                            {{ __('Catatan Admin') }}
                        </span>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $permohonan->catatan_verifikasi }}</p>
                    </div>
                @endif
                <div class="grid grid-cols-2 gap-4 mt-5 text-sm">
                    <div>
                        <span class="block text-xs text-slate-500 dark:text-slate-400 font-medium mb-0.5">{{ __('Jenis Pengajuan') }}</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $permohonan->jenis_pengajuan }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-slate-500 dark:text-slate-400 font-medium mb-0.5">{{ __('Tanggal Pengajuan') }}</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $permohonan->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
                <div class="mt-5">
                    <a href="{{ url('/permohonan-rekomendasi/'.$permohonan->nomor_tiket.'/bukti-pdf') }}"
                        class="ck-download-link">
                        <x-icons.ui name="download" class="h-4 w-4" />
                        {{ __('Unduh Bukti PDF') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    <style>

        .ck-wrap {
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        /* ── Search Cards ── */
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

        .ck-card-head {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
        }

        .ck-card-icon {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #178a53, #146a44);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px -2px rgba(20, 106, 68, 0.35);
        }
        .ck-card-icon svg { width: 18px; height: 18px; }

        .ck-card-title {
            font-size: 15px;
            font-weight: 700;
            color: #12201a;
            line-height: 1.3;
        }
        .ck-card-desc {
            font-size: 12px;
            color: #5b6b63;
            margin-top: 2px;
            line-height: 1.4;
        }

        /* ── Search Button ── */
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
        .ck-search-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -6px rgba(20, 106, 68, 0.55);
        }

        /* ── Result List ── */
        .ck-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 28px;
            padding: 0 10px;
            border-radius: 9999px;
            background: linear-gradient(180deg, #178a53, #146a44);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 4px 10px -2px rgba(20, 106, 68, 0.4);
        }

        .ck-result-card {
            background: #fff;
            border: 1px solid #e8efe9;
            border-radius: 20px;
            padding: 22px 24px;
            box-shadow: 0 1px 3px rgba(13,43,29,0.04), 0 8px 24px -12px rgba(13,43,29,0.08);
            transition: box-shadow .18s ease;
        }
        .ck-result-card:hover {
            box-shadow: 0 4px 12px -4px rgba(13,43,29,0.08), 0 16px 36px -16px rgba(13,43,29,0.12);
        }

        .ck-result-icon {
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #e6f5ec;
            color: #146a44;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ck-result-icon svg { width: 20px; height: 20px; }

        .ck-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid transparent;
        }
        .ck-status-badge--done {
            background: #dcfce7;
            color: #166534;
            border-color: #86efac;
        }
        .ck-status-badge--pending {
            background: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }
        .ck-status-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 9999px;
            background: currentColor;
            animation: ck-pulse 1.6s ease-in-out infinite;
        }
        @keyframes ck-pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .ck-download-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 9999px;
            background: #f4faf6;
            color: #146a44;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid #d1e7da;
            transition: background .15s ease, border-color .15s ease, transform .12s ease;
        }
        .ck-download-link:hover {
            background: #e6f5ec;
            border-color: #1ea567;
            transform: translateY(-1px);
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
        .dark .ck-download-link { background: rgba(30,165,103,0.1); color: #6ee7b7; border-color: rgba(30,165,103,0.25); }
        .dark .ck-download-link:hover { background: rgba(30,165,103,0.2); }
    </style>
</div>
