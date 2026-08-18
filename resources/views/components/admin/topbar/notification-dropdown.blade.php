<div
    class="relative"
    x-data="{
        open: false,
        count: {{ (int) ($notificationCount ?? 0) }},
        items: {{ Illuminate\Support\Js::from($notifications ?? collect()) }},
        csrf: '{{ csrf_token() }}',
        colorMap: {
            emerald: { bg: '#ecfdf5', text: '#047857', border: '#a7f3d0' },
            sky: { bg: '#f0f9ff', text: '#0369a1', border: '#bae6fd' },
            amber: { bg: '#fffbeb', text: '#b45309', border: '#fde68a' },
            rose: { bg: '#fff1f2', text: '#be123c', border: '#fecdd3' },
            indigo: { bg: '#eef2ff', text: '#4338ca', border: '#c7d2fe' },
            teal: { bg: '#f0fdfa', text: '#0f766e', border: '#99f6e4' },
            purple: { bg: '#faf5ff', text: '#6d28d9', border: '#e9d5ff' },
            red: { bg: '#fef2f2', text: '#b91c1c', border: '#fecaca' },
            blue: { bg: '#eff6ff', text: '#1d4ed8', border: '#bfdbfe' },
            orange: { bg: '#fff7ed', text: '#c2410c', border: '#fed7aa' },
            slate: { bg: '#f8fafc', text: '#475569', border: '#e2e8f0' },
            green: { bg: '#f0fdf4', text: '#15803d', border: '#bbf7d0' },
            pink: { bg: '#fdf2f8', text: '#be185d', border: '#fbcfe8' },
        },
        getIconStyle(color) {
            const palette = this.colorMap[color] || this.colorMap.slate;
            return 'background:' + palette.bg + ';color:' + palette.text + ';border:1px solid ' + palette.border;
        },
        async refresh() {
            try {
                const response = await fetch('{{ route('admin.notifications.poll') }}', { headers: { Accept: 'application/json' } });
                if (!response.ok) return;
                const data = await response.json();
                this.count = data.unread;
                this.items = data.notifications;
            } catch (error) {
                // Polling bersifat tambahan; UI yang sudah dirender tetap dipakai.
            }
        },
        async markRead(id) {
            try {
                const response = await fetch('{{ url('admin/notifications') }}/' + id + '/read', {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': this.csrf }
                });
                if (!response.ok) return;
                const data = await response.json();
                this.count = data.unread;
                await this.refresh();
            } catch (error) {
                // Navigasi ke notifikasi tetap boleh dilanjutkan saat request gagal.
            }
        },
        async markAllRead() {
            try {
                const response = await fetch('{{ route('admin.notifications.read-all') }}', {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': this.csrf }
                });
                if (!response.ok) return;
                this.count = 0;
                await this.refresh();
            } catch (error) {
                // Tidak mengubah daftar lokal apabila server tidak dapat dijangkau.
            }
        }
    }"
    x-init="setInterval(() => refresh(), 30000)"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
>
    <button
        type="button"
        x-on:click="open = !open; if (open) refresh()"
        x-bind:aria-expanded="open"
        aria-controls="admin-notification-panel"
        class="topbar-btn relative"
        :title="count > 0 ? 'Notifikasi (' + count + ' belum dibaca)' : 'Notifikasi'"
        :aria-label="count > 0 ? 'Notifikasi, ' + count + ' belum dibaca' : 'Notifikasi'"
    >
        <x-admin.icon name="bell" :size="20" />
        <template x-if="count > 0">
            <span class="absolute -right-0.5 -top-0.5 flex size-5 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white shadow-sm" aria-hidden="true">
                <span class="relative" x-text="count > 9 ? '9+' : count"></span>
            </span>
        </template>
    </button>

    <section
        id="admin-notification-panel"
        x-show="open"
        x-cloak
        x-transition:enter="transition-[opacity,transform] ease-out duration-200"
        x-transition:enter-start="-translate-y-1 scale-95 opacity-0"
        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
        x-transition:leave="transition-[opacity,transform] ease-in duration-150"
        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
        x-transition:leave-end="-translate-y-1 scale-95 opacity-0"
        class="glass-dropdown absolute right-0 top-full z-[60] mt-2 w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-xl"
        aria-label="Daftar notifikasi"
    >
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3.5 dark:border-white/[.06]">
            <div class="flex min-w-0 items-center gap-2">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Notifikasi</h2>
                <template x-if="count > 0">
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300" x-text="count"></span>
                </template>
            </div>
            <button
                type="button"
                x-show="count > 0"
                x-cloak
                x-on:click="markAllRead()"
                class="shrink-0 text-[12px] font-semibold text-emerald-700 transition-colors duration-150 hover:text-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 dark:text-emerald-300 dark:hover:text-emerald-200"
            >
                Tandai dibaca
            </button>
        </div>

        <div class="max-h-[22.5rem] overflow-y-auto" aria-live="polite" aria-relevant="additions text">
            <template x-if="items.length === 0">
                <div class="px-5 py-12 text-center">
                    <div class="mx-auto grid size-12 place-items-center rounded-xl bg-slate-100 text-slate-400 dark:bg-white/[.06] dark:text-slate-500">
                        <x-admin.icon name="bell" :size="22" />
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Tidak ada notifikasi</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Semua notifikasi telah dibaca.</p>
                </div>
            </template>

            <template x-for="notif in items" :key="notif.id">
                <a
                    :href="notif.href || '#'"
                    x-on:click="markRead(notif.id)"
                    class="flex gap-3 border-b border-slate-100 px-4 py-3.5 transition-[background-color,opacity] duration-150 hover:bg-emerald-50/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-emerald-500/50 dark:border-white/[.05] dark:hover:bg-emerald-900/15"
                    :class="notif.read ? 'opacity-65' : ''"
                    :aria-label="(notif.read ? 'Sudah dibaca. ' : 'Belum dibaca. ') + notif.title + '. ' + notif.message"
                >
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl" :style="getIconStyle(notif.color)" aria-hidden="true">
                        <x-admin.icon name="bell" :size="17" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-[13px] font-semibold text-slate-800 dark:text-slate-100" x-text="notif.title"></span>
                        <span class="mt-0.5 block line-clamp-2 text-[12px] text-slate-600 dark:text-slate-300" x-text="notif.message"></span>
                        <span class="mt-1 block text-[11px] text-slate-500 dark:text-slate-400" x-text="notif.time"></span>
                    </span>
                    <span x-show="!notif.read" class="mt-1.5 size-2 shrink-0 rounded-full bg-emerald-500" aria-label="Belum dibaca"></span>
                </a>
            </template>
        </div>

        <a
            href="{{ route('admin.notifications.index') }}"
            class="block border-t border-slate-100 bg-slate-50/80 px-5 py-3 text-center text-[12px] font-semibold text-emerald-700 transition-colors duration-150 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-emerald-500/50 dark:border-white/[.06] dark:bg-white/[.03] dark:text-emerald-300 dark:hover:bg-white/[.06]"
        >
            Lihat semua notifikasi
        </a>
    </section>
</div>
