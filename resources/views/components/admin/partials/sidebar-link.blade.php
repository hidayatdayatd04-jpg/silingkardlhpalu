@props(['href', 'active' => false, 'icon', 'label', 'collapsed' => false])

<a
    href="{{ $href }}"
    class="sidebar-link group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 ease-out
        {{ $active
            ? 'bg-gradient-to-r from-emerald-500/20 to-emerald-500/10 text-white shadow-[inset_0_0_0_1px_rgba(52,211,153,0.2),0_4px_12px_-4px_rgba(16,185,129,0.3)]'
            : 'text-white/50 hover:bg-white/5 hover:text-white/80' }}"
    @if($active) aria-current="page" @endif
>
    {{-- Active left bar --}}
    @if($active)
        <span class="absolute inset-y-2 left-0 w-[3px] rounded-full bg-emerald-400"></span>
        <span class="absolute left-[-17px] top-1/2 h-2 w-2 -translate-y-1/2 rounded-full bg-emerald-400 ring-2 ring-[#0a2f24]"></span>
    @else
        <span class="absolute left-[-17px] top-1/2 h-1.5 w-1.5 -translate-y-1/2 rounded-full bg-white/10 ring-2 ring-[#0a2f24] transition-colors duration-200 group-hover:bg-white/20"></span>
    @endif

    <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-white/5 text-white/30 group-hover:bg-white/10 group-hover:text-white/50' }} transition-all duration-200">
        <x-admin.icon :name="$icon" :size="16" />
    </span>
    <span class="truncate">{{ $label }}</span>

    @if($active)
        <span class="ml-auto h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
    @endif
</a>