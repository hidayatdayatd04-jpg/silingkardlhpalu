@props(['title' => null])

<div class="border-b border-slate-200 px-3 py-2.5">
    @if($title)
        <p class="mb-2 text-[11px] font-extrabold uppercase tracking-[0.1em] text-slate-500">{{ $title }}</p>
    @endif
    <div class="space-y-1">
        {{ $slot }}
    </div>
</div>
