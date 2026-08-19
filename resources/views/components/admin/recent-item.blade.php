@props([
    'icon' => 'file-text',
    'iconColor' => 'emerald',
    'title' => '',
    'subtitle' => '',
    'time' => '',
    'badge' => null,
    'badgeVariant' => 'default',   // dipetakan ke status-pill variant
    'href' => null,
    'avatarName' => null,          // bila diisi → tampil avatar inisial, bukan icon
])

@php
    $iconColorClasses = [
        'emerald' => 'bg-brand-100 text-brand-600',
        'sky'     => 'bg-info-100 text-info-600',
        'amber'   => 'bg-warning-100 text-warning-600',
        'rose'    => 'bg-danger-100 text-danger-600',
        'purple'  => 'bg-clay-100 text-clay-600',
        'slate'   => 'bg-slate-100 text-slate-600',
    ];
    $iconClass = $iconColorClasses[$iconColor] ?? $iconColorClasses['emerald'];

    $badgeText = null;
    if ($badge !== null) {
        if (is_object($badge) && method_exists($badge, 'label')) {
            $badgeText = $badge->label();
        } elseif (is_object($badge) && method_exists($badge, 'value')) {
            $badgeText = $badge->value;
        } else {
            $badgeText = (string) $badge;
        }
    }
    $badgeLower = mb_strtolower($badgeText ?? '');
    $pillVariant = match(true) {
        in_array($badgeVariant, ['success','warning','danger','info','neutral']) => $badgeVariant,
        str_contains($badgeLower, 'ditindaklanjuti') || (str_contains($badgeLower, 'ditinjau') && !str_contains($badgeLower, 'belum')) => 'success',
        str_contains($badgeLower, 'belum') => 'warning',
        str_contains($badgeLower, 'tolak') => 'danger',
        default => 'neutral',
    };
@endphp

<div class="stagger-item relative flex items-start gap-3 rounded-lg px-4 py-3 transition hover:bg-slate-50">
    @if($href)
        <a href="{{ $href }}" class="absolute inset-0 z-10" aria-label="{{ $title }}"></a>
    @endif

    @if($avatarName)
        <div class="relative z-20 shrink-0">
            <x-admin.avatar :name="$avatarName" size="md" />
        </div>
    @else
        <div class="relative z-20 grid size-10 shrink-0 place-items-center rounded-lg {{ $iconClass }}">
            <x-admin.icon :name="$icon" :size="18" />
        </div>
    @endif

    <div class="relative z-20 min-w-0 flex-1">
        <div class="flex items-start justify-between gap-2">
            <p class="truncate font-mono text-sm font-bold text-ink-900">{{ $title }}</p>
            @if($badge)
                <x-admin.status-pill :variant="$pillVariant" :label="$badgeText" class="relative z-30 shrink-0" />
            @endif
        </div>
        @if($subtitle)
            <p class="mt-0.5 truncate text-xs text-slate-600">{{ $subtitle }}</p>
        @endif
        @if($time)
            <p class="mt-1 flex items-center gap-1 text-xs text-slate-400">
                <x-admin.icon name="clock" :size="12" /> {{ $time }}
            </p>
        @endif
    </div>
</div>
