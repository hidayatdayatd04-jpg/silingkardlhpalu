@props(['user' => null])

@php
    $roleLabel = $user?->roleLabel() ?? 'Admin';
    $photoUrl = $user?->photoUrl();
@endphp

<div
    class="relative"
    x-data="{ open: false }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
>
    <button
        type="button"
        x-on:click="open = !open"
        x-bind:aria-expanded="open"
        aria-controls="topbar-profile-menu"
        aria-label="Buka menu akun {{ $user?->name ?? 'Admin' }}"
        class="flex min-h-9 items-center gap-2 rounded-xl p-1 pr-1.5 transition-[background-color,color] duration-150 hover:bg-slate-100/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 lg:min-h-10 lg:gap-2.5 lg:pr-2 dark:hover:bg-white/[.06]"
    >
        <span class="relative">
            <x-admin.avatar :name="$user?->name ?? ''" :src="$photoUrl" size="sm" class="ring-2 ring-white dark:ring-slate-800" />
            <span class="absolute -bottom-0.5 -right-0.5 size-3 rounded-full border-2 border-white bg-emerald-500 dark:border-slate-800" aria-label="Sedang aktif"></span>
        </span>

        <span class="hidden min-w-0 text-left xl:block">
            <span class="block max-w-32 truncate text-[13px] font-semibold text-slate-700 dark:text-slate-200">{{ $user?->name }}</span>
            <span class="block max-w-32 truncate text-[11px] text-slate-400 dark:text-slate-500">{{ $roleLabel }}</span>
        </span>

        <x-admin.icon name="chevron-down" :size="15" class="hidden shrink-0 text-slate-400 transition-transform duration-150 xl:block dark:text-slate-500" x-bind:class="open ? 'rotate-180' : ''" />
    </button>

    <div
        id="topbar-profile-menu"
        x-show="open"
        x-cloak
        x-transition:enter="transition-[opacity,transform] ease-out duration-200"
        x-transition:enter-start="-translate-y-1 scale-95 opacity-0"
        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
        x-transition:leave="transition-[opacity,transform] ease-in duration-150"
        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
        x-transition:leave-end="-translate-y-1 scale-95 opacity-0"
        class="glass-dropdown absolute right-0 top-full z-[60] mt-2 w-64 overflow-hidden rounded-xl p-2"
    >
        <div class="mb-1 flex items-center gap-3 rounded-lg px-3 py-3">
            <x-admin.avatar :name="$user?->name ?? ''" :src="$photoUrl" size="md" />
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-slate-800 dark:text-white">{{ $user?->name }}</p>
                <p class="truncate text-[12px] text-slate-500 dark:text-slate-400">{{ $roleLabel }}</p>
            </div>
        </div>

        <div class="my-1 h-px bg-slate-100 dark:bg-white/[.06]"></div>

        <div class="space-y-0.5 p-1">
            <a href="{{ route('admin.profile.edit') }}" class="profile-menu-item min-h-10 transition-[background-color,color] duration-150">
                <x-admin.icon name="user" :size="16" />
                <span>Profil Saya</span>
            </a>
            <a href="{{ route('admin.settings.edit') }}" class="profile-menu-item min-h-10 transition-[background-color,color] duration-150">
                <x-admin.icon name="settings" :size="16" />
                <span>Pengaturan</span>
            </a>
            <a href="{{ route('admin.notifications.index') }}" class="profile-menu-item min-h-10 transition-[background-color,color] duration-150">
                <x-admin.icon name="bell" :size="16" />
                <span>Notifikasi</span>
            </a>
            <a href="{{ route('admin.help.index') }}" class="profile-menu-item min-h-10 transition-[background-color,color] duration-150">
                <x-admin.icon name="book-open" :size="16" />
                <span>Bantuan</span>
            </a>
        </div>

        <div class="my-1 h-px bg-slate-100 dark:bg-white/[.06]"></div>

        <div class="p-1">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="profile-menu-item min-h-10 w-full text-red-600 transition-[background-color,color] duration-150 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-900/20 dark:hover:text-red-300">
                    <x-admin.icon name="logout" :size="16" />
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</div>
