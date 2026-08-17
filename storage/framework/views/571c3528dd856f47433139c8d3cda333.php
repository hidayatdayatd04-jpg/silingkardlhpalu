<div class="hidden lg:block" x-data="{ focused: false }">
    <div class="relative search-glow" :class="focused ? 'z-10' : ''">
        
        <input
            type="search"
            x-on:focus="focused = true"
            x-on:blur="focused = false"
            x-on:click="$dispatch('open-command-palette')"
            readonly
            placeholder="Cari menu, artikel, pengguna..."
            class="h-10 w-[340px] cursor-pointer rounded-full border border-slate-200/80 bg-slate-50/60 py-2 pl-4 pr-20 text-[13px] text-slate-700 outline-none transition-all duration-250 placeholder:text-slate-400 hover:border-slate-300 hover:bg-white dark:border-white/[.08] dark:bg-white/[.04] dark:text-slate-200 dark:placeholder:text-slate-500 dark:hover:border-white/[.15] dark:hover:bg-white/[.06]"
        />

        
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
            <kbd class="hidden rounded-md border border-slate-200/80 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-slate-400 shadow-sm dark:border-white/[.1] dark:bg-white/[.06] dark:text-slate-500 md:inline-block">
                <span class="text-xs">&#8984;</span>K
            </kbd>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\DLH - PALU\resources\views/components/admin/topbar/global-search.blade.php ENDPATH**/ ?>