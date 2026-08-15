@props([
    'label' => 'Filter',
    'active' => false,
    'count' => 0
])

<div
    x-data="{
        open: false,
        panelStyle: '',
        positionPanel() {
            const rect = this.$refs.trigger.getBoundingClientRect();
            const width = Math.min(288, window.innerWidth - 16);
            const top = rect.bottom + 6;
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
        x-ref="trigger"
        x-on:click="toggle()"
        type="button"
        class="group inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm font-semibold transition {{ $active ? 'border-emerald-300 bg-emerald-50 text-emerald-700 shadow-[0_4px_14px_rgba(16,185,129,0.18)]' : 'border-slate-200 bg-white text-slate-700 hover:border-emerald-300 hover:bg-emerald-50/60 hover:text-emerald-700' }}"
    >
        <span class="grid size-5 place-items-center rounded-lg {{ $active ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-emerald-100 group-hover:text-emerald-600' }} transition">
            <x-admin.icon name="filter" :size="14" />
        </span>
        <span>{{ $label }}</span>
        @if($count > 0)
            <span class="flex size-5 items-center justify-center rounded-full bg-emerald-600 px-1 text-[10px] font-bold text-white">
                {{ $count }}
            </span>
        @endif
        <x-admin.icon name="chevron-down" :size="16" class="text-slate-400 transition group-hover:text-emerald-600" ::class="{ 'rotate-180': open }" />
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
        x-bind:style="panelStyle"
        class="z-[9999] w-72 max-w-[calc(100vw-1rem)] overflow-y-auto rounded-xl border border-slate-200/80 bg-white/95 p-1.5 shadow-2xl shadow-slate-900/10 ring-1 ring-black/5 backdrop-blur-xl"
        style="display: none;"
    >
        {{ $slot }}
    </div>
</div>
