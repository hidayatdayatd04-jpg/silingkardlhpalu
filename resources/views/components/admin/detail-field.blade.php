@props(['label', 'value' => null, 'icon' => null, 'span' => 1])

@php
    $spanClass = match($span) {
        2 => 'md:col-span-2',
        3 => 'md:col-span-3',
        'full' => 'md:col-span-full',
        default => ''
    };
@endphp

<div {{ $attributes->merge(['class' => "space-y-2 $spanClass"]) }}>
    <div class="flex items-center gap-2">
        @if($icon)
            <x-admin.icon :name="$icon" :size="16" class="text-slate-400" />
        @endif
        <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-slate-500">{{ $label }}</p>
    </div>
    
    @if($slot->isNotEmpty())
        <div class="text-sm font-semibold leading-relaxed text-slate-800">
            {{ $slot }}
        </div>
    @else
        <p class="text-sm font-semibold leading-relaxed text-slate-800">
            {{ $value ?? '-' }}
        </p>
    @endif
</div>
