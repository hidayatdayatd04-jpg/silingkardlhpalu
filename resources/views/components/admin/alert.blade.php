@props(['type' => 'info', 'title' => null, 'dismissible' => false])

@php
    $configs = [
        'success' => ['shell' => 'border-success-200 bg-success-50 dark:border-success-900/70 dark:bg-success-950/35', 'text' => 'text-success-800 dark:text-success-200', 'icon' => 'circle-check', 'iconColor' => 'text-success-700 dark:text-success-300'],
        'error' => ['shell' => 'border-danger-200 bg-danger-50 dark:border-danger-900/70 dark:bg-danger-950/35', 'text' => 'text-danger-800 dark:text-danger-200', 'icon' => 'alert-circle', 'iconColor' => 'text-danger-700 dark:text-danger-300'],
        'warning' => ['shell' => 'border-warning-200 bg-warning-50 dark:border-warning-900/70 dark:bg-warning-950/35', 'text' => 'text-warning-800 dark:text-warning-200', 'icon' => 'alert-triangle', 'iconColor' => 'text-warning-700 dark:text-warning-300'],
        'info' => ['shell' => 'border-info-200 bg-info-50 dark:border-info-900/70 dark:bg-info-950/35', 'text' => 'text-info-800 dark:text-info-200', 'icon' => 'info-circle', 'iconColor' => 'text-info-700 dark:text-info-300'],
    ];
    $config = $configs[$type] ?? $configs['info'];
@endphp

<div
    {{ $attributes->merge(['class' => 'rounded-2xl border px-4 py-3.5 '.$config['shell']]) }}
    @if($dismissible) x-data="{ show: true }" x-show="show" x-transition:leave="transition-[opacity,transform] duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="-translate-y-1 opacity-0" @endif
    role="{{ $type === 'error' ? 'alert' : 'status' }}"
>
    <div class="flex items-start gap-3">
        <div class="grid size-8 shrink-0 place-items-center rounded-xl bg-white/55 {{ $config['iconColor'] }} dark:bg-black/10"><x-admin.icon :name="$config['icon']" :size="18" aria-hidden="true" /></div>
        <div class="min-w-0 flex-1 {{ $config['text'] }}">
            @if($title)<h2 class="text-sm font-bold">{{ $title }}</h2><div class="mt-0.5 text-sm leading-6">{{ $slot }}</div>@else<div class="text-sm font-medium leading-6">{{ $slot }}</div>@endif
        </div>
        @if($dismissible)
            <button type="button" x-on:click="show = false" class="grid size-8 shrink-0 place-items-center rounded-lg {{ $config['iconColor'] }} outline-none transition-colors duration-150 hover:bg-white/55 focus-visible:bg-white/55 dark:hover:bg-black/10 dark:focus-visible:bg-black/10" aria-label="Tutup notifikasi"><x-admin.icon name="x" :size="17" aria-hidden="true" /></button>
        @endif
    </div>
</div>
