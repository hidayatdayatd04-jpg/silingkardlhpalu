@props([
    'name' => '',
    'label' => '',
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'rows' => 4,
    'maxlength' => null,
    'error' => null,
    'hint' => null,
    'showCharCount' => true,
    'icon' => null,
    'minHeight' => null,
])

@php
    $id = $attributes->get('id', 'textarea-'.Str::random(8));
    $hasError = $error === '' ? false : (bool) ($error || $errors->has($name));
    $errorMessage = $error === '' ? null : ($error ?? $errors->first($name));
    $currentValue = old($name, $value);
    $charCount = mb_strlen((string) $currentValue);
    $providedDescribedBy = trim((string) $attributes->get('aria-describedby', ''));
    $feedbackId = $hasError ? $id.'-error' : ($hint ? $id.'-hint' : null);
    $describedBy = trim($providedDescribedBy.' '.($feedbackId ?? ''));
    $customInput = trim((string) $attributes->get('x-on:input', ''));
    $onInput = 'count = $el.value.length'.($customInput !== '' ? '; '.$customInput : '');
    $controlClasses = 'block w-full resize-y rounded-xl border bg-white px-3.5 py-3 text-sm leading-6 text-slate-950 outline-none transition-[border-color,box-shadow,background-color] duration-150 placeholder:text-slate-400 hover:border-slate-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500 disabled:opacity-75 read-only:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500 dark:hover:border-slate-600 dark:focus:border-brand-400 dark:focus:ring-brand-400/20 dark:disabled:bg-slate-900 dark:read-only:bg-slate-900';
    $controlClasses .= $hasError ? ' border-danger-500 focus:border-danger-600 focus:ring-danger-500/20 dark:border-danger-400 dark:focus:border-danger-400 dark:focus:ring-danger-400/20' : ' border-slate-200';
    $resolvedMinHeight = $minHeight ?: ($rows <= 2 ? '5.5rem' : '8rem');
@endphp

<div class="space-y-1.5" x-data="{ count: {{ $charCount }}, max: {{ $maxlength ?: 0 }} }">
    @if($label)
        <label for="{{ $id }}" class="flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-slate-100">
            @if($icon)
                <span class="grid size-6 place-items-center rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/55 dark:text-brand-300"><x-admin.icon :name="$icon" :size="14" aria-hidden="true" /></span>
            @endif
            <span>{{ $label }}@if($required)<span class="ml-0.5 text-danger-600 dark:text-danger-400" aria-hidden="true">*</span><span class="sr-only"> wajib diisi</span>@endif</span>
        </label>
    @endif

    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        style="min-height: {{ $resolvedMinHeight }};"
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        @if($hasError) aria-invalid="true" @endif
        @if($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        x-on:input="{{ $onInput }}"
        {{ $attributes->except(['id', 'class', 'icon', 'aria-describedby', 'x-on:input'])->merge(['class' => $controlClasses]) }}
    >{{ $currentValue }}</textarea>

    <div class="flex min-h-5 items-start justify-between gap-3">
        @if($hasError)
            <p id="{{ $id }}-error" class="flex items-start gap-1.5 text-xs font-medium leading-5 text-danger-600 dark:text-danger-300" role="alert">
                <x-admin.icon name="alert-circle" :size="15" class="mt-0.5 shrink-0" aria-hidden="true" />
                <span>{{ $errorMessage }}</span>
            </p>
        @elseif($hint)
            <p id="{{ $id }}-hint" class="text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $hint }}</p>
        @else
            <span></span>
        @endif

        @if($maxlength && $showCharCount)
            <span class="shrink-0 text-xs tabular-nums text-slate-500 dark:text-slate-400" x-bind:class="count >= max ? 'text-danger-600 dark:text-danger-300' : (count >= max * .85 ? 'text-warning-700 dark:text-warning-300' : '')">
                <span x-text="count"></span>/<span x-text="max"></span>
            </span>
        @endif
    </div>
</div>
