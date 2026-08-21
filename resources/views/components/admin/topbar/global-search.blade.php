<div class="hidden w-full max-w-[200px] md:block lg:max-w-[300px] xl:max-w-[360px]" x-data="{ focused: false }">
    <div class="relative search-glow" :class="focused ? 'z-10' : ''">
        <label class="sr-only" for="admin-global-search">Buka pencarian cepat</label>
        <input
            id="admin-global-search"
            type="search"
            readonly
            aria-haspopup="dialog"
            aria-controls="admin-command-palette"
            aria-label="Buka pencarian cepat"
            x-on:focus="focused = true"
            x-on:blur="focused = false"
            x-on:click="$dispatch('open-command-palette')"
            x-on:keydown.enter.prevent="$dispatch('open-command-palette')"
            x-on:keydown.space.prevent="$dispatch('open-command-palette')"
            placeholder="Cari menu, artikel, pengguna..."
            class="h-9 w-full cursor-pointer rounded-full border border-slate-200/80 bg-slate-50/60 py-2 pl-4 pr-14 text-[12px] text-slate-700 transition-[background-color,border-color,box-shadow] duration-200 placeholder:text-slate-400 hover:border-slate-300 hover:bg-white focus-visible:outline-none lg:h-10 lg:pr-20 lg:text-[13px] dark:border-white/[.08] dark:bg-white/[.04] dark:text-slate-200 dark:placeholder:text-slate-500 dark:hover:border-white/[.15] dark:hover:bg-white/[.06]"
        />

        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3" aria-hidden="true">
            <kbd class="hidden rounded-md border border-slate-200/80 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-slate-400 shadow-sm lg:inline-block dark:border-white/[.1] dark:bg-white/[.06] dark:text-slate-500">
                Ctrl K
            </kbd>
        </div>
    </div>
</div>
