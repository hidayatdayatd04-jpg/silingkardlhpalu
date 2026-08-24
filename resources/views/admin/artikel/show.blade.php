@extends('layouts.admin')

@section('title', ($record->judul ?? 'Artikel').' · SILINGKAR DLH ADMIN')
@section('heading', $resource['label'])

    @php
        use App\Enums\ArtikelStatus;

        $statusEnum = $record->status instanceof ArtikelStatus ? $record->status : null;
        $statusLabel = $statusEnum?->label() ?? '—';

        $kontenBersih = \App\Support\HtmlSanitizer::clean((string) $record->konten);
        do {
            $kontenPrev = $kontenBersih;
            $kontenBersih = preg_replace('/<(p|div|span)[^>]*>(?:\s|&nbsp;|<br\s*\/?>)*<\/\1>/i', '', $kontenBersih);
        } while ($kontenBersih !== $kontenPrev);
        $kontenBersih = trim($kontenBersih);
        $kontenKosong = trim(strip_tags($kontenBersih)) === '' && ! preg_match('/<(img|video|table|iframe)\b/i', $kontenBersih);

        $thumbUrl = null;
        if ($record->thumbnail) {
            try {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($record->thumbnail)) {
                    $thumbUrl = Storage::disk('public')->temporaryUrl($record->thumbnail, now()->addHours(24));
                } else {
                    $thumbUrl = asset('storage/' . $record->thumbnail);
                }
            } catch (\Throwable $e) {
                try {
                    $thumbUrl = Storage::url($record->thumbnail);
                } catch (\Throwable $e2) {
                    $thumbUrl = null;
                }
            }
        }

        $publicUrl = $record->status === ArtikelStatus::PUBLISHED && $record->slug
            ? url('/berita/'.$record->slug)
            : null;
    @endphp

@section('content')
<div class="mx-auto max-w-6xl pb-28" x-data x-init="window.staggerReveal($el.querySelectorAll('.stagger-item'), 80)">
    <div class="space-y-7">
        {{-- Header + breadcrumb navigasi --}}
        <x-admin.page-header
            class="stagger-item"
            :title="$record->judul"
            subtitle="Detail artikel dan informasi publikasi."
            icon="file-text"
            :breadcrumbs="[
                ['label' => $resource['label'], 'url' => route('admin.resources.index', $resource['slug'])],
                ['label' => 'Detail'],
            ]"
        >
            <x-slot:actions>
                <a href="{{ route('admin.resources.edit', [$resource['slug'], $record]) }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:from-emerald-600 hover:to-emerald-700">
                    <x-admin.icon name="edit" :size="16" />
                    Edit Artikel
                </a>
                @if ($publicUrl)
                    <a href="{{ $publicUrl }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-300 hover:text-emerald-700">
                        <x-admin.icon name="globe" :size="16" />
                        Lihat di Situs
                    </a>
                @endif
            </x-slot:actions>
        </x-admin.page-header>

        {{-- Hero --}}
        <div class="stagger-item card-lift relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[var(--shadow-lift)]">
            <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500"></div>
            <div class="flex flex-col gap-7 p-6 sm:p-8 lg:flex-row lg:items-center">
                <div class="shrink-0">
                    @if ($thumbUrl)
                        <img src="{{ $thumbUrl }}" alt="{{ $record->judul }}"
                            class="h-52 w-full rounded-2xl border border-slate-200 object-cover shadow-md ring-1 ring-black/5 lg:w-72" />
                    @else
                        <div class="grid h-52 w-full place-items-center rounded-2xl bg-gradient-to-br from-emerald-500/10 to-teal-500/10 ring-1 ring-emerald-500/20 lg:w-72">
                            <x-admin.icon name="image" :size="52" class="text-emerald-500/60" />
                        </div>
                    @endif
                </div>

                <div class="min-w-0 flex-1">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        @if ($statusEnum === ArtikelStatus::PUBLISHED)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                <span class="dot-pulse relative inline-block size-2 rounded-full bg-emerald-500"></span>
                                {{ $statusLabel }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
                                <span class="dot-pulse relative inline-block size-2 rounded-full bg-amber-500"></span>
                                {{ $statusLabel }}
                            </span>
                        @endif
                    </div>

                    <h1 class="font-display text-2xl font-bold leading-snug text-slate-900 sm:text-3xl">{{ $record->judul }}</h1>

                    <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-500">
                        @if ($record->user)
                            <span class="inline-flex items-center gap-1.5">
                                <x-admin.icon name="user" :size="16" class="text-emerald-600" />
                                {{ $record->user->name }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5">
                            <x-admin.icon name="clock" :size="16" class="text-emerald-600" />
                            Diperbarui {{ $record->updated_at->translatedFormat('d M Y H:i') }}
                        </span>
                    </div>

                    {{-- Kartu tanggal publish --}}
                    @if ($record->tanggal_publish)
                        <div class="mt-6 inline-flex items-center gap-4 rounded-2xl border border-emerald-200/70 bg-gradient-to-r from-emerald-50 to-teal-50/70 px-5 py-3.5 shadow-[var(--shadow-soft)]">
                            <div class="grid w-14 shrink-0 place-items-center rounded-xl bg-white py-2 text-center shadow-sm ring-1 ring-emerald-200/70">
                                <span class="block text-[10px] font-semibold uppercase tracking-wider text-emerald-600">{{ $record->tanggal_publish->translatedFormat('M') }}</span>
                                <span class="block font-display text-lg font-bold leading-none text-slate-900">{{ $record->tanggal_publish->format('d') }}</span>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700/70">Tanggal Tayang</p>
                                <p class="font-display text-base font-bold text-slate-900">{{ $record->tanggal_publish->translatedFormat('l, d F Y') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-7 lg:grid-cols-3">
            {{-- Konten --}}
            <div class="min-w-0 space-y-7 lg:col-span-2">
                <x-admin.section-card class="stagger-item" title="Konten Artikel" icon="file-text" subtitle="Teks artikel yang ditampilkan di halaman publik.">
                    @if ($kontenKosong)
                        <div class="flex flex-col items-center gap-2 rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-6 py-10 text-center">
                            <x-admin.icon name="file-text" :size="32" class="text-slate-300" />
                            <p class="text-sm text-slate-400">Konten artikel belum diisi.</p>
                        </div>
                    @else
                        <article class="font-sans text-[15px] leading-8 text-slate-700
                            [&_p]:mb-5 [&_h1]:mt-9 [&_h1]:mb-3 [&_h1]:text-2xl [&_h1]:font-bold [&_h1]:text-slate-900 [&_h1]:tracking-tight
                            [&_h2]:mt-9 [&_h2]:mb-3 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-slate-900 [&_h2]:tracking-tight
                            [&_h3]:mt-7 [&_h3]:mb-2 [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-slate-900
                            [&_h4]:mt-6 [&_h4]:mb-2 [&_h4]:text-lg [&_h4]:font-bold [&_h4]:text-slate-900
                            [&_ul]:my-5 [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:space-y-2 [&_ol]:my-5 [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:space-y-2
                            [&_li]:mb-1
                            [&_a]:font-semibold [&_a]:text-emerald-600 [&_a]:underline [&_a]:decoration-emerald-500/30 [&_a]:underline-offset-2
                            [&_img]:my-6 [&_img]:rounded-2xl [&_img]:shadow-lg [&_img]:max-w-full [&_img]:h-auto
                            [&_blockquote]:my-6 [&_blockquote]:border-l-4 [&_blockquote]:border-emerald-500 [&_blockquote]:bg-emerald-50/50 [&_blockquote]:rounded-r-xl [&_blockquote]:px-5 [&_blockquote]:py-3 [&_blockquote]:italic
                            [&_strong]:text-slate-900 [&_em]:italic
                            [&_pre]:my-6 [&_pre]:rounded-xl [&_pre]:bg-slate-900 [&_pre]:p-5 [&_pre]:overflow-x-auto [&_pre]:text-sm [&_pre]:text-slate-100
                            [&_code]:font-mono [&_code]:text-sm [&_code]:bg-slate-100 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded
                            [&_pre_code]:bg-transparent [&_pre_code]:p-0 [&_pre_code]:text-inherit
                            [&_table]:my-6 [&_table]:w-full [&_table]:overflow-hidden [&_table]:rounded-xl [&_table]:border [&_table]:border-slate-200
                            [&_thead]:bg-slate-50
                            [&_th]:px-4 [&_th]:py-3 [&_th]:text-left [&_th]:text-sm [&_th]:font-bold [&_th]:text-slate-900 [&_th]:border-b [&_th]:border-slate-200
                            [&_td]:px-4 [&_td]:py-3 [&_td]:text-sm [&_td]:border-b [&_td]:border-slate-100
                            [&_tbody_tr:last-child_td]:border-b-0
                            [&_hr]:my-8 [&_hr]:border-0 [&_hr]:h-px [&_hr]:bg-slate-200
                            [&_figure]:my-6 [&_figure]:rounded-2xl [&_figure]:overflow-hidden
                            [&_figcaption]:mt-2 [&_figcaption]:text-center [&_figcaption]:text-sm [&_figcaption]:text-slate-500
                            [&_sub]:text-xs [&_sub]:align-sub [&_sup]:text-xs [&_sup]:align-super">
                            {!! $kontenBersih !!}
                        </article>
                    @endif
                </x-admin.section-card>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-7">
                <x-admin.section-card class="stagger-item" title="Gambar Utama & File" icon="image" subtitle="Gambar utama artikel.">
                    @if ($record->thumbnail)
                        <x-admin.file-preview :path="$record->thumbnail" :label="$record->judul" :resource="$resource['slug']" />
                    @else
                        <div class="flex flex-col items-center gap-2 rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-6 py-8 text-center">
                            <x-admin.icon name="image" :size="30" class="text-slate-300" />
                            <p class="text-sm text-slate-400">Belum ada thumbnail.</p>
                        </div>
                    @endif
                </x-admin.section-card>

                <x-admin.section-card class="stagger-item" title="Informasi Publikasi" icon="send">
                    <dl class="divide-y divide-slate-100">
                        <div class="flex items-center justify-between gap-3 py-3">
                            <dt class="text-sm text-slate-500">Status</dt>
                            <dd>
                                @if ($statusEnum === ArtikelStatus::PUBLISHED)
                                    <x-admin.status-pill variant="success" :label="$statusLabel" pulse />
                                @else
                                    <x-admin.status-pill variant="warning" :label="$statusLabel" />
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-3">
                            <dt class="text-sm text-slate-500">Tanggal Tayang</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ $record->tanggal_publish?->translatedFormat('d M Y') ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-3">
                            <dt class="text-sm text-slate-500">Penulis</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ $record->user?->name ?? '—' }}</dd>
                        </div>
                    </dl>
                </x-admin.section-card>

                <x-admin.section-card class="stagger-item" title="Informasi Sistem" icon="info-circle">
                    <dl class="divide-y divide-slate-100">
                        <div class="flex items-center justify-between gap-3 py-3">
                            <dt class="text-sm text-slate-500">ID</dt>
                            <dd class="text-sm font-semibold text-slate-800">#{{ $record->getKey() }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-3">
                            <dt class="text-sm text-slate-500">Slug</dt>
                            <dd class="max-w-[12rem] truncate text-sm font-semibold text-slate-800" title="{{ $record->slug }}">{{ $record->slug ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-3">
                            <dt class="text-sm text-slate-500">Dibuat</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ $record->created_at?->translatedFormat('d M Y H:i') ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-3">
                            <dt class="text-sm text-slate-500">Diperbarui</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ $record->updated_at?->translatedFormat('d M Y H:i') ?? '—' }}</dd>
                        </div>
                    </dl>
                </x-admin.section-card>

                <x-admin.section-card class="stagger-item" title="Zona Berbahaya" icon="alert-triangle">
                    <p class="mb-4 text-sm text-slate-500">Menghapus artikel akan menghilangkan data secara permanen dan tidak dapat dikembalikan.</p>
                    <x-admin.button variant="danger" class="w-full justify-center" x-data="" x-on:click="$dispatch('open-modal', 'artikel-delete')">
                        <x-admin.icon name="trash" :size="16" />
                        Hapus Artikel
                    </x-admin.button>
                </x-admin.section-card>
            </div>
        </div>
    </div>
</div>

<x-admin.confirm-delete
    name="artikel-delete"
    :action="route('admin.resources.destroy', [$resource['slug'], $record])"
    :title="'Hapus Artikel?'"
    :message="'Artikel &quot;'.e($record->judul).'&quot; akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.'"
    :confirm-text="'Ya, Hapus Artikel'" />
@endsection
