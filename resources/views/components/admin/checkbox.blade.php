@props([
    'label' => '',
    'description' => null,
    'checked' => false,
])

<label {{ $attributes->except('class')->merge(['class' => 'flex min-h-11 cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white px-3.5 py-3 outline-none transition-[background-color,border-color,box-shadow] duration-150 hover:border-brand-300 hover:bg-brand-50/55 focus-within:border-brand-400 focus-within:ring-2 focus-within:ring-brand-600/15 dark:border-slate-700 dark:bg-slate-950 dark:hover:border-brand-700 dark:hover:bg-brand-950/30 dark:focus-within:border-brand-600']) }}>
    <input
        type="checkbox"
        @if($checked) checked @endif
        {{ $attributes->only(['name', 'value', 'id', 'disabled', 'required', 'wire:model', 'x-model', 'x-on:change'])->merge(['class' => 'mt-0.5 size-4 shrink-0 rounded border-slate-300 text-brand-700 focus:ring-2 focus:ring-brand-600/30 focus:ring-offset-0 dark:border-slate-600 dark:bg-slate-950 dark:checked:bg-brand-500']) }}
    >
    <span class="min-w-0 flex-1">
        <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $label }}</span>
        @if($description)
            <span class="mt-0.5 block text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $description }}</span>
        @endif
    </span>
</label>
