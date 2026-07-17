@extends('layouts.app')

@section('title', 'Cek Status Pengaduan - Bidang Pengendalian DLH Kota Palu')
@section('description', 'Lacak status pengaduan masyarakat bidang pengendalian dampak lingkungan menggunakan nomor tiket atau nomor HP.')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero title="{{ __('Cek Status Pengaduan') }}" description="{{ __('Masukkan nomor tiket atau nomor HP untuk melihat status pengaduan Anda.') }}" />

    <livewire:public.cek-pengaduan-pengendalian />
</div>
@endsection
