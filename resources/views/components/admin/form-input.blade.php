@props([
    'name' => '',
    'type' => 'text',
    'label' => '',
    'placeholder' => ' ',       // penting utk floating (:placeholder-shown)
    'value' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'hint' => null,
    'icon' => null,             // icon prefix
    'suffix' => null,           // teks/simbol suffix
    'floating' => true,
    'step' => null,
    'min' => null,
    'max' => null,
    'autocomplete' => null,
])

@php
    $id = $attributes->get('id', 'fi-' . Str::random(6));
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
    $currentValue = old($name, $value);

    $padL = $icon ? 'pl-11' : 'pl-3.5';
    $padR = $suffix ? 'pr-11' : 'pr-3.5';

    $ringState = $hasError
        ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-100'
        : 'border-slate-300 hover:border-slate-400 focus:border-brand-500 focus:ring-brand-100';
@endphp

<div
    class="space-y-1.5"
    x-data="{ shake: {{ $hasError ? 'true' : 'false' }} }"
    x-init="if (shake) { $refs.field.classList.add('field-shake'); setTimeout(() => $refs.field.classList.remove('field-shake'), 500) }"
>
    @if($label && !$floating)
        <label for="{{ $id }}" class="block text-sm font-semibold text-ink-800">
            {{ $label }}@if($required)<span class="text-danger-500"> *</span>@endif
        </label>
    @endif

    <div class="relative" x-ref="field">
        @if($icon)
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                <x-admin.icon :name="$icon" :size="18" />
            </div>
        @endif

        <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $currentValue }}"
            placeholder="{{ $floating ? ' ' : $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            @if($step !== null) step="{{ $step }}" @endif
            @if($min !== null) min="{{ $min }}" @endif
            @if($max !== null) max="{{ $max }}" @endif
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->except(['id', 'class', 'type', 'value']) }}
            class="peer block w-full rounded-lg border {{ $padL }} {{ $padR }} {{ $floating && $label ? 'pt-5 pb-2' : 'py-2.5' }} text-sm font-medium text-ink-900 shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition placeholder:text-slate-400 focus:ring-4 {{ $ringState }} {{ $disabled ? 'cursor-not-allowed bg-slate-50 opacity-70' : 'bg-white' }} {{ $readonly ? 'bg-slate-50' : '' }}"
        >

        @if($floating && $label)
            <label
                for="{{ $id }}"
                class="pointer-events-none absolute {{ $icon ? 'left-11' : 'left-3.5' }} top-1.5 origin-left text-xs font-semibold text-slate-400 transition-all duration-150
                       peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-sm peer-placeholder-shown:font-medium
                       peer-focus:top-1.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:{{ $hasError ? 'text-danger-600' : 'text-brand-600' }}"
            >
                {{ $label }}@if($required)<span class="text-danger-500"> *</span>@endif
            </label>
        @endif

        @if($suffix)
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-sm font-medium text-slate-500">
                {{ $suffix }}
            </div>
        @endif
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
