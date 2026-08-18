@props([
    'name' => 'document',
    'title' => null,
    'size' => null,
    'stroke' => null,
    'filled' => false,
])

@php
    $rawName = is_string($name) ? $name : 'document';
    $normalizedName = \Illuminate\Support\Str::of($rawName)
        ->trim()
        ->lower()
        ->replace(['_', ' '], '-')
        ->replaceMatches('/[^a-z0-9-]/', '')
        ->toString();

    $aliases = [
        'address' => 'map-pin',
        'archive-file' => 'archive',
        'arrow' => 'arrow-right',
        'briefcase' => 'building',
        'bin' => 'trash',
        'calendar-days' => 'calendar',
        'chevron' => 'chevron-right',
        'chevrons-down' => 'chevrons',
        'company' => 'building',
        'close' => 'close',
        'dots' => 'dots-vertical',
        'envelope' => 'mail',
        'file' => 'document',
        'file-text' => 'document',
        'info' => 'info-circle',
        'id' => 'id-card',
        'identity' => 'id-card',
        'leafy' => 'leaf',
        'loader-2' => 'loader',
        'loading' => 'loader',
        'location' => 'map-pin',
        'map' => 'map-pin',
        'message-circle' => 'message',
        'person' => 'user',
        'pencil' => 'edit',
        'save' => 'device-floppy',
        'send' => 'arrow-right',
        'success' => 'check',
        'warning' => 'alert',
        'x' => 'close',
    ];

    $name = $aliases[$normalizedName] ?? $normalizedName;
    $availableIcons = [
        'alamat', 'alert', 'alert-circle', 'alert-triangle', 'archive', 'arrow-left', 'arrow-right', 'at-sign', 'axe',
        'bell', 'berhasil', 'book-open', 'building', 'calendar', 'chart', 'chart-bar', 'check', 'check-circle',
        'chevron-down', 'chevron-left', 'chevron-right', 'chevron-up', 'chevrons', 'circle-check',
        'clipboard-check', 'clipboard-list', 'clock', 'close', 'command', 'copy', 'dashboard', 'database',
        'device-floppy', 'document', 'dots-vertical', 'download', 'edit', 'external-link', 'eye', 'eye-off',
        'factory', 'file-plus', 'filter', 'folder', 'folder-plus', 'forest', 'globe', 'grid', 'home', 'id-card', 'image',
        'info-circle', 'isi-formulir', 'jam-kerja', 'jam-respons', 'layers', 'leaf', 'link', 'list', 'loader', 'lock',
        'logout', 'mail', 'map-pin', 'megaphone', 'menu', 'message', 'message-plus', 'misi', 'moon', 'news', 'package',
        'palette', 'pantau-status', 'park', 'park-bench', 'pengendalian', 'phone', 'pilih-layanan', 'plus', 'presentation',
        'real-time', 'recycle', 'refresh', 'route', 'rth', 'rth-ha', 'sampah', 'sapa', 'search', 'seedling', 'send',
        'settings', 'shield', 'star', 'sun', 'table', 'tag', 'tata-penataan', 'terbuka', 'terintegrasi', 'titik-tps',
        'ton-sampah', 'tool', 'trash', 'trash-x', 'tree', 'trending-up', 'truck', 'upload', 'user', 'user-check',
        'user-plus', 'users', 'visi', 'whatsapp',
    ];

    $isKnownIcon = in_array($name, $availableIcons, true);

    // Jangan samarkan salah ketik atau metadata ikon yang belum didaftarkan sebagai
    // ikon dokumen. Fallback alert membuat masalah langsung tampak di antarmuka dan
    // tetap dapat ditelusuri melalui atribut data di HTML hasil render.
    if (! $isKnownIcon) {
        $name = 'alert';
    }

    $resolvedTitle = filled($title)
        ? $title
        : (! $isKnownIcon ? "Ikon tidak dikenal: {$rawName}" : null);

    $isFilled = $filled === true || $filled === 1 || $filled === '1' || $filled === 'true';
    $sizeValue = is_numeric($size) ? max(1, (float) $size) : null;
    $strokeValue = is_numeric($stroke) ? max(0, (float) $stroke) : null;
    $formatDimension = static fn (float $value): string => rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');

    $iconStyles = array_filter([
        $sizeValue !== null ? "width: {$formatDimension($sizeValue)}px" : null,
        $sizeValue !== null ? "height: {$formatDimension($sizeValue)}px" : null,
        $isFilled ? 'fill: currentColor' : null,
        $isFilled ? 'stroke: none' : ($strokeValue !== null ? "stroke-width: {$formatDimension($strokeValue)}" : null),
        $attributes->get('style'),
    ], static fn ($value) => filled($value));
    $inlineStyle = implode('; ', $iconStyles);

    $svgAttributes = $attributes
        ->except('style')
        ->merge([
            'class' => 'icon-duo',
            'viewBox' => '0 0 24 24',
            'fill' => 'none',
            'focusable' => 'false',
            'style' => $inlineStyle ?: null,
        ]);

    $socialAttributes = $attributes
        ->except('style')
        ->merge([
            'class' => 'inline-block shrink-0',
            'style' => $inlineStyle ?: null,
        ]);

    $isAccessible = filled($resolvedTitle) || $attributes->has('aria-label') || $attributes->has('aria-labelledby');
    $titleId = filled($resolvedTitle) ? 'ui-icon-' . \Illuminate\Support\Str::random(10) : null;
@endphp

@if($name === 'whatsapp')
    {{-- Ikon merek tetap menggunakan aset sosial berwarna, bukan SVG duotone generik. --}}
    <x-icons.social.whatsapp {{ $socialAttributes }} />
@else
<svg
    {{ $svgAttributes }}
    @if(! $isKnownIcon)
        data-icon-status="fallback"
        data-icon-name="{{ $rawName }}"
    @endif
    @if($isAccessible)
        role="img"
        @if($titleId) aria-labelledby="{{ $titleId }}" @endif
    @else
        aria-hidden="true"
    @endif
>
    @if($titleId)
        <title id="{{ $titleId }}">{{ $resolvedTitle }}</title>
    @endif

    @switch($name)
        @case('user')
            <circle class="icon-accent" cx="12" cy="8" r="4"/>
            <circle cx="12" cy="8" r="4"/>
            <path d="M4.5 20c.7-4 3.4-6 7.5-6s6.8 2 7.5 6"/>
            @break

        @case('building')
            <path class="icon-accent" d="M6 20V5.8A1.8 1.8 0 0 1 7.8 4h7.4A1.8 1.8 0 0 1 17 5.8V20Z"/>
            <path d="M4 20h16M6 20V5.8A1.8 1.8 0 0 1 7.8 4h7.4A1.8 1.8 0 0 1 17 5.8V20M9 8h.01M14 8h.01M9 11.5h.01M14 11.5h.01M10 20v-4h4v4"/>
            @break

        @case('id-card')
            <rect class="icon-accent" x="3.5" y="5" width="17" height="14" rx="2.5"/>
            <rect x="3.5" y="5" width="17" height="14" rx="2.5"/>
            <circle cx="8.4" cy="11" r="1.8"/>
            <path d="M5.8 16c.7-1.6 1.6-2.4 2.6-2.4s1.9.8 2.6 2.4M13.5 10h4M13.5 13h4M13.5 16h2.4"/>
            @break

        @case('phone')
            <path class="icon-accent" d="M7.1 3.6 4.5 4.8c-.8.4-1.2 1.2-1 2.1 1.3 6.4 6.2 11.3 12.6 12.6.9.2 1.7-.2 2.1-1l1.2-2.6-4-2.2-1.7 1.7a13.4 13.4 0 0 1-5-5l1.7-1.7-2.2-4.1Z"/>
            <path d="M7.1 3.6 4.5 4.8c-.8.4-1.2 1.2-1 2.1 1.3 6.4 6.2 11.3 12.6 12.6.9.2 1.7-.2 2.1-1l1.2-2.6-4-2.2-1.7 1.7a13.4 13.4 0 0 1-5-5l1.7-1.7-2.2-4.1Z"/>
            @break

        @case('mail')
            <rect class="icon-accent" x="3" y="5" width="18" height="14" rx="2.4"/>
            <rect x="3" y="5" width="18" height="14" rx="2.4"/>
            <path d="m4 7 8 6 8-6M4 17l5.5-5M20 17l-5.5-5"/>
            @break

        @case('map-pin')
            <path class="icon-accent" d="M12 21s6.5-5.4 6.5-11a6.5 6.5 0 1 0-13 0c0 5.6 6.5 11 6.5 11Z"/>
            <path d="M12 21s6.5-5.4 6.5-11a6.5 6.5 0 1 0-13 0c0 5.6 6.5 11 6.5 11Z"/>
            <circle cx="12" cy="10" r="2.3"/>
            @break

        @case('document')
            <path class="icon-accent" d="M6 3.5h7l5 5V20a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 20Z"/>
            <path d="M6 3.5h7l5 5V20a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 20ZM13 3.5v5h5M9 13h6M9 16.5h4"/>
            @break

        @case('folder')
            <path class="icon-accent" d="M3.5 7.5A2.5 2.5 0 0 1 6 5h4l2 2.4h6A2.5 2.5 0 0 1 20.5 10v7.5A2.5 2.5 0 0 1 18 20H6a2.5 2.5 0 0 1-2.5-2.5Z"/>
            <path d="M3.5 7.5A2.5 2.5 0 0 1 6 5h4l2 2.4h6A2.5 2.5 0 0 1 20.5 10v7.5A2.5 2.5 0 0 1 18 20H6a2.5 2.5 0 0 1-2.5-2.5ZM3.5 10h17"/>
            @break

        @case('leaf')
            <path class="icon-accent" d="M19.8 4.2C12.2 4.1 5.2 7.4 4.4 15.7c-.2 2.1 1.5 3.8 3.6 3.6 8.3-.8 11.6-7.8 11.8-15.1Z"/>
            <path d="M19.8 4.2C12.2 4.1 5.2 7.4 4.4 15.7c-.2 2.1 1.5 3.8 3.6 3.6 8.3-.8 11.6-7.8 11.8-15.1ZM5.5 18.5c3.7-4.1 6.8-6.3 11.7-9.3"/>
            @break

        @case('upload')
            <path class="icon-accent" d="M5 17.5a3.5 3.5 0 0 0 3.5 3.5h7a3.5 3.5 0 1 0-.8-6.9A5.2 5.2 0 0 0 5 12.5 2.5 2.5 0 0 0 5 17.5Z"/>
            <path d="M5 17.5a3.5 3.5 0 0 0 3.5 3.5h7a3.5 3.5 0 1 0-.8-6.9A5.2 5.2 0 0 0 5 12.5 2.5 2.5 0 0 0 5 17.5ZM12 15V4m0 0L8.5 7.5M12 4l3.5 3.5"/>
            @break

        @case('download')
            <path class="icon-accent" d="M5 18.5v1.2A1.8 1.8 0 0 0 6.8 21h10.4a1.8 1.8 0 0 0 1.8-1.8v-.7"/>
            <path d="M5 18.5v1.2A1.8 1.8 0 0 0 6.8 21h10.4a1.8 1.8 0 0 0 1.8-1.8v-.7M12 3v12m0 0 4-4m-4 4-4-4"/>
            @break

        @case('search')
            <circle class="icon-accent" cx="10.5" cy="10.5" r="5.8"/>
            <circle cx="10.5" cy="10.5" r="5.8"/>
            <path d="m15 15 4.5 4.5"/>
            @break

        @case('eye')
            <path class="icon-accent" d="M3.5 12s3.2-6.2 8.5-6.2 8.5 6.2 8.5 6.2-3.2 6.2-8.5 6.2S3.5 12 3.5 12Z"/>
            <path d="M3.5 12s3.2-6.2 8.5-6.2 8.5 6.2 8.5 6.2-3.2 6.2-8.5 6.2S3.5 12 3.5 12Z"/>
            <circle cx="12" cy="12" r="2.7"/>
            @break

        @case('calendar')
            <rect class="icon-accent" x="3.5" y="5.5" width="17" height="15" rx="2.2"/>
            <rect x="3.5" y="5.5" width="17" height="15" rx="2.2"/>
            <path d="M7.5 3.5v4M16.5 3.5v4M3.5 10h17M8 14h.01M12 14h.01M16 14h.01M8 17.5h.01M12 17.5h.01"/>
            @break

        @case('message')
            <path class="icon-accent" d="M5 5h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H10l-4.5 3V17A2 2 0 0 1 3 15V7a2 2 0 0 1 2-2Z"/>
            <path d="M5 5h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H10l-4.5 3V17A2 2 0 0 1 3 15V7a2 2 0 0 1 2-2ZM8 10h8M8 13h5"/>
            @break

        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16"/>
            @break

        @case('close')
            <path d="m6 6 12 12M18 6 6 18"/>
            @break

        @case('copy')
            <rect class="icon-accent" x="8" y="4" width="11.5" height="13.5" rx="2"/>
            <path d="M8 7H6.5A2.5 2.5 0 0 0 4 9.5v8A2.5 2.5 0 0 0 6.5 20H14a2.5 2.5 0 0 0 2.5-2.5V17.5M8 4h9.5A2 2 0 0 1 19.5 6v9.5a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2Z"/>
            @break

        @case('shield')
            <path class="icon-accent" d="M12 3.2c2.3 1.8 4.8 2.5 7.5 2.8v5.5c0 4.4-2.7 7.5-7.5 9.3-4.8-1.8-7.5-4.9-7.5-9.3V6c2.7-.3 5.2-1 7.5-2.8Z"/>
            <path d="M12 3.2c2.3 1.8 4.8 2.5 7.5 2.8v5.5c0 4.4-2.7 7.5-7.5 9.3-4.8-1.8-7.5-4.9-7.5-9.3V6c2.7-.3 5.2-1 7.5-2.8ZM9.1 12l1.9 1.9 4-4.1"/>
            @break

        @case('star')
            <path class="icon-accent" d="m12 3.2 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9L6.6 20l1-6.1-4.4-4.3 6.1-.9Z"/>
            <path d="m12 3.2 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9L6.6 20l1-6.1-4.4-4.3 6.1-.9Z"/>
            @break

        @case('chevron-left')
            <path d="m14.5 5-7 7 7 7"/>
            @break

        @case('chevron-right')
            <path d="m9.5 5 7 7-7 7"/>
            @break

        @case('chevron-up')
            <path d="m5 14.5 7-7 7 7"/>
            @break

        @case('chevron-down')
            <path d="m5 9.5 7 7 7-7"/>
            @break

        @case('chevrons')
            <path class="icon-accent" d="m5 8 7 7 7-7v4l-7 7-7-7Z"/>
            <path d="m5 5 7 7 7-7M5 12l7 7 7-7"/>
            @break

        @case('sun')
            <circle class="icon-accent" cx="12" cy="12" r="4"/>
            <circle cx="12" cy="12" r="4"/>
            <path d="M12 2.5v2M12 19.5v2M5.3 5.3l1.4 1.4M17.3 17.3l1.4 1.4M2.5 12h2M19.5 12h2M5.3 18.7l1.4-1.4M17.3 6.7l1.4-1.4"/>
            @break

        @case('moon')
            <path class="icon-accent" d="M20.2 15.3A8.5 8.5 0 0 1 8.7 3.8a8.5 8.5 0 1 0 11.5 11.5Z"/>
            <path d="M20.2 15.3A8.5 8.5 0 0 1 8.7 3.8a8.5 8.5 0 1 0 11.5 11.5Z"/>
            @break

        @case('arrow-right')
            <path class="icon-accent" d="M4 9h10V5l6 7-6 7v-4H4Z"/>
            <path d="M4 12h15M14 6l6 6-6 6"/>
            @break

        @case('arrow-left')
            <path class="icon-accent" d="M20 9H10V5l-6 7 6 7v-4h10Z"/>
            <path d="M20 12H5M10 6l-6 6 6 6"/>
            @break

        @case('refresh')
            <path class="icon-accent" d="M19 8.5V4.8l-2 2A8 8 0 1 0 20 14"/>
            <path d="M19 8.5V4.8l-2 2A8 8 0 1 0 20 14M19 4.8v3.7h-3.7"/>
            @break

        @case('plus')
            <circle class="icon-accent" cx="12" cy="12" r="8.5"/>
            <path d="M12 7.5v9M7.5 12h9"/>
            @break

        @case('tool')
            <path class="icon-accent" d="m14.6 6.1 3.4-3.4a4.4 4.4 0 0 1-5.3 5.6l-7.8 7.8a2.7 2.7 0 1 0 3.8 3.8l7.8-7.8A4.4 4.4 0 0 1 22 6.8l-3.4 3.4Z"/>
            <path d="m14.6 6.1 3.4-3.4a4.4 4.4 0 0 1-5.3 5.6l-7.8 7.8a2.7 2.7 0 1 0 3.8 3.8l7.8-7.8A4.4 4.4 0 0 1 22 6.8l-3.4 3.4Z"/>
            @break

        @case('trash')
            <path class="icon-accent" d="M6 7h12l-1 13H7L6 7Z"/>
            <path d="M4 7h16M9 7V4.5h6V7M6 7l1 13h10l1-13M10 11v5M14 11v5"/>
            @break

        @case('alert')
            <path class="icon-accent" d="M10.3 4.4 3.5 17a2 2 0 0 0 1.8 3h13.4a2 2 0 0 0 1.8-3L13.7 4.4a2 2 0 0 0-3.4 0Z"/>
            <path d="M10.3 4.4 3.5 17a2 2 0 0 0 1.8 3h13.4a2 2 0 0 0 1.8-3L13.7 4.4a2 2 0 0 0-3.4 0ZM12 9v4M12 16.5h.01"/>
            @break

        @case('check')
            <circle class="icon-accent" cx="12" cy="12" r="8.5"/>
            <circle cx="12" cy="12" r="8.5"/>
            <path d="m8.2 12.1 2.5 2.6 5.2-5.5"/>
            @break

        @case('dashboard')
            <rect class="icon-accent" x="4" y="4" width="6.5" height="7" rx="1.4"/>
            <rect x="4" y="4" width="6.5" height="7" rx="1.4"/>
            <rect x="13.5" y="4" width="6.5" height="4.5" rx="1.4"/>
            <rect x="4" y="14" width="6.5" height="6" rx="1.4"/>
            <rect x="13.5" y="11.5" width="6.5" height="8.5" rx="1.4"/>
            @break

        @case('home')
            <path class="icon-accent" d="m4 11.5 8-7 8 7V20H4Z"/>
            <path d="m3.5 11.5 8.5-7.5 8.5 7.5M5 10.2V20h14v-9.8M9.5 20v-5.5h5V20"/>
            @break

        @case('filter')
            <path class="icon-accent" d="M4 5h16l-6.2 7.2V19l-3.6 1.7v-8.5Z"/>
            <path d="M4 5h16l-6.2 7.2V19l-3.6 1.7v-8.5ZM4 5v2.2l6.2 5M20 5v2.2l-6.2 5"/>
            @break

        @case('dots-vertical')
            <circle class="icon-accent" cx="12" cy="5" r="1.6"/>
            <circle cx="12" cy="5" r="1.2"/>
            <circle cx="12" cy="12" r="1.2"/>
            <circle cx="12" cy="19" r="1.2"/>
            @break

        @case('device-floppy')
            <path class="icon-accent" d="M5 3.5h11l3 3V20a1.5 1.5 0 0 1-1.5 1.5h-12A1.5 1.5 0 0 1 4 20V5a1.5 1.5 0 0 1 1-1.5Z"/>
            <path d="M5 3.5h11l3 3V20a1.5 1.5 0 0 1-1.5 1.5h-12A1.5 1.5 0 0 1 4 20V5a1.5 1.5 0 0 1 1-1.5ZM7 3.5V9h8V4M8 20v-6h8v6M14.5 6.2h.01"/>
            @break

        @case('edit')
            <path class="icon-accent" d="m14.5 5 4.5 4.5-8.8 8.8-4.7.3.3-4.7Z"/>
            <path d="M5 20h14M6 17.5l.4-4.2L15.8 4a2.2 2.2 0 0 1 3.1 3.1l-9.3 9.3ZM13.8 6l4.2 4.2"/>
            @break

        @case('eye-off')
            <path class="icon-accent" d="M4 4.5 20 19.5 4 4.5Z"/>
            <path d="m4 4.5 16 15M10.6 6.1A10.7 10.7 0 0 1 20.5 12s-3.2 6.2-8.5 6.2a9.8 9.8 0 0 1-4.7-1.2M7.1 7.4C4.8 9 3.5 12 3.5 12s3.2 6.2 8.5 6.2c.8 0 1.5-.1 2.2-.3M9.7 9.8a3.2 3.2 0 0 0 4.5 4.5"/>
            @break

        @case('at-sign')
            <circle class="icon-accent" cx="12" cy="12" r="3.8"/>
            <path d="M16 8v5.3a2.7 2.7 0 0 0 5.4 0V12a9.4 9.4 0 1 0-3 6.9M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/>
            @break

        @case('check-circle')
        @case('circle-check')
            <circle class="icon-accent" cx="12" cy="12" r="8.5"/>
            <circle cx="12" cy="12" r="8.5"/>
            <path d="m8.2 12.1 2.5 2.6 5.2-5.5"/>
            @break

        @case('table')
            <rect class="icon-accent" x="3.5" y="4" width="17" height="16" rx="2"/>
            <rect x="3.5" y="4" width="17" height="16" rx="2"/>
            <path d="M3.5 10h17M9.2 4v16M15 4v16"/>
            @break

        @case('chart')
        @case('chart-bar')
            <rect class="icon-accent" x="4" y="12" width="4" height="8" rx="1"/>
            <path d="M4 20v-8h4v8ZM10 20V8h4v12ZM16 20V4h4v16Z"/>
            @break

        @case('trending-up')
            <path class="icon-accent" d="m4 17 5.5-5.5 3.8 3.8L20 8.5V13h-1.8V11.5l-5 5-3.8-3.8L5.3 17Z"/>
            <path d="m4 17 5.5-5.5 3.8 3.8L20 8.5M14.5 8.5H20V14"/>
            @break

        @case('clock')
            <circle class="icon-accent" cx="12" cy="12" r="8.5"/>
            <circle cx="12" cy="12" r="8.5"/>
            <path d="M12 7v5l3.5 2"/>
            @break

        @case('users')
            <circle class="icon-accent" cx="9" cy="8" r="3.4"/>
            <circle cx="9" cy="8" r="3.4"/>
            <path d="M3.5 20c.4-3.6 2.3-5.6 5.5-5.6s5.1 2 5.5 5.6M16 5.2a3.3 3.3 0 0 1 0 6.3M16.2 14.6c2.5.3 3.9 2.1 4.3 5.4"/>
            @break

        @case('bell')
            <path class="icon-accent" d="M6 16.5h12l-1.5-2.2V10a4.5 4.5 0 0 0-9 0v4.3Z"/>
            <path d="M6 16.5h12l-1.5-2.2V10a4.5 4.5 0 0 0-9 0v4.3ZM9.5 19a2.7 2.7 0 0 0 5 0M10 5.7a2.2 2.2 0 0 1 4 0"/>
            @break

        @case('alert-circle')
            <circle class="icon-accent" cx="12" cy="12" r="8.5"/>
            <circle cx="12" cy="12" r="8.5"/>
            <path d="M12 8v4.5M12 16h.01"/>
            @break

        @case('info-circle')
            <circle class="icon-accent" cx="12" cy="12" r="8.5"/>
            <circle cx="12" cy="12" r="8.5"/>
            <path d="M12 11v5M12 8h.01"/>
            @break

        @case('alert-triangle')
            <path class="icon-accent" d="m12 3.7 8.5 15a1.3 1.3 0 0 1-1.1 2H4.6a1.3 1.3 0 0 1-1.1-2Z"/>
            <path d="m12 3.7 8.5 15a1.3 1.3 0 0 1-1.1 2H4.6a1.3 1.3 0 0 1-1.1-2ZM12 9v4.5M12 16.5h.01"/>
            @break

        @case('settings')
            <path class="icon-accent" d="m12 3 1.4 2.2 2.6.5 1.8-1.2 2 2-1.2 1.8.5 2.6L21 12l-2.2 1.4-.5 2.6 1.2 1.8-2 2-1.8-1.2-2.6.5L12 21l-1.4-2.2-2.6-.5-1.8 1.2-2-2 1.2-1.8-.5-2.6L3 12l2.2-1.4.5-2.6-1.2-1.8 2-2 1.8 1.2 2.6-.5Z"/>
            <path d="m12 3 1.4 2.2 2.6.5 1.8-1.2 2 2-1.2 1.8.5 2.6L21 12l-2.2 1.4-.5 2.6 1.2 1.8-2 2-1.8-1.2-2.6.5L12 21l-1.4-2.2-2.6-.5-1.8 1.2-2-2 1.2-1.8-.5-2.6L3 12l2.2-1.4.5-2.6-1.2-1.8 2-2 1.8 1.2 2.6-.5ZM12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z"/>
            @break

        @case('logout')
            <path class="icon-accent" d="M4 5.8A1.8 1.8 0 0 1 5.8 4h6.4A1.8 1.8 0 0 1 14 5.8V8h-2V6H6v12h6v-2h2v2.2a1.8 1.8 0 0 1-1.8 1.8H5.8A1.8 1.8 0 0 1 4 18.2Z"/>
            <path d="M4 5.8A1.8 1.8 0 0 1 5.8 4h6.4A1.8 1.8 0 0 1 14 5.8V8M4 18.2A1.8 1.8 0 0 0 5.8 20h6.4a1.8 1.8 0 0 0 1.8-1.8V16M10 12h10m-3-3 3 3-3 3"/>
            @break

        @case('lock')
            <rect class="icon-accent" x="4" y="10" width="16" height="10" rx="2"/>
            <rect x="4" y="10" width="16" height="10" rx="2"/>
            <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v2"/>
            @break

        @case('archive')
            <path class="icon-accent" d="M4 7h16v12.5A1.5 1.5 0 0 1 18.5 21h-13A1.5 1.5 0 0 1 4 19.5Z"/>
            <path d="M3.5 4h17v3h-17ZM4 7h16v12.5A1.5 1.5 0 0 1 18.5 21h-13A1.5 1.5 0 0 1 4 19.5ZM10 11.5h4"/>
            @break

        @case('loader')
            <circle cx="12" cy="12" r="8.5" opacity=".25"/>
            <path class="icon-accent" d="M12 3.5a8.5 8.5 0 0 1 8.5 8.5h-3A5.5 5.5 0 0 0 12 6.5Z"/>
            <path d="M12 3.5a8.5 8.5 0 0 1 8.5 8.5"/>
            @break

        @case('tree')
            <path class="icon-accent" d="m12 3-6 8h3l-4 6h5v4h4v-4h5l-4-6h3Z"/>
            <path d="m12 3-6 8h3l-4 6h5v4h4v-4h5l-4-6h3ZM12 11v6M9 11l3 3 3-3"/>
            @break

        @case('recycle')
            <path class="icon-accent" d="m10.8 4.1 2.1 3.6-2.4.1-1.6 2.7-2.3-1.4 2.3-4ZM19.3 10.2l-4.2.1 1.1-2.1-1.6-2.7 2.4-1.4 2.3 4ZM14.1 19.9 12 16.3l2.4-.1 1.6-2.7 2.3 1.4-2.3 4Z"/>
            <path d="m10.8 4.1 2.1 3.6-2.4.1-1.6 2.7M19.3 10.2l-4.2.1 1.1-2.1-1.6-2.7M14.1 19.9 12 16.3l2.4-.1 1.6-2.7M4.7 13.8l4.2-.1-1.1 2.1 1.6 2.7"/>
            @break

        @case('megaphone')
            <path class="icon-accent" d="M4 10.2h3.7l8.8-4v11.6l-8.8-4H4Z"/>
            <path d="M4 10.2h3.7l8.8-4v11.6l-8.8-4H4ZM7.7 13.8l1.5 5H12l-1.3-5M18.7 9.3a4.2 4.2 0 0 1 0 5.4"/>
            @break

        @case('clipboard-check')
            <rect class="icon-accent" x="5" y="4.5" width="14" height="16" rx="2"/>
            <path d="M9 5V4a3 3 0 0 1 6 0v1M8 5h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2ZM9.2 13l2 2 4-4"/>
            @break

        @case('factory')
            <path class="icon-accent" d="M4 20V10l5 3V9l5 3V5h4v15Z"/>
            <path d="M3 20h18M4 20V10l5 3V9l5 3V5h4v15M7 17h.01M11 17h.01M15 17h.01"/>
            @break

        @case('clipboard-list')
            <rect class="icon-accent" x="5" y="4.5" width="14" height="16" rx="2"/>
            <path d="M9 5V4a3 3 0 0 1 6 0v1M8 5h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2ZM9 10h6M9 14h6M9 18h4"/>
            @break

        @case('list')
            <path class="icon-accent" d="M5 5.5h2v2H5z"/>
            <path d="M5 6.5h.01M9 6.5h10M5 12h.01M9 12h10M5 17.5h.01M9 17.5h10"/>
            @break

        @case('trash-x')
            <path class="icon-accent" d="M6 7h12l-1 13H7Z"/>
            <path d="M4 7h16M9 7V4.5h6V7M6 7l1 13h10l1-13M10 11l4 4m0-4-4 4"/>
            @break

        @case('package')
            <path class="icon-accent" d="m12 3.5 8 4.4v8.2L12 20.5 4 16.1V7.9Z"/>
            <path d="m12 3.5 8 4.4v8.2L12 20.5 4 16.1V7.9ZM4 7.9l8 4.3 8-4.3M12 12.2v8.3M8 5.7l8 4.4"/>
            @break

        @case('truck')
            <path class="icon-accent" d="M3.5 7h10v8H20l-2.5-4H13.5V7Z"/>
            <path d="M3.5 7h10v10h-10ZM13.5 11h4l2.5 4v2h-6.5M7 20a2.2 2.2 0 1 0 0-4.4A2.2 2.2 0 0 0 7 20ZM17 20a2.2 2.2 0 1 0 0-4.4A2.2 2.2 0 0 0 17 20Z"/>
            @break

        @case('axe')
            <path class="icon-accent" d="m15 4 4 4-5 3-4-4Z"/>
            <path d="m15 4 4 4-5 3-4-4ZM12 9 4.5 19.5M8.5 15.5l3 2.2"/>
            @break

        @case('park-bench')
            <path class="icon-accent" d="M5 10h14v5H5Z"/>
            <path d="M3 15h18M5 10h14v5H5ZM6 10V7h12v3M6 15v4M18 15v4"/>
            @break

        @case('park')
            <path class="icon-accent" d="m12 3-5 7h3l-3 6h10l-3-6h3Z"/>
            <path d="m12 3-5 7h3l-3 6h10l-3-6h3ZM12 16v5M8 21h8"/>
            @break

        @case('forest')
            <path class="icon-accent" d="m8 4-4 7h2l-3 6h10l-3-6h2Zm8 3-3.5 6h2l-2.5 5h9l-2.5-5h2Z"/>
            <path d="m8 4-4 7h2l-3 6h10l-3-6h2ZM8 17v4M16 7l-3.5 6h2l-2.5 5h9l-2.5-5h2M16 18v3"/>
            @break

        @case('route')
            <path class="icon-accent" d="M5 18a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm14-7a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
            <path d="M5 18a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm14-7a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM7.5 15.5c2.5-6 5-7 9-7"/>
            @break

        @case('seedling')
            <path class="icon-accent" d="M12 21v-8c0-4.7 2.7-7.4 6.5-7.4-.2 3.8-2.4 6.5-6.5 7.4ZM12 15.5C8 14.6 5.7 12 5.5 8.2c3.9 0 6.5 2.6 6.5 7.3Z"/>
            <path d="M12 21v-8c0-4.7 2.7-7.4 6.5-7.4-.2 3.8-2.4 6.5-6.5 7.4ZM12 15.5C8 14.6 5.7 12 5.5 8.2c3.9 0 6.5 2.6 6.5 7.3Z"/>
            @break

        @case('presentation')
            <rect class="icon-accent" x="4" y="4" width="16" height="11" rx="1.8"/>
            <path d="M4 4h16v11H4ZM12 15v5M8 20h8M8 8h5M8 11h8"/>
            @break

        @case('news')
            <rect class="icon-accent" x="3.5" y="5" width="17" height="14" rx="2"/>
            <path d="M3.5 5h17v14h-17ZM7 9h3M7 12h3M7 15h3M13 9h4M13 12h4M13 15h3"/>
            @break

        @case('image')
            <rect class="icon-accent" x="3.5" y="5" width="17" height="14" rx="2"/>
            <path d="M3.5 5h17v14h-17ZM7.5 10h.01M4.5 17l4.5-4.5 3.3 3.3 2.1-2.1 4.6 4.6"/>
            @break

        @case('send')
            <path class="icon-accent" d="m3.5 4 17 8-17 8 4-8Z"/>
            <path d="m3.5 4 17 8-17 8 4-8Zm4 8h13"/>
            @break

        @case('tag')
            <path class="icon-accent" d="M4 5.5A1.5 1.5 0 0 1 5.5 4H11l8.5 8.5a2 2 0 0 1 0 2.8l-4.2 4.2a2 2 0 0 1-2.8 0L4 11Z"/>
            <path d="M4 5.5A1.5 1.5 0 0 1 5.5 4H11l8.5 8.5a2 2 0 0 1 0 2.8l-4.2 4.2a2 2 0 0 1-2.8 0L4 11ZM8 8h.01"/>
            @break

        @case('user-check')
            <circle class="icon-accent" cx="9" cy="8" r="3.5"/>
            <path d="M3.5 20c.5-3.8 2.4-5.8 5.5-5.8 1.2 0 2.3.3 3.1.9M9 11.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM14.5 18l1.8 1.8 4-4.2"/>
            @break

        @case('command')
            <path class="icon-accent" d="M9.5 4H7a2 2 0 0 0-2 2v3.5a2 2 0 0 0 2 2h3.5a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Zm7 8.5H14a2 2 0 0 0-2 2V17a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2v-3.5a2 2 0 0 0-2-2Z"/>
            <path d="M9.5 4H7a2 2 0 0 0-2 2v3.5a2 2 0 0 0 2 2h3.5a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Zm7 8.5H14a2 2 0 0 0-2 2V17a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2v-3.5a2 2 0 0 0-2-2Z"/>
            @break

        @case('grid')
            <rect class="icon-accent" x="4" y="4" width="6" height="6" rx="1"/>
            <path d="M4 4h6v6H4ZM14 4h6v6h-6ZM4 14h6v6H4ZM14 14h6v6h-6Z"/>
            @break

        @case('external-link')
            <path class="icon-accent" d="M14 4h6v6l-3-3-6 6-2-2 6-6Z"/>
            <path d="M14 4h6v6M20 4l-9 9M11 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-5"/>
            @break

        @case('file-plus')
            <path class="icon-accent" d="M6 3.5h7l5 5V20a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 20Z"/>
            <path d="M6 3.5h7l5 5V20a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 20ZM13 3.5v5h5M12 12v5M9.5 14.5h5"/>
            @break

        @case('message-plus')
            <path class="icon-accent" d="M5 5h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-9l-4.5 3V17A2 2 0 0 1 3 15V7a2 2 0 0 1 2-2Z"/>
            <path d="M5 5h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-9l-4.5 3V17A2 2 0 0 1 3 15V7a2 2 0 0 1 2-2ZM12 8.5v5M9.5 11h5"/>
            @break

        @case('database')
            <ellipse class="icon-accent" cx="12" cy="6" rx="8" ry="3"/>
            <path d="M20 6v12c0 1.7-3.6 3-8 3s-8-1.3-8-3V6M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3M4 18c0 1.7 3.6 3 8 3s8-1.3 8-3M4 6c0-1.7 3.6-3 8-3s8 1.3 8 3-3.6 3-8 3-8-1.3-8-3Z"/>
            @break

        @case('user-plus')
            <circle class="icon-accent" cx="9" cy="8" r="3.5"/>
            <path d="M3.5 20c.5-3.8 2.4-5.8 5.5-5.8 2.1 0 3.7.9 4.7 2.5M9 11.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM18 14v6M15 17h6"/>
            @break

        @case('book-open')
            <path class="icon-accent" d="M4 5.5A8.5 8.5 0 0 1 12 7v13a8.5 8.5 0 0 0-8-1.5Z"/>
            <path d="M4 5.5A8.5 8.5 0 0 1 12 7v13a8.5 8.5 0 0 0-8-1.5ZM20 5.5A8.5 8.5 0 0 0 12 7v13a8.5 8.5 0 0 1 8-1.5Z"/>
            @break

        @case('palette')
            <path class="icon-accent" d="M12 3a9 9 0 1 0 0 18h1.4a1.7 1.7 0 0 0 1.1-3l-.6-.5a1.7 1.7 0 0 1 1.1-3H17a4 4 0 0 0 4-4.1A7.5 7.5 0 0 0 12 3Z"/>
            <path d="M12 3a9 9 0 1 0 0 18h1.4a1.7 1.7 0 0 0 1.1-3l-.6-.5a1.7 1.7 0 0 1 1.1-3H17a4 4 0 0 0 4-4.1A7.5 7.5 0 0 0 12 3ZM7.5 10h.01M10 7.5h.01M14 7.5h.01M16.5 10h.01"/>
            @break

        @case('globe')
            <circle class="icon-accent" cx="12" cy="12" r="8.5"/>
            <circle cx="12" cy="12" r="8.5"/>
            <path d="M3.8 12h16.4M12 3.5c2.3 2.2 3.5 5 3.5 8.5S14.3 18.3 12 20.5C9.7 18.3 8.5 15.5 8.5 12S9.7 5.7 12 3.5Z"/>
            @break

        @case('folder-plus')
            <path class="icon-accent" d="M3.5 7.5A2.5 2.5 0 0 1 6 5h4l2 2.4h6A2.5 2.5 0 0 1 20.5 10v7.5A2.5 2.5 0 0 1 18 20H6a2.5 2.5 0 0 1-2.5-2.5Z"/>
            <path d="M3.5 7.5A2.5 2.5 0 0 1 6 5h4l2 2.4h6A2.5 2.5 0 0 1 20.5 10v7.5A2.5 2.5 0 0 1 18 20H6a2.5 2.5 0 0 1-2.5-2.5ZM12 11v5M9.5 13.5h5"/>
            @break

        @case('layers')
            <path class="icon-accent" d="m12 3.5 8 4.3-8 4.3-8-4.3Z"/>
            <path d="m12 3.5 8 4.3-8 4.3-8-4.3ZM4 12l8 4.3 8-4.3M4 16.2l8 4.3 8-4.3"/>
            @break

        @case('link')
            <path class="icon-accent" d="m10 14-1.7 1.7a3 3 0 0 1-4.3-4.3l3-3a3 3 0 0 1 4.3 0"/>
            <path d="m10 14-1.7 1.7a3 3 0 0 1-4.3-4.3l3-3a3 3 0 0 1 4.3 0M14 10l1.7-1.7a3 3 0 0 1 4.3 4.3l-3 3a3 3 0 0 1-4.3 0M8.5 15.5l7-7"/>
            @break

        {{-- Ikon domain DLH: salinan duotone dari komponen publik khusus agar
             pemanggilan dinamis x-icons.ui tetap mempertahankan konteks layanan. --}}
        @case('alamat')
            <path class="icon-accent" d="M12 21.2s-6.9-5.6-6.9-10.7a6.9 6.9 0 1 1 13.8 0c0 5.1-6.9 10.7-6.9 10.7Z"/>
            <path d="M12 21.2s-6.9-5.6-6.9-10.7a6.9 6.9 0 1 1 13.8 0c0 5.1-6.9 10.7-6.9 10.7Z"/>
            <path d="M14.7 7.9c.4 2.6-1 4.3-3.6 4.5-.4-2.6 1-4.3 3.6-4.5Z"/>
            <path d="M9.7 13.7c.7-1.7 1.9-3 3.5-3.8"/>
            @break

        @case('berhasil')
            <circle class="icon-accent" cx="12" cy="12" r="8.6"/>
            <circle cx="12" cy="12" r="8.6"/>
            <path d="M8.2 12.4l2.5 2.6 5.1-5.4"/>
            @break

        @case('isi-formulir')
            <path class="icon-accent" d="m11.5 18.6.95-3.3 6.35-6.35a1.7 1.7 0 0 1 2.4 2.4l-6.35 6.35-3.35.9Z"/>
            <rect x="4" y="3" width="13" height="18" rx="2.1"/>
            <path d="M7.5 8.3h6M7.5 11.8h6M7.5 15.3h3.1"/>
            <path d="m11.5 18.6.95-3.3 6.35-6.35a1.7 1.7 0 0 1 2.4 2.4l-6.35 6.35-3.35.9Z"/>
            @break

        @case('jam-kerja')
            <circle class="icon-accent" cx="12" cy="12" r="8.6"/>
            <circle cx="12" cy="12" r="8.6"/>
            <path d="M12 5.6v1.4M18.4 12H17M12 18.4V17M5.6 12H7M12 8.8V12l2.5 1.6"/>
            @break

        @case('jam-respons')
            <circle class="icon-accent" cx="10.6" cy="13.4" r="7.2"/>
            <circle cx="10.6" cy="13.4" r="7.2"/>
            <path d="M10.6 9.8v3.6l2.3 1.5M20.6 2.3l-2.9 3.7h2.3l-2.9 3.7"/>
            @break

        @case('misi')
            <circle class="icon-accent" cx="12" cy="12" r="8.2"/>
            <circle cx="12" cy="12" r="8.2"/>
            <circle cx="12" cy="12" r="4.4"/>
            <circle cx="12" cy="12" r="1.1" fill="currentColor" stroke="none"/>
            <path d="M20.4 3.6 12 12M20.4 3.6h-3.3M20.4 3.6v3.3"/>
            @break

        @case('pantau-status')
            <rect class="icon-accent" x="3" y="4.4" width="18" height="15.2" rx="2.2"/>
            <rect x="3" y="4.4" width="18" height="15.2" rx="2.2"/>
            <path d="M3 8.6h18"/>
            <circle cx="5.9" cy="6.5" r=".7" fill="currentColor" stroke="none"/>
            <circle cx="8.3" cy="6.5" r=".7" fill="currentColor" stroke="none"/>
            <path d="M6 14.6h2.1l1.6-3.1 2.6 5.2 1.8-3.5 1 1.4h2.9"/>
            @break

        @case('pengendalian')
            <path class="icon-accent" d="M12 3.1 18.7 5.6v4.8c0 4.4-2.9 7.4-6.7 8.9-3.8-1.5-6.7-4.5-6.7-8.9V5.6L12 3.1Z"/>
            <path d="M12 3.1 18.7 5.6v4.8c0 4.4-2.9 7.4-6.7 8.9-3.8-1.5-6.7-4.5-6.7-8.9V5.6L12 3.1Z"/>
            <circle cx="12" cy="12.6" r="1.05" fill="currentColor" stroke="none"/>
            <path d="M9.6 10.2a3.4 3.4 0 0 1 4.8 0M7.7 8.2a6.1 6.1 0 0 1 8.6 0"/>
            @break

        @case('pilih-layanan')
            <rect class="icon-accent" x="3.4" y="3.4" width="7.1" height="7.1" rx="1.9"/>
            <rect x="3.4" y="3.4" width="7.1" height="7.1" rx="1.9"/>
            <rect x="13.5" y="3.4" width="7.1" height="7.1" rx="1.9"/>
            <rect x="3.4" y="13.5" width="7.1" height="7.1" rx="1.9"/>
            <rect x="13.5" y="13.5" width="7.1" height="7.1" rx="1.9"/>
            <path d="m5.9 6.9 1.5 1.5 2.6-2.9"/>
            @break

        @case('real-time')
            <circle class="icon-accent" cx="12" cy="12" r="8.6"/>
            <circle cx="12" cy="12" r="8.6"/>
            <path d="M12 7.4V12l3.1 1.9M18.9 2.9c.9.6 1.6 1.3 2.2 2.2M5.1 2.9C4.2 3.5 3.5 4.2 2.9 5.1"/>
            @break

        @case('rth-ha')
            <path class="icon-accent" d="M12 3.6c1.9 0 3.6 1.3 4.1 3.1 1.4.5 2.4 1.8 2.4 3.3 0 1.9-1.5 3.4-3.4 3.4H8.9C7 13.4 5.5 11.9 5.5 10c0-1.5 1-2.8 2.4-3.3.5-1.8 2.2-3.1 4.1-3.1Z"/>
            <path d="M12 3.6c1.9 0 3.6 1.3 4.1 3.1 1.4.5 2.4 1.8 2.4 3.3 0 1.9-1.5 3.4-3.4 3.4H8.9C7 13.4 5.5 11.9 5.5 10c0-1.5 1-2.8 2.4-3.3.5-1.8 2.2-3.1 4.1-3.1ZM12 13.4v3.4M3.4 16.8h17.2M5.6 20h12.8"/>
            @break

        @case('rth')
            <path class="icon-accent" d="M12 3.3c2.5 0 4.7 1.7 5.4 4 1.8.6 3.1 2.3 3.1 4.3 0 2.5-2 4.5-4.5 4.5H8c-2.5 0-4.5-2-4.5-4.5 0-2 1.3-3.7 3.1-4.3.7-2.3 2.9-4 5.4-4Z"/>
            <path d="M12 3.3c2.5 0 4.7 1.7 5.4 4 1.8.6 3.1 2.3 3.1 4.3 0 2.5-2 4.5-4.5 4.5H8c-2.5 0-4.5-2-4.5-4.5 0-2 1.3-3.7 3.1-4.3.7-2.3 2.9-4 5.4-4ZM12 16.1v4.4M12 18.4c-1.1-.6-1.9-1.5-2.3-2.7M12 17.6c1.1-.6 1.9-1.5 2.3-2.7M8.2 20.5h7.6"/>
            @break

        @case('sampah')
            <rect class="icon-accent" x="2.9" y="6.2" width="10.4" height="9.3" rx="1.6"/>
            <path d="M13.3 9.2h3.1c.44 0 .86.19 1.15.52l2.3 2.6c.23.26.35.6.35.94v1.32a.9.9 0 0 1-.9.9h-6"/>
            <rect x="2.9" y="6.2" width="10.4" height="9.3" rx="1.6"/>
            <path d="M6.5 8.9v3.9M9.9 8.9v3.9M9.1 17.7h5.7"/>
            <circle cx="7.2" cy="17.7" r="1.9"/>
            <circle cx="16.7" cy="17.7" r="1.9"/>
            @break

        @case('sapa')
            <circle class="icon-accent" cx="13" cy="12.5" r="8.2"/>
            <path d="M4.9 5.2l1.7 1.2M3.6 9.2l2 .5M8.2 13.2V7.4a1.4 1.4 0 0 1 2.8 0v3.4M11 10.8V5.2a1.4 1.4 0 0 1 2.8 0v5.6M13.8 10.8V6.2a1.4 1.4 0 0 1 2.8 0v5.4M16.6 12v-1.2a1.4 1.4 0 0 1 2.8 0v4c0 3.7-2.6 6.4-6.3 6.4-2.9 0-4.4-1.5-6.4-5.3-.5-.9-.2-2 .7-2.5.8-.5 1.9-.2 2.4.6l.9 1.6"/>
            @break

        @case('tata-penataan')
            <rect class="icon-accent" x="3.25" y="3.25" width="17.5" height="17.5" rx="2.6"/>
            <rect x="3.25" y="3.25" width="17.5" height="17.5" rx="2.6"/>
            <path d="M3.25 9.4h17.5M9.9 9.4v11.35"/>
            <rect x="13.1" y="12.6" width="4.6" height="4.7" rx="1"/>
            <rect x="5.1" y="13.2" width="3" height="2.1" rx=".7"/>
            <circle cx="6.55" cy="6.3" r="1.25"/>
            @break

        @case('terbuka')
            <path class="icon-accent" d="M2.9 12S6.4 5.9 12 5.9 21.1 12 21.1 12 17.6 18.1 12 18.1 2.9 12 2.9 12Z"/>
            <path d="M2.9 12S6.4 5.9 12 5.9 21.1 12 21.1 12 17.6 18.1 12 18.1 2.9 12 2.9 12Z"/>
            <circle cx="12" cy="12" r="3.1"/>
            <circle cx="12" cy="12" r=".9" fill="currentColor" stroke="none"/>
            @break

        @case('terintegrasi')
            <circle class="icon-accent" cx="12" cy="12" r="2.9"/>
            <circle cx="12" cy="12" r="2.9"/>
            <circle cx="4.6" cy="5.4" r="1.9"/>
            <circle cx="19.4" cy="5.4" r="1.9"/>
            <circle cx="12" cy="19.4" r="1.9"/>
            <path d="M6.2 6.6 9.9 10m7.9-3.4-3.7 3.4M12 14.9v2.6"/>
            @break

        @case('titik-tps')
            <path class="icon-accent" d="M12 21.2s-6.9-5.6-6.9-10.7a6.9 6.9 0 1 1 13.8 0c0 5.1-6.9 10.7-6.9 10.7Z"/>
            <path d="M12 21.2s-6.9-5.6-6.9-10.7a6.9 6.9 0 1 1 13.8 0c0 5.1-6.9 10.7-6.9 10.7Z"/>
            <path d="M9.9 8.2h4.2l-.45 3.7a1.35 1.35 0 0 1-1.34 1.2h-.62a1.35 1.35 0 0 1-1.34-1.2L9.9 8.2Z"/>
            @break

        @case('ton-sampah')
            <path class="icon-accent" d="M6.2 8.4h11.6l-1.1 9.2a2 2 0 0 1-2 1.8H9.3a2 2 0 0 1-2-1.8Z"/>
            <path d="M8.6 8.1V6.3a3.4 3.4 0 0 1 6.8 0v1.8M6.2 8.4h11.6l-1.1 9.2a2 2 0 0 1-2 1.8H9.3a2 2 0 0 1-2-1.8ZM14.29 12.9A2.5 2.5 0 0 0 9.71 12.9m0 0-.4-1.2m.4 1.2 1.2-.4M9.71 14.9a2.5 2.5 0 0 0 4.58 0m0 0 .4 1.2m-.4-1.2-1.2.4"/>
            @break

        @case('visi')
            <path class="icon-accent" d="M7.2 14a4.8 4.8 0 0 1 9.6 0Z"/>
            <path d="M7.2 14a4.8 4.8 0 0 1 9.6 0M3.2 14h17.6M12 5.4V7m-5.4.5 1.1 1.1m9.7-1.1-1.1 1.1M5.6 17.6c2 1.2 4.2 1.8 6.4 1.8s4.4-.6 6.4-1.8"/>
            @break

        @default
            {{-- Nama sudah tervalidasi sebelum switch; jalur ini hanya proteksi tambahan. --}}
            <path class="icon-accent" d="M10.3 4.4 3.5 17a2 2 0 0 0 1.8 3h13.4a2 2 0 0 0 1.8-3L13.7 4.4a2 2 0 0 0-3.4 0Z"/>
            <path d="M10.3 4.4 3.5 17a2 2 0 0 0 1.8 3h13.4a2 2 0 0 0 1.8-3L13.7 4.4a2 2 0 0 0-3.4 0ZM12 9v4M12 16.5h.01"/>
    @endswitch
</svg>
@endif
