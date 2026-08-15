@extends('layouts.admin')

@section('title', 'Bantuan - Admin DLH')
@section('heading', 'Bantuan')

@php
    $colorMap = [
        'brand'  => 'bg-brand-50 text-brand-600',
        'emerald'=> 'bg-emerald-50 text-emerald-600',
        'blue'   => 'bg-blue-50 text-blue-600',
        'amber'  => 'bg-amber-50 text-amber-600',
        'purple' => 'bg-purple-50 text-purple-600',
        'teal'   => 'bg-teal-50 text-teal-600',
        'rose'   => 'bg-rose-50 text-rose-600',
        'slate'  => 'bg-slate-100 text-slate-600',
    ];
    $accentMap = [
        'brand'  => 'from-brand-50 to-brand-100/50 border-brand-200/60',
        'emerald'=> 'from-emerald-50 to-emerald-100/50 border-emerald-200/60',
        'blue'   => 'from-blue-50 to-blue-100/50 border-blue-200/60',
        'amber'  => 'from-amber-50 to-amber-100/50 border-amber-200/60',
        'purple' => 'from-purple-50 to-purple-100/50 border-purple-200/60',
        'teal'   => 'from-teal-50 to-teal-100/50 border-teal-200/60',
        'rose'   => 'from-rose-50 to-rose-100/50 border-rose-200/60',
        'slate'  => 'from-slate-50 to-slate-100/50 border-slate-200/60',
    ];
@endphp

@section('content')
    {{-- Hero Header --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-600 via-brand-500 to-emerald-500 p-6 text-white shadow-xl sm:p-8">
        <div class="bg-grain pointer-events-none absolute inset-0 opacity-[0.04]"></div>
        <div class="pointer-events-none absolute -right-20 -top-20 size-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-16 left-1/3 size-48 rounded-full bg-emerald-400/20 blur-3xl"></div>

        <div class="relative flex flex-col items-center gap-4 text-center sm:flex-row sm:text-left">
            <div class="grid size-14 shrink-0 place-items-center rounded-2xl bg-white/15 backdrop-blur-sm">
                <x-admin.icon name="info-circle" :size="26" />
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Pusat Bantuan</h1>
                <p class="mt-1 text-sm text-white/75">Panduan lengkap penggunaan panel admin DLH Kota Palu.</p>
            </div>
        </div>
    </div>

    <div x-data="{ q: '' }" class="mt-6">
        {{-- Search --}}
        <div class="relative mb-6 max-w-lg">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                <x-admin.icon name="search" class="text-slate-400" :size="18" />
            </div>
            <input x-model="q" type="search" placeholder="Cari pertanyaan atau topik..."
                class="h-12 w-full rounded-xl border border-slate-200 bg-white py-2 pl-11 pr-4 text-sm shadow-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
            <button x-show="q !== ''" @click="q = ''" x-transition
                class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600">
                <x-admin.icon name="x" :size="16" />
            </button>
        </div>

        {{-- Quick Stats --}}
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @php
                $totalQ = collect($sections)->sum(fn($s) => count($s['items']));
            @endphp
            <div class="rounded-xl border border-white/80 bg-white p-4 shadow-sm">
                <div class="text-2xl font-bold text-ink-900">{{ count($sections) }}</div>
                <div class="text-xs font-medium text-slate-500">Kategori</div>
            </div>
            <div class="rounded-xl border border-white/80 bg-white p-4 shadow-sm">
                <div class="text-2xl font-bold text-ink-900">{{ $totalQ }}</div>
                <div class="text-xs font-medium text-slate-500">Pertanyaan</div>
            </div>
            <div class="rounded-xl border border-white/80 bg-white p-4 shadow-sm">
                <div class="text-2xl font-bold text-ink-900">24/7</div>
                <div class="text-xs font-medium text-slate-500">Tersedia</div>
            </div>
            <div class="rounded-xl border border-white/80 bg-white p-4 shadow-sm">
                <div class="text-2xl font-bold text-emerald-600">Online</div>
                <div class="text-xs font-medium text-slate-500">Status Sistem</div>
            </div>
        </div>

        {{-- Sections Grid --}}
        <div class="grid gap-5 lg:grid-cols-2">
            @foreach($sections as $section)
                <div x-show="q === '' || {{ \Illuminate\Support\Js::from(collect($section['items'])->map(fn($i) => strtolower($i['q'].' '.$i['a']))->implode(' | ')) }}.includes(q.toLowerCase())"
                    class="rounded-2xl border border-white/80 bg-white p-5 shadow-[0_12px_40px_rgba(15,23,42,0.06)] transition hover:shadow-[0_18px_60px_rgba(15,23,42,0.1)]">

                    <div class="mb-4 flex items-center gap-3">
                        <div class="grid size-10 place-items-center rounded-xl {{ $colorMap[$section['color']] ?? 'bg-brand-50 text-brand-600' }}">
                            <x-admin.icon :name="$section['icon']" :size="20" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-ink-900">{{ $section['title'] }}</h2>
                            <p class="text-xs text-slate-400">{{ count($section['items']) }} pertanyaan</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        @foreach($section['items'] as $item)
                            <div x-data="{ open: false }"
                                x-show="q === '' || {{ \Illuminate\Support\Js::from(strtolower($item['q'].' '.$item['a'])) }}.includes(q.toLowerCase())"
                                class="rounded-xl border border-slate-100 transition hover:border-slate-200 hover:bg-slate-50/50">
                                <button type="button" x-on:click="open = !open"
                                    class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left">
                                    <span class="text-sm font-semibold text-ink-800">{{ $item['q'] }}</span>
                                    <x-admin.icon name="chevron-down" :size="16"
                                        class="shrink-0 text-slate-400 transition duration-300"
                                        x-bind:class="{ 'rotate-180': open }" />
                                </button>
                                <div x-show="open" x-collapse x-cloak>
                                    <div class="border-t border-slate-100 px-4 py-3">
                                        <p class="text-sm leading-relaxed text-slate-600">{{ $item['a'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Empty State --}}
        <div x-show="q !== '' && document.querySelectorAll('[x-show]').length === 0" class="hidden">
            <div class="rounded-2xl border border-white/80 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto grid size-16 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                    <x-admin.icon name="search" :size="28" />
                </div>
                <h3 class="mt-4 text-lg font-bold text-ink-900">Tidak ada hasil ditemukan</h3>
                <p class="mt-1 text-sm text-slate-500">Coba kata kunci lain atau hubungi admin.</p>
            </div>
        </div>

        {{-- Contact Card --}}
        <div class="mt-6 overflow-hidden rounded-2xl border border-white/80 bg-white shadow-[0_12px_40px_rgba(15,23,42,0.06)]">
            <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <div class="grid size-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-brand-100 to-emerald-100 text-brand-600">
                        <x-admin.icon name="mail" :size="22" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-ink-900">Butuh bantuan lebih lanjut?</h3>
                        <p class="mt-1 text-sm text-slate-500">Hubungi administrator sistem atau Kepala Bidang untuk masalah akses dan teknis.</p>
                    </div>
                </div>
                <x-admin.button variant="primary" icon="mail" href="mailto:{{ \App\Models\Setting::get('contact_email', 'dlh@palukota.go.id') }}" class="shrink-0 rounded-xl px-5">
                    Kontak Admin
                </x-admin.button>
            </div>
        </div>
    </div>
@endsection
