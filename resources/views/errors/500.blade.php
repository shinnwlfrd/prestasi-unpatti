@extends('errors.layout')

@section('title', 'Kesalahan Server')
@section('code', '500')

@section('icon')
    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
    </svg>
@endsection

@section('message_title', 'Terjadi Kesalahan.')
@section('message_description', 'Oops! Server kami sedang mengalami kendala teknis. Tim kami telah diberitahu dan sedang berusaha memperbaikinya secepat mungkin.')
