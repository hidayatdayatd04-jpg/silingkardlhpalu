@extends('layouts.app')

@section('title', 'Pengaduan Masyarakat - Bidang Pengendalian DLH Kota Palu')
@section('description', 'Formulir pengaduan masyarakat terkait pembakaran sampah, limbah B3, banjir, dan longsor di Kota Palu.')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero
        badge="{{ __('Bidang Pengendalian') }}"
        title="{{ __('Pengaduan Masyarakat') }}"
        description="{{ __('Laporkan pembakaran sampah, limbah B3, banjir, atau longsor di Kota Palu.') }}"
    />

    <livewire:public.pengaduan-pengendalian />
</div>
@endsection
