@extends('layouts.admin')

@section('title', \App\Support\Admin\AdminRegistry::titleFor($record, $resource).' - Admin DLH')
@section('heading', $resource['label'])

@section('content')
@php
    $format = function ($value) {
        if ($value instanceof BackedEnum) {
            return method_exists($value, 'label') ? $value->label() : $value->value;
        }
        if ($value instanceof DateTimeInterface) {
            return $value->format('d M Y H:i');
        }
        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }
        if (is_array($value)) {
            return filled($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : '-';
        }
        return filled($value) ? (string) $value : '-';
    };

    $statusValue = $record->status ?? $record->status_sanksi ?? $record->status_tindak_lanjut ?? null;
    $statusText = $statusValue ? $format($statusValue) : null;
    $statusVariant = match (true) {
        !$statusText => 'neutral',
        in_array($statusText, ['Ditinjau', 'Ditindaklanjuti', 'Selesai', 'Aktif', 'approved', 'selesai', 'aktif'], true) => 'success',
        in_array($statusText, ['Belum Ditinjau', 'Belum Ditindaklanjuti', 'pending', 'diajukan', 'menunggu', 'draft'], true) => 'warning',
        in_array($statusText, ['Ditolak', 'Gagal', 'Batal', 'ditolak', 'gagal', 'nonaktif'], true) => 'danger',
        default => 'info',
    };

    $displayFields = collect($fields)
        ->reject(fn ($field) => in_array($field['type'] ?? null, ['section', 'relation_files', 'photos'], true))
        ->values();
    $mainFields = $displayFields->reject(fn ($field) => in_array($field['type'] ?? null, ['file', 'textarea'], true)
        || str_contains($field['name'], 'latitude') || str_contains($field['name'], 'longitude')
        || str_contains($field['name'], 'alamat') || str_contains($field['name'], 'lokasi')
    )->values();
    $locationFields = $displayFields->filter(fn ($field) => str_contains($field['name'], 'latitude')
        || str_contains($field['name'], 'longitude') || str_contains($field['name'], 'alamat')
        || str_contains($field['name'], 'lokasi')
    )->values();
    $textareaFields = $displayFields->filter(fn ($field) => ($field['type'] ?? null) === 'textarea')->values();
    $fileFields = $displayFields->filter(fn ($field) => ($field['type'] ?? null) === 'file')->values();

    $relationConfigs = [
        ['relation' => 'fotos', 'title' => 'Foto Bukti', 'path' => 'path_foto', 'name' => null, 'image' => true],
        ['relation' => 'dokumens', 'title' => 'Dokumen Relasi', 'path' => null, 'name' => null, 'image' => false],
        ['relation' => 'media', 'title' => 'Media', 'path' => 'path', 'name' => null, 'image' => true],
        ['relation' => 'files', 'title' => 'File Relasi', 'path' => 'path', 'name' => 'nama', 'image' => false],
        ['relation' => 'pesertas', 'title' => 'Peserta/Objek Terkait', 'path' => 'sertifikat_path', 'name' => null, 'image' => false],
    ];

    $pathFor = function ($item, $config) {
        if ($config['path']) return $item->{$config['path']} ?? null;
        foreach (['path_dokumen', 'file_path', 'path', 'sertifikat_path'] as $field) {
            if (filled($item->{$field} ?? null)) return $item->{$field};
        }
        return null;
    };
    $nameFor = function ($item, $path, $config) {
        if ($config['name'] && filled($item->{$config['name']} ?? null)) return $item->{$config['name']};
        foreach (['nama_dokumen', 'jenis_dokumen', 'tipe'] as $field) {
            if (filled($item->{$field} ?? null)) return $item->{$field};
        }
        return $path ? basename((string) $path) : 'Data terkait';
    };

    $lat = $record->latitude ?? null;
    $lng = $record->longitude ?? null;
    $hasCoords = $lat !== null && $lng !== null && $lat != 0 && $lng != 0;

    $iconFor = function ($fieldName) {
        return match(true) {
            str_contains($fieldName, 'nomor') => 'file-text',
            str_contains($fieldName, 'nama') || str_contains($fieldName, 'pelapor') => 'user',
            in_array($fieldName, ['email', 'username'], true) => 'mail',
            str_contains($fieldName, 'status') => 'alert-circle',
            str_contains($fieldName, 'alamat') || str_contains($fieldName, 'lokasi') => 'map-pin',
            str_contains($fieldName, 'tanggal') => 'calendar',
            default => 'file-text',
        };
    };
@endphp

<div x-data x-init="window.staggerReveal($el.querySelectorAll('.stagger-item'), 80)" class="space-y-6">

    <x-admin.page-header
        :title="\App\Support\Admin\AdminRegistry::titleFor($record, $resource)"
        :subtitle="$record->created_at ? 'Dibuat ' . $record->created_at->translatedFormat('d F Y, H:i') : null"
        :breadcrumbs="[
            ['label' => $resource['label'], 'url' => route('admin.resources.index', $resource['slug'])],
            ['label' => 'Detail'],
        ]"
    >
        <x-slot:actions>
            @if($statusText)
                <x-admin.status-pill :variant="$statusVariant" :label="$statusText" :pulse="$statusVariant === 'warning'" />
            @endif
            @if($resource['slug'] === 'pengaduan-tata-penataan')
                <x-admin.button variant="success" size="sm" icon="calendar" :href="route('admin.pengaduan-tata-penataan.buat-sidak', $record)">
                    Jadwalkan Sidak
                </x-admin.button>
            @endif
            <x-admin.button variant="secondary" size="sm" icon="chevron-left" :href="route('admin.resources.index', $resource['slug'])">Kembali</x-admin.button>
            <x-admin.button variant="primary" size="sm" icon="edit" :href="route('admin.resources.edit', [$resource['slug'], $record])">Edit</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            @if($mainFields->isNotEmpty())
                <div class="stagger-item">
                    <x-admin.section-card title="Informasi Utama" icon="file-text">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                            @foreach($mainFields as $field)
                                @php $value = $record->{$field['name']} ?? null; @endphp
                                <x-admin.detail-field :label="$field['label']" :icon="$iconFor($field['name'])">
                                    @if(($field['type'] ?? null) === 'checkbox')
                                        {{ $value ? 'Ya' : 'Tidak' }}
                                    @elseif(str_contains($field['name'], 'status') && filled($value))
                                        <x-admin.status-pill :variant="$statusVariant" :label="$format($value)" />
                                    @else
                                        {{ $format($value) }}
                                    @endif
                                </x-admin.detail-field>
                            @endforeach
                        </div>
                    </x-admin.section-card>
                </div>
            @endif

            @if($textareaFields->isNotEmpty())
                <div class="stagger-item">
                    <x-admin.section-card title="Deskripsi & Catatan" icon="message">
                        <div class="space-y-5">
                            @foreach($textareaFields as $field)
                                <div>
                                    <p class="mb-1.5 text-caption font-bold uppercase tracking-[0.1em] text-slate-500">{{ $field['label'] }}</p>
                                    <p class="whitespace-pre-line text-sm leading-relaxed text-ink-700">{{ $format($record->{$field['name']} ?? null) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </x-admin.section-card>
                </div>
            @endif

            {{-- Riwayat Sidak untuk Pengaduan Tata Penataan --}}
            @if($resource['slug'] === 'pengaduan-tata-penataan' && method_exists($record, 'sidaks') && $record->sidaks->isNotEmpty())
                <div class="stagger-item">
                    <x-admin.section-card title="Riwayat Sidak" icon="clipboard-check">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-700">
                                        <th class="pb-3 text-left font-semibold text-slate-600 dark:text-slate-400">Tanggal</th>
                                        <th class="pb-3 text-left font-semibold text-slate-600 dark:text-slate-400">Objek</th>
                                        <th class="pb-3 text-left font-semibold text-slate-600 dark:text-slate-400">Hasil</th>
                                        <th class="pb-3 text-left font-semibold text-slate-600 dark:text-slate-400">Status</th>
                                        <th class="pb-3 text-left font-semibold text-slate-600 dark:text-slate-400">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($record->sidaks as $sidak)
                                        <tr>
                                            <td class="py-3 text-slate-700 dark:text-slate-300">{{ $sidak->tanggal_sidak->format('d M Y') }}</td>
                                            <td class="py-3 text-slate-700 dark:text-slate-300">{{ $sidak->objekPengawasan?->nama_perusahaan ?? '-' }}</td>
                                            <td class="py-3">
                                                @if($sidak->hasil)
                                                    <x-admin.status-pill :variant="$sidak->hasil_color ?? 'info'" :label="$sidak->hasil_label ?? $sidak->hasil" />
                                                @else
                                                    <span class="text-slate-400">-</span>
                                                @endif
                                            </td>
                                            <td class="py-3">
                                                <x-admin.status-pill :variant="$sidak->status_tindak_lanjut->value === 'selesai' ? 'success' : 'warning'" :label="$sidak->status_tindak_lanjut?->label() ?? '-'" />
                                            </td>
                                            <td class="py-3">
                                                <a href="{{ route('admin.resources.show', ['sidak', $sidak]) }}" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">
                                                    Lihat Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-admin.section-card>
                </div>
            @endif

            {{-- Relation galleries / files --}}
            @foreach($relationConfigs as $config)
                @if(method_exists($record, $config['relation']) && $record->{$config['relation']} && $record->{$config['relation']}->isNotEmpty())
                    <div class="stagger-item">
                        <x-admin.section-card :title="$config['title']" :icon="$config['image'] ? 'eye' : 'folder'">
                            @if($config['image'])
                                @php
                                    $imgs = $record->{$config['relation']}->map(function ($item) use ($pathFor, $nameFor, $config) {
                                        $p = $pathFor($item, $config);
                                        return $p ? ['url' => Storage::url($p), 'caption' => $nameFor($item, $p, $config)] : null;
                                    })->filter()->values()->all();
                                @endphp
                                <x-admin.lightbox :images="$imgs" :columns="3" />
                            @else
                                <div class="space-y-3">
                                    @foreach($record->{$config['relation']} as $item)
                                        @php $path = $pathFor($item, $config); $label = $nameFor($item, $path, $config); @endphp
                                        <div class="flex items-center justify-between gap-4 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                                            <span class="text-sm font-semibold text-ink-700">{{ $label }}</span>
                                            @if($path)
                                                <a href="{{ Storage::url($path) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg bg-info-50 px-3 py-1.5 text-xs font-bold text-info-700 transition hover:bg-info-100">
                                                    <x-admin.icon name="file-text" :size="14" /> Lihat
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </x-admin.section-card>
                    </div>
                @endif
            @endforeach

            @if($fileFields->isNotEmpty())
                <div class="stagger-item">
                    <x-admin.section-card title="Lampiran & Dokumen" icon="folder">
                        <div class="space-y-3">
                            @foreach($fileFields as $field)
                                @php $path = $record->{$field['name']} ?? null; @endphp
                                <div class="flex items-center justify-between gap-4 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                                    <span class="text-sm font-semibold text-ink-700">{{ $field['label'] }}</span>
                                    @if($path)
                                        <a href="{{ Storage::url($path) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg bg-info-50 px-3 py-1.5 text-xs font-bold text-info-700 transition hover:bg-info-100">
                                            <x-admin.icon name="file-text" :size="14" /> Lihat file
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400">Tidak ada file</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </x-admin.section-card>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            @if($locationFields->isNotEmpty())
                <div class="stagger-item">
                    <x-admin.section-card title="Lokasi & Koordinat" icon="map-pin">
                        @if($hasCoords)
                            <div id="admin-detail-map" style="height:280px" class="mb-4 w-full overflow-hidden rounded-lg border border-slate-200 bg-slate-100"></div>
                        @endif
                        <div class="space-y-4">
                            @foreach($locationFields as $field)
                                <x-admin.detail-field :label="$field['label']" :value="$format($record->{$field['name']} ?? null)" />
                            @endforeach
                        </div>
                    </x-admin.section-card>
                </div>
                @if($hasCoords)
                    @push('scripts')
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            window.ensureMaplibreLoaded(function () {
                                var map = new maplibregl.Map({
                                    container: 'admin-detail-map',
                                    style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
                                    center: [{{ $lng }}, {{ $lat }}],
                                    zoom: 15,
                                    attributionControl: false
                                });
                                map.addControl(new maplibregl.NavigationControl({ showCompass: false, visualizePitch: false }), 'top-left');
                                if (window.DlhBasemapSwitcher) map.addControl(new DlhBasemapSwitcher(), 'bottom-right');
                                if (window.dlhAddLocBtn) dlhAddLocBtn(map);
                                new maplibregl.Marker({ anchor: 'center' })
                                    .setLngLat([{{ $lng }}, {{ $lat }}])
                                    .setPopup(new maplibregl.Popup({ offset: [0, -20] }).setText('Lokasi'))
                                    .addTo(map);
                                setTimeout(function () { map.resize(); }, 200);
                            });
                        });
                    </script>
                    @endpush
                @endif
            @endif

            <div class="stagger-item">
                <x-admin.section-card title="Informasi Sistem" icon="clock">
                    <div class="space-y-4">
                        <x-admin.detail-field label="Dibuat" icon="calendar" :value="$record->created_at?->translatedFormat('d F Y, H:i')" />
                        <x-admin.detail-field label="Diperbarui" icon="clock" :value="$record->updated_at?->translatedFormat('d F Y, H:i')" />
                    </div>
                </x-admin.section-card>
            </div>

            <div class="stagger-item rounded-xl border border-danger-200 bg-danger-50/50 p-5">
                <div class="flex items-start gap-3">
                    <div class="grid size-10 shrink-0 place-items-center rounded-lg bg-danger-100 text-danger-600">
                        <x-admin.icon name="trash" :size="20" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-ink-900">Hapus Data</p>
                        <p class="mt-0.5 text-xs text-slate-500">Tindakan ini permanen dan tidak dapat dibatalkan.</p>
                        <div class="mt-3">
                            <x-admin.button variant="danger" size="sm" icon="trash" x-data="" x-on:click="$dispatch('open-modal', 'generic-delete')">
                                Hapus Data
                            </x-admin.button>
                        </div>
                    </div>
                </div>
            </div>

            <x-admin.confirm-delete
                name="generic-delete"
                :action="route('admin.resources.destroy', [$resource['slug'], $record])"
                title="Hapus Data"
                message="Data ini akan dihapus permanen. Aksi ini tidak bisa dibatalkan."
            />
        </div>
    </div>
</div>
@endsection
