@extends('layouts.app')

@section('title', 'Berita - DLH Kota Palu')

@section('content')
<div class="space-y-10 pb-8">
    <x-public.page-hero badge="{{ __('Informasi Publik') }}" icon="document" title="{{ __('Berita & Artikel') }}" description="{{ __('Informasi terbaru, kegiatan, dan pengumuman dari Dinas Lingkungan Hidup Kota Palu.') }}" />

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($artikels as $artikel)
            <a href="{{ $artikel->publicUrl() }}"
                @if($artikel->isExternal()) target="_blank" rel="noopener noreferrer" @endif
                class="reveal group overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white shadow-[0_3px_14px_-8px_rgba(15,23,42,0.12)] transition-[border-color,box-shadow,transform] duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-[0_24px_46px_-24px_rgba(15,23,42,0.3)] dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800">
                <div class="relative h-52 overflow-hidden bg-gradient-to-br from-brand-700 via-brand-600 to-bay-600">
                    @if ($artikel->thumbnailUrl())
                        <img src="{{ $artikel->thumbnailUrl() }}" alt="{{ $artikel->judul }}" class="size-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_80%_10%,rgba(255,255,255,.3),transparent_24%),linear-gradient(135deg,rgba(255,255,255,.08),transparent)]"></div>
                        <span class="absolute bottom-5 left-5 grid size-12 place-items-center rounded-2xl border border-white/20 bg-white/15 text-white backdrop-blur-sm">
                            <x-icons.ui name="document" class="size-6" />
                        </span>
                    @endif
                </div>
                <div class="space-y-3 p-6">
                    <h2 class="line-clamp-2 text-lg font-extrabold leading-7 tracking-[-0.02em] text-slate-900 dark:text-slate-100">{{ $artikel->judul }}</h2>
                    <p class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                        <x-icons.ui name="calendar" class="size-4 text-brand-600 dark:text-brand-400" />
                        {{ $artikel->tanggal_publish?->translatedFormat('d F Y') }}
                    </p>
                </div>
            </a>
        @empty
            <section class="col-span-full rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center dark:border-slate-800 dark:bg-slate-900 sm:px-10" aria-labelledby="empty-news-title">
                <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-900/25 dark:text-brand-400">
                    <x-icons.ui name="document" class="size-7" />
                </div>
                <h2 id="empty-news-title" class="mt-4 text-lg font-bold text-slate-900 dark:text-white">{{ __('Belum ada berita dipublikasikan') }}</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Informasi dan kegiatan terbaru DLH Kota Palu akan tampil di halaman ini setelah dipublikasikan.') }}</p>
            </section>
        @endforelse
    </div>

    <div class="flex justify-center">{{ $artikels->withQueryString()->links() }}</div>
</div>
@endsection
