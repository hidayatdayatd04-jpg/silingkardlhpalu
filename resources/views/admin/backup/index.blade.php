@extends('layouts.admin')

@section('title', 'Cadangan & Pemulihan Data - SILINGKAR DLH ADMIN')
@section('heading', 'Cadangan & Pemulihan Data')

@php
    $tz = config('app.timezone', 'Asia/Makassar');
    $totalBackups = count($backups);
    $totalSize = array_sum(array_column($backups, 'size'));
    $totalSizeHuman = $totalSize > 0
        ? ($totalSize >= 1048576
            ? number_format($totalSize / 1048576, 2).' MB'
            : number_format($totalSize / 1024, 1).' KB')
        : '0 KB';
    $lastBackup = $totalBackups > 0
        ? \Carbon\Carbon::parse($backups[0]['modified_at'], $tz)
        : null;
@endphp

@section('content')
    <x-admin.page-header
        title="Cadangan & Pemulihan Data"
        subtitle="Buat cadangan data dan dokumen aplikasi agar dapat dipulihkan jika diperlukan."
        icon="database"
        :breadcrumbs="[['label' => 'Sistem'], ['label' => 'Cadangan Data']]"
    >
        <x-slot:actions>
            <form method="POST" action="{{ route('admin.backup.store') }}" class="flex">
                @csrf
                <button type="submit"
                    class="relative inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-100">
                    <x-admin.icon name="plus" :size="16" />
                    <span>Buat Cadangan Sekarang</span>
                </button>
            </form>
        </x-slot:actions>
    </x-admin.page-header>

    <p class="mt-2 text-[11px] text-slate-500">
        Proses berjalan di latar belakang. Anda tetap dapat menggunakan menu lain.
    </p>

    {{-- KPI strip --}}
    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card
            label="Total Cadangan"
            :value="$totalBackups"
            icon="database"
            color="emerald"
            sublabel="File tersimpan"
        />
        <x-admin.stat-card
            label="Total Ukuran"
            :value="$totalSizeHuman"
            icon="folder"
            color="sky"
            :numeric="false"
            sublabel="Ruang penyimpanan"
        />
        <x-admin.stat-card
            label="Cadangan Terakhir"
            :value="$lastBackup ? $lastBackup->format('d M Y') : '—'"
            icon="clock"
            color="bay"
            :numeric="false"
            sublabel="Pukul {{ $lastBackup ? $lastBackup->format('H:i') : '-' }}"
        />
        <x-admin.stat-card
            label="Status Sistem"
            value="Aktif"
            icon="circle-check"
            color="teal"
            :numeric="false"
            sublabel="Koneksi aman & terhubung"
        />
    </div>

    {{-- Warning Info --}}
    <div class="mt-4">
        <x-admin.alert type="warning">
            <strong>Perhatian:</strong> Data dari cadangan akan dipulihkan tanpa menghapus data lain yang sudah ada. Sistem akan mengamankan kondisi saat ini sebelum pemulihan dimulai.
        </x-admin.alert>
    </div>

    <div class="mt-4 grid gap-5 lg:grid-cols-3">
        {{-- Daftar cadangan --}}
        <div class="lg:col-span-2">
            <x-admin.card :padding="false" class="overflow-hidden">
                <div x-data='{ selected: [], names: @json(array_column($backups, "name")), get allSelected() { return this.names.length > 0 && this.selected.length === this.names.length; }, toggleAll(e) { this.selected = e.target.checked ? [...this.names] : []; } }'>
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <input type="checkbox" x-on:change="toggleAll($event)" :checked="allSelected"
                                class="size-4 shrink-0 rounded border-slate-300 text-brand-600 focus:ring-brand-500" title="Pilih semua" />
                            <div class="grid size-8 place-items-center rounded-lg bg-brand-50 text-brand-600">
                                <x-admin.icon name="archive" :size="16" />
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-ink-900">Daftar Cadangan</h2>
                                <p class="text-[11px] text-slate-500">{{ $totalBackups }} file tersimpan</p>
                            </div>
                        </div>

                        <button type="button" x-show="selected.length > 0"
                            x-on:click="$dispatch('open-modal', 'bulk-delete-backup')"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-danger-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-danger-700 focus:outline-none focus:ring-4 focus:ring-danger-100">
                            <x-admin.icon name="trash" :size="14" />
                            <span>Hapus terpilih (<span x-text="selected.length"></span>)</span>
                        </button>
                    </div>

                    {{-- Form hapus massal --}}
                    <form x-ref="bulkDelete" method="POST" action="{{ route('admin.backup.destroy-many') }}" class="hidden">
                        @csrf
                        <template x-for="name in selected" :key="name">
                            <input type="hidden" name="files[]" :value="name">
                        </template>
                    </form>

                    {{-- Modal konfirmasi hapus massal --}}
                    <x-admin.modal name="bulk-delete-backup" title="Hapus Cadangan Terpilih" variant="danger">
                        <p class="text-sm leading-relaxed text-ink-700 dark:text-slate-200">
                            <span class="font-bold" x-text="selected.length"></span> file cadangan terpilih akan dihapus permanen. Aksi ini tidak bisa dibatalkan.
                        </p>
                        <x-slot:footer>
                            <button type="button" data-modal-autofocus
                                x-on:click="$dispatch('close-modal', 'bulk-delete-backup')"
                                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition-[background-color,border-color,color] duration-150 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400/60 dark:border-white/[.1] dark:bg-white/[.04] dark:text-slate-200 dark:hover:bg-white/[.08]">
                                Batal
                            </button>
                            <button type="button"
                                x-on:click="$dispatch('close-modal', 'bulk-delete-backup'); $refs.bulkDelete.submit()"
                                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-danger-600 px-4 py-2.5 text-sm font-bold text-white shadow-[0_8px_20px_-8px_rgba(225,29,72,0.6)] transition-[background-color,box-shadow] duration-150 hover:bg-danger-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger-400">
                                <x-admin.icon name="trash" :size="16" aria-hidden="true" />
                                Ya, Hapus
                            </button>
                        </x-slot:footer>
                    </x-admin.modal>

                @if($totalBackups > 0)
                    <div class="divide-y divide-slate-100">
                        @foreach($backups as $b)
                            @php
                                $sizeHuman = $b['size'] >= 1048576
                                    ? number_format($b['size'] / 1048576, 2).' MB'
                                    : number_format($b['size'] / 1024, 1).' KB';
                                $created = \Carbon\Carbon::parse($b['modified_at'], $tz);
                                $isPreRestore = str_starts_with($b['name'], 'pre-restore');
                            @endphp
                            <div class="group flex items-center gap-3 px-4 py-2.5 transition hover:bg-brand-50/30">
                                <input type="checkbox" value="{{ $b['name'] }}" x-model="selected"
                                    class="size-4 shrink-0 rounded border-slate-300 text-brand-600 focus:ring-brand-500" title="Pilih file ini" />
                                <div class="grid size-8 shrink-0 place-items-center rounded-lg {{ $isPreRestore ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} transition group-hover:scale-105">
                                    <x-admin.icon :name="$isPreRestore ? 'refresh' : 'archive'" :size="15" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-mono text-xs font-semibold text-ink-800" title="{{ $b['name'] }}">
                                        {{ $b['name'] }}
                                    </p>
                                    <div class="mt-0.5 flex flex-wrap items-center gap-x-2.5 text-[11px] text-slate-500">
                                        <span class="inline-flex items-center gap-1">
                                            <x-admin.icon name="folder" :size="11" />
                                            {{ $sizeHuman }}
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <x-admin.icon name="calendar" :size="11" />
                                            {{ $created->format('d M Y') }}
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <x-admin.icon name="clock" :size="11" />
                                            {{ $created->format('H:i') }}
                                        </span>
                                        @if($isPreRestore)
                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-1.5 py-px text-[10px] font-bold text-amber-700">Cadangan Pengaman</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-0.5">
                                    <a href="{{ route('admin.backup.download', $b['name']) }}"
                                       class="grid size-7 place-items-center rounded-md text-slate-500 transition hover:bg-info-50 hover:text-info-600" title="Unduh">
                                        <x-admin.icon name="download" :size="14" />
                                    </a>
                                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'restore-{{ $loop->index }}')"
                                        class="grid size-7 place-items-center rounded-md text-slate-500 transition hover:bg-amber-50 hover:text-amber-600" title="Pulihkan">
                                        <x-admin.icon name="refresh" :size="14" />
                                    </button>
                                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'delbk-{{ $loop->index }}')"
                                        class="grid size-7 place-items-center rounded-md text-slate-500 transition hover:bg-danger-50 hover:text-danger-600" title="Hapus">
                                        <x-admin.icon name="trash" :size="14" />
                                    </button>
                                </div>

                                {{-- Modal restore --}}
                                <x-admin.modal name="restore-{{ $loop->index }}" title="Pulihkan dari Cadangan" variant="danger" maxWidth="md">
                                    <div class="mb-4 flex items-start gap-3 rounded-xl bg-danger-50 p-4">
                                        <div class="grid size-9 shrink-0 place-items-center rounded-lg bg-danger-100 text-danger-600">
                                            <x-admin.icon name="alert-triangle" :size="18" />
                                        </div>
                                        <p class="text-sm text-danger-800">Pulihkan data dari <strong class="font-mono">{{ $b['name'] }}</strong>? Data dari cadangan akan dipulihkan tanpa menghapus data lain yang sudah ada. Sistem akan mengamankan kondisi saat ini sebelum pemulihan dimulai.</p>
                                    </div>
                                    <form method="POST" action="{{ route('admin.backup.restore') }}">
                                        @csrf
                                        <input type="hidden" name="existing" value="{{ $b['name'] }}">
                                        <label class="mb-1.5 block text-sm font-bold text-ink-700">Ketik <span class="font-mono text-danger-600">PULIHKAN</span> untuk konfirmasi</label>
                                        <input name="confirmation" autocomplete="off" required placeholder="PULIHKAN"
                                            class="mb-4 h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none transition focus:border-danger-500 focus:ring-4 focus:ring-danger-100">
                                        <label class="mb-1.5 block text-sm font-bold text-ink-700">Ketik kode keamanan <span class="font-mono text-danger-600">{{ $restoreCode }}</span></label>
                                        <input name="restore_code" autocomplete="off" required
                                            class="mb-5 h-10 w-full rounded-lg border border-slate-300 bg-white px-3 font-mono text-sm uppercase outline-none transition focus:border-danger-500 focus:ring-4 focus:ring-danger-100">
                                        <div class="flex justify-end gap-2">
                                            <x-admin.button variant="soft" type="button" x-on:click="$dispatch('close-modal', 'restore-{{ $loop->index }}')">Batal</x-admin.button>
                                            <x-admin.button variant="danger" type="submit" icon="refresh">Pulihkan Sekarang</x-admin.button>
                                        </div>
                                    </form>
                                </x-admin.modal>

                                {{-- Modal hapus --}}
                                <x-admin.confirm-delete
                                    name="delbk-{{ $loop->index }}"
                                    :action="route('admin.backup.destroy', $b['name'])"
                                    title="Hapus Cadangan"
                                    message="File cadangan ini akan dihapus permanen." />
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-admin.empty-state icon="download" title="Belum ada cadangan"
                        description="Klik 'Buat Cadangan Sekarang' untuk membuat cadangan data pertama." />
                @endif
                </div>
            </x-admin.card>
        </div>

        {{-- Upload restore --}}
        <div class="space-y-4">
            <x-admin.card class="relative overflow-hidden">
                <div class="pointer-events-none absolute -right-8 -top-8 size-24 rounded-full bg-brand-50 blur-2xl"></div>
                <div class="relative">
                    <div class="mb-3 flex items-center gap-2.5">
                        <div class="grid size-8 place-items-center rounded-lg bg-brand-50 text-brand-600">
                            <x-admin.icon name="upload" :size="16" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-ink-900">Pulihkan dari Cadangan</h2>
                            <p class="text-[11px] text-slate-500">Unggah file cadangan (<strong>.zip</strong>) untuk dipulihkan</p>
                        </div>
                    </div>

                    <form id="restore-upload-form" method="POST" action="{{ route('admin.backup.restore') }}" enctype="multipart/form-data"
                        x-data="{ confirm: '', fileName: '', code: '' }" class="space-y-3">
                        @csrf
                        <div x-data="{ over: false }"
                            x-on:dragover.prevent="over = true"
                            x-on:dragleave.prevent="over = false"
                            x-on:drop.prevent="over = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0]?.name || ''"
                            :class="over ? 'border-brand-400 bg-brand-50' : 'border-slate-200 bg-slate-50/60'"
                            class="flex flex-col items-center justify-center gap-1.5 rounded-lg border-2 border-dashed px-3 py-5 text-center transition">
                            <input x-ref="fileInput" type="file" name="file" accept=".zip,.sql"
                                @change="fileName = $event.target.files[0]?.name || ''"
                                class="hidden" />
                            <div class="grid size-9 place-items-center rounded-lg bg-white text-brand-600 shadow-sm cursor-pointer"
                                x-on:click="$refs.fileInput.click()">
                                <x-admin.icon name="file-text" :size="18" />
                            </div>
                            <template x-if="!fileName">
                                <div>
                                    <p class="text-xs font-semibold text-ink-700">Pilih file cadangan (.zip)</p>
                                    <p class="text-[11px] text-slate-500">Klik atau seret file ke sini</p>
                                </div>
                            </template>
                            <template x-if="fileName">
                                <div>
                                    <p class="text-xs font-semibold text-brand-700" x-text="fileName"></p>
                                    <p class="text-[11px] text-slate-500">File siap diunggah</p>
                                </div>
                            </template>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold text-ink-700">Ketik <span class="font-mono text-danger-600">PULIHKAN</span></label>
                            <input name="confirmation" x-model="confirm" autocomplete="off" placeholder="PULIHKAN"
                                class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs outline-none transition focus:border-danger-500 focus:ring-4 focus:ring-danger-100">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold text-ink-700">Kode keamanan <span class="font-mono text-danger-600">{{ $restoreCode }}</span></label>
                            <input name="restore_code" x-model="code" autocomplete="off"
                                class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 font-mono text-xs uppercase outline-none transition focus:border-danger-500 focus:ring-4 focus:ring-danger-100">
                        </div>

                        <button type="submit"
                            x-bind:disabled="(confirm !== 'PULIHKAN' && confirm !== 'RESTORE') || !fileName || !code"
                            x-bind:class="((confirm === 'PULIHKAN' || confirm === 'RESTORE') && fileName && code) ? 'bg-danger-600 hover:bg-danger-700 text-white shadow-[0_8px_20px_-8px_rgba(225,29,72,0.6)]' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-xs font-bold transition focus:outline-none disabled:pointer-events-none disabled:opacity-60">
                            <span>Pulihkan Sekarang</span>
                        </button>
                    </form>
                </div>
            </x-admin.card>

            <x-admin.card class="bg-gradient-to-br from-slate-50 to-white">
                <div class="flex items-start gap-2.5">
                    <div class="grid size-8 shrink-0 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                        <x-admin.icon name="shield" :size="16" />
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-ink-900">Tips Keamanan Data</h3>
                        <ul class="mt-1.5 space-y-1 text-[11px] leading-relaxed text-slate-500">
                            <li class="flex gap-1.5"><x-admin.icon name="check" :size="12" class="mt-px shrink-0 text-emerald-500" />Cadangan mencakup seluruh data dan dokumen aplikasi.</li>
                            <li class="flex gap-1.5"><x-admin.icon name="check" :size="12" class="mt-px shrink-0 text-emerald-500" />File cadangan tersimpan aman di penyimpanan awan.</li>
                            <li class="flex gap-1.5"><x-admin.icon name="check" :size="12" class="mt-px shrink-0 text-emerald-500" />Sistem otomatis mengamankan kondisi saat ini sebelum pemulihan dimulai.</li>
                            <li class="flex gap-1.5"><x-admin.icon name="check" :size="12" class="mt-px shrink-0 text-emerald-500" />Hanya cadangan terbaru yang disimpan untuk mengoptimalkan ruang penyimpanan.</li>
                        </ul>
                    </div>
                </div>
            </x-admin.card>
        </div>
    </div>
@endsection
