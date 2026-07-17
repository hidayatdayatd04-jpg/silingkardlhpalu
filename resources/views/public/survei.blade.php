@extends('layouts.app')

@section('title', 'Survei IKM - DLH Kota Palu')
@section('description', 'Berikan penilaian Anda terhadap kualitas pelayanan pengelolaan pohon pelindung Dinas Lingkungan Hidup Kota Palu melalui survei IKM.')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-public.page-hero title="{{ __('Survei Kepuasan Masyarakat (IKM)') }}" description="{{ __('Bantu kami meningkatkan kualitas pelayanan dengan mengisi survei kepuasan.') }}" />
    <livewire:public.survei-ikm />
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/@fingerprintjs/fingerprintjs@4/dist/fp.min.js"></script>
@endsection
