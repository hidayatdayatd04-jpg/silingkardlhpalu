<button
    type="button"
    x-data="{
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        }
    }"
    @click="toggle()"
    :aria-label="dark ? '<?php echo e(__('Aktifkan mode terang')); ?>' : '<?php echo e(__('Aktifkan mode gelap')); ?>'"
    :aria-pressed="dark.toString()"
    class="relative h-10 w-10 inline-flex justify-center items-center overflow-hidden rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition-all duration-300 hover:border-brand-300 hover:shadow dark:bg-slate-800/60 dark:border-slate-700 dark:text-slate-300 dark:hover:border-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 cursor-pointer group"
>
    
    <span class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
          :class="dark ? 'bg-indigo-500/10' : 'bg-amber-400/15'" aria-hidden="true"></span>

    
    <svg class="absolute size-5 text-amber-500 transition-all duration-500"
         :class="dark ? 'opacity-0 rotate-90 scale-50' : 'opacity-100 rotate-0 scale-100'"
         fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/>
    </svg>

    
    <svg class="absolute size-5 text-indigo-300 transition-all duration-500"
         :class="dark ? 'opacity-100 rotate-0 scale-100' : 'opacity-0 -rotate-90 scale-50'"
         fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/>
    </svg>
</button>
<?php /**PATH C:\xampp\htdocs\DLH - PALU\resources\views/components/public/dark-mode-toggle.blade.php ENDPATH**/ ?>