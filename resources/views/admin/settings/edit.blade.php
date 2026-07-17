@extends('layouts.admin')

@section('title', 'Pengaturan - Admin DLH')
@section('heading', 'Pengaturan')

@section('content')
    <x-admin.page-header
        title="Pengaturan"
        subtitle="Preferensi tampilan akun Anda{{ $isSuperadmin ? ' dan konfigurasi aplikasi' : '' }}."
        icon="settings"
    />

    @if($errors->any())
        <x-admin.alert type="error" dismissible>
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </x-admin.alert>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <x-admin.card>
            <h2 class="mb-1 text-h4 font-bold text-ink-900">Preferensi Tampilan</h2>
            <p class="mb-5 text-sm text-slate-500">Pengaturan pribadi untuk akun Anda.</p>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-bold text-ink-700">Data per halaman</label>
                    <input type="number" name="per_page" min="5" max="100" value="{{ old('per_page', $user->pref('per_page', 15)) }}"
                        class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    <p class="mt-1 text-xs text-slate-400">Antara 5 dan 100 baris.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-bold text-ink-700">Bahasa Default</label>
                    <select name="locale" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-2 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                        <option value="id" @selected($user->pref('locale', 'id') === 'id')>Indonesia</option>
                        <option value="en" @selected($user->pref('locale', 'id') === 'en')>English</option>
                    </select>
                </div>
            </div>
        </x-admin.card>

        @if($isSuperadmin)
            <x-admin.card>
                <h2 class="mb-1 text-h4 font-bold text-ink-900">Konfigurasi Aplikasi</h2>
                <p class="mb-5 text-sm text-slate-500">Pengaturan global — hanya Kepala Bidang (Superadmin).</p>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-bold text-ink-700">Nama Aplikasi</label>
                        <input name="app_name" value="{{ old('app_name', $app['app_name']) }}"
                            class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-bold text-ink-700">Email Kontak</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $app['contact_email']) }}"
                            class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-bold text-ink-700">Telepon Kontak</label>
                        <input name="contact_phone" value="{{ old('contact_phone', $app['contact_phone']) }}"
                            class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card>
                <h2 class="mb-1 text-h4 font-bold text-ink-900">Pengaturan Survei IKM</h2>
                <p class="mb-5 text-sm text-slate-500">Konfigurasi perlindungan survei dari pengiriman duplikat.</p>

                <div class="rounded-lg border border-slate-200 bg-slate-50 px-5 py-4"
                     x-data="{ enabled: {{ $ikmFingerprintEnabled ? 'true' : 'false' }} }">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-bold text-ink-700">FingerprintJS</p>
                                <span :class="enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider transition-colors duration-300">
                                    <span x-text="enabled ? 'Aktif' : 'Nonaktif'"></span>
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Aktifkan untuk membatasi satu device hanya bisa satu kali mengikuti survei per minggu. Device akan diidentifikasi menggunakan browser fingerprint.</p>
                        </div>
                        <div class="ml-4 shrink-0">
                            <input type="hidden" name="ikm_fingerprint_enabled" :value="enabled ? '1' : '0'" />
                            {{-- iOS Spring Toggle --}}
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
                </div>
            </x-admin.card>
        @endif

        <div class="flex justify-end">
            <x-admin.button variant="primary" type="submit" icon="check">Simpan Pengaturan</x-admin.button>
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
    /* spring overshoot */
    transition: left 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.spring-toggle.is-on .spring-thumb {
    left: 33px;
}

/* thumb squish on press */
.spring-toggle:active .spring-thumb {
    width: 34px;
    border-radius: 16px;
}

.spring-toggle.is-on:active .spring-thumb {
    left: 27px;
}

/* icon inside thumb */
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
