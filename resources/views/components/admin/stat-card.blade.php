@props([
    'label' => '',
    'value' => 0,
    'icon' => 'chart',
    'color' => 'emerald',      // emerald|sky|amber|rose|purple|blue|teal|indigo
    'trend' => null,           // hanya render bila data historis real disuplai
    'trendUp' => true,
    'href' => null,
])

@php
    $map = [
        'emerald' => ['grad' => 'var(--gradient-stat-brand)',   'icon' => 'bg-brand-600'],
        'sky'     => ['grad' => 'var(--gradient-stat-info)',    'icon' => 'bg-info-600'],
        'blue'    => ['grad' => 'var(--gradient-stat-info)',    'icon' => 'bg-info-600'],
        'teal'    => ['grad' => 'var(--gradient-stat-info)',    'icon' => 'bg-info-600'],
        'amber'   => ['grad' => 'var(--gradient-stat-warning)', 'icon' => 'bg-warning-500'],
        'rose'    => ['grad' => 'var(--gradient-stat-danger)',  'icon' => 'bg-danger-600'],
        'purple'  => ['grad' => 'var(--gradient-stat-clay)',    'icon' => 'bg-clay-600'],
        'indigo'  => ['grad' => 'var(--gradient-stat-clay)',    'icon' => 'bg-clay-600'],
    ];
    $c = $map[$color] ?? $map['emerald'];
    $numeric = is_numeric($value);
@endphp

<x-admin.card :padding="false" {{ $attributes->merge(['class' => 'card-lift overflow-hidden']) }}>
    @if($href)<a href="{{ $href }}" class="block">@endif

    <div class="relative p-6" style="background: {{ $c['grad'] }};">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-ink-600">{{ $label }}</p>
                <p class="mt-2 text-display font-extrabold text-ink-900">
                    @if($numeric)
                        <x-admin.count-up :value="(int) $value" />
                    @else
                        {{ $value }}
                    @endif
                </p>

                @if($trend !== null)
                    <div class="mt-3 flex items-center gap-1.5">
                        <span class="flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold {{ $trendUp ? 'bg-success-100 text-success-700' : 'bg-danger-100 text-danger-700' }}">
                            <x-admin.icon name="trending-up" :size="14" class="{{ $trendUp ? '' : 'rotate-180' }}" />
                            {{ $trend }}
                        </span>
                        <span class="text-xs text-slate-500">vs periode lalu</span>
                    </div>
                @endif
            </div>

            <div class="grid size-14 shrink-0 place-items-center rounded-xl {{ $c['icon'] }} text-white shadow-[var(--shadow-lift)]">
                <x-admin.icon :name="$icon" :size="26" :stroke="2" />
            </div>
        </div>
    </div>

    @if($slot->isNotEmpty())
        <div class="border-t border-slate-100 px-6 py-4">{{ $slot }}</div>
    @endif

    @if($href)</a>@endif
</x-admin.card>
