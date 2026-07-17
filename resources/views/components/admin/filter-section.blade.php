@props(['title' => null])

<div class="border-b border-slate-200 p-4">
    @if($title)
        <p class="mb-3 text-xs font-extrabold uppercase tracking-[0.12em] text-slate-500">{{ $title }}</p>
    @endif
    <div class="space-y-2">
        {{ $slot }}
    </div>
</div>
