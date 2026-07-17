@props(['padding' => true, 'hover' => false])

@php
    $classes = 'rounded-lg border border-white/80 bg-white shadow-[0_18px_60px_rgba(15,23,42,0.08)]';
    if ($padding) {
        $classes .= ' p-6';
    }
    if ($hover) {
        $classes .= ' transition hover:shadow-[0_18px_80px_rgba(15,23,42,0.12)] hover:border-emerald-200';
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
