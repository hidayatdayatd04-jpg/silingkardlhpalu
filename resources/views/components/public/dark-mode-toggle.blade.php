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
    :aria-label="dark ? '{{ __('Aktifkan mode terang') }}' : '{{ __('Aktifkan mode gelap') }}'"
    :aria-pressed="dark.toString()"
    class="relative h-10 w-10 inline-flex justify-center items-center overflow-hidden rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition-all duration-300 hover:border-brand-300 hover:shadow dark:bg-slate-800/60 dark:border-slate-700 dark:text-slate-300 dark:hover:border-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 cursor-pointer group"
>
    {{-- Cahaya latar saat hover --}}
    <span class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
          :class="dark ? 'bg-indigo-500/10' : 'bg-amber-400/15'" aria-hidden="true"></span>

    {{-- Matahari --}}
    <x-icons.ui
        name="sun"
        class="absolute size-5 text-amber-500 transition-all duration-500"
        ::class="dark ? 'opacity-0 rotate-90 scale-50' : 'opacity-100 rotate-0 scale-100'"
    />

    {{-- Bulan --}}
    <x-icons.ui
        name="moon"
        class="absolute size-5 text-indigo-400 transition-all duration-500"
        ::class="dark ? 'opacity-100 rotate-0 scale-100' : 'opacity-0 -rotate-90 scale-50'"
    />
</button>
