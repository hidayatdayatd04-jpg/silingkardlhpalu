@extends('layouts.admin')

@section('title', $resource['label'].' - SILINGKAR DLH ADMIN')
@section('heading', $resource['label'])

@php
    $canEdit = $resource['can_edit'] ?? true;
    $canCreate = ($resource['can_create'] ?? true) && (!auth()->user()?->isSuperadmin() || ($resource['group'] ?? null) === 'konten');
    $recordIds = $records->pluck('id')->toArray();
    $filterKeys = collect($resource['filters'])->pluck('key')->all();

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
            'icon' => 'tool',
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
        filterKeys: {{ json_encode($filterKeys) }},
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
            const q = new URLSearchParams(window.location.search);
            const tracked = ['q', 'kategori', 'date_from', 'date_to'].concat(this.filterKeys || []);
            const hasFilter = tracked.some(k => {
                const v = q.get(k);
                return v !== null && v !== '';
            });
            return hasFilter ? 'Hasil Filter' : 'Semua Data';
        }
    }"
    x-on:bulk-export.window="bulkExport()"
    class="space-y-6"
>
    {{-- Header Page --}}
    <x-admin.page-header
        :title="$resource['label']"
        subtitle="Kelola inventaris dan data armada persampahan Dinas Lingkungan Hidup Kota Palu."
        icon="truck"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => $resource['label']],
        ]"
    >
        <x-slot:actions>
            <div class="hidden items-center gap-2 sm:flex">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <x-admin.icon name="truck" :size="14" class="text-teal-600 dark:text-teal-400" />
                    Total Keseluruhan: {{ number_format($totalKeseluruhanUnits) }} Unit
                </span>
            </div>
            @if($canCreate)
                <x-admin.button variant="primary" icon="plus" size="sm" :href="route('admin.resources.create', $resource['slug'])">
                    Tambah Armada
                </x-admin.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Sheet / Header Tab Selector --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pilihan Kategori Armada</h2>
            @if($activeCategory)
                <a href="{{ route('admin.resources.index', $resource['slug']) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    <span>Tampilkan Semua Kategori</span>
                    <x-admin.icon name="arrow-right" :size="12" />
                </a>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($categories as $cat)
                @php
                    $isActive = $activeCategory === $cat;
                    $cfg = $sheetConfig[$cat] ?? ['icon' => 'truck', 'accent' => 'teal'];
                    $units = $categoryUnits[$cat] ?? 0;
                    $targetUrl = $isActive
                        ? route('admin.resources.index', $resource['slug'])
                        : request()->fullUrlWithQuery(['kategori' => $cat, 'page' => null]);
                @endphp
                <a
                    href="{{ $targetUrl }}"
                    class="group relative flex flex-col justify-between rounded-2xl border p-4 transition-all duration-200 ease-out {{ $isActive ? 'border-teal-500/60 bg-gradient-to-br from-teal-50/90 to-emerald-50/50 shadow-md shadow-teal-500/10 ring-2 ring-teal-500/30 dark:border-teal-500/40 dark:from-teal-950/40 dark:to-emerald-950/20' : 'border-slate-200/80 bg-white hover:border-slate-300 hover:bg-slate-50/80 hover:shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700 dark:hover:bg-slate-850' }}"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl transition-colors {{ $isActive ? 'bg-teal-600 text-white shadow-sm shadow-teal-600/30' : 'bg-slate-100 text-slate-600 group-hover:bg-teal-50 group-hover:text-teal-700 dark:bg-slate-800 dark:text-slate-300 dark:group-hover:bg-teal-950/50 dark:group-hover:text-teal-300' }}">
                                <x-admin.icon :name="$cfg['icon']" :size="18" />
                            </span>
                            <div>
                                <h3 class="text-sm font-bold {{ $isActive ? 'text-teal-950 dark:text-teal-100' : 'text-slate-900 dark:text-white' }}">{{ $cat }}</h3>
                            </div>
                        </div>
                        @if($isActive)
                            <span class="inline-flex items-center rounded-full bg-teal-600 px-2 py-0.5 text-[10px] font-bold text-white shadow-xs">
                                Aktif
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 flex items-end justify-between border-t border-slate-200/60 pt-3 dark:border-slate-800">
                        <span class="text-xs text-slate-500 dark:text-slate-400">Total Unit</span>
                        <div class="text-right">
                            <span class="text-lg font-extrabold {{ $isActive ? 'text-teal-700 dark:text-teal-300' : 'text-slate-900 dark:text-white' }}">{{ number_format($units) }}</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Unit</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Main Table Card --}}
    <x-admin.card :padding="false" class="overflow-hidden">
        <x-admin.bulk-actions-bar :resource="$resource" />

        {{-- Toolbar: Search, Filter, Export --}}
        @php
            $hasFilters = collect($resource['filters'])->contains(fn ($f) =>
                $f['type'] === 'daterange'
                    ? (request()->filled($f['key'].'_from') || request()->filled($f['key'].'_to'))
                    : filled(request($f['key']))
            );
            $filterCount = collect($resource['filters'])->filter(function ($f) {
                if ($f['type'] === 'daterange') {
                    return request()->filled($f['key'].'_from') || request()->filled($f['key'].'_to');
                }
                $v = request($f['key']);
                return is_array($v) ? count(array_filter($v)) > 0 : filled($v);
            })->count();
        @endphp

        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/70 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/35 sm:px-6 lg:flex-row lg:items-center">
            <form method="GET" role="search" class="flex w-full flex-1 items-center gap-2 lg:max-w-md">
                @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                @if(request('direction'))<input type="hidden" name="direction" value="{{ request('direction') }}">@endif
                @if(request('kategori'))<input type="hidden" name="kategori" value="{{ request('kategori') }}">@endif
                @foreach($resource['filters'] as $f)
                    @if($f['key'] !== 'kategori')
                        @if($f['type'] === 'select' && filled(request($f['key'])))
                            <input type="hidden" name="{{ $f['key'] }}" value="{{ request($f['key']) }}">
                        @elseif($f['type'] === 'daterange')
                            @if(request($f['key'].'_from'))<input type="hidden" name="{{ $f['key'] }}_from" value="{{ request($f['key'].'_from') }}">@endif
                            @if(request($f['key'].'_to'))<input type="hidden" name="{{ $f['key'] }}_to" value="{{ request($f['key'].'_to') }}">@endif
                        @endif
                    @endif
                @endforeach

                <div class="relative flex-1">
                    <label for="resource-search" class="sr-only">Cari {{ $resource['label'] }}</label>
                    <x-admin.icon name="search" :size="17" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500" aria-hidden="true" />
                    <input id="resource-search" name="q" value="{{ $search }}" placeholder="Cari merek, tipe, atau tahun..."
                        class="h-10 w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-3.5 text-sm text-slate-950 outline-none transition-[border-color,box-shadow,background-color] duration-150 placeholder:text-slate-400 hover:border-slate-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:hover:border-slate-600 dark:focus:border-brand-400 dark:focus:ring-brand-400/20">
                </div>
            </form>

            <div class="hidden flex-1 lg:block"></div>

            <form method="GET" class="flex flex-wrap items-center gap-2">
                @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
                @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                @if(request('direction'))<input type="hidden" name="direction" value="{{ request('direction') }}">@endif

                <x-admin.filter-dropdown label="Filter" :active="$hasFilters" :count="$filterCount">
                    @foreach($resource['filters'] as $f)
                        @if($f['type'] === 'select')
                            <x-admin.filter-section :title="$f['label']">
                                <div class="px-1">
                                    <x-admin.select
                                        name="{{ $f['key'] }}"
                                        :options="$f['options']"
                                        :selected="request($f['key'])"
                                        placeholder="Semua"
                                    />
                                </div>
                            </x-admin.filter-section>
                        @elseif($f['type'] === 'daterange')
                            <x-admin.filter-section :title="$f['label']">
                                <div class="space-y-3 px-3">
                                    <x-admin.input type="date" name="{{ $f['key'] }}_from" label="Dari" :value="request($f['key'].'_from')" />
                                    <x-admin.input type="date" name="{{ $f['key'] }}_to" label="Sampai" :value="request($f['key'].'_to')" />
                                </div>
                            </x-admin.filter-section>
                        @endif
                    @endforeach
                    <x-admin.filter-actions :resetUrl="route('admin.resources.index', $resource['slug'])" />
                </x-admin.filter-dropdown>
            </form>

            <x-admin.export-icons :resource="$resource['slug']" :selectedCount="count($recordIds)" :filter-keys="$filterKeys" />
        </div>

        {{-- Active Filter Pills --}}
        @if($activeCategory || $search)
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 bg-teal-50/40 px-4 py-2.5 dark:border-slate-800 dark:bg-teal-950/20 sm:px-6">
                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Filter Aktif:</span>
                @if($activeCategory)
                    <x-admin.filter-badge :label="'Kategori: '.$activeCategory" :removeUrl="request()->fullUrlWithQuery(['kategori' => null])" />
                @endif
                @if($search)
                    <x-admin.filter-badge :label="'Pencarian: '.$search" :removeUrl="request()->fullUrlWithQuery(['q' => null])" />
                @endif
                <a href="{{ route('admin.resources.index', $resource['slug']) }}" class="ml-1 text-xs font-semibold text-danger-600 hover:text-danger-700 dark:text-danger-400">
                    Reset Filter
                </a>
            </div>
        @endif

        {{-- Data Table --}}
        <x-admin.table aria-label="Daftar {{ $resource['label'] }}">
            <thead class="bg-slate-50 dark:bg-slate-900">
                <tr>
                    <x-admin.table.checkbox-header />
                    <x-admin.table.header sortable column="kategori" :direction="$sortColumn === 'kategori' ? $sortDirection : null">
                        Kategori Kendaraan
                    </x-admin.table.header>
                    <x-admin.table.header sortable column="merk_type" :direction="$sortColumn === 'merk_type' ? $sortDirection : null">
                        Merek / Type
                    </x-admin.table.header>
                    <x-admin.table.header sortable column="tahun_perolehan" :direction="$sortColumn === 'tahun_perolehan' ? $sortDirection : null">
                        Tahun Perolehan
                    </x-admin.table.header>
                    <x-admin.table.header class="text-center">Aksi</x-admin.table.header>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($records as $record)
                    <x-admin.table.row>
                        <x-admin.table.checkbox-cell :value="$record->id" />
                        <x-admin.table.cell>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-300">
                                    <x-admin.icon name="truck" :size="15" />
                                </span>
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $record->kategori instanceof \App\Enums\KategoriArmadaPersampahan ? $record->kategori->label() : ($record->kategori ?? '-') }}
                                </span>
                            </div>
                        </x-admin.table.cell>
                        <x-admin.table.cell>
                            <span class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                {{ $record->merk_type ?? '-' }}
                            </span>
                        </x-admin.table.cell>
                        <x-admin.table.cell>
                            <span class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">
                                <x-admin.icon name="calendar" :size="14" class="text-slate-400" />
                                {{ $record->tahun_perolehan ?? '-' }}
                            </span>
                        </x-admin.table.cell>
                        <x-admin.table.cell class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.resources.show', [$resource['slug'], $record]) }}"
                                   class="group grid size-9 place-items-center rounded-xl text-slate-400 outline-none transition-[background-color,color,box-shadow] duration-150 hover:bg-info-50 hover:text-info-700 hover:shadow-sm focus-visible:ring-2 focus-visible:ring-info-500/30 dark:text-slate-500 dark:hover:bg-info-950/35 dark:hover:text-info-300" title="Detail" aria-label="Lihat detail {{ $record->merk_type }}">
                                     <x-admin.icon name="eye" :size="16" class="transition-transform duration-150 group-hover:scale-105" aria-hidden="true" />
                                </a>
                                @if($canEdit)
                                    <a href="{{ route('admin.resources.edit', [$resource['slug'], $record]) }}"
                                       class="group grid size-9 place-items-center rounded-xl text-slate-400 outline-none transition-[background-color,color,box-shadow] duration-150 hover:bg-brand-50 hover:text-brand-700 hover:shadow-sm focus-visible:ring-2 focus-visible:ring-brand-600/30 dark:text-slate-500 dark:hover:bg-brand-950/45 dark:hover:text-brand-300" title="Edit" aria-label="Edit {{ $record->merk_type }}">
                                          <x-admin.icon name="edit" :size="16" class="transition-transform duration-150 group-hover:scale-105" aria-hidden="true" />
                                    </a>
                                @endif
                                @if($canEdit)
                                    <button type="button"
                                        x-data="" x-on:click="$dispatch('open-modal', 'del-{{ $record->id }}')"
                                        class="group grid size-9 place-items-center rounded-xl text-slate-400 outline-none transition-[background-color,color,box-shadow] duration-150 hover:bg-danger-50 hover:text-danger-700 hover:shadow-sm focus-visible:ring-2 focus-visible:ring-danger-500/30 dark:text-slate-500 dark:hover:bg-danger-950/35 dark:hover:text-danger-300" title="Hapus" aria-label="Hapus {{ $record->merk_type }}">
                                         <x-admin.icon name="trash" :size="16" class="transition-transform duration-150 group-hover:scale-105" aria-hidden="true" />
                                    </button>
                                @endif
                            </div>

                            @if($canEdit)
                                <x-admin.confirm-delete
                                    name="del-{{ $record->id }}"
                                    :action="route('admin.resources.destroy', [$resource['slug'], $record])"
                                    title="Hapus Data Armada"
                                    message="Data armada ini akan dihapus permanen. Aksi ini tidak bisa dibatalkan."
                                />
                            @endif
                        </x-admin.table.cell>
                    </x-admin.table.row>
                @empty
                    <tr>
                        <td colspan="5">
                            <x-admin.empty-state
                                icon="truck"
                                title="Belum ada data armada persampahan"
                                description="{{ $activeCategory ? 'Belum ada armada pada kategori ' . $activeCategory . '.' : 'Data armada persampahan akan muncul di sini.' }}"
                                :actionText="$canCreate ? 'Tambah Armada Pertama' : null"
                                :actionUrl="$canCreate ? route('admin.resources.create', $resource['slug']) : null"
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

                    @if($activeCategory)
                        <div class="text-sm text-slate-700 dark:text-slate-300">
                            <span>Total Keseluruhan {{ $activeCategory }}:</span>
                            <span class="font-extrabold text-slate-900 dark:text-white">{{ number_format($categoryUnits[$activeCategory] ?? 0) }} Unit</span>
                        </div>
                        <span class="hidden text-slate-300 dark:text-slate-700 sm:inline">|</span>
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            Semua Kategori: <strong class="text-slate-700 dark:text-slate-300">{{ number_format($totalKeseluruhanUnits) }} Unit</strong>
                        </div>
                    @else
                        <div class="text-sm text-slate-700 dark:text-slate-300">
                            <span>Total Keseluruhan:</span>
                            <span class="font-extrabold text-teal-700 dark:text-teal-400 text-base">{{ number_format($totalKeseluruhanUnits) }} Unit</span>
                        </div>
                    @endif
                </div>

                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Menampilkan {{ $records->firstItem() ?? 0 }} - {{ $records->lastItem() ?? 0 }} dari {{ $records->total() }} data
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-slate-100 px-4 py-4 dark:border-slate-800 sm:px-6">
            {{ $records->links() }}
        </div>
    </x-admin.card>
</div>
@endsection
