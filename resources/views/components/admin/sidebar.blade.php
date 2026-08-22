@props(['groups', 'allGroups', 'user'])

@php
    $activeResource = request()->route('resource');
    $isDashboard = request()->routeIs('admin.dashboard');
    $isSuperadmin = \App\Support\AdminAccess::isSuperadmin($user);

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
        'user' => 'user-check',
        'website-settings' => 'settings',
        'peta' => 'map',
        'whatsapp' => 'whatsapp',
    ];

    // Peta memakai otorisasi yang sama dengan controllernya: superadmin atau
    // role yang memiliki grup Sampah & LB3 penuh (bukan sekadar akses slug).
    $hasPetaAccess = $isSuperadmin || in_array('sampah-lb3', $user->allowedGroups(), true);
    if ($hasPetaAccess && isset($groups['sampah-lb3'])) {
        $groups['sampah-lb3']['items'][] = ['slug' => 'peta', 'label' => 'Peta'];
    }
@endphp

{{-- Sidebar mobile --}}
<div
    class="admin-sidebar-mobile lg:hidden"
    x-data="{
        open: false,
        touchStartX: 0,
        touchStartY: 0,
        lastFocused: null,
        openSidebar(trigger = null) {
            this.lastFocused = trigger instanceof HTMLElement ? trigger : document.activeElement;
            this.open = true;
            this.$nextTick(() => this.$refs.closeButton?.focus());
        },
        closeSidebar() {
            this.open = false;
            const trigger = this.lastFocused;
            this.$nextTick(() => trigger?.focus?.());
        },
        trapFocus(event) {
            window.dlhTrapFocus?.(event, this.$refs.panel);
        }
    }"
    x-on:keydown.escape.window="if (open) closeSidebar()"
    x-on:keydown.tab.window="if (open) trapFocus($event)"
    x-on:open-sidebar.window="openSidebar($event.detail && $event.detail.trigger)"
    x-on:touchstart.window="
        if (!open && $event.touches[0].clientX < 30) {
            touchStartX = $event.touches[0].clientX;
            touchStartY = $event.touches[0].clientY;
        }
    "
    x-on:touchend.window="
        if (touchStartX > 0) {
            const deltaX = $event.changedTouches[0].clientX - touchStartX;
            const deltaY = Math.abs($event.changedTouches[0].clientY - touchStartY);
            if (deltaX > 60 && deltaY < 80) openSidebar();
            touchStartX = 0;
            touchStartY = 0;
        }
    "
>
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click="closeSidebar()"
        class="fixed inset-0 z-[70] bg-slate-950/50 backdrop-blur-sm"
        aria-hidden="true"
    ></div>

    <aside
        id="admin-mobile-sidebar"
        x-show="open"
        x-cloak
        x-ref="panel"
        x-transition:enter="transition-[opacity,transform] ease-out duration-200"
        x-transition:enter-start="-translate-x-full opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transition-[opacity,transform] ease-in duration-150"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="-translate-x-full opacity-0"
        class="admin-sidebar fixed inset-y-0 left-0 z-[80] flex w-[min(19rem,calc(100vw-2rem))] flex-col overflow-hidden text-white"
        style="background: linear-gradient(180deg, #0B3A2A 0%, #06291F 40%, #041B14 100%);"
        role="dialog"
        aria-modal="true"
        aria-label="Navigasi administrasi"
    >
        <header class="flex items-center justify-between gap-3 px-5 pb-4 pt-5">
            <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-3" aria-label="Dashboard DLH Kota Palu">
                <img src="{{ asset('assets/images/logo-web.webp') }}" alt="Logo DLH Kota Palu" width="320" height="337" class="h-12 w-auto shrink-0" style="filter: drop-shadow(0 4px 12px rgba(16,185,129,0.2));">
                <div class="min-w-0">
                    <p class="truncate text-[15px] font-bold tracking-tight text-white">DLH Kota Palu</p>
                    <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-emerald-300/65">Ruang Kendali</p>
                </div>
            </a>
            <button
                x-ref="closeButton"
                type="button"
                x-on:click="closeSidebar()"
                class="inline-grid size-10 shrink-0 place-items-center rounded-xl text-white/70 transition-colors duration-150 hover:bg-white/10 hover:text-white"
                aria-label="Tutup navigasi"
            >
                <x-admin.icon name="x" :size="20" />
            </button>
        </header>

        <nav class="sidebar-nav flex-1 overflow-y-auto px-3 pb-4" aria-label="Menu administrasi">
            <a
                href="{{ route('admin.dashboard') }}"
                class="group relative flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 transition-colors duration-150 {{ $isDashboard ? 'border border-emerald-400/20 bg-emerald-400/15 text-white' : 'border border-transparent text-white/70 hover:bg-white/[0.07] hover:text-white' }}"
                @if($isDashboard) aria-current="page" @endif
            >
                <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $isDashboard ? 'bg-emerald-400 text-emerald-950' : 'bg-white/[0.07] text-emerald-200' }}">
                    <x-admin.icon name="dashboard" :size="19" />
                </span>
                <span class="text-[13px] font-semibold">Dashboard</span>
            </a>

            @foreach ($groups as $groupKey => $group)
                <section class="pt-5" aria-labelledby="mobile-sidebar-group-{{ $groupKey }}">
                    <p id="mobile-sidebar-group-{{ $groupKey }}" class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-white/45">
                        {{ $group['label'] }}
                    </p>
                    <div class="space-y-1">
                        @foreach ($group['items'] as $item)
                            @php
                                $isPetaItem = ($item['slug'] ?? null) === 'peta';
                                $isLink = ! empty($item['link']);
                                $isExternalLink = $isLink && ! \Illuminate\Support\Str::startsWith($item['link'], '/'.trim((string) config('app.admin_path'), '/'));
                                $url = $isPetaItem
                                    ? route('admin.peta.index')
                                    : ($isLink ? $item['link'] : route('admin.resources.index', ['resource' => $item['slug']]));
                                $isActive = $isPetaItem
                                    ? request()->routeIs('admin.peta.*')
                                    : ($isLink ? request()->path() === ltrim($item['link'], '/') : $activeResource === $item['slug']);
                            @endphp
                            <a
                                href="{{ $url }}"
                                @if($isExternalLink) target="_blank" rel="noopener" @endif
                                x-on:click="open = false"
                                class="group relative flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 transition-colors duration-150 {{ $isActive ? 'border border-emerald-400/20 bg-emerald-400/15 text-white' : 'border border-transparent text-white/70 hover:bg-white/[0.07] hover:text-white' }}"
                                aria-label="{{ $item['label'] }}{{ $isExternalLink ? ' (membuka tab baru)' : '' }}"
                                @if($isActive) aria-current="page" @endif
                            >
                                <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $isActive ? 'bg-emerald-400 text-emerald-950' : 'bg-white/[0.07] text-emerald-200' }}">
                                    <x-admin.icon :name="$iconMap[$item['slug']] ?? 'folder'" :size="18" />
                                </span>
                                <span class="min-w-0 flex-1 truncate text-[13px] font-medium">{{ $item['label'] }}</span>
                                @if($isExternalLink)
                                    <x-admin.icon name="external-link" :size="14" class="shrink-0 opacity-70" />
                                @endif
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach

            @if($isSuperadmin)
                @php $isBackupActive = request()->routeIs('admin.backup.*'); @endphp
                <section class="pt-2" aria-labelledby="mobile-sidebar-system">
                    <p id="mobile-sidebar-system" class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-white/45">Sistem</p>
                    <a
                        href="{{ route('admin.backup.index') }}"
                        class="group relative flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 transition-colors duration-150 {{ $isBackupActive ? 'border border-emerald-400/20 bg-emerald-400/15 text-white' : 'border border-transparent text-white/70 hover:bg-white/[0.07] hover:text-white' }}"
                        @if($isBackupActive) aria-current="page" @endif
                    >
                        <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $isBackupActive ? 'bg-emerald-400 text-emerald-950' : 'bg-white/[0.07] text-emerald-200' }}">
                            <x-admin.icon name="database" :size="18" />
                        </span>
                        <span class="text-[13px] font-medium">Backup Database</span>
                    </a>
                </section>
            @endif
        </nav>

    </aside>
</div>

{{-- Sidebar desktop --}}
<aside
    class="admin-sidebar relative hidden min-h-screen flex-col border-r border-white/[0.08] text-white transition-[width] duration-200 lg:flex"
    style="background: linear-gradient(180deg, #0B3A2A 0%, #06291F 40%, #041B14 100%);"
    x-data="{ openSections: {} }"
    x-bind:class="$store.sidebar.collapsed ? 'w-[80px]' : 'w-[300px]'"
    aria-label="Navigasi administrasi"
>
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(52,211,153,.12),transparent_36%)]" aria-hidden="true"></div>

    <header class="relative z-10 px-5 pb-4 pt-5" x-bind:class="$store.sidebar.collapsed ? 'px-3' : ''">
        <a
            href="{{ route('admin.dashboard') }}"
            class="flex items-center gap-3 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300"
            x-bind:class="$store.sidebar.collapsed ? 'justify-center' : ''"
            aria-label="Dashboard DLH Kota Palu"
        >
            <img src="{{ asset('assets/images/logo-web.webp') }}" alt="Logo DLH Kota Palu" width="320" height="337" class="h-12 w-auto shrink-0" style="filter: drop-shadow(0 4px 12px rgba(16,185,129,0.2));">
            <div x-show="!$store.sidebar.collapsed" x-cloak x-transition:enter="transition-opacity ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="min-w-0">
                <p class="text-[15px] font-bold leading-tight tracking-tight text-white">DLH Kota Palu</p>
                <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-emerald-300/65">Ruang Kendali</p>
            </div>
        </a>
    </header>

    <nav class="sidebar-nav relative z-10 flex-1 overflow-y-auto px-3 pb-4" x-bind:class="$store.sidebar.collapsed ? 'px-2' : 'px-3'" aria-label="Menu administrasi">
        <a
            href="{{ route('admin.dashboard') }}"
            class="group relative flex min-h-11 items-center gap-3 rounded-xl py-2.5 transition-colors duration-150 {{ $isDashboard ? 'border border-emerald-400/20 bg-emerald-400/15 text-white' : 'border border-transparent text-white/70 hover:bg-white/[0.07] hover:text-white' }}"
            x-bind:class="$store.sidebar.collapsed ? 'justify-center px-0' : 'px-3'"
            aria-label="Dashboard"
            title="Dashboard"
            @if($isDashboard) aria-current="page" @endif
        >
            <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $isDashboard ? 'bg-emerald-400 text-emerald-950' : 'bg-white/[0.07] text-emerald-200' }}">
                <x-admin.icon name="dashboard" :size="18" />
            </span>
            <span x-show="!$store.sidebar.collapsed" x-cloak class="min-w-0 flex-1 truncate text-[13px] font-semibold">Dashboard</span>
        </a>

        @foreach ($groups as $groupKey => $group)
            <section class="pt-4" x-bind:class="$store.sidebar.collapsed ? 'pt-2' : 'pt-4'">
                <button
                    type="button"
                    x-show="!$store.sidebar.collapsed"
                    x-cloak
                    x-on:click="openSections['{{ $groupKey }}'] = openSections['{{ $groupKey }}'] === false"
                    x-bind:aria-expanded="openSections['{{ $groupKey }}'] !== false"
                    aria-controls="desktop-sidebar-group-{{ $groupKey }}"
                    class="flex min-h-8 w-full items-center gap-2 rounded-lg px-2 py-1 text-left text-[10px] font-semibold uppercase tracking-[0.14em] text-white/45 transition-colors duration-150 hover:bg-white/[0.06] hover:text-white/75"
                >
                    <span class="min-w-0 flex-1 truncate">{{ $group['label'] }}</span>
                    <x-admin.icon name="chevron-down" :size="14" class="shrink-0 transition-transform duration-150" x-bind:class="openSections['{{ $groupKey }}'] === false ? '-rotate-90' : ''" />
                </button>

                <div id="desktop-sidebar-group-{{ $groupKey }}" x-show="openSections['{{ $groupKey }}'] !== false" x-collapse class="space-y-1" x-bind:class="$store.sidebar.collapsed ? 'pt-0' : 'pt-1'">
                    @foreach ($group['items'] as $item)
                        @php
                            $isPetaItem = ($item['slug'] ?? null) === 'peta';
                            $isLink = ! empty($item['link']);
                            $isExternalLink = $isLink && ! \Illuminate\Support\Str::startsWith($item['link'], '/'.trim((string) config('app.admin_path'), '/'));
                            $url = $isPetaItem
                                ? route('admin.peta.index')
                                : ($isLink ? $item['link'] : route('admin.resources.index', ['resource' => $item['slug']]));
                            $isActive = $isPetaItem
                                ? request()->routeIs('admin.peta.*')
                                : ($isLink ? request()->path() === ltrim($item['link'], '/') : $activeResource === $item['slug']);
                        @endphp
                        <a
                            href="{{ $url }}"
                            @if($isExternalLink) target="_blank" rel="noopener" @endif
                            class="group relative flex min-h-11 items-center gap-3 rounded-xl py-2.5 transition-colors duration-150 {{ $isActive ? 'border border-emerald-400/20 bg-emerald-400/15 text-white' : 'border border-transparent text-white/70 hover:bg-white/[0.07] hover:text-white' }}"
                            x-bind:class="$store.sidebar.collapsed ? 'justify-center px-0' : 'px-3'"
                            aria-label="{{ $item['label'] }}{{ $isExternalLink ? ' (membuka tab baru)' : '' }}"
                            title="{{ $item['label'] }}"
                            @if($isActive) aria-current="page" @endif
                        >
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $isActive ? 'bg-emerald-400 text-emerald-950' : 'bg-white/[0.07] text-emerald-200' }}">
                                <x-admin.icon :name="$iconMap[$item['slug']] ?? 'folder'" :size="18" />
                            </span>
                            <span x-show="!$store.sidebar.collapsed" x-cloak class="min-w-0 flex-1 truncate text-[13px] font-medium">{{ $item['label'] }}</span>
                            @if($isExternalLink)
                                <x-admin.icon x-show="!$store.sidebar.collapsed" x-cloak name="external-link" :size="14" class="shrink-0 opacity-70" />
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach

            @if($isSuperadmin)
                @php $isBackupActive = request()->routeIs('admin.backup.*'); @endphp
                <section class="pt-2" x-bind:class="$store.sidebar.collapsed ? 'pt-1' : 'pt-2'">
                <a
                    href="{{ route('admin.backup.index') }}"
                    class="group relative flex min-h-11 items-center gap-3 rounded-xl py-2.5 transition-colors duration-150 {{ $isBackupActive ? 'border border-emerald-400/20 bg-emerald-400/15 text-white' : 'border border-transparent text-white/70 hover:bg-white/[0.07] hover:text-white' }}"
                    x-bind:class="$store.sidebar.collapsed ? 'justify-center px-0' : 'px-3'"
                    aria-label="Backup Database"
                    title="Backup Database"
                    @if($isBackupActive) aria-current="page" @endif
                >
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $isBackupActive ? 'bg-emerald-400 text-emerald-950' : 'bg-white/[0.07] text-emerald-200' }}">
                        <x-admin.icon name="database" :size="18" />
                    </span>
                    <span x-show="!$store.sidebar.collapsed" x-cloak class="min-w-0 flex-1 truncate text-[13px] font-medium">Backup Database</span>
                </a>
            </section>
        @endif
    </nav>

    <button
        type="button"
        x-on:click="$store.sidebar.toggle()"
        x-bind:aria-label="$store.sidebar.collapsed ? 'Perlebar sidebar' : 'Ciutkan sidebar'"
        x-bind:title="$store.sidebar.collapsed ? 'Perlebar sidebar' : 'Ciutkan sidebar'"
        x-bind:aria-expanded="!$store.sidebar.collapsed"
        class="absolute right-2 top-1/2 z-30 inline-grid size-8 -translate-y-1/2 place-items-center rounded-full border border-white/15 bg-[#073124] text-white/75 shadow-lg shadow-black/20 transition-[background-color,border-color,color,transform] duration-150 hover:border-emerald-300/60 hover:bg-emerald-500 hover:text-emerald-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-200"
    >
        <x-admin.icon name="chevron-left" :size="16" class="transition-transform duration-150" x-bind:class="$store.sidebar.collapsed ? 'rotate-180' : ''" />
    </button>

</aside>
