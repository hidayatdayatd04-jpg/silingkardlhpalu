@props(['groups', 'allGroups', 'user'])

@php
    $activeResource = request()->route('resource');
    $isDashboard = request()->routeIs('admin.dashboard');

    $iconMap = [
        'dashboard' => 'dashboard',
        'pengaduan-pengendalian' => 'megaphone',
        'permohonan-rekomendasi' => 'clipboard-check',
        'pengajuan-rintek-pertek' => 'factory',
        'registrasi-usaha-lb3' => 'clipboard-list',
        'statistik-sampah' => 'chart-bar',
        'pinjam-taman' => 'park-bench',
        'data-tanam-pohon' => 'seedling',
        'pengaduan-tata-penataan' => 'building',
        'pelanggaran' => 'alert-triangle',
        'sosialisasi' => 'presentation',
        'artikel' => 'news',
        'artikel-pengendalian' => 'news',
        'artikel-sampah-lb3' => 'news',
        'artikel-tata-penataan' => 'news',
        'artikel-rth' => 'news',
        'sekretariat' => 'building',
        'user' => 'user-check',
        'website-settings' => 'settings',
        'peta' => 'map',
        'whatsapp' => 'whatsapp',
    ];

    $roleName = $user->role?->label() ?? 'Admin';

    // Peta access per role (hanya Sampah & LB3)
    $hasPetaAccess = false;
    $adminRole = $user->adminRole();
    if ($adminRole) {
        $allowedGroups = $adminRole->allowedGroups();
        $hasPetaAccess = in_array('sampah-lb3', $allowedGroups);
    }

    // Peta menu dipindahkan ke dalam grup Sampah & LB3
    if ($hasPetaAccess && isset($groups['sampah-lb3'])) {
        $groups['sampah-lb3']['items'][] = ['slug' => 'peta', 'label' => 'Peta'];
    }
@endphp

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• MOBILE SIDEBAR â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div x-data="{ open: false, touchStartX: 0, touchStartY: 0 }" x-on:keydown.escape.window="open = false" x-on:open-sidebar.window="open = true"
    x-on:touchstart.window="
        if ($event.touches[0].clientX < 30 && Math.abs($event.touches[0].clientY) < window.innerHeight) {
            touchStartX = $event.touches[0].clientX;
            touchStartY = $event.touches[0].clientY;
        }
    "
    x-on:touchend.window="
        if (touchStartX > 0) {
            const dx = $event.changedTouches[0].clientX - touchStartX;
            const dy = Math.abs($event.changedTouches[0].clientY - touchStartY);
            if (dx > 60 && dy < 80) { open = true; }
            touchStartX = 0;
            touchStartY = 0;
        }
    "
    class="lg:hidden">
    <div x-show="open" x-transition.opacity x-on:click="open = false" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm"></div>
    <aside x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 z-50 w-[300px] flex flex-col overflow-hidden text-white" style="background: linear-gradient(180deg, #0B3A2A 0%, #06291F 40%, #041B14 100%);">
        <div class="px-6 pt-6 pb-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('assets/images/logo-web.webp') }}" alt="Logo" class="h-14 w-auto" style="filter: drop-shadow(0 4px 12px rgba(16,185,129,0.2));">
                    <div>
                        <p class="text-[15px] font-bold text-white tracking-tight">DLH Kota Palu</p>
                        <p class="text-[10px] text-emerald-400/50 tracking-widest uppercase mt-0.5">Ruang Kendali Admin</p>
                    </div>
                </a>
                <button x-on:click="open = false" class="grid size-10 place-items-center rounded-xl text-white/40 transition-all hover:bg-white/10 hover:text-white">
                    <x-admin.icon name="x" :size="20" />
                </button>
            </div>
        </div>
        <nav class="sidebar-nav flex-1 overflow-y-auto px-4 py-3 space-y-1">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3.5 rounded-2xl px-4 py-3.5 transition-all duration-300 {{ $isDashboard ? 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent text-white border border-emerald-500/15' : 'text-white/50 hover:bg-white/[0.04] hover:text-white/80 border border-transparent' }}">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl {{ $isDashboard ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/25' : 'bg-white/[0.04] text-white/40' }}"><x-admin.icon name="dashboard" :size="22" /></span>
                <span class="text-[13px] font-semibold">Dashboard</span>
            </a>
            @foreach ($groups as $groupKey => $group)
                <div class="pt-3">
                    <div class="flex items-center gap-3 px-2 mb-2.5">
                        <div class="h-px flex-1 bg-gradient-to-r from-white/[0.08] to-transparent"></div>
                        <p class="text-[9px] font-semibold uppercase tracking-[0.14em] text-white/30 shrink-0">{{ $group['label'] }}</p>
                        <div class="h-px flex-1 bg-gradient-to-l from-white/[0.08] to-transparent"></div>
                    </div>
                    <div class="space-y-1">
                        @foreach ($group['items'] as $item)
                            @php
                                $isPetaItem = $item['slug'] === 'peta';
                                $isLink = !empty($item['link']);
                                $isExternalLink = $isLink && !\Illuminate\Support\Str::startsWith($item['link'], '/admin');
                                $url = $isPetaItem ? route('admin.peta.index') : ($isLink ? $item['link'] : route('admin.resources.index', ['resource' => $item['slug']]));
                                $isActive = $isPetaItem ? request()->routeIs('admin.peta.*') : ($isLink ? (request()->path() === ltrim($item['link'], '/')) : $activeResource === $item['slug']);
                            @endphp
                            <a href="{{ $url }}"{{ $isExternalLink ? ' target="_blank" rel="noopener"' : '' }} class="group relative flex items-center gap-3.5 rounded-2xl px-4 py-3 transition-all duration-300 {{ $isActive ? 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent text-white border border-emerald-500/15' : 'text-white/50 hover:bg-white/[0.04] hover:text-white/80 border border-transparent' }}">
                                @if($isActive)<span class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span>@endif
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $isActive ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-white/[0.03] text-white/35' }}"><x-admin.icon :name="$iconMap[$item['slug']] ?? 'folder'" :size="20" /></span>
                                <span class="text-[13px] font-medium truncate flex-1">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Backup Database (Mobile, Superadmin only) --}}
            @if($user->isSuperadmin())
            <div class="pt-3">
                <div class="flex items-center gap-3 px-2 mb-2.5">
                    <div class="h-px flex-1 bg-gradient-to-r from-white/[0.08] to-transparent"></div>
                    <p class="text-[9px] font-semibold uppercase tracking-[0.14em] text-white/30 shrink-0">Sistem</p>
                    <div class="h-px flex-1 bg-gradient-to-l from-white/[0.08] to-transparent"></div>
                </div>
                <div class="space-y-1">
                    @php $isActive = request()->routeIs('admin.backup.*'); @endphp
                    <a href="{{ route('admin.backup.index') }}" class="group relative flex items-center gap-3.5 rounded-2xl px-4 py-3 transition-all duration-300 {{ $isActive ? 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent text-white border border-emerald-500/15' : 'text-white/50 hover:bg-white/[0.04] hover:text-white/80 border border-transparent' }}">
                        @if($isActive)<span class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span>@endif
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $isActive ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-white/[0.03] text-white/35' }}"><x-admin.icon name="database" :size="20" /></span>
                        <span class="text-[13px] font-medium truncate flex-1">Backup Database</span>
                    </a>
                </div>
            </div>
            @endif
        </nav>
        <div class="border-t border-white/[0.06] p-3">
            <div class="flex items-center gap-3 rounded-2xl bg-white/[0.03] border border-white/[0.06] p-3">
                <div class="relative shrink-0">
                    <div class="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 text-[14px] font-bold text-white">{{ substr($user->name, 0, 1) }}</div>
                    <div class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-[2.5px] border-[#06291F] bg-emerald-400"></div>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[13px] font-semibold text-white/90 truncate">{{ $user->name }}</p>
                    <p class="text-[10px] text-emerald-400/40 truncate">{{ $roleName }}</p>
                </div>
            </div>
        </div>
    </aside>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• DESKTOP SIDEBAR â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<aside
    x-data="{ profileOpen: false, searchQuery: '', openSections: {} }"
    x-on:click.away="profileOpen = false"
    x-bind:class="$store.sidebar.collapsed ? 'w-[80px]' : 'w-[300px]'"
    class="hidden lg:flex relative min-h-screen flex-col border-r border-white/[0.06] text-white transition-all duration-300"
    style="background: linear-gradient(180deg, #0B3A2A 0%, #06291F 40%, #041B14 100%); color: #fff;"
>
    <div class="bg-grain pointer-events-none absolute inset-0 opacity-[0.015]" aria-hidden="true"></div>

    {{-- HEADER --}}
    <div class="relative z-10 px-6 pt-6 pb-4" x-bind:class="$store.sidebar.collapsed ? 'px-3 pt-5 pb-3' : ''">
        <div class="flex items-center gap-3" x-bind:class="$store.sidebar.collapsed ? 'justify-center' : ''">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 shrink-0">
                <img src="{{ asset('assets/images/logo-web.webp') }}" alt="Logo" width="320" height="337" class="h-14 w-auto shrink-0" style="filter: drop-shadow(0 4px 12px rgba(16,185,129,0.2));">
                <div x-show="!$store.sidebar.collapsed" x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="min-w-0">
                    <p class="text-[15px] font-bold text-white tracking-tight leading-tight">DLH Kota Palu</p>
                    <p class="text-[10px] text-emerald-400/50 tracking-widest uppercase mt-0.5">Ruang Kendali Admin</p>
                </div>
            </a>
        </div>
        <div x-show="!$store.sidebar.collapsed" x-transition class="mt-4">
            <div class="flex items-center gap-2 rounded-full border border-emerald-500/10 bg-emerald-500/[0.06] px-3 py-1.5">
                <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span></span>
                <span class="text-[10px] font-semibold text-emerald-400/70 tracking-wide uppercase">System Online</span>
            </div>
        </div>
    </div>

    {{-- SEARCH (removed - search is in navbar) --}}

    {{-- NAVIGATION --}}
    <nav class="sidebar-nav relative z-10 flex-1 overflow-y-auto py-3 space-y-1" x-bind:class="$store.sidebar.collapsed ? 'px-2.5' : 'px-3.5'">

        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard') }}" class="group relative flex items-center gap-3.5 rounded-2xl transition-all duration-300 {{ $isDashboard ? 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent text-white shadow-[0_0_20px_rgba(16,185,129,0.1)] border border-emerald-500/15' : 'text-white/50 hover:bg-white/[0.05] hover:text-white/90 border border-transparent hover:border-white/[0.05]' }}" x-bind:class="$store.sidebar.collapsed ? 'justify-center px-0 py-2.5' : 'px-3.5 py-2.5'" title="Dashboard">
            @if($isDashboard)<span x-show="!$store.sidebar.collapsed" class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span>@endif
            <span class="grid size-10 shrink-0 place-items-center rounded-xl transition-all duration-300 {{ $isDashboard ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-white/[0.03] text-white/35 group-hover:bg-white/[0.07] group-hover:text-white/55' }}"><x-admin.icon name="dashboard" :size="20" /></span>
            <span x-show="!$store.sidebar.collapsed" x-transition class="text-[13px] font-medium truncate flex-1">Dashboard</span>
            @if($isDashboard)<span x-show="!$store.sidebar.collapsed" class="size-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.5)]"></span>@endif
        </a>

        {{-- Menu Groups --}}
        @foreach ($groups as $groupKey => $group)
            <div class="pt-3" x-bind:class="$store.sidebar.collapsed ? 'pt-2' : ''">
                <div x-show="!$store.sidebar.collapsed" x-transition class="flex items-center gap-3 px-2 mb-2.5 cursor-pointer select-none" x-on:click="openSections['{{ $groupKey }}'] = openSections['{{ $groupKey }}'] === false ? true : false">
                    <div class="h-px flex-1 bg-gradient-to-r from-white/[0.08] to-transparent"></div>
                    <p class="text-[9px] font-semibold uppercase tracking-[0.14em] text-white/30 shrink-0">{{ $group['label'] }}</p>
                    <div class="h-px flex-1 bg-gradient-to-l from-white/[0.08] to-transparent"></div>
                    <svg class="size-3 text-white/20 transition-transform duration-300 shrink-0" x-bind:class="openSections['{{ $groupKey }}'] === false ? '-rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                </div>
                <div x-show="openSections['{{ $groupKey }}'] !== false" x-collapse class="space-y-0.5">
                    @foreach ($group['items'] as $item)
                        @php
                            $isPetaItem = $item['slug'] === 'peta';
                            $isLink = !empty($item['link']);
                            $isExternalLink = $isLink && !\Illuminate\Support\Str::startsWith($item['link'], '/admin');
                            $url = $isPetaItem ? route('admin.peta.index') : ($isLink ? $item['link'] : route('admin.resources.index', ['resource' => $item['slug']]));
                                $isActive = $isPetaItem ? request()->routeIs('admin.peta.*') : ($isLink ? (request()->path() === ltrim($item['link'], '/')) : $activeResource === $item['slug']);
                        @endphp
                        <a href="{{ $url }}"{{ $isExternalLink ? ' target="_blank" rel="noopener"' : '' }} class="group relative flex items-center gap-3.5 rounded-2xl transition-all duration-300 {{ $isActive ? 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent text-white shadow-[0_0_20px_rgba(16,185,129,0.1)] border border-emerald-500/15' : 'text-white/50 hover:bg-white/[0.05] hover:text-white/90 border border-transparent hover:border-white/[0.05]' }}" x-bind:class="$store.sidebar.collapsed ? 'justify-center px-0 py-2.5' : 'px-3.5 py-2.5'" title="{{ $item['label'] }}">
                            @if($isActive)<span x-show="!$store.sidebar.collapsed" class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span>@endif
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl transition-all duration-300 {{ $isActive ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-white/[0.03] text-white/35 group-hover:bg-white/[0.07] group-hover:text-white/55' }}"><x-admin.icon :name="$iconMap[$item['slug']] ?? 'folder'" :size="20" /></span>
                            <span x-show="!$store.sidebar.collapsed" x-transition class="text-[13px] font-medium truncate flex-1">{{ $item['label'] }}</span>
                            @if($isActive)<span x-show="!$store.sidebar.collapsed" class="size-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.5)]"></span>@endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Backup Database (Superadmin only) --}}
        @if($user->isSuperadmin())
        <div class="pt-3" x-bind:class="$store.sidebar.collapsed ? 'pt-2' : ''">
            @php $isActive = request()->routeIs('admin.backup.*'); @endphp
            <a href="{{ route('admin.backup.index') }}" class="group relative flex items-center gap-3.5 rounded-2xl transition-all duration-300 {{ $isActive ? 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent text-white shadow-[0_0_20px_rgba(16,185,129,0.1)] border border-emerald-500/15' : 'text-white/50 hover:bg-white/[0.05] hover:text-white/90 border border-transparent hover:border-white/[0.05]' }}" x-bind:class="$store.sidebar.collapsed ? 'justify-center px-0 py-2.5' : 'px-3.5 py-2.5'" title="Backup Database">
                @if($isActive)<span x-show="!$store.sidebar.collapsed" class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span>@endif
                <span class="grid size-10 shrink-0 place-items-center rounded-xl transition-all duration-300 {{ $isActive ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-white/[0.03] text-white/35 group-hover:bg-white/[0.07] group-hover:text-white/55' }}"><x-admin.icon name="database" :size="20" /></span>
                <span x-show="!$store.sidebar.collapsed" x-transition class="text-[13px] font-medium truncate flex-1">Backup Database</span>
                @if($isActive)<span x-show="!$store.sidebar.collapsed" class="size-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.5)]"></span>@endif
            </a>
        </div>
        @endif
    </nav>

    {{-- COLLAPSE BUTTON --}}
    <button x-on:click="$store.sidebar.toggle()" style="top: 55%;" class="absolute right-2 z-30 size-6 rounded-full border border-white/10 bg-[#06291F] text-white/40 shadow-lg shadow-black/30 backdrop-blur-sm transition-all duration-300 hover:bg-emerald-600 hover:text-white hover:border-emerald-500/50 hover:shadow-emerald-500/30 hover:shadow-xl hover:scale-110 active:scale-90 flex items-center justify-center" x-bind:title="$store.sidebar.collapsed ? 'Perlebar' : 'Ciutkan'">
        <svg class="size-2.5 transition-transform duration-300" x-bind:class="$store.sidebar.collapsed ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
    </button>

    {{-- USER PROFILE --}}
    <div class="relative z-10 p-3" x-bind:class="$store.sidebar.collapsed ? 'p-2' : 'p-3'">
        <div class="relative rounded-2xl border border-white/[0.06] transition-all duration-300" x-bind:class="$store.sidebar.collapsed ? 'bg-white/[0.02]' : 'bg-white/[0.03] backdrop-blur-sm'">
            <button x-on:click="profileOpen = !profileOpen" class="flex w-full items-center gap-3 rounded-2xl p-3 transition-all duration-300 hover:bg-white/[0.04]" x-bind:class="$store.sidebar.collapsed ? 'justify-center' : ''">
                <div class="relative shrink-0">
                    <div class="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 text-[14px] font-bold text-white shadow-lg shadow-emerald-500/20">{{ substr($user->name, 0, 1) }}</div>
                    <div class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-[2.5px] border-[#06291F] bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></div>
                </div>
                <div x-show="!$store.sidebar.collapsed" x-transition class="min-w-0 flex-1 text-left">
                    <p class="text-[13px] font-semibold text-white/90 truncate">{{ $user->name }}</p>
                    <p class="text-[10px] text-emerald-400/40 truncate">{{ $roleName }}</p>
                </div>
                <svg x-show="!$store.sidebar.collapsed" class="size-4 shrink-0 text-white/25 transition-transform duration-200" x-bind:class="profileOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"/></svg>
            </button>
            <div x-show="profileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute bottom-full left-3 right-3 mb-2 overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0B3A2A]/95 backdrop-blur-xl shadow-2xl shadow-black/40" style="display: none;">
                <div class="px-4 py-3.5 border-b border-white/[0.06]">
                    <p class="text-[13px] font-bold text-white">{{ $user->name }}</p>
                    <p class="text-[11px] text-emerald-400/40 mt-0.5">{{ $user->email }}</p>
                </div>
                <div class="p-1.5">
                    <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium text-white/50 transition-all hover:bg-white/[0.06] hover:text-white/90"><span class="grid size-8 place-items-center rounded-lg bg-emerald-500/10 text-emerald-400/70"><x-admin.icon name="user" :size="16" /></span> Profil Saya</a>
                    <a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium text-white/50 transition-all hover:bg-white/[0.06] hover:text-white/90"><span class="grid size-8 place-items-center rounded-lg bg-white/[0.04] text-white/35"><x-admin.icon name="settings" :size="16" /></span> Pengaturan</a>
                    <a href="{{ route('admin.help.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium text-white/50 transition-all hover:bg-white/[0.06] hover:text-white/90"><span class="grid size-8 place-items-center rounded-lg bg-white/[0.04] text-white/35"><x-admin.icon name="info-circle" :size="16" /></span> Bantuan</a>
                </div>
                <div class="border-t border-white/[0.06] p-1.5">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium text-red-400/60 transition-all hover:bg-red-500/10 hover:text-red-400"><span class="grid size-8 place-items-center rounded-lg bg-red-500/[0.08] text-red-400/60"><x-admin.icon name="logout" :size="16" /></span> Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>

<style>
/* Hide all scrollbars in sidebar */
aside[style*="color: #fff"] { overflow-x: hidden; }
aside[style*="color: #fff"] .sidebar-nav { overflow-y: auto; overflow-x: hidden; -ms-overflow-style: none; scrollbar-width: none; }
aside[style*="color: #fff"] .sidebar-nav::-webkit-scrollbar { display: none; }

/* Fix tooltip position */
aside[style*="color: #fff"] .sidebar-nav a[title]:hover::after {
    content: attr(title);
    position: fixed;
    left: 80px;
    background: linear-gradient(135deg, #0B3A2A, #06291F);
    border: 1px solid rgba(255,255,255,0.1);
    color: white;
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
    z-index: 100;
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    pointer-events: none;
}

/* Active icon glow */
aside[style*="color: #fff"] .sidebar-nav a .grid.rounded-xl.bg-gradient-to-br {
    box-shadow: 0 0 16px rgba(16,185,129,0.35), 0 4px 12px rgba(16,185,129,0.2);
}
</style>
