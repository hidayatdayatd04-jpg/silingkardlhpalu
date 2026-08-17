@extends('layouts.admin')

@section('title', $resource['label'].' - Admin DLH')
@section('heading', $resource['label'])

@php
    $isUser = $resource['slug'] === 'user';
@endphp

@section('content')
    @php
        $format = function ($value, $column = null) {
            if ($column === 'jenis_kegiatan' && filled($value)) {
                return $value === 'monitoring-evaluasi' ? 'Monitoring & Evaluasi' : 'Sosialisasi';
            }
            if ($value instanceof BackedEnum) {
                return method_exists($value, 'label') ? $value->label() : $value->value;
            }
            if ($value instanceof DateTimeInterface) {
                return $value->format('d M Y H:i');
            }
            if (is_bool($value)) {
                return $value ? 'Ya' : 'Tidak';
            }
            if (is_array($value)) {
                return json_encode($value);
            }
            return filled($value) ? (string) $value : '-';
        };

        $recordIds = $records->pluck('id')->toArray();

        $filterKeys = collect($resource['filters'])->pluck('key')->all();

        $statusVariant = function ($status) {
            $map = [
                'Belum Ditinjau' => 'warning',
                'Ditinjau' => 'success',
                'Belum Ditindaklanjuti' => 'warning',
                'Ditindaklanjuti' => 'success',
                'Pending' => 'warning',
                'Disetujui' => 'success',
                'Ditolak' => 'danger',
                'Selesai' => 'success',
            ];
            return $map[$status] ?? 'neutral';
        };

        $avatarColumn = collect($resource['columns'])->first(fn ($c) => str_contains($c, 'nama') || str_contains($c, 'pelapor'));
    @endphp

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
                const tracked = ['q', 'status', 'date_from', 'date_to'].concat(this.filterKeys || []);
                const hasFilter = tracked.some(k => {
                    const v = q.get(k);
                    return v !== null && v !== '';
                });
                return hasFilter ? 'Hasil Filter' : 'Semua Data';
            }
        }"
        x-on:bulk-export.window="bulkExport()"
    >

    {{-- Page Header --}}
    <x-admin.page-header
        :title="$resource['label']"
        :subtitle="$records->total() . ' data terdaftar'"
        icon="{{ $isUser ? 'users' : 'folder' }}"
    >
        <x-slot:actions>
            @if($isUser)
                <div class="hidden items-center gap-2 sm:flex">
                    <div class="flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 border border-emerald-100">
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                        </span>
                        <span class="text-xs font-bold text-emerald-700">{{ collect($records->items())->where('is_active', true)->count() }} Aktif</span>
                    </div>
                </div>
            @endif
            @if(($resource['can_create'] ?? true))
                <x-admin.button variant="primary" icon="plus" size="sm" :href="route('admin.resources.create', $resource['slug'])">
                    Tambah
                </x-admin.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.card :padding="false" style="opacity: 1 !important; transform: none !important;">
        <x-admin.bulk-actions-bar :resource="$resource" />

        {{-- Toolbar --}}
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
        <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 bg-slate-50/50 px-6 py-3">
            <form method="GET" class="flex flex-1 items-center gap-2 lg:max-w-sm">
                @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                @if(request('direction'))<input type="hidden" name="direction" value="{{ request('direction') }}">@endif
                @foreach($resource['filters'] as $f)
                    @if($f['type'] === 'multiselect')
                        @foreach((array) request($f['key'], []) as $val)
                            @if(filled($val))<input type="hidden" name="{{ $f['key'] }}[]" value="{{ $val }}">@endif
                        @endforeach
                    @elseif($f['type'] === 'select')
                        @if(filled(request($f['key'])))<input type="hidden" name="{{ $f['key'] }}" value="{{ request($f['key']) }}">@endif
                    @elseif($f['type'] === 'daterange')
                        @if(request($f['key'].'_from'))<input type="hidden" name="{{ $f['key'] }}_from" value="{{ request($f['key'].'_from') }}">@endif
                        @if(request($f['key'].'_to'))<input type="hidden" name="{{ $f['key'] }}_to" value="{{ request($f['key'].'_to') }}">@endif
                    @endif
                @endforeach

<div class="relative flex-1">
                    <input name="q" value="{{ $search }}" placeholder="Cari {{ Str::lower($resource['label']) }}..."
                        class="h-10 w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 hover:border-slate-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                </div>
            </form>

            <div class="hidden flex-1 lg:block"></div>

            <form method="GET" class="contents">
                @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
                @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                @if(request('direction'))<input type="hidden" name="direction" value="{{ request('direction') }}">@endif

                <x-admin.filter-dropdown label="Filter" :active="$hasFilters" :count="$filterCount">
                    @foreach($resource['filters'] as $f)
                        @if($f['type'] === 'multiselect')
                            <x-admin.filter-section :title="$f['label']">
                                @foreach($f['options'] as $value => $label)
                                    <x-admin.filter-checkbox name="{{ $f['key'] }}[]" :label="$label" :value="$value"
                                        :checked="in_array((string) $value, array_map('strval', (array) request($f['key'], [])), true)" />
                                @endforeach
                            </x-admin.filter-section>
                        @elseif($f['type'] === 'select')
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

        {{-- Active filters --}}
        @php
            $activeFilters = collect();
            foreach ($resource['filters'] as $f) {
                if ($f['type'] === 'daterange') {
                    if (request()->filled($f['key'].'_from')) {
                        $activeFilters->push([
                            'label' => $f['label'].': '.\Carbon\Carbon::parse(request($f['key'].'_from'))->format('d M Y'),
                            'removeUrl' => request()->fullUrlWithQuery([$f['key'].'_from' => null]),
                        ]);
                    }
                    if (request()->filled($f['key'].'_to')) {
                        $activeFilters->push([
                            'label' => $f['label'].': s/d '.\Carbon\Carbon::parse(request($f['key'].'_to'))->format('d M Y'),
                            'removeUrl' => request()->fullUrlWithQuery([$f['key'].'_to' => null]),
                        ]);
                    }
                } elseif ($f['type'] === 'multiselect') {
                    foreach ((array) request($f['key'], []) as $val) {
                        if (filled($val) && isset($f['options'][$val])) {
                            $activeFilters->push([
                                'label' => $f['label'].': '.$f['options'][$val],
                                'removeUrl' => request()->fullUrlWithQuery([$f['key'] => array_values(array_diff((array) request($f['key']), [$val]))]),
                            ]);
                        }
                    }
                } elseif ($f['type'] === 'select') {
                    $val = request($f['key']);
                    if (filled($val) && isset($f['options'][$val])) {
                        $activeFilters->push([
                            'label' => $f['label'].': '.$f['options'][$val],
                            'removeUrl' => request()->fullUrlWithQuery([$f['key'] => null]),
                        ]);
                    }
                }
            }
        @endphp

        @if($activeFilters->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 bg-emerald-50/30 px-6 py-3">
                <span class="text-sm font-semibold text-slate-600">Filter aktif:</span>
                @foreach($activeFilters as $filter)
                    <x-admin.filter-badge :label="$filter['label']" :removeUrl="$filter['removeUrl']" />
                @endforeach
                <a href="{{ route('admin.resources.index', $resource['slug']) }}" class="ml-2 text-sm font-bold text-slate-500 transition hover:text-danger-600">
                    Hapus Semua
                </a>
            </div>
        @endif

        {{-- Data table --}}
        <div style="opacity: 1 !important; transform: none !important; display: block !important;">
        <x-admin.table>
            <thead class="bg-slate-50">
                <tr>
                    <x-admin.table.checkbox-header />
                    @foreach ($resource['columns'] as $column)
                        <x-admin.table.header sortable :column="$column"
                            :direction="$sortColumn === $column ? $sortDirection : null">
                            {{ Str::headline($column) }}
                        </x-admin.table.header>
                    @endforeach
                    <x-admin.table.header class="text-center">Aksi</x-admin.table.header>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($records as $record)
                    <x-admin.table.row class="transition hover:bg-emerald-50/30">
                        <x-admin.table.checkbox-cell :value="$record->id" />
                        @foreach ($resource['columns'] as $column)
                            <x-admin.table.cell>
                                @if($isUser && $column === 'name')
                                    <span class="inline-flex items-center gap-3">
                                    <span class="relative shrink-0">
                                        <x-admin.avatar :name="$format($record->{$column} ?? null, $column)" :src="$record->photoUrl()" size="sm" />
                                        @if($record->is_active)
                                            <span class="absolute -bottom-0.5 -right-0.5 size-3 rounded-full border-2 border-white bg-success-500"></span>
                                        @endif
                                    </span>
                                        <span class="flex min-w-0 flex-col">
                                            <span class="truncate text-sm font-semibold text-slate-900">{{ $format($record->{$column} ?? null, $column) }}</span>
                                            @if($record->primaryRoleName())
                                                <span class="text-xs text-slate-500">{{ $record->primaryRoleName() }}</span>
                                            @endif
                                        </span>
                                    </span>
                                @elseif($isUser && $column === 'is_active')
                                    @if($record->is_active)
                                        <x-admin.status-pill variant="success" label="Aktif" pulse />
                                    @else
                                        <x-admin.status-pill variant="neutral" label="Nonaktif" />
                                    @endif
                                @elseif($isUser && $column === 'role')
                                    @php
                                        $roleName = $record->primaryRoleName();
                                        $roleEnum = \App\Enums\AdminRole::tryFrom($roleName);
                                    @endphp
                                    @if($roleEnum)
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold
                                            {{ match($roleEnum) {
                                                \App\Enums\AdminRole::ADMIN => 'bg-indigo-50 text-indigo-700 border border-indigo-100',
                                                \App\Enums\AdminRole::BIDANG_PENGENDALIAN => 'bg-blue-50 text-blue-700 border border-blue-100',
                                                \App\Enums\AdminRole::BIDANG_SAMPAH_LB3 => 'bg-amber-50 text-amber-700 border border-amber-100',
                                                \App\Enums\AdminRole::BIDANG_TATA_PENATAAN => 'bg-slate-100 text-slate-700 border border-slate-200',
                                                \App\Enums\AdminRole::BIDANG_RTH => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                                                default => 'bg-slate-100 text-slate-600 border border-slate-200',
                                            } }}">
                                            <x-admin.icon :name="$roleEnum->icon()" :size="12" />
                                            {{ $roleEnum->label() }}
                                        </span>
                                    @else
                                        <span class="text-sm text-slate-400">-</span>
                                    @endif
                                @elseif($isUser && $column === 'email')
                                    <span class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                                        <x-admin.icon name="mail" :size="14" class="text-slate-400" />
                                        {{ $format($record->{$column}, $column) }}
                                    </span>
                                @elseif($isUser && $column === 'username')
                                    <span class="inline-flex items-center gap-1.5 font-mono text-sm font-semibold text-slate-700">
                                        <x-admin.icon name="user" :size="14" class="text-slate-400" />
                                        {{ $format($record->{$column}, $column) }}
                                    </span>
                                @elseif($column === 'status')
                                    @php
                                        $statusText = $format($record->{$column} ?? null, $column);
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-admin.status-pill :variant="$statusVariant($statusText)" :label="$statusText" />
                                    </span>
                                @elseif($column === 'nomor_tiket' || str_contains($column, 'nomor'))
                                    <span class="inline-flex items-center gap-1.5 font-mono text-sm font-bold text-slate-900">
                                        <x-admin.icon name="file-text" :size="14" class="text-slate-400" />
                                        {{ $format($record->{$column}, $column) }}
                                    </span>
                                @elseif($column === $avatarColumn)
                                    <span class="inline-flex items-center gap-2.5">
                                        <x-admin.avatar :name="$format($record->{$column} ?? null, $column)" size="sm" />
                                        <span class="text-sm font-semibold text-slate-900">{{ $format($record->{$column} ?? null, $column) }}</span>
                                    </span>
                                @elseif(str_contains($column, 'created_at') || str_contains($column, 'updated_at') || str_contains($column, 'tanggal'))
                                    <span class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                                        <x-admin.icon name="calendar" :size="14" class="text-slate-400" />
                                        {{ $format($record->{$column}, $column) }}
                                    </span>
                                @else
                                    <span class="text-sm font-medium text-slate-700">{{ $format($record->{$column} ?? null, $column) }}</span>
                                @endif
                            </x-admin.table.cell>
                        @endforeach
                        <x-admin.table.cell class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.resources.show', [$resource['slug'], $record]) }}"
                                   class="group grid size-9 place-items-center rounded-xl text-slate-400 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600 hover:shadow-sm" title="Detail" aria-label="Detail">
                                    <x-admin.icon name="eye" :size="16" class="transition-transform duration-200 group-hover:scale-110" />
                                </a>
                                <a href="{{ route('admin.resources.edit', [$resource['slug'], $record]) }}"
                                   class="group grid size-9 place-items-center rounded-xl text-slate-400 transition-all duration-200 hover:bg-emerald-50 hover:text-emerald-600 hover:shadow-sm" title="Edit" aria-label="Edit">
                                    <x-admin.icon name="edit" :size="16" class="transition-transform duration-200 group-hover:scale-110" />
                                </a>
                                <button type="button"
                                    x-data="" x-on:click="$dispatch('open-modal', 'del-{{ $record->id }}')"
                                    class="group grid size-9 place-items-center rounded-xl text-slate-400 transition-all duration-200 hover:bg-red-50 hover:text-red-600 hover:shadow-sm" title="Hapus" aria-label="Hapus">
                                    <x-admin.icon name="trash" :size="16" class="transition-transform duration-200 group-hover:scale-110" />
                                </button>
                            </div>

                            <x-admin.confirm-delete
                                name="del-{{ $record->id }}"
                                :action="route('admin.resources.destroy', [$resource['slug'], $record])"
                                title="Hapus Data"
                                message="Data ini akan dihapus permanen. Aksi ini tidak bisa dibatalkan."
                            />
                        </x-admin.table.cell>
                    </x-admin.table.row>
                @empty
                    <tr>
                        <td colspan="{{ count($resource['columns']) + 2 }}">
                            <x-admin.empty-state
                                icon="folder"
                                title="Belum ada data"
                                description="Data akan muncul di sini. Ubah kata kunci pencarian jika diperlukan."
                                :actionText="($resource['can_create'] ?? true) ? 'Tambah Data Pertama' : null"
                                :actionUrl="($resource['can_create'] ?? true) ? route('admin.resources.create', $resource['slug']) : null"
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.table>
        </div>

        <div class="border-t border-slate-100 px-6 py-4">
            {{ $records->links() }}
        </div>
    </x-admin.card>
@endsection
