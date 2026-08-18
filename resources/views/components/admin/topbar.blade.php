@props(['heading' => 'Dashboard'])

@php
    $user = auth()->user();
@endphp

<header class="admin-topbar topbar-noise topbar-glass sticky top-0 z-50 shrink-0 border-b border-slate-200/70 shadow-[0_1px_3px_rgba(15,23,42,.04)] dark:border-white/[.07] dark:shadow-[0_1px_3px_rgba(0,0,0,.25)]">
    <div class="relative z-10 flex min-h-16 items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:min-h-18 lg:px-8">
        <div class="flex min-w-0 items-center gap-2 sm:gap-3">
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

        <div class="hidden min-w-0 flex-1 justify-center lg:flex">
            <x-admin.topbar.global-search />
        </div>

        <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
            <div class="hidden sm:block">
                <x-admin.topbar.quick-action :user="$user" />
            </div>
            <x-admin.topbar.notification-dropdown :notifications="$notifications" :notification-count="$notificationCount" />
            <x-admin.topbar.public-website />
            <x-admin.topbar.profile-dropdown :user="$user" />
        </div>
    </div>
</header>
