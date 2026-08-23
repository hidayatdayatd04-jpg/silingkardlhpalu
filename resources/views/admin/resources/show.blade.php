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
            if (empty($value)) return '-';
            $formatItem = function ($v) use (&$format, &$formatItem) {
                if (is_array($v)) {
                    return collect($v)->map(fn ($vv, $kk) => str_replace('_', ' ', ucfirst((string) $kk)).': '.$format($vv))->implode('; ');
                }
                return $format($v);
            };
            if (array_is_list($value)) {
                // Daftar nilai ditampilkan sebagai teks biasa, bukan JSON mentah.
                return implode(', ', array_map($formatItem, $value));
            }
            // Pasangan label => nilai dirapikan menjadi "Label: nilai".
            return collect($value)
                ->map(fn ($v, $k) => str_replace('_', ' ', ucfirst((string) $k)).': '.$formatItem($v))
                ->implode('; ');
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
        ->reject(fn ($field) => in_array($field['type'] ?? null, ['section', 'relation_files', 'photos', 'daftar_hadir'], true))
        ->reject(fn ($field) => isset($field['show_on_status']) && $field['show_on_status'] !== $statusText)
        ->reject(function ($field) use ($record) {
            if (! isset($field['show_on_kegiatan'])) return false;
            return $field['show_on_kegiatan'] !== ($record->jenis_kegiatan ?? 'sosialisasi');
        })
        ->reject(fn ($field) => in_array($field['name'], ['password', 'role'], true)) // Hide password & role from generic fields
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
        ['relation' => 'dokumens', 'title' => 'Dokumen Terkait', 'path' => null, 'name' => null, 'image' => false],
        ['relation' => 'media', 'title' => 'Media', 'path' => 'path', 'name' => null, 'image' => true],
        ['relation' => 'files', 'title' => 'Berkas Terkait', 'path' => 'path', 'name' => 'nama', 'image' => false],
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

<div x-data x-init="window.staggerReveal($el.querySelectorAll('.stagger-item'), 80)" class="space-y-6 pb-24 lg:pb-8">

    {{-- ============================================================ --}}
    {{-- USER PROFILE HEADER (Special design for user resource) --}}
    {{-- ============================================================ --}}
    @if($resource['slug'] === 'user')
        @php
            $roleName = $record->primaryRoleName();
            $roleEnum = \App\Enums\AdminRole::tryFrom($roleName);
            $allowedGroups = $record->allowedGroups();
            $allGroups = \App\Support\Admin\AdminRegistry::all();
            $allMenus = \App\Support\Admin\AdminRegistry::flat();
            $extraSlugs = array_values(array_unique($record->additional_access ?? []));
        @endphp

        <div class="stagger-item">
            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                {{-- Gradient accent bar --}}
                <div class="h-2 bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500"></div>

                <div class="relative p-6 sm:p-8">
                    {{-- Decorative blurs --}}
                    <div class="pointer-events-none absolute -right-12 -top-12 size-40 rounded-full bg-emerald-50 opacity-50"></div>
                    <div class="pointer-events-none absolute -bottom-16 right-16 size-32 rounded-full bg-teal-50 opacity-50"></div>

                    <div class="relative flex flex-col gap-6 sm:flex-row sm:items-start">
                        {{-- Avatar --}}
                        <div class="relative shrink-0">
                        <div class="inline-grid size-20 place-items-center rounded-full bg-white p-2 shadow-sm ring-4 ring-emerald-200">
                                <x-admin.avatar :name="$record->name ?? 'Admin'" :src="$record->photoUrl()" size="lg" />
                            </div>
                            @if($record->is_active)
                                <span class="absolute -bottom-0.5 -right-0.5 size-5 rounded-full border-[3px] border-white bg-success-500 shadow-sm"></span>
                            @else
                                <span class="absolute -bottom-0.5 -right-0.5 size-5 rounded-full border-[3px] border-white bg-slate-400 shadow-sm"></span>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-3">
                                <h1 class="font-display text-2xl font-bold text-slate-900">{{ $record->name ?? 'Admin' }}</h1>
                                @if($roleEnum)
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold
                                        {{ match($roleEnum) {
                                            \App\Enums\AdminRole::ADMIN => 'bg-indigo-50 text-indigo-700 border border-indigo-100',
                                            \App\Enums\AdminRole::BIDANG_PENGENDALIAN => 'bg-blue-50 text-blue-700 border border-blue-100',
                                            \App\Enums\AdminRole::BIDANG_SAMPAH_LB3 => 'bg-amber-50 text-amber-700 border border-amber-100',
                                            \App\Enums\AdminRole::BIDANG_TATA_PENATAAN => 'bg-slate-100 text-slate-700 border border-slate-200',
                                            \App\Enums\AdminRole::BIDANG_RTH => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                                            default => 'bg-slate-100 text-slate-600 border border-slate-200',
                                        } }}">
                                        <x-admin.icon :name="$roleEnum->icon()" :size="14" />
                                        {{ $roleEnum->label() }}
                                    </span>
                                @endif
                                @if($record->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-100">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500 border border-slate-200">
                                        <span class="size-1.5 rounded-full bg-slate-400"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-500">
                                <span class="inline-flex items-center gap-2">
                                    <span class="grid size-7 place-items-center rounded-lg bg-slate-100 text-slate-500">
                                        <x-admin.icon name="user" :size="14" />
                                    </span>
                                    {{ $record->username ?? '-' }}
                                </span>
                                <span class="inline-flex items-center gap-2">
                                    <span class="grid size-7 place-items-center rounded-lg bg-slate-100 text-slate-500">
                                        <x-admin.icon name="mail" :size="14" />
                                    </span>
                                    {{ $record->email ?? '-' }}
                                </span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            <x-admin.button variant="secondary" size="sm" icon="chevron-left" :href="route('admin.resources.index', $resource['slug'])">Kembali</x-admin.button>
                            <x-admin.button variant="primary" size="sm" icon="edit" :href="route('admin.resources.edit', [$resource['slug'], $record])">Edit</x-admin.button>
                            @if(auth()->user()->isSuperadmin())
                                <x-admin.button variant="warning" size="sm" icon="key" x-data="" x-on:click="$dispatch('open-modal', 'reset-password-user')">Reset Password</x-admin.button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Grid (2x2 aligned) --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Account Info --}}
            <div class="stagger-item">
                <x-admin.section-card title="Informasi Akun" icon="user">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                        {{-- Name --}}
                        <div class="flex items-start gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-500">
                                <x-admin.icon name="user" :size="18" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama Lengkap</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-900">{{ $record->name ?? '-' }}</p>
                            </div>
                        </div>

                        {{-- Username --}}
                        <div class="flex items-start gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-500">
                                <x-admin.icon name="at-sign" :size="18" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Username</p>
                                <p class="mt-0.5 font-mono text-sm font-semibold text-slate-900">{{ $record->username ?? '-' }}</p>
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="flex items-start gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-500">
                                <x-admin.icon name="mail" :size="18" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Email</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-900">{{ $record->email ?? '-' }}</p>
                            </div>
                        </div>

                        {{-- Password — hanya tersimpan sebagai hash, tidak pernah ditampilkan --}}
                        <div class="flex items-start gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-500">
                                <x-admin.icon name="lock" :size="18" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Password</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-900">&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</p>
                                <p class="mt-0.5 text-xs text-slate-400">Password tidak dapat ditampilkan. Gunakan Reset Password jika perlu menggantinya.</p>
                            </div>
                        </div>
                    </div>
                </x-admin.section-card>
            </div>

            {{-- System Info --}}
            <div class="stagger-item">
                <x-admin.section-card title="Informasi Sistem" icon="clock">
                    <div class="space-y-4">
                        <x-admin.detail-field label="Dibuat" icon="calendar" :value="$record->created_at?->translatedFormat('d F Y, H:i')" />
                        <x-admin.detail-field label="Diperbarui" icon="clock" :value="$record->updated_at?->translatedFormat('d F Y, H:i')" />
                        @if($record->email_verified_at)
                            <x-admin.detail-field label="Email Terverifikasi" icon="check-circle" :value="$record->email_verified_at->translatedFormat('d F Y, H:i')" />
                        @endif
                    </div>
                </x-admin.section-card>
            </div>

            {{-- Role & Access --}}
            <div class="stagger-item">
                <x-admin.section-card title="Jabatan & Akses Menu" icon="shield">
                    <div class="space-y-5">
                        {{-- Jabatan --}}
                        <div class="flex items-start gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-600">
                                <x-admin.icon name="shield" :size="18" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jabatan</p>
                                @if($roleEnum)
                                    <p class="mt-0.5 text-sm font-bold text-slate-900">{{ $roleEnum->label() }}</p>
                                @else
                                    <p class="mt-0.5 text-sm text-slate-400">Tidak ada jabatan</p>
                                @endif
                            </div>
                        </div>

                        {{-- Separator --}}
                        <div class="border-t border-slate-100"></div>

                        {{-- Menu Access --}}
                        <div>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Menu yang Dapat Diakses</p>
                            <div class="flex flex-wrap gap-2">
                                @forelse($allGroups as $groupKey => $group)
                                    @php
                                        $hasAccess = in_array($groupKey, $allowedGroups);
                                    @endphp
                                    <span class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-bold transition
                                        {{ $hasAccess
                                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm'
                                            : 'bg-slate-50 text-slate-400 border border-slate-200 opacity-50' }}">
                                        @if($hasAccess)
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        @else
                                            <span class="size-1.5 rounded-full bg-slate-300"></span>
                                        @endif
                                        <x-admin.icon :name="$group['icon'] ?? 'folder'" :size="14" />
                                        {{ $group['label'] }}
                                    </span>
                                @empty
                                    <p class="text-sm text-slate-400">Tidak ada menu</p>
                                @endforelse
                            </div>
                            @if(!empty($extraSlugs))
                                <div class="mt-3">
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Akses Tambahan (Menu Spesifik)</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($extraSlugs as $slug)
                                            @php $menu = $allMenus[$slug] ?? null; @endphp
                                            @if($menu)
                                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700 border border-blue-100">
                                                    {{ $menu['label'] }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </x-admin.section-card>
            </div>

            {{-- Quick Stats --}}
            <div class="stagger-item">
                <x-admin.section-card title="Ringkasan" icon="bar-chart">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-emerald-50 p-4 text-center border border-emerald-100">
                            <p class="text-2xl font-bold text-emerald-700">{{ count($allowedGroups) }}</p>
                            <p class="mt-1 text-xs font-semibold text-emerald-600">Menu Akses</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4 text-center border border-slate-200">
                            <p class="text-2xl font-bold text-slate-700">{{ count($allGroups) }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Total Menu</p>
                        </div>
                    </div>
                </x-admin.section-card>
            </div>
        </div>

        <x-admin.confirm-delete
            name="generic-delete"
            :action="route('admin.resources.destroy', [$resource['slug'], $record])"
            title="Hapus Data"
            message="Data ini akan dihapus permanen. Aksi ini tidak bisa dibatalkan."
        />

        {{-- Floating Delete Action (pojok kanan bawah) --}}
        <button type="button" x-data="" x-on:click="$dispatch('open-modal', 'generic-delete')"
            class="group fixed bottom-6 right-6 z-50 grid size-14 place-items-center rounded-full bg-danger-600 text-white shadow-lg shadow-danger-600/30 transition hover:bg-danger-700 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-danger-100"
            title="Hapus Data — tindakan permanen dan tidak dapat dibatalkan"
            aria-label="Hapus Data">
            <x-admin.icon name="trash" :size="22" />
        </button>

        {{-- ============================================================ --}}
        {{-- MODAL RESET PASSWORD (superadmin) --}}
        {{-- ============================================================ --}}
        @if(auth()->user()->isSuperadmin())
            <x-admin.modal name="reset-password-user" title="Reset Password Pengguna" max-width="md">
                <p class="text-sm leading-relaxed text-slate-600">
                    Masukkan password baru untuk <span class="font-semibold text-slate-900">{{ $record->name }}</span>.
                    Password baru minimal 10 karakter dengan kombinasi huruf besar, huruf kecil, dan angka.
                </p>
                <form method="POST" action="{{ route('admin.user.reset-password', $record) }}" class="mt-4 space-y-4"
                    x-data="{ submitting: false }" x-on:submit="submitting = true">
                    @csrf
        <div x-data="{ show: false }">
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Password Baru</label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" name="password" required minlength="10" autocomplete="new-password"
                    class="admin-field pr-11" placeholder="Minimal 10 karakter (huruf besar, kecil, angka)">
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-0 grid w-11 place-items-center text-slate-400 transition hover:text-slate-600 focus:outline-none focus:text-emerald-600"
                    :title="show ? 'Sembunyikan password' : 'Tampilkan password'">
                    <x-admin.icon name="eye" :size="18" x-show="!show" />
                    <x-admin.icon name="eye-off" :size="18" x-show="show" x-cloak />
                </button>
            </div>
        </div>
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                        <button type="button" @click="closeModal()"
                            class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="submitting"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700 disabled:opacity-60">
                            <span x-show="submitting" x-cloak>Mereset...</span>
                            <span x-show="!submitting">Reset Password</span>
                        </button>
                    </div>
                </form>
            </x-admin.modal>
        @endif

    {{-- ============================================================ --}}
    {{-- GENERIC SHOW (Non-user resources) --}}
    {{-- ============================================================ --}}
    @else
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
                <x-admin.button variant="secondary" size="sm" icon="chevron-left" :href="route('admin.resources.index', $resource['slug'])">Kembali</x-admin.button>
                <x-admin.button variant="primary" size="sm" icon="edit" :href="route('admin.resources.edit', [$resource['slug'], $record])">Edit</x-admin.button>
            </x-slot:actions>
        </x-admin.page-header>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                @if($mainFields->isNotEmpty())
                    <div class="stagger-item">
                        <x-admin.section-card title="Informasi Utama" icon="file-text">
                            <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2">
                                @foreach($mainFields as $field)
                                    @php $value = $record->{$field['name']} ?? null; @endphp
                                    <x-admin.detail-field :label="$field['label']" :icon="$iconFor($field['name'])">
                                        @if(($field['type'] ?? null) === 'checkbox')
                                            {{ $value ? 'Ya' : 'Tidak' }}
                                        @elseif($field['name'] === 'jenis_kegiatan' && filled($value))
                                            {{ $value === 'monitoring-evaluasi' ? 'Monitoring & Evaluasi' : 'Sosialisasi' }}
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
                            <div class="space-y-6">
                                @foreach($textareaFields as $field)
                                    <div>
                                        <p class="mb-2 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">
                                            <span class="inline-flex size-7 items-center justify-center rounded-lg bg-slate-100 text-slate-400 dark:bg-white/[.06] dark:text-slate-500">
                                                <x-admin.icon name="message" :size="14" />
                                            </span>
                                            {{ $field['label'] }}
                                        </p>
                                        <p class="whitespace-pre-line pl-9 text-sm leading-relaxed text-slate-700">{{ $format($record->{$field['name']} ?? null) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </x-admin.section-card>
                    </div>
                @endif

                @if($resource['slug'] === 'sosialisasi' && $record->isMonitoringEvaluasi() && $record->pesertas()->count() > 0)
                    <div class="stagger-item">
                        <x-admin.section-card title="Daftar Hadir Monitoring & Evaluasi" icon="users">
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[720px] text-left text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-white/10 dark:text-slate-400">
                                            <th class="w-12 px-3 py-2.5 text-center">No</th>
                                            <th class="px-3 py-2.5">Nama Perusahaan</th>
                                            <th class="px-3 py-2.5">Jenis Usaha</th>
                                            <th class="px-3 py-2.5">Tanggal</th>
                                            <th class="px-3 py-2.5">Lokasi</th>
                                            <th class="px-3 py-2.5">Tim Survey</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($record->pesertas()->orderBy('id')->get() as $i => $peserta)
                                            @php $timNames = preg_split('/[\s,;]+/', (string) $peserta->tim_survey, -1, PREG_SPLIT_NO_EMPTY); @endphp
                                            <tr class="border-b border-slate-100 align-top last:border-0 dark:border-white/5">
                                                <td class="px-3 py-3 text-center font-bold text-slate-400">{{ $i + 1 }}</td>
                                                <td class="px-3 py-3 font-semibold text-slate-800 dark:text-slate-100">{{ $peserta->nama_perusahaan ?: '-' }}</td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ $peserta->jenis_usaha ?: '-' }}</td>
                                                <td class="px-3 py-3 whitespace-nowrap text-slate-600 dark:text-slate-300">{{ $peserta->tanggal?->format('d M Y') ?: '-' }}</td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ $peserta->lokasi ?: '-' }}</td>
                                                <td class="px-3 py-3">
                                                    <div class="flex flex-wrap gap-1.5">
                                                        @forelse($timNames as $nama)
                                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $nama }}</span>
                                                        @empty
                                                            <span class="text-slate-400">-</span>
                                                        @endforelse
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </x-admin.section-card>
                    </div>
                @endif

                {{-- Dokumen & Lampiran (field file tunggal) --}}
                @if($fileFields->isNotEmpty())
                    <div class="stagger-item">
                        <x-admin.section-card title="Dokumen & Lampiran" icon="download">
                            <div class="space-y-3">
                                @foreach($fileFields as $field)
                                    @php
                                        $docPath = $record->{$field['name']} ?? null;
                                        $docExt = $docPath ? pathinfo($docPath, PATHINFO_EXTENSION) : '';
                                        $docName = $docPath ? basename($docPath) : $field['label'];
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

                {{-- Relation galleries / files --}}
                @foreach($relationConfigs as $config)
                    @if(method_exists($record, $config['relation']) && $record->{$config['relation']} && $record->{$config['relation']}->isNotEmpty()
                        && ! ($resource['slug'] === 'sosialisasi' && $config['relation'] === 'pesertas' && $record->isMonitoringEvaluasi()))
                        <div class="stagger-item">
                            <x-admin.section-card :title="$config['title']" :icon="$config['image'] ? 'eye' : 'folder'">
                                @if($config['image'])
                                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                                        @foreach($record->{$config['relation']} as $item)
                                            @php
                                                $path = $pathFor($item, $config);
                                                $label = $nameFor($item, $path, $config);
                                                $fotoName = $path ? basename((string) $path) : $label;
                                            @endphp
                                            @if($path)
                                                <div class="flex flex-col gap-2 rounded-xl border border-slate-100 bg-slate-50/50 p-2">
                                                    <a href="{{ \App\Support\Admin\AdminRegistry::previewUrl($path, $resource['slug']) }}" target="_blank"
                                                        class="block aspect-square overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                                                        <img src="{{ \App\Support\Admin\AdminRegistry::previewUrl($path, $resource['slug']) }}" alt="{{ $label }}" loading="lazy" class="size-full object-cover transition hover:scale-105">
                                                    </a>
                                                    <div class="flex items-center gap-2">
                                                        <a href="{{ \App\Support\Admin\AdminRegistry::previewUrl($path, $resource['slug']) }}" target="_blank"
                                                            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-white px-2.5 py-1.5 text-xs font-bold text-blue-600 ring-1 ring-slate-200 transition hover:bg-blue-50">
                                                            <x-admin.icon name="eye" :size="14" /> Lihat
                                                        </a>
                                                        <a href="{{ route('admin.file.download', ['path' => $path, 'name' => $fotoName, 'resource' => $resource['slug']]) }}" target="_blank"
                                                            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-white px-2.5 py-1.5 text-xs font-bold text-emerald-600 ring-1 ring-slate-200 transition hover:bg-emerald-50">
                                                            <x-admin.icon name="download" :size="14" /> Unduh
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="space-y-3">
                                        @foreach($record->{$config['relation']} as $item)
                                            @php $path = $pathFor($item, $config); $label = $nameFor($item, $path, $config); @endphp
                                            <x-admin.file-preview :label="$label" :path="$path" :resource="$resource['slug']" />
                                        @endforeach
                                    </div>
                                @endif
                            </x-admin.section-card>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="space-y-6">
                @if($locationFields->isNotEmpty())
                    <div class="stagger-item">
                        <x-admin.section-card title="Lokasi & Koordinat" icon="map-pin">
                            @if($hasCoords)
                                <div id="admin-detail-map" style="height:280px" class="mb-4 w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-100"></div>
                            @endif
                            <div class="space-y-5">
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
                                    map.addControl(new DlhZoomControl(), 'top-left');

                                    if (window.DlhWeatherControl) map.addControl(new DlhWeatherControl(), 'top-right');
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
                        <div class="space-y-5">
                            <x-admin.detail-field label="Dibuat" icon="calendar" :value="$record->created_at?->translatedFormat('d F Y, H:i')" />
                            <x-admin.detail-field label="Diperbarui" icon="clock" :value="$record->updated_at?->translatedFormat('d F Y, H:i')" />
                        </div>
                    </x-admin.section-card>
                </div>

                <div class="stagger-item rounded-xl border border-red-100 bg-red-50/50 p-5 dark:border-red-500/20 dark:bg-red-900/10">
                    <div class="flex items-start gap-3">
                        <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                            <x-admin.icon name="trash" :size="20" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-slate-900 dark:text-white">Hapus Data</p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Tindakan ini permanen dan tidak dapat dibatalkan.</p>
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
    @endif
</div>
@endsection
