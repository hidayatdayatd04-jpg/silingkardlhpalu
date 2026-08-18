@props([
    'resource' => null,
    'selectedCount' => 0,
    'filterKeys' => [],
    'excelUrl' => null,
    'csvUrl' => null,
    'scopeLabel' => null,
    'showScope' => true,
])

@php
    $explicit = filled($excelUrl) || filled($csvUrl);
    $iconLink = 'group grid size-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 outline-none transition-[background-color,border-color,color,box-shadow] duration-150 focus-visible:ring-2 focus-visible:ring-brand-600/25 focus-visible:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:focus-visible:ring-offset-slate-950';
@endphp

<div class="flex items-center gap-2">
    @if($explicit)
        @if($excelUrl)
            <a href="{{ $excelUrl }}" title="Export Excel (.xlsx)" aria-label="Export Excel (.xlsx)" class="{{ $iconLink }} hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:hover:border-brand-700 dark:hover:bg-brand-950/45 dark:hover:text-brand-300">
                <img src="{{ asset('assets/images/excel.png') }}" alt="" class="h-5 w-5 object-contain">
            </a>
        @endif
        @if($csvUrl)
            <a href="{{ $csvUrl }}" title="Export CSV (.csv)" aria-label="Export CSV (.csv)" class="{{ $iconLink }} hover:border-sky-300 hover:bg-sky-50 hover:text-sky-700 dark:hover:border-sky-700 dark:hover:bg-sky-950/35 dark:hover:text-sky-300">
                <img src="{{ asset('assets/images/sheets.png') }}" alt="" class="h-5 w-5 object-contain">
            </a>
        @endif
        @if($showScope && $scopeLabel)
            <span class="hidden items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 sm:inline-flex" title="Cakupan data yang diekspor">
                <x-admin.icon name="filter" :size="12" class="text-brand-600 dark:text-brand-300" aria-hidden="true" />
                Export: <span class="font-semibold text-brand-800 dark:text-brand-200">{{ $scopeLabel }}</span>
            </span>
        @endif
    @else
        <a :href="exportHref('xlsx')" title="Export Excel (.xlsx)" aria-label="Export Excel (.xlsx)" class="{{ $iconLink }} hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:hover:border-brand-700 dark:hover:bg-brand-950/45 dark:hover:text-brand-300">
            <img src="{{ asset('assets/images/excel.png') }}" alt="" class="h-5 w-5 object-contain">
        </a>
        <a :href="exportHref('csv')" title="Export CSV (.csv)" aria-label="Export CSV (.csv)" class="{{ $iconLink }} hover:border-sky-300 hover:bg-sky-50 hover:text-sky-700 dark:hover:border-sky-700 dark:hover:bg-sky-950/35 dark:hover:text-sky-300">
            <img src="{{ asset('assets/images/sheets.png') }}" alt="" class="h-5 w-5 object-contain">
        </a>
        @if($showScope)
            <span class="hidden items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 sm:inline-flex" title="Cakupan data yang diekspor">
                <x-admin.icon name="filter" :size="12" class="text-brand-600 dark:text-brand-300" aria-hidden="true" />
                Export: <span x-text="exportScopeLabel()" class="font-semibold text-brand-800 dark:text-brand-200"></span>
            </span>
        @endif
    @endif
</div>
