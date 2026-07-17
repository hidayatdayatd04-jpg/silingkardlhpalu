@extends('layouts.app')

@section('title', 'Cek Status Rintek/Pertek - Bidang Pengendalian DLH Kota Palu')
@section('description', 'Lacak status pengajuan Rintek/Pertek bidang pengendalian dampak lingkungan menggunakan nomor pengajuan atau nama perusahaan.')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-public.page-hero badge="{{ __('Bidang Pengendalian') }}" title="{{ __('Cek Status RINTEK/PERTEK') }}" description="{{ __('Masukkan nomor pengajuan atau nama perusahaan untuk melihat status pengajuan.') }}" />

    <livewire:public.cek-rintek-pertek />
</div>
@endsection
