@extends('layouts.admin')

@section('title', 'Pengaturan - Admin DLH')
@section('heading', 'Pengaturan')

@section('content')
    {{-- Hero Header --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-800 via-slate-700 to-slate-900 p-6 text-white shadow-xl sm:p-8">
        <div class="bg-grain pointer-events-none absolute inset-0 opacity-[0.04]"></div>
        <div class="pointer-events-none absolute -right-20 -top-20 size-64 rounded-full bg-brand-400/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-16 left-1/4 size-48 rounded-full bg-emerald-400/10 blur-3xl"></div>

        <div class="relative flex items-center gap-4">
            <div class="grid size-14 shrink-0 place-items-center rounded-2xl bg-white/10 backdrop-blur-sm">
                <x-admin.icon name="settings" :size="26" />
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Pengaturan</h1>
                <p class="mt-1 text-sm text-white/60">Konfigurasi sistem aplikasi.</p>
            </div>
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
            x-init="setTimeout(() => show = false, 4000)"
            class="mt-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3.5 text-sm font-medium text-emerald-700 shadow-sm">
            <div class="grid size-8 shrink-0 place-items-center rounded-lg bg-emerald-100">
                <x-admin.icon name="check-circle" :size="18" />
            </div>
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="ml-auto shrink-0 text-emerald-400 hover:text-emerald-600">
                <x-admin.icon name="x" :size="16" />
            </button>
        </div>
    @endif

    {{-- Error Messages --}}
    @if($errors->any())
        <div x-data="{ show: true }" x-show="show" x-transition
            class="mt-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-3.5 text-sm text-red-700 shadow-sm">
            <div class="grid size-8 shrink-0 place-items-center rounded-lg bg-red-100">
                <x-admin.icon name="alert-circle" :size="18" />
            </div>
            <ul class="list-inside list-disc space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button @click="show = false" class="ml-auto shrink-0 text-red-400 hover:text-red-600">
                <x-admin.icon name="x" :size="16" />
            </button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        {{-- Mode Pemeliharaan --}}
        <div class="rounded-2xl border border-white/80 bg-white p-6 shadow-[0_12px_40px_rgba(15,23,42,0.06)]">
            <div class="mb-6 flex items-center gap-3">
                <div class="grid size-10 place-items-center rounded-xl bg-amber-50 text-amber-600">
                    <x-admin.icon name="alert-triangle" :size="20" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-ink-900">Mode Pemeliharaan</h2>
                    <p class="text-sm text-slate-500">Tutup sementara akses situs publik untuk keperluan pemeliharaan.</p>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-5 transition hover:border-brand-200 hover:bg-brand-50/20"
                 x-data="{ enabled: {{ $maintenanceEnabled ? 'true' : 'false' }} }">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-100 text-amber-600">
                            <x-admin.icon name="settings" :size="22" />
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-bold text-ink-700">Aktifkan Mode Pemeliharaan</p>
                                <span :class="enabled ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500'"
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider transition-colors duration-300">
                                    <span x-text="enabled ? 'Aktif' : 'Nonaktif'"></span>
                                </span>
                            </div>
                            <p class="mt-1 text-xs leading-relaxed text-slate-500">Halaman publik akan menampilkan layar pemeliharaan. Panel admin tetap dapat diakses penuh.</p>
                        </div>
                    </div>
                    <div class="ml-4 shrink-0">
                        <input type="hidden" name="maintenance_enabled" :value="enabled ? '1' : '0'" />
                        <button type="button" @click="enabled = !enabled"
                            class="spring-toggle" :class="{ 'is-on': enabled }">
                            <span class="spring-track"></span>
                            <span class="spring-thumb">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>

                {{-- Pengaturan lanjutan (hanya saat aktif) --}}
                <div x-show="enabled" x-collapse x-cloak class="mt-5 space-y-5 border-t border-slate-200 pt-5">
                    <div>
                        <x-admin.date-field
                            type="datetime-local"
                            name="maintenance_estimated_at"
                            label="Estimasi Selesai"
                            icon="clock"
                            :value="$maintenanceEstimatedAt"
                            hint="Opsional. Jika diisi, pengunjung akan melihat perkiraan waktu & hitung mundur."
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <x-admin.button variant="primary" type="submit" icon="check" class="rounded-xl px-6">
                Simpan Pengaturan
            </x-admin.button>
        </div>
    </form>
@endsection

@push('styles')
<style>
/* ===================== iOS SPRING TOGGLE ===================== */
.spring-toggle {
    position: relative;
    width: 64px;
    height: 34px;
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
}

.spring-track {
    position: absolute;
    inset: 0;
    border-radius: 999px;
    background: #CBD5E1;
    border: 1px solid rgba(0, 0, 0, 0.04);
    transition: background 0.4s ease, box-shadow 0.3s ease;
}

.spring-toggle.is-on .spring-track {
    background: #059669;
    box-shadow: 0 0 0 1px rgba(5, 150, 105, 0.15);
}

.spring-thumb {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15), 0 1px 2px rgba(0, 0, 0, 0.06);
    transition: left 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.spring-toggle.is-on .spring-thumb {
    left: 33px;
}

.spring-toggle:active .spring-thumb {
    width: 34px;
    border-radius: 16px;
}

.spring-toggle.is-on:active .spring-thumb {
    left: 27px;
}

.spring-thumb svg {
    width: 14px;
    height: 14px;
    color: #94A3B8;
    transition: transform 0.4s ease, color 0.4s ease;
}

.spring-toggle.is-on .spring-thumb svg {
    transform: rotate(360deg);
    color: #059669;
}
</style>
@endpush
