@props(['user' => null])

@php
    $isSuperadmin = \App\Support\AdminAccess::isSuperadmin($user);
    $menuItems = [];

    foreach (\App\Support\Admin\AdminRegistry::forUser($user) as $groupKey => $group) {
        foreach ($group['items'] as $item) {
            $slug = $item['slug'] ?? null;
            if (! $slug || ($slug === 'user' && ! $isSuperadmin)) {
                continue;
            }

            $menuItems[] = [
                'id' => 'resource-' . $slug,
                'label' => $item['label'],
                'url' => $item['link'] ?? route('admin.resources.index', ['resource' => $slug]),
                'group' => $group['label'],
                'icon' => $item['icon'] ?? $group['icon'] ?? 'folder',
            ];
        }
    }

    // Akses peta mengikuti controller: superadmin atau role dengan grup
    // Sampah & LB3 penuh. Akses tambahan terhadap satu slug tidak cukup.
    if ($isSuperadmin || in_array('sampah-lb3', $user?->allowedGroups() ?? [], true)) {
        $menuItems[] = [
            'id' => 'system-peta',
            'label' => 'Peta',
            'url' => route('admin.peta.index'),
            'group' => 'Sistem',
            'icon' => 'map',
        ];
    }

    $systemPages = [
        ['id' => 'system-dashboard', 'label' => 'Dashboard', 'url' => route('admin.dashboard'), 'group' => 'Sistem', 'icon' => 'dashboard'],
        ['id' => 'system-profile', 'label' => 'Profil Saya', 'url' => route('admin.profile.edit'), 'group' => 'Sistem', 'icon' => 'user'],
        ['id' => 'system-settings', 'label' => 'Pengaturan', 'url' => route('admin.settings.edit'), 'group' => 'Sistem', 'icon' => 'settings'],
        ['id' => 'system-notifications', 'label' => 'Notifikasi', 'url' => route('admin.notifications.index'), 'group' => 'Sistem', 'icon' => 'bell'],
        ['id' => 'system-help', 'label' => 'Bantuan', 'url' => route('admin.help.index'), 'group' => 'Sistem', 'icon' => 'info-circle'],
    ];

    if ($isSuperadmin) {
        $systemPages[] = ['id' => 'system-backup', 'label' => 'Backup Database', 'url' => route('admin.backup.index'), 'group' => 'Sistem', 'icon' => 'database'];
        $systemPages[] = ['id' => 'system-activity-log', 'label' => 'Log Aktivitas', 'url' => route('admin.activity-log.index'), 'group' => 'Sistem', 'icon' => 'clipboard-list'];
    }

    $allItems = collect(array_merge($systemPages, $menuItems))
        ->unique('id')
        ->values()
        ->all();
@endphp

<div
    x-data="{
        open: false,
        query: '',
        selectedIndex: 0,
        lastFocused: null,
        allItems: @js($allItems),
        get filteredItems() {
            const query = this.query.trim().toLowerCase();
            const items = query
                ? this.allItems.filter((item) => item.label.toLowerCase().includes(query) || item.group.toLowerCase().includes(query))
                : this.allItems;

            return items.slice(0, 12);
        },
        get groupedItems() {
            return this.filteredItems.reduce((groups, item) => {
                (groups[item.group] ||= []).push(item);
                return groups;
            }, {});
        },
        get activeItem() {
            return this.filteredItems[this.selectedIndex] || null;
        },
        itemIndex(item) {
            return this.filteredItems.findIndex((candidate) => candidate.id === item.id);
        },
        openPalette() {
            if (!this.open) this.lastFocused = document.activeElement;
            this.open = true;
            this.query = '';
            this.selectedIndex = 0;
            this.$nextTick(() => this.$refs.cmdInput?.focus());
        },
        close() {
            if (!this.open) return;
            this.open = false;
            this.query = '';
            const trigger = this.lastFocused;
            this.$nextTick(() => trigger?.focus?.());
        },
        moveUp() {
            if (!this.filteredItems.length) return;
            this.selectedIndex = Math.max(0, this.selectedIndex - 1);
        },
        moveDown() {
            if (!this.filteredItems.length) return;
            this.selectedIndex = Math.min(this.filteredItems.length - 1, this.selectedIndex + 1);
        },
        selectCurrent() {
            const item = this.activeItem;
            if (item) window.location.assign(item.url);
        },
        trapFocus(event) {
            window.dlhTrapFocus?.(event, this.$refs.palette);
        }
    }"
    x-on:open-command-palette.window="openPalette()"
    x-on:keydown.escape.window="if (open) close()"
    x-on:keydown.tab.window="if (open) trapFocus($event)"
    x-on:keydown.arrow-up="if (open && $event.target === $refs.cmdInput) { $event.preventDefault(); moveUp(); }"
    x-on:keydown.arrow-down="if (open && $event.target === $refs.cmdInput) { $event.preventDefault(); moveDown(); }"
    x-on:keydown.enter="if (open && $event.target === $refs.cmdInput) { $event.preventDefault(); selectCurrent(); }"
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
        x-on:click="close()"
        class="cmd-overlay fixed inset-0 z-[90]"
        aria-hidden="true"
    ></div>

    <section
        id="admin-command-palette"
        x-show="open"
        x-cloak
        x-ref="palette"
        x-transition:enter="transition-[opacity,transform] ease-out duration-200"
        x-transition:enter-start="translate-y-3 scale-95 opacity-0"
        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
        x-transition:leave="transition-[opacity,transform] ease-in duration-150"
        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
        x-transition:leave-end="translate-y-3 scale-95 opacity-0"
        x-on:click.stop
        class="fixed left-1/2 top-[max(4rem,15vh)] z-[100] w-[min(36rem,calc(100vw-2rem))] -translate-x-1/2 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-2xl dark:border-white/[.08] dark:bg-[#0a1f17]"
        role="dialog"
        aria-modal="true"
        aria-labelledby="admin-command-palette-title"
    >
        <h2 id="admin-command-palette-title" class="sr-only">Pencarian cepat</h2>

        <div class="flex items-center gap-3 border-b border-slate-100 px-4 dark:border-white/[.06]">
            <x-admin.icon name="search" :size="20" class="shrink-0 text-slate-500 dark:text-slate-400" />
            <label class="sr-only" for="admin-command-input">Cari menu atau halaman</label>
            <input
                id="admin-command-input"
                x-ref="cmdInput"
                type="search"
                x-model="query"
                x-on:input="selectedIndex = 0"
                x-bind:aria-activedescendant="activeItem ? 'admin-command-item-' + activeItem.id : null"
                x-bind:aria-expanded="open"
                role="combobox"
                aria-autocomplete="list"
                aria-haspopup="listbox"
                aria-controls="admin-command-results"
                placeholder="Cari menu atau halaman..."
                class="h-14 min-w-0 flex-1 bg-transparent text-[15px] font-medium text-slate-800 placeholder:text-slate-400 focus:outline-none dark:text-white dark:placeholder:text-slate-500"
            />
            <button type="button" x-on:click="close()" class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] font-semibold text-slate-500 transition-colors duration-150 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 dark:border-white/[.1] dark:bg-white/[.06] dark:text-slate-400 dark:hover:bg-white/[.1]" aria-label="Tutup pencarian cepat">
                ESC
            </button>
        </div>

        <div id="admin-command-results" class="max-h-[22.5rem] overflow-y-auto p-2" role="listbox" aria-label="Hasil pencarian">
            <template x-if="filteredItems.length === 0">
                <div class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-300">Tidak ada hasil ditemukan.</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Coba gunakan kata kunci lain.</p>
                </div>
            </template>

            <template x-for="(items, groupName) in groupedItems" :key="groupName">
                <div class="mb-2">
                    <p class="px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400" x-text="groupName"></p>
                    <template x-for="item in items" :key="item.id">
                        <a
                            :id="'admin-command-item-' + item.id"
                            :href="item.url"
                            role="option"
                            :aria-selected="selectedIndex === itemIndex(item)"
                            class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-[background-color,color] duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-emerald-500/50"
                            :class="selectedIndex === itemIndex(item) ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/[.05]'"
                            x-on:mouseenter="selectedIndex = itemIndex(item)"
                            x-on:focus="selectedIndex = itemIndex(item)"
                        >
                            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-500 dark:bg-white/[.06] dark:text-slate-400" aria-hidden="true">
                                <x-admin.icon name="folder" :size="16" />
                            </span>
                            <span class="min-w-0 flex-1 truncate" x-text="item.label"></span>
                            <span class="shrink-0 text-[11px] text-slate-500 dark:text-slate-400" x-text="item.group"></span>
                        </a>
                    </template>
                </div>
            </template>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-slate-100 px-4 py-2.5 dark:border-white/[.06]">
            <div class="flex items-center gap-3 text-[11px] text-slate-500 dark:text-slate-400">
                <span class="flex items-center gap-1"><kbd class="rounded border border-slate-200 bg-slate-50 px-1 py-0.5 text-[10px] dark:border-white/[.1] dark:bg-white/[.06]">↑ ↓</kbd>Navigasi</span>
                <span class="flex items-center gap-1"><kbd class="rounded border border-slate-200 bg-slate-50 px-1 py-0.5 text-[10px] dark:border-white/[.1] dark:bg-white/[.06]">↵</kbd>Pilih</span>
            </div>
            <span class="hidden text-[10px] text-slate-400 dark:text-slate-500 sm:inline">DLH Kota Palu</span>
        </div>
    </section>
</div>
