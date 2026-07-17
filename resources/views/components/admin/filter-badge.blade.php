@props(['label', 'removeUrl'])

<div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
    <span>{{ $label }}</span>
    <a 
        href="{{ $removeUrl }}" 
        class="grid size-4 place-items-center rounded-full transition hover:bg-emerald-200"
    >
        <x-admin.icon name="x" :size="12" />
    </a>
</div>
