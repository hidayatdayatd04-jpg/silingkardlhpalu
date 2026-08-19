@extends('layouts.app')

@section('title', 'Cek Status Penyewaan Taman - DLH Kota Palu')
@section('description', 'Lacak status permohonan penyewaan taman menggunakan nomor tiket atau nomor telepon.')

@section('content')
<div class="public-service-page max-w-3xl mx-auto space-y-6 md:space-y-8">
    <x-public.page-hero badge="{{ __('Bidang RTH') }}" title="{{ __('Cek Status Penyewaan Taman') }}" description="{{ __('Masukkan nomor tiket atau nomor telepon untuk melihat status penyewaan taman.') }}" icon="search" />

    <x-public.ticket-search-guide class="reveal" />

    <div class="reveal">
        <livewire:public.cek-pinjam-taman />
    </div>
</div>
@endsection
