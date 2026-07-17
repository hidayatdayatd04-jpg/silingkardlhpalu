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

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-slate-900">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        <!-- Prefix -->
        @if($prefix)
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <span class="text-sm font-medium text-slate-500">{{ $prefix }}</span>
            </div>
        @endif
        
        <!-- Icon (left) -->
        @if($icon && $iconPosition === 'left')
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <x-admin.icon :name="$icon" :size="18" class="text-slate-400" />
            </div>
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
            class="block w-full rounded-lg border px-3 py-2.5 text-sm transition placeholder:text-slate-400 focus:outline-none focus:ring-2 {{ $hasError ? 'border-rose-300 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-rose-100' : 'border-slate-300 bg-white text-slate-900 focus:border-emerald-500 focus:ring-emerald-100' }} {{ $disabled ? 'cursor-not-allowed opacity-60' : '' }} {{ $readonly ? 'bg-slate-50' : '' }} {{ $icon && $iconPosition === 'left' || $prefix ? 'pl-10' : '' }} {{ $icon && $iconPosition === 'right' || $suffix ? 'pr-10' : '' }}"
            {{ $attributes->except(['id', 'class', 'type']) }}
        >
        
        <!-- Icon (right) -->
        @if($icon && $iconPosition === 'right')
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <x-admin.icon :name="$icon" :size="18" class="text-slate-400" />
            </div>
        @endif
        
        <!-- Suffix -->
        @if($suffix)
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <span class="text-sm font-medium text-slate-500">{{ $suffix }}</span>
            </div>
        @endif
    </div>
    
    <!-- Hint text -->
    @if($hint)
        <p class="text-xs text-slate-600">{{ $hint }}</p>
    @endif
    
    <!-- Error message -->
    @if($hasError)
        <p class="flex items-center gap-1 text-xs font-medium text-rose-600">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ $errorMessage }}
        </p>
    @endif
</div>
