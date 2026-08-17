@props(['label', 'icon' => null, 'variant' => 'default'])

@php
    $variantClasses = [
        'default' => 'border-slate-200 bg-slate-50/80',
        'primary' => 'border-emerald-200 bg-emerald-50/80',
        'success' => 'border-green-200 bg-green-50/80',
        'warning' => 'border-amber-200 bg-amber-50/80',
        'danger' => 'border-rose-200 bg-rose-50/80',
        'info' => 'border-blue-200 bg-blue-50/80',
    ];
    $classes = $variantClasses[$variant] ?? $variantClasses['default'];
@endphp

<div {{ $attributes->merge(['class' => "rounded-xl border-2 p-6 shadow-md transition hover:shadow-lg {$classes}"]) }}>
    <div class="flex items-center gap-3">
        @if($icon)
            <x-admin.icon :name="$icon" :size="18" class="text-slate-400" />
        @endif
        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">{{ $label }}</p>
    </div>
    <div class="mt-4 text-sm font-bold leading-relaxed text-slate-800">
        {{ $slot }}
    </div>
</div>
