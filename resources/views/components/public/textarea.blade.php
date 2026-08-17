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
    'minHeight' => null,
])

@php
    $id = $attributes->get('id', 'pub-textarea-' . Str::random(6));
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
    {{-- Label --}}
    @if($label)
        <div class="ta-field-head">
            <label for="{{ $id }}" class="ta-field-label">
                @if($attributes->get('icon'))
                    <span class="ta-icon-badge">{!! $attributes->get('icon') !!}</span>
                @endif
                {{ $label }}
                @if($required)<span class="ta-required">*</span>@endif
            </label>
        </div>
    @endif

    {{-- Textarea --}}
    <div class="ta-shell
        {{ $hasError ? 'ta-shell--error' : '' }}"
        :class="{ 'ta-shell--focus': focused }"
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
            x-ref="ta"
            x-on:focus="focused = true"
            x-on:blur="focused = false"
            x-on:input="{{ $maxlength ? 'updateCount()' : '' }}"
            {{ $attributes->except(['id', 'class', 'icon']) }}
            class="ta-input
                {{ $disabled ? 'ta-input--disabled' : '' }}
                {{ $readonly ? 'ta-input--readonly' : '' }}"
            style="min-height: {{ $minHeight ?: ($rows <= 2 ? '78px' : '130px') }};"
        >{{ $currentValue }}</textarea>

        {{-- Footer --}}
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
                        <span x-text="count">{{ $charCount }}</span>/<span x-text="max">{{ $maxlength }}</span>
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- Error --}}
    @if($hasError)
        <p class="ta-error">
            <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            {{ $errorMessage }}
        </p>
    @endif

    <style>
        /* ── Outfit font ── */

        .ta-field {
            position: relative;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        /* ── Label ── */
        .ta-field-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .ta-field-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #12201a;
            cursor: pointer;
        }

        .ta-required {
            color: #178a53;
            font-size: 12px;
            font-weight: 400;
            margin-left: 1px;
        }

        .ta-icon-badge {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: #e6f5ec;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #146a44;
            flex-shrink: 0;
        }

        .ta-icon-badge svg {
            width: 15px;
            height: 15px;
        }

        /* ── Shell ── */
        .ta-shell {
            position: relative;
            border-radius: 16px;
            background: #ffffff;
            border: 1.5px solid #dfe9e3;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
            overflow: hidden;
        }

        .ta-shell::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, #1ea567, #146a44);
            transform: scaleY(0);
            transform-origin: top;
            transition: transform .22s ease;
            z-index: 1;
        }

        .ta-shell:hover:not(.ta-shell--focus):not(.ta-shell--error) {
            border-color: #c3d8cc;
        }

        .ta-shell--focus,
        .ta-shell.ta-shell--focus {
            border-color: #1ea567;
            box-shadow: 0 0 0 4px rgba(30, 165, 103, 0.12);
            background: #fff;
        }

        .ta-shell--focus::before {
            transform: scaleY(1);
        }

        .ta-shell--error {
            border-color: #e0533d;
            box-shadow: 0 0 0 4px rgba(224, 83, 61, 0.1);
        }

        /* ── Textarea ── */
        .ta-input {
            width: 100%;
            display: block;
            border: none;
            outline: none;
            resize: none;
            background: transparent;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            font-size: 13.5px;
            line-height: 1.55;
            color: #12201a;
            padding: 14px 16px 34px 18px;
        }

        .ta-input::placeholder {
            color: #5f7268;
            font-weight: 400;
            font-size: 13.5px;
        }

        .ta-input--disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }

        .ta-input--readonly {
            cursor: not-allowed;
        }

        /* ── Footer (absolute inside shell) ── */
        .ta-footer {
            position: absolute;
            left: 16px;
            right: 14px;
            bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            pointer-events: none;
        }

        .ta-hint {
            font-size: 11.5px;
            color: #5f7268;
            display: flex;
            align-items: center;
            gap: 5px;
            pointer-events: auto;
        }

        .ta-hint svg {
            width: 12px;
            height: 12px;
            flex-shrink: 0;
        }

        /* ── Counter (pill) ── */
        .ta-counter {
            font-size: 11.5px;
            font-weight: 500;
            color: #5f7268;
            background: #f4faf6;
            padding: 3px 9px;
            border-radius: 20px;
            pointer-events: auto;
            transition: color .15s ease, background .15s ease;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .ta-counter--warn {
            color: #a8651f;
            background: #fdf1e2;
        }

        .ta-counter--full {
            color: #fff;
            background: #e0533d;
        }

        /* ── Error ── */
        .ta-error {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 6px;
            font-size: 11.5px;
            font-weight: 500;
            color: #e0533d;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        .ta-error svg {
            width: 13px;
            height: 13px;
            flex-shrink: 0;
        }

        /* ── Dark mode ── */
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
