<?php

use App\Enums\StatusPermohonanPohon;
use App\Livewire\Concerns\ThrottlesPublic;
use App\Livewire\Concerns\VerifiesGoogleRecaptcha;
use App\Models\PermohonanPohon;
use Livewire\Component;

new class extends Component
{
    use VerifiesGoogleRecaptcha;
    use ThrottlesPublic;

    public string $search = '';
    public ?PermohonanPohon $permohonan = null;

    public function mount(): void
    {
        if (request()->has('tiket')) {
            $this->search = trim((string) request()->query('tiket'));
            $this->performLookup();
        }
    }

    private function normalizePhoneCandidates(string $digits): array
    {
        if ($digits === '') {
            return [];
        }

        $candidates = [$digits];

        if (str_starts_with($digits, '0')) {
            $candidates[] = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '62')) {
            $candidates[] = '0' . substr($digits, 2);
        }

        return array_values(array_unique($candidates));
    }

    public function lookup(): void
    {
        if (! $this->verifyCaptcha('lookup')) {
            return;
        }

        $this->resetCaptcha();
        $this->performLookup();
    }

    private function performLookup(): void
    {
        $this->validate(['search' => 'required|string']);

        if ($this->hitRateLimit('cek-permohonan-pohon:search', 20, 'form', __('Batas pencarian tercapai (maksimal 20 kali per jam).'))) {
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

            $this->permohonan = PermohonanPohon::query()
                ->where(function ($query) use ($candidates): void {
                    foreach ($candidates as $candidate) {
                        $query->orWhere('nomor_hp', $candidate)
                            ->orWhereRaw("REPLACE(REPLACE(REPLACE(nomor_hp, '+', ''), ' ', ''), '-', '') = ?", [$candidate]);
                    }
                })
                ->latest()
                ->first();

            if (! $this->permohonan) {
                $this->addError('search', __('Tidak ada permohonan dengan nomor telepon tersebut.'));
            }
        } else {
            $this->permohonan = PermohonanPohon::query()
                ->whereRaw('UPPER(nomor_tiket) = ?', [strtoupper($value)])
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
                <h3 class="ck-card-title">{{ __('Cek Status Permohonan Penebangan / Pemangkasan Pohon') }}</h3>
                <p class="ck-card-desc">{{ __('Masukkan nomor tiket (contoh: PHN-XXXX-XXXX) atau nomor WhatsApp yang Anda gunakan saat mengajukan permohonan.') }}</p>
            </div>
        </div>

        <form wire:submit.prevent="lookup" @if(\App\Support\Captcha::enabled()) data-dlh-recaptcha-action="lookup" @endif class="tracking-search-form">
            <div class="flex-1">
                <x-public.input
                    wire:model="search"
                    name="search"
                    placeholder="{{ __('Nomor tiket (PHN-...) atau nomor WhatsApp') }}"
                    required
                />
            </div>

            <button type="submit" class="ck-search-btn md:w-auto">
                <x-icons.ui name="search" class="h-4 w-4" />
                <span>{{ __('Cari Permohonan') }}</span>
            </button>
        </form>

        @error('search')
            <p class="mt-2 text-xs font-semibold text-rose-500">{{ $message }}</p>
        @enderror

        @error('form')
            <div class="dlh-limit-alert mt-3" role="alert">
                <x-icons.ui name="alert" />
                <span>{{ $message }}</span>
            </div>
        @enderror

        <x-google-recaptcha />
    </div>

    @if ($permohonan)
        @php
            $status = $permohonan->status;
            $statusValue = $status instanceof \BackedEnum ? $status->value : (string) $status;
            $stepIndex = $status instanceof \App\Enums\StatusPermohonanPohon ? $status->stepIndex() : 1;
            $isDone = ($statusValue === 'Selesai');
            $isRejected = ($statusValue === 'Ditolak');

            $fotoSebelum = $permohonan->getFotoSebelumList();
            $fotoSesudah = $permohonan->getFotoSesudahList();
        @endphp

        <div class="ck-result-card max-w-4xl mx-auto space-y-6">
            {{-- Card Header --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-6">
                <div class="flex items-center gap-3">
                    <span class="ck-result-icon">
                        <x-icons.ui name="axe" />
                    </span>
                    <div>
                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 font-bold tracking-widest uppercase">{{ __('Nomor Tiket Permohonan') }}</span>
                        <x-public.copy-ticket :ticket="$permohonan->nomor_tiket" class="text-2xl font-bold font-mono text-slate-900 dark:text-slate-100" />
                    </div>
                </div>

                <span class="ck-status-badge @if($isDone) ck-status-badge--done @elseif($isRejected) ck-status-badge--danger @else ck-status-badge--pending @endif">
                    @if ($isDone)
                        <x-icons.ui name="check" class="h-3.5 w-3.5" />
                    @elseif ($isRejected)
                        <x-icons.ui name="close" class="h-3.5 w-3.5" />
                    @else
                        <span class="ck-status-dot"></span>
                    @endif
                    <span>{{ $statusValue }}</span>
                </span>
            </div>

            {{-- Tahapan Progres Alur Kerja --}}
            <div>
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">{{ __('Alur Kerja Penanganan Pohon') }}</span>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2 text-center text-xs">
                    @foreach([
                        1 => ['label' => 'Diajukan', 'desc' => 'Laporan masuk'],
                        2 => ['label' => 'Verifikasi', 'desc' => 'Cek area publik'],
                        3 => ['label' => 'Survei', 'desc' => 'Observasi fisik'],
                        4 => ['label' => $isRejected ? 'Ditolak' : 'Disetujui', 'desc' => $isRejected ? 'Bukan fasum' : 'Disetujui'],
                        5 => ['label' => 'Jadwal', 'desc' => 'Penetapan regu'],
                        6 => ['label' => 'Eksekusi', 'desc' => 'Penebangan'],
                        7 => ['label' => 'Selesai', 'desc' => 'Tuntas'],
                    ] as $stepIdx => $st)
                        @php
                            $stepDone = ($stepIndex > $stepIdx) || ($stepIndex === 7 && $stepIdx === 7);
                            $stepCurrent = ($stepIndex === $stepIdx);
                            $stepIsRejected = ($isRejected && $stepIdx === 4);
                        @endphp
                        <div class="p-2.5 rounded-xl border transition-all
                            @if($stepIsRejected) border-rose-300 bg-rose-50 text-rose-700 dark:border-rose-900 dark:bg-rose-950/30
                            @elseif($stepCurrent) border-emerald-500 bg-emerald-50 text-emerald-800 dark:border-emerald-600 dark:bg-emerald-950/40 font-bold
                            @elseif($stepDone) border-emerald-200 bg-emerald-50/40 text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/20
                            @else border-slate-100 bg-slate-50/50 text-slate-400 dark:border-slate-800 dark:bg-slate-800/40 @endif
                        ">
                            <span class="block text-[11px] font-bold">
                                @if($stepDone) ✓ @elseif($stepIsRejected) ✕ @else {{ $stepIdx }} @endif
                            </span>
                            <span class="block mt-0.5 truncate font-semibold">{{ $st['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Kotak Peringatan Jika Ditolak --}}
            @if ($isRejected)
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 dark:bg-rose-950/30 dark:border-rose-900/60 space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-800 dark:text-rose-300 flex items-center gap-1.5">
                        <x-icons.ui name="alert-triangle" class="size-4" />
                        {{ __('Permohonan Ditolak') }}
                    </span>
                    <p class="text-sm text-rose-700 dark:text-rose-300 leading-relaxed">
                        {{ $permohonan->alasan_penolakan ?: __('Pohon yang dimohonkan teridentifikasi berada di pekarangan / area pribadi, sehingga berada di luar kewenangan operasional DLH Kota Palu.') }}
                    </p>
                </div>
            @endif

            {{-- Detail Rincian Permohonan --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @php
                    $infoItems = [
                        __('Nama Pelapor') => $permohonan->nama_pelapor,
                        __('Nomor WhatsApp') => $permohonan->nomor_hp,
                        __('Jenis Tindakan') => $permohonan->jenis_tindakan?->value ?? 'Pemangkasan',
                        __('Jenis Pohon') => filled($permohonan->jenis_pohon) ? $permohonan->jenis_pohon : '-',
                        __('Lokasi Pohon (Fasum)') => $permohonan->lokasi_pohon,
                        __('Tanggal Pengajuan') => $permohonan->created_at?->translatedFormat('d M Y H:i'),
                    ];
                @endphp
                @foreach ($infoItems as $label => $value)
                    <div class="ck-info-tile">
                        <span class="ck-info-label">{{ $label }}</span>
                        <span class="ck-info-value">{{ $value }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Alasan Pengajuan Warga --}}
            <div class="ck-info-tile">
                <span class="ck-info-label">{{ __('Alasan Pengajuan Tindakan') }}</span>
                <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed mt-1">{{ $permohonan->alasan_pengajuan }}</p>
            </div>

            {{-- Catatan Hasil Survei & Pelaksanaan DLH --}}
            @if(filled($permohonan->catatan_verifikasi) || filled($permohonan->kondisi_pohon) || filled($permohonan->rekomendasi_tindakan) || $permohonan->tanggal_pelaksanaan)
                <div class="p-5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">
                        {{ __('Informasi Penanganan Petugas DLH') }}
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm">
                        @if(filled($permohonan->catatan_verifikasi))
                            <div>
                                <span class="text-slate-400 font-semibold block text-[11px]">{{ __('Verifikasi Area') }}</span>
                                <p class="font-medium text-slate-800 dark:text-slate-200 mt-0.5">{{ $permohonan->catatan_verifikasi }}</p>
                            </div>
                        @endif

                        @if($permohonan->tanggal_survei)
                            <div>
                                <span class="text-slate-400 font-semibold block text-[11px]">{{ __('Tanggal Survei Lapangan') }}</span>
                                <p class="font-medium text-slate-800 dark:text-slate-200 mt-0.5">{{ $permohonan->tanggal_survei->translatedFormat('d F Y') }} ({{ $permohonan->petugas_survei ?: 'Tim RTH' }})</p>
                            </div>
                        @endif

                        @if(filled($permohonan->kondisi_pohon))
                            <div>
                                <span class="text-slate-400 font-semibold block text-[11px]">{{ __('Kondisi Fisik Pohon') }}</span>
                                <p class="font-medium text-slate-800 dark:text-slate-200 mt-0.5">{{ $permohonan->kondisi_pohon }}</p>
                            </div>
                        @endif

                        @if(filled($permohonan->rekomendasi_tindakan))
                            <div>
                                <span class="text-slate-400 font-semibold block text-[11px]">{{ __('Rekomendasi Tindakan') }}</span>
                                <p class="font-medium text-slate-800 dark:text-slate-200 mt-0.5">{{ $permohonan->rekomendasi_tindakan }}</p>
                            </div>
                        @endif

                        @if($permohonan->tanggal_pelaksanaan)
                            <div class="sm:col-span-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                                <span class="text-slate-400 font-semibold block text-[11px]">{{ __('Jadwal Pelaksanaan Eksekusi') }}</span>
                                <p class="font-bold text-emerald-700 dark:text-emerald-400 text-sm mt-0.5">
                                    {{ $permohonan->tanggal_pelaksanaan->translatedFormat('d F Y') }}
                                    @if(filled($permohonan->tim_pelaksana)) — {{ $permohonan->tim_pelaksana }} @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Dokumentasi Selesai (Foto Sesudah) --}}
            @if(count($fotoSesudah) > 0)
                <div class="space-y-3 pt-2">
                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Dokumentasi Hasil Pekerjaan di Lokasi') }}</span>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($fotoSesudah as $item)
                            <div class="aspect-square rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800">
                                <img src="{{ $item['url'] }}" alt="Hasil Pekerjaan" class="size-full object-cover" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <a
                    href="{{ url('/penebangan-pohon') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-slate-800 transition"
                >
                    <x-icons.ui name="arrow-left" class="size-4" />
                    <span>{{ __('Ajukan Permohonan Pohon Baru') }}</span>
                </a>
            </div>
        </div>
    @endif

    <style>
        .ck-wrap { font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif; }
        .ck-card {
            background: #fff; border: 1px solid #e8efe9; border-radius: 20px; padding: 24px;
            box-shadow: 0 1px 3px rgba(13,43,29,0.04), 0 8px 24px -12px rgba(13,43,29,0.08);
            transition: box-shadow .18s ease, border-color .18s ease;
        }
        .dark .ck-card { background: #0f172a; border-color: #1e293b; }
        .ck-card:hover { border-color: #c3d8cc; box-shadow: 0 4px 12px -4px rgba(13,43,29,0.08), 0 16px 36px -16px rgba(13,43,29,0.12); }
        .dark .ck-card:hover { border-color: #334155; }

        .ck-card-head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px; }
        .ck-card-icon {
            flex-shrink: 0; width: 40px; height: 40px; border-radius: 12px;
            background: linear-gradient(135deg, #178a53, #146a44); color: #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 10px -2px rgba(20, 106, 68, 0.35);
        }
        .ck-card-icon svg { width: 18px; height: 18px; }
        .ck-card-title { font-size: 15px; font-weight: 700; color: #12201a; line-height: 1.3; }
        .dark .ck-card-title { color: #f8fafc; }
        .ck-card-desc { font-size: 12px; color: #5b6b63; margin-top: 2px; line-height: 1.4; }
        .dark .ck-card-desc { color: #94a3b8; }

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
            background: #fff; border: 1px solid #e8efe9; border-radius: 20px; padding: 24px;
            box-shadow: 0 1px 3px rgba(13,43,29,0.04), 0 8px 24px -12px rgba(13,43,29,0.08);
        }
        .dark .ck-result-card { background: #0f172a; border-color: #1e293b; }

        .ck-result-icon {
            flex-shrink: 0; width: 42px; height: 42px; border-radius: 12px;
            background: #e6f5ec; color: #146a44;
            display: flex; align-items: center; justify-content: center;
        }
        .dark .ck-result-icon { background: #064e3b; color: #6ee7b7; }
        .ck-result-icon svg { width: 20px; height: 20px; }

        .ck-status-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 9999px;
            font-size: 12px; font-weight: 700; border: 1px solid transparent;
        }
        .ck-status-badge--done { background: #dcfce7; color: #166534; border-color: #86efac; }
        .ck-status-badge--pending { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .ck-status-badge--danger { background: #ffe4e6; color: #9f1239; border-color: #fecdd3; }
        .dark .ck-status-badge--done { background: #064e3b; color: #6ee7b7; border-color: #047857; }
        .dark .ck-status-badge--pending { background: #78350f; color: #fde68a; border-color: #b45309; }
        .dark .ck-status-badge--danger { background: #881337; color: #fecdd3; border-color: #be123c; }

        .ck-status-dot {
            display: inline-block; width: 6px; height: 6px; border-radius: 9999px;
            background: currentColor; animation: ck-pulse 1.6s ease-in-out infinite;
        }
        @keyframes ck-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

        .ck-info-tile {
            background: #f8faf9; border: 1px solid #e8efe9; border-radius: 14px; padding: 14px 16px;
        }
        .dark .ck-info-tile { background: #1e293b; border-color: #334155; }
        .ck-info-label {
            display: block; font-size: 11px; font-weight: 600; color: #5b6b63;
            text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;
        }
        .dark .ck-info-label { color: #94a3b8; }
        .ck-info-value { display: block; font-size: 14px; font-weight: 600; color: #12201a; line-height: 1.4; }
        .dark .ck-info-value { color: #f8fafc; }
    </style>
</div>
