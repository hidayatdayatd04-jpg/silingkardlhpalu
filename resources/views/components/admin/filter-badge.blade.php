@props(['label', 'removeUrl'])

<div class="inline-flex max-w-full items-center gap-1.5 rounded-full border border-brand-200 bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-800 dark:border-brand-900 dark:bg-brand-950/45 dark:text-brand-200">
    <span class="truncate">{{ $label }}</span>
    <a href="{{ $removeUrl }}" class="grid size-5 shrink-0 place-items-center rounded-full outline-none transition-colors duration-150 hover:bg-brand-200 focus-visible:bg-brand-200 dark:hover:bg-brand-900 dark:focus-visible:bg-brand-900" aria-label="Hapus filter {{ $label }}">
        <x-admin.icon name="x" :size="12" aria-hidden="true" />
    </a>
</div>
