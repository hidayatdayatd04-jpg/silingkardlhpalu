<?php

use App\Models\PermohonanRekomendasi;
use Livewire\Component;

new class extends Component
{
    public string $searchEmail = '';
    public string $searchPhone = '';
    public array $permohonans = [];

    public function searchByEmail()
    {
        $this->validate(['searchEmail' => 'required|email']);

        $this->permohonans = PermohonanRekomendasi::query()
            ->where('email', trim($this->searchEmail))
            ->latest()
            ->get()
            ->all();

        if (empty($this->permohonans)) {
            $this->addError('searchEmail', __('Tidak ada permohonan dengan email tersebut.'));
        } else {
            $this->resetErrorBag('searchEmail');
        }
    }

    public function searchByPhone()
    {
        $this->validate(['searchPhone' => 'required|string']);

        $this->permohonans = PermohonanRekomendasi::query()
            ->where('nomor_telepon', trim($this->searchPhone))
            ->latest()
            ->get()
            ->all();

        if (empty($this->permohonans)) {
            $this->addError('searchPhone', __('Tidak ada permohonan dengan nomor telepon tersebut.'));
        } else {
            $this->resetErrorBag('searchPhone');
        }
    }
};
?>

<div class="space-y-6 ck-wrap">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-4xl mx-auto">
        {{-- Cek via Email --}}
        <div class="ck-card">
            <div class="ck-card-head">
                <span class="ck-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg>
                </span>
                <div>
                    <h3 class="ck-card-title">{{ __('Cek via Email') }}</h3>
                    <p class="ck-card-desc">{{ __('Cari semua riwayat permohonan menggunakan email pemohon.') }}</p>
                </div>
            </div>
            <form wire:submit.prevent="searchByEmail" class="space-y-3">
                <x-public.input
                    wire:model="searchEmail"
                    name="searchEmail"
                    type="email"
                    placeholder="email@perusahaan.com"
                    required
                />
                <button type="submit" class="ck-search-btn">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    {{ __('Cari Riwayat') }}
                </button>
            </form>
        </div>

        {{-- Cek via Nomor Telepon --}}
        <div class="ck-card">
            <div class="ck-card-head">
                <span class="ck-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                </span>
                <div>
                    <h3 class="ck-card-title">{{ __('Cek via Nomor Telepon') }}</h3>
                    <p class="ck-card-desc">{{ __('Cari semua riwayat permohonan menggunakan nomor telepon pemohon.') }}</p>
                </div>
            </div>
            <form wire:submit.prevent="searchByPhone" class="space-y-3">
                <x-public.input
                    wire:model="searchPhone"
                    name="searchPhone"
                    type="tel"
                    placeholder="08123456789"
                    required
                />
                <button type="submit" class="ck-search-btn">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    {{ __('Cari Riwayat') }}
                </button>
            </form>
        </div>
    </div>

    @if (! empty($permohonans))
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center gap-3 pt-2">
                <span class="ck-count-badge">{{ count($permohonans) }}</span>
                <h3 class="text-lg font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ __('Riwayat Permohonan') }}</h3>
            </div>
            @foreach ($permohonans as $permohonan)
                <div class="ck-result-card">
                    <div class="flex flex-wrap items-start justify-between gap-4 pb-5 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-start gap-3">
                            <span class="ck-result-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                            </span>
                            <div>
                                <span class="block text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-widest font-extrabold">{{ __('Nomor Tiket') }}</span>
                                <x-public.copy-ticket :ticket="$permohonan->nomor_tiket" class="font-mono font-bold text-lg text-slate-900 dark:text-slate-100" />
                                <p class="text-sm text-slate-600 dark:text-slate-400 mt-0.5">{{ $permohonan->nama_perusahaan }} <span class="text-slate-300 dark:text-slate-600">•</span> {{ $permohonan->jenis_usaha }}</p>
                            </div>
                        </div>
                        @php
                            $isDone = $permohonan->status === 'Ditindaklanjuti' || $permohonan->status === 'Selesai';
                        @endphp
                        <span class="ck-status-badge {{ $isDone ? 'ck-status-badge--done' : 'ck-status-badge--pending' }}">
                            @if ($isDone)
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            @else
                                <span class="ck-status-dot"></span>
                            @endif
                            {{ $permohonan->status }}
                        </span>
                    </div>

                    <x-public.ticket-feedback :ticket="$permohonan" />

                    <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($permohonan)" />

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5 text-sm">
                        <div>
                            <span class="block text-xs text-slate-500 dark:text-slate-400 font-medium mb-0.5">{{ __('Jenis Pengajuan') }}</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $permohonan->jenis_pengajuan }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-500 dark:text-slate-400 font-medium mb-0.5">{{ __('Tanggal Pengajuan') }}</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $permohonan->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="block text-xs text-slate-500 dark:text-slate-400 font-medium mb-0.5">{{ __('Catatan Verifikasi') }}</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $permohonan->catatan_verifikasi ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="mt-5">
                        <a href="{{ url('/permohonan-rekomendasi/'.$permohonan->nomor_tiket.'/bukti-pdf') }}"
                            class="ck-download-link">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                            {{ __('Unduh Bukti PDF') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap');

        .ck-wrap {
            font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif;
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
            width: 100%;
            height: 46px;
            border: none;
            border-radius: 9999px;
            background: linear-gradient(180deg, #178a53, #146a44);
            color: #fff;
            font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 20px -6px rgba(20, 106, 68, 0.5);
            transition: transform .12s ease, box-shadow .12s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
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
