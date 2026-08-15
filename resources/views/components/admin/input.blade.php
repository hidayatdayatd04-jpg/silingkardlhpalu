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
    $id = $attributes->get('id', 'input-' . Str::random(8));
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
    $currentValue = old($name, $value);
@endphp

<div class="fi-field"
    x-data="{ focused: false }"
>
    @if($label)
        <label for="{{ $id }}" class="fi-label">
            @if($icon)
                <span class="fi-icon-badge">
                    <x-admin.icon :name="$icon" :size="15" />
                </span>
            @endif
            {{ $label }}
            @if($required)<span class="fi-required">*</span>@endif
        </label>
    @endif

    <div class="fi-pill-wrap">
        @if($prefix)
            <div class="fi-pill-prefix">
                <span class="text-sm font-medium text-slate-500">{{ $prefix }}</span>
            </div>
        @endif

        @if($icon && $iconPosition === 'left')
            <span class="fi-pill-icon-left">
                <x-admin.icon :name="$icon" :size="18" class="text-slate-400" />
            </span>
        @endif

        <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $currentValue }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $hasError ? 'aria-invalid="true"' : '' }}
            x-on:focus="focused = true"
            x-on:blur="focused = false"
            {{ $attributes->except(['id', 'class', 'type']) }}
            class="fi-pill-input
                {{ $hasError ? 'fi-pill-input--error' : '' }}
                {{ $disabled ? 'fi-pill-input--disabled' : '' }}
                {{ $readonly ? 'fi-pill-input--readonly' : '' }}
                {{ $icon && $iconPosition === 'left' ? 'fi-pill-input--icon-left' : '' }}
                {{ $suffix ? 'fi-pill-input--suffix' : '' }}"
        >

        @if($icon && $iconPosition === 'right')
            <span class="fi-pill-icon-right">
                <x-admin.icon :name="$icon" :size="18" class="text-slate-400" />
            </span>
        @endif

        @if($suffix)
            <div class="fi-pill-suffix">
                <span class="text-sm font-medium text-slate-500">{{ $suffix }}</span>
            </div>
        @endif
    </div>

    @if($hasError)
        <p class="fi-error">
            <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            {{ $errorMessage }}
        </p>
    @elseif($hint)
        <p class="fi-hint-sub">{{ $hint }}</p>
    @endif

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap');
        .fi-field { position: relative; font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif; }
        .fi-label { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; color: #12201a; margin-bottom: 8px; cursor: pointer; }
        .fi-required { color: #f43f5e; font-size: 14px; font-weight: 400; margin-left: 1px; }
        .fi-icon-badge { width: 26px; height: 26px; border-radius: 8px; background: #e6f5ec; display: flex; align-items: center; justify-content: center; color: #146a44; flex-shrink: 0; }
        .fi-icon-badge svg { width: 15px; height: 15px; }
        .fi-pill-wrap { position: relative; display: flex; align-items: center; }
        .fi-pill-input { width: 100%; height: 48px; border-radius: 9999px; border: 1.5px solid #dfe9e3; background: #fff; padding: 0 20px; font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif; font-size: 13.5px; transition: border-color .18s ease, box-shadow .18s ease, padding .18s ease; outline: none; color: #12201a; }
        .fi-pill-input::placeholder { color: #9fb0a8; font-size: 13.5px; }
        .fi-pill-input:hover:not(:focus):not(.fi-pill-input--error) { border-color: #c3d8cc; }
        .fi-pill-input:focus { border-color: #1ea567; box-shadow: 0 0 0 4px rgba(30, 165, 103, 0.12); }
        .fi-pill-input--error { border-color: #e0533d !important; box-shadow: 0 0 0 4px rgba(224, 83, 61, 0.1) !important; }
        .fi-pill-input--disabled { cursor: not-allowed; opacity: 0.5; background: #f8fafc; }
        .fi-pill-input--readonly { cursor: not-allowed; background: #f8fafc; }
        .fi-pill-input--icon-left { padding-left: 44px; }
        .fi-pill-input--suffix { padding-right: 44px; }
        .fi-pill-icon-left, .fi-pill-icon-right { position: absolute; inset-y: 0; display: flex; align-items: center; pointer-events: none; z-index: 1; }
        .fi-pill-icon-left { left: 14px; }
        .fi-pill-icon-right { right: 14px; }
        .fi-pill-prefix { position: absolute; left: 14px; display: flex; align-items: center; pointer-events: none; z-index: 1; font-size: 13.5px; color: #5b6b63; font-weight: 500; }
        .fi-pill-input--icon-left:not(.fi-pill-input--suffix) { padding-left: 44px; }
        .fi-error { display: flex; align-items: center; gap: 5px; margin-top: 6px; font-size: 11.5px; font-weight: 500; color: #e0533d; font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif; }
        .fi-error svg { width: 13px; height: 13px; flex-shrink: 0; }
        .fi-hint-sub { margin-top: 6px; font-size: 12px; color: #5b6b63; font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif; }
        .dark .fi-label { color: #e2e8f0; }
        .dark .fi-pill-input { background: #1e293b; border-color: #334155; color: #e2e8f0; }
        .dark .fi-pill-input::placeholder { color: #64748b; }
        .dark .fi-pill-input:hover:not(:focus):not(.fi-pill-input--error) { border-color: #475569; }
        .dark .fi-pill-input:focus { border-color: #1ea567; }
        .dark .fi-pill-input--disabled { background: #0f172a; }
        .dark .fi-pill-input--readonly { background: #0f172a; }
        .dark .fi-hint-sub { color: #94a3b8; }
        .dark .fi-icon-badge { background: #1e3a2f; color: #34d399; }
        .dark .fi-pill-prefix { color: #64748b; }
        .dark .fi-pill-suffix { color: #64748b; }
        .fi-pill-suffix { position: absolute; right: 14px; display: flex; align-items: center; pointer-events: none; z-index: 1; font-size: 13.5px; color: #5b6b63; font-weight: 500; }
        .fi-error { display: flex; align-items: center; gap: 5px; margin-top: 6px; font-size: 11.5px; font-weight: 500; color: #e0533d; font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif; }
        .fi-error svg { width: 13px; height: 13px; flex-shrink: 0; }
        .fi-hint-sub { margin-top: 6px; font-size: 12px; color: #5b6b63; font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif; }
        .dark .fi-label { color: #e2e8f0; }
        .dark .fi-pill-input { background: #1e293b; border-color: #334155; color: #e2e8f0; }
        .dark .fi-pill-input::placeholder { color: #64748b; }
        .dark .fi-pill-input:hover:not(:focus):not(.fi-pill-input--error) { border-color: #475569; }
        .dark .fi-pill-input:focus { border-color: #1ea567; }
        .dark .fi-pill-input--disabled { background: #0f172a; }
        .dark .fi-pill-input--readonly { background: #0f172a; }
        .dark .fi-hint-sub { color: #94a3b8; }
        .dark .fi-icon-badge { background: #1e3a2f; color: #34d399; }
        .dark .fi-pill-prefix { color: #64748b; }
        .dark .fi-pill-suffix { color: #64748b; }
    </style>
</div>