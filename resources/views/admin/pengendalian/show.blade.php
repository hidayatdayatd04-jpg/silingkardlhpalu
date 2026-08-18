@extends('layouts.admin')

@section('title', \App\Support\Admin\AdminRegistry::titleFor($record, $resource).' — Detail — Admin DLH')
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
        return filled($value) ? (string) $value : '-';
    };

    $statusValue = $record->status ?? null;
    $isEnumStatus = $statusValue instanceof BackedEnum;
    $statusText = $statusValue ? (is_string($statusValue) ? $statusValue : $format($statusValue)) : null;
    $statusVariant = match(true) {
        !$statusValue => 'neutral',
        in_array($statusText, ['Ditinjau', 'Ditindaklanjuti', 'Selesai', 'Disetujui'], true) => 'success',
        in_array($statusText, ['Belum Ditinjau', 'Belum Ditindaklanjuti', 'Pending', 'Menunggu'], true) => 'warning',
        in_array($statusText, ['Ditolak', 'Gagal', 'Batal'], true) => 'danger',
        default => 'info',
    };

    $regularFields = collect($fields)->reject(fn($f) => in_array($f['type'], ['file', 'textarea', 'section', 'photos']))->values()->all();
    $textareaFieldList = collect($fields)->filter(fn($f) => $f['type'] === 'textarea')->values()->all();
    $fileFieldList = collect($fields)->filter(fn($f) => $f['type'] === 'file')->values()->all();

    $iconFor = function ($fieldName) {
        return match(true) {
            str_contains($fieldName, 'nomor') => 'file-text',
            str_contains($fieldName, 'nama') || str_contains($fieldName, 'pelapor') || str_contains($fieldName, 'pemohon') => 'user',
            in_array($fieldName, ['email', 'username'], true) => 'mail',
            in_array($fieldName, ['status', 'kondisi', 'hasil'], true) => 'alert-circle',
            str_contains($fieldName, 'alamat') || str_contains($fieldName, 'lokasi') => 'map-pin',
            str_contains($fieldName, 'tanggal') || in_array($fieldName, ['created_at', 'updated_at'], true) => 'calendar',
            in_array($fieldName, ['latitude', 'longitude'], true) => 'map-pin',
            str_contains($fieldName, 'jenis') || str_contains($fieldName, 'kategori') || str_contains($fieldName, 'bidang') => 'filter',
            str_contains($fieldName, 'hp') || str_contains($fieldName, 'telepon') => 'message',
            default => 'file-text',
        };
    };

    $mapLat = $record->latitude ?? null;
    $mapLng = $record->longitude ?? null;
    $hasMap = $mapLat !== null && $mapLng !== null && $mapLat != 0 && $mapLng != 0;

    $fotos = ($record instanceof \App\Models\Laporan && $record->fotos && $record->fotos->isNotEmpty())
        ? $record->fotos->map(fn($f) => ['url' => $f->fullUrl(), 'caption' => ''])->all()
        : [];
@endphp

<div x-data x-init="window.staggerReveal($el.querySelectorAll('.stagger-item'), 80)" class="space-y-6">

    {{-- Page header --}}
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
            <x-admin.button variant="secondary" size="sm" icon="chevron-left" :href="route('admin.resources.index', $resource['slug'])">
                Kembali
            </x-admin.button>
            <x-admin.button variant="primary" size="sm" icon="edit" :href="route('admin.resources.edit', [$resource['slug'], $record])">
                Edit
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Informasi utama --}}
            @if($regularFields)
                <div class="stagger-item">
                    <x-admin.section-card title="Informasi Utama" icon="file-text" :subtitle="count($regularFields) . ' field'">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                            @foreach ($regularFields as $field)
                                @php $value = $record->{$field['name']} ?? null; @endphp
                                <x-admin.detail-field :label="$field['label']" :icon="$iconFor($field['name'])">
                                    @if ($field['name'] === 'status' && $statusText)
                                        <x-admin.status-pill :variant="$statusVariant" :label="$statusText" />
                                    @elseif ($field['type'] === 'checkbox')
                                        {{ $value ? 'Ya' : 'Tidak' }}
                                    @elseif (in_array($field['name'], ['latitude', 'longitude'], true) && filled($value))
                                        <span class="font-mono">{{ $value }}</span>
                                    @else
                                        {{ $format($value) }}
                                    @endif
                                </x-admin.detail-field>
                            @endforeach
                        </div>
                    </x-admin.section-card>
                </div>
            @endif

            {{-- Deskripsi & catatan --}}
            @if($textareaFieldList)
                <div class="stagger-item">
                    <x-admin.section-card title="Deskripsi & Catatan" icon="message">
                        <div class="space-y-5">
                            @foreach ($textareaFieldList as $field)
                                @php $value = $record->{$field['name']} ?? null; @endphp
                                <div>
                                    <p class="mb-1.5 text-caption font-semibold uppercase tracking-[0.08em] text-slate-500">{{ $field['label'] }}</p>
                                    <p class="whitespace-pre-line text-sm leading-relaxed text-ink-700">{{ filled($value) ? $value : '-' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </x-admin.section-card>
                </div>
            @endif

            {{-- Dokumen & lampiran (field file) --}}
            @if($fileFieldList)
                @php $fileFieldsWithData = collect($fileFieldList)->filter(fn ($field) => filled($record->{$field['name']} ?? null))->values(); @endphp
                @if($fileFieldsWithData->isNotEmpty())
                    <div class="stagger-item">
                        <x-admin.section-card title="Dokumen & Lampiran" icon="download" :subtitle="$fileFieldsWithData->count() . ' file'">
                            <div class="space-y-3">
                                @foreach($fileFieldsWithData as $field)
                                    @php
                                        $docPath = $record->{$field['name']} ?? null;
                                        $docExt = $docPath ? pathinfo($docPath, PATHINFO_EXTENSION) : '';
                                        $docName = $docExt ? $field['label'].'.'.$docExt : $field['label'];
                                    @endphp
                                    <x-admin.file-preview
                                        :label="$field['label']"
                                        :path="$docPath"
                                        :downloadName="$docName"
                                        :resource="$resource['slug']"
                                    />
                                @endforeach
                            </div>
                        </x-admin.section-card>
                    </div>
                @endif
            @endif

            {{-- Foto bukti --}}
            @if(!empty($fotos))
                <div class="stagger-item">
                    <x-admin.section-card title="Foto Bukti" icon="eye" :subtitle="count($fotos) . ' foto'">
                        <x-admin.lightbox :images="$fotos" :columns="3" />
                    </x-admin.section-card>
                </div>
            @endif

        </div>

        {{-- Side --}}
        <div class="space-y-6">
            {{-- Lokasi --}}
            @if($hasMap)
                <div class="stagger-item">
                    <x-admin.section-card title="Lokasi Kejadian" icon="map-pin">
                        <div id="admin-pengendalian-map" style="height:280px" class="w-full overflow-hidden rounded-lg border border-slate-200 bg-slate-100"></div>
                        <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                            <div class="rounded-lg bg-slate-50 px-3 py-2">
                                <p class="font-semibold uppercase tracking-wide text-slate-400">Latitude</p>
                                <p class="mt-0.5 font-mono font-semibold text-ink-800">{{ $mapLat }}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 px-3 py-2">
                                <p class="font-semibold uppercase tracking-wide text-slate-400">Longitude</p>
                                <p class="mt-0.5 font-mono font-semibold text-ink-800">{{ $mapLng }}</p>
                            </div>
                        </div>
                    </x-admin.section-card>
                </div>
                @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        window.ensureMaplibreLoaded(function () {
                            var map = new maplibregl.Map({
                                container: 'admin-pengendalian-map',
                                style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
                                center: [{{ $mapLng }}, {{ $mapLat }}],
                                zoom: 15,
                                attributionControl: false
                            });
                            map.addControl(new DlhZoomControl(), 'top-left');

                            if (window.DlhWeatherControl) map.addControl(new DlhWeatherControl(), 'top-right');
                            if (window.DlhBasemapSwitcher) map.addControl(new DlhBasemapSwitcher(), 'bottom-right');
                            if (window.dlhAddLocBtn) dlhAddLocBtn(map);
                            var el = document.createElement('div');
                            el.style.cssText = 'width:30px;height:30px;border-radius:50%;background:#10b981;color:#fff;box-shadow:0 4px 12px rgba(16,185,129,.5);border:2px solid #fff;display:grid;place-items:center;cursor:pointer';
                            el.innerHTML = '<span aria-hidden="true" style="display:block;width:10px;height:10px;border:2px solid currentColor;border-radius:9999px;box-sizing:border-box"></span>';
                            new maplibregl.Marker({ element: el, anchor: 'center' })
                                .setLngLat([{{ $mapLng }}, {{ $mapLat }}])
                                .setPopup(new maplibregl.Popup({ offset: [0, -20] }).setText('Lokasi kejadian'))
                                .addTo(map);
                            setTimeout(function () { map.resize(); }, 200);
                        });
                    });
                </script>
                @endpush
            @endif

            {{-- Meta --}}
            <div class="stagger-item">
                <x-admin.section-card title="Informasi Sistem" icon="clock">
                    <div class="space-y-4">
                        <x-admin.detail-field label="Dibuat" icon="calendar" :value="$record->created_at?->translatedFormat('d F Y, H:i')" />
                        <x-admin.detail-field label="Diperbarui" icon="clock" :value="$record->updated_at?->translatedFormat('d F Y, H:i')" />
                    </div>
                </x-admin.section-card>
            </div>

            {{-- Danger zone --}}
            <div class="stagger-item rounded-xl border border-danger-200 bg-danger-50/50 p-5">
                <div class="flex items-start gap-3">
                    <div class="grid size-10 shrink-0 place-items-center rounded-lg bg-danger-100 text-danger-600">
                        <x-admin.icon name="trash" :size="20" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-ink-900">Hapus Data</p>
                        <p class="mt-0.5 text-xs text-slate-500">Tindakan ini permanen dan tidak dapat dibatalkan.</p>
                        <div class="mt-3">
                            <x-admin.button variant="danger" size="sm" icon="trash" x-data="" x-on:click="$dispatch('open-modal', 'show-delete')">
                                Hapus Data
                            </x-admin.button>
                        </div>
                    </div>
                </div>
            </div>

            <x-admin.confirm-delete
                name="show-delete"
                :action="route('admin.resources.destroy', [$resource['slug'], $record])"
                title="Hapus Pengaduan"
                :message="'Data pengaduan ' . (\App\Support\Admin\AdminRegistry::titleFor($record, $resource)) . ' akan dihapus permanen. Aksi ini tidak bisa dibatalkan.'"
            />
        </div>
    </div>
</div>
@endsection
