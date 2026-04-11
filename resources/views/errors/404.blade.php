@extends('errors.layout')

@section('title', 'Halaman Tidak Ditemukan')
@section('code', '404')

@section('icon')
    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
    </svg>
@endsection

@section('message_title', 'Halaman Hilang?')
@section('message_description', 'Kami tidak dapat menemukan halaman yang Anda cari. Mungkin tautannya salah atau halaman tersebut telah dipindahkan ke alamat lain.')
