@extends('layouts.admin')

@section('title', 'Dashboard Admin — DLH Kota Palu')
@section('heading', 'Dashboard')

@section('content')
<div class="admin-dashboard space-y-8">
    @php
        $hour = (int) now()->format('H');
        $greet = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $firstName = \Illuminate\Support\Str::of($user?->name ?? 'Admin')->explode(' ')->first();
        $initials = collect(explode(' ', $user?->name ?? 'A'))
            ->map(fn ($w) => mb_substr($w, 0, 1))
            ->take(2)
            ->join('');
        $roleName = $user?->roles?->first()?->name;
        $roleLabel = \App\Enums\AdminRole::tryFrom($roleName)?->label() ?? 'Administrator';
    @endphp

    {{-- ══════════════════════ 1. HERO BANNER ══════════════════════ --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 p-6 sm:p-8 text-white shadow-2xl shadow-forest-950/30 border border-forest-700/50">
        {{-- Ambient decorative glow --}}
        <div class="pointer-events-none absolute -right-20 -top-20 size-80 rounded-full bg-brand-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-20 -bottom-20 size-80 rounded-full bg-bay-500/15 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0 max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-brand-400/30 bg-brand-500/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-brand-200 backdrop-blur-md">
                    <span class="size-2 rounded-full bg-brand-400 animate-pulse"></span>
                    <span>{{ $roleLabel }}</span>
                </div>

                <h1 class="mt-3 text-2xl font-black tracking-tight text-white sm:text-3xl lg:text-4xl">
                    {{ $greet }}, {{ $firstName }} 👋
                </h1>
                <p class="mt-2 text-sm text-forest-100/80 leading-relaxed">
                    Selamat datang di Ruang Kendali Operasional Dinas Lingkungan Hidup Kota Palu. Pantau pengaduan, verifikasi permohonan.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-white/80">
                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3 py-1.5 font-medium backdrop-blur">
                        <x-admin.icon name="calendar" :size="15" class="text-brand-300" />
                        {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3 py-1.5 font-mono font-semibold text-brand-200 backdrop-blur">
                        <x-admin.icon name="clock" :size="15" class="text-brand-300" />
                        <span x-data="{ t: '' }" x-init="const u=()=>t=new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'});u();setInterval(u,1000)" x-text="t"></span>
                        WITA
                    </span>
                </div>
            </div>

            {{-- Profile Card in Hero --}}
            <div class="flex items-center gap-4 rounded-2xl border border-white/15 bg-white/10 p-4 pr-6 backdrop-blur-md shadow-lg">
                <div class="grid size-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-brand-400 to-brand-600 text-lg font-black tracking-wider text-white shadow-md">
                    {{ strtoupper($initials) }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-white">{{ $user?->name ?? 'Admin' }}</p>
                    <p class="truncate text-xs text-white/70">{{ $user?->email }}</p>
                    <div class="mt-1 flex items-center gap-1 text-[11px] text-brand-200">
                        <span class="size-1.5 rounded-full bg-emerald-400"></span>
                        <span>Sistem Aktif & Terhubung</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════ 2. KPI RINGKASAN STRIP ══════════════════════ --}}
    @if($summary)
    <section class="grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
        <x-admin.kpi-strip label="Pengunjung Hari Ini" :value="$summary['pengunjung_hari_ini']" icon="users" color="bay" />
        <x-admin.kpi-strip label="Total Kunjungan" :value="$summary['total_pengunjung']" icon="chart" color="emerald" />
        <x-admin.kpi-strip label="Total Pelapor" :value="$summary['total_pelapor']" icon="megaphone" color="sky" />
        <x-admin.kpi-strip label="Total Permohonan" :value="$summary['total_pengajuan']" icon="file-plus" color="amber" />
    </section>
    @endif

    {{-- ══════════════════════ 3. KARTU STATISTIK MODUL ══════════════════════ --}}
    <section>
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="grid size-8 place-items-center rounded-xl bg-brand-500/10 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
                    <x-admin.icon name="layers" :size="18" />
                </span>
                <h2 class="text-lg font-extrabold tracking-tight text-ink-900 dark:text-white">
                    Ringkasan Modul & Layanan
                </h2>
            </div>
            <span class="text-xs text-slate-500">Data terkini per bidang</span>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($cards as $card)
                <div class="stagger-item" style="--reveal-delay: {{ $loop->index * 45 }}ms;">
                    <x-admin.stat-card
                        :label="$card['label']"
                        :value="$card['value']"
                        :icon="$card['icon'] ?? 'folder'"
                        :color="$card['tone']"
                        :href="isset($card['slug']) ? route('admin.resources.index', $card['slug']) : null"
                    />
                </div>
            @endforeach
            @if(empty($cards))
                <div class="md:col-span-2 xl:col-span-4">
                    <x-admin.card><p class="text-sm text-slate-500">Belum ada modul yang bisa ditampilkan untuk akun Anda.</p></x-admin.card>
                </div>
            @endif
        </div>
    </section>

    {{-- ══════════════════════ 4. RINGKASAN KINERJA PENANGANAN ══════════════════════ --}}
    @if($statusStats && $statusStats['total'] > 0)
    <section>
        <x-admin.card :padding="false" class="overflow-hidden border border-slate-200/90 shadow-xl shadow-slate-900/5 dark:border-slate-800">
            <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/40">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="grid size-9 place-items-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400">
                            <x-admin.icon name="clipboard-check" :size="20" />
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-ink-900 dark:text-white">Kinerja & Status Penanganan Pengaduan</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Tingkat responsivitas penyelesaian laporan pengaduan masyarakat</p>
                        </div>
                    </div>
                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        Total {{ number_format($statusStats['total'], 0, ',', '.') }} Laporan
                    </span>
                </div>
            </div>

            <div class="grid gap-6 p-6 md:grid-cols-12 items-center">
                {{-- Progress & KPI Numbers (8 Cols) --}}
                <div class="md:col-span-8 space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Persentase Penanganan Selesai</p>
                            <p class="mt-1 text-3xl font-black tracking-tight text-ink-900 dark:text-white">
                                {{ $statusStats['selesai_pct'] }}<span class="text-2xl text-brand-500 font-bold">%</span>
                            </p>
                        </div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            <b class="text-emerald-600 dark:text-emerald-400">{{ number_format($statusStats['selesai'], 0, ',', '.') }}</b> dari {{ number_format($statusStats['total'], 0, ',', '.') }} pengaduan telah ditindaklanjuti
                        </p>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="relative h-4 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800 p-0.5">
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-1000 ease-out shadow-sm"
                            style="width: {{ max($statusStats['selesai_pct'], 4) }}%;"
                        ></div>
                    </div>

                    {{-- 2 Core Status Cards --}}
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 pt-2">
                        <div class="flex items-center justify-between rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4 dark:border-amber-400/20 dark:bg-amber-400/5">
                            <div class="flex items-center gap-3">
                                <span class="grid size-10 place-items-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/20 dark:text-amber-300">
                                    <x-admin.icon name="clock" :size="20" />
                                </span>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Belum Ditindaklanjuti</p>
                                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ number_format($statusStats['belum'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-bold text-amber-700 dark:text-amber-300">Menunggu</span>
                        </div>

                        <div class="flex items-center justify-between rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-4 dark:border-emerald-400/20 dark:bg-emerald-400/5">
                            <div class="flex items-center gap-3">
                                <span class="grid size-10 place-items-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/20 dark:text-emerald-300">
                                    <x-admin.icon name="check-circle" :size="20" />
                                </span>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sudah Ditindaklanjuti</p>
                                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($statusStats['selesai'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-300">Selesai</span>
                        </div>
                    </div>
                </div>

                {{-- Donut Status Mini (4 Cols) --}}
                <div class="md:col-span-4 flex flex-col items-center justify-center p-2 border-t md:border-t-0 md:border-l border-slate-100 dark:border-slate-800">
                    <div class="relative h-44 w-44">
                        <canvas id="chartStatusMini"></canvas>
                        <div class="pointer-events-none absolute inset-0 grid place-items-center text-center">
                            <div>
                                <p class="text-2xl font-black tracking-tight text-ink-900 dark:text-white">{{ number_format($statusStats['total'], 0, ',', '.') }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Aduan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-admin.card>
    </section>
    @endif

    {{-- ══════════════════════ 5. SUITE GRAFIK & VISUALISASI UTAMA ══════════════════════ --}}
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Chart 1: Tren Pengaduan 6 Bulan (Area Chart) --}}
        <x-admin.card class="lg:col-span-8 border border-slate-200/90 shadow-xl shadow-slate-900/5 dark:border-slate-800">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 place-items-center rounded-xl bg-brand-500/10 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
                        <x-admin.icon name="trending-up" :size="20" />
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-ink-900 dark:text-white">Tren Pengaduan Bulanan</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Aktivitas volume laporan masuk 6 bulan terakhir</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 p-1 text-xs font-semibold dark:border-slate-700 dark:bg-slate-800">
                    <span class="size-2 rounded-full bg-emerald-500 ml-1.5"></span>
                    <span class="px-2 text-slate-700 dark:text-slate-200">Volume Total</span>
                </div>
            </div>
            <div class="relative h-72 w-full">
                <canvas id="chartTrend"></canvas>
            </div>
        </x-admin.card>

        {{-- Chart 2: Distribusi Status & Proporsi (Doughnut) --}}
        <x-admin.card class="lg:col-span-4 border border-slate-200/90 shadow-xl shadow-slate-900/5 dark:border-slate-800">
            <div class="mb-5 flex items-center gap-3">
                <span class="grid size-9 place-items-center rounded-xl bg-sky-500/10 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400">
                    <x-admin.icon name="pie-chart" :size="20" />
                </span>
                <div>
                    <h3 class="text-base font-bold text-ink-900 dark:text-white">Proporsi Bidang Laporan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Sebaran aduan berdasarkan sektor</p>
                </div>
            </div>
            <div class="relative h-72 w-full flex items-center justify-center">
                <canvas id="chartStatus"></canvas>
            </div>
        </x-admin.card>

        {{-- Chart 3: Jumlah Data per Modul (Bar Chart) --}}
        <x-admin.card class="lg:col-span-7 border border-slate-200/90 shadow-xl shadow-slate-900/5 dark:border-slate-800">
            <div class="mb-5 flex items-center gap-3">
                <span class="grid size-9 place-items-center rounded-xl bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400">
                    <x-admin.icon name="bar-chart-2" :size="20" />
                </span>
                <div>
                    <h3 class="text-base font-bold text-ink-900 dark:text-white">Volume Data per Modul</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Total entitas data yang aktif pada hak akses Anda</p>
                </div>
            </div>
            <div class="relative h-72 w-full">
                <canvas id="chartModules"></canvas>
            </div>
        </x-admin.card>

        {{-- Chart 4: Top Kategori Pengaduan (Horizontal Bar) --}}
        <x-admin.card class="lg:col-span-5 border border-slate-200/90 shadow-xl shadow-slate-900/5 dark:border-slate-800">
            <div class="mb-5 flex items-center gap-3">
                <span class="grid size-9 place-items-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/20 dark:text-amber-400">
                    <x-admin.icon name="award" :size="20" />
                </span>
                <div>
                    <h3 class="text-base font-bold text-ink-900 dark:text-white">Top Kategori Aduan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Isu lingkungan yang paling sering dilaporkan</p>
                </div>
            </div>
            <div class="relative h-72 w-full">
                <canvas id="chartTopCategories"></canvas>
            </div>
        </x-admin.card>

        {{-- Chart 5: Tren Komparatif per Bidang (Multi-Line Chart) --}}
        @if(count($charts['trendPerBidang']['datasets'] ?? []) > 1)
        <x-admin.card class="lg:col-span-12 border border-slate-200/90 shadow-xl shadow-slate-900/5 dark:border-slate-800">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 place-items-center rounded-xl bg-purple-500/10 text-purple-600 dark:bg-purple-400/20 dark:text-purple-400">
                        <x-admin.icon name="activity" :size="20" />
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-ink-900 dark:text-white">Tren Komparatif Antar Bidang</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Perbandingan fluktuasi laporan per bidang dinas tiap bulan</p>
                    </div>
                </div>
            </div>
            <div class="relative w-full h-80">
                <canvas id="chartTrendBidang"></canvas>
            </div>
        </x-admin.card>
        @endif
    </section>

    {{-- ══════════════════════ 6. SEBARAN PENGADUAN (PETA GIS & LIST) ══════════════════════ --}}
    <section class="grid grid-cols-1 gap-6">
        @include('admin.partials.sebaran-pengaduan')
    </section>

    {{-- ══════════════════════ 7. ACTION CENTER & SIDEBAR ACTIONS ══════════════════════ --}}
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Antrean Perlu Tindakan (7 Cols) --}}
        <div class="lg:col-span-7 space-y-6">
            @if($pendingTasks['total'] > 0)
                <x-admin.card :padding="false" class="border border-slate-200/90 shadow-xl shadow-slate-900/5 dark:border-slate-800">
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 py-4 dark:border-slate-800 dark:bg-slate-800/40">
                        <div class="flex items-center gap-3">
                            <span class="grid size-8 place-items-center rounded-xl bg-warning-100 text-warning-700 dark:bg-warning-900/40 dark:text-warning-300">
                                <x-admin.icon name="alert-triangle" :size="18" />
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-ink-900 dark:text-white">Perlu Tindakan & Verifikasi</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Antrean tugas yang membutuhkan respon admin</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-warning-100 px-2.5 py-0.5 text-xs font-extrabold text-warning-700 dark:bg-warning-900/50 dark:text-warning-300">
                            {{ number_format($pendingTasks['total'], 0, ',', '.') }} Menunggu
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($pendingTasks['items'] as $task)
                            @if($task['count'] > 0)
                            <a href="{{ $task['href'] }}" class="flex items-center justify-between gap-3 px-5 py-3.5 transition hover:bg-slate-50 dark:hover:bg-slate-800/60 group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="size-2 rounded-full" style="background: {{ $task['color'] ?? '#f59e0b' }};"></span>
                                    <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 group-hover:text-brand-600 dark:group-hover:text-brand-400 truncate">
                                        {{ $task['label'] }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                        {{ number_format($task['count'], 0, ',', '.') }}
                                    </span>
                                    <x-admin.icon name="chevron-right" :size="14" class="text-slate-400 transition group-hover:translate-x-0.5" />
                                </div>
                            </a>
                            @endif
                        @endforeach
                    </div>
                </x-admin.card>
            @endif

            {{-- Tautan Cepat Navigasi Modul --}}
            <x-admin.card :padding="false" class="border border-slate-200/90 shadow-xl shadow-slate-900/5 dark:border-slate-800">
                <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 dark:border-slate-800 dark:bg-slate-800/40">
                    <div class="flex items-center gap-2.5">
                        <x-admin.icon name="compass" :size="18" class="text-brand-600 dark:text-brand-400" />
                        <h3 class="text-sm font-bold text-ink-900 dark:text-white">Tautan Cepat Navigasi</h3>
                    </div>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3">
                        @foreach($groups as $groupKey => $group)
                            @foreach(array_slice($group['items'], 0, 2) as $item)
                                <a
                                    href="{{ route('admin.resources.index', $item['slug']) }}"
                                    class="flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white p-3 text-xs font-semibold text-slate-700 transition hover:border-brand-300 hover:bg-brand-50/50 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-brand-500 dark:hover:bg-slate-700"
                                >
                                    <span class="size-2 rounded-full bg-brand-500"></span>
                                    <span class="truncate">{{ \Illuminate\Support\Str::limit($item['label'], 22) }}</span>
                                </a>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </x-admin.card>
        </div>

        {{-- Log Aktivitas & Status Sistem (5 Cols) --}}
        <div class="lg:col-span-5 space-y-6">
            {{-- Aktivitas Terbaru Feed --}}
            <x-admin.card :padding="false" class="border border-slate-200/90 shadow-xl shadow-slate-900/5 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 py-4 dark:border-slate-800 dark:bg-slate-800/40">
                    <div class="flex items-center gap-2.5">
                        <x-admin.icon name="clock" :size="18" class="text-slate-600 dark:text-slate-400" />
                        <h3 class="text-sm font-bold text-ink-900 dark:text-white">Aktivitas Terkini</h3>
                    </div>
                    <a href="{{ route('admin.activity-log.index') }}" class="text-xs font-bold text-brand-600 hover:underline dark:text-brand-400">
                        Lihat Semua ↗
                    </a>
                </div>

                @if($activityFeed->isNotEmpty())
                    <div class="max-h-80 divide-y divide-slate-100 overflow-y-auto dark:divide-slate-800">
                        @foreach($activityFeed as $log)
                            @php $meta = $log->eventMeta(); @endphp
                            <div class="flex items-start gap-3 px-5 py-3 transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <div class="grid size-8 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    <x-admin.icon :name="$meta['icon']" :size="14" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-bold text-slate-900 dark:text-white">{{ $log->subject_label }}</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $log->user_name }} • {{ $log->created_at?->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center text-xs text-slate-400">
                        Belum ada riwayat aktivitas terbaru.
                    </div>
                @endif
            </x-admin.card>
        </div>
    </section>

    {{-- ══════════════════════ 8. TAB DATA TERBARU ══════════════════════ --}}
    @if(!empty($recent))
    <section x-data="{ activeTab: '{{ array_key_first($recent) }}' }" class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2.5">
                <span class="grid size-8 place-items-center rounded-xl bg-brand-500/10 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
                    <x-admin.icon name="file-text" :size="18" />
                </span>
                <h2 class="text-lg font-extrabold tracking-tight text-ink-900 dark:text-white">
                    Entri Data Terbaru
                </h2>
            </div>

            {{-- Tabs Controls --}}
            <div class="flex flex-wrap gap-1.5 rounded-2xl border border-slate-200 bg-slate-100/80 p-1 dark:border-slate-800 dark:bg-slate-900">
                @if(isset($recent['laporan']))
                    <button x-on:click="activeTab = 'laporan'" :class="activeTab === 'laporan' ? 'bg-white shadow-sm text-brand-700 dark:bg-slate-800 dark:text-brand-300' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400'" class="flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        <x-admin.icon name="megaphone" :size="14" />
                        <span>Pengaduan</span>
                        <span class="rounded-full bg-slate-200/80 px-1.5 py-0.5 text-[10px] font-extrabold text-slate-700 dark:bg-slate-700 dark:text-slate-300">{{ $recent['laporan']->count() }}</span>
                    </button>
                @endif
                @if(isset($recent['permohonan']))
                    <button x-on:click="activeTab = 'permohonan'" :class="activeTab === 'permohonan' ? 'bg-white shadow-sm text-brand-700 dark:bg-slate-800 dark:text-brand-300' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400'" class="flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        <x-admin.icon name="clipboard-check" :size="14" />
                        <span>Rekomendasi</span>
                        <span class="rounded-full bg-slate-200/80 px-1.5 py-0.5 text-[10px] font-extrabold text-slate-700 dark:bg-slate-700 dark:text-slate-300">{{ $recent['permohonan']->count() }}</span>
                    </button>
                @endif
                @if(isset($recent['registrasi_lb3']))
                    <button x-on:click="activeTab = 'registrasi_lb3'" :class="activeTab === 'registrasi_lb3' ? 'bg-white shadow-sm text-brand-700 dark:bg-slate-800 dark:text-brand-300' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400'" class="flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        <x-admin.icon name="building" :size="14" />
                        <span>LB3</span>
                        <span class="rounded-full bg-slate-200/80 px-1.5 py-0.5 text-[10px] font-extrabold text-slate-700 dark:bg-slate-700 dark:text-slate-300">{{ $recent['registrasi_lb3']->count() }}</span>
                    </button>
                @endif
                @if(isset($recent['rintek_pertek']))
                    <button x-on:click="activeTab = 'rintek_pertek'" :class="activeTab === 'rintek_pertek' ? 'bg-white shadow-sm text-brand-700 dark:bg-slate-800 dark:text-brand-300' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400'" class="flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        <x-admin.icon name="factory" :size="14" />
                        <span>RINTEK/PERTEK</span>
                        <span class="rounded-full bg-slate-200/80 px-1.5 py-0.5 text-[10px] font-extrabold text-slate-700 dark:bg-slate-700 dark:text-slate-300">{{ $recent['rintek_pertek']->count() }}</span>
                    </button>
                @endif
                @if(isset($recent['artikel']))
                    <button x-on:click="activeTab = 'artikel'" :class="activeTab === 'artikel' ? 'bg-white shadow-sm text-brand-700 dark:bg-slate-800 dark:text-brand-300' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400'" class="flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        <x-admin.icon name="news" :size="14" />
                        <span>Artikel</span>
                        <span class="rounded-full bg-slate-200/80 px-1.5 py-0.5 text-[10px] font-extrabold text-slate-700 dark:bg-slate-700 dark:text-slate-300">{{ $recent['artikel']->count() }}</span>
                    </button>
                @endif
            </div>
        </div>

        <x-admin.card :padding="false" class="overflow-hidden border border-slate-200/90 shadow-xl shadow-slate-900/5 dark:border-slate-800">
            @if(isset($recent['laporan']))
            <div x-show="activeTab === 'laporan'" class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recent['laporan'] as $item)
                    <div class="flex items-center justify-between p-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400">
                                <x-admin.icon name="megaphone" :size="18" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="font-mono text-xs font-bold text-slate-900 dark:text-white">{{ $item->nomor_tiket }}</p>
                                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $item->bidang_label ?? '-' }}</span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-300 truncate mt-0.5">{{ $item->jenis_pengaduan ?: 'Pengaduan Masyarakat' }} • {{ $item->alamat }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="hidden sm:inline text-xs text-slate-400">{{ $item->created_at?->diffForHumans() }}</span>
                            <a href="{{ $item->detail_url ?? '#' }}" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-brand-500 hover:text-brand-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                Detail ↗
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada laporan terbaru</p>
                @endforelse
            </div>
            @endif

            @if(isset($recent['permohonan']))
            <div x-show="activeTab === 'permohonan'" class="divide-y divide-slate-100 dark:divide-slate-800" x-cloak>
                @forelse($recent['permohonan'] as $item)
                    <div class="flex items-center justify-between p-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-indigo-500/10 text-indigo-600 dark:bg-indigo-400/10 dark:text-indigo-400">
                                <x-admin.icon name="clipboard-check" :size="18" />
                            </span>
                            <div class="min-w-0">
                                <p class="font-mono text-xs font-bold text-slate-900 dark:text-white">Rekomendasi #{{ $item->id }}</p>
                                <p class="text-xs text-slate-600 dark:text-slate-300 truncate mt-0.5">{{ $item->nama_perusahaan ?? $item->nama_pemilik ?? 'Permohonan Rekomendasi' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $item->status ?? 'Diajukan' }}</span>
                            <a href="{{ route('admin.resources.edit', ['resource' => 'permohonan-rekomendasi', 'record' => $item->id]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-indigo-500 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                Detail ↗
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada permohonan rekomendasi terbaru</p>
                @endforelse
            </div>
            @endif

            @if(isset($recent['registrasi_lb3']))
            <div x-show="activeTab === 'registrasi_lb3'" class="divide-y divide-slate-100 dark:divide-slate-800" x-cloak>
                @forelse($recent['registrasi_lb3'] as $item)
                    <div class="flex items-center justify-between p-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                                <x-admin.icon name="building" :size="18" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $item->nama_usaha ?? ('Registrasi #' . $item->id) }}</p>
                                <p class="text-xs text-slate-600 dark:text-slate-300 truncate mt-0.5">{{ $item->nama_pemilik ?? 'Registrasi Usaha LB3' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <a href="{{ route('admin.resources.edit', ['resource' => 'registrasi-usaha-lb3', 'record' => $item->id]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-amber-500 hover:text-amber-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                Detail ↗
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada registrasi LB3 terbaru</p>
                @endforelse
            </div>
            @endif

            @if(isset($recent['rintek_pertek']))
            <div x-show="activeTab === 'rintek_pertek'" class="divide-y divide-slate-100 dark:divide-slate-800" x-cloak>
                @forelse($recent['rintek_pertek'] as $item)
                    <div class="flex items-center justify-between p-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-sky-500/10 text-sky-600 dark:bg-sky-400/10 dark:text-sky-400">
                                <x-admin.icon name="factory" :size="18" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $item->nama_perusahaan ?? ('RINTEK #' . $item->id) }}</p>
                                <p class="text-xs text-slate-600 dark:text-slate-300 truncate mt-0.5">Pengajuan Dokumen Teknis RINTEK/PERTEK</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <a href="{{ route('admin.resources.edit', ['resource' => 'pengajuan-rintek-pertek', 'record' => $item->id]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-sky-500 hover:text-sky-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                Detail ↗
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada pengajuan RINTEK/PERTEK terbaru</p>
                @endforelse
            </div>
            @endif

            @if(isset($recent['artikel']))
            <div x-show="activeTab === 'artikel'" class="divide-y divide-slate-100 dark:divide-slate-800" x-cloak>
                @forelse($recent['artikel'] as $item)
                    <div class="flex items-center justify-between p-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-rose-500/10 text-rose-600 dark:bg-rose-400/10 dark:text-rose-400">
                                <x-admin.icon name="news" :size="18" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $item->judul ?? ('Artikel #' . $item->id) }}</p>
                                <p class="text-xs text-slate-600 dark:text-slate-300 truncate mt-0.5">Diterbitkan • {{ $item->created_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <a href="{{ route('admin.resources.edit', ['resource' => 'artikel', 'record' => $item->id]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-rose-500 hover:text-rose-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                Detail ↗
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada artikel terbaru</p>
                @endforelse
            </div>
            @endif
        </x-admin.card>
    </section>
    @endif
</div>
@endsection

@push('scripts')
@php
    $buildManifest = is_file(public_path('build/manifest.json'))
        ? json_decode((string) file_get_contents(public_path('build/manifest.json')), true)
        : [];
    $lazyChartsJs = $buildManifest['resources/js/dashboard-charts.js']['file'] ?? null;
    $lazyFpEntry = $buildManifest['resources/js/flatpickr-init.js'] ?? null;
@endphp
@if($lazyChartsJs && $lazyFpEntry)
<script>
    (function () {
        var base = @json(rtrim(asset('build'), '/') . '/');
        var kicked = false;
        function kick() {
            if (kicked) return;
            kicked = true;
            @foreach ((array) ($lazyFpEntry['css'] ?? []) as $cssFile)
            var l = document.createElement('link');
            l.rel = 'stylesheet';
            l.href = base + @json($cssFile);
            document.head.appendChild(l);
            @endforeach
            [@json($lazyFpEntry['file']), @json($lazyChartsJs)].forEach(function (f) {
                var s = document.createElement('script');
                s.type = 'module';
                s.src = base + f;
                document.head.appendChild(s);
            });
        }
        function schedule() {
            if (window.requestIdleCallback) requestIdleCallback(kick, { timeout: 1200 });
            else setTimeout(kick, 300);
        }
        if (document.readyState === 'complete') schedule();
        else window.addEventListener('load', schedule, { once: true });
    })();
</script>
@endif
<script>
    (function () {
        var kick = function () { if (window.ensureMapComponents) window.ensureMapComponents(); };
        var schedule = function () {
            if (window.requestIdleCallback) requestIdleCallback(kick, { timeout: 1500 });
            else setTimeout(kick, 250);
        };
        if (document.readyState === 'complete') schedule();
        else window.addEventListener('load', schedule, { once: true });
    })();
</script>
<script>
    (function () {
        const charts = @json($charts);
        const statusStats = @json($statusStats ?? null);

        const EMERALD = '#059669', TEAL = '#0d9488', SKY = '#0284c7', AMBER = '#d97706', ROSE = '#e11d48', INDIGO = '#4f46e5', PURPLE = '#8b5cf6', SLATE = '#64748b';
        const PALETTE = [EMERALD, SKY, PURPLE, AMBER, TEAL, INDIGO, ROSE, SLATE];

        const BIDANG_COLORS = {
            'pengendalian': '#ef4444',
            'sampah-lb3': '#0284c7',
            'rth': '#10b981',
            'tata-penataan': '#8b5cf6'
        };

        function ready(fn) {
            if (window.Chart) return fn();
            let tries = 0;
            const iv = setInterval(() => {
                if (window.Chart || tries++ > 60) {
                    clearInterval(iv);
                    if (window.Chart) fn();
                }
            }, 80);
        }

        ready(function () {
            Chart.defaults.font.family = 'ui-sans-serif, system-ui, -apple-system, sans-serif';
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#64748b';

            // ── 1. Chart Tren Pengaduan (Area Chart with Linear Gradient) ──
            const trendEl = document.getElementById('chartTrend');
            if (trendEl && charts.trend && charts.trend.labels) {
                const ctx = trendEl.getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 280);
                gradient.addColorStop(0, 'rgba(5, 150, 105, 0.35)');
                gradient.addColorStop(0.8, 'rgba(5, 150, 105, 0.02)');
                gradient.addColorStop(1, 'rgba(5, 150, 105, 0)');

                new Chart(trendEl, {
                    type: 'line',
                    data: {
                        labels: charts.trend.labels,
                        datasets: [{
                            label: 'Jumlah Pengaduan',
                            data: charts.trend.data,
                            borderColor: EMERALD,
                            borderWidth: 3,
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.42,
                            pointRadius: 4,
                            pointHoverRadius: 7,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: EMERALD,
                            pointBorderWidth: 2.5,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                padding: 12,
                                boxPadding: 6,
                                cornerRadius: 10,
                                titleFont: { weight: 'bold', size: 13 },
                                bodyFont: { size: 12 }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, padding: 8 },
                                grid: { color: 'rgba(226, 232, 240, 0.6)' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { padding: 8 }
                            }
                        }
                    },
                });
            }

            // ── 2. Chart Distribusi Status & Sektor (Doughnut) ──
            const statusEl = document.getElementById('chartStatus');
            if (statusEl && charts.bidangDistribution && charts.bidangDistribution.data.length) {
                new Chart(statusEl, {
                    type: 'doughnut',
                    data: {
                        labels: charts.bidangDistribution.labels,
                        datasets: [{
                            data: charts.bidangDistribution.data,
                            backgroundColor: [BIDANG_COLORS['pengendalian'], BIDANG_COLORS['sampah-lb3'], BIDANG_COLORS['rth'], BIDANG_COLORS['tata-penataan']],
                            borderWidth: 3,
                            borderColor: '#ffffff',
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, padding: 14, font: { weight: '600' }, usePointStyle: true }
                            }
                        }
                    },
                });
            } else if (statusEl) {
                statusEl.parentElement.innerHTML = '<p class="grid h-full place-items-center text-xs font-semibold text-slate-400">Belum ada data distribusi bidang</p>';
            }

            // ── 3. Chart Status Mini (Gauge Donut) ──
            const miniEl = document.getElementById('chartStatusMini');
            if (miniEl && statusStats && statusStats.distribution && statusStats.distribution.data.length) {
                new Chart(miniEl, {
                    type: 'doughnut',
                    data: {
                        labels: statusStats.distribution.labels,
                        datasets: [{
                            data: statusStats.distribution.data,
                            backgroundColor: [AMBER, EMERALD],
                            borderWidth: 3,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '74%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                padding: 10,
                                cornerRadius: 8
                            }
                        },
                    },
                });
            }

            // ── 4. Chart Jumlah Data per Modul (Bar) ──
            const modEl = document.getElementById('chartModules');
            if (modEl && charts.modules && charts.modules.data.length) {
                new Chart(modEl, {
                    type: 'bar',
                    data: {
                        labels: charts.modules.labels,
                        datasets: [{
                            label: 'Jumlah Data',
                            data: charts.modules.data,
                            backgroundColor: PALETTE,
                            borderRadius: 8,
                            maxBarThickness: 42,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { padding: 12, cornerRadius: 10 }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, padding: 8 },
                                grid: { color: 'rgba(226, 232, 240, 0.6)' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { maxRotation: 35, minRotation: 0, padding: 6, font: { size: 11 } }
                            }
                        }
                    },
                });
            }

            // ── 5. Chart Top Kategori Pengaduan (Horizontal Bar) ──
            const topCatEl = document.getElementById('chartTopCategories');
            if (topCatEl && charts.topCategories && charts.topCategories.labels && charts.topCategories.labels.length) {
                new Chart(topCatEl, {
                    type: 'bar',
                    data: {
                        labels: charts.topCategories.labels,
                        datasets: [{
                            label: 'Laporan',
                            data: charts.topCategories.data,
                            backgroundColor: [AMBER, SKY, TEAL, PURPLE, ROSE],
                            borderRadius: 6,
                            maxBarThickness: 24,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { padding: 10, cornerRadius: 8 }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: { precision: 0 },
                                grid: { color: 'rgba(226, 232, 240, 0.6)' }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { font: { size: 11, weight: '600' } }
                            }
                        }
                    }
                });
            } else if (topCatEl) {
                topCatEl.parentElement.innerHTML = '<p class="grid h-full place-items-center text-xs font-semibold text-slate-400">Belum ada riwayat kategori aduan</p>';
            }

            // ── 6. Chart Tren per Bidang (Multi-Line Chart) ──
            const trendBidangEl = document.getElementById('chartTrendBidang');
            if (trendBidangEl && charts.trendPerBidang && Object.keys(charts.trendPerBidang.datasets || {}).length > 1) {
                const datasets = Object.entries(charts.trendPerBidang.datasets).map(([bidang, obj]) => {
                    const color = BIDANG_COLORS[bidang] || SLATE;
                    return {
                        label: obj.label || ucfirst(bidang),
                        data: obj.data || [],
                        borderColor: color,
                        backgroundColor: color + '15',
                        fill: false,
                        tension: 0.38,
                        borderWidth: 2.5,
                        pointRadius: 3.5,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: color,
                        pointBorderWidth: 2,
                    };
                });

                new Chart(trendBidangEl, {
                    type: 'line',
                    data: { labels: charts.trendPerBidang.labels, datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'end',
                                labels: { boxWidth: 12, padding: 14, usePointStyle: true, pointStyle: 'circle', font: { weight: '600' } },
                            },
                            tooltip: { padding: 12, cornerRadius: 10 }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0, padding: 8 }, grid: { color: 'rgba(226, 232, 240, 0.6)' } },
                            x: { grid: { display: false }, ticks: { padding: 8 } },
                        },
                    },
                });
            }
        });

        // Stagger reveal on scroll
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
