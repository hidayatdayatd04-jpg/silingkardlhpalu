@props([
    'name' => 'alert',
    'size' => 20,
    'stroke' => 2,
    'filled' => null,
])

@php
    // Adapter kompatibilitas: seluruh ikon admin non-merek dirender oleh
    // katalog duotone x-icons.ui. Nama mentah sengaja diteruskan agar katalog
    // dapat menandai nama yang belum terdaftar sebagai fallback alert.
    $rawName = is_string($name) ? $name : 'alert';
    $normalizedName = \Illuminate\Support\Str::of($rawName)
        ->trim()
        ->lower()
        ->replace(['_', ' '], '-')
        ->replaceMatches('/[^a-z0-9-]/', '')
        ->toString();

    // Logo WhatsApp tetap memakai SVG merek penuh warna seperti perilaku lama.
    $filled = $filled ?? ($normalizedName === 'whatsapp');
    $sizeValue = is_numeric($size) ? max(1, (float) $size) : 20;
    $sizeCss = rtrim(rtrim(number_format($sizeValue, 3, '.', ''), '0'), '.');
    $callerStyle = $attributes->get('style');
    $brandStyle = implode('; ', array_filter([
        "width: {$sizeCss}px",
        "height: {$sizeCss}px",
        $callerStyle,
    ], static fn ($value) => filled($value)));
    $classes = trim('inline-block shrink-0 ' . (string) $attributes->get('class', ''));
    $forwardedAttributes = $attributes->except(['class', 'style', 'width', 'height']);
@endphp

@if($normalizedName === 'whatsapp')
    @include('components.icons.social.whatsapp', [
        'attributes' => $forwardedAttributes->merge(['class' => $classes, 'style' => $brandStyle]),
    ])
@else
    @include('components.icons.ui', [
        'name' => $rawName,
        'size' => $sizeValue,
        'stroke' => $stroke,
        'filled' => $filled,
        'attributes' => $forwardedAttributes->merge(['class' => $classes, 'style' => $callerStyle]),
    ])
@endif
