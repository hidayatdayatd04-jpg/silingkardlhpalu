@extends('layouts.app')

@section('title', 'Cek Status Registrasi LB3 - DLH Kota Palu')
@section('description', 'Lacak status registrasi usaha LB3 menggunakan nomor registrasi atau nomor telepon.')

@section('content')
<div class="public-service-page max-w-3xl mx-auto space-y-6 md:space-y-8">
    <x-public.page-hero badge="{{ __('Sampah & LB3') }}" title="{{ __('Cek Status Registrasi LB3') }}" description="{{ __('Masukkan nomor registrasi atau nomor telepon untuk melihat status registrasi usaha LB3.') }}" icon="search" />

    <x-public.ticket-search-guide class="reveal" />

    <div class="reveal">
        <livewire:public.cek-registrasi-lb3 />
    </div>
</div>
@endsection
