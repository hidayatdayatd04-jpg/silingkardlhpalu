@extends('layouts.app')

@section('title', 'Penyewaan Taman - DLH Kota Palu')

@section('content')
<div class="space-y-6">
    <x-public.page-hero badge="{{ __('Bidang RTH') }}" title="{{ __('Permohonan Penyewaan Taman') }}" description="{{ __('Ajukan penyewaan taman kota untuk kegiatan komunitas.') }}" />
    <livewire:public.pinjam-taman />
</div>
@endsection
