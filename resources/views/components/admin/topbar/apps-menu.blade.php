@props(['user' => null])

@php
    $registry = \App\Support\Admin\AdminRegistry::flat();
    $isSuperadmin = \App\Support\AdminAccess::isSuperadmin($user);
    $canAccess = function (string $slug) use ($registry, $user, $isSuperadmin): bool {
        $meta = $registry[$slug] ?? null;
        if (! $meta || (($meta['slug'] ?? null) === 'user' && ! $isSuperadmin)) {
            return false;
        }

        return $isSuperadmin || (bool) $user?->canAccessResource($meta);
    };

    $apps = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard'), 'icon' => 'dashboard', 'show' => true],
        ['label' => 'Website', 'url' => url('/'), 'icon' => 'external-link', 'show' => true, 'target' => '_blank'],
        ['label' => 'Artikel', 'url' => route('admin.resources.index', ['resource' => 'artikel']), 'icon' => 'news', 'show' => $canAccess('artikel')],
        ['label' => 'Pengaduan', 'url' => route('admin.resources.index', ['resource' => 'pengaduan-pengendalian']), 'icon' => 'megaphone', 'show' => $canAccess('pengaduan-pengendalian')],
        ['label' => 'Pengguna', 'url' => route('admin.resources.index', ['resource' => 'user']), 'icon' => 'users', 'show' => $canAccess('user')],
        ['label' => 'Pengaturan', 'url' => route('admin.settings.edit'), 'icon' => 'settings', 'show' => true],
        ['label' => 'Notifikasi', 'url' => route('admin.notifications.index'), 'icon' => 'bell', 'show' => true],
        ['label' => 'Bantuan', 'url' => route('admin.help.index'), 'icon' => 'info-circle', 'show' => true],
    ];
@endphp

<div class="relative" x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false">
    <button
        type="button"
        x-on:click="open = !open"
        x-bind:aria-expanded="open"
        aria-controls="topbar-apps-menu"
        class="topbar-btn"
        aria-label="Buka menu aplikasi"
        title="Menu aplikasi"
    >
        <x-admin.icon name="grid" :size="20" />
    </button>

    <div
        id="topbar-apps-menu"
        x-show="open"
        x-cloak
        x-transition:enter="transition-[opacity,transform] ease-out duration-200"
        x-transition:enter-start="-translate-y-1 scale-95 opacity-0"
        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
        x-transition:leave="transition-[opacity,transform] ease-in duration-150"
        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
        x-transition:leave-end="-translate-y-1 scale-95 opacity-0"
        class="glass-dropdown absolute right-0 top-full z-[60] mt-2 w-64 overflow-hidden rounded-xl p-2"
        aria-label="Menu aplikasi"
    >
        <p class="px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">Aplikasi</p>
        <div class="grid grid-cols-2 gap-1">
            @foreach($apps as $app)
                @if($app['show'])
                    <a
                        href="{{ $app['url'] }}"
                        @if($app['target'] ?? null) target="{{ $app['target'] }}" rel="noopener" @endif
                        class="flex min-h-11 items-center gap-2.5 rounded-xl px-3 py-2.5 text-[13px] font-medium text-slate-700 transition-[background-color,color] duration-150 hover:bg-emerald-50 hover:text-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 dark:text-slate-300 dark:hover:bg-emerald-900/20 dark:hover:text-emerald-200"
                        aria-label="{{ $app['label'] }}{{ isset($app['target']) ? ' (membuka tab baru)' : '' }}"
                    >
                        <x-admin.icon :name="$app['icon']" :size="16" class="shrink-0 opacity-75" />
                        <span class="truncate">{{ $app['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</div>
