{{-- Single form field renderer (redesign).
     Vars: $field, $record, $resource. Menghormati semua type asli:
     select | checkbox | password | textarea | file | photos | number/text/date/dst.
     - map-picker disisipkan sebelum field 'latitude' (sekali).
     - photos → dropzone (client preview) + preview foto lama saat edit.
     - input teks/number/date → form-input (floating label).
     - show_on_status → field hanya muncul saat status tertentu. --}}
@php
    $name = $field['name'];
    if ($name === 'role' && $resource['slug'] === 'user') {
        $value = old($name, $record->primaryRoleName() ?? null);
    } else {
        $value = old($name, $record->{$name} ?? null);
    }
    if ($value instanceof BackedEnum) $value = $value->value;
    if ($value instanceof DateTimeInterface) $value = $value->format('Y-m-d');
    $isReadonly = $field['readonly'] ?? false;
    if (! $isReadonly && ($field['readonly_on_edit'] ?? false) && $record->exists) {
        $isReadonly = true;
    }
    $isRequired = $field['required'] ?? false;
    $fieldStep = $field['step'] ?? 'any';
    $fieldAccept = $field['accept'] ?? null;
    $isWide = $field['wide'] ?? false;
    $err = $errors->first($name);
    $showOnStatus = $field['show_on_status'] ?? null;
    $xShowAttr = $showOnStatus ? "x-show=\"selectedStatus === '{$showOnStatus}'\" x-cloak" : '';
@endphp

@if ($field['type'] === 'select')
    @php
        $hasLainnya = $field['has_lainnya'] ?? false;
        $lainnyaName = $name . '_lainnya';
        $lainnyaValue = old($lainnyaName, $record->{$lainnyaName} ?? null);
        $selectOptions = $field['options'] ?? [];
        if ($hasLainnya) {
            $selectOptions['__lainnya__'] = 'Lainnya...';
        }
        $showLainnyaOnLoad = $hasLainnya && $record->exists && filled($lainnyaName) && blank($value);
    @endphp
    <div class="{{ $isWide ? 'sm:col-span-2' : '' }}" {!! $xShowAttr !!}>
        <x-admin.select
            :label="$field['label']"
            name="{{ $name }}"
            :error="$err"
            :options="$selectOptions"
            :selected="$value"
            placeholder="Pilih {{ $field['label'] }}"
            :required="$isRequired"
            :disabled="$isReadonly"
        />
        @if($hasLainnya)
            <div id="lainnya_{{ $name }}" class="mt-2" style="display: {{ $showLainnyaOnLoad ? 'block' : 'none' }};">
                <x-admin.form-input id="field-{{ $lainnyaName }}" type="text" name="{{ $lainnyaName }}"
                    label="" :value="$lainnyaValue" :error="$errors->first($lainnyaName)"
                    placeholder="Tulis {{ strtolower($field['label']) }} secara manual..." />
            </div>
        @endif
    </div>
@elseif ($field['type'] === 'checkbox')
    <div {!! $xShowAttr !!}>
        <div class="flex min-h-14 items-center rounded-lg border border-slate-200 bg-slate-50 px-4">
            <x-admin.checkbox name="{{ $name }}" value="1" :label="$field['label']" :checked="(bool) $value" />
        </div>
    </div>
@elseif ($field['type'] === 'password')
    <div {!! $xShowAttr !!}>
        <x-admin.form-input
            type="password"
            name="{{ $name }}"
            :label="$field['label']"
            icon="lock"
            :error="$err"
            :hint="$record->exists ? 'Kosongkan jika tidak ingin mengganti password' : null"
            :required="!$record->exists && $isRequired"
        />
    </div>
@elseif ($field['type'] === 'textarea')
    <div class="sm:col-span-2" {!! $xShowAttr !!}>
        <x-admin.textarea
            id="peng-{{ $name }}"
            name="{{ $name }}"
            :label="$field['label']"
            :value="$value"
            :error="$err"
            :required="$isRequired"
            :readonly="$isReadonly"
            :rows="$name === 'deskripsi' ? 4 : 3"
            :placeholder="$field['placeholder'] ?? ''"
        />
    </div>
@elseif ($field['type'] === 'file')
    @php $currentFile = $record->{$name} ?? null; @endphp
    <div class="{{ $isWide ? 'sm:col-span-2' : '' }}" {!! $xShowAttr !!}>
        <x-admin.file-upload
            name="{{ $name }}"
            :label="$field['label']"
            :error="$err"
            :currentFile="$currentFile"
            :hint="$fieldAccept ? 'Format: '.strtoupper(str_replace(['.', ','], ['', ', '], $fieldAccept)).' (Max: 2MB)' : 'Max: 2MB'"
            :accept="$fieldAccept"
            :required="!$record->exists && $isRequired"
        />
    </div>
@elseif ($field['type'] === 'relation_files')
    <div class="sm:col-span-2" {!! $xShowAttr !!}>
        <x-admin.file-upload name="{{ $name }}[]" :label="$field['label']" :error="$err"
            :hint="$record->exists ? 'Tambah lampiran baru jika diperlukan. Lampiran lama tetap tersimpan.' : 'Bisa unggah lebih dari satu file.'"
            :accept="$fieldAccept" multiple />
    </div>
@elseif ($field['type'] === 'photos')
    @php
        $allowAddNew = $field['add_new_on_edit'] ?? true;
        $canAddNew = $allowAddNew ? true : ! $record->exists;
    @endphp
    <div class="sm:col-span-2">
        @if($record->exists && $record instanceof \App\Models\Laporan && $record->fotos && $record->fotos->isNotEmpty())
            <label class="mb-1.5 block text-sm font-semibold text-ink-800">{{ $field['label'] }}</label>
            @if($canAddNew)
                <p class="mb-3 text-xs text-slate-500">Foto lampiran dari masyarakat (untuk menambah foto baru, gunakan unggahan di bawah).</p>
            @else
                <p class="mb-3 text-xs text-slate-500">Foto lampiran dari masyarakat. Admin tidak dapat menambah foto baru.</p>
            @endif
            <div class="grid grid-cols-3 gap-3 sm:grid-cols-5">
                @foreach($record->fotos as $foto)
                    <a href="{{ Storage::url($foto->path_foto) }}" target="_blank" class="group relative aspect-square overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                        <img src="{{ Storage::url($foto->path_foto) }}" alt="Foto {{ $loop->iteration }}" class="size-full object-cover transition group-hover:scale-105">
                    </a>
                @endforeach
            </div>
            @if($canAddNew)
                <div class="mt-4">
                    <x-admin.dropzone
                        name="{{ $name }}"
                        :label="'Tambah Foto Baru'"
                        :max="5"
                        :max-size-mb="2"
                        :accept="$fieldAccept ?: 'image/jpeg,image/png'"
                        hint="Maksimal 5 foto, JPG/PNG, 2MB per foto."
                    />
                </div>
            @endif
        @elseif($canAddNew)
            <x-admin.dropzone
                name="{{ $name }}"
                :label="$field['label']"
                :max="5"
                :max-size-mb="2"
                :accept="$fieldAccept ?: 'image/jpeg,image/png'"
                :required="$isRequired"
                hint="Maksimal 5 foto, JPG/PNG, 2MB per foto."
            />
        @else
            <p class="text-xs text-slate-500">Belum ada foto lampiran dari masyarakat.</p>
        @endif
    </div>
@else
    @php
        $isLatLng = in_array($name, ['latitude', 'longitude'], true);
    @endphp
    <div class="{{ $isWide ? 'sm:col-span-2' : '' }}" {!! $xShowAttr !!}>
        <x-admin.form-input
            id="peng-{{ $name }}"
            type="{{ $field['type'] }}"
            name="{{ $name }}"
            :label="$field['label']"
            :value="$value"
            :error="$err"
            :required="$isRequired"
            :readonly="$isReadonly"
            :step="$field['type'] === 'number' ? $fieldStep : null"
            :icon="$isLatLng ? 'map-pin' : null"
        />
    </div>
@endif
