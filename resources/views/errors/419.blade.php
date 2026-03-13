@extends('errors.layout')

@section('title', 'Sesi Berakhir')
@section('code', '419')

@section('icon')
    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
@endsection

@section('message_title', 'Sesi Telah Berakhir.')
@section('message_description', 'Halaman ini telah kedaluwarsa karena Anda terlalu lama tidak melakukan aktifitas. Silakan muat ulang halaman dan coba lagi.')
