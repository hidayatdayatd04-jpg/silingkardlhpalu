@props(['user' => null])

<div
    class="relative"
    x-data="{ open: false }"
    x-on:click.away="open = false"
    x-on:keydown.escape.window="open = false"
>
    <button
        x-on:click="open = !open"
        class="quick-action-btn flex items-center gap-1.5 rounded-full px-4 py-2 text-[13px] font-semibold text-white"
        title="Aksi Cepat"
    >
        <x-admin.icon name="plus" :size="16" />
        <span class="hidden sm:inline">Buat</span>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="glass-dropdown absolute right-0 top-full z-50 mt-2 w-56 overflow-hidden rounded-2xl p-1.5"
        style="display: none;"
    >
        <p class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Aksi Cepat</p>

        @if($user && $user->canAccessGroup('konten'))
            <a href="{{ route('admin.resources.create', 'artikel') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                    <x-admin.icon name="file-plus" :size="16" />
                </span>
                <span>Tambah Artikel</span>
            </a>
        @endif

        @if($user && $user->canAccessGroup('pengendalian'))
            <a href="{{ route('admin.resources.create', 'pengaduan-pengendalian') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                    <x-admin.icon name="message-plus" :size="16" />
                </span>
                <span>Tambah Pengaduan</span>
            </a>
        @endif

        @if($user && $user->canAccessGroup('sampah-lb3'))
            <a href="{{ route('admin.resources.create', 'statistik-sampah') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                    <x-admin.icon name="database" :size="16" />
                </span>
                <span>Tambah Data</span>
            </a>
        @endif

        @if($user->isSuperadmin())
            <a href="{{ route('admin.resources.create', 'user') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">
                    <x-admin.icon name="user-plus" :size="16" />
                </span>
                <span>Tambah User</span>
            </a>
        @endif
    </div>
</div>
