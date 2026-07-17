@props([
    'name' => null,          // opsional: buka via $dispatch('open-modal', 'name') / event 'close-modal'
    'title' => null,
    'variant' => 'default',  // default | danger
    'icon' => null,
    'maxWidth' => 'md',      // sm | md | lg | xl
    'show' => false,         // state awal (untuk modal statik / x-model luar)
])

@php
    $maxWidthClass = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-2xl',
    ][$maxWidth] ?? 'max-w-md';

    $variantIcon = $icon ?? ($variant === 'danger' ? 'alert-triangle' : 'info-circle');
    $iconWrap = $variant === 'danger'
        ? 'bg-danger-100 text-danger-600'
        : 'bg-brand-100 text-brand-600';
@endphp

<div
    x-data="{
        open: @js($show),
        openModal() { this.open = true; this.$nextTick(() => this.$refs.panel?.focus()); },
        closeModal() { this.open = false; }
    }"
    @if($name)
        x-on:open-modal.window="if ($event.detail === '{{ $name }}' || $event.detail?.name === '{{ $name }}') openModal()"
        x-on:close-modal.window="if (! $event.detail || $event.detail === '{{ $name }}') closeModal()"
    @endif
    x-on:keydown.escape.window="closeModal()"
    {{ $attributes }}
>
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            @if($title) aria-label="{{ $title }}" @endif
        >
            {{-- Backdrop --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-ink-950/50 backdrop-blur-sm"
                x-on:click="closeModal()"
            ></div>

            {{-- Panel --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                x-trap.noscroll="open"
                x-ref="panel"
                tabindex="-1"
                class="relative w-full {{ $maxWidthClass }} overflow-hidden rounded-xl border border-white/80 bg-white shadow-[var(--shadow-modal)] focus:outline-none"
            >
                {{-- Header --}}
                @if($title || isset($header))
                    <div class="flex items-start gap-4 border-b border-slate-100 p-5">
                        @if(isset($header))
                            {{ $header }}
                        @else
                            <div class="grid size-11 shrink-0 place-items-center rounded-xl {{ $iconWrap }}">
                                <x-admin.icon :name="$variantIcon" :size="22" />
                            </div>
                            <div class="min-w-0 flex-1 pt-0.5">
                                <h3 class="text-h4 font-bold text-ink-900">{{ $title }}</h3>
                            </div>
                        @endif
                        <button
                            type="button"
                            x-on:click="closeModal()"
                            class="grid size-8 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500/40"
                            aria-label="Tutup"
                        >
                            <x-admin.icon name="x" :size="18" />
                        </button>
                    </div>
                @endif

                {{-- Body --}}
                <div class="p-5 text-sm leading-relaxed text-ink-700">
                    {{ $slot }}
                </div>

                {{-- Footer --}}
                @if(isset($footer))
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/70 px-5 py-4">
                        {{ $footer }}
                    </div>
                @endif
            </div>
        </div>
    </template>
</div>
