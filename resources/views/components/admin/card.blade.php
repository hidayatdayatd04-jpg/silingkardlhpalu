@props(['padding' => true, 'hover' => false])

@php
    $classes = 'rounded-2xl border border-slate-200/90 bg-white shadow-[0_8px_24px_-18px_rgba(15,23,42,0.36)] dark:border-slate-800 dark:bg-slate-900';
    if ($padding) {
        $classes .= ' p-4 sm:p-5';
    }
    if ($hover) {
        $classes .= ' transition-[border-color,box-shadow,transform] duration-150 ease-out hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-[0_14px_30px_-18px_rgba(15,23,42,0.38)] dark:hover:border-brand-900';
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
