@props([
    'variant' => 'primary',   // primary | secondary | ghost | danger | subtle | dark | soft
    'size' => 'md',           // sm | md | lg
    'icon' => null,
    'iconRight' => null,
    'href' => null,
    'type' => 'button',
    'loading' => false,       // spinner + disable saat literal true
    'loadingText' => null,
])

@php
    $baseClasses = 'relative inline-flex items-center justify-center gap-2 font-bold transition duration-150 focus:outline-none focus:ring-4 disabled:pointer-events-none disabled:opacity-60';

    $variantClasses = [
        'primary'   => 'bg-brand-800 text-white shadow-[var(--shadow-brand-glow)] hover:bg-brand-900 focus:ring-brand-100',
        'secondary' => 'border border-slate-200 bg-white text-ink-700 shadow-[0_1px_2px_rgba(15,23,42,0.05)] hover:border-brand-300 hover:bg-brand-50 hover:text-brand-800 focus:ring-brand-100',
        'ghost'     => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus:ring-slate-100',
        'danger'    => 'bg-danger-600 text-white shadow-[0_8px_20px_-8px_rgba(225,29,72,0.6)] hover:bg-danger-700 focus:ring-danger-100',
        'subtle'    => 'bg-brand-50 text-brand-700 hover:bg-brand-100 focus:ring-brand-100',
        // legacy — backward compat
        'dark'      => 'bg-slate-950 text-white shadow-lg shadow-slate-950/15 hover:bg-emerald-700 focus:ring-emerald-100',
        'soft'      => 'border border-slate-200 bg-white text-slate-700 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 focus:ring-emerald-100',
    ];

    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-xs rounded-md',
        'md' => 'px-4 py-2.5 text-sm rounded-lg',
        'lg' => 'px-6 py-3 text-base rounded-lg',
    ];

    $iconSize = $size === 'sm' ? 16 : ($size === 'lg' ? 20 : 18);
    $variantCls = $variantClasses[$variant] ?? $variantClasses['primary'];
    $classes = $baseClasses . ' ' . $variantCls . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);

    $isLoading = filter_var($loading, FILTER_VALIDATE_BOOL);
    $spinner = '<svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)<x-admin.icon :name="$icon" :size="$iconSize" />@endif
        {{ $slot }}
        @if($iconRight)<x-admin.icon :name="$iconRight" :size="$iconSize" />@endif
    </a>
@else
    <button type="{{ $type }}" @if($isLoading) disabled @endif {{ $attributes->merge(['class' => $classes]) }}>
        @if($isLoading)
            {!! $spinner !!}
            <span>{{ $loadingText ?? $slot }}</span>
        @else
            @if($icon)<x-admin.icon :name="$icon" :size="$iconSize" />@endif
            {{ $slot }}
            @if($iconRight)<x-admin.icon :name="$iconRight" :size="$iconSize" />@endif
        @endif
    </button>
@endif
