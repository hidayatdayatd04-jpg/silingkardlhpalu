@extends('layouts.app')

@section('title', 'Pengaduan RTH - DLH Kota Palu')
@section('description', 'Sampaikan pengaduan terkait ruang terbuka hijau, taman, dan pohon pelindung di Kota Palu.')

@section('content')
<div class="space-y-6">
    <x-public.page-hero
        badge="{{ __('Bidang RTH') }}"
        title="{{ __('Pengaduan Ruang Terbuka Hijau') }}"
        description="{{ __('Laporkan permasalahan RTH tanpa perlu mendaftar akun.') }}"
    />
    <livewire:public.pengaduan-rth />
</div>
@endsection
@push('scripts')
{{-- Task 5: form pengaduan-rth memakai peta MapLibre — muat map-bundle --}}
@vite('resources/js/map-bundle.js')
@endpush
