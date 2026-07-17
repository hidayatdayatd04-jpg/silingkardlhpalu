<a
    href="{{ url('/') }}"
    target="_blank"
    class="group relative hidden items-center gap-2 overflow-hidden rounded-full px-4 py-2 text-[13px] font-semibold text-white transition-all duration-300 hover:shadow-lg hover:shadow-emerald-500/25 active:scale-[0.97] sm:inline-flex"
    style="background: linear-gradient(135deg, #059669 0%, #10b981 55%, #34d399 100%);"
    title="Lihat Website Publik"
>
    {{-- Hover glow --}}
    <span class="absolute inset-0 rounded-full bg-white/0 transition-all duration-300 group-hover:bg-white/10"></span>
    <x-admin.icon name="eye" :size="16" class="relative transition-transform duration-300 group-hover:scale-110" />
    <span class="relative hidden sm:inline">Lihat Website</span>
</a>
