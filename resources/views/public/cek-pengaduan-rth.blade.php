@extends('layouts.app')

@section('title', 'Cek Status Pengaduan RTH - DLH Kota Palu')

@section('content')
<div class="space-y-6">
    <x-public.page-hero badge="{{ __('Bidang RTH') }}" title="{{ __('Cek Status Pengaduan RTH') }}" description="{{ __('Masukkan nomor tiket atau nomor HP untuk melihat status pengaduan.') }}" />
    <livewire:public.cek-pengaduan-rth />
</div>
@endsection
