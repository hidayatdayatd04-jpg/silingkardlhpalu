@extends('layouts.app')

@section('title', 'Cek Status Perizinan Tebang Pohon - DLH Kota Palu')
@section('description', 'Lacak status permohonan perizinan tebang pohon menggunakan nomor tiket atau nama pemohon.')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero badge="{{ __('Bidang RTH') }}" title="{{ __('Cek Status Perizinan Tebang') }}" description="{{ __('Masukkan nomor tiket atau nama pemohon untuk melihat status permohonan.') }}" />

    <livewire:public.cek-perizinan-tebang-pohon />
</div>
@endsection
