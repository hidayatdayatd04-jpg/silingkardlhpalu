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
            <p class="col-span-full text-center text-slate-500">{{ __('Belum ada berita.') }}</p>
        @endforelse
    </div>

    <div class="flex justify-center">{{ $artikels->withQueryString()->links() }}</div>
</div>
@endsection
