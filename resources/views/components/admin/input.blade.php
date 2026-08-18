@props([
    'name' => '',
    'type' => 'text',
    'label' => '',
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'hint' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'prefix' => null,
    'suffix' => null,
])

@php
    $id = $attributes->get('id', 'input-'.Str::random(8));
    $hasError = $error === '' ? false : (bool) ($error || $errors->has($name));
    $errorMessage = $error === '' ? null : ($error ?? $errors->first($name));
    $currentValue = old($name, $value);
    $providedDescribedBy = trim((string) $attributes->get('aria-describedby', ''));
    $feedbackId = $hasError ? $id.'-error' : ($hint ? $id.'-hint' : null);
    $describedBy = trim($providedDescribedBy.' '.($feedbackId ?? ''));
    $hasLeft = $prefix !== null || ($icon && $iconPosition === 'left');
    $hasRight = $suffix !== null || ($icon && $iconPosition === 'right');
    $controlClasses = 'block h-11 w-full rounded-xl border bg-white px-3.5 text-sm text-slate-950 outline-none transition-[border-color,box-shadow,background-color] duration-150 placeholder:text-slate-400 hover:border-slate-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500 disabled:opacity-75 read-only:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500 dark:hover:border-slate-600 dark:focus:border-brand-400 dark:focus:ring-brand-400/20 dark:disabled:bg-slate-900 dark:read-only:bg-slate-900';
    $controlClasses .= $hasError ? ' border-danger-500 focus:border-danger-600 focus:ring-danger-500/20 dark:border-danger-400 dark:focus:border-danger-400 dark:focus:ring-danger-400/20' : ' border-slate-200';
    $controlClasses .= $hasLeft ? ' pl-10' : '';
    $controlClasses .= $hasRight ? ' pr-12' : '';
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-slate-800 dark:text-slate-100">
            {{ $label }}@if($required)<span class="ml-0.5 text-danger-600 dark:text-danger-400" aria-hidden="true">*</span><span class="sr-only"> wajib diisi</span>@endif
        </label>
    @endif

    <div class="relative">
        @if($prefix !== null)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3.5 text-sm font-medium text-slate-500 dark:text-slate-400">{{ $prefix }}</span>
        @elseif($icon && $iconPosition === 'left')
            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-slate-400 dark:text-slate-500">
                <x-admin.icon :name="$icon" :size="17" aria-hidden="true" />
            </span>
        @endif

        <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $currentValue }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            @if($hasError) aria-invalid="true" @endif
            @if($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->except(['id', 'class', 'type', 'value', 'aria-describedby'])->merge(['class' => $controlClasses]) }}
        >

        @if($suffix !== null)
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-sm font-medium text-slate-500 dark:text-slate-400">{{ $suffix }}</span>
        @elseif($icon && $iconPosition === 'right')
            <span class="pointer-events-none absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-400 dark:text-slate-500">
                <x-admin.icon :name="$icon" :size="17" aria-hidden="true" />
            </span>
        @endif
    </div>

    @if($hasError)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 text-xs font-medium leading-5 text-danger-600 dark:text-danger-300" role="alert">
            <x-admin.icon name="alert-circle" :size="15" class="mt-0.5 shrink-0" aria-hidden="true" />
            <span>{{ $errorMessage }}</span>
        </p>
    @elseif($hint)
        <p id="{{ $id }}-hint" class="text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
</div>
