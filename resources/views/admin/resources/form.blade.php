@extends('layouts.admin')

@section('title', ($record->exists ? 'Edit ' : 'Tambah ').$resource['label'].' - Admin DLH')
@section('heading', $resource['label'])

@section('content')
@php
    $visibleFields = collect($fields)
        ->reject(fn ($field) => ($field['hide_on_create'] ?? false) && ! $record->exists)
        ->values()
        ->all();

    $fieldValue = function (array $field) use ($record, $resource) {
        $name = $field['name'];
        if ($name === 'role' && $resource['slug'] === 'user') {
            $value = old($name, $record->primaryRoleName() ?? null);
        } else {
            $value = old($name, $record->{$name} ?? null);
        }
        if ($value instanceof BackedEnum) return $value->value;
        if ($value instanceof DateTimeInterface) {
            return str_contains((string) ($field['type'] ?? ''), 'datetime') ? $value->format('Y-m-d\TH:i') : $value->format('Y-m-d');
        }
        return $value;
    };

    $hasLatLng = collect($visibleFields)->pluck('name')->intersect(['latitude', 'longitude'])->count() >= 2;

    // Struktur section → fields
    $sections = [];
    $cur = null;
    foreach ($visibleFields as $f) {
        if (($f['type'] ?? null) === 'section') {
            $sections[] = ['label' => $f['label'], 'fields' => []];
            $cur = count($sections) - 1;
        } elseif ($cur !== null) {
            $sections[$cur]['fields'][] = $f;
        } else {
            $sections[] = ['label' => 'Informasi', 'fields' => [$f]];
            $cur = count($sections) - 1;
        }
    }
    if (empty($sections)) $sections[] = ['label' => 'Informasi', 'fields' => $visibleFields];
@endphp

<div class="mx-auto max-w-4xl space-y-6" @select-lainnya.window="
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

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="admin-resource-form"
          x-data="{ submitting: false }" x-on:submit="submitting = true" class="space-y-6">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        @foreach ($sections as $si => $section)
            @php $isCoordSection = $hasLatLng && collect($section['fields'])->pluck('name')->intersect(['latitude', 'longitude'])->count() >= 2; @endphp
            <div class="stagger-item relative z-0">
                <x-admin.section-card :number="$si + 1" :title="$section['label']">
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

                    <div class="grid grid-cols-1 gap-x-5 gap-y-5 sm:grid-cols-2">
                        @foreach ($section['fields'] as $field)
                            @php
                                $name = $field['name'];
                                $type = $field['type'] ?? 'text';
                                $value = $fieldValue($field);
                                $isReadonly = $field['readonly'] ?? false;
                                $isRequired = $field['required'] ?? false;
                                $isWide = $field['wide'] ?? false;
                                $step = $field['step'] ?? 'any';
                                $accept = $field['accept'] ?? null;
                                $error = $errors->first($name) ?: $errors->first($name.'.*');
                                $fullClass = ($isWide || in_array($type, ['textarea', 'relation_files', 'photos'], true)) ? 'sm:col-span-2' : '';
                                $inputType = $type === 'tel' ? 'tel' : ($type === 'date' ? 'date' : ($type === 'number' ? 'number' : ($type === 'email' ? 'email' : 'text')));
                                $isLatLng = in_array($name, ['latitude', 'longitude'], true);
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
                                    // Check if we should show lainnya on load (editing record with custom value)
                                    $showLainnyaOnLoad = $hasLainnya && $record->exists && filled($lainnyaName) && blank($value);
                                @endphp
                                <div class="{{ $fullClass }}">
                                    <x-admin.select :label="$field['label']" name="{{ $name }}" :error="$error" :options="$selectOptions"
                                        :selected="$value" placeholder="Pilih {{ $field['label'] }}" :disabled="$isDisabled"
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
                                <div class="flex min-h-14 items-center rounded-lg border border-slate-200 bg-slate-50 px-4">
                                    <x-admin.checkbox name="{{ $name }}" value="1" :label="$field['label']" :checked="(bool) $value" />
                                </div>
                            @elseif($type === 'password')
                                <div class="{{ $fullClass }}">
                                    <x-admin.form-input type="password" name="{{ $name }}" :label="$field['label']" icon="lock"
                                        :error="$error" :hint="$record->exists ? 'Kosongkan jika tidak ingin mengganti password' : null"
                                        :required="!$record->exists && $isRequired" />
                                </div>
                            @elseif($type === 'textarea')
                                <div class="sm:col-span-2">
                                    <label for="field-{{ $name }}" class="mb-1.5 block text-sm font-semibold text-ink-800">
                                        {{ $field['label'] }}@if($isRequired)<span class="text-danger-500"> *</span>@endif
                                    </label>
                                    <textarea id="field-{{ $name }}" name="{{ $name }}" rows="{{ in_array($name, ['konten', 'materi'], true) ? 7 : 4 }}"
                                        @if($isRequired) required @endif @if($isReadonly) readonly @endif
                                        class="block w-full rounded-lg border px-3.5 py-2.5 text-sm text-ink-900 shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition placeholder:text-slate-400 focus:ring-4 {{ $error ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-100' : 'border-slate-300 hover:border-slate-400 focus:border-brand-500 focus:ring-brand-100' }} {{ $isReadonly ? 'bg-slate-50' : 'bg-white' }}">{{ $value }}</textarea>
                                    @if($error)<p class="mt-1.5 flex items-center gap-1 text-xs font-semibold text-danger-600"><x-admin.icon name="alert-circle" :size="14" /> {{ $error }}</p>@endif
                                </div>
                            @elseif($type === 'jodit')
                                <div class="sm:col-span-2">
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
                                <div class="{{ $fullClass }}">
                                    <x-admin.file-upload name="{{ $name }}" :label="$field['label']" :error="$error" :currentFile="$currentFile"
                                        :hint="$record->exists ? 'Unggah file baru hanya jika ingin mengganti file lama.' : 'Max: 2MB'"
                                        :accept="$accept" :required="!$record->exists && $isRequired" />
                                </div>
                            @elseif($type === 'relation_files')
                                <div class="sm:col-span-2">
                                    <x-admin.file-upload name="{{ $name }}[]" :label="$field['label']" :error="$error"
                                        :hint="$record->exists ? 'Tambah lampiran baru jika diperlukan. Lampiran lama tetap tersimpan.' : 'Bisa unggah lebih dari satu file.'"
                                        :accept="$accept" multiple />
                                    @if($record->exists && isset($field['relation']) && method_exists($record, $field['relation']))
                                        @php $items = $record->{$field['relation']} ?? collect(); @endphp
                                        @if($items->isNotEmpty())
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @foreach($items as $item)
                                                    @php $path = $item->{$field['path_field']} ?? null; $label = $item->{$field['name_field'] ?? 'nama'} ?? basename((string) $path); @endphp
                                                    @if($path)
                                                        <a href="{{ Storage::url($path) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg bg-info-50 px-3 py-1.5 text-xs font-bold text-info-700 transition hover:bg-info-100">
                                                            <x-admin.icon name="file-text" :size="14" /> {{ $label ?: 'Lihat lampiran' }}
                                                        </a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @elseif($type === 'photos')
                                <div class="sm:col-span-2">
                                    <x-admin.dropzone name="{{ $name }}" :label="$field['label']" :max="5" :max-size-mb="2"
                                        :accept="$accept ?: 'image/jpeg,image/png'" :required="!$record->exists && $isRequired" />
                                </div>
                            @else
                                <div class="{{ $fullClass }}">
                                    <x-admin.form-input id="field-{{ $name }}" type="{{ $inputType }}" name="{{ $name }}"
                                        :label="$field['label']" :value="$value" :error="$error" :required="$isRequired"
                                        :readonly="$isReadonly" :step="$inputType === 'number' ? $step : null"
                                        :icon="$isLatLng ? 'map-pin' : null" />
                                </div>
                            @endif

                            @if ($name === 'role' && $resource['slug'] === 'user')
                                <div class="sm:col-span-2">
                                    <x-admin.group-access-selector :user="$record" :allGroups="\App\Support\Admin\AdminRegistry::availableGroups()" />
                                </div>
                            @endif
                        @endforeach
                    </div>
                </x-admin.section-card>
            </div>
        @endforeach

        <div class="stagger-item flex items-center justify-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-[var(--shadow-soft)]">
            <x-admin.button variant="secondary" icon="chevron-left" :href="route('admin.resources.index', $resource['slug'])">Kembali</x-admin.button>
            <button type="submit" :disabled="submitting"
                class="relative inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-[var(--shadow-brand-glow)] transition duration-150 hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-100 disabled:opacity-60">
                <svg x-show="submitting" x-cloak class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <x-admin.icon x-show="!submitting" name="check" :size="18" />
                <span x-text="submitting ? 'Menyimpan...' : '{{ $record->exists ? 'Perbarui Data' : 'Simpan Data' }}'"></span>
            </button>
        </div>
    </form>
</div>
@endsection
