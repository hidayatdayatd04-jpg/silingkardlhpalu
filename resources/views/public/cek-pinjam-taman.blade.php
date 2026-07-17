@extends('layouts.app')

@section('title', 'Cek Status Peminjaman Taman - DLH Kota Palu')
@section('description', 'Lacak status permohonan peminjaman taman menggunakan nomor tiket atau nama pemohon.')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero badge="{{ __('Bidang RTH') }}" title="{{ __('Cek Status Peminjaman Taman') }}" description="{{ __('Masukkan nomor tiket atau nama pemohon untuk melihat status peminjaman taman.') }}" />

    <livewire:public.cek-pinjam-taman />
</div>
@endsection
