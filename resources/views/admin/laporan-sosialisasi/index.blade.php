@extends('layouts.admin')

@section('title', 'Laporan Pembinaan & Sosialisasi')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Laporan Pembinaan & Sosialisasi</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Rekap kegiatan sosialisasi dan pembinaan perusahaan</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-2">
                <input type="month" name="bulan" value="{{ $bulan }}" 
                    class="rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-white focus:border-violet-500 focus:ring-violet-500">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-violet-600 rounded-xl hover:bg-violet-700 transition-colors">
                    Filter
                </button>
            </form>
            <a href="{{ route('admin.laporan-sosialisasi.export-pdf', ['bulan' => $bulan]) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <x-admin.icon name="download" :size="16" />
                Export PDF
            </a>
            <a href="{{ route('admin.laporan-sosialisasi.export-excel', ['bulan' => $bulan]) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <x-admin.icon name="table" :size="16" />
                Export Excel
            </a>
        </div>
    </div>

    {{-- Rekap --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        @foreach([
            ['label' => 'Total Kegiatan', 'value' => $rekap['total_sosialisasi'], 'icon' => 'presentation', 'color' => 'violet'],
            ['label' => 'Total Peserta', 'value' => $rekap['total_peserta'], 'icon' => 'users', 'color' => 'blue'],
            ['label' => 'Sudah Evaluasi', 'value' => $rekap['sudah_evaluasi'], 'icon' => 'check-circle', 'color' => 'green'],
            ['label' => 'Belum Evaluasi', 'value' => $rekap['belum_evaluasi'], 'icon' => 'clock', 'color' => 'amber'],
            ['label' => 'Sertifikat Terbit', 'value' => $rekap['sertifikat_terbit'], 'icon' => 'award', 'color' => 'emerald'],
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
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Tren 6 Bulan Terakhir</h3>
            <div class="h-64">
                <canvas id="chartTren"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Top 10 Peserta Terbanyak</h3>
            <div class="h-64">
                <canvas id="chartPeserta"></canvas>
            </div>
        </div>
    </div>

    {{-- Daftar Sosialisasi --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Daftar Kegiatan Sosialisasi</h3>
        </div>
        @if($sosialisasis->isEmpty())
            <div class="flex items-center justify-center h-32 text-sm text-slate-400">
                Tidak ada kegiatan sosialisasi bulan ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-400">Tanggal</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-400">Judul</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-400">Peserta</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-400">Evaluasi</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($sosialisasis as $sosialisasi)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $sosialisasi->tanggal->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ $sosialisasi->judul }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $sosialisasi->pesertas->count() }} orang</td>
                                <td class="px-4 py-3">
                                    @if($sosialisasi->hasil_evaluasi)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400">
                                            Sudah
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">
                                            Belum
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.resources.show', ['sosialisasi', $sosialisasi]) }}" class="text-violet-600 hover:text-violet-700 font-medium">
                                        Lihat Detail
                                    </a>
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
    const pesertaData = @json($distribusiPeserta);

    // Chart Tren
    new Chart(document.getElementById('chartTren'), {
        type: 'line',
        data: {
            labels: trenData.map(d => d.bulan),
            datasets: [
                {
                    label: 'Sosialisasi',
                    data: trenData.map(d => d.sosialisasi),
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Peserta',
                    data: trenData.map(d => d.peserta),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
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
                    ticks: {
                        stepSize: 1,
                    },
                },
            },
        },
    });

    // Chart Peserta
    new Chart(document.getElementById('chartPeserta'), {
        type: 'bar',
        data: {
            labels: pesertaData.map(d => d.nama.length > 20 ? d.nama.substring(0, 20) + '...' : d.nama),
            datasets: [{
                label: 'Jumlah Kehadiran',
                data: pesertaData.map(d => d.jumlah),
                backgroundColor: '#8b5cf6',
                borderRadius: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false,
                },
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                    },
                },
            },
        },
    });
</script>
@endpush
@endsection
