@extends('layouts.app')

@section('title', 'Profil Saya')
@section('subtitle', 'Pengaturan Akun')

@php
    $user = auth()->user();
    $userName = $user->name ?? 'User';
@endphp

@section('content')
<div class="max-w-3xl mx-auto space-y-6 animate-fade-in">
    <!-- Back Button -->
    <div>
        @php
            $backRoute = match(auth()->user()->role ?? 'Validator') {
                'Admin' => route('admin.dashboard'),
                'Validator' => route('validator.dashboard'),
                default => url()->previous() != url()->current() ? url()->previous() : '/'
            };
        @endphp
        <a href="{{ $backRoute }}" 
            class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>
    <!-- Profile Card -->
    <x-card>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <div class="relative">
                <img src="{{ $user->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff&size=128' }}"
                    alt="Foto Profil"
                    class="w-24 h-24 rounded-2xl object-cover border-4 border-indigo-100 dark:border-indigo-900/50 shadow-lg">
                @if($user->last_login_method === 'sso')
                    <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center border-4 border-white dark:border-slate-800" title="Login via SSO">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                @endif
            </div>
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $user->name }}</h2>
                <p class="text-indigo-600 dark:text-indigo-400 font-medium">{{ $user->email }}</p>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                        {{ $user->role }}
                    </span>
                    @if($user->is_active)
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                            Aktif
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                            Nonaktif
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </x-card>

    <!-- Authentication Info -->
    <x-card title="Metode Autentikasi">
        <div class="space-y-4">
            <!-- Local Auth Status -->
            <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800 dark:text-white">Login Lokal</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Email & Password</p>
                    </div>
                </div>
                @if($user->password)
                    <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Aktif
                    </span>
                @else
                    <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400">
                        Tidak Tersedia
                    </span>
                @endif
            </div>

            <!-- SSO Status -->
            <div class="flex items-center justify-between p-4 rounded-xl {{ $user->provider ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600' }} border">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg {{ $user->provider ? 'bg-blue-200 dark:bg-blue-800' : 'bg-gray-200 dark:bg-gray-600' }} flex items-center justify-center">
                        <svg class="w-5 h-5 {{ $user->provider ? 'text-blue-600 dark:text-blue-300' : 'text-gray-600 dark:text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800 dark:text-white">SSO SIAKAD</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($user->provider)
                                Terhubung sejak {{ $user->linked_at?->format('d M Y') ?? 'N/A' }}
                            @else
                                Single Sign-On
                            @endif
                        </p>
                    </div>
                </div>
                @if($user->provider)
                    <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Terhubung
                    </span>
                @elseif(config('sso.gates.sso.enabled'))
                    <a href="{{ route('sso.redirect') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-500 hover:bg-blue-600 text-white transition-colors">
                        Hubungkan
                    </a>
                @else
                    <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400">
                        Tidak Tersedia
                    </span>
                @endif
            </div>
        </div>
    </x-card>

    <!-- Login Activity -->
    <x-card title="Aktivitas Login Terakhir">
        <div class="space-y-3">
            @if($user->last_login_at)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $user->last_login_method === 'sso' ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-gray-100 dark:bg-gray-600' }} flex items-center justify-center">
                            @if($user->last_login_method === 'sso')
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $user->last_login_method === 'sso' ? 'Login via SSO' : 'Login Lokal' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $user->last_login_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $user->last_login_at->diffForHumans() }}
                    </span>
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Belum ada aktivitas login tercatat.</p>
            @endif
        </div>
    </x-card>

    <!-- SSO Migration Notice -->
    @if(config('sso.migration.deadline') && !$user->provider)
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold text-amber-800 dark:text-amber-300">Migrasi ke SSO</h4>
                    <p class="text-sm text-amber-600 dark:text-amber-400 mt-1">
                        Sistem akan beralih ke SSO penuh pada {{ \Carbon\Carbon::parse(config('sso.migration.deadline'))->format('d M Y') }}. 
                        Silakan hubungkan akun Anda dengan SSO SIAKAD sebelum tanggal tersebut.
                    </p>
                    <a href="{{ route('sso.redirect') }}" class="inline-flex items-center gap-1 mt-2 text-sm font-medium text-amber-700 dark:text-amber-300 hover:underline">
                        Hubungkan Sekarang
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
