@props([
    'label' => '',
    'value' => 0,
    'icon' => 'chart',
    'color' => 'emerald',   // emerald|sky|teal|amber|rose|purple|indigo|bay|slate
])

@php
    $palette = [
        'emerald' => ['hex' => '#059669', 'bg' => 'bg-brand-50', 'text' => 'text-brand-600'],
        'sky'     => ['hex' => '#0284c7', 'bg' => 'bg-info-50', 'text' => 'text-info-600'],
        'teal'    => ['hex' => '#0d9488', 'bg' => 'bg-teal-50', 'text' => 'text-teal-700'],
        'amber'   => ['hex' => '#d97706', 'bg' => 'bg-warning-50', 'text' => 'text-warning-600'],
        'rose'    => ['hex' => '#e11d48', 'bg' => 'bg-danger-50', 'text' => 'text-danger-600'],
        'purple'  => ['hex' => '#7c3aed', 'bg' => 'bg-clay-50', 'text' => 'text-clay-600'],
        'indigo'  => ['hex' => '#4f46e5', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-600'],
        'bay'     => ['hex' => '#0dabce', 'bg' => 'bg-info-50', 'text' => 'text-info-600'],
        'slate'   => ['hex' => '#64748b', 'bg' => 'bg-slate-50', 'text' => 'text-slate-600'],
    ];
    $c = $palette[$color] ?? $palette['emerald'];
    $numeric = is_numeric($value);
@endphp

<div>
    <x-admin.card :padding="false" class="card-lift overflow-hidden">
        <div class="flex items-center gap-4 p-5">
            <div class="grid size-12 shrink-0 place-items-center rounded-xl {{ $c['bg'] }} {{ $c['text'] }}">
                <x-admin.icon :name="$icon" :size="24" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-semibold uppercase tracking-[0.06em] text-ink-500">{{ $label }}</p>
                <p class="mt-1 text-xl font-bold tracking-tight text-ink-900">
                    @if($numeric)
                        <x-admin.count-up :value="(int) $value" />
                    @else
                        {{ $value }}
                    @endif
                </p>
            </div>
        </div>
    </x-admin.card>
</div>
