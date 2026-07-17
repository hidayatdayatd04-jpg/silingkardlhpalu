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
    $isRequired = $field['required'] ?? false;
    $fieldStep = $field['step'] ?? 'any';
    $fieldAccept = $field['accept'] ?? null;
    $isWide = $field['wide'] ?? false;
    $err = $errors->first($name);
    $showOnStatus = $field['show_on_status'] ?? null;
    $xShowAttr = $showOnStatus ? "x-show=\"selectedStatus === '{$showOnStatus}'\" x-cloak" : '';
@endphp

@if ($field['type'] === 'select')
    <div class="{{ $isWide ? 'sm:col-span-2' : '' }}" {!! $xShowAttr !!}>
        <x-admin.select
            :label="$field['label']"
            name="{{ $name }}"
            :error="$err"
            :options="$field['options']"
            :selected="$value"
            placeholder="Pilih {{ $field['label'] }}"
            :required="$isRequired"
        />
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
        <label for="peng-{{ $name }}" class="mb-1.5 block text-sm font-semibold text-ink-800">
            {{ $field['label'] }}@if($isRequired)<span class="text-danger-500"> *</span>@endif
        </label>
        <textarea
            id="peng-{{ $name }}"
            name="{{ $name }}"
            rows="{{ $name === 'deskripsi' ? 4 : 3 }}"
            @if($isRequired) required @endif
            @if($isReadonly) readonly @endif
            @if($err) aria-invalid="true" @endif
            class="block w-full rounded-lg border px-3.5 py-2.5 text-sm text-ink-900 shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition placeholder:text-slate-400 focus:ring-4 {{ $err ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-100' : 'border-slate-300 hover:border-slate-400 focus:border-brand-500 focus:ring-brand-100' }} {{ $isReadonly ? 'bg-slate-50' : 'bg-white' }}"
        >{{ $value }}</textarea>
        @if($err)
            <p class="mt-1.5 flex items-center gap-1 text-xs font-semibold text-danger-600">
                <x-admin.icon name="alert-circle" :size="14" /> {{ $err }}
            </p>
        @endif
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
    <div class="sm:col-span-2">
        @if($record->exists)
            {{-- Edit mode: hanya tampilkan foto yang sudah ada, tidak bisa tambah --}}
            @if($record instanceof \App\Models\Laporan && $record->fotos && $record->fotos->isNotEmpty())
                <label class="mb-1.5 block text-sm font-semibold text-ink-800">{{ $field['label'] }}</label>
                <p class="mb-3 text-xs text-slate-500">Foto lampiran dari masyarakat (tidak dapat diubah).</p>
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-5">
                    @foreach($record->fotos as $foto)
                        <a href="{{ Storage::url($foto->path_foto) }}" target="_blank" class="group relative aspect-square overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                            <img src="{{ Storage::url($foto->path_foto) }}" alt="Foto {{ $loop->iteration }}" class="size-full object-cover transition group-hover:scale-105">
                        </a>
                    @endforeach
                </div>
            @else
                <label class="mb-1.5 block text-sm font-semibold text-ink-800">{{ $field['label'] }}</label>
                <p class="text-xs text-slate-400 italic">Tidak ada foto lampiran.</p>
            @endif
        @else
            {{-- Create mode: tampilkan dropzone untuk upload --}}
            <x-admin.dropzone
                name="{{ $name }}"
                :label="$field['label']"
                :max="5"
                :max-size-mb="2"
                :accept="$fieldAccept ?: 'image/jpeg,image/png'"
                :required="$isRequired"
                hint="Maksimal 5 foto, JPG/PNG, 2MB per foto."
            />
        @endif
    </div>
@else
    @php
        $isLatLng = in_array($name, ['latitude', 'longitude'], true);
        // Koordinat hanya bisa diubah saat create, tidak saat edit
        if ($isLatLng && $record->exists) $isReadonly = true;
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
