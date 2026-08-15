@extends('layouts.app')

@section('title', 'Cek Status Penyewaan Taman - DLH Kota Palu')
@section('description', 'Lacak status permohonan penyewaan taman menggunakan nomor tiket atau nama pemohon.')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero badge="{{ __('Bidang RTH') }}" title="{{ __('Cek Status Penyewaan Taman') }}" description="{{ __('Masukkan nomor tiket atau nama pemohon untuk melihat status penyewaan taman.') }}" />

    <livewire:public.cek-pinjam-taman />
</div>
@endsection
