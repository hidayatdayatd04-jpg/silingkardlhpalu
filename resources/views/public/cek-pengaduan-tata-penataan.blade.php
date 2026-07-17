@extends('layouts.app')

@section('title', 'Cek Status Pengaduan - Bidang Tata Penataan DLH Kota Palu')
@section('description', 'Lacak status pengaduan tata penataan menggunakan nomor tiket atau nomor HP.')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero
        badge="{{ __('Bidang Tata Penataan') }}"
        title="{{ __('Cek Status Pengaduan') }}"
        description="{{ __('Masukkan nomor tiket atau nomor HP untuk melihat status pengaduan Anda.') }}"
    />

    <livewire:public.cek-pengaduan-tata-penataan />
</div>
@endsection
