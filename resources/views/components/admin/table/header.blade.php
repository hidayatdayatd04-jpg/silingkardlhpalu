@props(['sortable' => false, 'column' => null, 'direction' => null])

@php
    $isSorted = $sortable && $column && request('sort') === $column;
    $nextDirection = $isSorted && $direction === 'asc' ? 'desc' : 'asc';
    $sortUrl = $sortable && $column ? request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDirection]) : null;
@endphp

<th {{ $attributes->merge(['class' => 'sticky top-0 z-10 bg-slate-50/95 px-5 py-3.5 text-xs font-extrabold uppercase tracking-[0.12em] text-slate-500 backdrop-blur-sm first:rounded-tl-xl last:rounded-tr-xl']) }}>
    @if($sortable && $column)
        <a
            href="{{ $sortUrl }}"
            class="group inline-flex items-center gap-2 transition hover:text-brand-700"
        >
            <span>{{ $slot }}</span>
            <span class="relative flex flex-col">
                <!-- Arrow Up -->
                <x-admin.icon
                    name="chevron-up"
                    :size="14"
                    class="transition {{ $isSorted && $direction === 'asc' ? 'text-brand-600' : 'text-slate-300 group-hover:text-slate-400' }}"
                />
                <!-- Arrow Down -->
                <x-admin.icon
                    name="chevron-down"
                    :size="14"
                    class="-mt-2 transition {{ $isSorted && $direction === 'desc' ? 'text-brand-600' : 'text-slate-300 group-hover:text-slate-400' }}"
                />
            </span>
        </a>
    @else
        {{ $slot }}
    @endif
</th>
