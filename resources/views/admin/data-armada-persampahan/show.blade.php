@extends('layouts.admin')

@php
    $catVal = $record->kategori instanceof \BackedEnum ? $record->kategori->value : $record->kategori;
    $canEdit = ($resource['can_edit'] ?? true) && (!auth()->user()?->isSuperadmin() || ($resource['group'] ?? null) === 'konten');
    $armadaList = is_array($record->daftar_armada) ? $record->daftar_armada : [];
    $totalUnit = $record->totalUnit();
@endphp

@section('title', 'Detail '.$catVal.' - SILINGKAR DLH ADMIN')
@section('heading', $resource['label'])

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <x-admin.page-header
        :title="'Detail '.$catVal"
        subtitle="Rincian inventaris data armada persampahan DLH Kota Palu."
        icon="truck"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => $resource['label'], 'url' => route('admin.resources.index', $resource['slug'])],
            ['label' => $catVal],
        ]"
    >
        <x-slot:actions>
            <x-admin.button variant="secondary" size="sm" icon="arrow-left" :href="route('admin.resources.index', $resource['slug'])">
                Kembali
            </x-admin.button>
            @if($canEdit)
                <x-admin.button variant="primary" size="sm" icon="edit" :href="route('admin.resources.edit', [$resource['slug'], $record])">
                    Edit Data Armada
                </x-admin.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Section 1: Informasi Utama --}}
    <x-admin.card>
        <div class="mb-5 border-b border-slate-100 pb-4 dark:border-white/10">
            <div class="flex items-center gap-2.5">
                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-teal-600 text-xs font-bold text-white shadow-xs">
                    1
                </span>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Informasi Utama</h2>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kategori Kendaraan</span>
                <div class="flex items-center gap-2 pt-0.5">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-sm font-bold text-teal-800 dark:bg-teal-950/40 dark:text-teal-300">
                        <x-admin.icon name="truck" :size="15" />
                        {{ $catVal }}
                    </span>
                </div>
            </div>

            <div class="space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total {{ $catVal }}</span>
                <p class="text-lg font-extrabold text-teal-700 dark:text-teal-400">
                    {{ number_format($totalUnit) }} Unit
                </p>
            </div>
        </div>
    </x-admin.card>

    {{-- Section 2: Daftar Armada Persampahan --}}
    <x-admin.card>
        <div class="mb-5 border-b border-slate-100 pb-4 dark:border-white/10">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex size-7 items-center justify-center rounded-lg bg-teal-600 text-xs font-bold text-white shadow-xs">
                        2
                    </span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Daftar Armada Persampahan</h2>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-xs font-extrabold text-teal-800 dark:bg-teal-950/40 dark:text-teal-300">
                    <x-admin.icon name="truck" :size="13" />
                    Total {{ $catVal }}: {{ number_format($totalUnit) }} Unit
                </span>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-white/10">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-400">
                            <th class="w-14 px-4 py-3.5 text-center">NO</th>
                            <th class="px-4 py-3.5">MEREK / TYPE</th>
                            <th class="w-48 px-4 py-3.5">TAHUN PEROLEHAN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @forelse($armadaList as $i => $item)
                            <tr class="transition-colors hover:bg-slate-50/50 dark:hover:bg-white/[0.02]">
                                <td class="px-4 py-3 text-center font-bold text-slate-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                    {{ $item['merk_type'] ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-admin.icon name="calendar" :size="14" class="text-slate-400" />
                                        {{ $item['tahun_perolehan'] ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-400">
                                    Belum ada data armada yang tercatat pada kategori ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer Summary --}}
            <div class="flex items-center justify-between border-t border-slate-200/80 bg-slate-50/90 px-4 py-3.5 dark:border-slate-800 dark:bg-slate-900/60 sm:px-6">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Total {{ $catVal }}:</span>
                    <span class="text-sm font-extrabold text-teal-700 dark:text-teal-400">{{ number_format($totalUnit) }} Unit</span>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    {{ count($armadaList) }} data terdaftar
                </div>
            </div>
        </div>
    </x-admin.card>
</div>
@endsection
