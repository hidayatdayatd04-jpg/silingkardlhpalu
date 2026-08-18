@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconRight' => null,
    'href' => null,
    'type' => 'button',
    'loading' => false,
    'loadingText' => null,
])

@php
    $isLoading = filter_var($loading, FILTER_VALIDATE_BOOL);
    $iconOnly = $slot->isEmpty() && ($icon || $iconRight);
    $iconSize = $size === 'sm' ? 16 : ($size === 'lg' ? 20 : 18);
    $labels = [
        'eye' => 'Lihat detail', 'edit' => 'Edit', 'trash' => 'Hapus', 'x' => 'Tutup',
        'plus' => 'Tambah', 'download' => 'Unduh', 'upload' => 'Unggah', 'filter' => 'Filter',
        'search' => 'Cari', 'chevron-left' => 'Kembali', 'chevron-right' => 'Lanjut',
    ];
    $defaultLabel = $labels[$icon ?? $iconRight] ?? Str::headline($icon ?? $iconRight ?? 'Aksi');

    $baseClasses = 'relative inline-flex min-h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl font-semibold outline-none transition-[background-color,border-color,color,box-shadow,transform] duration-150 ease-out focus-visible:ring-2 focus-visible:ring-brand-600/30 focus-visible:ring-offset-2 focus-visible:ring-offset-white active:translate-y-px disabled:pointer-events-none disabled:opacity-55 dark:focus-visible:ring-offset-slate-950';
    $variantClasses = [
        'primary' => 'bg-brand-700 text-white shadow-[0_7px_16px_-10px_rgba(21,128,61,0.75)] hover:bg-brand-800',
        'secondary' => 'border border-slate-200 bg-white text-slate-700 shadow-sm hover:border-brand-300 hover:bg-brand-50 hover:text-brand-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-brand-700 dark:hover:bg-brand-950/45 dark:hover:text-brand-200',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white',
        'danger' => 'bg-danger-600 text-white shadow-[0_7px_16px_-10px_rgba(225,29,72,0.75)] hover:bg-danger-700',
        'subtle' => 'bg-brand-50 text-brand-800 hover:bg-brand-100 dark:bg-brand-950/50 dark:text-brand-200 dark:hover:bg-brand-950',
        'dark' => 'bg-slate-900 text-white shadow-sm hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white',
        'soft' => 'border border-slate-200 bg-white text-slate-700 hover:border-brand-300 hover:bg-brand-50 hover:text-brand-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-brand-700 dark:hover:bg-brand-950/45',
    ];
    $sizeClasses = [
        'sm' => 'min-h-8 px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'min-h-12 px-5 py-3 text-base',
    ];
    $classes = $baseClasses.' '.($variantClasses[$variant] ?? $variantClasses['primary']).' '.($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

@if($href)
    <a
        href="{{ $href }}"
        @if($isLoading) aria-busy="true" aria-disabled="true" @endif
        @if($iconOnly && ! $attributes->has('aria-label')) aria-label="{{ $defaultLabel }}" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($isLoading)
            <x-admin.icon name="loader" :size="$iconSize" class="shrink-0 animate-spin" aria-hidden="true" />
            <span>{{ $loadingText ?? $slot }}</span>
        @else
            @if($icon)<x-admin.icon :name="$icon" :size="$iconSize" aria-hidden="true" />@endif
            @if($iconOnly)<span class="sr-only">{{ $defaultLabel }}</span>@else{{ $slot }}@endif
            @if($iconRight)<x-admin.icon :name="$iconRight" :size="$iconSize" aria-hidden="true" />@endif
        @endif
    </a>
@else
    <button
        type="{{ $type }}"
        @if($isLoading) disabled aria-busy="true" @endif
        @if($iconOnly && ! $attributes->has('aria-label')) aria-label="{{ $defaultLabel }}" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($isLoading)
            <x-admin.icon name="loader" :size="$iconSize" class="shrink-0 animate-spin" aria-hidden="true" />
            <span>{{ $loadingText ?? $slot }}</span>
        @else
            @if($icon)<x-admin.icon :name="$icon" :size="$iconSize" aria-hidden="true" />@endif
            @if($iconOnly)<span class="sr-only">{{ $defaultLabel }}</span>@else{{ $slot }}@endif
            @if($iconRight)<x-admin.icon :name="$iconRight" :size="$iconSize" aria-hidden="true" />@endif
        @endif
    </button>
@endif
