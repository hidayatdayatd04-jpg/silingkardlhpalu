@extends('layouts.app')

@section('title', 'Cek Status Rintek/Pertek - Sampah & LB3 DLH Kota Palu')
@section('description', 'Lacak status pengajuan RINTEK/PERTEK bidang Sampah & LB3 menggunakan nomor pengajuan atau nomor telepon.')

@section('content')
<div class="public-service-page max-w-4xl mx-auto space-y-6 md:space-y-8">
    <x-public.page-hero badge="{{ __('Sampah & LB3') }}" title="{{ __('Cek Status RINTEK/PERTEK') }}" description="{{ __('Masukkan nomor pengajuan atau nomor telepon untuk melihat status pengajuan.') }}" icon="search" />

    <x-public.ticket-search-guide class="reveal" />

    <div class="reveal">
        <livewire:public.cek-rintek-pertek />
    </div>
</div>
@endsection
