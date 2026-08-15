@extends('layouts.app')

@section('title', 'Pengajuan RINTEK/PERTEK - Sampah & LB3 DLH Kota Palu')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-public.page-hero badge="{{ __('Sampah & LB3') }}" title="{{ __('Pengajuan RINTEK / PERTEK') }}" description="{{ __('Ajukan rekomendasi teknis dan persetujuan teknis pengelolaan lingkungan beserta kelengkapan dokumen.') }}" />
    <livewire:public.pengajuan-rintek-pertek />
</div>
@endsection
