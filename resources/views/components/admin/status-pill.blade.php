@props([
    'variant' => 'default',   // success | warning | danger | info | default | neutral
    'label' => null,
    'pulse' => false,         // dot berdenyut (status aktif/pending)
    'dot' => true,
])

@php
    $map = [
        'success' => 'border-success-200 bg-success-50 text-success-700',
        'warning' => 'border-warning-200 bg-warning-50 text-warning-700',
        'danger'  => 'border-danger-200 bg-danger-50 text-danger-700',
        'info'    => 'border-info-200 bg-info-50 text-info-700',
        'neutral' => 'border-slate-200 bg-slate-50 text-slate-600',
        'default' => 'border-slate-200 bg-slate-50 text-slate-700',
    ];
    $dotColor = [
        'success' => 'text-success-500',
        'warning' => 'text-warning-500',
        'danger'  => 'text-danger-500',
        'info'    => 'text-info-500',
        'neutral' => 'text-slate-400',
        'default' => 'text-slate-400',
    ];
    $cls = $map[$variant] ?? $map['default'];
    $dc = $dotColor[$variant] ?? $dotColor['default'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex max-w-full items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold leading-none $cls"]) }}>
    @if($dot)
        <span class="relative grid size-1.5 place-items-center {{ $dc }} {{ $pulse ? 'dot-pulse' : '' }}">
            <span class="size-1.5 rounded-full bg-current"></span>
        </span>
    @endif
    <span class="truncate">{{ $label ?? $slot }}</span>
</span>
