@extends('layouts.admin')

@section('title', ($record->exists ? 'Edit ' : 'Tambah ').$resource['label'].' - SILINGKAR DLH ADMIN')
@section('heading', $resource['label'])

@php
    $readOnly = (bool) ($readOnly ?? false);
    $initialVegetasi = old('vegetasi', $record->vegetasi ?? [
        ['jenis_pohon' => '', 'jumlah' => '']
    ]);
    if (empty($initialVegetasi) || !is_array($initialVegetasi)) {
        $initialVegetasi = [['jenis_pohon' => '', 'jumlah' => '']];
    }

    $initialBlok = old('kapasitas_blok', $record->kapasitas_blok ?? [
        ['agama' => 'Islam', 'jumlah_blok' => '', 'jumlah_makam' => ''],
        ['agama' => 'Kristen', 'jumlah_blok' => '', 'jumlah_makam' => ''],
        ['agama' => 'Hindu', 'jumlah_blok' => '', 'jumlah_makam' => ''],
        ['agama' => 'Buddha', 'jumlah_blok' => '', 'jumlah_makam' => ''],
    ]);
    if (empty($initialBlok) || !is_array($initialBlok)) {
        $initialBlok = [
            ['agama' => 'Islam', 'jumlah_blok' => '', 'jumlah_makam' => ''],
            ['agama' => 'Kristen', 'jumlah_blok' => '', 'jumlah_makam' => ''],
        ];
    }
@endphp

@section('content')
<div
    x-data="{
        vegetasiList: {{ json_encode($initialVegetasi) }},
        blokList: {{ json_encode($initialBlok) }},
        doc1Preview: '{{ $record->foto_dokumentasi_1 ? asset('storage/'.ltrim($record->foto_dokumentasi_1, '/')) : '' }}',
        doc2Preview: '{{ $record->foto_dokumentasi_2 ? asset('storage/'.ltrim($record->foto_dokumentasi_2, '/')) : '' }}',
        doc3Preview: '{{ $record->foto_dokumentasi_3 ? asset('storage/'.ltrim($record->foto_dokumentasi_3, '/')) : '' }}',
        addVegetasi() {
            this.vegetasiList.push({ jenis_pohon: '', jumlah: '' });
        },
        removeVegetasi(index) {
            if (this.vegetasiList.length > 1) {
                this.vegetasiList.splice(index, 1);
            }
        },
        addBlok() {
            this.blokList.push({ agama: '', jumlah_blok: '', jumlah_makam: '' });
        },
        removeBlok(index) {
            if (this.blokList.length > 1) {
                this.blokList.splice(index, 1);
            }
        },
        previewFile(event, docIndex) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (docIndex === 1) this.doc1Preview = e.target.result;
                    if (docIndex === 2) this.doc2Preview = e.target.result;
                    if (docIndex === 3) this.doc3Preview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    }"
    class="max-w-5xl mx-auto space-y-6"
>
    {{-- Header Form --}}
    <x-admin.page-header
        :title="($record->exists ? 'Edit ' : 'Tambah ').$resource['label']"
        :subtitle="$record->exists ? 'Perbarui informasi data TPU '.$record->nama_tpu : 'Tambahkan inventaris data TPU baru beserta vegetasi dan kapasitas blok.'"
        icon="park"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => $resource['label'], 'url' => route('admin.resources.index', $resource['slug'])],
            ['label' => $record->exists ? 'Edit Data' : 'Tambah Baru'],
        ]"
    />

    {{-- Banner Mode Baca --}}
    @if($readOnly)
        <div class="rounded-2xl p-4 bg-amber-500/10 border border-amber-500/20 text-amber-900 dark:text-amber-200 flex items-start gap-3">
            <x-icons.ui name="lock" class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" />
            <div class="text-sm">
                <span class="font-bold">Mode Baca (Read-Only):</span> Anda masuk sebagai Administrator Utama. Data hanya dapat dilihat dan tidak dapat diubah dari akun ini.
            </div>
        </div>
    @endif

    {{-- Form Container --}}
    <form
        method="POST"
        action="{{ $action }}"
        enctype="multipart/form-data"
        class="space-y-6"
    >
        @csrf
        @if($method === 'PUT')
            @method('PUT')
        @endif

        {{-- Section 1: Informasi Umum --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold text-sm">
                    1
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Informasi Umum</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Nama TPU dan estimasi luas lahan</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Nama TPU --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Nama TPU <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="nama_tpu"
                        value="{{ old('nama_tpu', $record->nama_tpu) }}"
                        placeholder="Contoh: TPU Lambara"
                        @if($readOnly) disabled @endif
                        required
                        class="w-full px-4 py-2.5 rounded-xl text-sm border @error('nama_tpu') border-rose-500 @else border-slate-200 dark:border-slate-700 @enderror bg-slate-50/50 dark:bg-slate-800/80 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all disabled:opacity-60 disabled:cursor-not-allowed"
                    />
                    @error('nama_tpu')
                        <p class="text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Luas Area Makam --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Luas Area Makam <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="luas_area_makam"
                        value="{{ old('luas_area_makam', $record->luas_area_makam) }}"
                        placeholder="Contoh: 2 Ha"
                        @if($readOnly) disabled @endif
                        required
                        class="w-full px-4 py-2.5 rounded-xl text-sm border @error('luas_area_makam') border-rose-500 @else border-slate-200 dark:border-slate-700 @enderror bg-slate-50/50 dark:bg-slate-800/80 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all disabled:opacity-60 disabled:cursor-not-allowed"
                    />
                    <p class="text-[11px] text-slate-400">Tuliskan satuan, misalnya: <strong>2 Ha</strong> atau <strong>15.000 m²</strong></p>
                    @error('luas_area_makam')
                        <p class="text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Section 2: Vegetasi Pohon Pelindung --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                        2
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Vegetasi Pohon Pelindung</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Rincian jenis pohon pelindung dan jumlah tanaman di TPU</p>
                    </div>
                </div>

                @if(!$readOnly)
                    <button
                        type="button"
                        @click="addVegetasi()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 dark:hover:bg-emerald-900/50 transition-all cursor-pointer"
                    >
                        <x-icons.ui name="plus" class="w-3.5 h-3.5" />
                        <span>Tambah Baris Pohon</span>
                    </button>
                @endif
            </div>

            {{-- Repeater Baris Vegetasi --}}
            <div class="space-y-3">
                <template x-for="(item, index) in vegetasiList" :key="'veg-' + index">
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                        <div class="w-6 text-center text-xs font-bold text-slate-400" x-text="index + 1 + '.'"></div>
                        
                        {{-- Jenis Pohon --}}
                        <div class="flex-1">
                            <input
                                type="text"
                                :name="'vegetasi[' + index + '][jenis_pohon]'"
                                x-model="item.jenis_pohon"
                                placeholder="Jenis Pohon (misal: Trambesi, Kamboja, Tanjung)"
                                @if($readOnly) disabled @endif
                                class="w-full px-3.5 py-2 rounded-lg text-sm border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 disabled:opacity-60"
                            />
                        </div>

                        {{-- Jumlah --}}
                        <div class="w-40 sm:w-48">
                            <input
                                type="text"
                                :name="'vegetasi[' + index + '][jumlah]'"
                                x-model="item.jumlah"
                                placeholder="Jumlah (misal: 13, 1 rumpun)"
                                @if($readOnly) disabled @endif
                                class="w-full px-3.5 py-2 rounded-lg text-sm border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 disabled:opacity-60"
                            />
                        </div>

                        @if(!$readOnly)
                            <button
                                type="button"
                                @click="removeVegetasi(index)"
                                class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                                title="Hapus Baris"
                                :disabled="vegetasiList.length <= 1"
                                :class="vegetasiList.length <= 1 ? 'opacity-30 cursor-not-allowed' : ''"
                            >
                                <x-icons.ui name="trash" class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                </template>
            </div>
        </div>

        {{-- Section 3: Kapasitas Blok Makam --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-sm">
                        3
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Kapasitas Blok Makam</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pembagian blok dan daya tampung makam per agama</p>
                    </div>
                </div>

                @if(!$readOnly)
                    <button
                        type="button"
                        @click="addBlok()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-sky-50 text-sky-700 hover:bg-sky-100 dark:bg-sky-900/30 dark:text-sky-300 dark:hover:bg-sky-900/50 transition-all cursor-pointer"
                    >
                        <x-icons.ui name="plus" class="w-3.5 h-3.5" />
                        <span>Tambah Blok Agama</span>
                    </button>
                @endif
            </div>

            {{-- Repeater Baris Kapasitas Blok --}}
            <div class="space-y-3">
                <template x-for="(item, index) in blokList" :key="'blok-' + index">
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                        <div class="w-6 text-center text-xs font-bold text-slate-400" x-text="index + 1 + '.'"></div>
                        
                        {{-- Agama --}}
                        <div class="w-44 sm:w-56">
                            <input
                                type="text"
                                :name="'kapasitas_blok[' + index + '][agama]'"
                                x-model="item.agama"
                                placeholder="Agama (misal: Islam, Kristen)"
                                @if($readOnly) disabled @endif
                                class="w-full px-3.5 py-2 rounded-lg text-sm border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 disabled:opacity-60 font-semibold"
                            />
                        </div>

                        {{-- Jumlah Blok --}}
                        <div class="flex-1">
                            <input
                                type="text"
                                :name="'kapasitas_blok[' + index + '][jumlah_blok]'"
                                x-model="item.jumlah_blok"
                                placeholder="Jumlah Blok (misal: 88 blok)"
                                @if($readOnly) disabled @endif
                                class="w-full px-3.5 py-2 rounded-lg text-sm border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 disabled:opacity-60"
                            />
                        </div>

                        {{-- Jumlah Makam --}}
                        <div class="flex-1">
                            <input
                                type="text"
                                :name="'kapasitas_blok[' + index + '][jumlah_makam]'"
                                x-model="item.jumlah_makam"
                                placeholder="Jumlah Makam (misal: 1.408 makam)"
                                @if($readOnly) disabled @endif
                                class="w-full px-3.5 py-2 rounded-lg text-sm border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 disabled:opacity-60"
                            />
                        </div>

                        @if(!$readOnly)
                            <button
                                type="button"
                                @click="removeBlok(index)"
                                class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                                title="Hapus Baris"
                                :disabled="blokList.length <= 1"
                                :class="blokList.length <= 1 ? 'opacity-30 cursor-not-allowed' : ''"
                            >
                                <x-icons.ui name="trash" class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                </template>
            </div>
        </div>

        {{-- Section 4: Dokumentasi 1, 2, 3 --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                    4
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Dokumentasi Foto</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Unggah hingga 3 foto dokumentasi lapangan area TPU</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                {{-- Dokumentasi 1 --}}
                <div class="space-y-2 rounded-xl p-4 bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Dokumentasi 1
                    </label>
                    <div class="aspect-video rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 overflow-hidden relative group flex items-center justify-center">
                        <template x-if="doc1Preview">
                            <img :src="doc1Preview" alt="Dokumentasi 1" class="w-full h-full object-cover" />
                        </template>
                        <template x-if="!doc1Preview">
                            <div class="flex flex-col items-center justify-center text-slate-400 text-xs">
                                <x-icons.ui name="image" class="w-8 h-8 mb-1" />
                                <span>Belum ada foto</span>
                            </div>
                        </template>
                    </div>
                    @if(!$readOnly)
                        <input
                            type="file"
                            name="foto_dokumentasi_1"
                            accept="image/jpeg,image/jpg,image/png,image/webp,image/avif"
                            @change="previewFile($event, 1)"
                            class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer"
                        />
                        @if($record->foto_dokumentasi_1)
                            <label class="flex items-center gap-2 text-xs text-rose-600 mt-1 cursor-pointer">
                                <input type="checkbox" name="remove_foto_dokumentasi_1" value="1" class="rounded text-rose-600 focus:ring-rose-500" />
                                <span>Hapus Foto 1</span>
                            </label>
                        @endif
                    @endif
                </div>

                {{-- Dokumentasi 2 --}}
                <div class="space-y-2 rounded-xl p-4 bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Dokumentasi 2
                    </label>
                    <div class="aspect-video rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 overflow-hidden relative group flex items-center justify-center">
                        <template x-if="doc2Preview">
                            <img :src="doc2Preview" alt="Dokumentasi 2" class="w-full h-full object-cover" />
                        </template>
                        <template x-if="!doc2Preview">
                            <div class="flex flex-col items-center justify-center text-slate-400 text-xs">
                                <x-icons.ui name="image" class="w-8 h-8 mb-1" />
                                <span>Belum ada foto</span>
                            </div>
                        </template>
                    </div>
                    @if(!$readOnly)
                        <input
                            type="file"
                            name="foto_dokumentasi_2"
                            accept="image/jpeg,image/jpg,image/png,image/webp,image/avif"
                            @change="previewFile($event, 2)"
                            class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer"
                        />
                        @if($record->foto_dokumentasi_2)
                            <label class="flex items-center gap-2 text-xs text-rose-600 mt-1 cursor-pointer">
                                <input type="checkbox" name="remove_foto_dokumentasi_2" value="1" class="rounded text-rose-600 focus:ring-rose-500" />
                                <span>Hapus Foto 2</span>
                            </label>
                        @endif
                    @endif
                </div>

                {{-- Dokumentasi 3 --}}
                <div class="space-y-2 rounded-xl p-4 bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Dokumentasi 3
                    </label>
                    <div class="aspect-video rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 overflow-hidden relative group flex items-center justify-center">
                        <template x-if="doc3Preview">
                            <img :src="doc3Preview" alt="Dokumentasi 3" class="w-full h-full object-cover" />
                        </template>
                        <template x-if="!doc3Preview">
                            <div class="flex flex-col items-center justify-center text-slate-400 text-xs">
                                <x-icons.ui name="image" class="w-8 h-8 mb-1" />
                                <span>Belum ada foto</span>
                            </div>
                        </template>
                    </div>
                    @if(!$readOnly)
                        <input
                            type="file"
                            name="foto_dokumentasi_3"
                            accept="image/jpeg,image/jpg,image/png,image/webp,image/avif"
                            @change="previewFile($event, 3)"
                            class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer"
                        />
                        @if($record->foto_dokumentasi_3)
                            <label class="flex items-center gap-2 text-xs text-rose-600 mt-1 cursor-pointer">
                                <input type="checkbox" name="remove_foto_dokumentasi_3" value="1" class="rounded text-rose-600 focus:ring-rose-500" />
                                <span>Hapus Foto 3</span>
                            </label>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a
                href="{{ route('admin.resources.index', $resource['slug']) }}"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            >
                Kembali
            </a>

            @if(!$readOnly)
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white shadow-md shadow-brand-600/20 transition-all cursor-pointer"
                >
                    <x-icons.ui name="save" class="w-4 h-4" />
                    <span>{{ $record->exists ? 'Perbarui Data TPU' : 'Simpan Data TPU' }}</span>
                </button>
            @endif
        </div>
    </form>
</div>
@endsection
