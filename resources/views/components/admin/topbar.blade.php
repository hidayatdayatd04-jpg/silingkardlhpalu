@props(['heading' => 'Dashboard'])

@php
    $user = auth()->user();
@endphp

<header
    class="topbar-noise sticky top-0 z-50 topbar-glass shadow-[0_1px_3px_rgba(0,0,0,.04)] dark:shadow-[0_1px_3px_rgba(0,0,0,.25)]"
>
    <div class="relative z-10 flex h-20 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">

        {{-- ═══════════════ LEFT ═══════════════ --}}
        <div class="flex min-w-0 items-center gap-3">
            {{-- Breadcrumb --}}
            <x-admin.topbar.breadcrumb :heading="$heading" />
        </div>

        {{-- ═══════════════ CENTER ═══════════════ --}}
        <div class="hidden flex-1 justify-center lg:flex">
            <x-admin.topbar.global-search />
        </div>

        {{-- ═══════════════ RIGHT ═══════════════ --}}
        <div class="flex items-center gap-1.5">
            {{-- Quick Action --}}
            <x-admin.topbar.quick-action :user="$user" />

            {{-- Notifications --}}
            <x-admin.topbar.notification-dropdown :notifications="$notifications" :notification-count="$notificationCount" />

            {{-- Lihat Website --}}
            <x-admin.topbar.public-website />
        </div>
    </div>
</header>
