<th class="w-12 px-5 py-4">
    <input 
        type="checkbox"
        x-model="selectAll"
        x-on:change="
            if (selectAll) {
                selected = items.map(i => i.toString());
            } else {
                selected = [];
            }
        "
        class="size-4 rounded border-slate-300 text-emerald-600 transition focus:ring-2 focus:ring-emerald-500 focus:ring-offset-0"
    >
</th>
