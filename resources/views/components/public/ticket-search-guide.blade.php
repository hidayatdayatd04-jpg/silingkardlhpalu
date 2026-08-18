<aside
    {{ $attributes->merge(['class' => 'group relative overflow-hidden rounded-2xl border border-brand-100/90 bg-white/90 px-4 py-3.5 shadow-[0_14px_30px_-25px_rgba(20,106,68,0.34)] backdrop-blur-sm dark:border-brand-900/50 dark:bg-slate-900/85 sm:px-5']) }}
    aria-label="{{ __('Panduan pencarian status') }}"
>
    <div class="pointer-events-none absolute inset-y-0 left-0 w-1 bg-gradient-to-b from-brand-400 via-brand-500 to-brand-700" aria-hidden="true"></div>

    <div class="relative grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] sm:items-center sm:gap-5">
        <div class="flex min-w-0 items-center gap-3">
            <span class="grid size-9 shrink-0 place-items-center rounded-xl border border-brand-100 bg-brand-50 text-brand-700 dark:border-brand-800/70 dark:bg-brand-950/45 dark:text-brand-300" aria-hidden="true">
                <x-icons.ui name="search" class="size-[1.05rem]" />
            </span>
            <div class="min-w-0">
                <p class="text-sm font-bold tracking-[-0.01em] text-slate-900 dark:text-slate-100">{{ __('Cari dengan tiket atau email') }}</p>
                <p class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ __('Masukkan data yang digunakan saat pengajuan.') }}</p>
            </div>
        </div>

        <span class="hidden h-9 w-px bg-brand-100 dark:bg-brand-900/70 sm:block" aria-hidden="true"></span>

        <div class="flex min-w-0 items-center gap-3 border-t border-brand-100/80 pt-3 dark:border-brand-900/60 sm:border-t-0 sm:pt-0">
            <span class="grid size-9 shrink-0 place-items-center rounded-xl border border-brand-100 bg-brand-50 text-brand-700 dark:border-brand-800/70 dark:bg-brand-950/45 dark:text-brand-300" aria-hidden="true">
                <x-icons.ui name="document" class="size-[1.05rem]" />
            </span>
            <div class="min-w-0">
                <p class="text-sm font-bold tracking-[-0.01em] text-slate-900 dark:text-slate-100">{{ __('Lihat status dan catatan petugas') }}</p>
                <p class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ __('Hasil pencarian ditampilkan di bawah formulir.') }}</p>
            </div>
        </div>
    </div>
</aside>
