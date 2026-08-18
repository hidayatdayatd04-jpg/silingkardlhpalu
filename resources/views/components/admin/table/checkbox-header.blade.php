<th scope="col" class="sticky top-0 z-10 w-12 border-b border-slate-200 bg-slate-50/95 px-3 py-3 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/95 sm:px-4">
    <label class="grid size-7 cursor-pointer place-items-center rounded-lg text-brand-700 outline-none transition-colors duration-150 hover:bg-brand-50 focus-within:ring-2 focus-within:ring-brand-600/30 dark:text-brand-300 dark:hover:bg-brand-950/45">
        <input
            type="checkbox"
            x-model="selectAll"
            aria-label="Pilih semua baris"
            x-on:change="
                if (selectAll) {
                    selected = items.map(i => i.toString());
                } else {
                    selected = [];
                }
            "
            class="size-4 cursor-pointer rounded border-slate-300 text-brand-700 focus:ring-2 focus:ring-brand-600/30 focus:ring-offset-0 dark:border-slate-600 dark:bg-slate-950 dark:checked:bg-brand-500"
        >
    </label>
</th>
