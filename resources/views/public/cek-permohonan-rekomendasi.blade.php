@extends('layouts.app')

@section('title', 'Cek Status Permohonan - Bidang Pengendalian DLH Kota Palu')
@section('description', 'Lacak status permohonan/rekomendasi lingkungan menggunakan nomor tiket atau email.')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero title="{{ __('Cek Status Permohonan') }}" description="{{ __('Masukkan nomor tiket atau email untuk melihat status permohonan.') }}" />

    <livewire:public.cek-permohonan-rekomendasi />
</div>
@endsection
