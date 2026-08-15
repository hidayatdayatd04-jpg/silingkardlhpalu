@extends('layouts.admin')

@section('title', 'Dashboard Admin DLH Kota Palu')
@section('heading', 'Dashboard')

@section('content')
    @php
        $hour = (int) now()->format('H');
        $greet = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
        $user = auth()->user();
        $firstName = \Illuminate\Support\Str::of($user?->name ?? 'Admin')->explode(' ')->first();
        $initials = collect(explode(' ', $user?->name ?? 'A'))
            ->map(fn ($w) => mb_substr($w, 0, 1))
            ->take(2)
            ->join('');
        $roleName = $user?->roles?->first()?->name;
        $roleLabel = \App\Enums\AdminRole::tryFrom($roleName)?->label() ?? 'Admin';
    @endphp

    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-2xl border border-forest-800/40 text-white shadow-[var(--shadow-lift)]" style="background: var(--gradient-header-hero);">
        <div class="bg-grain pointer-events-none absolute inset-0 opacity-[0.05]"></div>
        <div class="pointer-events-none absolute -right-16 -top-20 size-64 rounded-full bg-brand-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 right-24 size-56 rounded-full bg-bay-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-10 top-1/2 size-48 rounded-full bg-emerald-500/10 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 p-7 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-brand-200">{{ $roleLabel }}</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-white sm:text-[2rem]">
                    {{ $greet }}, {{ $firstName }} <span class="inline-block animate-pulse">👋</span>
                </h1>
                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-white/75">
                    <span class="inline-flex items-center gap-1.5">
                        <x-admin.icon name="calendar" :size="16" class="text-brand-300" />
                        {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 font-mono text-xs font-semibold text-brand-100 backdrop-blur">
                        <x-admin.icon name="clock" :size="14" class="text-brand-300" />
                        <span x-data="{ t: '' }" x-init="const u=()=>t=new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'});u();setInterval(u,1000)" x-text="t"></span>
                        WITA
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-2xl bg-white/10 p-3 pr-5 backdrop-blur">
                <div class="grid size-14 shrink-0 place-items-center rounded-xl bg-white/15 text-lg font-extrabold tracking-tight text-white">
                    {{ strtoupper($initials) }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-white">{{ $user?->name ?? 'Admin' }}</p>
                    <p class="truncate text-xs text-white/60">{{ $user?->email }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- KPI Ringkasan (StatistikService) --}}
    @if($summary)
    <section class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-admin.kpi-strip label="Pengunjung Hari Ini" :value="$summary['pengunjung_hari_ini']" icon="users" color="bay" />
        <x-admin.kpi-strip label="Total Pengunjung" :value="$summary['total_pengunjung']" icon="chart" color="emerald" />
        <x-admin.kpi-strip label="Total Pelapor" :value="$summary['total_pelapor']" icon="megaphone" color="sky" />
        <x-admin.kpi-strip label="Total Pengajuan" :value="$summary['total_pengajuan']" icon="file-plus" color="amber" />
    </section>
    @endif

    {{-- Stat cards --}}
    <section class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach ($cards as $card)
            <div class="stagger-item" style="--reveal-delay: {{ $loop->index * 60 }}ms;">
                <x-admin.stat-card
                    :label="$card['label']"
                    :value="$card['value']"
                    :icon="$card['icon'] ?? 'folder'"
                    :color="$card['tone']"
                />
            </div>
        @endforeach
        @if(empty($cards))
            <div class="md:col-span-2 xl:col-span-4">
                <x-admin.card><p class="text-sm text-slate-500">Belum ada modul yang bisa ditampilkan untuk akun Anda.</p></x-admin.card>
            </div>
        @endif
    </section>

    {{-- Ringkasan Kinerja Penanganan (progress) --}}
    @if($statusStats && $statusStats['total'] > 0)
    <section class="mt-8">
        <div class="mb-4 flex items-center gap-3">
            <span class="grid size-9 place-items-center rounded-xl bg-brand-50 text-brand-600">
                <x-admin.icon name="clipboard-check" :size="18" />
            </span>
            <div>
                <h2 class="text-h3 font-bold text-ink-900">Ringkasan Kinerja Penanganan</h2>
                <p class="text-xs text-slate-500">Tingkat penyelesaian pengaduan secara keseluruhan</p>
            </div>
        </div>

        <x-admin.card :padding="false" class="overflow-hidden">
            <div class="grid gap-6 p-6 md:grid-cols-3">
                {{-- Progress utama --}}
                <div class="md:col-span-2">
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-sm font-semibold text-ink-500">Tingkat Penyelesaian</p>
                            <p class="mt-1 text-3xl font-extrabold tracking-tight text-ink-900">{{ $statusStats['selesai_pct'] }}<span class="text-xl text-ink-400">%</span></p>
                        </div>
                        <p class="text-xs text-slate-500">{{ number_format($statusStats['selesai'], 0, ',', '.') }} dari {{ number_format($statusStats['total'], 0, ',', '.') }} pengaduan selesai</p>
                    </div>
                    <div class="mt-4 h-3 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-emerald-400 transition-all duration-700" style="width: {{ $statusStats['selesai_pct'] }}%;"></div>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                            <p class="text-xl font-extrabold text-amber-600">{{ number_format($statusStats['belum'], 0, ',', '.') }}</p>
                            <p class="mt-0.5 text-xs font-medium text-slate-500">Belum Ditindak</p>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                            <p class="text-xl font-extrabold text-sky-600">{{ number_format($statusStats['proses'], 0, ',', '.') }}</p>
                            <p class="mt-0.5 text-xs font-medium text-slate-500">Diproses</p>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                            <p class="text-xl font-extrabold text-emerald-600">{{ number_format($statusStats['selesai'], 0, ',', '.') }}</p>
                            <p class="mt-0.5 text-xs font-medium text-slate-500">Selesai</p>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                            <p class="text-xl font-extrabold text-rose-600">{{ number_format($statusStats['ditolak'], 0, ',', '.') }}</p>
                            <p class="mt-0.5 text-xs font-medium text-slate-500">Ditolak</p>
                        </div>
                    </div>
                </div>

                {{-- Donut mini --}}
                <div class="relative">
                    <div class="relative mx-auto h-44 w-44">
                        <canvas id="chartStatusMini"></canvas>
                        <div class="pointer-events-none absolute inset-0 grid place-items-center">
                            <div class="text-center">
                                <p class="text-2xl font-extrabold text-ink-900">{{ number_format($statusStats['total'], 0, ',', '.') }}</p>
                                <p class="text-[11px] font-medium text-slate-500">Total</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-admin.card>
    </section>
    @endif

    {{-- Charts grid (12-col dense) --}}
    <section class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Tren pengaduan (8) --}}
        <x-admin.card class="lg:col-span-8">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-h4 font-bold text-ink-900">Tren Pengaduan 6 Bulan</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Jumlah pengaduan masuk per bulan</p>
                </div>
                <span class="grid size-10 place-items-center rounded-xl bg-brand-50 text-brand-600">
                    <x-admin.icon name="trending-up" :size="20" />
                </span>
            </div>
            <div class="relative h-64">
                <canvas id="chartTrend"></canvas>
            </div>
        </x-admin.card>

        {{-- Distribusi status (4) --}}
        <x-admin.card class="lg:col-span-4">
            <div class="mb-5">
                <h2 class="text-h4 font-bold text-ink-900">Distribusi Status</h2>
                <p class="mt-0.5 text-xs text-slate-500">Status pengaduan saat ini</p>
            </div>
            <div class="relative h-64">
                <canvas id="chartStatus"></canvas>
            </div>
        </x-admin.card>

        {{-- Jumlah per modul (8) --}}
        <x-admin.card class="lg:col-span-8">
            <div class="mb-5">
                <h2 class="text-h4 font-bold text-ink-900">Jumlah Data per Modul</h2>
                <p class="mt-0.5 text-xs text-slate-500">Total data pada tiap modul yang Anda akses</p>
            </div>
            <div class="relative h-72">
                <canvas id="chartModules"></canvas>
            </div>
        </x-admin.card>

        {{-- Performa status (4) --}}
        <x-admin.card class="lg:col-span-4">
            <div class="mb-5">
                <h2 class="text-h4 font-bold text-ink-900">Performa Penanganan</h2>
                <p class="mt-0.5 text-xs text-slate-500">Tugas selesai vs tertunda</p>
            </div>
            <div class="relative h-72">
                <canvas id="chartPerformance"></canvas>
            </div>
        </x-admin.card>

        {{-- Tren per bidang (12, bila >1 bidang) --}}
        @if(count($charts['trendPerBidang']['datasets'] ?? []) > 1)
        <x-admin.card class="lg:col-span-12">
            <div class="mb-5">
                <h2 class="text-h4 font-bold text-ink-900">Tren per Bidang</h2>
                <p class="mt-0.5 text-xs text-slate-500">Perbandingan pengaduan antar bidang</p>
            </div>
            <div class="relative w-full" style="height: 340px;">
                <canvas id="chartTrendBidang"></canvas>
            </div>
        </x-admin.card>
        @endif
    </section>

    {{-- Map + right rail --}}
    <section class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-12">
        @include('admin.partials.sebaran-pengaduan')

        {{-- Right rail --}}
        <div class="space-y-6 lg:col-span-4">
            {{-- Status sistem (superadmin only) --}}
            @if($activeUsers !== null || $visits !== null)
                <x-admin.card :padding="false" class="overflow-hidden text-white" style="background: var(--gradient-header-hero);">
                    <div class="relative p-6">
                        <div class="bg-grain pointer-events-none absolute inset-0 opacity-[0.05]"></div>
                        <div class="relative flex items-center gap-3">
                            <div class="grid size-12 place-items-center rounded-xl bg-white/10">
                                <x-admin.icon name="chart" :size="24" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-brand-200">Status Sistem</p>
                                <p class="text-xs text-white/60">Ringkasan</p>
                            </div>
                        </div>
                        <div class="relative mt-6 grid grid-cols-2 gap-3">
                            @if($activeUsers !== null)
                                <div class="rounded-lg bg-white/10 p-4 backdrop-blur">
                                    <p class="text-h1 font-extrabold"><x-admin.count-up :value="(int) $activeUsers" /></p>
                                    <p class="mt-1 text-xs text-brand-200">Admin Aktif</p>
                                </div>
                            @endif
                            @if($visits !== null)
                                <div class="rounded-lg bg-white/10 p-4 backdrop-blur">
                                    <p class="text-h1 font-extrabold"><x-admin.count-up :value="(int) $visits" /></p>
                                    <p class="mt-1 text-xs text-brand-200">Kunjungan</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </x-admin.card>
            @endif

            {{-- Tugas tertunda --}}
            @if($pendingTasks['total'] > 0)
                <x-admin.card :padding="false">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <div>
                            <h2 class="text-h4 font-bold text-ink-900">Perlu Tindakan</h2>
                            <p class="text-xs text-slate-500">Antrean verifikasi & penanganan</p>
                        </div>
                        <span class="grid size-9 place-items-center rounded-full bg-warning-100 text-warning-700">
                            <x-admin.icon name="alert-triangle" :size="18" />
                        </span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($pendingTasks['items'] as $task)
                            <a href="{{ $task['href'] }}" class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-slate-50">
                                <span class="min-w-0 flex-1 truncate text-sm font-medium text-ink-700">{{ $task['label'] }}</span>
                                <span class="shrink-0 rounded-full bg-warning-100 px-2.5 py-0.5 text-xs font-bold text-warning-700">{{ number_format($task['count'], 0, ',', '.') }}</span>
                            </a>
                        @endforeach
                    </div>
                </x-admin.card>
            @endif

            {{-- Tautan Cepat --}}
            <x-admin.card :padding="false">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-h4 font-bold text-ink-900">Tautan Cepat</h2>
                    <p class="text-xs text-slate-500">Akses cepat ke modul Anda</p>
                </div>
                <div class="p-3">
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($groups as $groupKey => $group)
                            @foreach(array_slice($group['items'], 0, 2) as $item)
                                <a href="{{ route('admin.resources.index', $item['slug']) }}" class="flex items-center gap-2 rounded-xl border border-slate-100 bg-slate-50/50 px-3 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200">
                                    <span class="size-1.5 rounded-full bg-emerald-400"></span>
                                    {{ \Illuminate\Support\Str::limit($item['label'], 20) }}
                                </a>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </x-admin.card>

            {{-- Aktivitas terbaru --}}
            @if($activityFeed->isNotEmpty())
                <x-admin.card :padding="false">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-h4 font-bold text-ink-900">Aktivitas Terbaru</h2>
                        <p class="text-xs text-slate-500">Log tindakan pengguna</p>
                    </div>
                    <div class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                        @foreach($activityFeed as $log)
                            @php $meta = $log->eventMeta(); @endphp
                            <div class="flex items-start gap-3 px-5 py-3">
                                <div class="grid size-8 shrink-0 place-items-center rounded-lg bg-{{ $meta['variant'] === 'default' ? 'slate' : $meta['variant'] }}-100 text-{{ $meta['variant'] === 'default' ? 'slate' : $meta['variant'] }}-600">
                                    <x-admin.icon :name="$meta['icon']" :size="15" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-ink-800">{{ $log->subject_label }}</p>
                                    <p class="text-xs text-slate-500">{{ $log->user_name }} • {{ $log->created_at?->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('admin.activity-log.index') }}" class="block border-t border-slate-100 px-5 py-3 text-center text-xs font-bold text-brand-600 hover:bg-slate-50">Lihat semua log</a>
                </x-admin.card>
            @else
                <x-admin.card :padding="false">
                    <div class="flex items-center gap-3 px-5 py-4">
                        <div class="grid size-10 place-items-center rounded-xl bg-slate-100 text-slate-400">
                            <x-admin.icon name="check-circle" :size="20" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink-700">Semua Terkini</p>
                            <p class="text-xs text-slate-500">Tidak ada aktivitas terbaru</p>
                        </div>
                    </div>
                </x-admin.card>
            @endif
        </div>
    </section>

    {{-- Data terbaru --}}
    @if(!empty($recent))
    <section x-data="{ activeTab: '{{ array_key_first($recent) }}' }" class="mt-8 space-y-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-h3 font-bold text-ink-900">Data Terbaru</h2>
                <p class="text-sm text-slate-500">Catatan terbaru pada modul yang Anda akses</p>
            </div>
            <div class="flex flex-wrap rounded-xl bg-slate-100 p-1">
                @if(isset($recent['laporan']))
                    <button x-on:click="activeTab = 'laporan'" :class="activeTab === 'laporan' ? 'bg-white shadow-sm text-brand-700' : 'text-slate-500 hover:text-slate-700'" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition">
                        <x-admin.icon name="megaphone" :size="14" />
                        Pengaduan
                        <span class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">{{ $recent['laporan']->count() }}</span>
                    </button>
                @endif
                @if(isset($recent['permohonan']))
                    <button x-on:click="activeTab = 'permohonan'" :class="activeTab === 'permohonan' ? 'bg-white shadow-sm text-brand-700' : 'text-slate-500 hover:text-slate-700'" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition">
                        <x-admin.icon name="clipboard-check" :size="14" />
                        Rekomendasi
                        <span class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">{{ $recent['permohonan']->count() }}</span>
                    </button>
                @endif
                @if(isset($recent['registrasi_lb3']))
                    <button x-on:click="activeTab = 'registrasi_lb3'" :class="activeTab === 'registrasi_lb3' ? 'bg-white shadow-sm text-brand-700' : 'text-slate-500 hover:text-slate-700'" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition">
                        <x-admin.icon name="building" :size="14" />
                        LB3
                        <span class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">{{ $recent['registrasi_lb3']->count() }}</span>
                    </button>
                @endif
                @if(isset($recent['tata_penataan']))
                    <button x-on:click="activeTab = 'tata_penataan'" :class="activeTab === 'tata_penataan' ? 'bg-white shadow-sm text-brand-700' : 'text-slate-500 hover:text-slate-700'" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition">
                        <x-admin.icon name="building" :size="14" />
                        Tata Penataan
                        <span class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">{{ $recent['tata_penataan']->count() }}</span>
                    </button>
                @endif
                @if(isset($recent['rintek_pertek']))
                    <button x-on:click="activeTab = 'rintek_pertek'" :class="activeTab === 'rintek_pertek' ? 'bg-white shadow-sm text-brand-700' : 'text-slate-500 hover:text-slate-700'" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition">
                        <x-admin.icon name="factory" :size="14" />
                        RINTEK/PERTEK
                        <span class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">{{ $recent['rintek_pertek']->count() }}</span>
                    </button>
                @endif
                @if(isset($recent['artikel']))
                    <button x-on:click="activeTab = 'artikel'" :class="activeTab === 'artikel' ? 'bg-white shadow-sm text-brand-700' : 'text-slate-500 hover:text-slate-700'" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition">
                        <x-admin.icon name="news" :size="14" />
                        Artikel
                        <span class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">{{ $recent['artikel']->count() }}</span>
                    </button>
                @endif
            </div>
        </div>

        <x-admin.card :padding="false" class="overflow-hidden">
            @if(isset($recent['laporan']))
            <div x-show="activeTab === 'laporan'" class="divide-y divide-slate-100">
                @forelse($recent['laporan'] as $item)
                    <x-admin.recent-item
                        icon="megaphone"
                        icon-color="emerald"
                        :title="$item->nomor_tiket"
                        :subtitle="'Laporan — '.(\Illuminate\Support\Str::title(str_replace('-', ' ', $item->bidang->value ?? '-'))) . ($item->jenis_pengaduan ? ' • '.$item->jenis_pengaduan : '')"
                        :time="$item->created_at?->diffForHumans()"
                        :badge="$item->status"
                        href="#"
                    />
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada laporan terbaru</p>
                @endforelse
            </div>
            @endif

            @if(isset($recent['permohonan']))
            <div x-show="activeTab === 'permohonan'" class="divide-y divide-slate-100" x-cloak>
                @forelse($recent['permohonan'] as $item)
                    <x-admin.recent-item
                        icon="clipboard-check"
                        icon-color="indigo"
                        :title="'Rekomendasi #' . $item->id"
                        :subtitle="$item->nama_perusahaan ?? $item->nama_pemilik ?? 'Permohonan Rekomendasi'"
                        :time="$item->created_at?->diffForHumans()"
                        :badge="$item->status ?? null"
                        href="#"
                    />
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada permohonan terbaru</p>
                @endforelse
            </div>
            @endif

            @if(isset($recent['registrasi_lb3']))
            <div x-show="activeTab === 'registrasi_lb3'" class="divide-y divide-slate-100" x-cloak>
                @forelse($recent['registrasi_lb3'] as $item)
                    <x-admin.recent-item
                        icon="building"
                        icon-color="amber"
                        :title="$item->nama_usaha ?? ('Registrasi #' . $item->id)"
                        :subtitle="$item->nama_pemilik ?? ('Registrasi Usaha LB3' . ($item->status ? ' • ' . ($item->status instanceof \BackedEnum ? $item->status->value : $item->status) : ''))"
                        :time="$item->created_at?->diffForHumans()"
                        :badge="$item->status ?? null"
                        href="#"
                    />
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada registrasi LB3 terbaru</p>
                @endforelse
            </div>
            @endif

            @if(isset($recent['tata_penataan']))
            <div x-show="activeTab === 'tata_penataan'" class="divide-y divide-slate-100" x-cloak>
                @forelse($recent['tata_penataan'] as $item)
                    <x-admin.recent-item
                        icon="building"
                        icon-color="purple"
                        :title="$item->nomor_tiket ?? ('Pengaduan #' . $item->id)"
                        :subtitle="$item->nama_pelapor ?? 'Pengaduan Tata Penataan'"
                        :time="$item->created_at?->diffForHumans()"
                        :badge="$item->status ?? null"
                        href="#"
                    />
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada pengaduan tata penataan terbaru</p>
                @endforelse
            </div>
            @endif

            @if(isset($recent['rintek_pertek']))
            <div x-show="activeTab === 'rintek_pertek'" class="divide-y divide-slate-100" x-cloak>
                @forelse($recent['rintek_pertek'] as $item)
                    <x-admin.recent-item
                        icon="factory"
                        icon-color="sky"
                        :title="$item->nama_perusahaan ?? ('RINTEK #' . $item->id)"
                        :subtitle="'Pengajuan RINTEK/PERTEK'"
                        :time="$item->created_at?->diffForHumans()"
                        :badge="$item->status ?? null"
                        href="#"
                    />
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada pengajuan RINTEK/PERTEK terbaru</p>
                @endforelse
            </div>
            @endif

            @if(isset($recent['artikel']))
            <div x-show="activeTab === 'artikel'" class="divide-y divide-slate-100" x-cloak>
                @forelse($recent['artikel'] as $item)
                    <x-admin.recent-item
                        icon="news"
                        icon-color="blue"
                        :title="$item->judul ?? ('Artikel #' . $item->id)"
                        :subtitle="'Artikel'"
                        :time="$item->created_at?->diffForHumans()"
                        :badge="$item->status ?? null"
                        href="#"
                    />
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada artikel terbaru</p>
                @endforelse
            </div>
            @endif
        </x-admin.card>
    </section>
    @endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<link href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" rel="stylesheet" />
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>
<script>
    (function () {
        const charts = @json($charts);
        const emerald = '#059669', teal = '#0d9488', sky = '#0284c7', amber = '#d97706', rose = '#e11d48', indigo = '#4f46e5', purple = '#7c3aed', slate = '#64748b';
        const palette = [emerald, sky, amber, teal, indigo, rose, purple, slate];
        const bidangColors = { pengendalian: emerald, sampah: sky, rth: teal, 'tata-penataan': purple };

        function ready(fn) {
            if (window.Chart) return fn();
            let tries = 0;
            const iv = setInterval(() => {
                if (window.Chart || tries++ > 50) { clearInterval(iv); if (window.Chart) fn(); }
            }, 100);
        }

        ready(function () {
            Chart.defaults.font.family = 'ui-sans-serif, system-ui, sans-serif';
            Chart.defaults.color = '#64748b';

            const trendEl = document.getElementById('chartTrend');
            if (trendEl) {
                new Chart(trendEl, {
                    type: 'line',
                    data: {
                        labels: charts.trend.labels,
                        datasets: [{
                            label: 'Pengaduan', data: charts.trend.data,
                            borderColor: emerald, backgroundColor: 'rgba(5,150,105,0.12)',
                            fill: true, tension: 0.35, borderWidth: 2, pointRadius: 3, pointBackgroundColor: emerald,
                        }],
                    },
                    options: { responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } },
                });
            }

            const statusEl = document.getElementById('chartStatus');
            if (statusEl && charts.status.data.length) {
                new Chart(statusEl, {
                    type: 'doughnut',
                    data: { labels: charts.status.labels, datasets: [{ data: charts.status.data, backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }] },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '62%',
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } } } },
                });
            } else if (statusEl) {
                statusEl.parentElement.innerHTML = '<p class="grid h-full place-items-center text-sm text-slate-400">Belum ada data status</p>';
            }

            const statusStats = @json($statusStats ?? null);
            const miniEl = document.getElementById('chartStatusMini');
            if (miniEl && statusStats && statusStats.distribution.data.length) {
                const miniColors = { 'Belum Ditindaklanjuti': amber, 'Belum Ditinjau': amber, 'Ditindaklanjuti': sky, 'Ditinjau': sky, 'Selesai': emerald, 'Ditolak': rose };
                new Chart(miniEl, {
                    type: 'doughnut',
                    data: {
                        labels: statusStats.distribution.labels,
                        datasets: [{
                            data: statusStats.distribution.data,
                            backgroundColor: statusStats.distribution.labels.map(l => miniColors[l] || slate),
                            borderWidth: 3,
                            borderColor: '#ffffff',
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: { legend: { display: false }, tooltip: { enabled: true } },
                    },
                });
            } else if (miniEl) {
                miniEl.parentElement.innerHTML = '<p class="grid h-full place-items-center text-sm text-slate-400">Belum ada data</p>';
            }

            const modEl = document.getElementById('chartModules');
            if (modEl && charts.modules.data.length) {
                new Chart(modEl, {
                    type: 'bar',
                    data: { labels: charts.modules.labels, datasets: [{ label: 'Jumlah', data: charts.modules.data, backgroundColor: palette, borderRadius: 6, maxBarThickness: 48 }] },
                    options: { responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false }, ticks: { autoSkip: false, maxRotation: 45, minRotation: 0 } } } },
                });
            } else if (modEl) {
                modEl.parentElement.innerHTML = '<p class="grid h-full place-items-center text-sm text-slate-400">Belum ada data modul</p>';
            }

            const perfEl = document.getElementById('chartPerformance');
            if (perfEl && charts.performance && charts.performance.length) {
                const perfColors = { 'Belum Ditindaklanjuti': amber, 'Belum Ditinjau': amber, 'Ditindaklanjuti': sky, 'Ditinjau': sky, 'Selesai': emerald, 'Ditolak': rose };
                const labels = charts.performance.map(p => p.status);
                const data = charts.performance.map(p => p.total);
                new Chart(perfEl, {
                    type: 'bar',
                    data: { labels, datasets: [{ label: 'Jumlah', data, backgroundColor: labels.map(l => perfColors[l] || slate), borderRadius: 6, maxBarThickness: 26 }] },
                    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, y: { grid: { display: false } } } },
                });
            } else if (perfEl) {
                perfEl.parentElement.innerHTML = '<p class="grid h-full place-items-center text-sm text-slate-400">Belum ada data</p>';
            }

            const trendBidangEl = document.getElementById('chartTrendBidang');
            if (trendBidangEl && charts.trendPerBidang && Object.keys(charts.trendPerBidang.datasets || {}).length > 1) {
                const datasets = Object.entries(charts.trendPerBidang.datasets).map(([bidang, data]) => ({
                    label: bidang.charAt(0).toUpperCase() + bidang.slice(1),
                    data: data,
                    borderColor: bidangColors[bidang] || slate,
                    backgroundColor: (bidangColors[bidang] || slate) + '18',
                    fill: false,
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 3,
                }));
                const trendBidangChart = new Chart(trendBidangEl, {
                    type: 'line',
                    data: { labels: charts.trendPerBidang.labels, datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { top: 4, bottom: 4 } },
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'center',
                                labels: { boxWidth: 12, padding: 16, usePointStyle: true, pointStyle: 'circle' },
                            },
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                            x: { grid: { display: false } },
                        },
                    },
                });
                if (window.ResizeObserver) {
                    new ResizeObserver(() => trendBidangChart.resize()).observe(trendBidangEl.parentElement);
                }
                window.addEventListener('resize', () => trendBidangChart.resize());
            } else if (trendBidangEl) {
                trendBidangEl.parentElement.innerHTML = '<p class="grid h-full place-items-center text-sm text-slate-400">Perlu akses lebih dari 1 bidang</p>';
            }
        });

        // Peta Sebaran Pengaduan ditangani di partial admin.partials.sebaran-pengaduan

        // Stagger reveal
        const revealEls = Array.from(document.querySelectorAll('.stagger-item'));
        if (revealEls.length) {
            const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduce || !('IntersectionObserver' in window)) {
                revealEls.forEach(el => el.classList.add('is-in'));
            } else {
                const io = new IntersectionObserver((entries, obs) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-in');
                            obs.unobserve(entry.target);
                        }
                    });
                }, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 });
                revealEls.forEach(el => io.observe(el));
            }
        }
    })();
</script>
@endpush
