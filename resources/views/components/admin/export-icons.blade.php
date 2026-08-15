@props([
    'resource' => null,      // slug resource (mode generik)
    'selectedCount' => 0,    // jumlah baris terpilih (mode generik)
    'filterKeys' => [],      // kunci filter untuk deteksi scope (mode generik)
    'excelUrl' => null,      // mode eksplisit
    'csvUrl' => null,        // mode eksplisit
    'scopeLabel' => null,    // label cakupan (mode eksplisit)
    'showScope' => true,
])

@php
    $explicit = filled($excelUrl) || filled($csvUrl);
@endphp

<div class="flex items-center gap-2">
    @if($explicit)
        @if($excelUrl)
            <a href="{{ $excelUrl }}" title="Export Excel (.xlsx)" class="group grid size-9 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-600 hover:shadow-sm">
                <img src="{{ asset('assets/images/excel.png') }}" alt="Excel" class="h-5 w-5 object-contain">
            </a>
        @endif
        @if($csvUrl)
            <a href="{{ $csvUrl }}" title="Export CSV (.csv)" class="group grid size-9 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-sky-300 hover:bg-sky-50 hover:text-sky-600 hover:shadow-sm">
                <img src="{{ asset('assets/images/sheets.png') }}" alt="CSV" class="h-5 w-5 object-contain">
            </a>
        @endif
        @if($showScope && $scopeLabel)
            <span class="hidden items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-500 sm:inline-flex" title="Cakupan data yang diekspor">
                <x-admin.icon name="filter" :size="12" class="text-emerald-500" />
                Export: <span class="text-emerald-700">{{ $scopeLabel }}</span>
            </span>
        @endif
    @else
        <a :href="exportHref('xlsx')" title="Export Excel (.xlsx)" class="group grid size-9 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-600 hover:shadow-sm">
            <img src="{{ asset('assets/images/excel.png') }}" alt="Excel" class="h-5 w-5 object-contain">
        </a>
        <a :href="exportHref('csv')" title="Export CSV (.csv)" class="group grid size-9 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-sky-300 hover:bg-sky-50 hover:text-sky-600 hover:shadow-sm">
            <img src="{{ asset('assets/images/sheets.png') }}" alt="CSV" class="h-5 w-5 object-contain">
        </a>
        @if($showScope)
            <span class="hidden items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-500 sm:inline-flex" title="Cakupan data yang diekspor">
                <x-admin.icon name="filter" :size="12" class="text-emerald-500" />
                Export: <span x-text="exportScopeLabel()" class="text-emerald-700"></span>
            </span>
        @endif
    @endif
</div>
