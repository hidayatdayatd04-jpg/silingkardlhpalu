@extends('layouts.admin')

@section('title', $resource['label'].' - SILINGKAR DLH ADMIN')
@section('heading', $resource['label'])

@php
    $canEdit = $resource['can_edit'] ?? true;
    $canCreate = ($resource['can_create'] ?? true) && (!auth()->user()?->isSuperadmin() || ($resource['group'] ?? null) === 'konten');
    $isSuperadmin = auth()->user()?->isSuperadmin() ?? false;
    $recordIds = $records->pluck('id')->toArray();
@endphp

@section('content')
<div
    x-data="{
        selected: [],
        selectAll: false,
        items: {{ json_encode($recordIds) }},
        lightboxOpen: false,
        lightboxImage: '',
        lightboxTitle: '',
        openLightbox(url, title) {
            this.lightboxImage = url;
            this.lightboxTitle = title;
            this.lightboxOpen = true;
        },
        closeLightbox() {
            this.lightboxOpen = false;
        },
        bulkExport(format = 'xlsx') {
            const params = new URLSearchParams();
            this.selected.forEach(id => params.append('ids[]', id));
            params.append('format', format);
            window.location.href = '{{ route('admin.resources.bulk-export', $resource['slug']) }}?' + params.toString();
        },
        exportHref(format) {
            if (this.selected.length > 0) {
                const params = new URLSearchParams();
                this.selected.forEach(id => params.append('ids[]', id));
                params.append('format', format);
                return '{{ route('admin.resources.bulk-export', $resource['slug']) }}?' + params.toString();
            }
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);
            params.delete('page');
            params.set('format', format);
            return '{{ route('admin.resources.export', $resource['slug']) }}?' + params.toString();
        },
        exportScopeLabel() {
            if (this.selected.length > 0) {
                return 'Terpilih (' + this.selected.length + ')';
            }
            const q = new URLSearchParams(window.location.search);
            const hasFilter = q.get('q') !== null && q.get('q') !== '';
            return hasFilter ? 'Hasil Filter' : 'Semua Data';
        }
    }"
    x-on:bulk-export.window="bulkExport()"
    x-on:keydown.escape.window="closeLightbox()"
    class="space-y-6"
>
    {{-- Header Page --}}
    <x-admin.page-header
        :title="$resource['label']"
        subtitle="Inventarisasi data Taman Pemakaman Umum (TPU), kapasitas blok makam, dan vegetasi pohon pelindung di Kota Palu."
        icon="park"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'RTH', 'url' => '#'],
            ['label' => $resource['label']],
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                {{-- Dropdown Ekspor --}}
                <div x-data="{ exportOpen: false }" @click.outside="exportOpen = false" class="relative">
                    <button
                        @click="exportOpen = !exportOpen"
                        type="button"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition-all shadow-sm cursor-pointer"
                    >
                        <x-icons.ui name="download" class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                        <span>Ekspor</span>
                        <x-icons.ui name="chevron-down" class="w-3.5 h-3.5 transition-transform" ::class="exportOpen ? 'rotate-180' : ''" />
                    </button>
                    <div
                        x-show="exportOpen"
                        x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-48 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl py-1 z-30"
                    >
                        <div class="px-3 py-1.5 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                            Format Dokumen
                        </div>
                        <a :href="exportHref('xlsx')" class="flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Excel (.xlsx)</span>
                        </a>
                        <a :href="exportHref('csv')" class="flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                            <span>CSV (.csv)</span>
                        </a>
                    </div>
                </div>

                {{-- Tombol Tambah --}}
                @if($canCreate)
                    <a
                        href="{{ route('admin.resources.create', $resource['slug']) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white shadow-sm shadow-brand-600/20 hover:shadow-md transition-all cursor-pointer"
                    >
                        <x-icons.ui name="plus" class="w-4 h-4" />
                        <span>Tambah TPU</span>
                    </a>
                @endif
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Mode Baca Info Banner untuk Superadmin --}}
    @if($isSuperadmin)
        <div class="rounded-2xl p-4 bg-amber-500/10 border border-amber-500/20 text-amber-900 dark:text-amber-200 flex items-start gap-3">
            <x-icons.ui name="info-circle" class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" />
            <div class="text-sm">
                <span class="font-bold">Mode Baca (Read-Only):</span> Sebagai Administrator Utama, Anda dapat meninjau dan mengekspor seluruh data TPU. Pengelolaan dan pembaruan data operasional dilakukan oleh admin <strong>Bidang Ruang Terbuka Hijau (RTH)</strong>.
            </div>
        </div>
    @endif

    {{-- Statistik Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl p-5 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total TPU</p>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($totalTpu ?? 0, 0, ',', '.') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Taman Pemakaman Terdaftar</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                    <x-icons.ui name="park" class="w-6 h-6" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl p-5 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Luas</p>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $records->count() > 0 ? $records->sum(fn($r) => (float) filter_var($r->luas_area_makam, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0 }} Ha</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Luas Lahan TPU Aktif</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <x-icons.ui name="tree" class="w-6 h-6" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl p-5 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estimasi Kapasitas</p>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($totalMakam ?? 0, 0, ',', '.') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Total Makam Tersedia</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                    <x-icons.ui name="building" class="w-6 h-6" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl p-5 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Vegetasi Pohon</p>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($totalPohon ?? 0, 0, ',', '.') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pohon Pelindung Area TPU</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <x-icons.ui name="seedling" class="w-6 h-6" />
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Pencarian --}}
    <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('admin.resources.index', $resource['slug']) }}" class="w-full sm:w-80 relative">
            <input
                type="text"
                name="q"
                value="{{ $search ?? '' }}"
                placeholder="Cari nama TPU..."
                class="w-full pl-9 pr-4 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all"
            />
            <x-icons.ui name="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
            @if(filled($search ?? ''))
                <a href="{{ route('admin.resources.index', $resource['slug']) }}" class="absolute right-3 top-2.5 text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Reset</a>
            @endif
        </form>

        <div class="flex items-center gap-2 self-end sm:self-auto text-xs text-slate-500 dark:text-slate-400">
            <span>Menampilkan <strong>{{ $records->count() }}</strong> dari <strong>{{ $records->total() }}</strong> TPU</span>
        </div>
    </div>

    {{-- Tabel Format Spreadsheet TPU --}}
    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                {{-- Header Utama Tabel Sesuai Format Excel --}}
                <thead>
                    <tr class="bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700">
                        <th rowspan="2" class="px-3 py-3 font-bold text-center w-12 border-r border-slate-200 dark:border-slate-700">No.</th>
                        <th colspan="2" class="px-4 py-2 font-bold text-center border-r border-slate-200 dark:border-slate-700 bg-slate-200/60 dark:bg-slate-800">Informasi Umum</th>
                        <th colspan="2" class="px-4 py-2 font-bold text-center border-r border-slate-200 dark:border-slate-700 bg-emerald-100/40 dark:bg-emerald-950/20 text-emerald-900 dark:text-emerald-300">Vegetasi</th>
                        <th colspan="6" class="px-4 py-2 font-bold text-center border-r border-slate-200 dark:border-slate-700 bg-sky-100/40 dark:bg-sky-950/20 text-sky-900 dark:text-sky-300">Kapasitas Blok</th>
                        <th rowspan="2" class="px-3 py-3 font-bold text-center border-r border-slate-200 dark:border-slate-700 min-w-[150px]">Dokumentasi Foto</th>
                        <th rowspan="2" class="px-4 py-3 font-bold text-center w-28">Aksi</th>
                    </tr>
                    <tr class="bg-slate-50 dark:bg-slate-800/40 text-xs font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
                        <th class="px-3 py-2 text-center border-r border-slate-200 dark:border-slate-700 min-w-[140px]">Nama TPU</th>
                        <th class="px-3 py-2 text-center border-r border-slate-200 dark:border-slate-700 min-w-[90px]">Luas Area</th>
                        <th class="px-3 py-2 text-center border-r border-slate-200 dark:border-slate-700 min-w-[160px]">Jenis Pohon Pelindung</th>
                        <th class="px-3 py-2 text-center border-r border-slate-200 dark:border-slate-700 min-w-[80px]">Jumlah</th>
                        <th class="px-3 py-2 text-center border-r border-slate-200 dark:border-slate-700 min-w-[90px]">Agama</th>
                        <th class="px-3 py-2 text-center border-r border-slate-200 dark:border-slate-700 min-w-[90px]">Jumlah Blok</th>
                        <th class="px-3 py-2 text-center border-r border-slate-200 dark:border-slate-700 min-w-[100px]">Kap. / Blok</th>
                        <th class="px-3 py-2 text-center border-r border-slate-200 dark:border-slate-700 min-w-[110px]">Total Kapasitas</th>
                        <th class="px-3 py-2 text-center border-r border-slate-200 dark:border-slate-700 min-w-[90px]">Terisi</th>
                        <th class="px-3 py-2 text-center border-r border-slate-200 dark:border-slate-700 min-w-[90px]">Kosong</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($records as $index => $record)
                        @php
                            $vegetasi = is_array($record->vegetasi) ? $record->vegetasi : [];
                            $kapasitas = is_array($record->kapasitas_blok) ? $record->kapasitas_blok : [];
                            $photos = $record->getDokumentasiList();
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors align-top">
                            {{-- No. --}}
                            <td class="px-3 py-4 text-center font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">
                                {{ $records->firstItem() + $index }}.
                            </td>

                            {{-- Nama TPU --}}
                            <td class="px-3 py-4 font-bold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-800">
                                <span class="block text-base">{{ $record->nama_tpu }}</span>
                                <span class="inline-block mt-1 text-[11px] font-medium px-2 py-0.5 rounded-full bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300">
                                    {{ count($vegetasi) }} Jenis Pohon
                                </span>
                            </td>

                            {{-- Luas Area Makam --}}
                            <td class="px-3 py-4 text-center font-semibold text-slate-800 dark:text-slate-200 border-r border-slate-200 dark:border-slate-800 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold">
                                    {{ $record->luas_area_makam }}
                                </span>
                            </td>

                            {{-- Vegetasi (Jenis Pohon Pelindung & Jumlah) --}}
                            <td colspan="2" class="p-0 border-r border-slate-200 dark:border-slate-800">
                                @if(empty($vegetasi))
                                    <div class="p-4 text-xs text-slate-400 italic text-center">Belum ada data vegetasi</div>
                                @else
                                    <table class="w-full text-xs divide-y divide-slate-100 dark:divide-slate-800/60">
                                        @foreach($vegetasi as $v)
                                            <tr>
                                                <td class="px-3 py-1.5 text-slate-700 dark:text-slate-300 border-r border-slate-100 dark:border-slate-800/60 font-medium">
                                                    {{ $v['jenis_pohon'] ?? '-' }}
                                                </td>
                                                <td class="px-3 py-1.5 text-slate-800 dark:text-slate-200 text-center font-bold whitespace-nowrap w-24">
                                                    {{ $v['jumlah'] ?? '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif
                            </td>

                            {{-- Kapasitas Blok (Agama, Jumlah Blok, Kapasitas per Blok, Total Kapasitas, Makam Terisi, Makam Kosong) --}}
                            <td colspan="6" class="p-0 border-r border-slate-200 dark:border-slate-800">
                                @if(empty($kapasitas))
                                    <div class="p-4 text-xs text-slate-400 italic text-center">Belum ada data blok</div>
                                @else
                                    <table class="w-full text-xs divide-y divide-slate-100 dark:divide-slate-800/60">
                                        @foreach($kapasitas as $k)
                                            <tr>
                                                <td class="px-3 py-1.5 text-slate-800 dark:text-slate-200 font-semibold border-r border-slate-100 dark:border-slate-800/60">
                                                    {{ $k['agama'] ?? '-' }}
                                                </td>
                                                <td class="px-3 py-1.5 text-slate-700 dark:text-slate-300 text-center border-r border-slate-100 dark:border-slate-800/60 whitespace-nowrap">
                                                    {{ $k['jumlah_blok'] ?? '-' }}
                                                </td>
                                                <td class="px-3 py-1.5 text-slate-600 dark:text-slate-400 text-center border-r border-slate-100 dark:border-slate-800/60 whitespace-nowrap">
                                                    {{ $k['kapasitas_per_blok'] ?? '-' }}
                                                </td>
                                                <td class="px-3 py-1.5 text-slate-900 dark:text-white font-bold text-center border-r border-slate-100 dark:border-slate-800/60 whitespace-nowrap">
                                                    {{ $k['jumlah_makam'] ?? '-' }}
                                                </td>
                                                <td class="px-3 py-1.5 text-amber-700 dark:text-amber-300 font-semibold text-center border-r border-slate-100 dark:border-slate-800/60 whitespace-nowrap">
                                                    {{ $k['makam_terisi'] ?? '-' }}
                                                </td>
                                                <td class="px-3 py-1.5 text-emerald-700 dark:text-emerald-300 font-semibold text-center whitespace-nowrap">
                                                    {{ $k['makam_kosong'] ?? '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif
                            </td>

                            {{-- Dokumentasi Foto (Dinamis) --}}
                            <td class="px-3 py-3 border-r border-slate-200 dark:border-slate-800">
                                @if(count($photos) > 0)
                                    <div class="flex flex-wrap items-center gap-1.5 justify-center">
                                        @foreach($photos as $p)
                                            <div class="relative group cursor-pointer inline-block rounded-lg overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700 w-16 h-12 shrink-0 bg-slate-100 dark:bg-slate-800"
                                                 @click="openLightbox('{{ $p['url'] }}', '{{ $p['label'] }} - {{ $record->nama_tpu }}')">
                                                <img src="{{ $p['url'] }}" alt="{{ $p['label'] }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-200" />
                                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white">
                                                    <x-icons.ui name="eye" class="w-3.5 h-3.5" />
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-[10px] font-bold text-slate-400 text-center mt-1">{{ count($photos) }} Foto</p>
                                @else
                                    <div class="text-center">
                                        <span class="text-xs text-slate-400 italic">Kosong</span>
                                    </div>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-3 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Detail --}}
                                    <a
                                        href="{{ route('admin.resources.show', [$resource['slug'], $record]) }}"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                        title="Lihat Detail"
                                    >
                                        <x-icons.ui name="eye" class="w-4 h-4" />
                                    </a>

                                    @if(!$isSuperadmin)
                                        {{-- Edit --}}
                                        <a
                                            href="{{ route('admin.resources.edit', [$resource['slug'], $record]) }}"
                                            class="p-1.5 rounded-lg text-sky-600 hover:text-sky-700 hover:bg-sky-50 dark:hover:bg-sky-950/40 transition-colors"
                                            title="Ubah Data"
                                        >
                                            <x-icons.ui name="edit" class="w-4 h-4" />
                                        </a>

                                        {{-- Hapus --}}
                                        <form
                                            method="POST"
                                            action="{{ route('admin.resources.destroy', [$resource['slug'], $record]) }}"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data {{ $record->nama_tpu }}?');"
                                            class="inline-block"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                                                title="Hapus Data"
                                            >
                                                <x-icons.ui name="trash" class="w-4 h-4" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <x-icons.ui name="park" class="w-10 h-10 text-slate-300 dark:text-slate-600" />
                                    <p class="text-base font-semibold">Belum Ada Data TPU</p>
                                    <p class="text-xs">Data Taman Pemakaman Umum yang didaftarkan akan tampil di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($records->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                {{ $records->links() }}
            </div>
        @endif
    </div>

    {{-- Lightbox Modal --}}
    <div
        x-show="lightboxOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
        @click.self="closeLightbox()"
    >
        <div class="relative max-w-4xl max-h-[90vh] bg-slate-900 rounded-2xl overflow-hidden shadow-2xl border border-slate-700">
            <div class="flex items-center justify-between px-4 py-3 bg-slate-800/90 text-white border-b border-slate-700">
                <span class="text-sm font-semibold truncate" x-text="lightboxTitle"></span>
                <button @click="closeLightbox()" class="p-1 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white transition-colors cursor-pointer">
                    <x-icons.ui name="close" class="w-5 h-5" />
                </button>
            </div>
            <div class="p-2 flex items-center justify-center max-h-[calc(90vh-60px)]">
                <img :src="lightboxImage" alt="Preview Foto" class="max-h-[calc(90vh-80px)] max-w-full object-contain rounded-lg" />
            </div>
        </div>
    </div>
</div>
@endsection
