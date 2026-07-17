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
    $id = $attributes->get('id', 'textarea-' . Str::random(8));
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
    $currentValue = old($name, $value);
    $charCount = strlen($currentValue);
@endphp

<div class="space-y-2.5"
    x-data="{
        count: {{ $charCount }},
        max: {{ $maxlength ?: 9999 }},
        updateCount() {
            this.count = this.$refs.textarea.value.length;
        }
    }"
>
    {{-- Label --}}
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-slate-800 dark:text-slate-200">
            {{ $label }}@if($required)<span class="text-danger-500 ml-0.5">*</span>@endif
        </label>
    @endif

    {{-- Textarea --}}
    <div class="relative rounded-xl border bg-white dark:bg-slate-900 transition-all duration-200
        {{ $hasError
            ? 'border-danger-300 ring-4 ring-danger-100'
            : 'border-slate-300 dark:border-slate-700 hover:border-slate-400 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-100'
        }}
        shadow-sm"
    >
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $maxlength ? 'maxlength=' . $maxlength : '' }}
            x-ref="textarea"
            x-on:input="{{ $maxlength ? 'updateCount()' : '' }}"
            {{ $attributes->except(['id', 'class']) }}
            class="pub-textarea w-full border-none outline-none bg-transparent text-sm text-slate-800 dark:text-slate-200 placeholder:text-slate-400 dark:placeholder:text-slate-500
                {{ $disabled ? 'cursor-not-allowed opacity-60' : '' }}
                {{ $readonly ? 'cursor-not-allowed bg-slate-50' : '' }}"
            style="padding: 0.75rem 1rem; min-height: {{ $minHeight ?: ($rows <= 2 ? '80px' : '120px') }}; line-height: 1.6; resize: none; overflow-y: auto;"
        >{{ $currentValue }}</textarea>

        {{-- Footer --}}
        @if($hint || ($maxlength && $showCharCount))
            <div class="flex items-center justify-between px-4 pb-2.5">
                @if($hint)
                    <span class="flex items-center gap-1.5 text-xs text-slate-400">
                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 16v-5M12 8h.01"/>
                        </svg>
                        {{ $hint }}
                    </span>
                @else
                    <span></span>
                @endif

                @if($maxlength && $showCharCount)
                    <span class="text-xs font-medium text-slate-400"
                        :class="count >= max ? 'text-danger-500' : (count >= max * 0.85 ? 'text-warning-600' : '')">
                        <span x-text="count"></span>/<span x-text="max"></span>
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- Error --}}
    @if($hasError)
        <p class="flex items-center gap-1.5 text-xs font-semibold text-danger-600">
            <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ $errorMessage }}
        </p>
    @endif
</div>
