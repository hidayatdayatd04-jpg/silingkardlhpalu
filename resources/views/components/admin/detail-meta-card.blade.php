@props(['label', 'icon' => null, 'variant' => 'default'])

@php
    $variantClasses = [
        'default' => 'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/65',
        'primary' => 'border-brand-200 bg-brand-50 dark:border-brand-900 dark:bg-brand-950/35',
        'success' => 'border-success-200 bg-success-50 dark:border-success-900/70 dark:bg-success-950/30',
        'warning' => 'border-warning-200 bg-warning-50 dark:border-warning-900/70 dark:bg-warning-950/30',
        'danger' => 'border-danger-200 bg-danger-50 dark:border-danger-900/70 dark:bg-danger-950/30',
        'info' => 'border-info-200 bg-info-50 dark:border-info-900/70 dark:bg-info-950/30',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border p-4 '.($variantClasses[$variant] ?? $variantClasses['default'])]) }}>
    <div class="flex items-center gap-2">@if($icon)<x-admin.icon :name="$icon" :size="16" class="text-slate-500 dark:text-slate-400" aria-hidden="true" />@endif<p class="text-[11px] font-bold uppercase tracking-[0.09em] text-slate-500 dark:text-slate-400">{{ $label }}</p></div>
    <div class="mt-2 text-sm font-semibold leading-6 text-slate-800 dark:text-slate-100">{{ $slot }}</div>
</div>
