@props(['type' => 'info', 'title' => null, 'dismissible' => false])

@php
    $configs = [
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-200',
            'text' => 'text-green-800',
            'icon' => 'circle-check',
            'iconColor' => 'text-green-600',
        ],
        'error' => [
            'bg' => 'bg-rose-50',
            'border' => 'border-rose-200',
            'text' => 'text-rose-800',
            'icon' => 'alert-circle',
            'iconColor' => 'text-rose-600',
        ],
        'warning' => [
            'bg' => 'bg-amber-50',
            'border' => 'border-amber-200',
            'text' => 'text-amber-800',
            'icon' => 'alert-triangle',
            'iconColor' => 'text-amber-600',
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'text' => 'text-blue-800',
            'icon' => 'info-circle',
            'iconColor' => 'text-blue-600',
        ],
    ];
    
    $config = $configs[$type] ?? $configs['info'];
@endphp

<div 
    {{ $attributes->merge(['class' => "rounded-lg border p-4 {$config['bg']} {$config['border']}"]) }}
    @if($dismissible) x-data="{ show: true }" x-show="show" @endif
>
    <div class="flex gap-3">
        <div class="shrink-0 {{ $config['iconColor'] }}">
            <x-admin.icon :name="$config['icon']" :size="20" />
        </div>
        
        <div class="flex-1 {{ $config['text'] }}">
            @if($title)
                <h4 class="text-sm font-bold">{{ $title }}</h4>
                <div class="mt-1 text-sm">{{ $slot }}</div>
            @else
                <div class="text-sm font-semibold">{{ $slot }}</div>
            @endif
        </div>
        
        @if($dismissible)
            <button 
                x-on:click="show = false"
                class="shrink-0 text-slate-400 transition hover:text-slate-600"
            >
                <x-admin.icon name="x" :size="18" />
            </button>
        @endif
    </div>
</div>
