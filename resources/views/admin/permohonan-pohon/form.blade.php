@extends('layouts.admin')

@section('title', ($record->exists ? 'Proses Permohonan '.$record->nomor_tiket : 'Tambah Permohonan Pohon').' - SILINGKAR DLH ADMIN')
@section('heading', $resource['label'])

@php
    $statusOptions = \App\Enums\StatusPermohonanPohon::options();
    $currentStatus = old('status', $record->status instanceof \BackedEnum ? $record->status->value : ($record->status ?: 'Diajukan'));
    $fotoSebelumList = $record->exists ? $record->getFotoSebelumList() : [];
    $fotoSesudahList = $record->exists ? $record->getFotoSesudahList() : [];
@endphp

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <x-admin.page-header
        :title="$record->exists ? 'Pemrosesan & Verifikasi: ' . $record->nomor_tiket : 'Tambah Permohonan Pohon Baru'"
        subtitle="Kelola verifikasi, hasil survei lapangan, persetujuan, jadwal pelaksanaan, serta unggah dokumentasi eksekusi."
        icon="axe"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => $resource['label'], 'url' => route('admin.resources.index', $resource['slug'])],
            ['label' => $record->exists ? $record->nomor_tiket : 'Tambah Baru'],
        ]"
    >
        <x-slot:actions>
            <a
                href="{{ $record->exists ? route('admin.resources.show', [$resource['slug'], $record]) : route('admin.resources.index', $resource['slug']) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition shadow-sm"
            >
                <x-icons.ui name="arrow-left" class="size-4" />
                <span>Batal / Kembali</span>
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50/80 p-4 dark:border-rose-900/50 dark:bg-rose-950/30">
            <div class="flex items-center gap-3 text-rose-700 dark:text-rose-300 font-bold text-sm">
                <x-icons.ui name="alert-triangle" class="size-5 shrink-0" />
                <span>Mohon periksa kembali isian formulir:</span>
            </div>
            <ul class="mt-2 list-disc list-inside text-xs text-rose-600 dark:text-rose-400 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ $action }}"
        method="POST"
        enctype="multipart/form-data"
        class="space-y-6"
        x-data="{
            status: '{{ $currentStatus }}',
            existingSebelum: {{ json_encode(array_column($fotoSebelumList, 'path')) }},
            existingSesudah: {{ json_encode(array_column($fotoSesudahList, 'path')) }},
            removeSebelum(index) {
                this.existingSebelum.splice(index, 1);
            },
            removeSesudah(index) {
                this.existingSesudah.splice(index, 1);
            }
        }"
    >
        @csrf
        @if($method === 'PUT')
            @method('PUT')
        @endif

        {{-- Section 1: Alur & Status Permohonan --}}
        <div class="rounded-2xl border border-brand-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                <span class="size-9 rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300 flex items-center justify-center font-bold">1</span>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Alur Status Penanganan</h3>
                    <p class="text-xs text-slate-500">Pilih tahapan proses yang sedang atau telah diselesaikan oleh tim DLH.</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                <div>
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        Status Saat Ini <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="status"
                        id="status"
                        x-model="status"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                        required
                    >
                        @foreach($statusOptions as $val => $lbl)
                            <option value="{{ $val }}" {{ $currentStatus === $val ? 'selected' : '' }}>
                                {{ $lbl }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Field Alasan Penolakan Jika Ditolak --}}
                <div x-show="status === 'Ditolak'" x-transition class="p-4 rounded-xl bg-rose-50/80 border border-rose-200 dark:bg-rose-950/30 dark:border-rose-900/50 space-y-2">
                    <label for="alasan_penolakan" class="block text-xs font-bold uppercase tracking-wider text-rose-800 dark:text-rose-300">
                        Alasan Penolakan <span class="text-rose-600">*</span>
                    </label>
                    <textarea
                        name="alasan_penolakan"
                        id="alasan_penolakan"
                        rows="3"
                        placeholder="Contoh: Pohon berada di dalam pekarangan rumah milik warga (area privat), bukan sempadan jalan raya publik, sehingga menjadi kewenangan pemilik lahan."
                        class="w-full rounded-xl border border-rose-300 dark:border-rose-800 bg-white dark:bg-slate-800 p-3 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500/20"
                    >{{ old('alasan_penolakan', $record->alasan_penolakan) }}</textarea>
                    <p class="text-[11px] text-rose-600 dark:text-rose-400">Alasan ini akan dapat dilihat oleh pelapor saat mengecek status permohonan.</p>
                </div>
            </div>
        </div>

        {{-- Section 2: Hasil Verifikasi & Survei Lapangan --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                <span class="size-9 rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 flex items-center justify-center font-bold">2</span>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Verifikasi & Hasil Survei Lapangan</h3>
                    <p class="text-xs text-slate-500">Catatan kepemilikan area publik, observasi fisik pohon, dan rekomendasi teknis.</p>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label for="catatan_verifikasi" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Catatan Verifikasi (Kesesuaian Area Fasum / Publik)
                    </label>
                    <textarea
                        name="catatan_verifikasi"
                        id="catatan_verifikasi"
                        rows="2"
                        placeholder="Hasil verifikasi lokasi: telah dikonfirmasi berada di sempadan jalan / jalur hijau publik..."
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-3 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                    >{{ old('catatan_verifikasi', $record->catatan_verifikasi) }}</textarea>
                </div>

                <div>
                    <label for="tanggal_survei" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Tanggal Survei Lapangan
                    </label>
                    <input
                        type="date"
                        name="tanggal_survei"
                        id="tanggal_survei"
                        value="{{ old('tanggal_survei', $record->tanggal_survei?->format('Y-m-d')) }}"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                    />
                </div>

                <div>
                    <label for="petugas_survei" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Nama Petugas Survei
                    </label>
                    <input
                        type="text"
                        name="petugas_survei"
                        id="petugas_survei"
                        value="{{ old('petugas_survei', $record->petugas_survei) }}"
                        placeholder="Nama tim/petugas survei DLH"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                    />
                </div>

                <div class="sm:col-span-2">
                    <label for="kondisi_pohon" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Kondisi Fisik Pohon
                    </label>
                    <textarea
                        name="kondisi_pohon"
                        id="kondisi_pohon"
                        rows="2"
                        placeholder="Tinggi pohon ±8m, diameter ±60cm, batang tengah mulai keropos/lapuk, condong 20 derajat ke arah jalan..."
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-3 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                    >{{ old('kondisi_pohon', $record->kondisi_pohon) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label for="rekomendasi_tindakan" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Rekomendasi Tindakan Teknis
                    </label>
                    <textarea
                        name="rekomendasi_tindakan"
                        id="rekomendasi_tindakan"
                        rows="2"
                        placeholder="Contoh: Pemangkasan berat pada dahan sisi barat yang menyentuh kabel listrik, atau Penebangan total demi keselamatan pengguna jalan..."
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-3 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                    >{{ old('rekomendasi_tindakan', $record->rekomendasi_tindakan) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label for="catatan_survei" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Catatan Tambahan Survei
                    </label>
                    <textarea
                        name="catatan_survei"
                        id="catatan_survei"
                        rows="2"
                        placeholder="Catatan pendukung lainnya..."
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-3 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                    >{{ old('catatan_survei', $record->catatan_survei) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 3: Jadwal Pelaksanaan & Dokumentasi Eksekusi --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                <span class="size-9 rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300 flex items-center justify-center font-bold">3</span>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Jadwal Pelaksanaan & Dokumentasi Eksekusi</h3>
                    <p class="text-xs text-slate-500">Penetapan jadwal eksekusi tim lapangan dan bukti foto sebelum & sesudah pekerjaan.</p>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="tanggal_pelaksanaan" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Tanggal / Jadwal Pelaksanaan
                    </label>
                    <input
                        type="date"
                        name="tanggal_pelaksanaan"
                        id="tanggal_pelaksanaan"
                        value="{{ old('tanggal_pelaksanaan', $record->tanggal_pelaksanaan?->format('Y-m-d')) }}"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                    />
                </div>

                <div>
                    <label for="tim_pelaksana" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Tim Pelaksana Lapangan
                    </label>
                    <input
                        type="text"
                        name="tim_pelaksana"
                        id="tim_pelaksana"
                        value="{{ old('tim_pelaksana', $record->tim_pelaksana) }}"
                        placeholder="Contoh: Regu A Bidang RTH DLH"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                    />
                </div>

                <div class="sm:col-span-2">
                    <label for="catatan_pelaksanaan" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Catatan Pelaksanaan
                    </label>
                    <textarea
                        name="catatan_pelaksanaan"
                        id="catatan_pelaksanaan"
                        rows="2"
                        placeholder="Pekerjaan selesai dilaksanakan dengan aman, ranting/kayu telah diangkut ke TPA..."
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-3 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                    >{{ old('catatan_pelaksanaan', $record->catatan_pelaksanaan) }}</textarea>
                </div>

                {{-- Upload Foto Sebelum --}}
                <div class="sm:col-span-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                        Foto Dokumentasi SEBELUM Eksekusi
                    </label>

                    {{-- Existing photos --}}
                    <div class="flex flex-wrap gap-3" x-show="existingSebelum.length > 0">
                        <template x-for="(path, idx) in existingSebelum" :key="idx">
                            <div class="relative size-20 rounded-lg overflow-hidden border border-slate-300 dark:border-slate-700">
                                <input type="hidden" name="existing_foto_sebelum[]" :value="path" />
                                <img :src="'/storage/' + path" class="size-full object-cover" />
                                <button type="button" @click="removeSebelum(idx)" class="absolute top-1 right-1 p-0.5 rounded-full bg-rose-600 text-white hover:bg-rose-700 cursor-pointer">
                                    <x-icons.ui name="close" class="size-3" />
                                </button>
                            </div>
                        </template>
                    </div>

                    <div>
                        <input
                            type="file"
                            name="foto_sebelum[]"
                            multiple
                            accept="image/jpeg,image/png,image/webp,image/avif"
                            class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-slate-700 dark:file:text-slate-200 cursor-pointer"
                        />
                        <p class="mt-1 text-[11px] text-slate-400">Pilih satu atau lebih file foto sebelum pohon dipotong/ditebang. Maksimal 5MB per foto.</p>
                    </div>
                </div>

                {{-- Upload Foto Sesudah --}}
                <div class="sm:col-span-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                        Foto Dokumentasi SESUDAH Eksekusi
                    </label>

                    {{-- Existing photos --}}
                    <div class="flex flex-wrap gap-3" x-show="existingSesudah.length > 0">
                        <template x-for="(path, idx) in existingSesudah" :key="idx">
                            <div class="relative size-20 rounded-lg overflow-hidden border border-slate-300 dark:border-slate-700">
                                <input type="hidden" name="existing_foto_sesudah[]" :value="path" />
                                <img :src="'/storage/' + path" class="size-full object-cover" />
                                <button type="button" @click="removeSesudah(idx)" class="absolute top-1 right-1 p-0.5 rounded-full bg-rose-600 text-white hover:bg-rose-700 cursor-pointer">
                                    <x-icons.ui name="close" class="size-3" />
                                </button>
                            </div>
                        </template>
                    </div>

                    <div>
                        <input
                            type="file"
                            name="foto_sesudah[]"
                            multiple
                            accept="image/jpeg,image/png,image/webp,image/avif"
                            class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-slate-700 dark:file:text-slate-200 cursor-pointer"
                        />
                        <p class="mt-1 text-[11px] text-slate-400">Pilih satu atau lebih file foto setelah pemangkasan/penebangan selesai. Maksimal 5MB per foto.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Data Pelapor Asli (Read-only review saat edit) --}}
        @if($record->exists)
            <div class="rounded-2xl border border-slate-200/80 bg-slate-50/50 p-6 dark:border-slate-800 dark:bg-slate-900/40">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Tinjauan Data Pengajuan Awal Warga</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 font-semibold">Nama Pelapor:</span>
                        <p class="font-bold text-slate-800 dark:text-slate-200">{{ $record->nama_pelapor }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold">Nomor WhatsApp:</span>
                        <p class="font-bold text-slate-800 dark:text-slate-200">{{ $record->nomor_hp }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold">Jenis Tindakan:</span>
                        <p class="font-bold text-slate-800 dark:text-slate-200">{{ $record->jenis_tindakan?->value }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold">Jenis Pohon:</span>
                        <p class="font-bold text-slate-800 dark:text-slate-200">{{ $record->jenis_pohon ?: '-' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-slate-400 font-semibold">Lokasi Pohon:</span>
                        <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $record->lokasi_pohon }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-slate-400 font-semibold">Alasan Pengajuan:</span>
                        <p class="text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $record->alasan_pengajuan }}</p>
                    </div>
                </div>
            </div>
        @else
            {{-- Form Create Manual jika admin membuat data baru --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Data Permohonan Pelapor</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Nama Pelapor *</label>
                        <input type="text" name="nama_pelapor" required class="w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:bg-slate-800" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Nomor HP/WA *</label>
                        <input type="tel" name="nomor_hp" required class="w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:bg-slate-800" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Jenis Tindakan *</label>
                        <select name="jenis_tindakan" class="w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:bg-slate-800">
                            <option value="Pemangkasan">Pemangkasan</option>
                            <option value="Penebangan">Penebangan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Jenis Pohon</label>
                        <input type="text" name="jenis_pohon" class="w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:bg-slate-800" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Lokasi Pohon (Fasum) *</label>
                        <textarea name="lokasi_pohon" rows="2" required class="w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:bg-slate-800"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Alasan Pengajuan *</label>
                        <textarea name="alasan_pengajuan" rows="2" required class="w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:bg-slate-800"></textarea>
                    </div>
                </div>
            </div>
        @endif

        {{-- Action Buttons --}}
        <div class="flex items-center justify-end gap-3 pt-4">
            <a
                href="{{ $record->exists ? route('admin.resources.show', [$resource['slug'], $record]) : route('admin.resources.index', $resource['slug']) }}"
                class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
            >
                Batal
            </a>
            <button
                type="submit"
                class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-sm font-bold shadow-sm transition flex items-center gap-2 cursor-pointer"
            >
                <x-icons.ui name="check" class="size-4" />
                <span>Simpan Pembaruan</span>
            </button>
        </div>
    </form>
</div>
@endsection
