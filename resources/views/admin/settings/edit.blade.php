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
            <button @click="show = false" class="ml-auto shrink-0 text-emerald-400 hover:text-emerald-600" aria-label="Tutup notifikasi">
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
            <button @click="show = false" class="ml-auto shrink-0 text-red-400 hover:text-red-600" aria-label="Tutup pesan error">
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
                                <span :class="enabled ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700'"
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider transition-colors duration-300">
                                    <span x-text="enabled ? 'Aktif' : 'Nonaktif'"></span>
                                </span>
                            </div>
                            <p class="mt-1 text-xs leading-relaxed text-slate-500">Halaman publik akan menampilkan layar pemeliharaan. Panel admin tetap dapat diakses penuh.</p>
                        </div>
                    </div>
                    <div class="ml-4 shrink-0">
                        <input type="hidden" name="maintenance_enabled" :value="enabled ? '1' : '0'" />
                        <button type="button" @click="enabled = !enabled"
                            class="spring-toggle" :class="{ 'is-on': enabled }"
                            role="switch" :aria-checked="enabled ? 'true' : 'false'"
                            aria-label="Aktifkan mode pemeliharaan">
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

    @if($isSuperadmin)
        {{-- Konfigurasi AI Assistant --}}
        @php
            // Form edit antar provider memakai nama field yang sama, jadi hanya
            // form yang barusan dikirim (provider_id via old()) yang menampilkan error.
            $failedProviderId = old('provider_id');
            $providerTypeLabels = [
                'openrouter' => 'OpenRouter',
                'google'     => 'Google Gemini',
                'custom'     => 'Custom',
            ];
        @endphp
        <div class="mt-8">
            <div class="mb-4 flex items-center gap-3">
                <div class="grid size-10 place-items-center rounded-xl bg-violet-50 text-violet-600">
                    <x-admin.icon name="message" :size="20" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-ink-900">Konfigurasi AI Assistant</h2>
                    <p class="text-sm text-slate-500">Pilih provider OpenRouter, Google Gemini, atau Custom (OpenAI-compatible seperti TokenRouter). Provider dicoba berurutan dari prioritas terkecil — bila satu gagal/kuota habis, otomatis lanjut ke berikutnya.</p>
                </div>
            </div>

            <div class="space-y-4">
                @forelse($providers as $provider)
                    <div class="rounded-2xl border border-white/80 bg-white p-6 shadow-[0_12px_40px_rgba(15,23,42,0.06)]">
                        <div class="mb-5 flex items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <span class="grid size-8 place-items-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-600" title="Prioritas">#{{ $provider->priority }}</span>
                                <p class="text-sm font-bold text-ink-900">{{ $provider->name }}</p>
                                <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-violet-700">{{ $providerTypeLabels[$provider->type] ?? $provider->type }}</span>
                                <span class="max-w-[220px] truncate rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-500" title="{{ $provider->model }}">{{ $provider->model }}</span>
                                @if($provider->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-emerald-700">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-slate-500">
                                        <span class="size-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                    </span>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('admin.settings.providers.destroy', $provider) }}"
                                  class="js-confirm-delete" data-confirm="Hapus provider &quot;{{ $provider->name }}&quot;?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus provider" aria-label="Hapus provider {{ $provider->name }}">
                                    <x-admin.icon name="trash" :size="16" />
                                </button>
                            </form>
                        </div>

                        <form method="POST" action="{{ route('admin.settings.providers.update', $provider) }}"
                              class="grid grid-cols-1 gap-4 md:grid-cols-2"
                              x-data="aiProviderForm(@js(old('type', $provider->type)), @js(old('model', $provider->model)), @js(old('is_active', $provider->is_active ? '1' : '0') === '1'))">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="provider_id" value="{{ $provider->id }}" />

                            @php $showErr = $failedProviderId == $provider->id; @endphp

                            <x-admin.form-input
                                name="name"
                                label="Nama Provider"
                                :required="true"
                                :value="$provider->name"
                                :error="$showErr && $errors->has('name') ? $errors->first('name') : ''"
                            />

                            <x-admin.select
                                name="type"
                                label="Tipe Provider"
                                :required="true"
                                :options="[
                                    'openrouter' => 'OpenRouter',
                                    'google' => 'Google Gemini',
                                    'custom' => 'Custom (OpenAI-compatible)',
                                ]"
                                :selected="$provider->type"
                                placeholder="Pilih tipe provider"
                                x-model="type"
                                :error="$showErr && $errors->has('type') ? $errors->first('type') : ''"
                            />

                            <x-admin.form-input
                                name="api_key"
                                label="API Key"
                                toggleable
                                value=""
                                placeholder="•••• (terenkripsi — kosongkan untuk mempertahankan)"
                                data-provider-id="{{ $provider->id }}"
                                hint="Key tersimpan terenkripsi di database. Kosongkan bila tidak ingin mengubah key; key tersimpan akan dipakai saat memuat daftar model."
                                :error="$showErr && $errors->has('api_key') ? $errors->first('api_key') : ''"
                            />

                            <x-admin.form-input
                                name="priority"
                                label="Prioritas"
                                type="number"
                                :min="1"
                                :max="100"
                                :value="$provider->priority"
                                hint="Angka terkecil dicoba lebih dulu."
                                :error="$showErr && $errors->has('priority') ? $errors->first('priority') : ''"
                            />

                            {{-- Base URL hanya untuk provider custom --}}
                            <div x-show="type === 'custom'" x-cloak class="md:col-span-2">
                                <x-admin.form-input
                                    name="base_url"
                                    label="Base URL"
                                    type="url"
                                    :required="true"
                                    placeholder="https://api.tokenrouter.com/v1"
                                    hint="Akhiran /chat/completions ditambahkan otomatis bila belum ada."
                                    :value="$provider->base_url"
                                    :error="$showErr && $errors->has('base_url') ? $errors->first('base_url') : ''"
                                />
                            </div>

                            {{-- Muat daftar model (semua tipe provider) --}}
                            <div class="md:col-span-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <button type="button" @click="loadModels()" :disabled="loading"
                                            class="inline-flex h-10 items-center gap-2 rounded-xl bg-violet-600 px-4 text-xs font-bold text-white transition hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-60">
                                        <svg class="size-3.5 animate-spin" x-show="loading" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                        <span x-text="loading ? 'Memuat…' : 'Muat Daftar Model'"></span>
                                    </button>
                                    <p class="text-xs font-medium" :class="statusError ? 'text-red-500' : 'text-slate-500'" x-text="status" x-show="status" x-cloak></p>
                                </div>
                                <p class="mt-1.5 text-xs text-slate-500">OpenRouter menampilkan model gratis, Google menampilkan model Gemini untuk API key Anda, dan Custom membaca dari endpoint {base_url}/models.</p>
                            </div>

                            {{-- Field Model: dropdown pilihan + opsi tulis manual --}}
                            <div class="fi-field md:col-span-2">
                                <label class="fi-label">Model<span class="fi-required">*</span></label>
                                <input type="hidden" name="model" x-model="modelValue" />

                                <div class="fi-select-shell" :class="{ 'fi-select-shell--open': modelOpen }">
                                    <button type="button" @click="modelOpen = !modelOpen"
                                            class="fi-select-trigger {{ $showErr && $errors->has('model') ? 'fi-select-trigger--error' : '' }}">
                                        <span x-show="!modelChoice" class="fi-select-placeholder">Pilih model</span>
                                        <span x-show="modelChoice" x-text="modelChoiceLabel" class="fi-select-value"></span>
                                        <svg class="fi-select-chevron" :class="{ 'fi-select-chevron--open': modelOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                                    </button>

                                    <div x-show="modelOpen" x-cloak
                                         x-transition:enter="fi-select-enter"
                                         x-transition:enter-start="fi-select-enter-start"
                                         x-transition:enter-end="fi-select-enter-end"
                                         x-transition:leave="fi-select-leave"
                                         x-transition:leave-start="fi-select-leave-start"
                                         x-transition:leave-end="fi-select-leave-end"
                                         x-on:click.outside="modelOpen = false"
                                         class="fi-select-panel">
                                        <div class="fi-select-options-scroll">
                                            <template x-for="option in modelOptions" :key="option.value">
                                                <div x-on:click="selectModel(option.value)"
                                                     class="fi-select-option"
                                                     :class="{ 'fi-select-option--active': modelChoice === option.value }">
                                                    <span x-text="option.label" class="fi-select-option-text"></span>
                                                    <span x-show="modelChoice === option.value" class="fi-select-check">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                                    </span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="modelChoice === '__custom__'" x-cloak class="mt-2">
                                    <input type="text" x-model="customModel"
                                           placeholder="Tulis nama model secara manual, mis. qwen/qwen3.8-max-free"
                                           class="fi-pill-input" />
                                </div>

                                @if($showErr && $errors->has('model'))
                                    <p class="fi-error">
                                        <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                        {{ $errors->first('model') }}
                                    </p>
                                @else
                                    <p class="fi-hint-sub">Pilih model dari daftar, atau pilih "Lainnya" untuk menulis nama model secara manual.</p>
                                @endif
                            </div>

                            <div class="flex items-center gap-3 md:col-span-2">
                                <input type="hidden" name="is_active" :value="active ? '1' : '0'" />
                                <button type="button" @click="active = !active"
                                    class="spring-toggle" :class="{ 'is-on': active }"
                                    role="switch" :aria-checked="active ? 'true' : 'false'"
                                    aria-label="Aktifkan provider">
                                    <span class="spring-track"></span>
                                    <span class="spring-thumb">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </span>
                                </button>
                                <span class="text-sm font-medium text-slate-600">Aktif</span>
                                <div class="ml-auto">
                                    <x-admin.button variant="primary" type="submit" icon="check" class="rounded-xl px-5">
                                        Simpan
                                    </x-admin.button>
                                </div>
                            </div>
                        </form>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">
                        Belum ada provider AI. Tambahkan provider pertama lewat formulir di bawah.
                    </div>
                @endforelse

                {{-- Tambah provider baru --}}
                <div class="rounded-2xl border border-white/80 bg-white p-6 shadow-[0_12px_40px_rgba(15,23,42,0.06)]">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="grid size-9 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                            <x-admin.icon name="plus" :size="18" />
                        </div>
                        <p class="text-sm font-bold text-ink-900">Tambah Provider Baru</p>
                    </div>

                    @php $showAddErr = $failedProviderId === null && $errors->any(); @endphp

                    <form method="POST" action="{{ route('admin.settings.providers.store') }}"
                          class="grid grid-cols-1 gap-4 md:grid-cols-2"
                          x-data="aiProviderForm(@js(old('type', 'openrouter')), @js(old('model', '')), @js(old('is_active', '1') === '1'))">
                        @csrf

                        <x-admin.form-input
                            name="name"
                            label="Nama Provider"
                            :required="true"
                            placeholder="mis. TokenRouter"
                            :error="$showAddErr && $errors->has('name') ? $errors->first('name') : ''"
                        />

                        <x-admin.select
                            name="type"
                            label="Tipe Provider"
                            :required="true"
                            :options="[
                                'openrouter' => 'OpenRouter',
                                'google' => 'Google Gemini',
                                'custom' => 'Custom (OpenAI-compatible)',
                            ]"
                            selected="openrouter"
                            placeholder="Pilih tipe provider"
                            x-model="type"
                            :error="$showAddErr && $errors->has('type') ? $errors->first('type') : ''"
                        />

                        <x-admin.form-input
                            name="api_key"
                            label="API Key"
                            toggleable
                            :required="true"
                            placeholder="sk-..."
                            :error="$showAddErr && $errors->has('api_key') ? $errors->first('api_key') : ''"
                        />

                        <x-admin.form-input
                            name="priority"
                            label="Prioritas"
                            type="number"
                            :min="1"
                            :max="100"
                            :value="1"
                            hint="Angka terkecil dicoba lebih dulu."
                            :error="$showAddErr && $errors->has('priority') ? $errors->first('priority') : ''"
                        />

                        {{-- Base URL hanya untuk provider custom --}}
                        <div x-show="type === 'custom'" x-cloak class="md:col-span-2">
                            <x-admin.form-input
                                name="base_url"
                                label="Base URL"
                                type="url"
                                :required="true"
                                placeholder="https://api.tokenrouter.com/v1"
                                hint="Akhiran /chat/completions ditambahkan otomatis bila belum ada."
                                :error="$showAddErr && $errors->has('base_url') ? $errors->first('base_url') : ''"
                            />
                        </div>

                        {{-- Muat daftar model (semua tipe provider) --}}
                        <div class="md:col-span-2">
                            <div class="flex flex-wrap items-center gap-3">
                                <button type="button" @click="loadModels()" :disabled="loading"
                                        class="inline-flex h-10 items-center gap-2 rounded-xl bg-violet-600 px-4 text-xs font-bold text-white transition hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-60">
                                    <svg class="size-3.5 animate-spin" x-show="loading" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                    <span x-text="loading ? 'Memuat…' : 'Muat Daftar Model'"></span>
                                </button>
                                <p class="text-xs font-medium" :class="statusError ? 'text-red-500' : 'text-slate-500'" x-text="status" x-show="status" x-cloak></p>
                            </div>
                            <p class="mt-1.5 text-xs text-slate-500">OpenRouter menampilkan model gratis, Google menampilkan model Gemini untuk API key Anda, dan Custom membaca dari endpoint {base_url}/models.</p>
                        </div>

                        {{-- Field Model: dropdown pilihan + opsi tulis manual --}}
                        <div class="fi-field md:col-span-2">
                            <label class="fi-label">Model<span class="fi-required">*</span></label>
                            <input type="hidden" name="model" x-model="modelValue" />

                            <div class="fi-select-shell" :class="{ 'fi-select-shell--open': modelOpen }">
                                <button type="button" @click="modelOpen = !modelOpen"
                                        class="fi-select-trigger {{ $showAddErr && $errors->has('model') ? 'fi-select-trigger--error' : '' }}">
                                    <span x-show="!modelChoice" class="fi-select-placeholder">Pilih model</span>
                                    <span x-show="modelChoice" x-text="modelChoiceLabel" class="fi-select-value"></span>
                                    <svg class="fi-select-chevron" :class="{ 'fi-select-chevron--open': modelOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                                </button>

                                <div x-show="modelOpen" x-cloak
                                     x-transition:enter="fi-select-enter"
                                     x-transition:enter-start="fi-select-enter-start"
                                     x-transition:enter-end="fi-select-enter-end"
                                     x-transition:leave="fi-select-leave"
                                     x-transition:leave-start="fi-select-leave-start"
                                     x-transition:leave-end="fi-select-leave-end"
                                     x-on:click.outside="modelOpen = false"
                                     class="fi-select-panel">
                                    <div class="fi-select-options-scroll">
                                        <template x-for="option in modelOptions" :key="option.value">
                                            <div x-on:click="selectModel(option.value)"
                                                 class="fi-select-option"
                                                 :class="{ 'fi-select-option--active': modelChoice === option.value }">
                                                <span x-text="option.label" class="fi-select-option-text"></span>
                                                <span x-show="modelChoice === option.value" class="fi-select-check">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                                </span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div x-show="modelChoice === '__custom__'" x-cloak class="mt-2">
                                <input type="text" x-model="customModel"
                                       placeholder="Tulis nama model secara manual, mis. qwen/qwen3.8-max-free"
                                       class="fi-pill-input" />
                            </div>

                            @if($showAddErr && $errors->has('model'))
                                <p class="fi-error">
                                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                    {{ $errors->first('model') }}
                                </p>
                            @else
                                <p class="fi-hint-sub">Pilih model dari daftar, atau pilih "Lainnya" untuk menulis nama model secara manual.</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-3 md:col-span-2">
                            <input type="hidden" name="is_active" :value="active ? '1' : '0'" />
                            <button type="button" @click="active = !active"
                                class="spring-toggle" :class="{ 'is-on': active }"
                                role="switch" :aria-checked="active ? 'true' : 'false'"
                                aria-label="Aktifkan provider">
                                <span class="spring-track"></span>
                                <span class="spring-thumb">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </span>
                            </button>
                            <span class="text-sm font-medium text-slate-600">Aktif</span>
                            <div class="ml-auto">
                                <x-admin.button variant="primary" type="submit" icon="plus" class="rounded-xl px-5">
                                    Tambah Provider
                                </x-admin.button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Monitoring Infrastruktur --}}
    <div class="mt-8">
        <div class="mb-4 flex items-center gap-3">
            <div class="grid size-10 place-items-center rounded-xl bg-brand-50 text-brand-600">
                <x-admin.icon name="chart" :size="20" />
            </div>
            <div>
                <h2 class="text-lg font-bold text-ink-900">Monitoring Infrastruktur</h2>
                <p class="text-sm text-slate-500">Pantauan penggunaan penyimpanan cloud &amp; database (diperbarui otomatis tiap 5 menit).</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Card: Backblaze B2 --}}
            <div class="rounded-2xl border border-white/80 bg-white p-6 shadow-[0_12px_40px_rgba(15,23,42,0.06)]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="grid size-11 place-items-center rounded-xl bg-gradient-to-br from-sky-100 to-blue-100 text-sky-600">
                            <x-admin.icon name="package" :size="22" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-ink-900">Backblaze B2</p>
                            <p class="text-xs text-slate-500">Total storage terpakai</p>
                        </div>
                    </div>
                    @if($b2['error'])
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-red-700">
                            <span class="size-1.5 rounded-full bg-red-500"></span> Error
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-emerald-700">
                            <span class="size-1.5 rounded-full bg-emerald-500"></span> Live
                        </span>
                    @endif
                </div>

                @if($b2['error'])
                    <p class="mt-5 text-sm font-medium text-red-600">{{ $b2['message'] }}</p>
                    <p class="mt-1 break-words text-xs text-slate-400">{{ $b2['details'] }}</p>
                @else
                    <div class="mt-5">
                        <p class="text-2xl font-bold tracking-tight text-ink-900">{{ $b2['human'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">
                            dari kuota {{ $b2['limit_human'] }}
                            <span class="font-semibold text-slate-600">({{ $b2['percent'] }}%)</span>
                        </p>
                    </div>

                    <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-gradient-to-r from-sky-400 to-blue-500 transition-all duration-500"
                             style="width: {{ min(100, $b2['percent']) }}%"></div>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                        <span>{{ $b2['files'] }} file</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 font-medium text-slate-600" title="Status paket Backblaze B2">
                            <x-admin.icon name="tag" :size="12" /> {{ $b2['plan'] }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- Card: Neon PostgreSQL --}}
            <div class="rounded-2xl border border-white/80 bg-white p-6 shadow-[0_12px_40px_rgba(15,23,42,0.06)]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="grid size-11 place-items-center rounded-xl bg-gradient-to-br from-violet-100 to-fuchsia-100 text-violet-600">
                            <x-admin.icon name="database" :size="22" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-ink-900">Neon PostgreSQL</p>
                            <p class="text-xs text-slate-500">Database size terpakai</p>
                        </div>
                    </div>
                    @if($neon['error'])
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-red-700">
                            <span class="size-1.5 rounded-full bg-red-500"></span> Error
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-emerald-700">
                            <span class="size-1.5 rounded-full bg-emerald-500"></span> Live
                        </span>
                    @endif
                </div>

                @if($neon['error'])
                    <p class="mt-5 text-sm font-medium text-red-600">{{ $neon['message'] }}</p>
                    <p class="mt-1 break-words text-xs text-slate-400">{{ $neon['details'] }}</p>
                @else
                    <div class="mt-5">
                        <p class="text-2xl font-bold tracking-tight text-ink-900">{{ $neon['human'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">
                            dari kuota {{ $neon['limit_human'] }}
                            <span class="font-semibold text-slate-600">({{ $neon['percent'] }}%)</span>
                        </p>
                    </div>

                    <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-gradient-to-r from-violet-400 to-fuchsia-500 transition-all duration-500"
                             style="width: {{ min(100, $neon['percent']) }}%"></div>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                        <span class="truncate" title="Database: {{ $neon['database'] }}">DB: {{ $neon['database'] }}</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 font-medium text-slate-600" title="Status paket Neon PostgreSQL">
                            <x-admin.icon name="tag" :size="12" /> {{ $neon['plan'] }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
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

@if($isSuperadmin)
@push('scripts')
<script>
    // Form provider AI: show/hide field sesuai tipe + muat daftar model + dropdown model.
    function aiProviderForm(initialType, initialModel, initialActive) {
        return {
            type: initialType || 'openrouter',
            active: initialActive !== false,
            models: [],
            loading: false,
            status: '',
            statusError: false,

            // Dropdown model: choice = id model atau '__custom__' untuk tulis manual.
            modelOpen: false,
            modelChoice: initialModel || '',
            customModel: '',

            get modelValue() {
                return this.modelChoice === '__custom__' ? this.customModel : this.modelChoice;
            },

            get modelOptions() {
                const options = [];

                // Model tersimpan yang tidak ada di daftar hasil muat tetap ditampilkan.
                if (this.modelChoice && this.modelChoice !== '__custom__'
                    && !this.models.some((m) => m.id === this.modelChoice)) {
                    options.push({ value: this.modelChoice, label: this.modelChoice });
                }

                for (const m of this.models) {
                    options.push({
                        value: m.id,
                        label: m.label && m.label !== m.id ? m.label + ' (' + m.id + ')' : m.id,
                    });
                }

                options.push({ value: '__custom__', label: 'Lainnya (tulis manual)' });

                return options;
            },

            get modelChoiceLabel() {
                if (!this.modelChoice) return '';
                if (this.modelChoice === '__custom__') {
                    return this.customModel || 'Lainnya (tulis manual)';
                }
                const found = this.models.find((m) => m.id === this.modelChoice);
                return found && found.label && found.label !== found.id
                    ? found.label + ' (' + found.id + ')'
                    : this.modelChoice;
            },

            selectModel(value) {
                this.modelChoice = value;
                this.modelOpen = false;
            },

            async loadModels() {
                const apiKeyInput = this.$root.querySelector('[name="api_key"]');
                const apiKey = apiKeyInput?.value?.trim();
                // Form edit menyimpan provider_id di data-attribute agar backend bisa
                // memakai api_key terenkripsi dari DB saat input dikosongkan.
                const providerId = apiKeyInput?.dataset?.providerId || null;

                if (!apiKey && !providerId) {
                    this.status = 'Isi API key terlebih dahulu untuk memuat daftar model.';
                    this.statusError = true;
                    return;
                }

                let baseUrl = null;
                if (this.type === 'custom') {
                    baseUrl = this.$root.querySelector('[name="base_url"]')?.value?.trim();
                    if (!baseUrl) {
                        this.status = 'Isi Base URL terlebih dahulu untuk memuat daftar model.';
                        this.statusError = true;
                        return;
                    }
                }

                this.loading = true;
                this.status = '';
                this.statusError = false;

                try {
                    const response = await fetch(@json(route('admin.settings.providers.models')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ type: this.type, api_key: apiKey || null, base_url: baseUrl, provider_id: providerId }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || data.message || 'Gagal memuat daftar model.');
                    }

                    this.models = data.models || [];
                    this.status = this.models.length
                        ? this.models.length + ' model ditemukan.'
                        : 'Tidak ada model yang cocok ditemukan.';
                    this.statusError = false;

                    if (this.models.length) this.modelOpen = true;
                } catch (error) {
                    this.models = [];
                    this.status = error.message;
                    this.statusError = true;
                } finally {
                    this.loading = false;
                }
            },
        };
    }

    // CSP: konfirmasi hapus provider dipasang via addEventListener, bukan onsubmit inline.
    document.querySelectorAll('form.js-confirm-delete').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.getAttribute('data-confirm') || 'Yakin?')) {
                event.preventDefault();
            }
        });
    });
</script>
@endpush
@endif
