@props(['value'])

<td class="w-12 px-3 py-3.5 sm:px-4">
    <label class="grid size-7 cursor-pointer place-items-center rounded-lg text-brand-700 outline-none transition-colors duration-150 hover:bg-brand-50 focus-within:ring-2 focus-within:ring-brand-600/30 dark:text-brand-300 dark:hover:bg-brand-950/45">
        <input
            type="checkbox"
            :value="{{ $value }}"
            x-model="selected"
            aria-label="Pilih baris"
            class="size-4 cursor-pointer rounded border-slate-300 text-brand-700 focus:ring-2 focus:ring-brand-600/30 focus:ring-offset-0 dark:border-slate-600 dark:bg-slate-950 dark:checked:bg-brand-500"
        >
    </label>
</td>
