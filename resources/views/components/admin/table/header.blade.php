@props(['sortable' => false, 'column' => null, 'direction' => null])

@php
    $isSorted = $sortable && $column && request('sort') === $column;
    $nextDirection = $isSorted && $direction === 'asc' ? 'desc' : 'asc';
    $sortUrl = $sortable && $column ? request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDirection]) : null;
    $ariaSort = $isSorted ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none';
@endphp

<th
    scope="col"
    @if($sortable && $column) aria-sort="{{ $ariaSort }}" @endif
    {{ $attributes->merge(['class' => 'sticky top-0 z-10 border-b border-slate-200 bg-slate-50/95 px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/95 dark:text-slate-400 sm:px-5']) }}
>
    @if($sortable && $column)
        <a
            href="{{ $sortUrl }}"
            aria-label="Urutkan berdasarkan {{ trim(strip_tags($slot)) }} {{ $nextDirection === 'asc' ? 'menaik' : 'menurun' }}"
            class="group inline-flex min-h-6 items-center gap-1.5 rounded outline-none transition-colors duration-150 hover:text-brand-700 focus-visible:text-brand-700 dark:hover:text-brand-300 dark:focus-visible:text-brand-300"
        >
            <span>{{ $slot }}</span>
            <span class="relative flex h-4 w-3 flex-col justify-center" aria-hidden="true">
                <x-admin.icon name="chevron-up" :size="13" class="-mb-1.5 transition-colors duration-150 {{ $isSorted && $direction === 'asc' ? 'text-brand-700 dark:text-brand-300' : 'text-slate-300 group-hover:text-slate-500 dark:text-slate-600 dark:group-hover:text-slate-400' }}" />
                <x-admin.icon name="chevron-down" :size="13" class="-mt-1.5 transition-colors duration-150 {{ $isSorted && $direction === 'desc' ? 'text-brand-700 dark:text-brand-300' : 'text-slate-300 group-hover:text-slate-500 dark:text-slate-600 dark:group-hover:text-slate-400' }}" />
            </span>
        </a>
    @else
        {{ $slot }}
    @endif
</th>
