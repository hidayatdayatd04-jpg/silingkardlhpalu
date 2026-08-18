@extends('layouts.app')

@section('title', 'Cek Status Rintek/Pertek - Sampah & LB3 DLH Kota Palu')
@section('description', 'Lacak status pengajuan RINTEK/PERTEK bidang Sampah & LB3 menggunakan nomor pengajuan atau email.')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-public.page-hero badge="{{ __('Sampah & LB3') }}" title="{{ __('Cek Status RINTEK/PERTEK') }}" description="{{ __('Masukkan nomor pengajuan atau email untuk melihat status pengajuan.') }}" />

    <livewire:public.cek-rintek-pertek />
</div>
@endsection
