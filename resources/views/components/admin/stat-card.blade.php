@props([
    'label' => '',
    'value' => 0,
    'icon' => 'chart',
    'color' => 'emerald',      // emerald|sky|teal|amber|rose|purple|indigo|bay|slate|blue (alias)
    'tone' => null,            // alias untuk color
    'trend' => null,           // hanya render bila data historis real disuplai
    'trendUp' => true,
    'sublabel' => null,
    'href' => null,
])

@php
    $color = $tone ?? $color;

    // Palet aksen — hex eksplisit agar render konsisten tanpa bergantung token tema.
    $palette = [
        'emerald' => '#059669',
        'brand'   => '#059669',
        'sky'     => '#0284c7',
        'blue'    => '#0284c7',
        'bay'     => '#0dabce',
        'info'    => '#0dabce',
        'teal'    => '#0d9488',
        'amber'   => '#d97706',
        'warning' => '#d97706',
        'rose'    => '#e11d48',
        'danger'  => '#e11d48',
        'red'     => '#e11d48',
        'purple'  => '#7c3aed',
        'clay'    => '#c74a26',
        'indigo'  => '#4f46e5',
        'slate'   => '#64748b',
    ];
    $hex = $palette[$color] ?? $palette['emerald'];
    $rgb = implode(',', array_map('hexdec', str_split(ltrim($hex, '#'), 2)));
    $tint = 'rgba(' . $rgb . ',0.12)';
    $tintStrong = 'rgba(' . $rgb . ',0.18)';
    $numeric = is_numeric($value);
@endphp

<x-admin.card :padding="false" {{ $attributes->merge(['class' => 'card-lift overflow-hidden group relative']) }}>
    @if($href)<a href="{{ $href }}" class="block">@endif

    {{-- Aksen warna tipis di tepi kiri --}}
    <span class="absolute inset-y-0 left-0 w-1 rounded-l-lg" style="background: {{ $hex }};"></span>

    <div class="relative p-4 pl-5">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-semibold uppercase tracking-[0.06em] text-ink-500">{{ $label }}</p>
                <p class="mt-1.5 text-xl font-bold tracking-tight text-ink-900">
                    @if($numeric)
                        <x-admin.count-up :value="(int) $value" />
                    @else
                        {{ $value }}
                    @endif
                </p>

                @if($sublabel)
                    <p class="mt-0.5 text-[11px] text-ink-400">{{ $sublabel }}</p>
                @endif

                @if($trend !== null)
                    <div class="mt-2 flex items-center gap-1.5">
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-bold {{ $trendUp ? 'bg-success-100 text-success-700' : 'bg-danger-100 text-danger-700' }}">
                            <x-admin.icon name="trending-up" :size="12" class="{{ $trendUp ? '' : 'rotate-180' }}" />
                            {{ $trend }}
                        </span>
                        <span class="text-[11px] text-slate-500">vs periode lalu</span>
                    </div>
                @endif
            </div>

            <div class="grid size-9 shrink-0 place-items-center rounded-lg transition group-hover:scale-105 group-hover:shadow-sm" style="background: {{ $tint }}; color: {{ $hex }};">
                <x-admin.icon :name="$icon" :size="18" :stroke="2" />
            </div>
        </div>
    </div>

    @if($slot->isNotEmpty())
        <div class="border-t border-slate-100 px-6 py-3 text-xs text-slate-500">{{ $slot }}</div>
    @endif

    @if($href)</a>@endif
</x-admin.card>
