@extends('layouts.app')

@section('title', 'Perizinan Tebang Pohon - DLH Kota Palu')

@section('content')
<div class="space-y-6">
    <x-public.page-hero badge="{{ __('Bidang RTH') }}" title="{{ __('Perizinan Penebangan Pohon') }}" description="{{ __('Ajukan permohonan izin tebang pohon secara online.') }}" />
    <livewire:public.perizinan-tebang-pohon />
</div>
@endsection
