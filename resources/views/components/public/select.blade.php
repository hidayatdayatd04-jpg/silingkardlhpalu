@props([
    'name' => '',
    'label' => '',
    'placeholder' => 'Pilih opsi...',
    'options' => [],
    'selected' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
    'searchable' => false,
])

@php
    $id = $attributes->get('id', 'pub-select-' . Str::random(8));
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
    $normalizedOptions = collect($options)
        ->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])
        ->values();
@endphp

<div
    {{ $attributes->whereStartsWith('wire:model') }}
    {{ $attributes->whereStartsWith('wire:key') }}
    x-modelable="selected"
    class="fi-field"
    x-data="{
        open: false,
        search: '',
        selected: @js((string) old($name, $selected)),
        selectedLabel: '',
        options: @js($normalizedOptions),
        searchable: {{ $searchable ? 'true' : 'false' }},

        get filteredOptions() {
            if (!this.searchable || !this.search) return this.options;
            return this.options.filter((option) =>
                option.label.toLowerCase().includes(this.search.toLowerCase())
            );
        },

        selectOption(value, label) {
            this.selected = value;
            this.selectedLabel = label;
            this.open = false;
            this.search = '';
            this.$refs.hiddenInput.value = value;
            this.$refs.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
            this.$nextTick(() => {
                if (typeof @this !== 'undefined') {
                    @this.set('{{ $name }}', value);
                }
            });
        },

        init() {
            this.$watch('selected', (value) => {
                if (value === '' && !this._initialized) return;
                const selectedOption = this.options.find((option) => option.value === String(value));
                this.selectedLabel = selectedOption ? selectedOption.label : '';
                this.$refs.hiddenInput.value = value;
                this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                this.$refs.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                this.$nextTick(() => {
                    if (typeof @this !== 'undefined') {
                        @this.set('{{ $name }}', value);
                    }
                });
            });
            this._initialized = true;
            const selectedOption = this.options.find((option) => option.value === String(this.selected));
            this.selectedLabel = selectedOption ? selectedOption.label : '';

            document.addEventListener('click', (e) => {
                if (!this.$el.contains(e.target)) this.open = false;
            });
        }
    }"
    x-on:keydown.escape.window="open = false"
>
    {{-- Label --}}
    @if($label)
        <label for="{{ $id }}" class="fi-label">
            {{ $label }}
            @if($required)<span class="fi-required">*</span>@endif
        </label>
    @endif

    {{-- Select Shell --}}
    <div class="fi-select-shell" :class="{ 'fi-select-shell--open': open }">
        <input
            type="hidden"
            id="{{ $id }}"
            name="{{ $name }}"
            x-ref="hiddenInput"
            :value="selected"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
        >

        {{-- Trigger --}}
        <button
            type="button"
            x-ref="trigger"
            x-on:click="open = !open"
            :disabled="{{ $disabled ? 'true' : 'false' }}"
            aria-label="{{ $label ?: $placeholder }}"
            class="fi-select-trigger {{ $hasError ? 'fi-select-trigger--error' : '' }} {{ $disabled ? 'fi-select-trigger--disabled' : '' }}"
        >
            <span x-show="!selected" class="fi-select-placeholder">{{ $placeholder }}</span>
            <span x-show="selected" x-text="selectedLabel" class="fi-select-value"></span>
            <svg class="fi-select-chevron" :class="{ 'fi-select-chevron--open': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 9l6 6 6-6"/>
            </svg>
        </button>

        {{-- Panel --}}
        <div
            x-show="open"
            x-transition:enter="fi-select-enter"
            x-transition:enter-start="fi-select-enter-start"
            x-transition:enter-end="fi-select-enter-end"
            x-transition:leave="fi-select-leave"
            x-transition:leave-start="fi-select-leave-start"
            x-transition:leave-end="fi-select-leave-end"
            x-on:click.outside="open = false"
            class="fi-select-panel"
        >
            {{-- Search --}}
            @if($searchable)
                <div class="fi-select-search-wrap">
                    <input
                        type="text"
                        x-model="search"
                        placeholder="Cari..."
                        class="fi-select-search"
                        x-on:click.stop
                    >
                </div>
            @endif

            {{-- Empty option --}}
            <div
                x-on:click="selectOption('', '')"
                class="fi-select-option"
                :class="{ 'fi-select-option--active': selected === '' }"
            >
                <span>{{ $placeholder }}</span>
            </div>

            {{-- Options --}}
            <div class="fi-select-options-scroll">
                <template x-for="option in filteredOptions" :key="option.value">
                    <div
                        x-on:click="selectOption(option.value, option.label)"
                        class="fi-select-option"
                        :class="{ 'fi-select-option--active': selected === option.value }"
                    >
                        <span x-text="option.label" class="fi-select-option-text"></span>
                        <span x-show="selected === option.value" class="fi-select-check">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                        </span>
                    </div>
                </template>
            </div>

            {{-- Empty search --}}
            <div x-show="searchable && filteredOptions.length === 0" class="fi-select-empty">
                Tidak ada hasil ditemukan
            </div>
        </div>
    </div>

    {{-- Error / Hint --}}
    @if($hasError)
        <p class="fi-error">
            <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            {{ $errorMessage }}
        </p>
    @elseif($hint)
        <p class="fi-hint-sub">{{ $hint }}</p>
    @endif

    <style>

        .fi-field {
            position: relative;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        .fi-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #12201a;
            margin-bottom: 8px;
            cursor: pointer;
        }

        .fi-required {
            color: #f43f5e;
            font-size: 14px;
            font-weight: 400;
            margin-left: 1px;
        }

        /* ── Select Shell ── */
        .fi-select-shell {
            position: relative;
        }

        .fi-select-trigger {
            width: 100%;
            height: 48px;
            border-radius: 9999px;
            border: 1.5px solid #dfe9e3;
            background: #fff;
            padding: 0 20px;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            font-size: 13.5px;
            font-weight: 500;
            color: #12201a;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: border-color .18s ease, box-shadow .18s ease;
            text-align: left;
        }

        .fi-select-trigger:hover:not(.fi-select-trigger--error):not(.fi-select-trigger--disabled) {
            border-color: #c3d8cc;
        }

        .fi-select-shell--open .fi-select-trigger,
        .fi-select-trigger:focus {
            border-color: #1ea567;
            box-shadow: 0 0 0 4px rgba(30, 165, 103, 0.12);
        }

        .fi-select-trigger--error {
            border-color: #e0533d;
            box-shadow: 0 0 0 4px rgba(224, 83, 61, 0.1);
        }

        .fi-select-trigger--disabled {
            cursor: not-allowed;
            opacity: 0.5;
            background: #f8fafc;
        }

        .fi-select-placeholder {
            color: #5f7268;
            font-weight: 400;
        }

        .fi-select-value {
            font-weight: 500;
            color: #12201a;
        }

        .fi-select-chevron {
            width: 16px;
            height: 16px;
            color: #5b6b63;
            transition: transform .18s ease;
            flex-shrink: 0;
        }

        .fi-select-chevron--open {
            transform: rotate(180deg);
        }

        /* ── Panel ── */
        .fi-select-panel {
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 8px);
            background: #fff;
            border: 1px solid #dfe9e3;
            border-radius: 18px;
            box-shadow: 0 12px 32px -10px rgba(13, 43, 29, 0.18);
            padding: 8px;
            z-index: 50;
            max-height: 360px;
            overflow: visible;
            overscroll-behavior: contain;
        }

        /* ── Transition classes ── */
        .fi-select-enter { transition: opacity .15s ease, transform .15s ease; }
        .fi-select-enter-start { opacity: 0; transform: translateY(-4px) scale(0.98); }
        .fi-select-enter-end { opacity: 1; transform: translateY(0) scale(1); }
        .fi-select-leave { transition: opacity .1s ease, transform .1s ease; }
        .fi-select-leave-start { opacity: 1; transform: translateY(0) scale(1); }
        .fi-select-leave-end { opacity: 0; transform: translateY(-4px) scale(0.98); }

        /* ── Search ── */
        .fi-select-search-wrap {
            padding: 4px 4px 6px;
        }

        .fi-select-search {
            width: 100%;
            height: 36px;
            border-radius: 10px;
            border: 1px solid #dfe9e3;
            background: #f4faf6;
            padding: 0 12px;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            font-size: 12.5px;
            color: #12201a;
            outline: none;
            transition: border-color .15s ease, background .15s ease;
        }

        .fi-select-search::placeholder { color: #5f7268; }
        .fi-select-search:focus { border-color: #1ea567; background: #fff; }

        /* ── Options ── */
        .fi-select-options-scroll {
            max-height: 280px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #dfe9e3 transparent;
            overscroll-behavior: contain;
        }

        .fi-select-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 14px;
            border-radius: 12px;
            font-size: 13.5px;
            color: #12201a;
            cursor: pointer;
            transition: background .12s ease;
        }

        .fi-select-option:hover {
            background: #f4faf6;
        }

        .fi-select-option--active {
            background: #178a53;
            color: #fff;
            font-weight: 500;
        }

        .fi-select-option--active:hover {
            background: #146a44;
        }

        .fi-select-option-text {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .fi-select-check {
            width: 18px;
            height: 18px;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-left: 8px;
        }

        .fi-select-check svg {
            width: 11px;
            height: 11px;
        }

        .fi-select-empty {
            padding: 24px 16px;
            text-align: center;
            font-size: 13px;
            color: #5f7268;
        }

        /* ── Error / Hint ── */
        .fi-error {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 6px;
            font-size: 11.5px;
            font-weight: 500;
            color: #e0533d;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        .fi-error svg {
            width: 13px;
            height: 13px;
            flex-shrink: 0;
        }

        .fi-hint-sub {
            margin-top: 6px;
            font-size: 12px;
            color: #5b6b63;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        /* ── Dark mode ── */
        .dark .fi-label { color: #e2e8f0; }
        .dark .fi-select-trigger {
            background: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
        }
        .dark .fi-select-trigger:hover:not(.fi-select-trigger--error) { border-color: #475569; }
        .dark .fi-select-trigger--disabled { background: #0f172a; }
        .dark .fi-select-placeholder { color: #64748b; }
        .dark .fi-select-value { color: #e2e8f0; }
        .dark .fi-select-panel { background: #1e293b; border-color: #334155; }
        .dark .fi-select-search { background: #0f172a; border-color: #334155; color: #e2e8f0; }
        .dark .fi-select-search::placeholder { color: #64748b; }
        .dark .fi-select-search:focus { border-color: #1ea567; background: #1e293b; }
        .dark .fi-select-option { color: #e2e8f0; }
        .dark .fi-select-option:hover { background: #334155; }
        .dark .fi-select-option--active { background: #178a53; }
        .dark .fi-select-option--active:hover { background: #146a44; }
        .dark .fi-hint-sub { color: #94a3b8; }
    </style>
</div>
