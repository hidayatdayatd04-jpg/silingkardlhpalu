@props([
    'label' => 'Filter',
    'active' => false,
    'count' => 0
])

<div 
    x-data="{ open: false }"
    x-on:click.away="open = false"
    class="relative"
>
    <button
        x-on:click="open = !open"
        type="button"
        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold transition {{ $active ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50' }}"
    >
        <x-admin.icon name="filter" :size="16" />
        <span>{{ $label }}</span>
        @if($count > 0)
            <span class="flex size-5 items-center justify-center rounded-full bg-emerald-600 text-[10px] font-bold text-white">
                {{ $count }}
            </span>
        @endif
        <x-admin.icon name="chevron-down" :size="16" class="transition" ::class="{ 'rotate-180': open }" />
    </button>

    <div
        x-show="open"
        x-transition
        class="absolute left-0 top-full z-10 mt-2 w-72 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xl"
        style="display: none;"
    >
        {{ $slot }}
    </div>
</div>
