@extends('layouts.admin')

@section('title', 'Dashboard Admin DLH Kota Palu')
@section('heading', 'Dashboard')

@section('content')
    @php
        $hour = (int) now()->format('H');
        $greet = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
        $firstName = \Illuminate\Support\Str::of(auth()->user()?->name ?? 'Admin')->explode(' ')->first();
    @endphp

    {{-- Hero --}}
    <x-admin.page-header
        :title="$greet . ', ' . $firstName . ' 👋'"
        :subtitle="\Carbon\Carbon::now()->translatedFormat('l, d F Y')"
        hero
    />

    {{-- Stat cards --}}
    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <x-admin.stat-card
                :label="$card['label']"
                :value="$card['value']"
                :icon="$card['icon'] ?? 'folder'"
                :color="$card['tone']"
            />
        @endforeach
        @if(empty($cards))
            <div class="md:col-span-2 xl:col-span-4">
                <x-admin.card><p class="text-sm text-slate-500">Belum ada modul yang bisa ditampilkan untuk akun Anda.</p></x-admin.card>
            </div>
        @endif
    </section>

    {{-- Charts --}}
    <section class="grid gap-6 lg:grid-cols-3">
        <x-admin.card class="lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-h4 font-bold text-ink-900">Tren Pengaduan 6 Bulan</h2>
                    <p class="text-xs text-slate-500">Jumlah pengaduan masuk per bulan</p>
                </div>
                <x-admin.icon name="trending-up" :size="20" class="text-brand-400" />
            </div>
            <div class="relative h-64">
                <canvas id="chartTrend"></canvas>
            </div>
        </x-admin.card>

        <x-admin.card>
            <div class="mb-4">
                <h2 class="text-h4 font-bold text-ink-900">Distribusi Status</h2>
                <p class="text-xs text-slate-500">Status pengaduan saat ini</p>
            </div>
            <div class="relative h-64">
                <canvas id="chartStatus"></canvas>
            </div>
        </x-admin.card>
    </section>

    <section class="grid gap-6 lg:grid-cols-3">
        <x-admin.card class="lg:col-span-2">
            <div class="mb-4">
                <h2 class="text-h4 font-bold text-ink-900">Jumlah Data per Modul</h2>
                <p class="text-xs text-slate-500">Total data pada tiap modul yang Anda akses</p>
            </div>
            <div class="relative h-72">
                <canvas id="chartModules"></canvas>
            </div>
        </x-admin.card>
    </section>

    {{-- Tren per Bidang --}}
    @if(count($charts['trendPerBidang']['datasets'] ?? []) > 1)
    <section class="grid gap-6 lg:grid-cols-3">
        <x-admin.card class="lg:col-span-2">
            <div class="mb-4">
                <h2 class="text-h4 font-bold text-ink-900">Tren per Bidang</h2>
                <p class="text-xs text-slate-500">Perbandingan pengaduan antar bidang</p>
            </div>
            <div class="relative h-72">
                <canvas id="chartTrendBidang"></canvas>
            </div>
        </x-admin.card>

        <div class="space-y-6">
            {{-- Side panel --}}
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

            {{-- Aktivitas terbaru (audit) --}}
            @if($activityFeed->isNotEmpty())
                <x-admin.card :padding="false">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-h4 font-bold text-ink-900">Aktivitas Terbaru</h2>
                        <p class="text-xs text-slate-500">Log tindakan pengguna</p>
                    </div>
                    <div class="divide-y divide-slate-100">
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
            @endif
        </div>
    </section>
    @else
    <section class="grid gap-6 lg:grid-cols-3">
        <x-admin.card class="lg:col-span-2">
            <div class="mb-4">
                <h2 class="text-h4 font-bold text-ink-900">Jumlah Data per Modul</h2>
                <p class="text-xs text-slate-500">Total data pada tiap modul yang Anda akses</p>
            </div>
            <div class="relative h-72">
                <canvas id="chartModules"></canvas>
            </div>
        </x-admin.card>

        <div class="space-y-6">
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

            @if($activityFeed->isNotEmpty())
                <x-admin.card :padding="false">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-h4 font-bold text-ink-900">Aktivitas Terbaru</h2>
                        <p class="text-xs text-slate-500">Log tindakan pengguna</p>
                    </div>
                    <div class="divide-y divide-slate-100">
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
            @endif
        </div>
    </section>
    @endif

    {{-- Peta Sebaran Pengaduan --}}
    @if($mapLocations)
    <section x-data="{ activeTab: 'map' }">
        <div class="flex items-center gap-4 mb-4">
            <h2 class="text-h3 font-bold text-ink-900">Sebaran Pengaduan</h2>
            <div class="flex rounded-lg bg-slate-100 dark:bg-slate-800 p-0.5">
                <button x-on:click="activeTab = 'map'" :class="activeTab === 'map' ? 'bg-white dark:bg-slate-700 shadow-sm text-ink-900' : 'text-slate-500'" class="px-3 py-1.5 text-xs font-semibold rounded-md transition">Peta</button>
                <button x-on:click="activeTab = 'table'" :class="activeTab === 'table' ? 'bg-white dark:bg-slate-700 shadow-sm text-ink-900' : 'text-slate-500'" class="px-3 py-1.5 text-xs font-semibold rounded-md transition">Tabel</button>
            </div>
        </div>
        <x-admin.card :padding="false" class="overflow-hidden">
            <div x-show="activeTab === 'map'" class="relative" style="height: 450px;">
                <div id="adminMap" class="w-full h-full"></div>
            </div>
            <div x-show="activeTab === 'table'" class="p-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500">Nomor Tiket</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500">Bidang</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500">Lokasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($mapLocations as $loc)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-3 py-2 font-mono text-xs font-bold">{{ $loc['nomor_tiket'] }}</td>
                                    <td class="px-3 py-2">{{ ucfirst($loc['kategori'] ?? '-') }}</td>
                                    <td class="px-3 py-2">{{ $loc['status'] ?? '-' }}</td>
                                    <td class="px-3 py-2 text-xs text-slate-500">{{ $loc['alamat'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </x-admin.card>
    </section>
    @endif

    {{-- Akses cepat modul --}}
    <x-admin.card>
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-h3 font-bold text-ink-900">Akses Cepat Modul</h2>
                <p class="text-sm text-slate-500">Semua halaman admin yang bisa Anda akses</p>
            </div>
            <x-admin.icon name="dashboard" :size="24" class="text-slate-300" />
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($groups as $group)
                <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-4">
                    <p class="mb-3 flex items-center gap-2 text-caption font-bold uppercase tracking-[0.16em] text-brand-700">
                        <span class="size-1.5 rounded-full bg-brand-500"></span>
                        {{ $group['label'] }}
                    </p>
                    <div class="space-y-1">
                        @foreach ($group['items'] as $item)
                            <a href="{{ route('admin.resources.index', $item['slug']) }}"
                               class="group flex items-center justify-between rounded-md px-3 py-2 text-sm font-semibold text-ink-700 transition hover:translate-x-0.5 hover:bg-brand-50 hover:text-brand-700">
                                <span>{{ $item['label'] }}</span>
                                <x-admin.icon name="chevron-right" :size="16" class="text-slate-300 transition group-hover:text-brand-500" />
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </x-admin.card>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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

            const modEl = document.getElementById('chartModules');
            if (modEl && charts.modules.data.length) {
                new Chart(modEl, {
                    type: 'bar',
                    data: { labels: charts.modules.labels, datasets: [{ label: 'Jumlah', data: charts.modules.data, backgroundColor: palette, borderRadius: 6, maxBarThickness: 48 }] },
                    options: { responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } },
                });
            } else if (modEl) {
                modEl.parentElement.innerHTML = '<p class="grid h-full place-items-center text-sm text-slate-400">Belum ada data modul</p>';
            }

            // Trend per Bidang (multi-line)
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
                new Chart(trendBidangEl, {
                    type: 'line',
                    data: { labels: charts.trendPerBidang.labels, datasets },
                    options: { responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } },
                });
            } else if (trendBidangEl) {
                trendBidangEl.parentElement.innerHTML = '<p class="grid h-full place-items-center text-sm text-slate-400">Perlu akses lebih dari 1 bidang</p>';
            }
        });

        // Leaflet Map
        const mapLocations = @json($mapLocations ?? []);
        const mapEl = document.getElementById('adminMap');
        if (mapEl && mapLocations.length) {
            const map = L.map('adminMap').setView([-0.9, 119.87], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);
            const bidangMarkers = { pengendalian: emerald, sampah: sky, rth: teal, 'tata-penataan': purple };
            mapLocations.forEach(function (loc) {
                if (!loc.latitude || !loc.longitude) return;
                const color = bidangMarkers[loc.kategori] || '#64748b';
                const marker = L.circleMarker([loc.latitude, loc.longitude], {
                    radius: 6, fillColor: color, color: '#fff', weight: 2, fillOpacity: 0.85
                }).addTo(map);
                marker.bindPopup('<b>' + (loc.nomor_tiket || '') + '</b><br>' + (loc.jenis_pengaduan || '') + '<br>' + (loc.status || ''));
            });
            if (mapLocations.length) {
                const bounds = L.latLngBounds(mapLocations.filter(l => l.latitude && l.longitude).map(l => [l.latitude, l.longitude]));
                if (bounds.isValid()) map.fitBounds(bounds, { padding: [30, 30] });
            }
        }
    })();
</script>
@endpush
