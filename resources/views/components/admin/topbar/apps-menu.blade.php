@props(['user' => null])

@php
    $apps = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard'), 'icon' => 'dashboard', 'show' => true],
        ['label' => 'Website', 'url' => url('/'), 'icon' => 'external-link', 'show' => true, 'target' => '_blank'],
        ['label' => 'Artikel', 'url' => route('admin.resources.index', 'artikel'), 'icon' => 'news', 'show' => $user->canAccessGroup('konten')],
        ['label' => 'Pengaduan', 'url' => route('admin.resources.index', 'pengaduan-pengendalian'), 'icon' => 'megaphone', 'show' => $user->canAccessGroup('pengendalian')],
        ['label' => 'Pengguna', 'url' => route('admin.resources.index', 'user'), 'icon' => 'users', 'show' => $user->isSuperadmin()],
        ['label' => 'Pengaturan', 'url' => route('admin.settings.edit'), 'icon' => 'settings', 'show' => true],
        ['label' => 'Notifikasi', 'url' => route('admin.notifications.index'), 'icon' => 'bell', 'show' => true],
        ['label' => 'Bantuan', 'url' => route('admin.help.index'), 'icon' => 'info-circle', 'show' => true],
    ];
@endphp

<div
    class="relative"
    x-data="{ open: false }"
    x-on:click.away="open = false"
    x-on:keydown.escape.window="open = false"
>
    <button
        x-on:click="open = !open"
        class="topbar-btn"
        title="Menu Aplikasi"
    >
        <x-admin.icon name="grid" :size="20" />
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
        <p class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Aplikasi</p>

        <div class="grid grid-cols-2 gap-1">
            @foreach($apps as $app)
                @if($app['show'])
                    <a
                        href="{{ $app['url'] }}"
                        @if($app['target'] ?? null) target="{{ $app['target'] }}" @endif
                        class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-[13px] font-medium text-slate-600 transition-all duration-150 hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-400 dark:hover:bg-emerald-900/20 dark:hover:text-emerald-400"
                    >
                        <x-admin.icon :name="$app['icon']" :size="16" class="opacity-60" />
                        <span>{{ $app['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</div>
