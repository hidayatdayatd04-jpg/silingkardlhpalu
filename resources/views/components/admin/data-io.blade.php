@props([
    'resource' => null,
    'selectedCount' => 0,
])

@php
    $slug = $resource['slug'] ?? null;
    $baseParams = request()->only(['q', 'sort', 'direction', 'status', 'date_from', 'date_to']);
    $exportUrl = fn ($format) => $slug ? route('admin.resources.export', array_merge([$slug, 'format' => $format], $baseParams)) : '#';
    $exportAllUrl = fn ($format) => $slug ? route('admin.resources.export-all', [$slug, 'format' => $format]) : '#';
    $hasActiveFilters = request()->hasAny(['q', 'status', 'date_from', 'date_to']);
    $formats = [
        ['key' => 'xlsx', 'label' => 'Excel (.xlsx)', 'icon' => 'table',     'color' => 'emerald'],
        ['key' => 'csv',  'label' => 'CSV (.csv)',    'icon' => 'file-text', 'color' => 'sky'],
        ['key' => 'pdf',  'label' => 'PDF (.pdf)',    'icon' => 'file-text', 'color' => 'rose'],
    ];
@endphp

<div class="flex items-center gap-2">
    {{-- Export --}}
    <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative">
        <x-admin.button variant="soft" icon="download" type="button" x-on:click="open = !open" class="gap-2">
            Export
            <x-admin.icon name="chevron-down" :size="16" x-bind:class="{ 'rotate-180': open }" />
        </x-admin.button>

        <div x-show="open" x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            class="absolute right-0 z-30 mt-2 w-72 origin-top-right overflow-hidden rounded-xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10"
            style="display: none;">

            <p class="px-2 pb-1 pt-1 text-xs font-bold uppercase tracking-wide text-slate-400">
                {{ $hasActiveFilters ? 'Hasil filter saat ini' : 'Halaman ini' }}
            </p>
            @foreach($formats as $fmt)
                <a href="{{ $exportUrl($fmt['key']) }}" class="group flex items-center gap-3 rounded-lg p-2.5 transition hover:bg-{{ $fmt['color'] }}-50">
                    <div class="grid size-8 shrink-0 place-items-center rounded-lg bg-{{ $fmt['color'] }}-50 text-{{ $fmt['color'] }}-600">
                        <x-admin.icon :name="$fmt['icon']" :size="16" />
                    </div>
                    <span class="text-sm font-semibold text-slate-800">{{ $fmt['label'] }}</span>
                </a>
            @endforeach

            <div class="my-1.5 border-t border-slate-100"></div>
            <p class="px-2 pb-1 text-xs font-bold uppercase tracking-wide text-slate-400">Semua data</p>
            @foreach($formats as $fmt)
                <a href="{{ $exportAllUrl($fmt['key']) }}" class="group flex items-center gap-3 rounded-lg p-2.5 transition hover:bg-slate-50">
                    <div class="grid size-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-500">
                        <x-admin.icon :name="$fmt['icon']" :size="16" />
                    </div>
                    <span class="text-sm font-semibold text-slate-700">Semua — {{ $fmt['label'] }}</span>
                </a>
            @endforeach

            @if($selectedCount > 0)
                <div class="my-1.5 border-t border-slate-100"></div>
                <button type="button" @click="$dispatch('bulk-export'); open = false"
                    class="group flex w-full items-center gap-3 rounded-lg p-2.5 transition hover:bg-amber-50">
                    <div class="grid size-8 shrink-0 place-items-center rounded-lg bg-amber-50 text-amber-600">
                        <x-admin.icon name="download" :size="16" />
                    </div>
                    <span class="text-sm font-semibold text-slate-800">Export Terpilih ({{ $selectedCount }}) — xlsx</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Import --}}
    <div x-data="{ open: false }">
        <x-admin.button variant="secondary" icon="upload" type="button" x-on:click="open = true">Import</x-admin.button>

        <template x-teleport="body">
            <div x-show="open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="display:none;">
                <div class="absolute inset-0 bg-ink-950/50 backdrop-blur-sm" x-on:click="open = false"></div>
                <div class="relative w-full max-w-md overflow-hidden rounded-xl border border-white/80 bg-white shadow-[var(--shadow-modal)]">
                    <div class="flex items-center justify-between border-b border-slate-100 p-5">
                        <h3 class="text-h4 font-bold text-ink-900">Import {{ $resource['label'] ?? 'Data' }}</h3>
                        <button type="button" x-on:click="open = false" class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100"><x-admin.icon name="x" :size="18" /></button>
                    </div>
                    <form method="POST" action="{{ $slug ? route('admin.resources.import', $slug) : '#' }}" enctype="multipart/form-data" class="p-5">
                        @csrf
                        <p class="mb-3 text-sm text-slate-600">Unggah file <strong>.xlsx</strong> atau <strong>.csv</strong>. Pastikan header kolom sesuai template.</p>
                        <a href="{{ $slug ? route('admin.resources.import-template', $slug) : '#' }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-bold text-brand-600 hover:text-brand-700">
                            <x-admin.icon name="download" :size="14" /> Unduh Template
                        </a>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            class="mb-4 block w-full rounded-lg border border-slate-300 bg-white p-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-sm file:font-bold file:text-brand-700">
                        <div class="flex justify-end gap-2">
                            <x-admin.button variant="soft" type="button" x-on:click="open = false">Batal</x-admin.button>
                            <x-admin.button variant="primary" type="submit" icon="upload">Import</x-admin.button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</div>
