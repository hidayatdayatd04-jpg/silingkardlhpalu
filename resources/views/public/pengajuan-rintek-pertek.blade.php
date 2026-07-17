@extends('layouts.app')

@section('title', 'Pengajuan RINTEK/PERTEK - Bidang Pengendalian DLH Kota Palu')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-public.page-hero badge="{{ __('Bidang Pengendalian') }}" title="{{ __('Pengajuan RINTEK / PERTEK') }}" description="{{ __('Ajukan rekomendasi teknis dan persetujuan teknis pengelolaan lingkungan beserta kelengkapan dokumen.') }}" />
    <livewire:public.pengajuan-rintek-pertek />
</div>
@endsection
