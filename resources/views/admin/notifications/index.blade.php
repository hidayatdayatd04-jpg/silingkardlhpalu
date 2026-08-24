@extends('layouts.admin')

@section('title', 'Notifikasi - SILINGKAR DLH ADMIN')
@section('heading', 'Notifikasi')

@section('content')
<div class="admin-notifications">
    <x-admin.page-header
        title="Semua Notifikasi"
        subtitle="Daftar seluruh notifikasi yang masuk ke akun Anda."
        icon="bell"
    >
        <x-slot:actions>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                    @csrf
                    <x-admin.button variant="secondary" size="sm" type="submit">
                        Tandai semua dibaca ({{ $unreadCount }})
                    </x-admin.button>
                </form>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="admin-notification-panel overflow-hidden">
        <div class="notification-feed-header flex items-center justify-between gap-4 px-4 py-3 sm:px-5">
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Notifikasi terbaru</p>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $notifications->total() }} notifikasi</p>
        </div>

        <div class="notification-feed divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($notifications as $n)
                @php
                    $data = $n->data;
                    $isUnread = is_null($n->read_at);
                    $iconTone = match ($data['color'] ?? 'emerald') {
                        'rose', 'red' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300',
                        'amber', 'orange' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
                        'sky', 'blue' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300',
                        'indigo', 'purple' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300',
                        'teal' => 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300',
                        default => 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-300',
                    };
                @endphp
                <article class="admin-notification-row grid grid-cols-[auto_minmax(0,1fr)] items-start gap-x-3 gap-y-3 px-4 py-4 sm:grid-cols-[auto_minmax(0,1fr)_auto] sm:gap-x-4 sm:px-5 {{ $isUnread ? 'is-unread' : 'is-read' }}">
                    <div class="notification-icon grid size-10 shrink-0 place-items-center rounded-xl {{ $iconTone }}">
                        <x-admin.icon :name="$data['icon'] ?? 'bell'" :size="18" />
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <p class="text-sm font-bold text-ink-900 dark:text-white">{{ $data['title'] ?? 'Notifikasi' }}</p>
                            @if($isUnread)
                                <span class="notification-unread-badge">Baru</span>
                            @endif
                        </div>
                        @if(!empty($data['message']))
                            <p class="mt-1 text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $data['message'] }}</p>
                        @endif
                        <p class="mt-2 flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                            <x-admin.icon name="clock" :size="13" />
                            <time datetime="{{ $n->created_at?->toIso8601String() }}">{{ $n->created_at?->diffForHumans() }}</time>
                            <span class="sr-only">{{ $isUnread ? 'Belum dibaca' : 'Sudah dibaca' }}</span>
                        </p>
                    </div>
                    <div class="notification-actions col-start-2 flex shrink-0 items-center gap-1 sm:col-start-auto sm:self-start sm:-mt-1">
                        @if(!empty($data['href']))
                            <a href="{{ $data['href'] }}" class="admin-icon-button text-slate-500 hover:bg-sky-50 hover:text-sky-700 dark:text-slate-400 dark:hover:bg-sky-950/40 dark:hover:text-sky-300" aria-label="Buka notifikasi: {{ $data['title'] ?? 'Notifikasi' }}" title="Buka detail">
                                <x-admin.icon name="eye" :size="16" />
                            </a>
                        @endif
                        <button
                            type="button"
                            x-data
                            x-on:click="$dispatch('open-modal', 'delete-notification-{{ $n->id }}')"
                            class="admin-icon-button text-slate-500 hover:bg-rose-50 hover:text-rose-700 dark:text-slate-400 dark:hover:bg-rose-950/40 dark:hover:text-rose-300"
                            aria-label="Hapus notifikasi: {{ $data['title'] ?? 'Notifikasi' }}"
                            title="Hapus notifikasi"
                        >
                            <x-admin.icon name="trash" :size="16" />
                        </button>

                        <x-admin.confirm-delete
                            name="delete-notification-{{ $n->id }}"
                            :action="route('admin.notifications.destroy', $n->id)"
                            title="Hapus Notifikasi"
                            message="Notifikasi ini akan dihapus permanen. Aksi ini tidak bisa dibatalkan."
                            confirm-text="Hapus Notifikasi"
                        />
                    </div>
                </article>
            @empty
                <x-admin.empty-state icon="bell" title="Belum ada notifikasi"
                    description="Notifikasi baru akan muncul di sini saat ada aktivitas." />
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </x-admin.card>
</div>
@endsection
