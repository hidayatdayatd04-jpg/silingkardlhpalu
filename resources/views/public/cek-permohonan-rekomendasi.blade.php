@extends('layouts.app')

@section('title', 'Cek Status Permohonan - Bidang Pengendalian DLH Kota Palu')
@section('description', 'Lacak status permohonan/rekomendasi lingkungan menggunakan nomor tiket atau email.')

@section('content')
<div class="public-service-page max-w-3xl mx-auto space-y-6 md:space-y-8">
    <x-public.page-hero badge="{{ __('Pelacakan Pengendalian') }}" title="{{ __('Cek Status Permohonan') }}" description="{{ __('Masukkan nomor tiket atau email untuk melihat status permohonan.') }}" icon="search" />

    <x-public.ticket-search-guide class="reveal" />

    <div class="reveal">
        <livewire:public.cek-permohonan-rekomendasi />
    </div>
</div>
@endsection
