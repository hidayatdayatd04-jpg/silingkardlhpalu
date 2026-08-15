<nav class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
    <span class="me-2 text-xs font-bold uppercase tracking-wide text-slate-400">PoC Nav</span>
    <a wire:navigate href="{{ route('admin.navigate-poc.index') }}"
       class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-700">Index</a>
    <a wire:navigate href="{{ route('admin.navigate-poc.show', 1) }}"
       class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-500">Item 1</a>
    <a wire:navigate href="{{ route('admin.navigate-poc.show', 2) }}"
       class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-500">Item 2</a>
    <a href="{{ route('admin.navigate-poc.show', 3) }}"
       class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-red-400">Item 3 <span class="opacity-80">(tanpa navigate)</span></a>
</nav>