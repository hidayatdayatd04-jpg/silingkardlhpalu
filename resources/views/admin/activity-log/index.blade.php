@extends('layouts.admin')

@section('title', 'Log Aktivitas - Admin DLH')
@section('heading', 'Log Aktivitas')

@section('content')
    <x-admin.page-header
        title="Log Aktivitas Sistem"
        subtitle="Catatan seluruh aktivitas pengguna: tambah, ubah, hapus, login, ekspor, dan backup."
        icon="clock"
    />

    <x-admin.card :padding="false">
        {{-- Filter bar --}}
        <form method="GET" class="border-b border-slate-200 p-4">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                <div class="lg:col-span-2">
                    <label class="mb-1 block text-xs font-bold text-slate-500">Pencarian</label>
                    <input name="q" value="{{ $filters['q'] }}" placeholder="Subjek / user / IP..."
                        class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-500">Pengguna</label>
                    <select name="user_id" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                        <option value="">Semua</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected($filters['user_id'] == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-500">Aksi</label>
                    <select name="event" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                        <option value="">Semua</option>
                        @foreach($events as $value => $label)
                            <option value="{{ $value }}" @selected($filters['event'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-500">Modul</label>
                    <select name="module" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                        <option value="">Semua</option>
                        @foreach($modules as $m)
                            <option value="{{ $m }}" @selected($filters['module'] === $m)>{{ \Illuminate\Support\Str::headline($m) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2 lg:col-span-1">
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-500">Dari</label>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] }}"
                            class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-500">Sampai</label>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] }}"
                            class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    </div>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <x-admin.button variant="primary" type="submit" icon="filter" size="sm">Terapkan</x-admin.button>
                <x-admin.button variant="soft" size="sm" :href="route('admin.activity-log.index')">Reset</x-admin.button>
            </div>
        </form>

        <x-admin.table>
            <thead class="bg-slate-50">
                <tr>
                    <x-admin.table.header>Waktu</x-admin.table.header>
                    <x-admin.table.header>Pengguna</x-admin.table.header>
                    <x-admin.table.header>Aksi</x-admin.table.header>
                    <x-admin.table.header>Modul</x-admin.table.header>
                    <x-admin.table.header>Subjek</x-admin.table.header>
                    <x-admin.table.header>IP</x-admin.table.header>
                    <x-admin.table.header class="text-center">Detail</x-admin.table.header>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                    @php $meta = $log->eventMeta(); @endphp
                    <tr class="align-top transition hover:bg-brand-50/40">
                        <td class="px-4 py-3">
                            <span class="text-sm text-slate-600">{{ $log->created_at?->format('d M Y') }}</span>
                            <span class="block text-xs text-slate-400">{{ $log->created_at?->format('H:i:s') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-2">
                                <x-admin.avatar :name="$log->user_name ?? 'System'" size="sm" />
                                <span class="text-sm font-semibold text-ink-800">{{ $log->user_name ?? 'System' }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <x-admin.badge :variant="$meta['variant']" :icon="$meta['icon']">{{ $meta['label'] }}</x-admin.badge>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-slate-600">{{ \Illuminate\Support\Str::headline($log->module ?? '-') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm font-medium text-ink-700">{{ $log->subject_label ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-slate-500">{{ $log->ip_address ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(!empty($log->properties))
                                <div x-data="{ open: false }">
                                    <button type="button" x-on:click="open = !open"
                                        class="grid size-8 place-items-center rounded-lg text-slate-500 transition hover:bg-info-50 hover:text-info-600" title="Lihat perubahan">
                                        <x-admin.icon name="eye" :size="16" />
                                    </button>
                                    <template x-teleport="body">
                                        <div x-show="open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="display:none;">
                                            <div class="absolute inset-0 bg-ink-950/50 backdrop-blur-sm" x-on:click="open = false"></div>
                                            <div class="relative max-h-[80vh] w-full max-w-2xl overflow-auto rounded-xl border border-white/80 bg-white p-5 shadow-[var(--shadow-modal)]">
                                                <div class="mb-4 flex items-center justify-between">
                                                    <h3 class="text-h4 font-bold text-ink-900">Perubahan Data</h3>
                                                    <button type="button" x-on:click="open = false" class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100"><x-admin.icon name="x" :size="18" /></button>
                                                </div>
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-left text-sm">
                                                        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                                                            <tr>
                                                                <th class="px-3 py-2">Kolom</th>
                                                                <th class="px-3 py-2">Sebelum</th>
                                                                <th class="px-3 py-2">Sesudah</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-100">
                                                            @php
                                                                $old = $log->properties['old'] ?? [];
                                                                $new = $log->properties['new'] ?? [];
                                                                $keys = collect(array_keys($old))->merge(array_keys($new))->unique();
                                                            @endphp
                                                            @foreach($keys as $key)
                                                                <tr>
                                                                    <td class="px-3 py-2 font-semibold text-ink-700">{{ \Illuminate\Support\Str::headline($key) }}</td>
                                                                    <td class="px-3 py-2 text-danger-600">{{ \Illuminate\Support\Str::limit(is_array($old[$key] ?? null) ? json_encode($old[$key]) : ($old[$key] ?? '—'), 120) }}</td>
                                                                    <td class="px-3 py-2 text-success-700">{{ \Illuminate\Support\Str::limit(is_array($new[$key] ?? null) ? json_encode($new[$key]) : ($new[$key] ?? '—'), 120) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-admin.empty-state icon="clock" title="Belum ada aktivitas"
                                description="Log aktivitas akan muncul di sini saat pengguna melakukan tindakan." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.table>

        <div class="border-t border-slate-200 px-6 py-4">
            {{ $logs->links() }}
        </div>
    </x-admin.card>
@endsection
