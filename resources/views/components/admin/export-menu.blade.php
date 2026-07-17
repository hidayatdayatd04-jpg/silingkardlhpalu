@props([
    'resource' => null,
    'filteredUrl' => null,
    'allUrl' => null,
    'selectedCount' => 0,
    'bulkExportUrl' => null,
])

@php
    $filteredUrl = $filteredUrl ?? ($resource ? route('admin.resources.export', array_merge([$resource['slug']], request()->only(['q', 'sort', 'direction', 'status', 'date_from', 'date_to']))) : '#');
    $allUrl = $allUrl ?? ($resource ? route('admin.resources.export-all', $resource['slug']) : '#');
    $hasActiveFilters = request()->hasAny(['q', 'status', 'date_from', 'date_to', 'sort', 'direction']);
@endphp

<div
    x-data="{ open: false }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="relative"
>
    <x-admin.button
        variant="soft"
        icon="download"
        type="button"
        x-on:click="open = !open"
        class="gap-2"
    >
        Export
        <x-admin.icon name="chevron-down" :size="16" class="transition" x-bind:class="{ 'rotate-180': open }" />
    </x-admin.button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute right-0 z-30 mt-2 w-64 origin-top-right overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-900/10"
        style="display: none;"
    >
        <a
            href="{{ $filteredUrl }}"
            class="group flex items-start gap-3 rounded-lg p-3 transition hover:bg-emerald-50"
        >
            <div class="grid size-9 shrink-0 place-items-center rounded-lg bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-100">
                <x-admin.icon name="filter" :size="18" />
            </div>
            <div class="min-w-0">
                <p class="text-sm font-bold text-slate-800">Export Hasil Filter</p>
                <p class="mt-0.5 text-xs text-slate-500">
                    @if($hasActiveFilters)
                        Hanya data yang cocok filter saat ini
                    @else
                        Semua data di halaman ini (tanpa filter aktif)
                    @endif
                </p>
            </div>
        </a>

        <a
            href="{{ $allUrl }}"
            class="group flex items-start gap-3 rounded-lg p-3 transition hover:bg-sky-50"
        >
            <div class="grid size-9 shrink-0 place-items-center rounded-lg bg-sky-50 text-sky-600 transition group-hover:bg-sky-100">
                <x-admin.icon name="table" :size="18" />
            </div>
            <div class="min-w-0">
                <p class="text-sm font-bold text-slate-800">Export Semua Data</p>
                <p class="mt-0.5 text-xs text-slate-500">Seluruh {{ $resource['label'] ?? 'data' }} tanpa filter</p>
            </div>
        </a>

        @if($selectedCount > 0 && $bulkExportUrl)
            <div class="my-1.5 border-t border-slate-100"></div>
            <button
                type="button"
                @click="$dispatch('bulk-export')"
                class="group flex w-full items-start gap-3 rounded-lg p-3 transition hover:bg-amber-50"
            >
                <div class="grid size-9 shrink-0 place-items-center rounded-lg bg-amber-50 text-amber-600 transition group-hover:bg-amber-100">
                    <x-admin.icon name="download" :size="18" />
                </div>
                <div class="min-w-0 text-left">
                    <p class="text-sm font-bold text-slate-800">Export Terpilih ({{ $selectedCount }})</p>
                    <p class="mt-0.5 text-xs text-slate-500">Hanya item yang dicentang</p>
                </div>
            </button>
        @endif

        <div class="mt-1.5 border-t border-slate-100 px-3 py-2">
            <p class="text-xs text-slate-400">Format: Excel (.xlsx)</p>
        </div>
    </div>
</div>
