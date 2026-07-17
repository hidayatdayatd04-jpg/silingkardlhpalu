@extends('layouts.admin')

@section('title', 'Laporan & Statistik Tata Penataan — Admin DLH')
@section('heading', 'Laporan Tata Penataan')

@section('content')
@php
    $bulanList = collect(range(11, 0))->map(fn ($i) => [
        'value' => \Carbon\Carbon::now()->subMonths($i)->format('Y-m'),
        'label' => \Carbon\Carbon::now()->subMonths($i)->translatedFormat('F Y'),
    ]);
@endphp

<x-admin.page-header
    title="Laporan & Statistik Tata Penataan"
    subtitle="Rekapitulasi data pengawasan, pelanggaran, dan sanksi"
    icon="bar-chart"
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Laporan Tata Penataan'],
    ]"
>
    <x-slot:actions>
        <form method="GET" class="flex items-center gap-2">
            <select name="bulan" onchange="this.form.submit()" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                @foreach($bulanList as $item)
                    <option value="{{ $item['value'] }}" {{ $item['value'] === $bulan ? 'selected' : '' }}>{{ $item['label'] }}</option>
                @endforeach
            </select>
        </form>
        <x-admin.button variant="secondary" size="sm" icon="file-text" :href="route('admin.laporan-tata-penataan.export-pdf')">
            Export PDF
        </x-admin.button>
        <x-admin.button variant="secondary" size="sm" icon="download" :href="route('admin.laporan-tata-penataan.export-excel')">
            Export Excel
        </x-admin.button>
    </x-slot:actions>
</x-admin.page-header>

{{-- Stat Cards --}}
<section class="grid gap-5 md:grid-cols-2 xl:grid-cols-5">
    <x-admin.stat-card label="Pengaduan" :value="$rekap['pengaduan']" icon="megaphone" color="amber" />
    <x-admin.stat-card label="Sidak" :value="$rekap['sidak']" icon="clipboard-check" color="blue" />
    <x-admin.stat-card label="Pelanggaran" :value="$rekap['pelanggaran']" icon="alert-triangle" color="orange" />
    <x-admin.stat-card label="Sanksi" :value="$rekap['sanksi']" icon="shield" color="red" />
    <x-admin.stat-card label="Sanksi Selesai" :value="$rekap['sanksi_selesai']" icon="check-circle" color="green" />
</section>

{{-- Charts --}}
<section class="grid gap-6 lg:grid-cols-3">
    <x-admin.card class="lg:col-span-2">
        <div class="mb-4">
            <h2 class="text-h4 font-bold text-ink-900">Tren 6 Bulan</h2>
            <p class="text-xs text-slate-500">Pengaduan, Sidak, dan Pelanggaran</p>
        </div>
        <div class="relative h-72">
            <canvas id="chartTren"></canvas>
        </div>
    </x-admin.card>

    <div class="space-y-6">
        <x-admin.card>
            <div class="mb-4">
                <h2 class="text-h4 font-bold text-ink-900">Jenis Pelanggaran</h2>
                <p class="text-xs text-slate-500">Tahun {{ now()->year }}</p>
            </div>
            <div class="relative h-48">
                <canvas id="chartPelanggaran"></canvas>
            </div>
        </x-admin.card>

        <x-admin.card>
            <div class="mb-4">
                <h2 class="text-h4 font-bold text-ink-900">Hasil Sidak</h2>
                <p class="text-xs text-slate-500">Tahun {{ now()->year }}</p>
            </div>
            <div class="relative h-48">
                <canvas id="chartSidak"></canvas>
            </div>
        </x-admin.card>
    </div>
</section>

{{-- Rekap Bulanan --}}
<section class="mt-6">
    <x-admin.card>
        <div class="mb-4">
            <h2 class="text-h4 font-bold text-ink-900">Rekap Bulanan</h2>
            <p class="text-xs text-slate-500">6 bulan terakhir</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700">
                        <th class="pb-3 text-left font-semibold text-slate-600 dark:text-slate-400">Bulan</th>
                        <th class="pb-3 text-center font-semibold text-slate-600 dark:text-slate-400">Pengaduan</th>
                        <th class="pb-3 text-center font-semibold text-slate-600 dark:text-slate-400">Sidak</th>
                        <th class="pb-3 text-center font-semibold text-slate-600 dark:text-slate-400">Pelanggaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($tren as $row)
                        <tr>
                            <td class="py-3 font-medium text-ink-800 dark:text-ink-200">{{ $row['bulan'] }}</td>
                            <td class="py-3 text-center text-slate-600 dark:text-slate-400">{{ $row['pengaduan'] }}</td>
                            <td class="py-3 text-center text-slate-600 dark:text-slate-400">{{ $row['sidak'] }}</td>
                            <td class="py-3 text-center text-slate-600 dark:text-slate-400">{{ $row['pelanggaran'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trenData = @json($tren);
    const grafikPelanggaran = @json($distribusiPelanggaran);
    const grafikSidak = @json($distribusiSidak);

    // Chart Tren
    new Chart(document.getElementById('chartTren'), {
        type: 'line',
        data: {
            labels: trenData.map(d => d.bulan),
            datasets: [
                { label: 'Pengaduan', data: trenData.map(d => d.pengaduan), borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.1)', fill: true, tension: 0.4 },
                { label: 'Sidak', data: trenData.map(d => d.sidak), borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.4 },
                { label: 'Pelanggaran', data: trenData.map(d => d.pelanggaran), borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)', fill: true, tension: 0.4 },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true } } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Chart Pelanggaran
    const pelLabels = Object.keys(grafikPelanggaran);
    if (pelLabels.length > 0) {
        new Chart(document.getElementById('chartPelanggaran'), {
            type: 'doughnut',
            data: {
                labels: pelLabels,
                datasets: [{ data: Object.values(grafikPelanggaran), backgroundColor: ['#f59e0b', '#ef4444', '#3b82f6', '#10b981', '#8b5cf6'], borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { padding: 8, usePointStyle: true, pointStyle: 'circle', font: { size: 10 } } } } }
        });
    }

    // Chart Sidak
    const sidakLabels = Object.keys(grafikSidak);
    if (sidakLabels.length > 0) {
        new Chart(document.getElementById('chartSidak'), {
            type: 'doughnut',
            data: {
                labels: sidakLabels,
                datasets: [{ data: Object.values(grafikSidak), backgroundColor: ['#10b981', '#f59e0b', '#ef4444'], borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { padding: 8, usePointStyle: true, pointStyle: 'circle', font: { size: 10 } } } } }
        });
    }
});
</script>
@endpush
@endsection
