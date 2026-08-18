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
    $id = $attributes->get('id', 'fi-'.Str::random(6));
    $hasError = $error === '' ? false : (bool) ($error || $errors->has($name));
    $errorMessage = $error === '' ? null : ($error ?? $errors->first($name));
    $currentValue = old($name, $value);
    $hasIcon = $icon !== null;
    $hasRightAdornment = $suffix !== null || $toggleable;
    $providedDescribedBy = trim((string) $attributes->get('aria-describedby', ''));
    $feedbackId = $hasError ? $id.'-error' : ($hint ? $id.'-hint' : null);
    $describedBy = trim($providedDescribedBy.' '.($feedbackId ?? ''));
    $controlClasses = 'peer block h-11 w-full rounded-xl border bg-white px-3.5 text-sm text-slate-950 outline-none transition-[border-color,box-shadow,background-color] duration-150 placeholder:text-slate-400 hover:border-slate-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500 disabled:opacity-75 read-only:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500 dark:hover:border-slate-600 dark:focus:border-brand-400 dark:focus:ring-brand-400/20 dark:disabled:bg-slate-900 dark:read-only:bg-slate-900';
    $controlClasses .= $hasError ? ' border-danger-500 focus:border-danger-600 focus:ring-danger-500/20 dark:border-danger-400 dark:focus:border-danger-400 dark:focus:ring-danger-400/20' : ' border-slate-200';
    $controlClasses .= $hasIcon ? ' pl-10' : '';
    $controlClasses .= $hasRightAdornment ? ' pr-12' : '';
@endphp

<div class="space-y-1.5" x-data="{ show: false }">
    @if($label && !$floating)
        <label for="{{ $id }}" class="block text-sm font-semibold text-slate-800 dark:text-slate-100">
            {{ $label }}@if($required)<span class="ml-0.5 text-danger-600 dark:text-danger-400" aria-hidden="true">*</span><span class="sr-only"> wajib diisi</span>@endif
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-10 items-center justify-center text-slate-400 dark:text-slate-500">
                <x-admin.icon :name="$icon" :size="17" aria-hidden="true" />
            </span>
        @endif

        <input
            @if($toggleable) x-bind:type="show ? 'text' : 'password'" @else type="{{ $type }}" @endif
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
            @if($hasError) aria-invalid="true" @endif
            @if($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->except(['id', 'class', 'type', 'value', 'aria-describedby'])->merge(['class' => $controlClasses]) }}
        >

        @if($toggleable)
            <button
                type="button"
                x-on:click="show = !show"
                x-bind:aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
                class="absolute inset-y-0 right-0 z-10 grid w-11 place-items-center rounded-r-xl text-slate-400 outline-none transition-colors duration-150 hover:text-brand-700 focus-visible:text-brand-700 dark:text-slate-500 dark:hover:text-brand-300 dark:focus-visible:text-brand-300"
            >
                <x-admin.icon name="eye" :size="18" x-show="!show" aria-hidden="true" />
                <x-admin.icon name="eye-off" :size="18" x-show="show" x-cloak aria-hidden="true" />
            </button>
        @elseif($suffix !== null)
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-sm font-medium text-slate-500 dark:text-slate-400">{{ $suffix }}</span>
        @endif

        @if($floating && $label)
            <label
                for="{{ $id }}"
                class="pointer-events-none absolute top-1/2 z-10 -translate-y-1/2 bg-white px-1 text-sm text-slate-500 transition-[color,top,transform,font-size] duration-150 peer-focus:top-0 peer-focus:-translate-y-1/2 peer-focus:text-xs peer-focus:text-brand-700 peer-[:not(:placeholder-shown)]:top-0 peer-[:not(:placeholder-shown)]:-translate-y-1/2 peer-[:not(:placeholder-shown)]:text-xs {{ $hasIcon ? 'left-9' : 'left-3' }} {{ $hasError ? 'text-danger-600 peer-focus:text-danger-600' : '' }} dark:bg-slate-950 dark:text-slate-400 dark:peer-focus:text-brand-300"
            >
                {{ $label }}@if($required)<span class="text-danger-600 dark:text-danger-400" aria-hidden="true">*</span>@endif
            </label>
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
