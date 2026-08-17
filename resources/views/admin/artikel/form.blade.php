@extends('layouts.admin')

@section('title', ($record->exists ? 'Edit ' : 'Tambah ').'Artikel · Admin DLH')
@section('heading', $resource['label'])

@php
    $isEdit = $record->exists;
    $artikelStatusOptions = \App\Enums\ArtikelStatus::options();
    $selectedStatus = old('status', $record->status?->value ?? '');
    $selectedTanggal = old('tanggal_publish', $record->tanggal_publish?->format('Y-m-d'));
@endphp

@section('content')
<div class="mx-auto max-w-6xl pb-28"
    x-data="artikelForm(@js($isEdit), @js((bool) ($record->thumbnail ?? null)), @js($selectedStatus), @js($selectedTanggal), @js($artikelStatusOptions))"
    x-on:submit="handleSubmit($event)"
    x-on:change="onFieldChange($event)"
    x-init="window.staggerReveal($el.querySelectorAll('.stagger-item'), 70)">

    <form id="artikel-form" method="POST" action="{{ $action }}" enctype="multipart/form-data" novalidate>
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div class="space-y-7">
            {{-- Header + breadcrumb navigasi --}}
            <x-admin.page-header
                class="stagger-item"
                :title="$isEdit ? 'Edit Artikel' : 'Tambah Artikel Baru'"
                subtitle="Lengkapi seluruh kolom bertanda * untuk menyimpan artikel."
                :icon="$isEdit ? 'edit' : 'plus'"
                :breadcrumbs="[
                    ['label' => $resource['label'], 'url' => route('admin.resources.index', $resource['slug'])],
                    ['label' => $isEdit ? 'Edit' : 'Tambah Baru'],
                ]"
            >
                <x-slot:actions>
                    <x-admin.status-pill :variant="$isEdit ? 'info' : 'success'" :label="$isEdit ? 'Mode Edit' : 'Mode Tambah'" />
                </x-slot:actions>
            </x-admin.page-header>

            {{-- Error banner --}}
            <template x-if="errorCount > 0">
                <div class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700"
                    x-transition>
                    <x-admin.icon name="alert-triangle" :size="18" class="mt-0.5 shrink-0" />
                    <div>
                        <p class="font-semibold">Terdapat <span x-text="errorCount"></span> field yang belum diisi.</p>
                        <p class="text-rose-600/80">Mohon lengkapi semua field wajib sebelum menyimpan.</p>
                    </div>
                </div>
            </template>

            <div class="grid grid-cols-1 gap-7 lg:grid-cols-3">
                {{-- ═══════════ Kolom kiri: konten utama ═══════════ --}}
                <div class="min-w-0 space-y-7 lg:col-span-2">
                    {{-- Section: Informasi Artikel --}}
                    <x-admin.section-card class="stagger-item" title="Informasi Artikel" icon="file-text" number="1" subtitle="Judul dan gambar unggulan artikel.">
                        <div class="space-y-7">
                            <div data-error-key="judul">
                                <x-admin.form-input
                                    id="artikel_judul"
                                    name="judul"
                                    label="Judul Artikel"
                                    required
                                    :value="old('judul', $record->judul)"
                                    placeholder="Masukkan judul artikel"
                                    :error="$errors->first('judul')"
                                    x-on:input="clearError('judul')"
                                />
                                <p x-show="errors.judul" x-cloak x-text="errors.judul" class="mt-2 text-xs font-semibold text-rose-600"></p>
                            </div>

                            <div data-error-key="thumbnail">
                                <x-admin.file-upload
                                    name="thumbnail"
                                    label="Thumbnail"
                                    accept="image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif"
                                    :required="!$record->thumbnail"
                                    :current-file="$record->thumbnail"
                                    hint="Gambar utama artikel. Format JPG, PNG, WEBP, AVIF, atau HEIC. Maksimal 5MB."
                                    :error="$errors->first('thumbnail')"
                                />
                                <p x-show="errors.thumbnail" x-cloak x-text="errors.thumbnail" class="mt-2 text-xs font-semibold text-rose-600"></p>
                            </div>
                        </div>
                    </x-admin.section-card>

                    {{-- Section: Konten --}}
                    <x-admin.section-card class="stagger-item" title="Konten Artikel" icon="news" number="2" subtitle="Tulis isi artikel yang akan tampil di halaman publik.">
                        <div data-error-key="konten">
                            <x-admin.jodit-editor
                                id="artikel_konten"
                                name="konten"
                                label="Konten Artikel"
                                required
                                :value="old('konten', $record->konten)"
                                :error="$errors->first('konten')"
                            />
                            <p x-show="errors.konten" x-cloak x-text="errors.konten" class="mt-2 text-xs font-semibold text-rose-600"></p>
                        </div>
                    </x-admin.section-card>
                </div>

                {{-- ═══════════ Kolom kanan: pengaturan publikasi ═══════════ --}}
                <div class="space-y-7 lg:sticky lg:top-24 lg:self-start">
                    {{-- Section: Pengaturan Publikasi --}}
                    <x-admin.section-card class="stagger-item" title="Pengaturan Publikasi" icon="send" number="3" subtitle="Kapan artikel tayang dan statusnya.">
                        <div class="space-y-7">
                            <div data-error-key="tanggal_publish">
                                <x-admin.date-field
                                    id="artikel_tanggal"
                                    name="tanggal_publish"
                                    type="date"
                                    label="Tanggal Publish"
                                    required
                                    :value="$selectedTanggal"
                                    :error="$errors->first('tanggal_publish')"
                                    x-on:change="clearError('tanggal_publish')"
                                />
                                <p x-show="errors.tanggal_publish" x-cloak x-text="errors.tanggal_publish" class="mt-2 text-xs font-semibold text-rose-600"></p>
                            </div>

                            <div data-error-key="status">
                                <x-admin.select
                                    name="status"
                                    label="Status"
                                    required
                                    :options="$artikelStatusOptions"
                                    :selected="$selectedStatus"
                                    placeholder="Pilih status"
                                    :error="$errors->first('status')"
                                />
                                <p x-show="errors.status" x-cloak x-text="errors.status" class="mt-2 text-xs font-semibold text-rose-600"></p>
                            </div>
                        </div>
                    </x-admin.section-card>

                    {{-- Ringkasan publikasi (live) --}}
                    <section class="stagger-item relative overflow-hidden rounded-xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/90 via-white to-teal-50/70 p-6 shadow-[var(--shadow-soft)]">
                        <div class="pointer-events-none absolute -right-10 -top-10 size-32 rounded-full bg-emerald-200/40 blur-2xl"></div>
                        <div class="relative">
                            <div class="flex items-center gap-3">
                                <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-white text-emerald-600 shadow-sm ring-1 ring-emerald-200/70">
                                    <x-admin.icon name="eye" :size="19" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">Ringkasan Publikasi</h3>
                                    <p class="text-xs text-slate-500">Pratinjau pengaturan artikel Anda.</p>
                                </div>
                            </div>

                            <dl class="mt-5 space-y-2.5">
                                <div class="flex items-center justify-between gap-3 rounded-xl bg-white/80 px-4 py-3 ring-1 ring-slate-200/60">
                                    <dt class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        <x-admin.icon name="send" :size="14" class="text-emerald-500" /> Status
                                    </dt>
                                    <dd class="text-sm font-bold text-slate-800" x-text="statusOptions[summaryStatus] || '—'"></dd>
                                </div>
                                <div class="flex items-center justify-between gap-3 rounded-xl bg-white/80 px-4 py-3 ring-1 ring-slate-200/60">
                                    <dt class="flex shrink-0 items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        <x-admin.icon name="calendar" :size="14" class="text-emerald-500" /> Tanggal
                                    </dt>
                                    <dd class="text-right text-sm font-bold text-slate-800" x-text="summaryTanggalLabel"></dd>
                                </div>
                                <div class="flex items-center justify-between gap-3 rounded-xl bg-white/80 px-4 py-3 ring-1 ring-slate-200/60">
                                    <dt class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        <x-admin.icon name="image" :size="14" class="text-emerald-500" /> Thumbnail
                                    </dt>
                                    <dd class="text-sm font-bold" :class="summaryThumb ? 'text-emerald-600' : 'text-amber-600'"
                                        x-text="summaryThumb ? 'Sudah terisi' : 'Belum diunggah'"></dd>
                                </div>
                            </dl>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        {{-- Floating action buttons --}}
        <div class="fixed bottom-6 right-6 z-40 flex items-center gap-3" x-data="{ show: false }" x-init="setTimeout(() => show = true, 300)" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <a href="{{ route('admin.resources.index', $resource['slug']) }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-lg transition hover:bg-slate-50 hover:shadow-xl">
                <x-admin.icon name="x" :size="16" />
                Batal
            </a>
            <button type="submit" :disabled="submitting"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:from-emerald-600 hover:to-emerald-700 hover:shadow-xl disabled:opacity-60">
                <template x-if="!submitting"><span class="flex items-center gap-2"><x-admin.icon name="device-floppy" :size="16" />{{ $isEdit ? 'Perbarui Artikel' : 'Simpan Artikel' }}</span></template>
                <template x-if="submitting"><span class="flex items-center gap-2"><svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Menyimpan…</span></template>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function artikelForm(isEdit, hasExistingThumb, initialStatus, initialTanggal, statusOptions) {
        return {
            submitting: false,
            errors: {},
            hasExistingThumb: hasExistingThumb,
            summaryStatus: initialStatus || '',
            summaryTanggal: initialTanggal || '',
            summaryThumb: !!hasExistingThumb,
            statusOptions: statusOptions || {},
            get errorCount() { return Object.keys(this.errors).length; },
            get summaryTanggalLabel() {
                if (!this.summaryTanggal) return 'Belum dipilih';
                const m = String(this.summaryTanggal).match(/^(\d{4})-(\d{2})-(\d{2})/);
                if (!m) return this.summaryTanggal;
                const d = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
                try {
                    return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                } catch (e) {
                    return this.summaryTanggal;
                }
            },
            clearError(key) {
                if (this.errors[key]) { delete this.errors[key]; }
            },
            onFieldChange(e) {
                const t = e && e.target;
                if (!t || !t.name) return;
                if (t.name === 'status') this.summaryStatus = t.value;
                if (t.name === 'tanggal_publish') this.summaryTanggal = t.value;
                if (t.name === 'thumbnail' && t.files && t.files.length > 0) this.summaryThumb = true;
            },
            syncKonten() {
                const ta = document.getElementById('artikel_konten');
                const editor = window['jodit_artikel_konten'];
                if (ta && editor && typeof editor.value !== 'undefined') {
                    ta.value = editor.value;
                }
            },
            validate() {
                const errors = {};

                const judul = (document.querySelector('#artikel-form [name="judul"]')?.value || '').trim();
                if (!judul) { errors.judul = 'Judul artikel wajib diisi.'; }

                const fileInput = document.querySelector('#artikel-form input[name="thumbnail"]');
                const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                if (!hasFile && !this.hasExistingThumb) { errors.thumbnail = 'Thumbnail wajib diunggah.'; }

                const ta = document.getElementById('artikel_konten');
                const html = (ta?.value || '').trim();
                const plain = html.replace(/<[^>]*>/g, ' ').replace(/&nbsp;/gi, ' ').replace(/\s+/g, ' ').trim();
                const hasMedia = /<(img|video|table|iframe)\b/i.test(html);
                if (!plain && !hasMedia) { errors.konten = 'Konten artikel wajib diisi.'; }

                const tanggal = (document.querySelector('#artikel-form [name="tanggal_publish"]')?.value || '').trim();
                if (!tanggal) { errors.tanggal_publish = 'Tanggal publish wajib dipilih.'; }

                const statusInput = document.querySelector('#artikel-form [name="status"]');
                if (!statusInput || !statusInput.value) { errors.status = 'Status wajib dipilih.'; }

                this.errors = errors;
                return errors;
            },
            handleSubmit(e) {
                if (this.submitting) { e.preventDefault(); return; }
                this.syncKonten();
                const errors = this.validate();

                if (Object.keys(errors).length) {
                    e.preventDefault();
                    if (window.showToast) {
                        window.showToast('Mohon lengkapi ' + Object.keys(errors).length + ' field yang belum diisi.', 'error');
                    }
                    const firstKey = Object.keys(errors)[0];
                    const el = document.querySelector('#artikel-form [data-error-key="' + firstKey + '"]');
                    if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
                    return;
                }

                this.submitting = true;
            },
        };
    }
</script>
@endpush
@endsection
