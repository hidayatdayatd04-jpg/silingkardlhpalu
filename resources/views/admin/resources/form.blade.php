@extends('layouts.admin')

@section('title', ($record->exists ? 'Edit ' : 'Tambah ').$resource['label'].' - Admin DLH')
@section('heading', $resource['label'])

@section('content')
@php
    $visibleFields = collect($fields)
        ->reject(fn ($field) => ($field['hide_on_create'] ?? false) && ! $record->exists)
        ->values()
        ->all();

    $currentStatus = old('status', $record->status instanceof \BackedEnum ? $record->status->value : ($record->status ?? ''));

    $currentKegiatan = old('jenis_kegiatan', $record->jenis_kegiatan ?? 'sosialisasi');

    $daftarHadirRows = [];
    if ($resource['slug'] === 'sosialisasi' && $record->exists && $record->isMonitoringEvaluasi()) {
        $daftarHadirRows = $record->pesertas()
            ->orderBy('id')
            ->get()
            ->map(fn ($p) => [
                'nama_perusahaan' => $p->nama_perusahaan ?? '',
                'jenis_usaha' => $p->jenis_usaha ?? '',
                'tanggal' => $p->tanggal?->format('Y-m-d') ?? '',
                'lokasi' => $p->lokasi ?? '',
                'tim_survey' => $p->tim_survey ?? '',
            ])
            ->all();
    }
    if (empty($daftarHadirRows)) {
        $daftarHadirRows = [[
            'nama_perusahaan' => '',
            'jenis_usaha' => '',
            'tanggal' => '',
            'lokasi' => '',
            'tim_survey' => '',
        ]];
    }

    $fieldValue = function (array $field) use ($record, $resource) {
        $name = $field['name'];
        if ($name === 'role' && $resource['slug'] === 'user') {
            $value = old($name, $record->primaryRoleName() ?? null);
        } else {
            $value = old($name, $record->{$name} ?? null);
        }
        if ($name === 'jenis_kegiatan' && blank($value)) return 'sosialisasi';
        if ($value instanceof BackedEnum) return $value->value;
        if ($value instanceof DateTimeInterface) {
            return str_contains((string) ($field['type'] ?? ''), 'datetime') ? $value->format('Y-m-d\TH:i') : $value->format('Y-m-d');
        }
        return $value;
    };

    $hasLatLng = collect($visibleFields)->pluck('name')->intersect(['latitude', 'longitude'])->count() >= 2;

    $sections = [];
    $cur = null;
    foreach ($visibleFields as $f) {
        if (($f['type'] ?? null) === 'section') {
            $sections[] = ['label' => $f['label'], 'fields' => [], 'show_on_kegiatan' => $f['show_on_kegiatan'] ?? null];
            $cur = count($sections) - 1;
        } elseif ($cur !== null) {
            $sections[$cur]['fields'][] = $f;
        } else {
            $sections[] = ['label' => 'Informasi', 'fields' => [$f], 'show_on_kegiatan' => null];
            $cur = count($sections) - 1;
        }
    }
    if (empty($sections)) $sections[] = ['label' => 'Informasi', 'fields' => $visibleFields, 'show_on_kegiatan' => null];
@endphp

<div class="user-resource-form mx-auto max-w-4xl space-y-6 pb-24" @select-lainnya.window="
    if ($event.detail.name && $event.detail.value === '__lainnya__') {
        document.getElementById('lainnya_' + $event.detail.name).style.display = 'block';
    } else if ($event.detail.name) {
        var el = document.getElementById('lainnya_' + $event.detail.name);
        if (el) el.style.display = 'none';
    }
" x-init="window.staggerReveal($el.querySelectorAll('.stagger-item'), 70)">

    <x-admin.page-header
        :title="$record->exists ? 'Edit Data '.$resource['label'] : 'Tambah '.$resource['label']"
        subtitle="Kolom bertanda * wajib diisi"
        :icon="$record->exists ? 'edit' : 'plus'"
        :breadcrumbs="[
            ['label' => $resource['label'], 'url' => route('admin.resources.index', $resource['slug'])],
            ['label' => $record->exists ? 'Edit Data' : 'Tambah Data Baru'],
        ]"
    >
        <x-slot:actions>
            <x-admin.status-pill :variant="$record->exists ? 'info' : 'success'" :label="$record->exists ? 'Mode Edit' : 'Data Baru'" />
        </x-slot:actions>
    </x-admin.page-header>

    {{-- User Profile Preview --}}
    @if($resource['slug'] === 'user')
        <div class="stagger-item" x-data="{
            name: '{{ addslashes(old('name', $record->name ?? '')) }}',
            photo: '{{ $record->photoUrl() ?? '' }}',
            previewUrl: '',
            photoName: '',
            removePhoto: false
        }">
            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
                <div class="h-1.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500"></div>
                <div class="relative p-6">
                    <div class="pointer-events-none absolute -right-10 -top-10 size-32 rounded-full bg-emerald-50 opacity-50"></div>
                    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">
                        <div class="relative shrink-0">
                        <div class="inline-grid size-20 place-items-center rounded-full bg-white p-2 shadow-sm ring-4 ring-emerald-200">
                                <template x-if="!removePhoto && (previewUrl || photo)">
                                    <img :src="previewUrl || photo" alt="Foto profil"
                                        class="size-16 rounded-full object-cover object-center shadow-sm">
                                </template>
                                <template x-if="removePhoto || !(previewUrl || photo)">
                                    <div class="grid size-16 place-items-center rounded-full bg-gradient-to-br from-emerald-100 to-teal-100 text-lg font-bold text-emerald-700">
                                        {{ \Illuminate\Support\Str::of($record->name ?? 'Admin')->explode(' ')->map(fn($w) => mb_substr($w,0,1))->take(2)->implode('') }}
                                    </div>
                                </template>
                            </div>
                            <span class="absolute -bottom-0.5 -right-0.5 size-4 rounded-full border-[3px] border-white bg-success-500 shadow-sm"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Pratinjau Profil</p>
                            <p class="mt-1 truncate font-display text-xl font-bold text-slate-900" x-text="name || 'Nama Admin'"></p>
                            <p class="mt-0.5 text-sm text-slate-500">{{ $record->exists ? 'Memperbarui pengguna' : 'Pengguna baru akan ditambahkan' }}</p>

                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                <label class="cursor-pointer">
                                    <span class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10">
                                        <x-admin.icon name="upload" :size="16" /> Pilih Foto
                                    </span>
                                    <input type="file" name="photo" form="admin-resource-form" accept="image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif" class="hidden" x-ref="photoInput"
                                        x-on:change="previewUrl = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : ''; photoName = $event.target.files[0] ? $event.target.files[0].name : ''; removePhoto = false;">
                                </label>

                                <button type="button" form="admin-resource-form" x-show="photo || previewUrl" @click="removePhoto = true; previewUrl = ''; photoName = ''; if ($refs.photoInput) $refs.photoInput.value = ''"
                                    class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3.5 py-2 text-sm font-semibold text-rose-600 shadow-sm transition hover:bg-rose-100 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300 dark:hover:bg-rose-500/20">
                                    <x-admin.icon name="trash" :size="16" /> Hapus Foto
                                </button>
                                <input type="hidden" name="photo_remove" form="admin-resource-form" :value="removePhoto ? 1 : 0">
                            </div>

                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400" x-text="photoName || 'JPG, PNG, atau WEBP. Maksimal 5MB.'"></p>
                            @error('photo')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="admin-resource-form"
          x-data="{ submitting: false, selectedStatus: '{{ $currentStatus }}', selectedKegiatan: '{{ $currentKegiatan }}' }" x-on:submit="submitting = true"
          x-on:change="if ($event.target.name === 'status') selectedStatus = $event.target.value; if ($event.target.name === 'jenis_kegiatan') selectedKegiatan = $event.target.value" class="space-y-6">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        @foreach ($sections as $si => $section)
            @php
                $isCoordSection = $hasLatLng && collect($section['fields'])->pluck('name')->intersect(['latitude', 'longitude'])->count() >= 2;
                $isUserForm = $resource['slug'] === 'user';
                $sectionMeta = match ($section['label']) {
                    'Informasi Akun' => ['icon' => 'user', 'subtitle' => 'Data login & identitas pengguna'],
                    'Role & Akses' => ['icon' => 'shield', 'subtitle' => 'Tetapkan peran & hak akses menu'],
                    default => ['icon' => 'file-text', 'subtitle' => null],
                };
                $sectionKegiatanShow = $section['show_on_kegiatan'] ?? null;
                if (! $sectionKegiatanShow) {
                    $kegiatanValues = collect($section['fields'])->pluck('show_on_kegiatan')->filter()->unique()->values();
                    if ($kegiatanValues->count() === 1) {
                        $sectionKegiatanShow = $kegiatanValues->first();
                    }
                }
                $sectionShowAttr = $sectionKegiatanShow ? "x-show=\"selectedKegiatan === '{$sectionKegiatanShow}'\" x-cloak" : '';
            @endphp
            <div class="stagger-item relative" {!! $sectionShowAttr !!}>
                <x-admin.section-card
                    :title="$section['label']"
                    :number="$isUserForm ? null : $si + 1"
                    :icon="$isUserForm ? $sectionMeta['icon'] : null"
                    :subtitle="$isUserForm ? $sectionMeta['subtitle'] : null"
                >
                    @if($isCoordSection)
                        <div class="mb-6">
                            <x-admin.map-picker
                                lat-input="field-latitude"
                                lng-input="field-longitude"
                                :lat="old('latitude', $record->latitude ?? null)"
                                :lng="old('longitude', $record->longitude ?? null)"
                            />
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-x-5 gap-y-6 sm:grid-cols-2">
                        @foreach ($section['fields'] as $field)
                            @php
                                $name = $field['name'];
                                $type = $field['type'] ?? 'text';
                                $value = $fieldValue($field);
                                $isReadonly = $field['readonly'] ?? false;
                                if (! $isReadonly && ($field['readonly_on_edit'] ?? false) && $record->exists) {
                                    $isReadonly = true;
                                }
                                $isRequired = $field['required'] ?? false;
                                $isWide = $field['wide'] ?? false;
                                $step = $field['step'] ?? 'any';
                                $accept = $field['accept'] ?? null;
                                $error = $errors->first($name) ?: $errors->first($name.'.*');
                                $fullClass = ($isWide || in_array($type, ['textarea', 'relation_files', 'photos'], true)) ? 'sm:col-span-2' : '';
                                $inputType = $type === 'tel' ? 'tel' : ($type === 'date' ? 'date' : ($type === 'number' ? 'number' : ($type === 'email' ? 'email' : 'text')));
                                $isLatLng = in_array($name, ['latitude', 'longitude'], true);
                                $showOnStatus = $field['show_on_status'] ?? null;
                                $showOnKegiatan = $field['show_on_kegiatan'] ?? null;
                                $xShowAttr = '';
                                if ($showOnStatus) $xShowAttr .= "x-show=\"selectedStatus === '{$showOnStatus}'\" ";
                                if ($showOnKegiatan) $xShowAttr .= "x-show=\"selectedKegiatan === '{$showOnKegiatan}'\" ";
                                if ($xShowAttr !== '') $xShowAttr .= 'x-cloak';
                            @endphp

                            @if($type === 'select')
                                @php
                                    $isDisabled = ($name === 'role' && $resource['slug'] === 'user' && $record->exists && $record->isSuperadmin());
                                    $hasLainnya = $field['has_lainnya'] ?? false;
                                    $lainnyaName = $name . '_lainnya';
                                    $lainnyaValue = old($lainnyaName, $record->{$lainnyaName} ?? null);
                                    $selectOptions = $field['options'] ?? [];
                                    if ($hasLainnya) {
                                        $selectOptions['__lainnya__'] = 'Lainnya...';
                                    }
                                    $showLainnyaOnLoad = $hasLainnya && $record->exists && filled($lainnyaName) && blank($value);
                                @endphp
                                <div class="{{ $fullClass }}" {!! $xShowAttr !!}>
                                    <x-admin.select :label="$field['label']" name="{{ $name }}" :error="$error" :options="$selectOptions"
                                        :selected="$value" placeholder="Pilih {{ $field['label'] }}" :disabled="$isDisabled || $isReadonly"
                                        :required="$isRequired" :searchable="count($field['options'] ?? []) > 8" />
                                    @if($isDisabled)<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endif
                                    @if($hasLainnya)
                                        <div id="lainnya_{{ $name }}" class="mt-2" style="display: {{ $showLainnyaOnLoad ? 'block' : 'none' }};">
                                            <x-admin.form-input id="field-{{ $lainnyaName }}" type="text" name="{{ $lainnyaName }}"
                                                label="" :value="$lainnyaValue" :error="$errors->first($lainnyaName)"
                                                placeholder="Tulis {{ strtolower($field['label']) }} secara manual..." />
                                        </div>
                                    @endif
                                </div>
                @elseif($type === 'checkbox')
                            @if($resource['slug'] === 'user' && $name === 'is_active')
                                <div class="sm:col-span-2 flex flex-wrap items-center justify-between gap-4" {!! $xShowAttr !!}>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-700">{{ $field['label'] }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">Akun dapat login &amp; mengakses menu yang diizinkan</p>
                                    </div>
                                    <label class="relative inline-flex shrink-0 cursor-pointer items-center" title="Aktif / Nonaktif">
                                        <input type="checkbox" name="{{ $name }}" value="1" {{ (bool) $value ? 'checked' : '' }} class="peer sr-only" aria-label="{{ $field['label'] }}">
                                        <span class="h-7 w-12 rounded-full bg-slate-200 shadow-inner transition-colors duration-300 ease-in-out peer-checked:bg-emerald-500 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-300/70"></span>
                                        <span class="pointer-events-none absolute left-0.5 top-0.5 size-6 rounded-full bg-white shadow-md transition-transform duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)] peer-checked:translate-x-5"></span>
                                    </label>
                                </div>
                            @else
                        <div class="flex min-h-14 items-center rounded-xl border border-slate-200 bg-slate-50/50 px-4 transition hover:bg-slate-50" {!! $xShowAttr !!}>
                            <x-admin.checkbox name="{{ $name }}" value="1" :label="$field['label']" :checked="(bool) $value" />
                        </div>
                    @endif
                            @elseif($type === 'password')
                            <div class="{{ $fullClass }}" {!! $xShowAttr !!}>
                                <x-admin.form-input type="password" name="{{ $name }}" :label="$field['label']" icon="lock"
                                    :error="$error" :hint="$record->exists ? 'Kosongkan jika tidak ingin mengganti password' : null"
                                    :required="!$record->exists && $isRequired" toggleable />
                            </div>
                            @elseif($type === 'textarea')
                                <div class="sm:col-span-2" {!! $xShowAttr !!}>
                                    <x-admin.textarea
                                        name="{{ $name }}"
                                        label="{{ $field['label'] }}"
                                        :value="$value"
                                        :required="$isRequired"
                                        :readonly="$isReadonly"
                                        :error="$error"
                                        :rows="in_array($name, ['konten', 'materi'], true) ? 7 : 4"
                                    />
                                </div>
                            @elseif($type === 'jodit')
                                <div class="sm:col-span-2" {!! $xShowAttr !!}>
                                    <x-admin.jodit-editor
                                        name="{{ $name }}"
                                        label="{{ $field['label'] }}"
                                        :value="$value"
                                        :required="$isRequired"
                                        :error="$error"
                                    />
                                </div>
                            @elseif($type === 'file')
                                @php $currentFile = $record->{$name} ?? null; @endphp
                                <div class="{{ $fullClass }}" {!! $xShowAttr !!}>
                                    @if($isReadonly)
                                        @if($currentFile)
                                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">{{ $field['label'] }}</label>
                                            <x-admin.file-preview :path="$currentFile" />
                                        @else
                                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">{{ $field['label'] }}</label>
                                            <p class="text-xs text-slate-400">Belum ada file diunggah.</p>
                                        @endif
                                    @else
                                        <x-admin.file-upload name="{{ $name }}" :label="$field['label']" :error="$error" :currentFile="$currentFile"
                                            :hint="$record->exists ? 'Unggah file baru hanya jika ingin mengganti file lama.' : 'Max: 5MB'"
                                            :accept="$accept" :required="!$record->exists && $isRequired" />
                                    @endif
                                </div>
                            @elseif($type === 'relation_files')
                                <div class="sm:col-span-2" {!! $xShowAttr !!}>
                                    @if(!$isReadonly)
                                        <x-admin.file-upload name="{{ $name }}[]" :label="$field['label']" :error="$error"
                                            :hint="$record->exists ? 'Tambah lampiran baru jika diperlukan. Lampiran lama tetap tersimpan.' : 'Bisa unggah lebih dari satu file.'"
                                            :accept="$accept" multiple />
                                    @else
                                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">{{ $field['label'] }}</label>
                                        <p class="mb-3 text-xs text-slate-500">Lampiran dari masyarakat. Admin tidak dapat menambah atau mengubah lampiran.</p>
                                    @endif
                                    @if($record->exists && isset($field['relation']) && method_exists($record, $field['relation']))
                                        @php $items = $record->{$field['relation']} ?? collect(); @endphp
                                        @if($items->isNotEmpty())
                                            <div class="mt-3 space-y-2">
                                                @foreach($items as $item)
                                                    @php
                                                        $path = $item->{$field['path_field']} ?? null;
                                                        $label = $item->{$field['name_field'] ?? 'nama'} ?? basename((string) $path);
                                                        $itemExt = $path ? strtolower(pathinfo((string) $path, PATHINFO_EXTENSION)) : '';
                                                        $itemIsImage = in_array($itemExt, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'bmp'], true);
                                                        $itemSrc = null;
                                                        if ($path && $itemIsImage) {
                                                            try {
                                                                $itemSrc = Storage::disk('public')->temporaryUrl($path, now()->addHours(24));
                                                            } catch (\Throwable $e) {
                                                                $itemSrc = Storage::url($path);
                                                            }
                                                        }
                                                    @endphp
                                                    @if($path)
                                                        <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/50 px-3 py-2.5 transition hover:bg-slate-50">
                                                            @if($itemIsImage)
                                                                <img src="{{ $itemSrc }}" alt="{{ $label }}" loading="lazy"
                                                                    class="size-12 shrink-0 rounded-lg object-cover ring-1 ring-slate-200">
                                                            @else
                                                                <span class="grid size-12 shrink-0 place-items-center rounded-lg bg-blue-50 text-blue-600">
                                                                    <x-admin.icon name="file-text" :size="18" />
                                                                </span>
                                                            @endif
                                                            <p class="min-w-0 flex-1 truncate text-xs font-semibold text-slate-600" title="{{ $label }}">{{ $label ?: 'Lampiran' }}</p>
                                                            <a href="{{ route('admin.file.download', ['path' => $path, 'name' => $label ?: basename((string) $path)]) }}" target="_blank"
                                                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-white px-2.5 py-1.5 text-xs font-bold text-blue-600 ring-1 ring-slate-200 transition hover:bg-blue-50">
                                                                <x-admin.icon name="eye" :size="14" /> Lihat
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @elseif($isReadonly)
                                            <p class="mt-3 text-xs text-slate-400">Belum ada lampiran diunggah.</p>
                                        @endif
                                    @endif
                                </div>
                            @elseif($type === 'photos')
                                @php
                                    $allowAddNew = $field['add_new_on_edit'] ?? true;
                                    $canAddNew = $allowAddNew ? true : ! $record->exists;
                                    $existingFotos = ($record->exists && method_exists($record, 'fotos')) ? $record->fotos : collect();
                                @endphp
                                <div class="sm:col-span-2">
                                    @if($canAddNew)
                                        <x-admin.dropzone name="{{ $name }}" :label="$field['label']" :max="5" :max-size-mb="5"
                                            :accept="$accept ?: 'image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif'" :required="!$record->exists && $isRequired" />
                                    @elseif($existingFotos->isNotEmpty())
                                        <label class="mb-1.5 block text-sm font-semibold text-ink-800">{{ $field['label'] }}</label>
                                        <p class="mb-3 text-xs text-slate-500">Foto lampiran dari masyarakat. Admin tidak dapat menambah foto baru.</p>
                                        <div class="grid grid-cols-3 gap-3 sm:grid-cols-5">
                                            @foreach($existingFotos as $foto)
                                                <a href="{{ $foto->fullUrl() }}" target="_blank" class="group relative aspect-square overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                                                    <img src="{{ $foto->fullUrl() }}" alt="Foto {{ $loop->iteration }}" class="size-full object-cover transition group-hover:scale-105">
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-xs text-slate-500">Foto lampiran dari masyarakat. Admin tidak dapat menambah foto baru.</p>
                                    @endif
                                </div>
                            @elseif($type === 'daftar_hadir')
                                <div class="sm:col-span-2" x-data="{
                                    rows: {{ Js::from($daftarHadirRows) }},
                                    addRow() {
                                        this.rows.push({ nama_perusahaan: '', jenis_usaha: '', tanggal: '', lokasi: '', tim_survey: '' });
                                    },
                                    removeRow(index) {
                                        if (this.rows.length <= 1) {
                                            this.rows = [{ nama_perusahaan: '', jenis_usaha: '', tanggal: '', lokasi: '', tim_survey: '' }];
                                            return;
                                        }
                                        this.rows.splice(index, 1);
                                    }
                                }">
                                    <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-white/10">
                                        <div class="overflow-x-auto">
                                            <table class="w-full min-w-[760px] text-left text-sm">
                                                <thead>
                                                    <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-400">
                                                        <th class="w-12 px-3 py-3 text-center">No</th>
                                                        <th class="px-3 py-3">Nama Perusahaan</th>
                                                        <th class="px-3 py-3">Jenis Usaha</th>
                                                        <th class="px-3 py-3">Tanggal</th>
                                                        <th class="px-3 py-3">Lokasi</th>
                                                        <th class="px-3 py-3">Tim Survey</th>
                                                        <th class="w-14 px-3 py-3"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="(row, i) in rows" :key="i">
                                                        <tr class="border-b border-slate-100 align-top last:border-0 dark:border-white/5">
                                                            <td class="px-3 py-2 text-center font-bold text-slate-400" x-text="i + 1"></td>
                                                            <td class="px-3 py-2">
                                                                <input type="text" name="daftar_hadir[][nama_perusahaan]" x-model="row.nama_perusahaan" placeholder="Nama perusahaan"
                                                                    class="w-full rounded-lg border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400 dark:border-white/10 dark:bg-white/5">
                                                            </td>
                                                            <td class="px-3 py-2">
                                                                <input type="text" name="daftar_hadir[][jenis_usaha]" x-model="row.jenis_usaha" placeholder="Jenis usaha"
                                                                    class="w-full rounded-lg border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400 dark:border-white/10 dark:bg-white/5">
                                                            </td>
                                                            <td class="px-3 py-2">
                                                                <input type="date" name="daftar_hadir[][tanggal]" x-model="row.tanggal"
                                                                    class="w-full rounded-lg border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400 dark:border-white/10 dark:bg-white/5">
                                                            </td>
                                                            <td class="px-3 py-2">
                                                                <input type="text" name="daftar_hadir[][lokasi]" x-model="row.lokasi" placeholder="Lokasi kegiatan"
                                                                    class="w-full rounded-lg border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400 dark:border-white/10 dark:bg-white/5">
                                                            </td>
                                                            <td class="px-3 py-2">
                                                                <input type="text" name="daftar_hadir[][tim_survey]" x-model="row.tim_survey" placeholder="Nama petugas, pisah koma"
                                                                    class="w-full rounded-lg border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400 dark:border-white/10 dark:bg-white/5">
                                                            </td>
                                                            <td class="px-3 py-2 text-center">
                                                                <button type="button" @click="removeRow(i)" title="Hapus baris"
                                                                    class="inline-flex size-8 items-center justify-center rounded-lg text-rose-500 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10">
                                                                    <x-admin.icon name="trash" :size="15" />
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                    <tr x-show="rows.length === 0" x-cloak>
                                                        <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-400">Belum ada data daftar hadir. Klik "Tambah Baris" untuk mengisi.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 bg-slate-50/70 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Isi satu baris per kunjungan usaha. Tim survey: nama petugas dipisah koma.</p>
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="rows = []" x-show="rows.length > 0" x-cloak
                                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-500 transition hover:bg-slate-200 dark:text-slate-400 dark:hover:bg-white/10">
                                                    <x-admin.icon name="trash" :size="13" /> Kosongkan
                                                </button>
                                                <button type="button" @click="addRow()"
                                                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">
                                                    <x-admin.icon name="plus" :size="13" /> Tambah Baris
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="{{ $fullClass }}" {!! $xShowAttr !!}>
                                    <x-admin.form-input id="field-{{ $name }}" type="{{ $inputType }}" name="{{ $name }}"
                                        :label="$field['label']" :value="$value" :error="$error" :required="$isRequired"
                                        :readonly="$isReadonly" :step="$inputType === 'number' ? $step : null"
                                        :icon="$isLatLng ? 'map-pin' : null" />
                                </div>
                            @endif

                @if ($name === 'role' && $resource['slug'] === 'user')
                    @php
                        $roleDefaults = collect(\App\Enums\AdminRole::cases())
                            ->mapWithKeys(fn ($r) => [$r->value => $r->allowedGroups()])
                            ->all();
                    @endphp
                    <div class="sm:col-span-2">
                        <x-admin.group-access-selector :user="$record" :allGroups="\App\Support\Admin\AdminRegistry::all()" :roleDefaults="$roleDefaults" />
                    </div>
                @endif
                        @endforeach
                    </div>
                </x-admin.section-card>
            </div>
        @endforeach

            <button type="submit" :disabled="submitting"
                class="group fixed bottom-6 right-6 z-50 inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-emerald-600 to-teal-500 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/30 transition hover:brightness-110 hover:shadow-xl hover:shadow-emerald-600/40 focus:outline-none focus:ring-4 focus:ring-emerald-100 disabled:opacity-60"
                title="{{ $record->exists ? 'Perbarui Data' : 'Simpan Data' }}">
                <span x-show="submitting" x-cloak class="size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                <x-admin.icon x-show="!submitting" name="check" :size="18" />
                <span x-text="submitting ? 'Menyimpan...' : '{{ $record->exists ? 'Perbarui Data' : 'Simpan Data' }}'"></span>
            </button>
    </form>
</div>
@endsection
