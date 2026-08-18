@extends('layouts.app')

@section('title', 'Lacak Laporan - DLH Kota Palu')
@section('description', 'Masukkan nomor tiket atau nomor telepon aduan Anda untuk memantau status verifikasi dan tindak lanjut penanganan pohon oleh Dinas Lingkungan Hidup Kota Palu.')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero
        title="{{ __('Lacak Status Aduan') }}"
        description="{{ __('Masukkan nomor tiket atau nomor telepon untuk melihat status verifikasi dan tindak lanjut petugas.') }}"
    />
    
    <livewire:public.lacak-laporan />
</div>
@endsection
