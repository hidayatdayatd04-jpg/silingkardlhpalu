@props(['user' => null])

@php
    $allGroups = \App\Support\Admin\AdminRegistry::all();
    $allowedGroups = $user->allowedGroups();
    $allowedSlugs = $user->allowedSlugs();
    $menuItems = [];
    foreach ($allGroups as $groupKey => $group) {
        $hasFullGroup = in_array($groupKey, $allowedGroups, true);
        foreach ($group['items'] as $item) {
            $slug = $item['slug'] ?? null;
            if (! $slug) {
                continue;
            }
            if (! $hasFullGroup && ! in_array($slug, $allowedSlugs, true)) {
                continue;
            }
            $menuItems[] = [
                'label' => $item['label'],
                'url' => $item['link'] ?? route('admin.resources.index', $item['slug']),
                'group' => $group['label'],
                'icon' => str_starts_with($slug, 'artikel') ? 'news' : ($slug === 'user' ? 'user-check' : 'folder'),
            ];
        }
    }
    // Add system pages
    $systemPages = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard'), 'group' => 'Sistem', 'icon' => 'dashboard'],
        ['label' => 'Profil Saya', 'url' => route('admin.profile.edit'), 'group' => 'Sistem', 'icon' => 'user'],
        ['label' => 'Pengaturan', 'url' => route('admin.settings.edit'), 'group' => 'Sistem', 'icon' => 'settings'],
        ['label' => 'Notifikasi', 'url' => route('admin.notifications.index'), 'group' => 'Sistem', 'icon' => 'bell'],
        ['label' => 'Bantuan', 'url' => route('admin.help.index'), 'group' => 'Sistem', 'icon' => 'info-circle'],
    ];
    if ($user->isSuperadmin()) {
        $systemPages[] = ['label' => 'Log Aktivitas', 'url' => route('admin.activity-log.index'), 'group' => 'Sistem', 'icon' => 'clipboard-list'];
    }
    $allItems = array_merge($systemPages, $menuItems);
@endphp

<div
    x-data="{
        open: false,
        query: '',
        selectedIndex: 0,
        allItems: @js($allItems),
        get filteredItems() {
            if (!this.query) return this.allItems.slice(0, 10);
            const q = this.query.toLowerCase();
            return this.allItems.filter(item =>
                item.label.toLowerCase().includes(q) ||
                item.group.toLowerCase().includes(q)
            ).slice(0, 12);
        },
        get groupedItems() {
            const groups = {};
            this.filteredItems.forEach(item => {
                if (!groups[item.group]) groups[item.group] = [];
                groups[item.group].push(item);
            });
            return groups;
        },
        openPalette() { this.open = true; this.query = ''; this.selectedIndex = 0; this.$nextTick(() => this.$refs.cmdInput?.focus()); },
        close() { this.open = false; this.query = ''; },
        moveUp() { this.selectedIndex = Math.max(0, this.selectedIndex - 1); },
        moveDown() { this.selectedIndex = Math.min(this.filteredItems.length - 1, this.selectedIndex + 1); },
        selectCurrent() {
            const item = this.filteredItems[this.selectedIndex];
            if (item) window.location.href = item.url;
        }
    }"
    x-on:open-command-palette.window="openPalette()"
    x-on:keydown.escape.window="if (open) close()"
    x-on:keydown.arrow-up.prevent="if (open) moveUp()"
    x-on:keydown.arrow-down.prevent="if (open) moveDown()"
    x-on:keydown.enter.prevent="if (open) selectCurrent()"
>
    {{-- Overlay --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="cmd-overlay fixed inset-0 z-[9999]"
        style="display: none;"
        x-on:click="close()"
    ></div>

    {{-- Palette --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        class="fixed left-1/2 top-[15%] z-[10000] w-full max-w-[580px] -translate-x-1/2 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-2xl dark:border-white/[.08] dark:bg-[#0a1f17]"
        style="display: none;"
        x-on:click.stop
    >
        {{-- Search input --}}
        <div class="flex items-center gap-3 border-b border-slate-100 px-5 dark:border-white/[.06]">
            <x-admin.icon name="search" :size="20" class="shrink-0 text-slate-400 dark:text-slate-500" />
            <input
                x-ref="cmdInput"
                type="text"
                x-model="query"
                placeholder="Cari menu, halaman, pengguna..."
                class="h-14 flex-1 bg-transparent text-[15px] font-medium text-slate-800 outline-none placeholder:text-slate-400 dark:text-white dark:placeholder:text-slate-500"
                x-on:input="selectedIndex = 0"
            />
            <kbd class="shrink-0 rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] font-semibold text-slate-400 dark:border-white/[.1] dark:bg-white/[.06] dark:text-slate-500">ESC</kbd>
        </div>

        {{-- Results --}}
        <div class="max-h-[360px] overflow-y-auto p-2">
            <template x-if="filteredItems.length === 0">
                <div class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-slate-400 dark:text-slate-500">Tidak ada hasil ditemukan</p>
                </div>
            </template>

            <template x-for="(items, groupName) in groupedItems" :key="groupName">
                <div class="mb-2">
                    <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500" x-text="groupName"></p>
                    <template x-for="(item, idx) in items" :key="item.url">
                        <a
                            :href="item.url"
                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-100"
                            :class="selectedIndex === allItems.indexOf(item) ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-white/[.04]'"
                            x-on:mouseenter="selectedIndex = allItems.indexOf(item)"
                        >
                            <x-admin.icon :name="'folder'" :size="16" class="opacity-50" />
                            <span x-text="item.label"></span>
                            <span class="ml-auto text-[11px] text-slate-400 dark:text-slate-600" x-text="item.group"></span>
                        </a>
                    </template>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between border-t border-slate-100 px-5 py-2.5 dark:border-white/[.06]">
            <div class="flex items-center gap-3 text-[11px] text-slate-400 dark:text-slate-500">
                <span class="flex items-center gap-1">
                    <kbd class="rounded border border-slate-200 bg-slate-50 px-1 py-0.5 text-[10px] dark:border-white/[.1] dark:bg-white/[.06]">&uarr;&darr;</kbd>
                    Navigasi
                </span>
                <span class="flex items-center gap-1">
                    <kbd class="rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] dark:border-white/[.1] dark:bg-white/[.06]">&#9166;</kbd>
                    Pilih
                </span>
            </div>
            <span class="text-[10px] text-slate-300 dark:text-slate-600">Powered by DLH Palu</span>
        </div>
    </div>
</div>
