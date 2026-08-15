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
    'suffix' => null,
    'floating' => false,
    'step' => null,
    'min' => null,
    'max' => null,
    'autocomplete' => null,
    'toggleable' => false,
])

@php
    $id = $attributes->get('id', 'fi-' . Str::random(6));
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
    $currentValue = old($name, $value);
    $hasIcon = $icon !== null;
    $hasSuffix = $suffix || $toggleable;
@endphp

<div
    class="fi-field"
    x-data="{ shake: {{ $hasError ? 'true' : 'false'}}, show: false }"
    x-init="if (shake) { $refs.field.classList.add('field-shake'); setTimeout(() => $refs.field.classList.remove('field-shake'), 500) }"
>
    @if($label && !$floating)
        <label for="{{ $id }}" class="fi-label">
            {{ $label }}@if($required)<span class="fi-required"> *</span>@endif
        </label>
    @endif

    <div class="fi-pill-wrap" x-ref="field">
        @if($icon)
            <span class="fi-pill-icon-left">
                <x-admin.icon :name="$icon" :size="18" />
            </span>
        @endif

        <input
            @if($toggleable) :type="show ? 'text' : 'password'" @else type="{{ $type }}" @endif
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
            class="fi-pill-input
                {{ $hasError ? 'fi-pill-input--error' : '' }}
                {{ $disabled ? 'fi-pill-input--disabled' : '' }}
                {{ $readonly ? 'fi-pill-input--readonly' : '' }}
                {{ $hasIcon ? 'fi-pill-input--icon-left' : '' }}
                {{ $hasSuffix ? 'fi-pill-input--suffix' : '' }}"
        >

        @if($toggleable)
            <button type="button" @click="show = !show"
                class="fi-toggle-btn"
                :title="show ? 'Sembunyikan password' : 'Tampilkan password'">
                <x-admin.icon name="eye" :size="18" x-show="!show" />
                <x-admin.icon name="eye-off" :size="18" x-show="show" x-cloak />
            </button>
        @endif

        @if($suffix)
            <span class="fi-pill-suffix">
                <span class="text-sm font-medium text-slate-500">{{ $suffix }}</span>
            </span>
        @endif

        @if($floating && $label)
            <label
                for="{{ $id }}"
                class="fi-float-label {{ $hasIcon ? 'fi-float-label--icon' : '' }} {{ $hasError ? 'fi-float-label--error' : '' }}"
            >
                {{ $label }}@if($required)<span class="fi-required"> *</span>@endif
            </label>
        @endif
    </div>

    @if($hasError)
        <p id="{{ $id }}-error" class="fi-error">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            {{ $errorMessage }}
        </p>
    @elseif($hint)
        <p class="fi-hint-sub">{{ $hint }}</p>
    @endif

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap');
        .fi-field { position: relative; font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif; margin-bottom: 4px; }
        .fi-label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px; cursor: pointer; letter-spacing: 0.01em; }
        .fi-required { color: #f43f5e; font-size: 13px; font-weight: 400; margin-left: 2px; }
        .fi-pill-wrap { position: relative; display: flex; align-items: center; }
        .fi-pill-input { width: 100%; height: 46px; border-radius: 12px; border: 1.5px solid #e2e8f0; background: #fff; padding: 0 16px; font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif; font-size: 14px; transition: border-color .2s ease, box-shadow .2s ease; outline: none; color: #1e293b; }
        .fi-pill-input::placeholder { color: #94a3b8; font-size: 13.5px; }
        .fi-pill-input:hover:not(:focus):not(.fi-pill-input--error) { border-color: #cbd5e1; }
        .fi-pill-input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
        .fi-pill-input--error { border-color: #ef4444 !important; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.08) !important; }
        .fi-pill-input--disabled { cursor: not-allowed; opacity: 0.5; background: #f8fafc; }
        .fi-pill-input--readonly { cursor: not-allowed; background: #f8fafc; }
        .fi-pill-input--icon-left { padding-left: 44px; }
        .fi-pill-input--suffix { padding-right: 44px; }
        .fi-pill-icon-left { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); display: flex; align-items: center; pointer-events: none; z-index: 1; color: #94a3b8; }
        .fi-toggle-btn { position: absolute; right: 14px; display: flex; align-items: center; justify-content: center; color: #94a3b8; cursor: pointer; transition: color .15s ease; background: none; border: none; padding: 2px; z-index: 1; }
        .fi-toggle-btn:hover { color: #10b981; }
        .fi-toggle-btn:focus { outline: none; color: #10b981; }
        .fi-pill-suffix { position: absolute; right: 14px; display: flex; align-items: center; pointer-events: none; z-index: 1; font-size: 13.5px; color: #64748b; font-weight: 500; }
        .fi-float-label { position: absolute; left: {{ $hasIcon ? '44px' : '16px' }}; top: 50%; transform: translateY(-50%); transform-origin: left center; font-size: 13.5px; font-weight: 500; color: #94a3b8; pointer-events: none; transition: all .15s ease; }
        .fi-float-label--icon { left: 44px; }
        .fi-float-label--error { color: #ef4444; }
        .fi-pill-input:not(:placeholder-shown) ~ .fi-float-label,
        .fi-pill-input:focus ~ .fi-float-label { top: 1.5px; transform: translateY(0) scale(0.85); color: #10b981; }
        .fi-pill-input--error:not(:placeholder-shown) ~ .fi-float-label--error,
        .fi-pill-input--error:focus ~ .fi-float-label--error { color: #ef4444; }
        .fi-error { display: flex; align-items: center; gap: 5px; margin-top: 6px; font-size: 12px; font-weight: 500; color: #ef4444; font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif; }
        .fi-error svg { width: 14px; height: 14px; flex-shrink: 0; }
        .fi-hint-sub { margin-top: 6px; font-size: 12px; color: #64748b; font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif; }
        @keyframes fieldShake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-4px); } 75% { transform: translateX(4px); } }
        .field-shake { animation: fieldShake .4s ease; }
        .dark .fi-label { color: #e2e8f0; }
        .dark .fi-pill-input { background: #1e293b; border-color: #334155; color: #e2e8f0; }
        .dark .fi-pill-input::placeholder { color: #64748b; }
        .dark .fi-pill-input:hover:not(:focus):not(.fi-pill-input--error) { border-color: #475569; }
        .dark .fi-pill-input:focus { border-color: #10b981; }
        .dark .fi-pill-input--disabled { background: #0f172a; }
        .dark .fi-pill-input--readonly { background: #0f172a; }
        .dark .fi-pill-icon-left { color: #64748b; }
        .dark .fi-toggle-btn { color: #64748b; }
        .dark .fi-toggle-btn:hover { color: #34d399; }
        .dark .fi-pill-suffix, .dark .fi-float-label { color: #64748b; }
        .dark .fi-pill-input:not(:placeholder-shown) ~ .fi-float-label,
        .dark .fi-pill-input:focus ~ .fi-float-label { color: #10b981; }
        .dark .fi-error { color: #f87171; }
        .dark .fi-hint-sub { color: #94a3b8; }
    </style>
</div>
