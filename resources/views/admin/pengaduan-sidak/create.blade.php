@extends('layouts.admin')

@section('title', 'Jadwalkan Sidak dari Pengaduan')

@section('content')
<div class="space-y-6">
    <x-admin.page-header
        title="Jadwalkan Sidak"
        subtitle="Buat jadwal sidak dari pengaduan {{ $pengaduan->nomor_tiket }}"
        icon="calendar"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Pengaduan Tata Penataan', 'url' => route('admin.resources.index', 'pengaduan-tata-penataan')],
            ['label' => $pengaduan->nomor_tiket, 'url' => route('admin.resources.show', ['pengaduan-tata-penataan', $pengaduan])],
            ['label' => 'Jadwalkan Sidak'],
        ]"
    >
        <x-slot:actions>
            <x-admin.button variant="secondary" size="sm" icon="chevron-left" :href="route('admin.resources.show', ['pengaduan-tata-penataan', $pengaduan])">
                Kembali
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Form --}}
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('admin.pengaduan-tata-penataan.buat-sidak', $pengaduan) }}">
                @csrf
                <x-admin.section-card title="Data Sidak" icon="clipboard-check">
                    <div class="space-y-5">
                        <div>
                            <label for="objek_pengawasan_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                Objek Pengawasan <span class="text-red-500">*</span>
                            </label>
                            <select name="objek_pengawasan_id" id="objek_pengawasan_id" required
                                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Pilih Objek Pengawasan</option>
                                @php
                                    $objeks = \App\Models\ObjekPengawasan::orderBy('nama_perusahaan')->get();
                                @endphp
                                @foreach($objeks as $objek)
                                    <option value="{{ $objek->id }}" {{ old('objek_pengawasan_id', $objekPengawasan?->id) == $objek->id ? 'selected' : '' }}>
                                        {{ $objek->nama_perusahaan }} - {{ $objek->alamat }}
                                    </option>
                                @endforeach
                            </select>
                            @error('objek_pengawasan_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="tanggal_sidak" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                    Tanggal Sidak <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="tanggal_sidak" id="tanggal_sidak" value="{{ old('tanggal_sidak', now()->format('Y-m-d')) }}" required
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
                                @error('tanggal_sidak')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="nama_petugas" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                    Nama Petugas <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama_petugas" id="nama_petugas" value="{{ old('nama_petugas', auth()->user()->name) }}" required
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
                                @error('nama_petugas')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="catatan_jadwal" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                Catatan Jadwal
                            </label>
                            <textarea name="catatan_jadwal" id="catatan_jadwal" rows="3"
                                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500"
                                placeholder="Catatan tambahan untuk jadwal sidak ini...">{{ old('catatan_jadwal') }}</textarea>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex justify-end gap-3">
                            <x-admin.button variant="secondary" :href="route('admin.resources.show', ['pengaduan-tata-penataan', $pengaduan])">
                                Batal
                            </x-admin.button>
                            <x-admin.button variant="primary" type="submit" icon="check">
                                Jadwalkan Sidak
                            </x-admin.button>
                        </div>
                    </x-slot:footer>
                </x-admin.section-card>
            </form>
        </div>

        {{-- Info Pengaduan --}}
        <div class="space-y-6">
            <x-admin.section-card title="Info Pengaduan" icon="info-circle">
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Nomor Tiket</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $pengaduan->nomor_tiket }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Pelapor</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $pengaduan->nama_pelapor }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Jenis Pengaduan</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $pengaduan->jenis_pengaduan?->label() ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Perusahaan Terlapor</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $pengaduan->nama_perusahaan_terlapor ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Lokasi</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $pengaduan->alamat }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Deskripsi</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $pengaduan->deskripsi }}</p>
                    </div>
                </div>
            </x-admin.section-card>

            @if($pengaduan->fotos->isNotEmpty())
            <x-admin.section-card title="Foto Bukti" icon="image">
                <div class="grid grid-cols-2 gap-2">
                    @foreach($pengaduan->fotos->take(4) as $foto)
                        <img src="{{ Storage::url($foto->path_foto) }}" alt="Foto Bukti" 
                            class="rounded-lg object-cover w-full h-24">
                    @endforeach
                </div>
            </x-admin.section-card>
            @endif
        </div>
    </div>
</div>
@endsection
