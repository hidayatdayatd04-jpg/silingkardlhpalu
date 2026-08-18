@extends('layouts.admin')

@section('title', 'Notifikasi - Admin DLH')
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
                    <x-admin.button variant="secondary" size="sm" icon="check" type="submit">
                        Tandai semua dibaca ({{ $unreadCount }})
                    </x-admin.button>
                </form>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.card :padding="false">
        <div class="divide-y divide-slate-100">
            @forelse($notifications as $n)
                @php $data = $n->data; @endphp
                <div class="admin-notification-row flex items-start gap-3 px-5 py-4 {{ $n->read_at ? 'opacity-60' : '' }}">
                    <div class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-100 text-brand-600">
                        <x-admin.icon :name="$data['icon'] ?? 'bell'" :size="18" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-ink-900">{{ $data['title'] ?? 'Notifikasi' }}</p>
                        <p class="mt-0.5 text-sm text-slate-600">{{ $data['message'] ?? '' }}</p>
                        <p class="mt-1 flex items-center gap-1 text-xs text-slate-400">
                            <x-admin.icon name="clock" :size="12" /> {{ $n->created_at?->diffForHumans() }}
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        @if(!empty($data['href']))
                            <a href="{{ $data['href'] }}" class="admin-icon-button text-slate-500 hover:bg-info-50 hover:text-info-600" aria-label="Buka notifikasi: {{ $data['title'] ?? 'Notifikasi' }}" title="Buka">
                                <x-admin.icon name="eye" :size="16" />
                            </a>
                        @endif
                        @if(!$n->read_at)
                            <form method="POST" action="{{ route('admin.notifications.read', $n->id) }}">
                                @csrf
                                <button type="submit" class="admin-icon-button text-slate-500 hover:bg-brand-50 hover:text-brand-600" aria-label="Tandai notifikasi sebagai dibaca" title="Tandai dibaca">
                                    <x-admin.icon name="check" :size="16" />
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
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
