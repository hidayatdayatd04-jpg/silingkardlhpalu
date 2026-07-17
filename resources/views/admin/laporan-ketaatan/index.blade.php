@extends('layouts.admin')

@section('title', 'Laporan Ketaatan Perusahaan')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Laporan Ketaatan Perusahaan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Analisis tingkat ketaatan perusahaan terhadap regulasi lingkungan</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-2">
                <input type="month" name="bulan" value="{{ $bulan }}" 
                    class="rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors">
                    Filter
                </button>
            </form>
            <a href="{{ route('admin.laporan-ketaatan.export-pdf', ['bulan' => $bulan]) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <x-admin.icon name="download" :size="16" />
                Export PDF
            </a>
            <a href="{{ route('admin.laporan-ketaatan.export-excel', ['bulan' => $bulan]) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <x-admin.icon name="table" :size="16" />
                Export Excel
            </a>
        </div>
    </div>

    {{-- Persentase Ketaatan --}}
    <div class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium opacity-90">Persentase Ketaatan Bulan Ini</p>
                <p class="text-5xl font-bold mt-2">{{ $rekap['persentase_ketaatan'] }}%</p>
                <p class="text-sm opacity-75 mt-2">
                    Berdasarkan {{ $rekap['total_sidak'] }} kali inspeksi
                </p>
            </div>
            <div class="grid size-24 place-items-center rounded-full bg-white/20">
                <x-admin.icon name="check-circle" :size="48" />
            </div>
        </div>
    </div>

    {{-- Rekap --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Total Sidak', 'value' => $rekap['total_sidak'], 'icon' => 'clipboard-check', 'color' => 'blue'],
            ['label' => 'Taat', 'value' => $rekap['taat'], 'icon' => 'check-circle', 'color' => 'green'],
            ['label' => 'Tidak Taat', 'value' => $rekap['tidak_taat'], 'icon' => 'x-circle', 'color' => 'red'],
            ['label' => 'Perlu Pembinaan', 'value' => $rekap['perlu_pembinaan'], 'icon' => 'alert-circle', 'color' => 'amber'],
        ] as $item)
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="flex items-center gap-3">
                <div class="grid size-10 place-items-center rounded-xl bg-{{ $item['color'] }}-50 dark:bg-{{ $item['color'] }}-900/20">
                    <x-admin.icon name="{{ $item['icon'] }}" :size="20" class="text-{{ $item['color'] }}-600 dark:text-{{ $item['color'] }}-400" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $item['value'] }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Tren Ketaatan 6 Bulan</h3>
            <div class="h-64">
                <canvas id="chartTren"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Distribusi Hasil Sidak</h3>
            <div class="h-64">
                <canvas id="chartDistribusi"></canvas>
            </div>
        </div>
    </div>

    {{-- Ketaatan Per Objek --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Ketaatan Perusahaan (Top 20)</h3>
            <p class="text-sm text-slate-500 mt-1">Berdasarkan jumlah sidak yang dilakukan</p>
        </div>
        @if($ketaatanPerObjek->isEmpty())
            <div class="flex items-center justify-center h-32 text-sm text-slate-400">
                Belum ada data sidak bulan ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-400">Perusahaan</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-400">Total Sidak</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-400">Taat</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-400">Ketaatan</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($ketaatanPerObjek as $objek)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ $objek->nama_perusahaan }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $objek->total_sidak }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $objek->sidak_taat }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                            <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $objek->persentase }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $objek->persentase }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($objek->persentase >= 80)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-700">
                                            Baik
                                        </span>
                                    @elseif($objek->persentase >= 50)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-100 text-amber-700">
                                            Cukup
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-700">
                                            Kurang
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const trenData = @json($tren);
    const distribusiData = @json($distribusiHasil);

    // Chart Tren
    new Chart(document.getElementById('chartTren'), {
        type: 'line',
        data: {
            labels: trenData.map(d => d.bulan),
            datasets: [
                {
                    label: 'Persentase Ketaatan',
                    data: trenData.map(d => d.persentase),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Total Sidak',
                    data: trenData.map(d => d.total),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'y1',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) { return value + '%'; },
                    },
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                },
            },
        },
    });

    // Chart Distribusi
    const colors = ['#10b981', '#ef4444', '#f59e0b'];
    new Chart(document.getElementById('chartDistribusi'), {
        type: 'doughnut',
        data: {
            labels: distribusiData.map(d => d.label),
            datasets: [{
                data: distribusiData.map(d => d.total),
                backgroundColor: colors,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
            },
        },
    });
</script>
@endpush
@endsection
