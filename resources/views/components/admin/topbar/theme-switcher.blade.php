<button
    x-on:click="$store.theme.toggle()"
    class="topbar-btn relative overflow-hidden"
    :title="$store.theme.dark ? 'Mode Terang' : 'Mode Gelap'"
    aria-label="Toggle dark mode"
>
    {{-- Sun icon (shown in dark mode) --}}
    <span
        x-show="$store.theme.dark"
        x-transition:enter="theme-spin-enter"
        class="absolute inset-0 grid place-items-center"
    >
        <x-admin.icon name="sun" :size="20" />
    </span>

    {{-- Moon icon (shown in light mode) --}}
    <span
        x-show="!$store.theme.dark"
        x-transition:enter="theme-spin-enter"
        class="absolute inset-0 grid place-items-center"
    >
        <x-admin.icon name="moon" :size="20" />
    </span>
</button>
