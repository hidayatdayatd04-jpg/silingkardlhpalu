@props(['user' => null])

@php
    $registry = \App\Support\Admin\AdminRegistry::flat();
    $groups = \App\Support\Admin\AdminRegistry::all();
    $isSuperadmin = \App\Support\AdminAccess::isSuperadmin($user);

    $iconMap = [
        'statistik-sampah' => 'chart-bar',
        'data-armada-persampahan' => 'truck',
        'pelanggaran' => 'alert-triangle',
        'sosialisasi' => 'presentation',
        'data-tanam-pohon' => 'seedling',
        'data-tpu' => 'park',
        'artikel' => 'news',
        'user' => 'user-plus',
    ];
    $toneMap = [
        'pengendalian' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        'sampah-lb3' => 'bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300',
        'rth' => 'bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300',
        'tata-penataan' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        'konten' => 'bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300',
    ];

    $quickActions = collect($registry)
        ->filter(function (array $meta) use ($user, $isSuperadmin) {
            if (! isset($meta['model']) || ! ($meta['can_create'] ?? true)) {
                return false;
            }

            // Resource pengguna dilindungi controller khusus; jangan pernah
            // tampilkan untuk admin bidang walaupun memiliki grup Konten.
            if (($meta['slug'] ?? null) === 'user' && ! $isSuperadmin) {
                return false;
            }

            // Administrator Utama hanya dapat membuat data konten (artikel & user).
            // Data operasional bidang diinput oleh admin bidang terkait.
            if ($isSuperadmin && ($meta['group'] ?? null) !== 'konten') {
                return false;
            }

            return $isSuperadmin || $user?->canAccessResource($meta);
        })
        ->map(function (array $meta) use ($iconMap, $toneMap, $groups) {
            $groupKey = $meta['group'] ?? '';

            return [
                'label' => $meta['label'],
                'group' => $groups[$groupKey]['label'] ?? 'Data',
                'url' => route('admin.resources.create', ['resource' => $meta['slug']]),
                'icon' => $iconMap[$meta['slug']] ?? $groups[$groupKey]['icon'] ?? 'plus',
                'tone' => $toneMap[$groupKey] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
            ];
        })
        ->values();
@endphp

@if($quickActions->isNotEmpty())
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
            aria-controls="quick-action-menu"
            class="quick-action-btn quick-action-btn--responsive min-h-9 transition-[background-color,box-shadow,transform] duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 lg:min-h-10 dark:focus-visible:ring-offset-slate-900"
            aria-label="Buat data baru"
            title="Buat data baru"
        >
            <x-admin.icon name="plus" :size="17" />
            <span class="hidden sm:inline">Buat</span>
        </button>

        <div
            id="quick-action-menu"
            x-show="open"
            x-cloak
            x-transition:enter="transition-[opacity,transform] ease-out duration-200"
            x-transition:enter-start="-translate-y-1 scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            x-transition:leave="transition-[opacity,transform] ease-in duration-150"
            x-transition:leave-start="translate-y-0 scale-100 opacity-100"
            x-transition:leave-end="-translate-y-1 scale-95 opacity-0"
            class="glass-dropdown absolute right-0 top-full z-[60] mt-2 w-72 overflow-hidden rounded-xl p-1.5"
            aria-label="Aksi cepat"
        >
            <p class="px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">Buat data baru</p>
            <div class="max-h-80 space-y-0.5 overflow-y-auto p-1">
                @foreach($quickActions as $action)
                    <a href="{{ $action['url'] }}" class="profile-menu-item min-h-11 transition-[background-color,color] duration-150">
                        <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $action['tone'] }}">
                            <x-admin.icon :name="$action['icon']" :size="16" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate">{{ $action['label'] }}</span>
                            <span class="mt-0.5 block truncate text-[11px] font-normal text-slate-500 dark:text-slate-400">{{ $action['group'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif
