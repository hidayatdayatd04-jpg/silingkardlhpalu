@props(['heading' => 'Dashboard'])

@php
    $user = auth()->user();
@endphp

<header class="admin-topbar topbar-noise topbar-glass sticky top-0 z-50 shrink-0 border-b border-slate-200/70 shadow-[0_1px_3px_rgba(15,23,42,.04)] dark:border-white/[.07] dark:shadow-[0_1px_3px_rgba(0,0,0,.25)]">
    <div class="relative z-10 flex min-h-14 items-center gap-2 px-3 py-2 sm:min-h-16 sm:px-4 md:gap-3 md:px-5 lg:min-h-18 lg:px-8">

        {{-- Kiri: burger + breadcrumb — breadcrumb menyusut/truncate di layar sempit --}}
        <div class="flex min-w-0 items-center gap-1.5 sm:gap-2">
            <button
                type="button"
                x-on:click="$dispatch('open-sidebar', { trigger: $el })"
                class="topbar-btn lg:hidden"
                aria-label="Buka navigasi"
                aria-controls="admin-mobile-sidebar"
            >
                <x-admin.icon name="menu" :size="20" />
            </button>

            <x-admin.topbar.breadcrumb :heading="$heading" />
        </div>

        {{-- Tengah: global search — muncul di md ke atas --}}
        <div class="hidden min-w-0 flex-1 items-center justify-center md:flex">
            <x-admin.topbar.global-search />
        </div>

        {{-- Kanan: pencarian (mobile) + notifikasi + profil --}}
        <div class="ml-auto flex shrink-0 items-center gap-1 sm:gap-1.5 md:ml-0 md:gap-2">
            <button
                type="button"
                x-on:click="$dispatch('open-command-palette')"
                class="topbar-btn md:hidden"
                aria-label="Buka pencarian cepat"
                aria-haspopup="dialog"
                aria-controls="admin-command-palette"
            >
                <x-admin.icon name="search" :size="20" />
            </button>
            <x-admin.topbar.notification-dropdown :notifications="$notifications" :notification-count="$notificationCount" />
            <x-admin.topbar.profile-dropdown :user="$user" />
        </div>
    </div>
</header>

