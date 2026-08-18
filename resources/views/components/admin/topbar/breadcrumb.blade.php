@props(['heading' => 'Dashboard'])

@php
    $isDashboard = in_array(strtolower(trim($heading)), ['dashboard', ''], true);
@endphp

<nav class="flex min-w-0 items-center gap-2 text-sm" aria-label="Jejak navigasi">
    <a
        href="{{ route('admin.dashboard') }}"
        aria-label="Dashboard"
        @if($isDashboard) aria-current="page" @endif
        class="group flex min-h-9 items-center gap-1.5 rounded-lg text-slate-600 transition-colors duration-150 hover:text-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 dark:text-slate-300 dark:hover:text-emerald-300"
    >
        <span class="grid size-8 place-items-center rounded-lg bg-emerald-50 text-emerald-700 transition-[background-color,color,box-shadow] duration-150 group-hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 dark:group-hover:bg-emerald-900/50">
            <x-admin.icon name="home" :size="16" />
        </span>
        <span class="hidden font-semibold sm:inline">Dashboard</span>
    </a>

    @if(! $isDashboard)
        <x-admin.icon name="chevron-right" :size="14" class="shrink-0 text-slate-300 dark:text-slate-600" />
        <span class="max-w-[11rem] truncate font-semibold text-slate-800 dark:text-slate-100 sm:max-w-none" aria-current="page">
            {{ $heading }}
        </span>
    @endif
</nav>
