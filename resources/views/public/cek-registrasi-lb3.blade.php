@extends('layouts.app')

@section('title', 'Cek Status Registrasi LB3 - DLH Kota Palu')
@section('description', 'Lacak status registrasi usaha LB3 menggunakan nomor registrasi atau nama perusahaan.')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero badge="{{ __('Sampah & LB3') }}" title="{{ __('Cek Status Registrasi LB3') }}" description="{{ __('Masukkan nomor registrasi atau nama perusahaan untuk melihat status registrasi usaha LB3.') }}" />

    <livewire:public.cek-registrasi-lb3 />
</div>
@endsection
