@props([
    'name' => '',
    'type' => 'date',            // 'date' | 'datetime-local'
    'label' => '',
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'hint' => null,
    'icon' => null,              // default: clock (datetime-local) / calendar (date)
    'min' => null,
    'max' => null,
    'step' => null,
])

@php
    $id = $attributes->get('id', 'datefield-' . Str::random(6));
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
    $currentValue = (string) old($name, $value);
    $resolvedIcon = $icon ?? ($type === 'datetime-local' ? 'clock' : 'calendar');
    $isDatePicker = $type === 'date';
@endphp

@if($isDatePicker)
{{-- ═══════════════════════════════════════════════════════════
     Date picker custom (Alpine) — kalender berbahasa Indonesia
     ═══════════════════════════════════════════════════════════ --}}
<div class="df-field"
     x-data="datePicker(@js($currentValue), @js($min), @js($max), {{ $hasError ? 'true' : 'false' }}, {{ ($disabled || $readonly) ? 'true' : 'false' }})"
     x-on:keydown.escape.window="open = false">

    @if($label)
        <label for="{{ $id }}" class="df-label" x-on:click.prevent="toggle()">
            {{ $label }}@if($required)<span class="df-required">*</span>@endif
        </label>
    @endif

    <div class="relative" x-ref="field">
        {{-- Input hidden penyimpan nilai Y-m-d (tetap dikirim saat submit) --}}
        <input
            type="hidden"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $currentValue }}"
            x-model="value"
            x-ref="input"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->except(['id', 'class']) }}
        >

        {{-- Trigger --}}
        <button
            type="button"
            x-on:click="toggle()"
            @if($disabled || $readonly) disabled @endif
            class="df-trigger {{ $hasError ? 'df-trigger--error' : '' }}"
            :class="{ 'df-trigger--open': open }"
            aria-haspopup="dialog"
            :aria-expanded="open ? 'true' : 'false'"
        >
            <span class="df-trigger-icon">
                <x-admin.icon :name="$resolvedIcon" :size="17" />
            </span>
            <span class="df-trigger-value" x-show="value" x-text="displayLabel" x-cloak></span>
            <span class="df-trigger-placeholder" x-show="!value">{{ $placeholder ?: 'Pilih tanggal…' }}</span>
            <span class="df-trigger-chevron">
                <x-admin.icon name="chevron-down" :size="16" />
            </span>
        </button>

        {{-- Popover kalender --}}
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1 scale-[0.98]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            x-on:click.outside="open = false"
            class="df-popover"
            role="dialog"
            aria-label="Pilih tanggal"
        >
            <div class="df-pop-header">
                <div class="flex items-center gap-0.5">
                    <button type="button" class="df-nav-btn" x-on:click="prevYear()" title="Tahun sebelumnya">
                        <span class="flex -space-x-1.5">
                            <x-admin.icon name="chevron-left" :size="13" />
                            <x-admin.icon name="chevron-left" :size="13" />
                        </span>
                    </button>
                    <button type="button" class="df-nav-btn" x-on:click="prevMonth()" title="Bulan sebelumnya">
                        <x-admin.icon name="chevron-left" :size="15" />
                    </button>
                </div>

                <div class="df-pop-title" x-text="monthLabel"></div>

                <div class="flex items-center gap-0.5">
                    <button type="button" class="df-nav-btn" x-on:click="nextMonth()" title="Bulan berikutnya">
                        <x-admin.icon name="chevron-right" :size="15" />
                    </button>
                    <button type="button" class="df-nav-btn" x-on:click="nextYear()" title="Tahun berikutnya">
                        <span class="flex -space-x-1.5">
                            <x-admin.icon name="chevron-right" :size="13" />
                            <x-admin.icon name="chevron-right" :size="13" />
                        </span>
                    </button>
                </div>
            </div>

            <div class="df-weekdays">
                <template x-for="d in dayNames" :key="d">
                    <span x-text="d"></span>
                </template>
            </div>

            <div class="df-days">
                <template x-for="cell in weeks" :key="cell.iso">
                    <button
                        type="button"
                        class="df-day"
                        :class="{
                            'df-day--outside': !cell.inMonth,
                            'df-day--today': cell.isToday && !cell.isSelected,
                            'df-day--selected': cell.isSelected,
                            'df-day--disabled': cell.isDisabled
                        }"
                        :disabled="cell.isDisabled"
                        x-on:click="select(cell.iso)"
                        x-text="cell.day"
                    ></button>
                </template>
            </div>

            <div class="df-pop-footer">
                <button type="button" class="df-today-btn" x-on:click="selectToday()">
                    <x-admin.icon name="calendar" :size="14" />
                    Hari Ini
                </button>
                <button type="button" class="df-clear-btn" x-show="value" x-cloak x-on:click="clearValue()">
                    Hapus
                </button>
            </div>
        </div>
    </div>

    @if($hasError)
        <p id="{{ $id }}-error" class="df-error">
            <x-admin.icon name="alert-circle" :size="14" />
            {{ $errorMessage }}
        </p>
    @elseif($hint)
        <p class="df-hint">{{ $hint }}</p>
    @endif
</div>

@else
{{-- ═══════════════════════════════════════════════════════════
     datetime-local — tetap native, styling disamakan
     ═══════════════════════════════════════════════════════════ --}}
<div class="df-field"
     x-data="{ shake: {{ $hasError ? 'true' : 'false' }} }"
     x-init="if (shake) { $refs.field.classList.add('field-shake'); setTimeout(() => $refs.field.classList.remove('field-shake'), 500) }">

    @if($label)
        <label for="{{ $id }}" class="df-label">
            {{ $label }}@if($required)<span class="df-required">*</span>@endif
        </label>
    @endif

    <div class="relative" x-ref="field">
        <span class="df-native-icon">
            <x-admin.icon :name="$resolvedIcon" :size="18" />
        </span>
        <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $currentValue }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            @if($min !== null) min="{{ $min }}" @endif
            @if($max !== null) max="{{ $max }}" @endif
            @if($step !== null) step="{{ $step }}" @endif
            @if($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->except(['id', 'class', 'type', 'value']) }}
            class="df-native-input {{ $hasError ? 'df-native-input--error' : '' }}"
        >
    </div>

    @if($hasError)
        <p id="{{ $id }}-error" class="df-error">
            <x-admin.icon name="alert-circle" :size="14" />
            {{ $errorMessage }}
        </p>
    @elseif($hint)
        <p class="df-hint">{{ $hint }}</p>
    @endif
</div>
@endif

<style>

    .df-field { position: relative; font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif; }
    .df-label { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; color: #12201a; margin-bottom: 8px; cursor: pointer; }
    .df-required { color: #f43f5e; font-size: 14px; margin-left: 1px; }

    /* ── Trigger ── */
    .df-trigger { width: 100%; height: 48px; border-radius: 14px; border: 1.5px solid #dfe9e3; background: #fff; padding: 0 14px; display: flex; align-items: center; gap: 10px; font-family: inherit; font-size: 13.5px; font-weight: 500; color: #12201a; cursor: pointer; transition: border-color .18s ease, box-shadow .18s ease; text-align: left; }
    .df-trigger:hover:not(:disabled):not(.df-trigger--error) { border-color: #c3d8cc; }
    .df-trigger--open, .df-trigger:focus { border-color: #1ea567; box-shadow: 0 0 0 4px rgba(30, 165, 103, 0.12); outline: none; }
    .df-trigger--error { border-color: #e0533d !important; box-shadow: 0 0 0 4px rgba(224, 83, 61, 0.1) !important; }
    .df-trigger:disabled { cursor: not-allowed; opacity: 0.55; background: #f8fafc; }
    .df-trigger-icon { display: grid; place-items: center; width: 30px; height: 30px; border-radius: 9px; background: #ecfdf3; color: #1ea567; flex-shrink: 0; }
    .df-trigger-value { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .df-trigger-placeholder { flex: 1; color: #5f7268; font-weight: 400; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .df-trigger-chevron { color: #5b6b63; transition: transform .18s ease; flex-shrink: 0; display: flex; }
    .df-trigger--open .df-trigger-chevron { transform: rotate(180deg); }

    /* ── Popover kalender ── */
    .df-popover { position: absolute; left: 0; top: calc(100% + 8px); z-index: 60; width: 320px; max-width: calc(100vw - 2rem); background: #fff; border: 1px solid #e3ede6; border-radius: 18px; box-shadow: 0 16px 40px -12px rgba(13, 43, 29, 0.22); padding: 14px; }
    .df-pop-header { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 10px; }
    .df-pop-title { font-size: 14px; font-weight: 700; color: #12201a; letter-spacing: 0.01em; }
    .df-nav-btn { display: grid; place-items: center; width: 30px; height: 30px; border-radius: 9px; border: none; background: transparent; color: #5b6b63; cursor: pointer; transition: background .15s ease, color .15s ease; }
    .df-nav-btn:hover { background: #f1f8f4; color: #1ea567; }
    .df-weekdays { display: grid; grid-template-columns: repeat(7, 1fr); margin-bottom: 4px; }
    .df-weekdays span { text-align: center; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #5f7268; padding: 4px 0; }
    .df-days { display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px; }
    .df-day { display: grid; place-items: center; height: 36px; border-radius: 10px; border: none; background: transparent; font-family: inherit; font-size: 13px; font-weight: 500; color: #1e293b; cursor: pointer; transition: background .12s ease, color .12s ease, box-shadow .12s ease; }
    .df-day:hover:not(.df-day--selected):not(.df-day--disabled) { background: #f1f8f4; }
    .df-day--outside { color: #c7d2cc; }
    .df-day--today { color: #1ea567; font-weight: 700; box-shadow: inset 0 0 0 1.5px rgba(30, 165, 103, 0.45); }
    .df-day--selected { background: linear-gradient(135deg, #10b981, #0d9463); color: #fff; font-weight: 600; box-shadow: 0 6px 14px -6px rgba(16, 185, 129, 0.55); }
    .df-day--selected:hover { background: linear-gradient(135deg, #0ea672, #0b8157); }
    .df-day--disabled { opacity: 0.35; cursor: not-allowed; }
    .df-pop-footer { display: flex; align-items: center; justify-content: space-between; gap: 8px; border-top: 1px solid #eef4f0; margin-top: 12px; padding-top: 10px; min-height: 44px; }
    .df-today-btn { display: inline-flex; align-items: center; gap: 6px; border: none; border-radius: 9999px; background: #ecfdf3; color: #14855a; padding: 8px 14px; font-family: inherit; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: background .15s ease, transform .15s ease; }
    .df-today-btn:hover { background: #d8f7e6; transform: translateY(-1px); }
    .df-clear-btn { border: none; background: transparent; color: #94a3b8; font-family: inherit; font-size: 12.5px; font-weight: 600; cursor: pointer; padding: 8px 10px; border-radius: 9999px; transition: color .15s ease, background .15s ease; }
    .df-clear-btn:hover { color: #e0533d; background: #fef2f0; }

    /* ── Input native (datetime-local) ── */
    .df-native-input { width: 100%; height: 48px; border-radius: 14px; border: 1.5px solid #dfe9e3; background: #fff; padding: 0 14px 0 44px; font-family: inherit; font-size: 13.5px; font-weight: 500; color: #12201a; outline: none; transition: border-color .18s ease, box-shadow .18s ease; }
    .df-native-input:hover:not(:focus):not(.df-native-input--error) { border-color: #c3d8cc; }
    .df-native-input:focus { border-color: #1ea567; box-shadow: 0 0 0 4px rgba(30, 165, 103, 0.12); }
    .df-native-input--error { border-color: #e0533d !important; box-shadow: 0 0 0 4px rgba(224, 83, 61, 0.1) !important; }
    .df-native-input:disabled, .df-native-input[readonly] { cursor: not-allowed; opacity: 0.6; background: #f8fafc; }
    .df-native-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #1ea567; pointer-events: none; display: flex; z-index: 1; }

    /* ── Error / hint ── */
    .df-error { display: flex; align-items: center; gap: 5px; margin-top: 7px; font-size: 12px; font-weight: 500; color: #e0533d; font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif; }
    .df-hint { margin-top: 7px; font-size: 12px; color: #5b6b63; font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif; }

    .dark .df-label { color: #e2e8f0; }
    .dark .df-trigger { background: #1e293b; border-color: #334155; color: #e2e8f0; }
    .dark .df-trigger-placeholder { color: #64748b; }
    .dark .df-popover { background: #1e293b; border-color: #334155; }
    .dark .df-pop-title { color: #e2e8f0; }
    .dark .df-day { color: #e2e8f0; }
    .dark .df-day--outside { color: #475569; }
    .dark .df-day:hover:not(.df-day--selected):not(.df-day--disabled) { background: #334155; }
    .dark .df-native-input { background: #1e293b; border-color: #334155; color: #e2e8f0; }
    .dark .df-hint { color: #94a3b8; }
</style>

@once
    @push('scripts')
    <script>
        /**
         * datePicker — date picker custom Alpine untuk komponen x-admin.date-field.
         * Menyimpan nilai format Y-m-d pada hidden input & men-dispatch event
         * `input` + `change` (bubbles) agar validasi/clearError form tetap jalan.
         */
        function datePicker(initialValue, minIso, maxIso, hasError, isDisabled) {
            var MONTHS = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            var DAYS = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

            function parse(s) {
                if (!s) return null;
                var m = String(s).match(/^(\d{4})-(\d{2})-(\d{2})/);
                if (!m) return null;
                var d = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
                return isNaN(d.getTime()) ? null : d;
            }
            function toIso(d) {
                return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            }

            return {
                open: false,
                shake: !!hasError,
                isDisabled: !!isDisabled,
                value: initialValue || '',
                viewYear: new Date().getFullYear(),
                viewMonth: new Date().getMonth(),
                minDate: parse(minIso),
                maxDate: parse(maxIso),
                monthNames: MONTHS,
                dayNames: DAYS,

                init() {
                    var d = parse(this.value) || new Date();
                    this.viewYear = d.getFullYear();
                    this.viewMonth = d.getMonth();
                    if (this.shake && this.$refs.field) {
                        this.$refs.field.classList.add('field-shake');
                        setTimeout(() => this.$refs.field.classList.remove('field-shake'), 500);
                    }
                },

                get selectedDate() { return parse(this.value); },

                get displayLabel() {
                    var d = this.selectedDate;
                    if (!d) return '';
                    try {
                        return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' });
                    } catch (e) {
                        return d.getDate() + ' ' + this.monthNames[d.getMonth()].slice(0, 3) + ' ' + d.getFullYear();
                    }
                },

                get monthLabel() { return this.monthNames[this.viewMonth] + ' ' + this.viewYear; },

                get weeks() {
                    var first = new Date(this.viewYear, this.viewMonth, 1);
                    var start = new Date(this.viewYear, this.viewMonth, 1 - first.getDay());
                    var todayIso = toIso(new Date());
                    var cells = [];
                    for (var i = 0; i < 42; i++) {
                        var d = new Date(start.getFullYear(), start.getMonth(), start.getDate() + i);
                        var iso = toIso(d);
                        cells.push({
                            iso: iso,
                            day: d.getDate(),
                            inMonth: d.getMonth() === this.viewMonth,
                            isToday: iso === todayIso,
                            isSelected: this.value !== '' && iso === this.value,
                            isDisabled: (this.minDate && d < this.minDate) || (this.maxDate && d > this.maxDate),
                        });
                    }
                    return cells;
                },

                toggle() {
                    if (this.isDisabled) return;
                    this.open = !this.open;
                    if (this.open) {
                        var d = parse(this.value) || new Date();
                        this.viewYear = d.getFullYear();
                        this.viewMonth = d.getMonth();
                    }
                },

                prevMonth() { if (this.viewMonth === 0) { this.viewMonth = 11; this.viewYear--; } else { this.viewMonth--; } },
                nextMonth() { if (this.viewMonth === 11) { this.viewMonth = 0; this.viewYear++; } else { this.viewMonth++; } },
                prevYear() { this.viewYear--; },
                nextYear() { this.viewYear++; },

                emitChange() {
                    this.$nextTick(() => {
                        var input = this.$refs.input;
                        if (!input) return;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                },

                select(iso) {
                    this.value = iso;
                    this.open = false;
                    var d = parse(iso);
                    if (d) { this.viewYear = d.getFullYear(); this.viewMonth = d.getMonth(); }
                    this.emitChange();
                },

                selectToday() { this.select(toIso(new Date())); },

                clearValue() {
                    this.value = '';
                    this.open = false;
                    this.emitChange();
                },
            };
        }
    </script>
    @endpush
@endonce
