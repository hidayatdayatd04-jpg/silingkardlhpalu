@extends('layouts.admin')

@section('title', 'Monitoring Pelanggaran & Sanksi — Admin DLH')
@section('heading', 'Monitoring Sanksi')

@section('content')
<x-admin.page-header
    title="Monitoring Pelanggaran & Sanksi"
    subtitle="Pantau pipeline pelanggaran dan status sanksi"
    icon="shield"
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Monitoring Sanksi'],
    ]"
>
    <x-slot:actions>
        <x-admin.button variant="secondary" size="sm" icon="download" :href="route('admin.monitoring-sanksi.export')">
            Export CSV
        </x-admin.button>
        <form method="POST" action="{{ route('admin.monitoring-sanksi.check-overdue') }}" class="inline">
            @csrf
            <x-admin.button variant="warning" size="sm" icon="bell" type="submit">
                Kirim Notifikasi Jatuh Tempo
            </x-admin.button>
        </form>
    </x-slot:actions>
</x-admin.page-header>

{{-- Stat Cards --}}
<section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
    <x-admin.stat-card label="Total Pelanggaran" :value="$totalPelanggaran" icon="alert-triangle" color="amber" />
    <x-admin.stat-card label="Total Sanksi" :value="$totalSanksi" icon="shield" color="blue" />
    <x-admin.stat-card label="Sanksi Aktif" :value="$sanksiAktif" icon="clock" color="orange" />
    <x-admin.stat-card label="Terlambat" :value="$sanksiTerlambat" icon="alert-circle" color="red" />
</section>

{{-- Charts --}}
<section class="grid gap-6 lg:grid-cols-3">
    <x-admin.card class="lg:col-span-1">
        <div class="mb-4">
            <h2 class="text-h4 font-bold text-ink-900">Jenis Pelanggaran</h2>
            <p class="text-xs text-slate-500">Distribusi tahun {{ now()->year }}</p>
        </div>
        <div class="relative h-64">
            <canvas id="chartPelanggaran"></canvas>
        </div>
    </x-admin.card>

    <x-admin.card class="lg:col-span-2">
        <div class="mb-4">
            <h2 class="text-h4 font-bold text-ink-900">Sanksi Mendekati Jatuh Tempo</h2>
            <p class="text-xs text-slate-500">Dalam 7 hari ke depan</p>
        </div>
        @if($sanksiMendekatiJatuhTempo->isEmpty())
            <div class="flex items-center justify-center h-64 text-sm text-slate-400">
                Tidak ada sanksi mendekati jatuh tempo.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="pb-3 text-left font-semibold text-slate-600 dark:text-slate-400">Jenis Sanksi</th>
                            <th class="pb-3 text-left font-semibold text-slate-600 dark:text-slate-400">Objek</th>
                            <th class="pb-3 text-left font-semibold text-slate-600 dark:text-slate-400">Batas Waktu</th>
                            <th class="pb-3 text-left font-semibold text-slate-600 dark:text-slate-400">Sisa Hari</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($sanksiMendekatiJatuhTempo as $sanksi)
                            @php $sisaHari = (int) now()->diffInDays($sanksi->batas_waktu_perbaikan, false); @endphp
                            <tr class="{{ $sisaHari <= 2 ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                                <td class="py-3 font-medium text-ink-800 dark:text-ink-200">{{ $sanksi->jenis_sanksi }}</td>
                                <td class="py-3 text-slate-600 dark:text-slate-400">{{ $sanksi->pelanggaran?->objekPengawasan?->nama_perusahaan ?? '-' }}</td>
                                <td class="py-3 text-slate-600 dark:text-slate-400">{{ $sanksi->batas_waktu_perbaikan->format('d M Y') }}</td>
                                <td class="py-3">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold {{ $sisaHari <= 2 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $sisaHari }} hari
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-admin.card>
</section>

{{-- Pipeline --}}
<section class="mt-6">
    <x-admin.card>
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-h4 font-bold text-ink-900">Pipeline Pelanggaran → Sanksi</h2>
                <p class="text-xs text-slate-500">20 data terbaru</p>
            </div>
        </div>
        @if($pipeline->isEmpty())
            <div class="flex items-center justify-center h-32 text-sm text-slate-400">
                Belum ada data pelanggaran.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="pb-3 text-left font-semibold text-slate-600 dark:text-slate-400">Tanggal</th>
                            <th class="pb-3 text-left font-semibold text-slate-600 dark:text-slate-400">Objek</th>
                            <th class="pb-3 text-left font-semibold text-slate-600 dark:text-slate-400">Jenis Pelanggaran</th>
                            <th class="pb-3 text-left font-semibold text-slate-600 dark:text-slate-400">Status Sanksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($pipeline as $pelanggaran)
                            <tr>
                                <td class="py-3 text-slate-600 dark:text-slate-400">{{ $pelanggaran->created_at->format('d M Y') }}</td>
                                <td class="py-3 font-medium text-ink-800 dark:text-ink-200">{{ $pelanggaran->objekPengawasan?->nama_perusahaan ?? '-' }}</td>
                                <td class="py-3 text-slate-600 dark:text-slate-400">{{ $pelanggaran->jenis_pelanggaran }}</td>
                                <td class="py-3">
                                    @if(! $pelanggaran->sanksi)
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">Belum ada sanksi</span>
                                    @else
                                        @php $statusClass = match($pelanggaran->sanksi->status_sanksi) { 'selesai' => 'bg-green-100 text-green-700', 'diberikan' => 'bg-blue-100 text-blue-700', default => 'bg-amber-100 text-amber-700' }; @endphp
                                        <span class="inline-flex items-center rounded-full {{ $statusClass }} px-2.5 py-0.5 text-xs font-medium">
                                            {{ $pelanggaran->sanksi->status_sanksi }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-admin.card>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const grafikData = @json($grafikPelanggaran);
    const labels = Object.keys(grafikData);
    const data = Object.values(grafikData);

    if (labels.length > 0) {
        new Chart(document.getElementById('chartPelanggaran'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ['#f59e0b', '#ef4444', '#3b82f6', '#10b981', '#8b5cf6', '#ec4899'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, pointStyle: 'circle' } }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
