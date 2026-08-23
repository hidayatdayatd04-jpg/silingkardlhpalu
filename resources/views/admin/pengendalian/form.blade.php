@extends('layouts.admin')

@section('title', ($record->exists ? 'Edit ' : 'Tambah ').$resource['label'].' — Admin DLH')
@section('heading', $resource['label'])

@section('content')
@php
    $visibleFields = collect($fields)
        ->reject(fn ($field) => ($field['hide_on_create'] ?? false) && ! $record->exists)
        ->values()
        ->all();

    $hasLatLng = collect($visibleFields)->pluck('name')->intersect(['latitude', 'longitude'])->count() >= 2;

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

@php
    $currentStatus = old('status', $record->status instanceof \BackedEnum ? $record->status->value : ($record->status ?? 'Belum Ditindaklanjuti'));
@endphp

<div class="mx-auto max-w-4xl space-y-6" @select-lainnya.window="
    if ($event.detail.name && $event.detail.value === '__lainnya__') {
        document.getElementById('lainnya_' + $event.detail.name).style.display = 'block';
    } else if ($event.detail.name) {
        var el = document.getElementById('lainnya_' + $event.detail.name);
        if (el) el.style.display = 'none';
    }
">

    {{-- Page header --}}
    <x-admin.page-header
        :title="$record->exists ? 'Edit Data Pengaduan' : 'Tambah Pengaduan Baru'"
        subtitle="Kolom bertanda * wajib diisi"
        :icon="$record->exists ? 'edit' : 'plus'"
        :breadcrumbs="[
            ['label' => $resource['label'], 'url' => route('admin.resources.index', $resource['slug'])],
            ['label' => $record->exists ? 'Edit Data' : 'Tambah Data Baru'],
        ]"
    >
    </x-admin.page-header>

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="pengendalian-form"
          x-data="{ submitting: false, selectedStatus: '{{ $currentStatus }}' }"
          x-init="window.staggerReveal($el.querySelectorAll('.stagger-item'), 70)"
          x-on:change="if ($event.target.name === 'status') selectedStatus = $event.target.value"
          class="space-y-6">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        @foreach ($sections as $i => $section)
            @php
                $isCoordSection = $hasLatLng && collect($section['fields'])->pluck('name')->intersect(['latitude', 'longitude'])->count() >= 2;
                // Peta readonly jika salah satu field lat/lng punya readonly_on_edit dan record sudah ada.
                $isCoordReadonly = $isCoordSection && $record->exists && collect($section['fields'])
                    ->filter(fn ($f) => in_array($f['name'] ?? '', ['latitude', 'longitude']))
                    ->contains(fn ($f) => ($f['readonly_on_edit'] ?? false) || ($f['readonly'] ?? false));
            @endphp
            <div class="stagger-item">
                <x-admin.section-card :number="$i + 1" :title="$section['label']">
                    @if($isCoordSection)
                        <div class="mb-6">
                            <x-admin.map-picker
                                lat-input="peng-latitude"
                                lng-input="peng-longitude"
                                :lat="old('latitude', $record->latitude ?? null)"
                                :lng="old('longitude', $record->longitude ?? null)"
                                :readonly="$isCoordReadonly"
                            />
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-x-5 gap-y-5 sm:grid-cols-2">
                        @foreach ($section['fields'] as $field)
                            @include('admin.pengendalian._field', ['field' => $field, 'record' => $record, 'resource' => $resource])
                            @if ($field['name'] === 'role' && $resource['slug'] === 'user')
                                <div class="sm:col-span-2">
                                    <x-admin.group-access-selector :user="$record" :allGroups="\App\Support\Admin\AdminRegistry::all()" />
                                </div>
                            @endif
                        @endforeach
                    </div>
                </x-admin.section-card>
            </div>
        @endforeach

        {{-- Actions --}}
        <div class="stagger-item flex items-center justify-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-[var(--shadow-soft)]">
            <x-admin.button variant="secondary" icon="chevron-left" :href="route('admin.resources.index', $resource['slug'])">
                Kembali
            </x-admin.button>
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
