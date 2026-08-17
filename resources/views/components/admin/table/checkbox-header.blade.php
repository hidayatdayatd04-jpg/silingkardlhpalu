<th class="sticky top-0 z-10 w-12 rounded-tl-xl bg-slate-50/95 px-5 py-3.5 backdrop-blur-sm">
    <input
        type="checkbox"
        x-model="selectAll"
        aria-label="{{ __('Pilih semua') }}"
        x-on:change="
            if (selectAll) {
                selected = items.map(i => i.toString());
            } else {
                selected = [];
            }
        "
        class="size-4 cursor-pointer rounded-md border-slate-300 text-brand-600 transition focus:ring-2 focus:ring-brand-500/40 focus:ring-offset-0 checked:bg-brand-600"
    >
</th>
