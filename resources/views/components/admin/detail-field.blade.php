@props(['label', 'value' => null, 'icon' => null, 'span' => 1])

@php
    $spanClass = match($span) {
        2 => 'md:col-span-2',
        3 => 'md:col-span-3',
        'full' => 'md:col-span-full',
        default => ''
    };
@endphp

<div {{ $attributes->merge(['class' => "detail-field $spanClass"]) }}>
    <div class="flex items-center gap-2 mb-2">
        @if($icon)
            <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-400 dark:bg-white/[.06] dark:text-slate-500">
                <x-admin.icon :name="$icon" :size="14" />
            </span>
        @endif
        <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400 dark:text-slate-500">{{ $label }}</p>
    </div>

    @if($slot->isNotEmpty())
        <div class="pl-9 text-sm font-semibold leading-relaxed text-slate-800 dark:text-slate-200">
            {{ $slot }}
        </div>
    @else
        <p class="pl-9 text-sm font-semibold leading-relaxed text-slate-800 dark:text-slate-200">
            {{ $value ?? '-' }}
        </p>
    @endif
</div>
