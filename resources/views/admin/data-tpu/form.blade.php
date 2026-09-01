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

    $existingPhotoList = $record->exists ? $record->getDokumentasiList() : [];
@endphp

@section('content')
<div
    x-data="{
        vegetasiList: {{ json_encode($initialVegetasi) }},
        blokList: {{ json_encode($initialBlok) }},
        existingPhotos: {{ json_encode($existingPhotoList) }},
        newPhotos: [],
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
        removeExistingPhoto(index) {
            this.existingPhotos.splice(index, 1);
        },
        handleNewFiles(event) {
            const files = Array.from(event.target.files);
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.newPhotos.push({
                        file: file,
                        preview: e.target.result,
                        name: file.name
                    });
                };
                reader.readAsDataURL(file);
            });
            event.target.value = '';
        },
        removeNewPhoto(index) {
            this.newPhotos.splice(index, 1);
        }
    }"
    class="max-w-5xl mx-auto space-y-6"
>
    {{-- Header Form --}}
    <x-admin.page-header
        :title="($record->exists ? 'Edit ' : 'Tambah ').$resource['label']"
        :subtitle="$record->exists ? 'Perbarui informasi data TPU '.$record->nama_tpu : 'Tambahkan inventaris data TPU baru beserta vegetasi, kapasitas blok, dan dokumentasi foto lapangan.'"
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

        {{-- Section 4: Dokumentasi Foto (Dinamis: Bisa 0, 1, 2, 3 atau lebih) --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                        4
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Dokumentasi Foto Lapangan</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Unggah foto dokumentasi area TPU (fleksibel: bisa kosong, satu, dua, atau ditambah sesuai kebutuhan)</p>
                    </div>
                </div>

                @if(!$readOnly)
                    <label class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition-all cursor-pointer">
                        <x-icons.ui name="plus" class="w-3.5 h-3.5" />
                        <span>Tambah Foto</span>
                        <input
                            type="file"
                            multiple
                            accept="image/jpeg,image/jpg,image/png,image/webp,image/avif"
                            class="hidden"
                            @change="handleNewFiles($event)"
                        />
                    </label>
                @endif
            </div>

            {{-- Hidden Inputs untuk Foto yang Dipertahankan --}}
            <template x-for="(item, index) in existingPhotos" :key="'exist-' + index">
                <input type="hidden" name="existing_photos[]" :value="item.path" />
            </template>

            {{-- Galeri Preview Foto --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                {{-- 1. Kartu Foto Eksisting yang Tersimpan --}}
                <template x-for="(item, index) in existingPhotos" :key="'card-exist-' + index">
                    <div class="relative group rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 aspect-video shadow-sm">
                        <img :src="item.url" :alt="item.label" class="w-full h-full object-cover" />
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent flex flex-col justify-between p-2.5">
                            <div class="flex justify-end">
                                @if(!$readOnly)
                                    <button
                                        type="button"
                                        @click="removeExistingPhoto(index)"
                                        class="p-1.5 rounded-lg bg-rose-600 text-white hover:bg-rose-700 shadow-md transition-colors cursor-pointer"
                                        title="Hapus Foto Ini"
                                    >
                                        <x-icons.ui name="trash" class="w-3.5 h-3.5" />
                                    </button>
                                @endif
                            </div>
                            <span class="text-[11px] font-bold text-white tracking-wide" x-text="item.label || ('Foto ' + (index + 1))"></span>
                        </div>
                    </div>
                </template>

                {{-- 2. Kartu Foto Baru yang Dipilih (Siap Diunggah) --}}
                <template x-for="(item, index) in newPhotos" :key="'card-new-' + index">
                    <div class="relative group rounded-2xl overflow-hidden border-2 border-emerald-500/60 bg-emerald-50/30 dark:bg-emerald-950/20 aspect-video shadow-sm">
                        <img :src="item.preview" :alt="item.name" class="w-full h-full object-cover" />
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent flex flex-col justify-between p-2.5">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded-md bg-emerald-600 text-white text-[10px] font-bold">Baru</span>
                                <button
                                    type="button"
                                    @click="removeNewPhoto(index)"
                                    class="p-1.5 rounded-lg bg-rose-600 text-white hover:bg-rose-700 shadow-md transition-colors cursor-pointer"
                                    title="Batalkan Foto Ini"
                                >
                                    <x-icons.ui name="close" class="w-3.5 h-3.5" />
                                </button>
                            </div>
                            <span class="text-[11px] font-bold text-white tracking-wide truncate" x-text="item.name"></span>
                        </div>
                    </div>
                </template>

                {{-- 3. Kotak Upload Area Tambah Foto --}}
                @if(!$readOnly)
                    <label class="rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-amber-500 dark:hover:border-amber-400 bg-slate-50/50 dark:bg-slate-800/40 hover:bg-amber-50/30 dark:hover:bg-amber-950/20 aspect-video flex flex-col items-center justify-center p-4 text-center cursor-pointer transition-colors group">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <x-icons.ui name="camera" class="w-5 h-5" />
                        </div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Pilih / Tambah Foto</span>
                        <span class="text-[10px] text-slate-400 mt-0.5">Mendukung JPG, PNG, WEBP (maks 5MB)</span>
                        <input
                            type="file"
                            name="new_photos[]"
                            multiple
                            accept="image/jpeg,image/jpg,image/png,image/webp,image/avif"
                            class="hidden"
                            @change="handleNewFiles($event)"
                        />
                    </label>
                @endif
            </div>

            {{-- Pesan Jika Kosong (Read-Only Mode) --}}
            @if($readOnly)
                <template x-if="existingPhotos.length === 0">
                    <div class="p-6 text-center text-slate-400 text-xs italic bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                        Tidak ada dokumentasi foto yang diunggah.
                    </div>
                </template>
            @endif
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
