@extends('layouts.app')

@section('title', 'Registrasi Usaha LB3 - DLH Kota Palu')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero badge="{{ __('Sampah & LB3') }}" title="{{ __('Registrasi Pelaku Usaha LB3') }}" description="{{ __('Daftarkan perusahaan/pelaku usaha pengelola limbah B3 untuk mendapatkan nomor registrasi resmi.') }}" />
    <livewire:public.registrasi-usaha-lb3 />
</div>
@endsection
