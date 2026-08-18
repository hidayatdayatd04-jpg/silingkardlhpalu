@props(['label', 'value' => null, 'icon' => null, 'span' => 1])

@php
    $spanClass = match($span) {
        2 => 'md:col-span-2',
        3 => 'md:col-span-3',
        'full' => 'md:col-span-full',
        default => '',
    };
@endphp

<div {{ $attributes->merge(['class' => 'min-w-0 '.$spanClass]) }}>
    <div class="flex items-center gap-2">
        @if($icon)<span class="grid size-7 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"><x-admin.icon :name="$icon" :size="14" aria-hidden="true" /></span>@endif
        <p class="text-[11px] font-bold uppercase tracking-[0.09em] text-slate-500 dark:text-slate-400">{{ $label }}</p>
    </div>
    <div class="mt-2 {{ $icon ? 'pl-9' : '' }} text-sm font-medium leading-6 text-slate-800 dark:text-slate-100">
        @if($slot->isNotEmpty()){{ $slot }}@else{{ $value ?? '-' }}@endif
    </div>
</div>
