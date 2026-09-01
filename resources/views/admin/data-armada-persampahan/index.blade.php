@extends('layouts.admin')

@section('title', $resource['label'].' - SILINGKAR DLH ADMIN')
@section('heading', $resource['label'])

@php
    $canEdit = $resource['can_edit'] ?? true;
    $canCreate = ($resource['can_create'] ?? true) && (!auth()->user()?->isSuperadmin() || ($resource['group'] ?? null) === 'konten');
    $recordIds = $records->pluck('id')->toArray();

    $sheetConfig = [
        'Kendaraan Roda 2' => [
            'icon' => 'truck',
            'accent' => 'sky',
        ],
        'Kendaraan Roda 4' => [
            'icon' => 'truck',
            'accent' => 'emerald',
        ],
        'Kendaraan Roda 6' => [
            'icon' => 'truck',
            'accent' => 'amber',
        ],
        'Alat Berat' => [
            'icon' => 'excavator',
            'accent' => 'rose',
        ],
    ];
@endphp

@section('content')
<div
    x-data="{
        selected: [],
        selectAll: false,
        items: {{ json_encode($recordIds) }},
        bulkExport(format = 'xlsx') {
            const params = new URLSearchParams();
            this.selected.forEach(id => params.append('ids[]', id));
            params.append('format', format);
            window.location.href = '{{ route('admin.resources.bulk-export', $resource['slug']) }}?' + params.toString();
        },
        exportHref(format) {
            if (this.selected.length > 0) {
                const params = new URLSearchParams();
                this.selected.forEach(id => params.append('ids[]', id));
                params.append('format', format);
                return '{{ route('admin.resources.bulk-export', $resource['slug']) }}?' + params.toString();
            }
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);
            params.delete('page');
            params.set('format', format);
            return '{{ route('admin.resources.export', $resource['slug']) }}?' + params.toString();
        },
        exportScopeLabel() {
            if (this.selected.length > 0) {
                return 'Terpilih (' + this.selected.length + ')';
            }
            return 'Semua Data';
        }
    }"
    x-on:bulk-export.window="bulkExport()"
    class="space-y-6"
>
    {{-- Header Page --}}
    <x-admin.page-header
        :title="$resource['label']"
        subtitle="Kelola inventaris dan data armada persampahan Dinas Lingkungan Hidup Kota Palu berdasarkan kategori kendaraan."
        icon="truck"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => $resource['label']],
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <span class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3.5 py-1.5 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <x-admin.icon name="truck" :size="14" class="text-teal-600 dark:text-teal-400" />
                    Total Keseluruhan: {{ number_format($totalKeseluruhanUnits) }} Unit
                </span>
                @if($canCreate)
                    <x-admin.button variant="primary" size="sm" icon="plus" :href="route('admin.resources.create', $resource['slug'])">
                        Tambah {{ $resource['label'] }}
                    </x-admin.button>
                @endif
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- 4 Kategori Stat Cards --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($categories as $cat)
            @php
                $cfg = $sheetConfig[$cat] ?? ['icon' => 'truck', 'accent' => 'teal'];
                $units = $categoryUnits[$cat] ?? 0;
                $catRecord = $records->first(fn ($r) => ($r->kategori instanceof \BackedEnum ? $r->kategori->value : $r->kategori) === $cat);
            @endphp
            <div class="group relative flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all duration-200 hover:border-teal-500/40 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700 transition-colors group-hover:bg-teal-600 group-hover:text-white dark:bg-teal-950/50 dark:text-teal-300">
                            <x-admin.icon :name="$cfg['icon']" :size="18" />
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $cat }}</h3>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex items-end justify-between border-t border-slate-100 pt-3 dark:border-slate-800">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Total Unit</span>
                    <div class="text-right">
                        <span class="text-lg font-extrabold text-teal-700 dark:text-teal-300">{{ number_format($units) }}</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Unit</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Main Table Card --}}
    <x-admin.card :padding="false" class="overflow-hidden">
        <x-admin.bulk-actions-bar :resource="$resource" />

        {{-- Toolbar: Search & Export --}}
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/70 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/35 sm:px-6 lg:flex-row lg:items-center">
            <form method="GET" role="search" class="flex w-full flex-1 items-center gap-2 lg:max-w-md">
                @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                @if(request('direction'))<input type="hidden" name="direction" value="{{ request('direction') }}">@endif

                <div class="relative flex-1">
                    <label for="resource-search" class="sr-only">Cari {{ $resource['label'] }}</label>
                    <x-admin.icon name="search" :size="17" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500" aria-hidden="true" />
                    <input id="resource-search" name="q" value="{{ $search }}" placeholder="Cari kategori armada..."
                        class="h-10 w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-3.5 text-sm text-slate-950 outline-none transition-[border-color,box-shadow,background-color] duration-150 placeholder:text-slate-400 hover:border-slate-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:hover:border-slate-600 dark:focus:border-brand-400 dark:focus:ring-brand-400/20">
                </div>
            </form>

            <div class="hidden flex-1 lg:block"></div>

            <x-admin.export-icons :resource="$resource['slug']" :selectedCount="count($recordIds)" :filter-keys="[]" />
        </div>

        {{-- Data Table --}}
        <x-admin.table aria-label="Daftar {{ $resource['label'] }}">
            <thead class="bg-slate-50 dark:bg-slate-900">
                <tr>
                    <x-admin.table.checkbox-header />
                    <x-admin.table.header sortable column="kategori" :direction="$sortColumn === 'kategori' ? $sortDirection : null">
                        Kategori Kendaraan
                    </x-admin.table.header>
                    <x-admin.table.header class="text-center">
                        Total Unit Terdaftar
                    </x-admin.table.header>
                    <x-admin.table.header class="text-center">Aksi</x-admin.table.header>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($records as $record)
                    @php
                        $catVal = $record->kategori instanceof \BackedEnum ? $record->kategori->value : $record->kategori;
                        $cfg = $sheetConfig[$catVal] ?? ['icon' => 'truck', 'accent' => 'teal'];
                        $unitCount = $record->totalUnit();
                    @endphp
                    <x-admin.table.row>
                        <x-admin.table.checkbox-cell :value="$record->id" />
                        <x-admin.table.cell>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-300">
                                    <x-admin.icon :name="$cfg['icon']" :size="18" />
                                </span>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">
                                        {{ $record->kategori instanceof \App\Enums\KategoriArmadaPersampahan ? $record->kategori->label() : ($record->kategori ?? '-') }}
                                    </span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $unitCount }} jenis/unit armada
                                    </span>
                                </div>
                            </div>
                        </x-admin.table.cell>
                        <x-admin.table.cell class="text-center">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-xs font-extrabold text-teal-700 dark:bg-teal-950/40 dark:text-teal-300">
                                <x-admin.icon :name="$cfg['icon']" :size="13" />
                                {{ number_format($unitCount) }} Unit
                            </span>
                        </x-admin.table.cell>
                        <x-admin.table.cell class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.resources.show', [$resource['slug'], $record]) }}"
                                   class="group grid size-9 place-items-center rounded-xl text-slate-400 outline-none transition-[background-color,color,box-shadow] duration-150 hover:bg-info-50 hover:text-info-700 hover:shadow-sm focus-visible:ring-2 focus-visible:ring-info-500/30 dark:text-slate-500 dark:hover:bg-info-950/35 dark:hover:text-info-300" title="Detail Armada" aria-label="Lihat detail {{ $catVal }}">
                                     <x-admin.icon name="eye" :size="16" class="transition-transform duration-150 group-hover:scale-105" aria-hidden="true" />
                                </a>
                                @if($canEdit)
                                    <a href="{{ route('admin.resources.edit', [$resource['slug'], $record]) }}"
                                       class="group grid size-9 place-items-center rounded-xl text-slate-400 outline-none transition-[background-color,color,box-shadow] duration-150 hover:bg-brand-50 hover:text-brand-700 hover:shadow-sm focus-visible:ring-2 focus-visible:ring-brand-600/30 dark:text-slate-500 dark:hover:bg-brand-950/45 dark:hover:text-brand-300" title="Kelola Daftar Armada" aria-label="Edit {{ $catVal }}">
                                          <x-admin.icon name="edit" :size="16" class="transition-transform duration-150 group-hover:scale-105" aria-hidden="true" />
                                    </a>
                                @endif
                            </div>
                        </x-admin.table.cell>
                    </x-admin.table.row>
                @empty
                    <tr>
                        <td colspan="4">
                            <x-admin.empty-state
                                icon="truck"
                                title="Belum ada data armada persampahan"
                                description="Data kategori armada persampahan akan muncul di sini."
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.table>

        {{-- Bottom Total Section: Total Keseluruhan --}}
        <div class="border-t border-slate-200/80 bg-slate-50/90 px-4 py-4 dark:border-slate-800 dark:bg-slate-900/60 sm:px-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-3.5 py-2 text-white shadow-xs">
                        <x-admin.icon name="truck" :size="16" class="text-white" />
                        <span class="text-xs font-bold uppercase tracking-wider">Total Keseluruhan</span>
                    </div>

                    <div class="text-sm text-slate-700 dark:text-slate-300">
                        <span>Total Keseluruhan Armada:</span>
                        <span class="font-extrabold text-teal-700 dark:text-teal-400 text-base">{{ number_format($totalKeseluruhanUnits) }} Unit</span>
                    </div>
                </div>

                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Menampilkan {{ $records->count() }} kategori kendaraan
                </div>
            </div>
        </div>
    </x-admin.card>
</div>
@endsection
