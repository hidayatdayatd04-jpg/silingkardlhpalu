@extends('layouts.admin')

@section('title', 'Backup Database - Admin DLH')
@section('heading', 'Backup Database')

@section('content')
    <x-admin.page-header
        title="Backup & Restore Database"
        subtitle="Kelola cadangan database {{ $database }}. Restore bersifat destruktif — gunakan hati-hati."
        icon="download"
    >
        <x-slot:actions>
            <form method="POST" action="{{ route('admin.backup.store') }}">
                @csrf
                <x-admin.button variant="primary" type="submit" icon="download">Buat Backup Sekarang</x-admin.button>
            </form>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Peringatan restore --}}
    <x-admin.alert type="warning">
        <strong>Perhatian:</strong> Restore akan <strong>menimpa seluruh data saat ini</strong> dengan isi file backup. Sistem otomatis membuat cadangan <em>pre-restore</em> sebelum menjalankan restore.
    </x-admin.alert>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Daftar backup --}}
        <div class="lg:col-span-2">
            <x-admin.card :padding="false">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-h4 font-bold text-ink-900">File Backup ({{ count($backups) }})</h2>
                    <p class="text-xs text-slate-500">Tersimpan di penyimpanan privat server.</p>
                </div>
                <x-admin.table>
                    <thead class="bg-slate-50">
                        <tr>
                            <x-admin.table.header>Nama File</x-admin.table.header>
                            <x-admin.table.header>Ukuran</x-admin.table.header>
                            <x-admin.table.header>Tanggal</x-admin.table.header>
                            <x-admin.table.header class="text-center">Aksi</x-admin.table.header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($backups as $b)
                            <tr class="transition hover:bg-brand-50/40">
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-2 font-mono text-sm font-semibold text-ink-800">
                                        <x-admin.icon name="file-text" :size="15" class="text-slate-400" />
                                        {{ $b['name'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ number_format($b['size'] / 1024, 1) }} KB</td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::createFromTimestamp($b['modified'])->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('admin.backup.download', $b['name']) }}"
                                           class="grid size-8 place-items-center rounded-lg text-slate-500 transition hover:bg-info-50 hover:text-info-600" title="Unduh">
                                            <x-admin.icon name="download" :size="16" />
                                        </a>
                                        <button type="button" x-data x-on:click="$dispatch('open-modal', 'restore-{{ $loop->index }}')"
                                            class="grid size-8 place-items-center rounded-lg text-slate-500 transition hover:bg-amber-50 hover:text-amber-600" title="Restore">
                                            <x-admin.icon name="refresh" :size="16" />
                                        </button>
                                        <button type="button" x-data x-on:click="$dispatch('open-modal', 'delbk-{{ $loop->index }}')"
                                            class="grid size-8 place-items-center rounded-lg text-slate-500 transition hover:bg-danger-50 hover:text-danger-600" title="Hapus">
                                            <x-admin.icon name="trash" :size="16" />
                                        </button>
                                    </div>

                                    {{-- Modal restore --}}
                                    <x-admin.modal name="restore-{{ $loop->index }}" title="Restore Database" variant="danger" maxWidth="md">
                                        <p class="mb-3">Restore dari <strong class="font-mono">{{ $b['name'] }}</strong>? Seluruh data saat ini akan <strong>ditimpa</strong>.</p>
                                        <form method="POST" action="{{ route('admin.backup.restore') }}">
                                            @csrf
                                            <input type="hidden" name="existing" value="{{ $b['name'] }}">
                                            <label class="mb-1 block text-sm font-bold text-ink-700">Ketik <span class="font-mono text-danger-600">RESTORE</span> untuk konfirmasi</label>
                                            <input name="confirmation" autocomplete="off" required
                                                class="mb-4 h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-danger-500 focus:ring-4 focus:ring-danger-100">
                                            <div class="flex justify-end gap-2">
                                                <x-admin.button variant="soft" type="button" x-on:click="$dispatch('close-modal', 'restore-{{ $loop->index }}')">Batal</x-admin.button>
                                                <x-admin.button variant="danger" type="submit" icon="refresh">Restore Sekarang</x-admin.button>
                                            </div>
                                        </form>
                                    </x-admin.modal>

                                    {{-- Modal hapus --}}
                                    <x-admin.confirm-delete
                                        name="delbk-{{ $loop->index }}"
                                        :action="route('admin.backup.destroy', $b['name'])"
                                        title="Hapus Backup"
                                        message="File backup ini akan dihapus permanen." />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-admin.empty-state icon="download" title="Belum ada backup"
                                        description="Klik 'Buat Backup Sekarang' untuk membuat cadangan database pertama." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-admin.table>
            </x-admin.card>
        </div>

        {{-- Upload restore --}}
        <div>
            <x-admin.card>
                <h2 class="mb-1 text-h4 font-bold text-ink-900">Restore dari File</h2>
                <p class="mb-4 text-sm text-slate-500">Unggah file <strong>.sql</strong> hasil backup untuk dipulihkan.</p>

                <form method="POST" action="{{ route('admin.backup.restore') }}" enctype="multipart/form-data" x-data="{ confirm: '' }">
                    @csrf
                    <input type="file" name="file" accept=".sql,.txt" required
                        class="mb-3 block w-full rounded-lg border border-slate-300 bg-white p-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-sm file:font-bold file:text-brand-700">
                    <label class="mb-1 block text-sm font-bold text-ink-700">Ketik <span class="font-mono text-danger-600">RESTORE</span></label>
                    <input name="confirmation" x-model="confirm" autocomplete="off" required
                        class="mb-4 h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-danger-500 focus:ring-4 focus:ring-danger-100">
                    <x-admin.button variant="danger" type="submit" icon="upload" class="w-full justify-center"
                        x-bind:disabled="confirm !== 'RESTORE'">Restore dari File</x-admin.button>
                </form>
            </x-admin.card>
        </div>
    </div>
@endsection
