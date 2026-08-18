@props([
    'label' => 'Filter',
    'active' => false,
    'count' => 0,
])

@php($panelId = 'admin-filter-'.Str::random(8))

<div
    x-data="{
        open: false,
        panelStyle: '',
        positionPanel() {
            const rect = this.$refs.trigger.getBoundingClientRect();
            const width = Math.min(304, window.innerWidth - 16);
            const top = rect.bottom + 8;
            const avail = Math.max(220, window.innerHeight - top - 16);
            let left = rect.left;
            const maxLeft = window.innerWidth - width - 8;
            if (left > maxLeft) left = maxLeft;
            if (left < 8) left = 8;
            this.panelStyle = `position:fixed; top:${top}px; left:${left}px; width:${width}px; max-height:${avail}px;`;
        },
        toggle() {
            if (!this.open) this.positionPanel();
            this.open = !this.open;
        },
        init() {
            const reposition = () => { if (this.open) this.positionPanel(); };
            window.addEventListener('scroll', reposition, true);
            window.addEventListener('resize', reposition);
        }
    }"
    x-on:click.window="if (open && !$el.contains($event.target) && !$event.target.closest('[data-select-panel]')) open = false"
    x-on:keydown.escape.window="open = false"
    class="relative"
>
    <button
        id="{{ $panelId }}-button"
        x-ref="trigger"
        x-on:click="toggle()"
        type="button"
        aria-haspopup="dialog"
        aria-controls="{{ $panelId }}"
        x-bind:aria-expanded="open.toString()"
        class="group inline-flex min-h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold outline-none transition-[background-color,border-color,color,box-shadow] duration-150 focus-visible:ring-2 focus-visible:ring-brand-600/25 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-slate-950 {{ $active ? 'border-brand-300 bg-brand-50 text-brand-800 shadow-sm dark:border-brand-800 dark:bg-brand-950/55 dark:text-brand-200' : 'border-slate-200 bg-white text-slate-700 hover:border-brand-300 hover:bg-brand-50 hover:text-brand-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-brand-700 dark:hover:bg-brand-950/45 dark:hover:text-brand-200' }}"
    >
        <span class="grid size-6 place-items-center rounded-lg {{ $active ? 'bg-brand-700 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-brand-100 group-hover:text-brand-700 dark:bg-slate-800 dark:text-slate-400 dark:group-hover:bg-brand-950 dark:group-hover:text-brand-300' }} transition-[background-color,color] duration-150">
            <x-admin.icon name="filter" :size="14" aria-hidden="true" />
        </span>
        <span>{{ $label }}</span>
        @if($count > 0)
            <span class="grid size-5 place-items-center rounded-full bg-brand-700 px-1 text-[10px] font-bold text-white">{{ $count }}</span>
        @endif
        <x-admin.icon name="chevron-down" :size="16" class="text-slate-400 transition-transform duration-150 dark:text-slate-500" x-bind:class="{ 'rotate-180': open }" aria-hidden="true" />
    </button>

    <div
        id="{{ $panelId }}"
        x-show="open"
        x-transition:enter="transition-[opacity,transform] ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1 scale-[.98]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition-[opacity,transform] ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-1 scale-[.98]"
        x-bind:style="panelStyle"
        role="dialog"
        aria-labelledby="{{ $panelId }}-button"
        class="z-[95] w-72 max-w-[calc(100vw-1rem)] overflow-y-auto rounded-2xl border border-slate-200 bg-white p-1.5 shadow-[0_18px_40px_-16px_rgba(15,23,42,0.35)] ring-1 ring-slate-950/5 dark:border-slate-700 dark:bg-slate-900 dark:ring-white/5"
        style="display: none;"
    >
        {{ $slot }}
    </div>
</div>
