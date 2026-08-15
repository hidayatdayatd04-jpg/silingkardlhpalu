@props(['value'])

<td class="w-12 px-5 py-3.5 first:rounded-l-xl last:rounded-r-xl">
    <input
        type="checkbox"
        :value="{{ $value }}"
        x-model="selected"
        class="size-4 cursor-pointer rounded-md border-slate-300 text-brand-600 transition focus:ring-2 focus:ring-brand-500/40 focus:ring-offset-0 checked:bg-brand-600"
    >
</td>
