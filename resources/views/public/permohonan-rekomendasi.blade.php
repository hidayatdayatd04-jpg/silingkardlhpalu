@extends('layouts.app')

@section('title', 'Permohonan/Rekomendasi - Bidang Pengendalian DLH Kota Palu')
@section('description', 'Formulir permohonan dan rekomendasi lingkungan untuk pelaku usaha di Kota Palu.')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero badge="{{ __('Bidang Pengendalian') }}" title="{{ __('Permohonan/Rekomendasi') }}" description="{{ __('Ajukan permohonan rekomendasi lingkungan dengan melengkapi data perusahaan dan dokumen pendukung.') }}" />

    <livewire:public.permohonan-rekomendasi />
</div>
@endsection
