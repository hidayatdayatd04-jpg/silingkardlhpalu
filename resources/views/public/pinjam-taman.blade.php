@extends('layouts.app')

@section('title', 'Pinjam Pakai Taman - DLH Kota Palu')

@section('content')
<div class="space-y-6">
    <x-public.page-hero badge="{{ __('Bidang RTH') }}" title="{{ __('Permohonan Pinjam Pakai Taman') }}" description="{{ __('Ajukan peminjaman taman kota untuk kegiatan komunitas.') }}" />
    <livewire:public.pinjam-taman />
</div>
@endsection
