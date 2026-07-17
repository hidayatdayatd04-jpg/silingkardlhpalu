@extends('layouts.admin')

@section('title', $resource['label'].' - Admin DLH')
@section('heading', $resource['label'])

@section('content')
<?php
    $indikatorLabels = [
        'indikator_1' => 'Persyaratan',
        'indikator_2' => 'Kecepatan',
        'indikator_3' => 'Biaya',
        'indikator_4' => 'Sarana',
        'indikator_5' => 'Petugas',
        'indikator_6' => 'Pengaduan',
        'indikator_7' => 'Hasil',
    ];

    $indikatorFullLabels = \App\Models\IkmResponse::$indikatorLabels;

    $scaleLabels = [
        1 => ['text' => 'Sangat Tidak Puas', 'class' => 'bg-red-100 text-red-700', 'bar' => 'bg-red-500'],
        2 => ['text' => 'Kurang Puas', 'class' => 'bg-orange-100 text-orange-700', 'bar' => 'bg-orange-500'],
        3 => ['text' => 'Puas', 'class' => 'bg-emerald-100 text-emerald-700', 'bar' => 'bg-emerald-500'],
        4 => ['text' => 'Sangat Puas', 'class' => 'bg-blue-100 text-blue-700', 'bar' => 'bg-blue-500'],
    ];

    $recordIds = $records->pluck('id')->toArray();
?>

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

        <div class="flex flex-wrap items-center gap-2 px-4 py-3">
            <form method="GET" class="flex flex-1 items-center gap-2 lg:max-w-sm">
                @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                @if(request('direction'))<input type="hidden" name="direction" value="{{ request('direction') }}">@endif
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

            <x-admin.data-io :resource="$resource" :selectedCount="count($recordIds)" />

            <x-admin.button variant="primary" icon="plus" size="sm" :href="route('admin.resources.create', $resource['slug'])">
                Tambah
            </x-admin.button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/80">
                    <th class="w-12 px-4 py-3 text-center">
                        <input type="checkbox" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    </th>
                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500">ID</th>
                    @foreach ($indikatorLabels as $key => $shortLabel)
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500" title="{{ $indikatorFullLabels[$key] }}">
                            <div class="flex items-center gap-1">
                                <span>{{ $shortLabel }}</span>
                                <div class="group relative">
                                    <x-admin.icon name="info" :size="12" class="text-slate-400" />
                                    <div class="absolute bottom-full left-1/2 z-10 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white shadow-lg group-hover:block">
                                        {{ $indikatorFullLabels[$key] }}
                                    </div>
                                </div>
                            </div>
                        </th>
                    @endforeach
                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500 text-center">Rata-rata</th>
                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500 max-w-[200px]">Saran</th>
                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500">Tanggal</th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($records as $record)
                    @php
                        $avg = $record->nilai_rata_rata;
                        if ($avg >= 3.5) {
                            $avgColor = 'text-blue-600 bg-blue-50';
                        } elseif ($avg >= 2.5) {
                            $avgColor = 'text-emerald-600 bg-emerald-50';
                        } elseif ($avg >= 1.5) {
                            $avgColor = 'text-orange-600 bg-orange-50';
                        } else {
                            $avgColor = 'text-red-600 bg-red-50';
                        }
                    @endphp
                    <tr class="transition hover:bg-brand-50/30">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" value="{{ $record->id }}" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs font-bold text-slate-500">#{{ $record->id }}</span>
                        </td>
                        @foreach ($indikatorLabels as $key => $shortLabel)
                            @php
                                $val = $record->{$key} ?? 0;
                                $scale = $scaleLabels[$val] ?? $scaleLabels[1];
                            @endphp
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-bold {{ $scale['class'] }}">
                                        {{ $val }}
                                    </span>
                                    <div class="hidden lg:block flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden max-w-[60px]">
                                        <div class="h-full rounded-full {{ $scale['bar'] }}" style="width: {{ ($val / 4) * 100 }}%"></div>
                                    </div>
                                </div>
                            </td>
                        @endforeach
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center min-w-[48px] rounded-lg px-2.5 py-1 text-sm font-bold {{ $avgColor }}">
                                {{ number_format($avg, 1) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 max-w-[200px]">
                            <p class="truncate text-sm text-slate-600" title="{{ $record->saran ?? '-' }}">
                                {{ $record->saran ?? '-' }}
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                                <x-admin.icon name="calendar" :size="14" class="text-slate-400" />
                                {{ $record->created_at?->format('d M Y H:i') }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.resources.show', [$resource['slug'], $record]) }}"
                                   class="grid size-8 place-items-center rounded-lg text-slate-500 transition hover:bg-info-50 hover:text-info-600" title="Detail">
                                    <x-admin.icon name="eye" :size="16" />
                                </a>
                                <a href="{{ route('admin.resources.edit', [$resource['slug'], $record]) }}"
                                   class="grid size-8 place-items-center rounded-lg text-slate-500 transition hover:bg-brand-50 hover:text-brand-600" title="Edit">
                                    <x-admin.icon name="edit" :size="16" />
                                </a>
                                <button type="button"
                                    x-data="" x-on:click="$dispatch('open-modal', 'del-{{ $record->id }}')"
                                    class="grid size-8 place-items-center rounded-lg text-slate-500 transition hover:bg-danger-50 hover:text-danger-600" title="Hapus">
                                    <x-admin.icon name="trash" :size="16" />
                                </button>
                            </div>
                            <x-admin.confirm-delete
                                name="del-{{ $record->id }}"
                                :action="route('admin.resources.destroy', [$resource['slug'], $record])"
                                title="Hapus Data"
                                message="Data ini akan dihapus permanen. Aksi ini tidak bisa dibatalkan."
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($indikatorLabels) + 6 }}">
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
        </table>
    </div>

    <div class="border-t border-slate-200 px-6 py-4">
        {{ $records->links() }}
    </div>
</x-admin.card>
</div>
@endsection
