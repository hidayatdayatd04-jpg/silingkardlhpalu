@extends('layouts.admin')

@section('title', 'Survei IKM - Admin DLH')
@section('heading', 'Survei IKM')

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
        1 => ['text' => 'Sangat Tidak Puas', 'color' => 'text-red-600', 'bg' => 'bg-red-50', 'bar' => 'bg-red-500', 'ring' => 'ring-red-200'],
        2 => ['text' => 'Kurang Puas', 'color' => 'text-orange-600', 'bg' => 'bg-orange-50', 'bar' => 'bg-orange-500', 'ring' => 'ring-orange-200'],
        3 => ['text' => 'Puas', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'bar' => 'bg-emerald-500', 'ring' => 'ring-emerald-200'],
        4 => ['text' => 'Sangat Puas', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50', 'bar' => 'bg-blue-500', 'ring' => 'ring-blue-200'],
    ];

    $avg = $record->nilai_rata_rata;
    $avgColor = match(true) {
        $avg >= 3.5 => ['text' => 'text-blue-600', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
        $avg >= 2.5 => ['text' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
        $avg >= 1.5 => ['text' => 'text-orange-600', 'bg' => 'bg-orange-50', 'border' => 'border-orange-200'],
        default => ['text' => 'text-red-600', 'bg' => 'bg-red-50', 'border' => 'border-red-200'],
    };
    $indikatorLabels = \App\Models\IkmResponse::$indikatorLabels;
?>

<div x-data x-init="window.staggerReveal($el.querySelectorAll('.stagger-item'), 80)" class="space-y-6">

    <x-admin.page-header
        title="Survei IKM - {{ $record->id }}"
        subtitle="{{ $record->created_at ? 'Dibuat ' . $record->created_at->translatedFormat('d F Y, H:i') : null }}"
        :breadcrumbs="[
            ['label' => 'Survei IKM', 'url' => route('admin.resources.index', $resource['slug'])],
            ['label' => 'Detail'],
        ]"
    >
        <x-slot:actions>
            <x-admin.button variant="secondary" size="sm" icon="chevron-left" :href="route('admin.resources.index', $resource['slug'])">Kembali</x-admin.button>
            <x-admin.button variant="primary" size="sm" icon="edit" :href="route('admin.resources.edit', [$resource['slug'], $record])">Edit</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="stagger-item">
        <div class="rounded-2xl border {{ $avgColor['border'] }} {{ $avgColor['bg'] }} p-6">
            <div class="flex flex-col sm:flex-row items-center gap-6">
                <div class="flex-shrink-0">
                    <div class="relative">
                        <div class="w-24 h-24 rounded-full border-4 {{ $avgColor['border'] }} bg-white flex items-center justify-center">
                            <span class="text-3xl font-bold {{ $avgColor['text'] }}">{{ number_format($avg, 1) }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <h3 class="text-lg font-bold text-ink-900">Nilai Rata-rata IKM</h3>
                    <p class="mt-1 text-sm text-slate-600">
                        @if($avg >= 3.5)
                            Kualitas pelayanan sangat baik. Pertahankan!
                        @elseif($avg >= 2.5)
                            Kualitas pelayanan cukup baik. Masih ada ruang untuk peningkatan.
                        @elseif($avg >= 1.5)
                            Kualitas pelayanan perlu ditingkatkan.
                        @else
                            Kualitas pelayanan memerlukan perhatian serius.
                        @endif
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($scaleLabels as $val => $info)
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $info['bg'] }} {{ $info['color'] }}">
                                {{ $val }} = {{ $info['text'] }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="stagger-item">
                <x-admin.section-card title="Detail Jawaban per Indikator" icon="check-circle">
                    <div class="space-y-4">
                        @foreach($questions as $key => $questionText)
                            @php $val = $record->{$key} ?? 0; $scale = $scaleLabels[$val] ?? $scaleLabels[1]; $percentage = ($val / 4) * 100; @endphp
                            <div class="rounded-xl border border-slate-100 bg-white p-4 transition hover:shadow-sm">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-sm font-bold {{ $scale['bg'] }} {{ $scale['color'] }} ring-1 {{ $scale['ring'] }}">
                                            {{ $val }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $indikatorLabels[$key] }}</p>
                                                <p class="mt-1 text-sm font-medium text-ink-800">{{ $questionText }}</p>
                                            </div>
                                            <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $scale['bg'] }} {{ $scale['color'] }}">
                                                {{ $scale['text'] }}
                                            </span>
                                        </div>
                                        <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full {{ $scale['bar'] }} transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-admin.section-card>
            </div>

            @if(filled($record->saran))
                <div class="stagger-item">
                    <x-admin.section-card title="Saran & Masukan" icon="message">
                        <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-5">
                            <div class="flex items-start gap-3">
                                <div class="grid size-9 shrink-0 place-items-center rounded-lg bg-amber-100 text-amber-600">
                                    <x-admin.icon name="message" :size="18" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="whitespace-pre-line text-sm leading-relaxed text-ink-700">{{ $record->saran }}</p>
                                </div>
                            </div>
                        </div>
                    </x-admin.section-card>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="stagger-item">
                <x-admin.section-card title="Informasi Sistem" icon="clock">
                    <div class="space-y-4">
                        <x-admin.detail-field label="ID Survei" icon="hash" :value="'#'.$record->id" />
                        <x-admin.detail-field label="Dibuat" icon="calendar" :value="$record->created_at?->translatedFormat('d F Y, H:i')" />
                        <x-admin.detail-field label="Diperbarui" icon="clock" :value="$record->updated_at?->translatedFormat('d F Y, H:i')" />
                        @if($record->fingerprint_hash)
                            <x-admin.detail-field label="Fingerprint" icon="key" :value="substr($record->fingerprint_hash, 0, 16).'...'" />
                        @endif
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
                title="Hapus Data Survei IKM"
                message="Data survei ini akan dihapus permanen. Aksi ini tidak bisa dibatalkan."
            />
        </div>
    </div>
</div>
@endsection
