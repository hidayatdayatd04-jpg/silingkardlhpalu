@props(['user' => null])

@php
    $adminRole = $user?->adminRole();
    $allowedGroups = $adminRole ? $adminRole->allowedGroups() : [];
@endphp

<div
    class="relative"
    x-data="{ open: false }"
    x-on:click.away="open = false"
    x-on:keydown.escape.window="open = false"
>
    <button
        x-on:click="open = !open"
        class="quick-action-btn flex items-center gap-1.5 rounded-full px-4 py-2 text-[13px] font-semibold text-white"
        title="Aksi Cepat"
    >
        <x-admin.icon name="plus" :size="16" />
        <span class="hidden sm:inline">Buat</span>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="glass-dropdown absolute right-0 top-full z-50 mt-2 w-64 overflow-hidden rounded-2xl p-1.5"
        style="display: none;"
    >
        <p class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Aksi Cepat</p>

        {{-- Pengendalian --}}
        @if(in_array('pengendalian', $allowedGroups))
            <a href="{{ route('admin.resources.create', 'pengaduan-pengendalian') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                    <x-admin.icon name="megaphone" :size="16" />
                </span>
                <span>Laporan Pengendalian</span>
            </a>
            <a href="{{ route('admin.resources.create', 'permohonan-rekomendasi') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                    <x-admin.icon name="clipboard-check" :size="16" />
                </span>
                <span>Permohonan Rekomendasi</span>
            </a>
        @endif

        {{-- Sampah LB3 --}}
        @if(in_array('sampah-lb3', $allowedGroups))
            <a href="{{ route('admin.resources.create', 'pengaduan-sampah') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400">
                    <x-admin.icon name="recycle" :size="16" />
                </span>
                <span>Laporan Sampah</span>
            </a>
            <a href="{{ route('admin.resources.create', 'registrasi-usaha-lb3') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                    <x-admin.icon name="building" :size="16" />
                </span>
                <span>Registrasi LB3</span>
            </a>
            <a href="{{ route('admin.resources.create', 'pengajuan-rintek-pertek') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400">
                    <x-admin.icon name="factory" :size="16" />
                </span>
                <span>RINTEK/PERTEK</span>
            </a>
        @endif

        {{-- RTH --}}
        @if(in_array('rth', $allowedGroups))
            <a href="{{ route('admin.resources.create', 'pengaduan-rth') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-teal-50 text-teal-600 dark:bg-teal-900/30 dark:text-teal-400">
                    <x-admin.icon name="tree" :size="16" />
                </span>
                <span>Laporan RTH</span>
            </a>
            <a href="{{ route('admin.resources.create', 'pinjam-taman') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                    <x-admin.icon name="park-bench" :size="16" />
                </span>
                <span>Pinjam Taman</span>
            </a>
            <a href="{{ route('admin.resources.create', 'data-tanam-pohon') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                    <x-admin.icon name="seedling" :size="16" />
                </span>
                <span>Tanam Pohon</span>
            </a>
        @endif

        {{-- Tata Penataan --}}
        @if(in_array('tata-penataan', $allowedGroups))
            <a href="{{ route('admin.resources.create', 'pengaduan-tata-penataan') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">
                    <x-admin.icon name="building" :size="16" />
                </span>
                <span>Pengaduan Tata Penataan</span>
            </a>
            <a href="{{ route('admin.resources.create', 'sosialisasi') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                    <x-admin.icon name="clipboard-check" :size="16" />
                </span>
                <span>Sosialisasi & Monev</span>
            </a>
            <a href="{{ route('admin.resources.create', 'pelanggaran') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                    <x-admin.icon name="alert-triangle" :size="16" />
                </span>
                <span>Pelanggaran</span>
            </a>
        @endif

        {{-- Konten (Superadmin) --}}
        @if(in_array('konten', $allowedGroups))
            <a href="{{ route('admin.resources.create', 'artikel') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                    <x-admin.icon name="news" :size="16" />
                </span>
                <span>Tulis Artikel</span>
            </a>
        @endif

        @if($user?->isSuperadmin())
            <a href="{{ route('admin.resources.create', 'user') }}" class="profile-menu-item">
                <span class="grid size-8 place-items-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-900/30 dark:text-slate-400">
                    <x-admin.icon name="user-plus" :size="16" />
                </span>
                <span>Tambah Admin</span>
            </a>
        @endif
    </div>
</div>
