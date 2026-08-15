@extends('layouts.app')

@section('title', 'Pengaduan Sampah - DLH Kota Palu')
@section('description', 'Laporkan masalah sampah menumpuk, armada tidak lewat, atau sampah tidak diangkut di Kota Palu.')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero badge="{{ __('Sampah & LB3') }}" title="{{ __('Pengaduan Persampahan') }}" description="{{ __('Sampaikan keluhan terkait pengelolaan sampah di wilayah Kota Palu.') }}" />
    <livewire:public.pengaduan-sampah />
</div>
@endsection
@push('scripts')
{{-- Task 5: form pengaduan-sampah memakai peta MapLibre — muat map-bundle --}}
@vite('resources/js/map-bundle.js')
@endpush
