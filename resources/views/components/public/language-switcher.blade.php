{{-- Language switcher ID/EN — bendera PNG, animasi halus, menyimpan pilihan di sesi --}}
@php $current = app()->getLocale(); @endphp
<div x-data="{ open: false }" @click.away="open = false" @keydown.escape="open = false" class="relative">
    <button
        type="button"
        @click="open = !open"
        :aria-expanded="open.toString()"
        aria-label="{{ __('Bahasa') }}"
        class="group inline-flex items-center gap-1.5 h-10 pl-1.5 pr-2 rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm hover:border-brand-300 hover:shadow transition-all duration-300 dark:bg-slate-800/60 dark:border-slate-700 dark:text-slate-300 dark:hover:border-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 cursor-pointer"
    >
        <span class="relative block h-7 w-7 rounded-full overflow-hidden ring-1 ring-black/5 shadow-inner">
            <img src="{{ asset('assets/images/' . ($current === 'en' ? 'english.png' : 'indonesia.png')) }}"
                 alt="" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" aria-hidden="true">
        </span>
        <span class="text-xs font-bold uppercase tracking-wide">{{ $current === 'en' ? 'EN' : 'ID' }}</span>
        <svg class="size-3.5 text-slate-400 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <div
        x-show="open" x-cloak style="display:none"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
        class="absolute right-0 mt-2 w-48 origin-top-right rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-slate-100 dark:border-slate-800 shadow-2xl shadow-slate-900/10 p-1.5 z-50"
    >
        @foreach ([['id','indonesia.png',__('Indonesia')],['en','english.png',__('Inggris')]] as [$code,$flag,$label])
        <a href="{{ route('lang.switch', $code) }}"
           class="group/item flex items-center gap-3 px-2.5 py-2 rounded-xl text-sm font-medium transition-all duration-200 {{ $current === $code ? 'text-brand-700 dark:text-brand-300 bg-brand-50/70 dark:bg-brand-900/20' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
            <span class="block h-7 w-7 rounded-full overflow-hidden ring-1 ring-black/5 shadow-sm shrink-0">
                <img src="{{ asset('assets/images/' . $flag) }}" alt="" class="h-full w-full object-cover transition-transform duration-500 group-hover/item:scale-110" aria-hidden="true">
            </span>
            <span class="flex-1">{{ $label }}</span>
            @if($current === $code)
                <svg class="size-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5"/></svg>
            @endif
        </a>
        @endforeach
    </div>
</div>
