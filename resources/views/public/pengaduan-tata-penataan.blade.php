@extends('layouts.app')

@section('title', 'Pengaduan Tata Penataan - DLH Kota Palu')
@section('description', 'Formulir pengaduan masyarakat terkait limbah, polusi udara (debu/asap), kebisingan, dan bau di Kota Palu.')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero
        badge="{{ __('Bidang Tata Penataan') }}"
        title="{{ __('Pengaduan Masyarakat') }}"
        description="{{ __('Laporkan pencemaran limbah, polusi udara (debu/asap), kebisingan, atau bau dari aktivitas industri dan usaha di Kota Palu.') }}"
    />

    <livewire:public.pengaduan-tata-penataan />
</div>
@endsection
@push('scripts')
{{-- Task 5: form pengaduan-tata-penataan lazy-load peta via ensureMaplibreLoaded --}}
@endpush
