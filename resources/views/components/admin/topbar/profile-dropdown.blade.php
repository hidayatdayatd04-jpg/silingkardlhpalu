@props(['user' => null])

@php
    $roleLabel = $user?->roleLabel() ?? 'Admin';
    $photoUrl = $user->photoUrl();
@endphp

<div
    class="relative"
    x-data="{ open: false }"
    x-on:click.away="open = false"
    x-on:keydown.escape.window="open = false"
>
    <button
        x-on:click="open = !open"
        class="flex items-center gap-2.5 rounded-xl p-1 pr-2 transition-all duration-200 hover:bg-slate-100/80 dark:hover:bg-white/[.06]"
    >
        {{-- Avatar with online indicator --}}
        <div class="relative">
            <x-admin.avatar :name="$user->name" :src="$photoUrl" size="sm" class="ring-2 ring-white dark:ring-slate-800" />
            <span class="absolute -bottom-0.5 -right-0.5 size-3 rounded-full border-2 border-white bg-emerald-500 dark:border-slate-800"></span>
        </div>

        {{-- Name & Role (desktop) --}}
        <div class="hidden min-w-0 text-left xl:block">
            <p class="truncate text-[13px] font-semibold text-slate-700 dark:text-slate-200">{{ $user->name }}</p>
            <p class="truncate text-[11px] text-slate-400 dark:text-slate-500">{{ $roleLabel }}</p>
        </div>

        {{-- Chevron --}}
        <span class="hidden xl:block" x-bind:class="open ? 'rotate-180' : ''">
            <x-admin.icon name="chevron-down" :size="14" class="text-slate-400 transition-transform duration-200" />
        </span>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="glass-dropdown absolute right-0 top-full z-50 mt-2 w-64 overflow-hidden rounded-2xl p-2"
        style="display: none;"
    >
        {{-- User info header --}}
        <div class="mb-1 flex items-center gap-3 rounded-xl px-3 py-3">
            <x-admin.avatar :name="$user->name" :src="$photoUrl" size="md" />
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-slate-800 dark:text-white">{{ $user->name }}</p>
                <p class="truncate text-[12px] text-slate-400 dark:text-slate-500">{{ $roleLabel }}</p>
            </div>
        </div>

        <div class="my-1 h-px bg-slate-100 dark:bg-white/[.06]"></div>

        {{-- Menu items --}}
        <div class="space-y-0.5 p-1">
            <a href="{{ route('admin.profile.edit') }}" class="profile-menu-item">
                <x-admin.icon name="user" :size="16" />
                <span>Profil Saya</span>
            </a>

            <a href="{{ route('admin.settings.edit') }}" class="profile-menu-item">
                <x-admin.icon name="settings" :size="16" />
                <span>Pengaturan</span>
            </a>

            <a href="{{ route('admin.notifications.index') }}" class="profile-menu-item">
                <x-admin.icon name="bell" :size="16" />
                <span>Notifikasi</span>
            </a>

            <button
                x-on:click="$store.theme.toggle(); open = false"
                class="profile-menu-item w-full"
            >
                <x-admin.icon name="palette" :size="16" />
                <span>Tampilan</span>
                <span class="ml-auto text-[11px] text-slate-400 dark:text-slate-500" x-text="$store.theme.dark ? 'Gelap' : 'Terang'"></span>
            </button>

            @if($user->isSuperadmin())
                <a href="{{ route('admin.help.index') }}" class="profile-menu-item">
                    <x-admin.icon name="book-open" :size="16" />
                    <span>Dokumentasi</span>
                </a>
            @endif
        </div>

        <div class="my-1 h-px bg-slate-100 dark:bg-white/[.06]"></div>

        {{-- Logout --}}
        <div class="p-1">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="profile-menu-item w-full text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-900/20 dark:hover:text-red-300">
                    <x-admin.icon name="logout" :size="16" />
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</div>
