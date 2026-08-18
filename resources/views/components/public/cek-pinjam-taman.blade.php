<?php

use App\Models\PermohonanPinjamTaman;
use Livewire\Component;

new class extends Component
{
    public string $searchTicket = '';
    public string $searchNama = '';
    public ?PermohonanPinjamTaman $permohonan = null;

    public function searchByTicket()
    {
        $this->validate(['searchTicket' => 'required|string']);

        $this->permohonan = PermohonanPinjamTaman::query()
            ->where('nomor_tiket', trim($this->searchTicket))
            ->first();

        if (! $this->permohonan) {
            $this->addError('searchTicket', __('Nomor tiket tidak ditemukan.'));
        } else {
            $this->resetErrorBag('searchTicket');
        }
    }

    public function searchByNama()
    {
        $this->validate(['searchNama' => 'required|string|min:3']);

        $this->permohonan = PermohonanPinjamTaman::query()
            ->where('nama_pemohon', 'like', '%'.trim($this->searchNama).'%')
            ->latest()
            ->first();

        if (! $this->permohonan) {
            $this->addError('searchNama', __('Permohonan dengan nama pemohon tersebut tidak ditemukan.'));
        } else {
            $this->resetErrorBag('searchNama');
        }
    }
};
?>

<div class="space-y-6 ck-wrap">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-4xl mx-auto">
        {{-- Cek via Nomor Tiket --}}
        <div class="ck-card">
            <div class="ck-card-head">
                <span class="ck-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </span>
                <div>
                    <h3 class="ck-card-title">{{ __('Cek via Nomor Tiket') }}</h3>
                    <p class="ck-card-desc">{{ __('Masukkan nomor tiket penyewaan yang Anda terima.') }}</p>
                </div>
            </div>
            <form wire:submit.prevent="searchByTicket" class="space-y-3">
                <x-public.input
                    wire:model="searchTicket"
                    name="searchTicket"
                    placeholder="{{ __('Nomor tiket') }}"
                    required
                />
                <button type="submit" class="ck-search-btn">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    {{ __('Cari Tiket') }}
                </button>
            </form>
        </div>

        {{-- Cek via Nama Pemohon --}}
        <div class="ck-card">
            <div class="ck-card-head">
                <span class="ck-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                <div>
                    <h3 class="ck-card-title">{{ __('Cek via Nama Pemohon') }}</h3>
                    <p class="ck-card-desc">{{ __('Cari permohonan berdasarkan nama pemohon/komunitas.') }}</p>
                </div>
            </div>
            <form wire:submit.prevent="searchByNama" class="space-y-3">
                <x-public.input
                    wire:model="searchNama"
                    name="searchNama"
                    placeholder="{{ __('Nama pemohon/komunitas') }}"
                    required
                />
                <button type="submit" class="ck-search-btn">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    {{ __('Cari via Nama') }}
                </button>
            </form>
        </div>
    </div>

    @if ($permohonan)
        <div class="ck-result-card max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-6">
                <div class="flex items-center gap-3">
                    <span class="ck-result-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                    </span>
                    <div>
                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 font-bold tracking-widest uppercase">{{ __('Nomor Tiket') }}</span>
                        <x-public.copy-ticket :ticket="$permohonan->nomor_tiket" class="text-2xl font-bold font-mono text-slate-900 dark:text-slate-100" />
                    </div>
                </div>
                @php
                    $statusColor = $permohonan->status?->color() ?? 'gray';
                    $badgeMap = [
                        'gray' => 'ck-status-badge--pending',
                        'success' => 'ck-status-badge--done',
                        'warning' => 'ck-status-badge--pending',
                    ];
                    $isDone = in_array($statusColor, ['success']);
                @endphp
                <span class="ck-status-badge {{ $badgeMap[$statusColor] ?? 'ck-status-badge--pending' }}">
                    @if ($isDone)
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    @else
                        <span class="ck-status-dot"></span>
                    @endif
                    {{ $permohonan->status?->label() ?? $permohonan->status }}
                </span>
            </div>

            <x-public.ticket-feedback :ticket="$permohonan" />

            <x-public.status-timeline :timeline="\App\Services\TicketTimelineService::forTicket($permohonan)" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-6">
                @php
                    $infoItems = [
                        __('Nama Pemohon') => $permohonan->nama_pemohon,
                        __('Nama Kegiatan') => $permohonan->nama_kegiatan,
                        __('Taman') => $permohonan->nama_taman ?? '-',
                        __('Tanggal Pengajuan') => $permohonan->created_at->format('d M Y H:i'),
                        __('Tanggal Kegiatan') => $permohonan->tanggal_kegiatan->format('d M Y H:i'),
                        __('Tanggal Selesai') => $permohonan->tanggal_selesai?->format('d M Y H:i') ?? '-',
                    ];
                @endphp
                @foreach ($infoItems as $label => $value)
                    <div class="ck-info-tile">
                        <span class="ck-info-label">{{ $label }}</span>
                        <span class="ck-info-value">{{ $value }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Keamanan: catatan_admin adalah catatan internal petugas dan tidak
                 ditampilkan di kanal publik untuk mencegah kebocoran informasi. --}}
        </div>
    @endif

    <style>

        .ck-wrap { font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif; }

        .ck-card {
            background: #fff; border: 1px solid #e8efe9; border-radius: 20px; padding: 24px;
            box-shadow: 0 1px 3px rgba(13,43,29,0.04), 0 8px 24px -12px rgba(13,43,29,0.08);
            transition: box-shadow .18s ease, border-color .18s ease;
        }
        .ck-card:hover { border-color: #c3d8cc; box-shadow: 0 4px 12px -4px rgba(13,43,29,0.08), 0 16px 36px -16px rgba(13,43,29,0.12); }

        .ck-card-head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px; }
        .ck-card-icon {
            flex-shrink: 0; width: 40px; height: 40px; border-radius: 12px;
            background: linear-gradient(135deg, #178a53, #146a44); color: #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 10px -2px rgba(20, 106, 68, 0.35);
        }
        .ck-card-icon svg { width: 18px; height: 18px; }
        .ck-card-title { font-size: 15px; font-weight: 700; color: #12201a; line-height: 1.3; }
        .ck-card-desc { font-size: 12px; color: #5b6b63; margin-top: 2px; line-height: 1.4; }

        .ck-search-btn {
            width: 100%; height: 46px; border: none; border-radius: 9999px;
            background: linear-gradient(180deg, #178a53, #146a44); color: #fff;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            font-size: 13.5px; font-weight: 700; cursor: pointer;
            box-shadow: 0 8px 20px -6px rgba(20, 106, 68, 0.5);
            transition: transform .12s ease, box-shadow .12s ease;
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        }
        .ck-search-btn:hover { transform: translateY(-1px); box-shadow: 0 12px 24px -6px rgba(20, 106, 68, 0.55); }

        .ck-result-card {
            background: #fff; border: 1px solid #e8efe9; border-radius: 20px; padding: 24px;
            box-shadow: 0 1px 3px rgba(13,43,29,0.04), 0 8px 24px -12px rgba(13,43,29,0.08);
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
        .dark .ck-info-tile { background: #0f172a; border-color: #334155; }
        .dark .ck-info-label { color: #94a3b8; }
        .dark .ck-info-value { color: #e2e8f0; }
        .dark .ck-note-box { background: rgba(30,165,103,0.08); border-color: rgba(30,165,103,0.25); }
        .dark .ck-note-label { color: #6ee7b7; }
    </style>
</div>
