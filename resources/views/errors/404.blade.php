@extends('errors.layout')

@php
    $exceptionMessage = trim((string) ($exception?->getMessage() ?? ''));
    $safeMessage = (str_starts_with($exceptionMessage, 'File ') || str_starts_with($exceptionMessage, 'Gagal mengambil file'))
        ? $exceptionMessage
        : 'Halaman atau berkas yang Anda cari tidak ditemukan.';
@endphp

@section('title', 'Halaman tidak ditemukan')
@section('code', '404')
@section('eyebrow', 'Kesalahan navigasi')
@section('heading', 'Halaman tidak ditemukan')

@section('content')
    {{ $safeMessage }} Periksa kembali alamat yang dibuka atau kembali ke halaman utama.
@endsection
