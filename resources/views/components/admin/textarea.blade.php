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

<div class="ta-field"
    x-data="{
        count: {{ $charCount }},
        max: {{ $maxlength ?: 9999 }},
        focused: false,
        init() {
            this.$refs.ta.style.resize = 'none';
            this.$refs.ta.style.overflowY = 'auto';
            this.$refs.ta.style.scrollbarWidth = 'none';
        },
        updateCount() {
            this.count = this.$refs.ta.value.length;
        }
    }"
    x-init="init()"
>
    @if($label)
        <div class="ta-field-head">
            <label for="{{ $id }}" class="ta-field-label">
                @if($icon)
                    <span class="ta-icon-badge">
                        <x-admin.icon :name="$icon" :size="15" />
                    </span>
                @endif
                {{ $label }}
                @if($required)<span class="ta-required">*</span>@endif
            </label>
        </div>
    @endif

    <div class="ta-shell {{ $hasError ? 'ta-shell--error' : '' }}" :class="{ 'ta-shell--focus': focused }">
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $maxlength ? 'maxlength=' . $maxlength : '' }}
            x-ref="ta"
            x-on:focus="focused = true"
            x-on:blur="focused = false"
            x-on:input="{{ $maxlength ? 'updateCount()' : '' }}"
            {{ $attributes->except(['id', 'class', 'icon']) }}
            class="ta-input {{ $disabled ? 'ta-input--disabled' : '' }} {{ $readonly ? 'ta-input--readonly' : '' }}"
            style="min-height: {{ $minHeight ?: ($rows <= 2 ? '78px' : '130px') }};"
        >{{ $currentValue }}</textarea>

        @if($hint || ($maxlength && $showCharCount))
            <div class="ta-footer">
                @if($hint)
                    <span class="ta-hint">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 16v-5M12 8h.01"/></svg>
                        {{ $hint }}
                    </span>
                @else
                    <span></span>
                @endif

                @if($maxlength && $showCharCount)
                    <span class="ta-counter"
                        :class="count >= max ? 'ta-counter--full' : (count >= max * 0.85 ? 'ta-counter--warn' : '')">
                        <span x-text="count"></span>/<span x-text="max"></span>
                    </span>
                @endif
            </div>
        @endif
    </div>

    @if($hasError)
        <p class="ta-error">
            <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            {{ $errorMessage }}
        </p>
    @endif

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap');
        .ta-field { position: relative; font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif; margin-bottom: 4px; }
        .ta-field-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
        .ta-field-label { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #334155; cursor: pointer; letter-spacing: 0.01em; }
        .ta-required { color: #10b981; font-size: 13px; font-weight: 400; margin-left: 2px; }
        .ta-icon-badge { width: 26px; height: 26px; border-radius: 8px; background: #ecfdf5; display: flex; align-items: center; justify-content: center; color: #059669; flex-shrink: 0; }
        .ta-icon-badge svg { width: 15px; height: 15px; }
        .ta-shell { position: relative; border-radius: 12px; background: #ffffff; border: 1.5px solid #e2e8f0; transition: border-color .2s ease, box-shadow .2s ease, background .2s ease; overflow: hidden; }
        .ta-shell::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: linear-gradient(180deg, #10b981, #059669); transform: scaleY(0); transform-origin: top; transition: transform .22s ease; z-index: 1; }
        .ta-shell:hover:not(.ta-shell--focus):not(.ta-shell--error) { border-color: #cbd5e1; }
        .ta-shell--focus, .ta-shell.ta-shell--focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); background: #fff; }
        .ta-shell--focus::before { transform: scaleY(1); }
        .ta-shell--error { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.08); }
        .ta-input { width: 100%; display: block; border: none; outline: none; resize: none; background: transparent; font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif; font-size: 14px; line-height: 1.55; color: #1e293b; padding: 14px 16px 34px 18px; }
        .ta-input::placeholder { color: #94a3b8; font-weight: 400; font-size: 13.5px; }
        .ta-input--disabled { cursor: not-allowed; opacity: 0.5; }
        .ta-input--readonly { cursor: not-allowed; }
        .ta-footer { position: absolute; left: 16px; right: 14px; bottom: 10px; display: flex; align-items: center; justify-content: space-between; pointer-events: none; }
        .ta-hint { font-size: 12px; color: #94a3b8; display: flex; align-items: center; gap: 5px; pointer-events: auto; }
        .ta-hint svg { width: 12px; height: 12px; flex-shrink: 0; }
        .ta-counter { font-size: 12px; font-weight: 500; color: #94a3b8; background: #f0fdf4; padding: 3px 10px; border-radius: 20px; pointer-events: auto; transition: color .15s ease, background .15s ease; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .ta-counter--warn { color: #d97706; background: #fef3c7; }
        .ta-counter--full { color: #fff; background: #ef4444; }
        .ta-error { display: flex; align-items: center; gap: 5px; margin-top: 6px; font-size: 12px; font-weight: 500; color: #ef4444; font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif; }
        .ta-error svg { width: 14px; height: 14px; flex-shrink: 0; }
        .dark .ta-field-label { color: #e2e8f0; }
        .dark .ta-shell { background: #1e293b; border-color: #334155; }
        .dark .ta-shell:hover:not(.ta-shell--focus):not(.ta-shell--error) { border-color: #475569; }
        .dark .ta-shell--focus { background: #1e293b; }
        .dark .ta-input { color: #e2e8f0; }
        .dark .ta-input::placeholder { color: #64748b; }
        .dark .ta-hint { color: #64748b; }
        .dark .ta-counter { color: #64748b; background: #0f172a; }
        .dark .ta-counter--warn { color: #fbbf24; background: #422006; }
    </style>
</div>