@props(['user' => null])

@php
    $hour = (int) now()->format('G');
    $greeting = match(true) {
        $hour >= 4 && $hour < 11 => 'Selamat Pagi',
        $hour >= 11 && $hour < 15 => 'Selamat Siang',
        $hour >= 15 && $hour < 18 => 'Selamat Sore',
        default => 'Selamat Malam',
    };
    $userName = $user?->name ?? 'Admin';
    $roleLabel = $user->role?->label() ?? 'Admin';
    $today = now()->translatedFormat('l, d F Y');
@endphp

<div class="hidden min-w-0 lg:block" x-data="{
    time: '{{ now()->format('H:i') }}',
    init() {
        setInterval(() => {
            this.time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Asia/Makassar' });
        }, 1000);
    }
}">
    <p class="text-[13px] font-semibold text-slate-700 dark:text-slate-200">
        {{ $greeting }}, {{ $userName }} <span class="inline-block" x-data="{ show: false }" x-init="setTimeout(() => show = true, 300)" x-show="show" x-transition>&#x1F44B;</span>
    </p>
    <p class="mt-0.5 flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-slate-500">
        <x-admin.icon name="clock" :size="12" class="opacity-60" />
        <span>{{ $today }}</span>
        <span class="text-slate-300 dark:text-slate-600">&bull;</span>
        <span x-text="time" class="tabular-nums"></span>
        <span class="text-[10px] font-medium text-slate-300 dark:text-slate-600">WITA</span>
    </p>
</div>
