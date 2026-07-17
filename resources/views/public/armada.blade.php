@extends('layouts.app')

@section('title', 'Pelacakan Armada Sampah - DLH Kota Palu')
@section('description', 'Pantau lokasi real-time armada truk sampah dan pickup Dinas Lingkungan Hidup Kota Palu yang sedang aktif beroperasi.')

@section('content')
<div class="space-y-6">
    <x-public.page-hero badge="{{ __('Informasi') }}" title="{{ __('Pelacakan Armada Real-Time') }}" description="{{ __('Pantau lokasi armada truk sampah dan pickup yang sedang beroperasi hari ini.') }}" />

    <livewire:public.tracking-armada />

    @php
        $jadwals = \App\Models\JadwalArmada::orderBy('hari')->orderBy('jam')->get();
    @endphp

    @if($jadwals->isNotEmpty())
    <section class="max-w-5xl mx-auto">
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100 mb-4">{{ __('Jadwal & Rute Armada') }}</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="text-left py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">{{ __('Hari') }}</th>
                            <th class="text-left py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">{{ __('Jam') }}</th>
                            <th class="text-left py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">{{ __('Nama Rute') }}</th>
                            <th class="text-left py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">{{ __('Wilayah Dilalui') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jadwals as $jadwal)
                        <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900">
                            <td class="py-3 px-4 font-medium text-slate-900 dark:text-slate-100">{{ $jadwal->hari }}</td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-400">{{ $jadwal->jam }}</td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-400">{{ $jadwal->nama_rute }}</td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-400">{{ $jadwal->wilayah_dilalui }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    @endif
</div>
@endsection
