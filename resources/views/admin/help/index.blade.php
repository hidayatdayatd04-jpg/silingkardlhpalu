@extends('layouts.admin')

@section('title', 'Bantuan - Admin DLH')
@section('heading', 'Bantuan')

@section('content')
    <x-admin.page-header
        title="Pusat Bantuan"
        subtitle="Panduan penggunaan panel admin DLH Kota Palu."
        icon="info-circle"
    />

    <div x-data="{ q: '' }">
        <div class="mb-6">
            <div class="relative max-w-md">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <x-admin.icon name="search" class="text-slate-400" :size="16" />
                </div>
                <input x-model="q" type="search" placeholder="Cari pertanyaan..."
                    class="h-10 w-full rounded-pill border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            @foreach($sections as $section)
                <x-admin.card
                    x-show="q === '' || {{ \Illuminate\Support\Js::from(collect($section['items'])->map(fn($i) => strtolower($i['q'].' '.$i['a']))->implode(' | ')) }}.includes(q.toLowerCase())">
                    <div class="mb-4 flex items-center gap-2.5">
                        <div class="grid size-9 place-items-center rounded-lg bg-brand-50 text-brand-600">
                            <x-admin.icon :name="$section['icon']" :size="18" />
                        </div>
                        <h2 class="text-h4 font-bold text-ink-900">{{ $section['title'] }}</h2>
                    </div>
                    <div class="space-y-2">
                        @foreach($section['items'] as $item)
                            <div x-data="{ open: false }" class="rounded-lg border border-slate-200"
                                x-show="q === '' || {{ \Illuminate\Support\Js::from(strtolower($item['q'].' '.$item['a'])) }}.includes(q.toLowerCase())">
                                <button type="button" x-on:click="open = !open"
                                    class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left">
                                    <span class="text-sm font-semibold text-ink-800">{{ $item['q'] }}</span>
                                    <x-admin.icon name="chevron-down" :size="16" class="shrink-0 text-slate-400 transition" x-bind:class="{ 'rotate-180': open }" />
                                </button>
                                <div x-show="open" x-collapse x-cloak>
                                    <p class="border-t border-slate-100 px-4 py-3 text-sm leading-relaxed text-slate-600">{{ $item['a'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-admin.card>
            @endforeach
        </div>

        <x-admin.card class="mt-6">
            <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-h4 font-bold text-ink-900">Butuh bantuan lebih lanjut?</h3>
                    <p class="mt-1 text-sm text-slate-500">Hubungi administrator sistem atau Kepala Bidang untuk masalah akses dan teknis.</p>
                </div>
                <x-admin.button variant="primary" icon="mail" href="mailto:{{ \App\Models\Setting::get('contact_email', 'dlh@palukota.go.id') }}">
                    Kontak Admin
                </x-admin.button>
            </div>
        </x-admin.card>
    </div>
@endsection
