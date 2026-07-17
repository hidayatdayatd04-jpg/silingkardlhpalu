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
    $id = $attributes->get('id', 'select-' . Str::random(8));
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
    $normalizedOptions = collect($options)
        ->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])
        ->values();
@endphp

<div
    {{ $attributes->whereStartsWith('wire:model') }}
    x-modelable="selected"
    class="space-y-1.5"
    x-data="{
        open: false,
        search: '',
        selected: @js((string) old($name, $selected)),
        selectedLabel: '',
        options: @js($normalizedOptions),
        searchable: {{ $searchable ? 'true' : 'false' }},
        panelStyle: '',

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
            // Update Livewire property langsung
            this.$nextTick(() => {
                if (typeof @this !== 'undefined') {
                    @this.set('{{ $name }}', value);
                }
            });
        },

        positionPanel() {
            const rect = this.$refs.trigger.getBoundingClientRect();
            this.panelStyle = `position:fixed; top:${rect.bottom + 8}px; left:${rect.left}px; width:${rect.width}px;`;
        },

        toggle() {
            if (!this.open) {
                this.positionPanel();
            }
            this.open = !this.open;
        },

        init() {
            this.$watch('selected', (value) => {
                const selectedOption = this.options.find((option) => option.value === String(value));
                this.selectedLabel = selectedOption ? selectedOption.label : '';
                this.$refs.hiddenInput.value = value;
                this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                this.$refs.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                // Update Livewire property langsung via @this
                this.$nextTick(() => {
                    if (typeof @this !== 'undefined') {
                        @this.set('{{ $name }}', value);
                    }
                });
            });
            const selectedOption = this.options.find((option) => option.value === String(this.selected));
            this.selectedLabel = selectedOption ? selectedOption.label : '';

            const reposition = () => { if (this.open) this.positionPanel(); };
            window.addEventListener('scroll', reposition, true);
            window.addEventListener('resize', reposition);
        }
    }"
    x-on:keydown.escape.window="open = false"
>
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-slate-900">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <input
            type="hidden"
            id="{{ $id }}"
            name="{{ $name }}"
            x-ref="hiddenInput"
            :value="selected"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
        >

        <button
            type="button"
            x-ref="trigger"
            x-on:click="toggle()"
            :disabled="{{ $disabled ? 'true' : 'false' }}"
            class="group relative min-h-11 w-full rounded-xl border py-2.5 pl-3.5 pr-11 text-left text-sm shadow-[0_1px_2px_rgba(15,23,42,0.04)] transition duration-150 focus:outline-none focus:ring-4 {{ $hasError ? 'border-rose-300 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-rose-100' : 'border-slate-300 bg-white text-slate-900 hover:border-emerald-400 hover:shadow-[0_6px_18px_rgba(15,23,42,0.08)] focus:border-emerald-500 focus:ring-emerald-100' }} {{ $disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }}"
            :class="{ 'ring-4 ring-emerald-100 border-emerald-500 shadow-[0_8px_24px_rgba(13,148,136,0.12)]': open }"
        >
            <span x-show="!selected" class="block truncate text-slate-500">{{ $placeholder }}</span>
            <span x-show="selected" x-text="selectedLabel" class="block truncate font-semibold text-slate-900"></span>

            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <svg
                    class="h-5 w-5 text-slate-400 transition duration-150 group-hover:text-emerald-500"
                    :class="{ 'rotate-180 text-emerald-500': open }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </span>
        </button>

        <template x-teleport="body">
            <div
                x-show="open"
                x-on:click.outside="open = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                class="z-[9999] max-h-72 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-[0_18px_48px_rgba(15,23,42,0.16)] ring-1 ring-slate-900/5 focus:outline-none"
                x-bind:style="panelStyle"
                style="display: none;"
            >
                <template x-if="searchable && options.length > 5">
                    <div class="p-1">
                        <input
                            type="text"
                            x-model="search"
                            placeholder="Cari..."
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100"
                            x-on:click.stop
                        >
                    </div>
                </template>

                <button
                    type="button"
                    x-on:click="selectOption('', '')"
                    class="mb-1 flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm text-slate-500 transition hover:bg-slate-50 hover:text-slate-700"
                >
                    <span>{{ $placeholder }}</span>
                </button>

                <div class="max-h-56 overflow-auto pr-0.5">
                    <template x-for="option in filteredOptions" :key="option.value">
                        <button
                            type="button"
                            x-on:click="selectOption(option.value, option.label)"
                            class="relative mb-1 flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-left text-sm transition"
                            :class="selected === option.value ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-800 hover:bg-emerald-50 hover:text-emerald-800'"
                        >
                            <span x-text="option.label" class="truncate font-medium"></span>
                            <span x-show="selected === option.value" class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/20">
                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </button>
                    </template>
                </div>

                <div x-show="searchable && filteredOptions.length === 0" class="px-3 py-6 text-center text-sm font-medium text-slate-500">
                    Tidak ada hasil ditemukan
                </div>
            </div>
        </template>
    </div>

    @if($hint)
        <p class="text-xs text-slate-600">{{ $hint }}</p>
    @endif

    @if($hasError)
        <p class="flex items-center gap-1 text-xs font-medium text-rose-600">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ $errorMessage }}
        </p>
    @endif
</div>
