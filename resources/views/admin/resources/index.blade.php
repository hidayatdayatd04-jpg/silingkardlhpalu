@extends('layouts.admin')

@section('title', $resource['label'].' - Admin DLH')
@section('heading', $resource['label'])

@section('content')
    @php
        $format = function ($value, $column = null) {
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

        // Peta status → variant status-pill
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

        // Kolom pertama yg mengandung 'nama' / 'pelapor' → tampil avatar
        $avatarColumn = collect($resource['columns'])->first(fn ($c) => str_contains($c, 'nama') || str_contains($c, 'pelapor'));
    @endphp

    <div
        x-data="{
            selected: [],
            selectAll: false,
            items: {{ json_encode($recordIds) }},
            bulkExport() {
                const params = new URLSearchParams();
                this.selected.forEach(id => params.append('ids[]', id));
                params.append('format', 'xlsx');
                window.location.href = '{{ route('admin.resources.bulk-export', $resource['slug']) }}?' + params.toString();
            }
        }"
        x-on:bulk-export.window="bulkExport()"
    >

    <x-admin.card :padding="false">
        <x-admin.bulk-actions-bar :resource="$resource" />

        <div class="border-b border-slate-200 bg-white">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <div class="flex items-center gap-2.5">
                    <div class="grid size-9 place-items-center rounded-lg text-white shadow-[var(--shadow-brand-glow)]" style="background: var(--gradient-brand);">
                        <x-admin.icon name="folder" :size="16" />
                    </div>
                    <div>
                        <h2 class="text-h4 font-bold text-ink-900">{{ $resource['label'] }}</h2>
                        <p class="text-xs text-slate-500">Total {{ $records->total() }} data</p>
                    </div>
                </div>
            </div>

            {{-- Toolbar --}}
            <div class="flex flex-wrap items-center gap-2 px-4 py-3">
                <form method="GET" class="flex flex-1 items-center gap-2 lg:max-w-sm">
                    @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                    @if(request('direction'))<input type="hidden" name="direction" value="{{ request('direction') }}">@endif
                    @foreach((array) request('status', []) as $status)
                        <input type="hidden" name="status[]" value="{{ $status }}">
                    @endforeach
                    @if(request('date_from'))<input type="hidden" name="date_from" value="{{ request('date_from') }}">@endif
                    @if(request('date_to'))<input type="hidden" name="date_to" value="{{ request('date_to') }}">@endif

                    <div class="relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-admin.icon name="search" class="text-slate-400" :size="16" />
                        </div>
                        <input name="q" value="{{ $search }}" placeholder="Cari data..."
                            class="h-9 w-full rounded-pill border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm text-ink-900 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <x-admin.button variant="primary" type="submit" icon="search" size="sm">Cari</x-admin.button>
                </form>

                <div class="hidden flex-1 lg:block"></div>

                @php
                    $hasFilters = request()->hasAny(['status', 'date_from', 'date_to']);
                    $filterCount = collect(request()->only(['status', 'date_from', 'date_to']))->filter()->count();
                @endphp

                <form method="GET" class="contents">
                    @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
                    @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                    @if(request('direction'))<input type="hidden" name="direction" value="{{ request('direction') }}">@endif

                    <x-admin.filter-dropdown label="Filter" :active="$hasFilters" :count="$filterCount">
                        <x-admin.filter-section title="Status">
                            @if(isset($resource['filters']['status']))
                                @foreach($resource['filters']['status'] as $value => $label)
                                    <x-admin.filter-checkbox name="status[]" :label="$label" :value="$value"
                                        :checked="in_array($value, (array) request('status', []))" />
                                @endforeach
                            @endif
                        </x-admin.filter-section>
                        <x-admin.filter-section title="Tanggal">
                            <div class="space-y-3 px-3">
                                <x-admin.input type="date" name="date_from" label="Dari" :value="request('date_from')" />
                                <x-admin.input type="date" name="date_to" label="Sampai" :value="request('date_to')" />
                            </div>
                        </x-admin.filter-section>
                        <x-admin.filter-actions :resetUrl="route('admin.resources.index', $resource['slug'])" />
                    </x-admin.filter-dropdown>
                </form>

                <x-admin.data-io :resource="$resource" :selectedCount="count($recordIds)" />

                <x-admin.button variant="primary" icon="plus" size="sm" :href="route('admin.resources.create', $resource['slug'])">
                    Tambah
                </x-admin.button>
            </div>
        </div>

        {{-- Active filters --}}
        @php
            $activeFilters = collect();
            if (request()->has('status') && is_array(request('status'))) {
                foreach (request('status') as $statusValue) {
                    if ($statusValue && isset($resource['filters']['status'][$statusValue])) {
                        $activeFilters->push([
                            'label' => 'Status: ' . $resource['filters']['status'][$statusValue],
                            'removeUrl' => request()->fullUrlWithQuery(['status' => array_values(array_diff((array) request('status'), [$statusValue]))]),
                        ]);
                    }
                }
            }
            if (request()->filled('date_from')) {
                $activeFilters->push(['label' => 'Dari: ' . \Carbon\Carbon::parse(request('date_from'))->format('d M Y'), 'removeUrl' => request()->fullUrlWithQuery(['date_from' => null])]);
            }
            if (request()->filled('date_to')) {
                $activeFilters->push(['label' => 'Sampai: ' . \Carbon\Carbon::parse(request('date_to'))->format('d M Y'), 'removeUrl' => request()->fullUrlWithQuery(['date_to' => null])]);
            }
        @endphp

        @if($activeFilters->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 px-6 py-4">
                <span class="text-sm font-semibold text-slate-600">Filter aktif:</span>
                @foreach($activeFilters as $filter)
                    <x-admin.filter-badge :label="$filter['label']" :removeUrl="$filter['removeUrl']" />
                @endforeach
                <a href="{{ route('admin.resources.index', $resource['slug']) }}" class="ml-2 text-sm font-bold text-slate-500 transition hover:text-danger-600">
                    Hapus Semua
                </a>
            </div>
        @endif

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
                    <x-admin.table.row class="transition hover:bg-brand-50/40">
                        <x-admin.table.checkbox-cell :value="$record->id" />
                        @foreach ($resource['columns'] as $column)
                            <x-admin.table.cell>
                                @if($column === 'status')
                                    @php
                                        $statusText = $format($record->{$column} ?? null, $column);
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-admin.status-pill :variant="$statusVariant($statusText)" :label="$statusText" />
                                    </span>
                                @elseif($column === 'nomor_tiket' || str_contains($column, 'nomor'))
                                    <span class="inline-flex items-center gap-1.5 font-mono text-sm font-bold text-ink-900">
                                        <x-admin.icon name="file-text" :size="14" class="text-slate-400" />
                                        {{ $format($record->{$column}, $column) }}
                                    </span>
                                @elseif($column === $avatarColumn)
                                    <span class="inline-flex items-center gap-2.5">
                                        <x-admin.avatar :name="$format($record->{$column} ?? null, $column)" size="sm" />
                                        <span class="text-sm font-semibold text-ink-800">{{ $format($record->{$column} ?? null, $column) }}</span>
                                    </span>
                                @elseif(str_contains($column, 'created_at') || str_contains($column, 'updated_at') || str_contains($column, 'tanggal'))
                                    <span class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                                        <x-admin.icon name="calendar" :size="14" class="text-slate-400" />
                                        {{ $format($record->{$column}, $column) }}
                                    </span>
                                @else
                                    <span class="text-sm font-medium text-ink-700">{{ $format($record->{$column} ?? null, $column) }}</span>
                                @endif
                            </x-admin.table.cell>
                        @endforeach
                        <x-admin.table.cell class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.resources.show', [$resource['slug'], $record]) }}"
                                   class="grid size-8 place-items-center rounded-lg text-slate-500 transition hover:bg-info-50 hover:text-info-600" title="Detail" aria-label="Detail">
                                    <x-admin.icon name="eye" :size="16" />
                                </a>
                                <a href="{{ route('admin.resources.edit', [$resource['slug'], $record]) }}"
                                   class="grid size-8 place-items-center rounded-lg text-slate-500 transition hover:bg-brand-50 hover:text-brand-600" title="Edit" aria-label="Edit">
                                    <x-admin.icon name="edit" :size="16" />
                                </a>
                                <button type="button"
                                    x-data="" x-on:click="$dispatch('open-modal', 'del-{{ $record->id }}')"
                                    class="grid size-8 place-items-center rounded-lg text-slate-500 transition hover:bg-danger-50 hover:text-danger-600" title="Hapus" aria-label="Hapus">
                                    <x-admin.icon name="trash" :size="16" />
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
                                description="Data akan muncul di sini. Tambahkan data baru atau ubah kata kunci pencarian."
                                actionText="Tambah Data Pertama"
                                :actionUrl="route('admin.resources.create', $resource['slug'])"
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.table>

        <div class="border-t border-slate-200 px-6 py-4">
            {{ $records->links() }}
        </div>
    </x-admin.card>
    </div>
@endsection
