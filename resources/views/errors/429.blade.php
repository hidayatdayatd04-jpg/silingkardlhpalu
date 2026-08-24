@extends('errors.layout')

@section('title', 'Terlalu banyak permintaan')
@section('code', '429')
@section('eyebrow', 'Permintaan dibatasi sementara')
@section('heading', 'Tunggu sebentar sebelum mencoba lagi')

@section('content')
    Sistem menerima terlalu banyak permintaan dalam waktu singkat. Silakan tunggu beberapa saat, kemudian coba kembali.
@endsection
