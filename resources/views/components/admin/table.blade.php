@props([
    'responsive' => true,
    'ariaLabel' => 'Tabel data',
])

@php
    $wrapperClasses = $responsive
        ? 'admin-table-scroll relative overflow-x-auto overscroll-x-contain rounded-2xl outline-none focus-visible:ring-2 focus-visible:ring-brand-600/30 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-950'
        : 'relative rounded-2xl';
    $resolvedAriaLabel = $attributes->get('aria-label', $ariaLabel);
@endphp

<div
    {{ $attributes->except('aria-label')->merge(['class' => $wrapperClasses]) }}
    @if($responsive) role="region" tabindex="0" aria-label="{{ $resolvedAriaLabel }}. Geser horizontal untuk melihat kolom lainnya." @endif
>
    <table class="min-w-full border-separate border-spacing-0 text-left text-sm text-slate-700 dark:text-slate-200">
        {{ $slot }}
    </table>
</div>
