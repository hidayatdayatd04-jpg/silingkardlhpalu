@extends('layouts.admin')

@section('title', $resource['label'].' - SILINGKAR DLH ADMIN')
@section('heading', $resource['label'])

@php
    use App\Enums\ArtikelStatus;
    use Illuminate\Support\Facades\Storage;

    $recordIds = $records->pluck('id')->toArray();
    $filterKeys = collect($resource['filters'])->pluck('key')->all();

    // Statistik global (seluruh artikel, bukan hanya halaman ini).
    $modelClass = $resource['model'];
    $totalArtikel = $modelClass::count();
    $totalPublished = $modelClass::where('status', ArtikelStatus::PUBLISHED->value)->count();
    $totalDraft = max(0, $totalArtikel - $totalPublished);

    // URL thumbnail — temporaryUrl (B2 S3, signing lokal tanpa API call),
    // dengan fallback berjenjang bila file tidak ada / signing gagal.
    $thumbUrl = function ($record) {
        if ($record->isExternal()) {
            return $record->thumbnailUrl();
        }

        if (blank($record->thumbnail)) {
            return null;
        }

        try {
            if (Storage::disk('public')->exists($record->thumbnail)) {
                return Storage::disk('public')->temporaryUrl($record->thumbnail, now()->addHours(24));
            }

            return asset('storage/'.$record->thumbnail);
        } catch (\Throwable $e) {
            try {
                return Storage::url($record->thumbnail);
            } catch (\Throwable $e2) {
                return null;
            }
        }
    };
@endphp

@section('content')
<div
    x-data="{
        selected: [],
        selectAll: false,
        items: {{ json_encode($recordIds) }},
        filterKeys: {{ json_encode($filterKeys) }},
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
            const tracked = ['q', 'status', 'date_from', 'date_to'].concat(this.filterKeys || []);
            const hasFilter = tracked.some(k => {
                const v = q.get(k);
                return v !== null && v !== '';
            });
            return hasFilter ? 'Hasil Filter' : 'Semua Data';
        }
    }"
    x-on:bulk-export.window="bulkExport()"
>
    <div class="space-y-6">
        {{-- ═══════════ Header + breadcrumb navigasi ═══════════ --}}
        <x-admin.page-header
            :title="$resource['label']"
            subtitle="Kelola artikel berita dan publikasi resmi dinas."
            icon="news"
            :breadcrumbs="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => $resource['label']],
            ]"
        >
            <x-slot:actions>
                <x-admin.status-pill class="hidden sm:inline-flex" variant="success" :label="number_format($totalPublished).' tayang'" />
                @if(($resource['can_create'] ?? true))
                    <x-admin.button variant="primary" size="sm" icon="plus" :href="route('admin.resources.create', $resource['slug'])">Tambah artikel</x-admin.button>
                @endif
            </x-slot:actions>
        </x-admin.page-header>

        {{-- ═══════════ Kartu statistik ═══════════ --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-admin.stat-card
                label="Total Artikel"
                :value="$totalArtikel"
                icon="news"
                color="emerald"
                sublabel="Seluruh artikel terdaftar"
                :href="route('admin.resources.index', $resource['slug'])"
            />
            <x-admin.stat-card
                label="Tayang"
                :value="$totalPublished"
                icon="send"
                color="teal"
                sublabel="Tayang di situs publik"
                :href="route('admin.resources.index', ['resource' => $resource['slug'], 'status' => [ArtikelStatus::PUBLISHED->value]])"
            />
            <x-admin.stat-card
                label="Draf"
                :value="$totalDraft"
                icon="file-text"
                color="amber"
                sublabel="Masih draf"
                :href="route('admin.resources.index', ['resource' => $resource['slug'], 'status' => [ArtikelStatus::DRAFT->value]])"
            />
        </div>

        {{-- ═══════════ Tabel artikel ═══════════ --}}
        <x-admin.card :padding="false" class="overflow-hidden">
            <x-admin.bulk-actions-bar :resource="$resource" />

            {{-- Toolbar: pencarian + filter + export --}}
            @php
                $hasFilters = collect($resource['filters'])->contains(fn ($f) =>
                    $f['type'] === 'daterange'
                        ? (request()->filled($f['key'].'_from') || request()->filled($f['key'].'_to'))
                        : filled(request($f['key']))
                );
                $filterCount = collect($resource['filters'])->filter(function ($f) {
                    if ($f['type'] === 'daterange') {
                        return request()->filled($f['key'].'_from') || request()->filled($f['key'].'_to');
                    }
                    $v = request($f['key']);
                    return is_array($v) ? count(array_filter($v)) > 0 : filled($v);
                })->count();
            @endphp
            <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/70 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/35 sm:px-6 lg:flex-row lg:items-center">
                <form method="GET" role="search" class="flex w-full flex-1 items-center gap-2 lg:max-w-md">
                    @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                    @if(request('direction'))<input type="hidden" name="direction" value="{{ request('direction') }}">@endif
                    @foreach($resource['filters'] as $f)
                        @if($f['type'] === 'multiselect')
                            @foreach((array) request($f['key'], []) as $val)
                                @if(filled($val))<input type="hidden" name="{{ $f['key'] }}[]" value="{{ $val }}">@endif
                            @endforeach
                        @elseif($f['type'] === 'select')
                            @if(filled(request($f['key'])))<input type="hidden" name="{{ $f['key'] }}" value="{{ request($f['key']) }}">@endif
                        @elseif($f['type'] === 'daterange')
                            @if(request($f['key'].'_from'))<input type="hidden" name="{{ $f['key'] }}_from" value="{{ request($f['key'].'_from') }}">@endif
                            @if(request($f['key'].'_to'))<input type="hidden" name="{{ $f['key'] }}_to" value="{{ request($f['key'].'_to') }}">@endif
                        @endif
                    @endforeach

                    <div class="relative flex-1">
                        <label for="article-search" class="sr-only">Cari judul artikel</label>
                        <x-admin.icon name="search" :size="17" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500" aria-hidden="true" />
                        <input name="q" value="{{ $search }}" placeholder="Cari judul artikel..."
                            id="article-search" class="h-10 w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-3.5 text-sm text-slate-950 outline-none transition-[border-color,box-shadow,background-color] duration-150 placeholder:text-slate-400 hover:border-slate-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:hover:border-slate-600 dark:focus:border-brand-400 dark:focus:ring-brand-400/20">
                    </div>
                </form>

                <div class="hidden flex-1 lg:block"></div>

                <form method="GET" class="flex flex-wrap items-center gap-2">
                    @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
                    @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                    @if(request('direction'))<input type="hidden" name="direction" value="{{ request('direction') }}">@endif

                    <x-admin.filter-dropdown label="Filter" :active="$hasFilters" :count="$filterCount">
                        @foreach($resource['filters'] as $f)
                            @if($f['type'] === 'multiselect')
                                <x-admin.filter-section :title="$f['label']">
                                    @foreach($f['options'] as $value => $label)
                                        <x-admin.filter-checkbox name="{{ $f['key'] }}[]" :label="$label" :value="$value"
                                            :checked="in_array((string) $value, array_map('strval', (array) request($f['key'], [])), true)" />
                                    @endforeach
                                </x-admin.filter-section>
                            @elseif($f['type'] === 'select')
                                <x-admin.filter-section :title="$f['label']">
                                    <div class="px-1">
                                        <x-admin.select
                                            name="{{ $f['key'] }}"
                                            :options="$f['options']"
                                            :selected="request($f['key'])"
                                            placeholder="Semua"
                                        />
                                    </div>
                                </x-admin.filter-section>
                            @elseif($f['type'] === 'daterange')
                                <x-admin.filter-section :title="$f['label']">
                                    <div class="space-y-3 px-3">
                                        <x-admin.input type="date" name="{{ $f['key'] }}_from" label="Dari" :value="request($f['key'].'_from')" />
                                        <x-admin.input type="date" name="{{ $f['key'] }}_to" label="Sampai" :value="request($f['key'].'_to')" />
                                    </div>
                                </x-admin.filter-section>
                            @endif
                        @endforeach
                        <x-admin.filter-actions :resetUrl="route('admin.resources.index', $resource['slug'])" />
                    </x-admin.filter-dropdown>
                </form>

                <x-admin.export-icons :resource="$resource['slug']" :selectedCount="count($recordIds)" :filter-keys="$filterKeys" />
            </div>

            {{-- Filter aktif --}}
            @php
                $activeFilters = collect();
                foreach ($resource['filters'] as $f) {
                    if ($f['type'] === 'daterange') {
                        if (request()->filled($f['key'].'_from')) {
                            $activeFilters->push([
                                'label' => $f['label'].': '.\Carbon\Carbon::parse(request($f['key'].'_from'))->format('d M Y'),
                                'removeUrl' => request()->fullUrlWithQuery([$f['key'].'_from' => null]),
                            ]);
                        }
                        if (request()->filled($f['key'].'_to')) {
                            $activeFilters->push([
                                'label' => $f['label'].': s/d '.\Carbon\Carbon::parse(request($f['key'].'_to'))->format('d M Y'),
                                'removeUrl' => request()->fullUrlWithQuery([$f['key'].'_to' => null]),
                            ]);
                        }
                    } elseif ($f['type'] === 'multiselect') {
                        foreach ((array) request($f['key'], []) as $val) {
                            if (filled($val) && isset($f['options'][$val])) {
                                $activeFilters->push([
                                    'label' => $f['label'].': '.$f['options'][$val],
                                    'removeUrl' => request()->fullUrlWithQuery([$f['key'] => array_values(array_diff((array) request($f['key']), [$val]))]),
                                ]);
                            }
                        }
                    } elseif ($f['type'] === 'select') {
                        $val = request($f['key']);
                        if (filled($val) && isset($f['options'][$val])) {
                            $activeFilters->push([
                                'label' => $f['label'].': '.$f['options'][$val],
                                'removeUrl' => request()->fullUrlWithQuery([$f['key'] => null]),
                            ]);
                        }
                    }
                }
            @endphp

            @if($activeFilters->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 bg-brand-50/60 px-4 py-3 dark:border-slate-800 dark:bg-brand-950/25 sm:px-6">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Filter aktif:</span>
                    @foreach($activeFilters as $filter)
                        <x-admin.filter-badge :label="$filter['label']" :removeUrl="$filter['removeUrl']" />
                    @endforeach
                    <a href="{{ route('admin.resources.index', $resource['slug']) }}" class="ml-1 rounded px-1 text-sm font-semibold text-slate-600 outline-none transition-colors duration-150 hover:text-danger-600 focus-visible:text-danger-600 dark:text-slate-300 dark:hover:text-danger-300 dark:focus-visible:text-danger-300">
                        Hapus Semua
                    </a>
                </div>
            @endif

            {{-- Tabel data --}}
            <x-admin.table aria-label="Daftar artikel">
                <thead class="bg-slate-50 dark:bg-slate-900">
                    <tr>
                        <x-admin.table.checkbox-header />
                        <x-admin.table.header sortable column="judul"
                            :direction="$sortColumn === 'judul' ? $sortDirection : null">
                            Artikel
                        </x-admin.table.header>
                        <x-admin.table.header sortable column="status"
                            :direction="$sortColumn === 'status' ? $sortDirection : null">
                            Status
                        </x-admin.table.header>
                        <x-admin.table.header sortable column="tanggal_publish"
                            class="whitespace-nowrap"
                            :direction="$sortColumn === 'tanggal_publish' ? $sortDirection : null">
                            Tanggal Tayang
                        </x-admin.table.header>
                        <x-admin.table.header>Penulis</x-admin.table.header>
                        <x-admin.table.header class="text-center">Aksi</x-admin.table.header>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($records as $record)
                        <x-admin.table.row>
                            <x-admin.table.checkbox-cell :value="$record->id" />

                            {{-- Artikel: thumbnail + judul + slug --}}
                            <x-admin.table.cell>
                                @php $thumb = $thumbUrl($record); @endphp
                                <div class="flex items-center gap-3.5">
                                    @if($thumb)
                                        <a href="{{ route('admin.resources.show', [$resource['slug'], $record]) }}"
                                           class="group/thumb block size-12 shrink-0 overflow-hidden rounded-xl border border-slate-200 shadow-sm ring-1 ring-black/5 outline-none transition-[border-color,box-shadow] duration-150 hover:border-brand-300 focus-visible:ring-2 focus-visible:ring-brand-600/30 dark:border-slate-700 dark:hover:border-brand-700">
                                            <img src="{{ $thumb }}" alt="{{ $record->judul }}" loading="lazy"
                                                  class="size-full object-cover transition-transform duration-150 group-hover/thumb:scale-[1.03]" />
                                        </a>
                                    @else
                                        <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 ring-1 ring-brand-100 dark:bg-brand-950/50 dark:text-brand-300 dark:ring-brand-900">
                                            <x-admin.icon name="image" :size="20" />
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.resources.show', [$resource['slug'], $record]) }}"
                                           class="block max-w-[320px] truncate rounded text-sm font-bold text-slate-900 outline-none transition-colors duration-150 hover:text-brand-700 focus-visible:text-brand-700 dark:text-slate-100 dark:hover:text-brand-300 dark:focus-visible:text-brand-300"
                                           title="{{ $record->judul }}">
                                            {{ $record->judul }}
                                        </a>
                                        @if($record->isExternal())
                                            <span class="mt-0.5 flex items-center gap-1 text-xs text-sky-600 dark:text-sky-400">
                                                <x-admin.icon name="external-link" :size="12" class="shrink-0" />
                                                <span class="max-w-[280px] truncate">{{ $record->external_url }}</span>
                                            </span>
                                        @elseif($record->slug)
                                            <span class="mt-0.5 flex items-center gap-1 text-xs text-slate-400">
                                                <x-admin.icon name="globe" :size="12" class="shrink-0" />
                                                <span class="max-w-[280px] truncate">/berita/{{ $record->slug }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </x-admin.table.cell>

                            {{-- Status --}}
                            <x-admin.table.cell>
                                @if($record->status === ArtikelStatus::PUBLISHED)
                                    <x-admin.status-pill variant="success" label="Tayang" pulse />
                                @else
                                    <x-admin.status-pill variant="warning" label="Draf" />
                                @endif
                            </x-admin.table.cell>

                            {{-- Tanggal publish --}}
                            <x-admin.table.cell class="whitespace-nowrap">
                                @if($record->tanggal_publish)
                                    <div class="inline-flex min-w-[190px] items-center gap-2.5">
                                        <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100">
                                            <x-admin.icon name="calendar" :size="15" />
                                        </span>
                                        <span class="whitespace-nowrap">
                                            <span class="block text-sm font-bold text-slate-700">{{ $record->tanggal_publish->translatedFormat('d M Y') }}</span>
                                            <span class="block text-[11px] font-medium text-slate-400">{{ $record->tanggal_publish->diffForHumans() }}</span>
                                        </span>
                                    </div>
                                @else
                                    <span class="text-sm font-medium text-slate-300">—</span>
                                @endif
                            </x-admin.table.cell>

                            {{-- Penulis --}}
                            <x-admin.table.cell>
                                @if($record->user)
                                    <span class="inline-flex items-center gap-2.5">
                                        <x-admin.avatar :name="$record->user->name" size="sm" />
                                        <span class="max-w-[160px] truncate text-sm font-semibold text-slate-700">{{ $record->user->name }}</span>
                                    </span>
                                @else
                                    <span class="text-sm font-medium text-slate-300">—</span>
                                @endif
                            </x-admin.table.cell>

                            {{-- Aksi --}}
                            <x-admin.table.cell class="text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if($record->isInternal())
                                        @php $komenCount = (int) ($record->komentars_count ?? 0); @endphp
                                        <a href="{{ route('admin.artikel.komentar.index', $record->id) }}"
                                            class="group relative grid size-9 place-items-center rounded-xl text-slate-400 outline-none transition-[background-color,color,box-shadow] duration-150 hover:bg-violet-50 hover:text-violet-700 hover:shadow-sm focus-visible:ring-2 focus-visible:ring-violet-500/30 dark:text-slate-500 dark:hover:bg-violet-950/35 dark:hover:text-violet-300" title="Komentar ({{ $komenCount }})" aria-label="Komentar {{ $record->judul }}">
                                              <x-admin.icon name="message" :size="16" class="transition-transform duration-150 group-hover:scale-105" aria-hidden="true" />
                                              @if($komenCount>0)<span class="absolute -right-1 -top-1 grid min-w-[18px] place-items-center rounded-full bg-violet-600 px-1 py-0.5 text-[10px] font-bold leading-none text-white">{{ $komenCount > 99 ? '99+' : $komenCount }}</span>@endif
                                         </a>
                                    @endif
                                    <a href="{{ route('admin.resources.show', [$resource['slug'], $record]) }}"
                                        class="group grid size-9 place-items-center rounded-xl text-slate-400 outline-none transition-[background-color,color,box-shadow] duration-150 hover:bg-info-50 hover:text-info-700 hover:shadow-sm focus-visible:ring-2 focus-visible:ring-info-500/30 dark:text-slate-500 dark:hover:bg-info-950/35 dark:hover:text-info-300" title="Detail" aria-label="Lihat detail {{ $record->judul }}">
                                          <x-admin.icon name="eye" :size="16" class="transition-transform duration-150 group-hover:scale-105" aria-hidden="true" />
                                     </a>
                                    <a href="{{ route('admin.resources.edit', [$resource['slug'], $record]) }}"
                                        class="group grid size-9 place-items-center rounded-xl text-slate-400 outline-none transition-[background-color,color,box-shadow] duration-150 hover:bg-brand-50 hover:text-brand-700 hover:shadow-sm focus-visible:ring-2 focus-visible:ring-brand-600/30 dark:text-slate-500 dark:hover:bg-brand-950/45 dark:hover:text-brand-300" title="Edit" aria-label="Edit {{ $record->judul }}">
                                          <x-admin.icon name="edit" :size="16" class="transition-transform duration-150 group-hover:scale-105" aria-hidden="true" />
                                     </a>
                                    <button type="button"
                                        x-data="" x-on:click="$dispatch('open-modal', 'del-{{ $record->id }}')"
                                        class="group grid size-9 place-items-center rounded-xl text-slate-400 outline-none transition-[background-color,color,box-shadow] duration-150 hover:bg-danger-50 hover:text-danger-700 hover:shadow-sm focus-visible:ring-2 focus-visible:ring-danger-500/30 dark:text-slate-500 dark:hover:bg-danger-950/35 dark:hover:text-danger-300" title="Hapus" aria-label="Hapus {{ $record->judul }}">
                                          <x-admin.icon name="trash" :size="16" class="transition-transform duration-150 group-hover:scale-105" aria-hidden="true" />
                                     </button>
                                </div>

                                <x-admin.confirm-delete
                                    name="del-{{ $record->id }}"
                                    :action="route('admin.resources.destroy', [$resource['slug'], $record])"
                                    title="Hapus Artikel"
                                    message="Artikel ini akan dihapus permanen. Aksi ini tidak bisa dibatalkan."
                                />
                            </x-admin.table.cell>
                        </x-admin.table.row>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state
                                    icon="news"
                                    title="Belum ada artikel"
                                    description="Artikel yang ditambahkan akan tampil di sini. Ubah kata kunci pencarian jika diperlukan."
                                    :actionText="($resource['can_create'] ?? true) ? 'Tambah Artikel Pertama' : null"
                                    :actionUrl="($resource['can_create'] ?? true) ? route('admin.resources.create', $resource['slug']) : null"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-admin.table>

            {{-- Pagination + ringkasan jumlah --}}
            <div class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/40 px-4 py-4 dark:border-slate-800 dark:bg-slate-950/25 sm:flex-row sm:px-6">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                    @if($records->total() > 0)
                        Menampilkan {{ $records->firstItem() }}–{{ $records->lastItem() }} dari {{ number_format($records->total()) }} artikel
                    @else
                        Tidak ada artikel yang cocok
                    @endif
                </p>
                {{ $records->links() }}
            </div>
        </x-admin.card>
    </div>
</div>
@endsection
