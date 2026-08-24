@extends('errors.layout')

@section('title', 'Sesi berakhir')
@section('code', '419')
@section('eyebrow', 'Sesi perlu diperbarui')
@section('heading', 'Sesi Anda telah berakhir')
@section('action_url', url()->current())
@section('action_label', 'Muat Ulang Halaman')

@section('content')
    Demi keamanan, sesi tidak aktif telah berakhir. Muat ulang halaman ini, lalu ulangi tindakan Anda.
@endsection
