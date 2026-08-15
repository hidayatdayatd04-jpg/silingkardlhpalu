@extends('layouts.app')

@section('title', 'Peta Objek Pengawasan - DLH Kota Palu')
@section('description', 'Peta interaktif objek pengawasan lingkungan hidup di Kota Palu beserta status dokumen perusahaan.')

@section('content')
<div class="space-y-6">
    <x-public.page-hero
        badge="{{ __('Bidang Tata Penataan') }}"
        title="{{ __('Peta Objek Pengawasan') }}"
        description="{{ __('Jelajahi lokasi perusahaan/industri yang diawasi DLH Kota Palu beserta status dokumen lingkungan.') }}"
    />

    <livewire:public.peta-objek-pengawasan />
</div>
@endsection
@push('scripts')
{{-- Task 5: peta-objek-pengawasan (dlhPetaObjekPengawasan) butuh map-bundle --}}
@vite('resources/js/map-bundle.js')
@endpush
