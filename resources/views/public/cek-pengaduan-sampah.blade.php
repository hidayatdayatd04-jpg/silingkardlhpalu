@extends('layouts.app')

@section('title', 'Cek Pengaduan Sampah - DLH Kota Palu')

@section('content')
<div class="space-y-6">
    <x-public.page-hero badge="{{ __('Sampah & LB3') }}" title="{{ __('Cek Status Pengaduan Sampah') }}" description="{{ __('Masukkan nomor tiket atau nomor HP untuk melihat status pengaduan persampahan.') }}" />
    <livewire:public.cek-pengaduan-sampah />
</div>
@endsection
