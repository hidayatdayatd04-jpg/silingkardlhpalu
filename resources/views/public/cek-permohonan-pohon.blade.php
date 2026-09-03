@extends('layouts.app')

@section('title', 'Cek Status Permohonan Penebangan & Pemangkasan Pohon - DLH Kota Palu')
@section('description', 'Lacak status permohonan penebangan atau pemangkasan pohon di fasilitas umum menggunakan nomor tiket atau nomor WhatsApp.')

@section('content')
<div class="public-service-page max-w-4xl mx-auto space-y-6 md:space-y-8">
    <x-public.page-hero
        badge="{{ __('Bidang Ruang Terbuka Hijau (RTH)') }}"
        title="{{ __('Cek Status Permohonan Pohon') }}"
        description="{{ __('Masukkan nomor tiket (contoh: PHN-XXXX-XXXX) atau nomor WhatsApp yang Anda daftarkan untuk memantau status survei dan pelaksanaan tindakan.') }}"
        icon="search"
    />

    <x-public.ticket-search-guide class="reveal" />

    <div class="reveal">
        <livewire:public.cek-permohonan-pohon />
    </div>
</div>
@endsection
