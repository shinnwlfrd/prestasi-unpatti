@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Admin</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Selamat datang, {{ auth()->user()->name }}</p>
        </div>
        <a href="{{ route('admin.achievements.dashboard') }}" 
            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Monitoring Prestasi
        </a>
    </div>

    <!-- Active Period Info -->
    @if($activePeriod)
    <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-purple-100 dark:bg-purple-900/40 rounded-lg">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-purple-900 dark:text-purple-100">Periode Akademik Aktif:</p>
                <p class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $activePeriod->name }}</p>
            </div>
            <div class="ml-auto text-right">
                <p class="text-xs text-purple-600 dark:text-purple-400">{{ $activePeriod->start_date->format('d M Y') }} - {{ $activePeriod->end_date->format('d M Y') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Students -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Mahasiswa</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($stats['students']) }}</p>
            <a href="{{ route('admin.students') }}" class="text-blue-600 dark:text-blue-400 text-xs mt-2 inline-flex items-center gap-1 hover:gap-2 transition-all">
                Lihat semua
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Total Achievements -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Prestasi</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($stats['achievements']) }}</p>
            <a href="{{ route('admin.achievements.validation.index') }}" class="text-purple-600 dark:text-purple-400 text-xs mt-2 inline-flex items-center gap-1 hover:gap-2 transition-all">
                Lihat semua
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Validators -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Validator Aktif</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($stats['validators']) }}</p>
            <a href="{{ route('admin.users') }}" class="text-emerald-600 dark:text-emerald-400 text-xs mt-2 inline-flex items-center gap-1 hover:gap-2 transition-all">
                Kelola users
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Pending -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Menunggu Validasi</p>
            <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-2">{{ number_format($stats['pending']) }}</p>
            <a href="{{ route('admin.achievements.validation.index', ['status' => 'pending']) }}" class="text-yellow-600 dark:text-yellow-400 text-xs mt-2 inline-flex items-center gap-1 hover:gap-2 transition-all">
                Review sekarang
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Status Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-yellow-600 dark:text-yellow-400 font-medium">Menunggu</p>
                    <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-300 mt-1">{{ $statusStats['menunggu'] }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/40 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-green-600 dark:text-green-400 font-medium">Disetujui</p>
                    <p class="text-2xl font-bold text-green-700 dark:text-green-300 mt-1">{{ $statusStats['disetujui'] }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/40 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-red-600 dark:text-red-400 font-medium">Ditolak</p>
                    <p class="text-2xl font-bold text-red-700 dark:text-red-300 mt-1">{{ $statusStats['ditolak'] }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/40 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Revisi</p>
                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300 mt-1">{{ $statusStats['revisi'] }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Urgent Alerts & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Urgent Pending Alerts -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Perlu Perhatian Segera
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pending > 7 hari</p>
                </div>
                @if($urgentPending->isNotEmpty())
                    <span class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-xs font-bold rounded-full">
                        {{ $urgentPending->count() }}
                    </span>
                @endif
            </div>
            @if($urgentPending->isEmpty())
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-green-300 dark:text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Tidak ada prestasi yang urgent</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($urgentPending as $alert)
                    <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $alert->event_name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $alert->student?->name }} • {{ $alert->submitted_at->diffInDays() }} hari</p>
                        </div>
                        <a href="{{ route('admin.achievements.validation.show', $alert) }}" 
                            class="ml-3 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg font-medium transition-colors">
                            Review
                        </a>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Recent Validations -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        Aktivitas Terbaru
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">5 validasi terakhir</p>
                </div>
            </div>
            @if($recentValidations->isEmpty())
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Belum ada aktivitas</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($recentValidations as $log)
                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $log->new_status === 'Disetujui' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }}">
                            @if($log->new_status === 'Disetujui')
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $log->studentAchievement->event_name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $log->validator->name }} • {{ $log->validated_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Achievements Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Prestasi Terbaru</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">10 pengajuan prestasi terakhir</p>
                </div>
                <a href="{{ route('admin.achievements.validation.index') }}" 
                    class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400 font-medium flex items-center gap-1">
                    Lihat Semua
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Prestasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recentAchievements as $achievement)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-purple-600 dark:text-purple-400 font-medium">{{ substr($achievement->student?->name ?? 'N', 0, 1) }}</span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $achievement->student?->name ?? '-' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $achievement->student_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($achievement->event_name, 40) }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $achievement->achievement->category->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-medium {{ $achievement->level === 'Internasional' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : ($achievement->level === 'Nasional' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400') }}">
                                {{ $achievement->level }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColor = match($achievement->validation_status) {
                                    'Disetujui' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'Ditolak' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    'Revisi' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    default => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $achievement->validation_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $achievement->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400 mt-4">Belum ada prestasi</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
