@extends('layouts.app')

@section('title', 'Berita - DLH Kota Palu')

@section('content')
<div class="space-y-8">
    <x-public.page-hero title="{{ __('Berita & Artikel') }}" description="{{ __('Informasi terbaru dari Dinas Lingkungan Hidup Kota Palu.') }}" />

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($artikels as $artikel)
            <a href="/berita/{{ $artikel->slug }}" class="group bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden hover:shadow-lg transition">
                @if ($artikel->thumbnail)
                    <img src="{{ Storage::disk('public')->temporaryUrl($artikel->thumbnail, now()->addHours(24)) }}" alt="{{ $artikel->judul }}" class="h-48 w-full object-cover group-hover:scale-105 transition duration-300">
                @else
                    <div class="h-48 bg-gradient-to-br from-brand-500 to-emerald-400"></div>
                @endif
                <div class="p-5 space-y-2">
                    <h2 class="font-bold text-lg dark:text-slate-100 line-clamp-2">{{ $artikel->judul }}</h2>
                    <p class="text-xs text-slate-500">{{ $artikel->tanggal_publish?->format('d M Y') }}</p>
                </div>
            </a>
        @empty
            <section class="col-span-full rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center dark:border-slate-800 dark:bg-slate-900 sm:px-10" aria-labelledby="empty-news-title">
                <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-900/25 dark:text-brand-400">
                    <svg class="size-7" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8Z" />
                    </svg>
                </div>
                <h2 id="empty-news-title" class="mt-4 text-lg font-bold text-slate-900 dark:text-white">{{ __('Belum ada berita dipublikasikan') }}</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ __('Informasi dan kegiatan terbaru DLH Kota Palu akan tampil di halaman ini setelah dipublikasikan.') }}</p>
            </section>
        @endforelse
    </div>

    <div class="flex justify-center">{{ $artikels->withQueryString()->links() }}</div>
</div>
@endsection
