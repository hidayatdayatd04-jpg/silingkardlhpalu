@props(['resetUrl' => null])

<div class="flex items-center gap-2 border-t border-slate-100 bg-slate-50/80 p-2.5 dark:border-slate-800 dark:bg-slate-950/35">
    @if($resetUrl)
        <a href="{{ $resetUrl }}" class="inline-flex min-h-9 flex-1 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-center text-sm font-semibold text-slate-700 outline-none transition-[background-color,border-color,color] duration-150 hover:bg-slate-50 focus-visible:ring-2 focus-visible:ring-brand-600/25 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
            Reset
        </a>
    @endif
    <button type="submit" class="inline-flex min-h-9 flex-1 items-center justify-center rounded-lg bg-brand-700 px-3 text-center text-sm font-semibold text-white outline-none transition-[background-color,box-shadow] duration-150 hover:bg-brand-800 focus-visible:ring-2 focus-visible:ring-brand-600/30 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-50 dark:focus-visible:ring-offset-slate-950">
        Terapkan
    </button>
</div>
