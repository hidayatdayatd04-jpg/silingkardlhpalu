@props(['heading' => 'Dashboard'])

@php
    $isDashboard = in_array(strtolower(trim($heading)), ['dashboard', ''], true);
    $segments = request()->segments();
    $lastSegment = end($segments) ?? 'beranda';
@endphp

<div class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.dashboard') }}" class="group flex items-center gap-1.5 text-slate-500 transition-colors duration-200 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400">
        <span class="grid size-7 place-items-center rounded-lg bg-emerald-50/80 text-emerald-600 transition-all duration-200 group-hover:bg-emerald-100 group-hover:shadow-sm dark:bg-emerald-900/30 dark:text-emerald-400 dark:group-hover:bg-emerald-900/50">
            <x-admin.icon name="home" :size="15" />
        </span>
        <span class="hidden font-semibold sm:inline">Dashboard</span>
    </a>

    @if(! $isDashboard)
        <x-admin.icon name="chevron-right" :size="14" class="text-slate-300 dark:text-slate-600" />
        <span class="max-w-[200px] truncate font-semibold text-slate-800 dark:text-slate-100 sm:max-w-none">
            {{ $heading }}
        </span>
    @endif
</div>
