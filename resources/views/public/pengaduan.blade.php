@extends('layouts.app')

@section('title', 'Pengaduan Masyarakat - DLH Kota Palu')
@section('description', 'Formulir pengaduan masyarakat terpadu untuk semua bidang: Pengendalian, Sampah & LB3, Tata Penataan, dan RTH.')
@section('full_width', '')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero
        badge="{{ __('Informasi Layanan Public') }}"
        title="{{ __('Pengaduan Masyarakat') }}"
        description="{{ __('Pilih bidang terkait dan sampaikan pengaduan Anda melalui formulir di bawah ini.') }}"
    />

    <livewire:public.pengaduan-unified />
</div>
@endsection
