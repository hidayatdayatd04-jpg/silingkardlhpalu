<button
    type="button"
    x-on:click="$store.theme.toggle()"
    class="topbar-btn relative overflow-hidden"
    :title="$store.theme.dark ? 'Gunakan mode terang' : 'Gunakan mode gelap'"
    :aria-label="$store.theme.dark ? 'Gunakan mode terang' : 'Gunakan mode gelap'"
    :aria-pressed="$store.theme.dark"
>
    <span x-show="$store.theme.dark" x-cloak x-transition:enter="transition-[opacity,transform] ease-out duration-150" x-transition:enter-start="scale-75 opacity-0" x-transition:enter-end="scale-100 opacity-100" class="absolute inset-0 grid place-items-center" aria-hidden="true">
        <x-admin.icon name="sun" :size="20" />
    </span>
    <span x-show="!$store.theme.dark" x-cloak x-transition:enter="transition-[opacity,transform] ease-out duration-150" x-transition:enter-start="scale-75 opacity-0" x-transition:enter-end="scale-100 opacity-100" class="absolute inset-0 grid place-items-center" aria-hidden="true">
        <x-admin.icon name="moon" :size="20" />
    </span>
</button>
