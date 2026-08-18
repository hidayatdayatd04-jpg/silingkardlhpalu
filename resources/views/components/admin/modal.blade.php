@props([
    'name' => null,
    'title' => null,
    'variant' => 'default',
    'icon' => null,
    'maxWidth' => 'md',
    'show' => false,
])

@php
    $maxWidthClass = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-2xl',
    ][$maxWidth] ?? 'max-w-md';

    $variantIcon = $icon ?? ($variant === 'danger' ? 'alert-triangle' : 'info-circle');
    $iconWrap = $variant === 'danger' ? 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-300' : 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300';
    $dialogId = 'admin-modal-' . \Illuminate\Support\Str::random(10);
    $titleId = $dialogId . '-title';
    $bodyId = $dialogId . '-body';
@endphp

<div
    x-data="{
        open: @js($show),
        lastFocused: null,
        openModal() {
            this.lastFocused = document.activeElement;
            this.open = true;
            this.$nextTick(() => this.focusFirst());
        },
        closeModal() {
            if (!this.open) return;
            this.open = false;
            const trigger = this.lastFocused;
            this.$nextTick(() => trigger?.focus?.());
        },
        focusFirst() {
            const panel = this.$refs.panel;
            if (!panel) return;
            const first = panel.querySelector('[data-modal-autofocus], button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled])');
            (first || panel).focus();
        },
        trapFocus(event) {
            window.dlhTrapFocus?.(event, this.$refs.panel);
        }
    }"
    x-init="if (open) $nextTick(() => focusFirst())"
    @if($name)
        x-on:open-modal.window="if ($event.detail === '{{ $name }}' || $event.detail?.name === '{{ $name }}') openModal()"
        x-on:close-modal.window="if (! $event.detail || $event.detail === '{{ $name }}') closeModal()"
    @endif
    x-on:keydown.escape.window="if (open) closeModal()"
    x-on:keydown.tab.window="if (open) trapFocus($event)"
    {{ $attributes }}
>
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $titleId }}"
            aria-describedby="{{ $bodyId }}"
        >
            <div
                x-show="open"
                x-transition:enter="transition-opacity ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-ink-950/55 backdrop-blur-sm"
                x-on:click="closeModal()"
                aria-hidden="true"
            ></div>

            <div
                x-show="open"
                x-transition:enter="transition-[opacity,transform] ease-out duration-200"
                x-transition:enter-start="translate-y-3 scale-95 opacity-0"
                x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                x-transition:leave="transition-[opacity,transform] ease-in duration-150"
                x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                x-transition:leave-end="translate-y-2 scale-95 opacity-0"
                x-ref="panel"
                tabindex="-1"
                class="relative max-h-[calc(100vh-2rem)] w-full {{ $maxWidthClass }} overflow-y-auto rounded-xl border border-white/80 bg-white shadow-[var(--shadow-modal)] focus:outline-none dark:border-white/[.08] dark:bg-slate-900"
            >
                @if($title || isset($header))
                    <div class="flex items-start gap-4 border-b border-slate-100 p-5 dark:border-white/[.07]">
                        @if(isset($header))
                            <div id="{{ $titleId }}" class="min-w-0 flex-1">
                                {{ $header }}
                            </div>
                        @else
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl {{ $iconWrap }}" aria-hidden="true">
                                <x-admin.icon :name="$variantIcon" :size="22" />
                            </span>
                            <div class="min-w-0 flex-1 pt-0.5">
                                <h2 id="{{ $titleId }}" class="text-h4 font-bold text-ink-900 dark:text-white">{{ $title }}</h2>
                            </div>
                        @endif
                        <button
                            type="button"
                            x-on:click="closeModal()"
                            class="grid size-9 shrink-0 place-items-center rounded-lg text-slate-500 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500/50 dark:text-slate-400 dark:hover:bg-white/[.07] dark:hover:text-white"
                            aria-label="Tutup dialog"
                        >
                            <x-admin.icon name="x" :size="18" />
                        </button>
                    </div>
                @else
                    <h2 id="{{ $titleId }}" class="sr-only">Dialog</h2>
                @endif

                <div id="{{ $bodyId }}" class="p-5 text-sm leading-relaxed text-ink-700 dark:text-slate-200">
                    {{ $slot }}
                </div>

                @if(isset($footer))
                    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/70 px-5 py-4 dark:border-white/[.07] dark:bg-white/[.03]">
                        {{ $footer }}
                    </div>
                @endif
            </div>
        </div>
    </template>
</div>
