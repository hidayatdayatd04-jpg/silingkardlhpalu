@props([
    'name' => '',
    'size' => 'md',   // sm | md | lg
    'src' => null,    // opsional url gambar
])

@php
    $name = trim((string) $name);
    // Inisial: maks 2 huruf dari kata pertama & terakhir
    $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if (count($parts) >= 2) {
        $initials = mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
    } elseif (count($parts) === 1) {
        $initials = mb_strtoupper(mb_substr($parts[0], 0, 2));
    } else {
        $initials = '?';
    }

    // Hash warna deterministik dari nama → salah satu palet brand
    $palette = [
        'bg-brand-100 text-brand-700',
        'bg-info-100 text-info-700',
        'bg-warning-100 text-warning-700',
        'bg-clay-100 text-clay-700',
        'bg-forest-100 text-forest-700',
        'bg-danger-100 text-danger-700',
    ];
    $hash = $name !== '' ? crc32($name) % count($palette) : 0;
    $color = $palette[$hash];

    $sizeCls = [
        'sm' => 'size-8 text-xs',
        'md' => 'size-10 text-sm',
        'lg' => 'size-14 text-lg',
    ][$size] ?? 'size-10 text-sm';
@endphp

<span {{ $attributes->merge(['class' => "relative inline-grid shrink-0 place-items-center overflow-hidden rounded-full font-bold $sizeCls " . ($src ? '' : $color)]) }}>
    @if($src)
        <img src="{{ $src }}" alt="{{ $name }}" class="size-full object-cover">
    @else
        {{ $initials }}
    @endif
</span>
