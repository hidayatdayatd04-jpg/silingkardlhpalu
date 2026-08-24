@extends('errors.layout')

@section('title', 'Akses tidak diizinkan')
@section('code', '403')
@section('eyebrow', 'Akses dibatasi')
@section('heading', 'Anda tidak memiliki izin untuk membuka halaman ini')

@section('content')
    Halaman ini hanya tersedia untuk pengguna yang memiliki wewenang. Silakan kembali ke halaman utama atau masuk dengan akun yang sesuai.
@endsection
