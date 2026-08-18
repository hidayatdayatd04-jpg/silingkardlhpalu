@props(['title' => null])

<div class="border-b border-slate-100 px-2.5 py-3 last:border-b-0 dark:border-slate-800">
    @if($title)
        <p class="mb-2 px-0.5 text-[11px] font-bold uppercase tracking-[0.09em] text-slate-500 dark:text-slate-400">{{ $title }}</p>
    @endif
    <div class="space-y-1">
        {{ $slot }}
    </div>
</div>
