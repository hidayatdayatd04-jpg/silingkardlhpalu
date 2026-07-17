@extends('layouts.app')

@section('title', 'Cek Status Permohonan - Bidang Pengendalian DLH Kota Palu')
@section('description', 'Lacak riwayat permohonan/rekomendasi lingkungan menggunakan email atau nomor telepon pemohon.')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero title="{{ __('Cek Status Permohonan') }}" description="{{ __('Masukkan email atau nomor telepon untuk melihat riwayat semua permohonan Anda.') }}" />

    <livewire:public.cek-permohonan-rekomendasi />
</div>
@endsection
