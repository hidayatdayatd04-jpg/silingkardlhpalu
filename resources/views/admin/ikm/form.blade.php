@extends('layouts.admin')

@section('title', ($record->exists ? 'Edit ' : 'Tambah ').$resource['label'].' - Admin DLH')
@section('heading', $resource['label'])

@section('content')
<?php
    $questions = [
        'indikator_1' => 'Bagaimana kemudahan persyaratan pelayanan pengelolaan pohon pelindung?',
        'indikator_2' => 'Bagaimana kecepatan waktu petugas dalam menangani pengaduan/layanan?',
        'indikator_3' => 'Bagaimana transparansi biaya/tarif pelayanan (bebas pungli)?',
        'indikator_4' => 'Bagaimana kelayakan sarana, prasarana, dan alat keselamatan petugas?',
        'indikator_5' => 'Bagaimana keramahan, kesopanan, dan kompetensi petugas di lapangan?',
        'indikator_6' => 'Bagaimana penanganan pengaduan dan ketepatan respons kendala?',
        'indikator_7' => 'Bagaimana hasil pelayanan pemangkasan/penanganan pohon pelindung?',
    ];

    $scaleLabels = [
        1 => ['text' => 'Sangat Tidak Puas', 'color' => 'text-red-600', 'bg' => 'bg-red-50', 'ring' => 'ring-red-200 hover:ring-red-300'],
        2 => ['text' => 'Kurang Puas', 'color' => 'text-orange-600', 'bg' => 'bg-orange-50', 'ring' => 'ring-orange-200 hover:ring-orange-300'],
        3 => ['text' => 'Puas', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'ring' => 'ring-emerald-200 hover:ring-emerald-300'],
        4 => ['text' => 'Sangat Puas', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50', 'ring' => 'ring-blue-200 hover:ring-blue-300'],
    ];

    $indikatorLabels = \App\Models\IkmResponse::$indikatorLabels;

    $fieldValue = function (string $name) use ($record) {
        $value = old($name, $record->{$name} ?? null);
        return $value;
    };
?>

<div class="mx-auto max-w-4xl space-y-6" x-data x-init="window.staggerReveal($el.querySelectorAll('.stagger-item'), 70)">

    <x-admin.page-header
        :title="$record->exists ? 'Edit Survei IKM - '.$record->id : 'Tambah Survei IKM'"
        subtitle="Kolom bertanda * wajib diisi"
        :icon="$record->exists ? 'edit' : 'plus'"
        :breadcrumbs="[
            ['label' => 'Survei IKM', 'url' => route('admin.resources.index', $resource['slug'])],
            ['label' => $record->exists ? 'Edit Data' : 'Tambah Data Baru'],
        ]"
    >
        <x-slot:actions>
            <x-admin.status-pill :variant="$record->exists ? 'info' : 'success'" :label="$record->exists ? 'Mode Edit' : 'Data Baru'" />
        </x-slot:actions>
    </x-admin.page-header>

    @if($errors->any())
        <div class="rounded-xl border border-danger-200 bg-danger-50 p-4">
            <div class="flex items-start gap-3">
                <div class="grid size-8 shrink-0 place-items-center rounded-lg bg-danger-100 text-danger-600">
                    <x-admin.icon name="alert-circle" :size="16" />
                </div>
                <ul class="list-inside list-disc text-sm text-danger-700">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="admin-resource-form"
          x-data="{ submitting: false }" x-on:submit="submitting = true" class="space-y-6">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        {{-- Section: Penilaian Indikator --}}
        <div class="stagger-item">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-[var(--shadow-soft)] overflow-hidden">
                <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                    <div class="grid size-8 place-items-center rounded-lg bg-brand-100 text-brand-600">
                        <span class="text-sm font-bold">1</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-ink-900">Penilaian Indikator</h2>
                        <p class="text-xs text-slate-500">Pilih jawaban untuk setiap pertanyaan berikut</p>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($questions as $key => $questionText)
                        @php
                            $val = $fieldValue($key);
                            $error = $errors->first($key);
                            $num = (int) substr($key, -1);
                        @endphp
                        <div class="px-6 py-5">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 pt-0.5">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-xs font-bold text-slate-600">
                                        {{ $num }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <label class="block text-sm font-semibold text-ink-800">
                                        {{ $questionText }}<span class="text-danger-500"> *</span>
                                    </label>
                                    @if($error)
                                        <p class="mt-1 text-xs font-semibold text-danger-600">{{ $error }}</p>
                                    @endif

                                    <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-2">
                                        @foreach($scaleLabels as $scaleVal => $scaleInfo)
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="{{ $key }}" value="{{ $scaleVal }}"
                                                    {{ $val == $scaleVal ? 'checked' : '' }}
                                                    class="peer sr-only" required />
                                                <div class="rounded-xl border-2 border-slate-200 bg-white p-3 text-center transition-all duration-150
                                                    hover:border-slate-300 hover:bg-slate-50
                                                    peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:shadow-[0_0_0_4px_rgba(var(--color-brand-500-rgb,59,130,246),0.1)]">
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold
                                                        {{ $scaleInfo['bg'] }} {{ $scaleInfo['color'] }} ring-1 {{ $scaleInfo['ring'] }}">
                                                        {{ $scaleVal }}
                                                    </span>
                                                    <p class="mt-1.5 text-xs font-semibold text-ink-700">{{ $scaleInfo['text'] }}</p>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Section: Saran & Masukan --}}
        <div class="stagger-item">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-[var(--shadow-soft)] overflow-hidden">
                <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                    <div class="grid size-8 place-items-center rounded-lg bg-brand-100 text-brand-600">
                        <span class="text-sm font-bold">2</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-ink-900">Saran & Masukan</h2>
                        <p class="text-xs text-slate-500">Opsional — berikan saran untuk perbaikan layanan</p>
                    </div>
                </div>

                <div class="p-6">
                    <label for="saran" class="block text-sm font-semibold text-ink-800">Saran & Masukan</label>
                    <textarea name="saran" id="saran" rows="4"
                        placeholder="Tuliskan saran perbaikan layanan di sini..."
                        class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-ink-900 shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100">{{ $fieldValue('saran') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Submit --}}
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
