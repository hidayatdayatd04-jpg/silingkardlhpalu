@props([
    'name' => '',
    'type' => 'date',            // 'date' | 'datetime-local'
    'label' => '',
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'hint' => null,
    'icon' => null,              // default: clock (datetime-local) / calendar (date)
    'min' => null,
    'max' => null,
    'step' => null,
])

@php
    $id = $attributes->get('id', 'datefield-' . Str::random(6));
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
    $currentValue = old($name, $value);
    $resolvedIcon = $icon ?? ($type === 'datetime-local' ? 'clock' : 'calendar');
    $ringState = $hasError
        ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-100'
        : 'border-slate-300 hover:border-slate-400 focus:border-brand-500 focus:ring-brand-100';
@endphp

<div class="space-y-1.5"
     x-data="{ shake: {{ $hasError ? 'true' : 'false' }} }"
     x-init="if (shake) { $refs.field.classList.add('field-shake'); setTimeout(() => $refs.field.classList.remove('field-shake'), 500) }">
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-ink-800">
            {{ $label }}@if($required)<span class="text-danger-500"> *</span>@endif
        </label>
    @endif

    <div class="relative" x-ref="field">
        <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $currentValue }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            @if($min !== null) min="{{ $min }}" @endif
            @if($max !== null) max="{{ $max }}" @endif
            @if($step !== null) step="{{ $step }}" @endif
            @if($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->except(['id', 'class', 'type', 'value']) }}
            class="block w-full rounded-lg border bg-white py-2.5 pl-3.5 pr-11 text-sm font-medium text-ink-900 shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition placeholder:text-slate-400 focus:ring-4 {{ $ringState }} {{ $disabled ? 'cursor-not-allowed bg-slate-50 opacity-70' : '' }} {{ $readonly ? 'bg-slate-50' : '' }}"
        >
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400">
            <x-admin.icon :name="$resolvedIcon" :size="18" />
        </div>
    </div>

    @if($hasError)
        <p id="{{ $id }}-error" class="flex items-center gap-1 text-xs font-semibold text-danger-600">
            <x-admin.icon name="alert-circle" :size="14" />
            {{ $errorMessage }}
        </p>
    @elseif($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
