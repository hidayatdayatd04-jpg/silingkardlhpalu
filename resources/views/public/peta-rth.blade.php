@extends('layouts.app')

@section('title', 'Peta Ruang Terbuka Hijau - DLH Kota Palu')
@section('description', 'Peta interaktif Ruang Terbuka Hijau (RTH) di Kota Palu.')

@section('content')
<div class="space-y-6">
    <x-public.page-hero badge="{{ __('Bidang RTH') }}" title="{{ __('Peta Ruang Terbuka Hijau') }}" description="{{ __('Jelajahi data RTH Kota Palu dengan layer toggle interaktif.') }}" />
    <livewire:public.peta-rth />
</div>
@endsection
