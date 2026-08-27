@extends('layouts.admin')

@section('title', ($record->exists ? 'Edit ' : 'Tambah ').'Artikel · SILINGKAR DLH ADMIN')
@section('heading', $resource['label'])

@php
    $isEdit = $record->exists;
    $artikelStatusOptions = \App\Enums\ArtikelStatus::options();
    $selectedStatus = old('status', $record->status?->value ?? '');
    $selectedTanggal = old('tanggal_publish', $record->tanggal_publish?->format('Y-m-d'));
    $initialType = $isEdit ? ($record->isExternal() ? 'external' : 'internal') : old('article_type', 'internal');
    $initialExternalUrl = old('external_url', $record->external_url);
    $initialPreviewTitle = $record->isExternal() ? $record->judul : null;
    $initialPreviewImage = $record->isExternal() ? $record->external_thumbnail_url : null;
@endphp

@section('content')
<div class="mx-auto max-w-6xl pb-24"
    x-data="artikelForm({
        isEdit: @js($isEdit),
        initialType: @js($initialType),
        hasExistingThumb: @js((bool) ($record->thumbnail ?? null)),
        initialExternalUrl: @js($initialExternalUrl),
        initialPreviewTitle: @js($initialPreviewTitle),
        initialPreviewImage: @js($initialPreviewImage),
        previewUrl: @js(route('admin.artikel.metadata.preview')),
    })"
    x-on:submit="handleSubmit($event)">

    <form id="artikel-form" method="POST" action="{{ $action }}" enctype="multipart/form-data" novalidate>
        @csrf
        @if ($method !== 'POST') @method($method) @endif
        <input type="hidden" name="article_type" :value="articleType">

        <div class="space-y-7">
            <x-admin.page-header
                :title="$isEdit ? 'Edit Artikel' : 'Tambah Artikel Baru'"
                subtitle="Pilih menulis artikel DLH atau menampilkan link berita dari website lain."
                :icon="$isEdit ? 'edit' : 'plus'"
                :breadcrumbs="[
                    ['label' => $resource['label'], 'url' => route('admin.resources.index', $resource['slug'])],
                    ['label' => $isEdit ? 'Edit' : 'Tambah Baru'],
                ]" />

            @if($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/30 dark:text-rose-300" role="alert">
                    <p class="font-bold">Artikel belum dapat disimpan.</p>
                    <p class="mt-1">{{ $errors->first() }}</p>
                </div>
            @endif

            <section class="rounded-2xl border border-slate-200 bg-white p-2 shadow-sm dark:border-slate-700 dark:bg-slate-900" aria-label="Pilih mode artikel">
                <div class="grid grid-cols-2 gap-2" role="tablist">
                    <button type="button" role="tab" :aria-selected="articleType === 'internal'" @click="selectType('internal')"
                        :class="articleType === 'internal' ? 'bg-brand-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'"
                        class="flex min-h-12 items-center justify-center gap-2 rounded-xl px-4 text-sm font-bold transition">
                        <x-admin.icon name="edit" :size="17" /> Tulis Artikel
                    </button>
                    <button type="button" role="tab" :aria-selected="articleType === 'external'" @click="selectType('external')"
                        :class="articleType === 'external' ? 'bg-sky-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'"
                        class="flex min-h-12 items-center justify-center gap-2 rounded-xl px-4 text-sm font-bold transition">
                        <x-admin.icon name="external-link" :size="17" /> Insert Link
                    </button>
                </div>
                @if($isEdit)
                    <p class="px-3 pb-2 pt-3 text-center text-xs text-slate-500 dark:text-slate-400">Jenis artikel dikunci saat edit untuk menjaga konsistensi data.</p>
                @endif
            </section>

            <div class="grid grid-cols-1 gap-7 lg:grid-cols-3">
                <div class="min-w-0 space-y-7 lg:col-span-2">
                    @if(!$isEdit || $record->isInternal())
                    <div x-show="articleType === 'internal'" x-cloak class="space-y-7">
                        <x-admin.section-card title="Informasi Artikel" icon="file-text" number="1" subtitle="Judul dan gambar unggulan artikel DLH.">
                            <div class="space-y-7">
                                <div data-error-key="judul">
                                    <x-admin.form-input id="artikel_judul" name="judul" label="Judul Artikel" required
                                        :value="old('judul', $record->judul)" placeholder="Masukkan judul artikel" :error="$errors->first('judul')" />
                                    <p x-show="errors.judul" x-text="errors.judul" class="mt-2 text-xs font-semibold text-rose-600"></p>
                                </div>
                                <div data-error-key="thumbnail">
                                    <x-admin.file-upload name="thumbnail" label="Gambar Utama"
                                        accept="image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif"
                                        :required="!$record->thumbnail" :current-file="$record->thumbnail"
                                        hint="Gambar utama artikel. Maksimal 5MB." :error="$errors->first('thumbnail')" />
                                    <p x-show="errors.thumbnail" x-text="errors.thumbnail" class="mt-2 text-xs font-semibold text-rose-600"></p>
                                </div>
                            </div>
                        </x-admin.section-card>

                        <x-admin.section-card title="Konten Artikel" icon="news" number="2" subtitle="Tulis isi artikel yang tampil di halaman publik.">
                            <div data-error-key="konten">
                                <x-admin.jodit-editor id="artikel_konten" name="konten" label="Konten Artikel" required
                                    :value="old('konten', $record->konten)" :error="$errors->first('konten')" />
                                <p x-show="errors.konten" x-text="errors.konten" class="mt-2 text-xs font-semibold text-rose-600"></p>
                            </div>
                        </x-admin.section-card>
                    </div>
                    @endif

                    @if(!$isEdit || $record->isExternal())
                    <div x-show="articleType === 'external'" x-cloak>
                        <x-admin.section-card title="Link Berita Eksternal" icon="external-link" number="1" subtitle="Judul dan thumbnail diambil otomatis dari metadata website sumber.">
                            <div class="space-y-5">
                                <div data-error-key="external_url">
                                    <label for="external_url" class="mb-2 block text-sm font-bold text-slate-800 dark:text-slate-200">Link Berita <span class="text-rose-500">*</span></label>
                                    <div class="flex flex-col gap-3 sm:flex-row">
                                        <input id="external_url" name="external_url" type="url" x-model="externalUrl" @input="onExternalUrlInput"
                                            placeholder="https://contoh.com/berita/..."
                                            class="min-h-11 min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 outline-none transition focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                                        <button type="button" @click="fetchMetadata" :disabled="fetching || !externalUrl.trim()"
                                            class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 text-sm font-bold text-white transition hover:bg-sky-700 disabled:pointer-events-none disabled:opacity-50">
                                            <svg x-show="fetching" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                            <x-admin.icon name="refresh" :size="16" />
                                            <span x-text="fetching ? 'Mengambil…' : (previewTitle ? 'Ambil Ulang Data' : 'Ambil Data Berita')"></span>
                                        </button>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Sistem hanya menyimpan URL thumbnail dari sumber; gambar tidak disalin ke storage/B2.</p>
                                    <p x-show="errors.external_url" x-text="errors.external_url" class="mt-2 text-xs font-semibold text-rose-600"></p>
                                </div>

                                <div x-show="previewError" x-text="previewError" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 dark:border-rose-900 dark:bg-rose-950/30 dark:text-rose-300" role="alert"></div>

                                <div x-show="previewTitle && previewImage" x-cloak class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50">
                                    <div class="grid gap-5 p-4 sm:grid-cols-[12rem_1fr]">
                                        <img :src="previewImage" :alt="previewTitle" class="aspect-video w-full rounded-xl bg-slate-200 object-cover sm:aspect-[4/3]" />
                                        <div class="min-w-0 self-center">
                                            <p class="text-xs font-bold uppercase tracking-wide text-sky-600 dark:text-sky-400">Preview metadata</p>
                                            <h3 x-text="previewTitle" class="mt-2 text-lg font-extrabold leading-snug text-slate-900 dark:text-white"></h3>
                                            <p class="mt-3 break-all text-xs text-slate-500 dark:text-slate-400">Sumber: <span x-text="externalUrl"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-admin.section-card>
                    </div>
                    @endif
                </div>

                <div class="space-y-7 lg:sticky lg:top-24 lg:self-start">
                    <x-admin.section-card title="Pengaturan Publikasi" icon="send" number="3" subtitle="Tanggal tayang dan status artikel.">
                        <div class="space-y-7">
                            <div data-error-key="tanggal_publish">
                                <x-admin.date-field id="artikel_tanggal" name="tanggal_publish" type="date" label="Tanggal Tayang" required
                                    :value="$selectedTanggal" :error="$errors->first('tanggal_publish')" />
                                <p x-show="errors.tanggal_publish" x-text="errors.tanggal_publish" class="mt-2 text-xs font-semibold text-rose-600"></p>
                            </div>
                            <div data-error-key="status">
                                <x-admin.select name="status" label="Status" required :options="$artikelStatusOptions"
                                    :selected="$selectedStatus" placeholder="Pilih status" :error="$errors->first('status')" />
                                <p x-show="errors.status" x-text="errors.status" class="mt-2 text-xs font-semibold text-rose-600"></p>
                            </div>
                            @if(!$isEdit || $record->isInternal())
                            <div x-show="articleType === 'internal'" x-cloak data-error-key="komentar_enabled">
                                <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 dark:border-slate-700 dark:bg-slate-800/40"
                                    x-data="{ enabled: {{ old('komentar_enabled', $record->komentar_enabled ?? true) ? 'true' : 'false' }} }">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">Izinkan Komentar</p>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Komentar hanya tersedia untuk artikel DLH.</p>
                                        </div>
                                        <input type="hidden" name="komentar_enabled" :value="enabled ? '1' : '0'" />
                                        <button type="button" @click="enabled = !enabled" class="spring-toggle" :class="{ 'is-on': enabled }" role="switch" :aria-checked="enabled ? 'true' : 'false'">
                                            <span class="spring-track"></span><span class="spring-thumb"><x-admin.icon name="check" :size="14" /></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </x-admin.section-card>
                </div>
            </div>
        </div>

        <div class="fixed inset-x-4 bottom-4 z-40 flex items-center justify-end gap-2 rounded-2xl border border-slate-200 bg-white/95 p-2 shadow-[0_14px_32px_-16px_rgba(15,23,42,0.35)] backdrop-blur dark:border-slate-700 dark:bg-slate-900/95 sm:left-auto sm:right-6">
            <a href="{{ route('admin.resources.index', $resource['slug']) }}" class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200"><x-admin.icon name="x" :size="16" /> Batal</a>
            <button type="submit" :disabled="submitting" class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-brand-700 px-4 text-sm font-semibold text-white hover:bg-brand-800 disabled:pointer-events-none disabled:opacity-60">
                <svg x-show="submitting" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <x-admin.icon name="device-floppy" :size="16" />
                <span x-text="submitting ? 'Menyimpan…' : @js($isEdit ? 'Perbarui Artikel' : 'Simpan Artikel')"></span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function artikelForm(config) {
        return {
            isEdit: !!config.isEdit,
            articleType: config.initialType || 'internal',
            hasExistingThumb: !!config.hasExistingThumb,
            externalUrl: config.initialExternalUrl || '',
            previewTitle: config.initialPreviewTitle || '',
            previewImage: config.initialPreviewImage || '',
            previewError: '', previewUrl: config.previewUrl,
            fetching: false, submitting: false, errors: {},

            selectType(type) {
                if (!this.isEdit && ['internal', 'external'].includes(type)) { this.articleType = type; this.errors = {}; }
            },
            onExternalUrlInput() {
                this.previewError = ''; this.errors.external_url = '';
                if (!this.isEdit || this.externalUrl !== config.initialExternalUrl) { this.previewTitle = ''; this.previewImage = ''; }
            },
            async fetchMetadata() {
                if (this.fetching || !this.externalUrl.trim()) return;
                this.fetching = true; this.previewError = ''; this.errors.external_url = '';
                try {
                    const response = await fetch(this.previewUrl, {
                        method: 'POST', credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('#artikel-form [name="_token"]').value },
                        body: JSON.stringify({ external_url: this.externalUrl.trim() }),
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'Data berita tidak dapat diambil.');
                    this.previewTitle = data.title || ''; this.previewImage = data.image_url || '';
                } catch (error) {
                    this.previewTitle = ''; this.previewImage = ''; this.previewError = error.message || 'Data berita tidak dapat diambil.';
                } finally { this.fetching = false; }
            },
            syncKonten() {
                if (this.articleType !== 'internal') return;
                const textarea = document.getElementById('artikel_konten'); const editor = window['jodit_artikel_konten'];
                if (textarea && editor && typeof editor.value !== 'undefined') textarea.value = editor.value;
            },
            validate() {
                const errors = {};
                if (this.articleType === 'external') {
                    if (!this.externalUrl.trim()) errors.external_url = 'Link berita wajib diisi.';
                } else {
                    const title = (document.querySelector('#artikel-form [name="judul"]')?.value || '').trim();
                    if (!title) errors.judul = 'Judul artikel wajib diisi.';
                    const input = document.querySelector('#artikel-form input[name="thumbnail"]');
                    if (!(input?.files?.length) && !this.hasExistingThumb) errors.thumbnail = 'Gambar utama wajib diunggah.';
                    const html = (document.getElementById('artikel_konten')?.value || '').trim();
                    const plain = html.replace(/<[^>]*>/g, ' ').replace(/&nbsp;/gi, ' ').replace(/\s+/g, ' ').trim();
                    if (!plain && !/<(img|video|table|iframe)\b/i.test(html)) errors.konten = 'Konten artikel wajib diisi.';
                }
                if (!(document.querySelector('#artikel-form [name="tanggal_publish"]')?.value || '').trim()) errors.tanggal_publish = 'Tanggal tayang wajib dipilih.';
                if (!(document.querySelector('#artikel-form [name="status"]')?.value || '').trim()) errors.status = 'Status wajib dipilih.';
                this.errors = errors; return Object.keys(errors).length === 0;
            },
            handleSubmit(event) {
                if (this.submitting) { event.preventDefault(); return; }
                this.syncKonten();
                if (!this.validate()) {
                    event.preventDefault();
                    document.querySelector('[data-error-key="'+Object.keys(this.errors)[0]+'"]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                this.submitting = true;
            },
        };
    }
</script>
@endpush
@endsection
