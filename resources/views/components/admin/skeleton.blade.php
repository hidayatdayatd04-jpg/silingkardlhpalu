@props(['type' => 'line', 'width' => 'full', 'height' => 'base'])

@php
    $typeClasses = [
        'line' => 'rounded',
        'circle' => 'rounded-full',
        'rect' => 'rounded-lg',
    ];

    $widthClasses = [
        'full' => 'w-full',
        '3/4' => 'w-3/4',
        '1/2' => 'w-1/2',
        '1/3' => 'w-1/3',
        '1/4' => 'w-1/4',
    ];

    $heightClasses = [
        'sm' => 'h-3',
        'base' => 'h-4',
        'lg' => 'h-5',
        'xl' => 'h-6',
        'button' => 'h-10',
        'card' => 'h-32',
    ];

    $classes = implode(' ', [
        $typeClasses[$type] ?? $typeClasses['line'],
        $widthClasses[$width] ?? $widthClasses['full'],
        $heightClasses[$height] ?? $heightClasses['base'],
        'animate-pulse bg-gradient-to-r from-emerald-200/60 via-teal-200/60 to-cyan-200/60'
    ]);
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}></div>
