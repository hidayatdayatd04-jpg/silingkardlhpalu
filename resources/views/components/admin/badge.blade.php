@props([
    'variant' => 'default',
    'icon' => null,
    'dot' => false,
])

@php
    $variantClasses = [
        'default' => 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200',
        'primary' => 'border-brand-200 bg-brand-50 text-brand-800 dark:border-brand-900 dark:bg-brand-950/45 dark:text-brand-200',
        'success' => 'border-success-200 bg-success-50 text-success-700 dark:border-success-900/70 dark:bg-success-950/35 dark:text-success-300',
        'warning' => 'border-warning-200 bg-warning-50 text-warning-800 dark:border-warning-900/70 dark:bg-warning-950/35 dark:text-warning-300',
        'danger' => 'border-danger-200 bg-danger-50 text-danger-700 dark:border-danger-900/70 dark:bg-danger-950/35 dark:text-danger-300',
        'info' => 'border-info-200 bg-info-50 text-info-700 dark:border-info-900/70 dark:bg-info-950/35 dark:text-info-300',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex max-w-full items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold leading-4 '.($variantClasses[$variant] ?? $variantClasses['default'])]) }}>
    @if($dot)<span class="size-1.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span>@endif
    @if($icon)<x-admin.icon :name="$icon" :size="13" :stroke="2.25" aria-hidden="true" />@endif
    <span class="truncate">{{ $slot }}</span>
</span>
