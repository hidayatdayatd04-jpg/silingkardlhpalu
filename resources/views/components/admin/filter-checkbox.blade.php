@props(['label', 'value', 'checked' => false])

<label class="flex min-h-10 cursor-pointer items-center gap-2.5 rounded-xl px-2.5 py-1.5 outline-none transition-colors duration-150 hover:bg-slate-50 focus-within:bg-slate-50 dark:hover:bg-slate-800/75 dark:focus-within:bg-slate-800/75">
    <input
        type="checkbox"
        value="{{ $value }}"
        @if($checked) checked @endif
        {{ $attributes->merge(['class' => 'size-4 rounded border-slate-300 text-brand-700 focus:ring-2 focus:ring-brand-600/30 focus:ring-offset-0 dark:border-slate-600 dark:bg-slate-950 dark:checked:bg-brand-500']) }}
    >
    <span class="min-w-0 flex-1 text-[13px] font-medium text-slate-700 dark:text-slate-200">{{ $label }}</span>
    @if($slot->isNotEmpty())
        <span class="shrink-0 text-xs text-slate-500 dark:text-slate-400">{{ $slot }}</span>
    @endif
</label>
