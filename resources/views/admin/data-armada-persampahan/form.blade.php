@extends('layouts.admin')

@section('title', ($record->exists ? 'Edit ' : 'Tambah ').$resource['label'].' - SILINGKAR DLH ADMIN')
@section('heading', $resource['label'])

@section('content')
@php
    $readOnly = (bool) ($readOnly ?? false);
    $kategoriValue = old('kategori', $record->kategori instanceof \BackedEnum ? $record->kategori->value : ($record->kategori ?? 'Kendaraan Roda 2'));

    $oldArmada = old('daftar_armada');
    $armadaRows = [];

    $makeRow = static function (array $row, int $index): array {
        return [
            'key' => 'armada-'.$index.'-'.uniqid(),
            'merk_type' => (string) ($row['merk_type'] ?? ''),
            'tahun_perolehan' => (string) ($row['tahun_perolehan'] ?? ''),
        ];
    };

    if (is_array($oldArmada)) {
        $armadaRows = collect($oldArmada)
            ->filter(fn ($r) => is_array($r))
            ->values()
            ->map(fn (array $r, int $i) => $makeRow($r, $i))
            ->all();
    } elseif ($record->exists && is_array($record->daftar_armada)) {
        $armadaRows = collect($record->daftar_armada)
            ->values()
            ->map(fn (array $r, int $i) => $makeRow($r, $i))
            ->all();
    }

    if (empty($armadaRows) && ! $readOnly) {
        $armadaRows = [$makeRow([], 0)];
    }
@endphp

<div
    x-data="{
        kategori: '{{ $kategoriValue }}',
        rows: {{ Js::from($armadaRows) }},
        readOnly: {{ $readOnly ? 'true' : 'false' }},
        get totalUnit() {
            return this.rows.filter(r => r.merk_type && r.merk_type.trim() !== '').length;
        },
        addRow() {
            if (this.readOnly) return;
            this.rows.push({
                key: 'armada-new-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5),
                merk_type: '',
                tahun_perolehan: ''
            });
        },
        removeRow(index) {
            if (this.readOnly) return;
            this.rows.splice(index, 1);
        },
        clearRows() {
            if (this.readOnly) return;
            this.rows = [];
        }
    }"
    class="space-y-6"
>
    <x-admin.page-header
        :title="($record->exists ? 'Edit ' : 'Tambah ').$resource['label']"
        :subtitle="$record->exists ? 'Perbarui daftar inventaris armada pada kategori '.$kategoriValue : 'Tambah data armada persampahan.'"
        icon="truck"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => $resource['label'], 'url' => route('admin.resources.index', $resource['slug'])],
            ['label' => $record->exists ? 'Edit' : 'Tambah'],
        ]"
    />

    @if($readOnly)
        <x-admin.alert type="info" title="Mode Baca">
            Anda sedang melihat formulir ini dalam mode baca saja. Perubahan data tidak dapat disimpan.
        </x-admin.alert>
    @endif

    <form method="POST" action="{{ $action }}" class="space-y-6" id="armada-form">
        @csrf
        @if($method === 'PUT')
            @method('PUT')
        @endif

        {{-- Section 1: Informasi Utama --}}
        <x-admin.card>
            <div class="mb-5 border-b border-slate-100 pb-4 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex size-7 items-center justify-center rounded-lg bg-teal-600 text-xs font-bold text-white shadow-xs">
                        1
                    </span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Informasi Utama</h2>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Pilih kategori kendaraan atau alat berat yang akan dikelola.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Kategori Kendaraan <span class="text-rose-500">*</span>
                    </label>
                    @if($record->exists)
                        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-800 dark:border-white/10 dark:bg-white/5 dark:text-slate-200">
                            <x-admin.icon name="truck" :size="18" class="text-teal-600 dark:text-teal-400" />
                            <span>{{ $kategoriValue }}</span>
                        </div>
                        <input type="hidden" name="kategori" value="{{ $kategoriValue }}">
                    @else
                        <select name="kategori" x-model="kategori" required {{ $readOnly ? 'disabled' : '' }}
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                            @foreach(\App\Enums\KategoriArmadaPersampahan::options() as $val => $label)
                                <option value="{{ $val }}" {{ $kategoriValue === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    @endif
                    @error('kategori')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-admin.card>

        {{-- Section 2: Daftar Hadir / Dynamic Table for Armada --}}
        <x-admin.card>
            <div class="mb-5 border-b border-slate-100 pb-4 dark:border-white/10">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex size-7 items-center justify-center rounded-lg bg-teal-600 text-xs font-bold text-white shadow-xs">
                                2
                            </span>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">Daftar Armada Persampahan</h2>
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Rincian inventaris kendaraan / alat berat yang terdaftar pada kategori ini.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-xs font-extrabold text-teal-800 dark:bg-teal-950/40 dark:text-teal-300">
                            <x-admin.icon name="truck" :size="14" />
                            Total <span x-text="kategori || 'Kendaraan'"></span>: <span x-text="totalUnit + ' Unit'"></span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-white/10">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[600px] text-left text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-400">
                                    <th class="w-14 px-4 py-3.5 text-center">NO</th>
                                    <th class="px-4 py-3.5">MEREK / TYPE</th>
                                    <th class="w-48 px-4 py-3.5">TAHUN PEROLEHAN</th>
                                    @if(!$readOnly)
                                        <th class="w-14 px-4 py-3.5 text-center"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                <template x-for="(row, i) in rows" :key="row.key">
                                    <tr class="transition-colors hover:bg-slate-50/50 dark:hover:bg-white/[0.02]">
                                        <td class="px-4 py-3 text-center font-bold text-slate-400" x-text="i + 1"></td>
                                        <td class="px-4 py-3">
                                            <input
                                                type="text"
                                                x-bind:name="'daftar_armada[' + i + '][merk_type]'"
                                                x-model="row.merk_type"
                                                placeholder="Merek / Type armada"
                                                {{ $readOnly ? 'readonly' : '' }}
                                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-xs outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-white/10 dark:bg-slate-900 dark:text-white"
                                            >
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                type="text"
                                                x-bind:name="'daftar_armada[' + i + '][tahun_perolehan]'"
                                                x-model="row.tahun_perolehan"
                                                placeholder="Contoh: 2021"
                                                {{ $readOnly ? 'readonly' : '' }}
                                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-xs outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-white/10 dark:bg-slate-900 dark:text-white"
                                            >
                                        </td>
                                        @if(!$readOnly)
                                            <td class="px-4 py-3 text-center">
                                                <button
                                                    type="button"
                                                    @click="removeRow(i)"
                                                    title="Hapus baris"
                                                    class="inline-flex size-9 items-center justify-center rounded-xl text-rose-500 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10"
                                                >
                                                    <x-admin.icon name="trash" :size="16" />
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                </template>
                                <tr x-show="rows.length === 0" x-cloak>
                                    <td colspan="{{ $readOnly ? 3 : 4 }}" class="px-4 py-8 text-center text-sm text-slate-400">
                                        Belum ada data armada pada kategori ini. Klik "+ Tambah Baris" untuk mengisi.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer with Total and Actions --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 bg-slate-50/90 px-4 py-3.5 dark:border-white/10 dark:bg-white/5 sm:px-6">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                Total <span x-text="kategori || 'Kendaraan'"></span>:
                            </span>
                            <span class="text-sm font-extrabold text-teal-700 dark:text-teal-300" x-text="totalUnit + ' Unit'"></span>
                        </div>

                        @if(!$readOnly)
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="clearRows()"
                                    x-show="rows.length > 0"
                                    x-cloak
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-500 transition hover:bg-slate-200 dark:text-slate-400 dark:hover:bg-white/10"
                                >
                                    <x-admin.icon name="trash" :size="14" /> Kosongkan
                                </button>
                                <button
                                    type="button"
                                    @click="addRow()"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-teal-700 active:scale-95"
                                >
                                    <x-admin.icon name="plus" :size="14" /> Tambah Baris
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </x-admin.card>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <x-admin.button variant="secondary" :href="route('admin.resources.index', $resource['slug'])">
                {{ $readOnly ? 'Kembali' : 'Batal' }}
            </x-admin.button>
            @if(!$readOnly)
                <x-admin.button variant="primary" type="submit">
                    {{ $record->exists ? 'Perbarui Data' : 'Simpan Data' }}
                </x-admin.button>
            @endif
        </div>
    </form>
</div>
@endsection
