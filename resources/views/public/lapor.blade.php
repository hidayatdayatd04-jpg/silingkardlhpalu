@extends('layouts.app')

@section('title', 'Lapor Pohon - DLH Kota Palu')
@section('description', 'Laporkan kondisi pohon pelindung yang rawan tumbang, tumbang, atau mengganggu jaringan utilitas publik di Kota Palu.')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="text-center md:text-left space-y-2">
        <h1 class="text-3xl font-extrabold tracking-tight">{{ __('Formulir Laporan Kondisi Pohon') }}</h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __('Laporkan pohon tumbang, rawan tumbang, atau dahan pohon pelindung yang mengganggu jaringan kabel/jalan.') }}</p>
    </div>
    
    <livewire:public.lapor-pohon />
</div>
@endsection
