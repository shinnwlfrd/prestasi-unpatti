@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
@php
    // Set default values for all variables to prevent undefined errors
    $stats = $stats ?? ['students' => 0, 'achievements' => 0, 'validators' => 0, 'approved' => 0];
    $statusStats = $statusStats ?? ['menunggu' => 0, 'disetujui' => 0, 'ditolak' => 0, 'revisi' => 0];
    $urgentPending = $urgentPending ?? collect();
    $recentValidations = $recentValidations ?? collect();
    $recentAchievements = $recentAchievements ?? collect();
    $topPerformers = $topPerformers ?? collect();
    $periods = $periods ?? collect();
    $selectedPeriod = $selectedPeriod ?? null;
    $periodComparison = $periodComparison ?? null;
    $activePeriod = $activePeriod ?? null;
    $statistics = $statistics ?? ['total' => 0, 'pending' => 0, 'approved' => 0, 'approval_rate' => 0, 'avg_time_to_approve' => 0];
    $monthlyTrend = $monthlyTrend ?? collect();
    $levelDistribution = $levelDistribution ?? ['Internasional' => 0, 'Nasional' => 0, 'Universitas' => 0];
    $facultyComparison = $facultyComparison ?? collect();
    $categoryDistribution = $categoryDistribution ?? collect();
@endphp
<div class="space-y-6">
    <!-- Header with Period Filter -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Admin</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Selamat datang, {{ auth()->user()->name }}</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Period Filter -->
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <select name="period" onchange="this.form.submit()" 
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                    <option value="all" {{ isset($periodComparison) && $periodComparison ? 'selected' : '' }}>Semua Periode</option>
                    @if(isset($periods))
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}" {{ isset($selectedPeriod) && $selectedPeriod && $selectedPeriod->id == $period->id ? 'selected' : '' }}>
                                {{ $period->name }} {{ $period->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </form>
        </div>
    </div>

    <!-- Active Period Info -->
    @isset($selectedPeriod)
    @if($selectedPeriod)
    <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-purple-100 dark:bg-purple-900/40 rounded-lg">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-purple-900 dark:text-purple-100">Periode Akademik {{ $selectedPeriod->is_active ? 'Aktif' : 'Terpilih' }}:</p>
                <p class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $selectedPeriod->name }}</p>
            </div>
            <div class="ml-auto text-right">
                <p class="text-xs text-purple-600 dark:text-purple-400">{{ $selectedPeriod->start_date->format('d M Y') }} - {{ $selectedPeriod->end_date->format('d M Y') }}</p>
            </div>
        </div>
    </div>
    @endif
    @endisset

    <!-- Period Comparison (only shown when "Semua Periode" selected) -->
    @isset($periodComparison)
    @if($periodComparison && $periodComparison->isNotEmpty())
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/40 rounded-lg">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-blue-900 dark:text-blue-100">Perbandingan Semua Periode</p>
                <p class="text-xs text-blue-600 dark:text-blue-400">Menampilkan data dari semua periode akademik</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-blue-100 dark:bg-blue-900/30">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-blue-900 dark:text-blue-100">Periode</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-blue-900 dark:text-blue-100">Total</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-blue-900 dark:text-blue-100">Disetujui</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-blue-900 dark:text-blue-100">Pending</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-blue-900 dark:text-blue-100">Ditolak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-200 dark:divide-blue-800">
                    @foreach($periodComparison as $comp)
                    <tr class="hover:bg-blue-100 dark:hover:bg-blue-900/20">
                        <td class="px-3 py-2 text-blue-900 dark:text-blue-100 font-medium">{{ $comp->period }}</td>
                        <td class="px-3 py-2 text-center text-blue-900 dark:text-blue-100">{{ $comp->total }}</td>
                        <td class="px-3 py-2 text-center">
                            <span class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-xs font-medium">
                                {{ $comp->approved }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center">
                            <span class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full text-xs font-medium">
                                {{ $comp->pending }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center">
                            <span class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-full text-xs font-medium">
                                {{ $comp->rejected }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endisset

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
            <a href="{{ route('admin.student-achievements') }}" class="text-purple-600 dark:text-purple-400 text-xs mt-2 inline-flex items-center gap-1 hover:gap-2 transition-all">
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

        <!-- Approved -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Disetujui</p>
            <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">{{ number_format($statusStats['disetujui']) }}</p>
            <a href="{{ route('admin.student-achievements') }}" class="text-green-600 dark:text-green-400 text-xs mt-2 inline-flex items-center gap-1 hover:gap-2 transition-all">
                Lihat semua
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Two-Stage Validation Queues -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- University Validation Queue -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                        </svg>
                        Antrian Validasi Universitas
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Disetujui fakultas, menunggu validasi universitas</p>
                </div>
                @if(isset($universityPending) && $universityPending->isNotEmpty())
                    <span class="px-2 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 text-xs font-bold rounded-full">
                        {{ $universityPending->count() }}
                    </span>
                @endif
            </div>
            @if(!isset($universityPending) || $universityPending->isEmpty())
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Tidak ada prestasi pending</p>
                </div>
            @else
                <div class="space-y-3 max-h-[400px] overflow-y-auto">
                    @foreach($universityPending as $achievement)
                    <div class="flex items-center justify-between p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $achievement->event_name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $achievement->student?->name }}</p>
                                <span class="text-xs text-gray-400">•</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $achievement->student?->faculty_name }}</p>
                            </div>
                            <p class="text-xs text-purple-600 dark:text-purple-400 mt-0.5">
                                Disetujui fakultas {{ $achievement->faculty_validated_at?->diffForHumans() }}
                            </p>
                        </div>
                        <a href="{{ route('admin.university.show', $achievement) }}" 
                            class="ml-3 px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded-lg font-medium transition-colors flex-shrink-0">
                            Validasi
                        </a>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.university.index') }}" 
                        class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400 font-medium flex items-center justify-center gap-1">
                        Lihat Semua Antrian Universitas
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            @endif
        </div>

        <!-- Appeal Queue -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Antrian Banding
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Banding dari mahasiswa yang perlu direview</p>
                </div>
                @if(isset($appealsPending) && $appealsPending->isNotEmpty())
                    <span class="px-2 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-bold rounded-full">
                        {{ $appealsPending->count() }}
                    </span>
                @endif
            </div>
            @if(!isset($appealsPending) || $appealsPending->isEmpty())
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Tidak ada banding pending</p>
                </div>
            @else
                <div class="space-y-3 max-h-[400px] overflow-y-auto">
                    @foreach($appealsPending as $appeal)
                    <div class="flex items-center justify-between p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $appeal->studentAchievement?->event_name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $appeal->studentAchievement?->student?->name }}</p>
                                <span class="text-xs text-gray-400">•</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $appeal->studentAchievement?->student?->faculty_name }}</p>
                            </div>
                            <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">
                                Diajukan {{ $appeal->submitted_at?->diffForHumans() }}
                            </p>
                        </div>
                        <a href="{{ route('admin.appeals.show', $appeal) }}" 
                            class="ml-3 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs rounded-lg font-medium transition-colors flex-shrink-0">
                            Review
                        </a>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.appeals.index') }}" 
                        class="text-sm text-amber-600 hover:text-amber-700 dark:text-amber-400 font-medium flex items-center justify-center gap-1">
                        Lihat Semua Banding
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Activity & Achievements Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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
                <div class="space-y-3 max-h-[400px] overflow-y-auto">
                    @foreach($urgentPending as $alert)
                    <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $alert->event_name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $alert->student?->name }} • {{ $alert->submitted_at->diffInDays() }} hari</p>
                        </div>
                        <a href="{{ route('admin.university.show', $alert) }}" 
                            class="ml-3 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg font-medium transition-colors flex-shrink-0">
                            Review
                        </a>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Combined Recent Activity & Achievements -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        Aktivitas & Prestasi Terbaru
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">5 aktivitas terakhir</p>
                </div>
                <a href="{{ route('admin.student-achievements') }}" 
                    class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400 font-medium flex items-center gap-1">
                    Lihat Semua
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            @if($recentValidations->isEmpty() && $recentAchievements->isEmpty())
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Belum ada aktivitas</p>
                </div>
            @else
                <div class="space-y-3 max-h-[400px] overflow-y-auto">
                    @php
                        // Combine validations and achievements, then sort by date and take 5
                        $combinedActivities = collect();
                        
                        // Add validations with type marker
                        foreach($recentValidations as $log) {
                            $combinedActivities->push([
                                'type' => 'validation',
                                'data' => $log,
                                'date' => $log->validated_at
                            ]);
                        }
                        
                        // Add achievements with type marker
                        foreach($recentAchievements as $achievement) {
                            $combinedActivities->push([
                                'type' => 'achievement',
                                'data' => $achievement,
                                'date' => $achievement->created_at
                            ]);
                        }
                        
                        // Sort by date descending and take 5
                        $combinedActivities = $combinedActivities->sortByDesc('date')->take(5);
                    @endphp
                    
                    @foreach($combinedActivities as $activity)
                        @if($activity['type'] === 'validation')
                            @php $log = $activity['data']; @endphp
                            <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900/70 transition-colors">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $log->new_status === 'Disetujui' ? 'bg-green-100 dark:bg-green-900/30' : ($log->new_status === 'Ditolak' ? 'bg-red-100 dark:bg-red-900/30' : 'bg-blue-100 dark:bg-blue-900/30') }}">
                                    @if($log->new_status === 'Disetujui')
                                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    @elseif($log->new_status === 'Ditolak')
                                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                            Validasi
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $log->new_status === 'Disetujui' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($log->new_status === 'Ditolak' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400') }}">
                                            {{ $log->new_status }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate mt-1">{{ $log->studentAchievement->event_name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $log->validator->name }} • {{ $log->validated_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @else
                            @php $achievement = $activity['data']; @endphp
                            <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900/70 transition-colors">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3 flex-1 min-w-0">
                                        <div class="flex-shrink-0 h-8 w-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                            <span class="text-purple-600 dark:text-purple-400 font-medium text-xs">{{ substr($achievement->student?->name ?? 'N', 0, 1) }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                                    Pengajuan
                                                </span>
                                                @php
                                                    $statusColor = match($achievement->validation_status) {
                                                        'Disetujui' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                                        'Ditolak' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                        'Revisi' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                                        default => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                    };
                                                @endphp
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                                    {{ $achievement->validation_status }}
                                                </span>
                                            </div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate mt-1">{{ $achievement->event_name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $achievement->student?->name ?? '-' }} • {{ $achievement->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('admin.university.show', $achievement) }}" 
                                        class="flex-shrink-0 px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded-lg font-medium transition-colors">
                                        Detail
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Top 5 Students -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Top 5 Mahasiswa Berprestasi
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Berdasarkan jumlah prestasi disetujui</p>
                </div>
            </div>
            @if($topPerformers->isEmpty())
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Belum ada data mahasiswa berprestasi</p>
                </div>
            @else
                <div class="space-y-3 max-h-[400px] overflow-y-auto">
                    @foreach($topPerformers->take(5) as $index => $student)
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                @if($index === 0)
                                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center shadow-lg">
                                        <span class="text-white font-bold text-lg">1</span>
                                    </div>
                                @elseif($index === 1)
                                    <div class="w-12 h-12 bg-gradient-to-br from-gray-300 to-gray-500 rounded-full flex items-center justify-center shadow-lg">
                                        <span class="text-white font-bold text-lg">2</span>
                                    </div>
                                @elseif($index === 2)
                                    <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full flex items-center justify-center shadow-lg">
                                        <span class="text-white font-bold text-lg">3</span>
                                    </div>
                                @else
                                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                        <span class="text-purple-600 dark:text-purple-400 font-bold text-lg">{{ $index + 1 }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $student->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $student->student_id }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $student->faculty }}</p>
                            </div>
                            
                            <div class="text-right flex-shrink-0">
                                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $student->achievements_count }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Prestasi</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Trend / Period Comparison Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            @if($periodComparison)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Perbandingan Prestasi Per Periode</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Distribusi dan status prestasi di setiap periode akademik</p>
                </div>
                <div class="h-80">
                    <canvas id="periodComparisonChart"></canvas>
                </div>
            @else
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Trend Submission Bulanan</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Perbandingan pengajuan dan persetujuan</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                            <span class="w-3 h-3 bg-purple-500 rounded-full"></span>
                            Submitted
                        </span>
                        <span class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                            <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                            Approved
                        </span>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            @endif
        </div>

        <!-- Faculty Comparison Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Perbandingan Per Fakultas</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Distribusi prestasi berdasarkan fakultas</p>
            </div>
            <div class="h-80">
                <canvas id="facultyComparisonChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Level & Category Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Level Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Distribusi Tingkat Lomba</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Berdasarkan tingkat kompetisi</p>
            </div>
            <div class="h-80 flex items-center justify-center">
                <canvas id="levelDistributionChart"></canvas>
            </div>
        </div>

        <!-- Category Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Distribusi Kategori</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Prestasi berdasarkan kategori</p>
            </div>
            <div class="h-80">
                <canvas id="categoryDistributionChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#9ca3af' : '#6b7280';
    const gridColor = isDark ? '#374151' : '#e5e7eb';

    // Debug: Log all data
    console.log('Period Comparison:', @json($periodComparison ?? null));
    console.log('Monthly Trend:', @json($monthlyTrend ?? []));
    console.log('Level Distribution:', @json($levelDistribution ?? []));
    console.log('Faculty Comparison:', @json($facultyComparison ?? []));
    console.log('Category Distribution:', @json($categoryDistribution ?? []));

    @if($periodComparison)
    // Period Comparison Chart
    const periodData = @json($periodComparison);
    if (periodData && periodData.length > 0) {
        new Chart(document.getElementById('periodComparisonChart'), {
        type: 'bar',
        data: {
            labels: periodData.map(d => d.period),
            datasets: [
                {
                    label: 'Total',
                    data: periodData.map(d => d.total),
                    backgroundColor: '#8b5cf6',
                    borderRadius: 4,
                    barThickness: 20
                },
                {
                    label: 'Disetujui',
                    data: periodData.map(d => d.approved),
                    backgroundColor: '#10b981',
                    borderRadius: 4,
                    barThickness: 20
                },
                {
                    label: 'Menunggu',
                    data: periodData.map(d => d.pending),
                    backgroundColor: '#f59e0b',
                    borderRadius: 4,
                    barThickness: 20
                },
                {
                    label: 'Ditolak',
                    data: periodData.map(d => d.rejected),
                    backgroundColor: '#ef4444',
                    borderRadius: 4,
                    barThickness: 20
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: textColor,
                        padding: 10,
                        font: { size: 10 },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: textColor, font: { size: 9 } },
                    grid: { color: gridColor, drawBorder: false }
                },
                x: {
                    ticks: { color: textColor, font: { size: 9 }, maxRotation: 45 },
                    grid: { display: false }
                }
            }
        }
    });
    } else {
        console.warn('Period comparison data is empty');
        document.getElementById('periodComparisonChart').parentElement.innerHTML = '<p class="text-center text-gray-500 py-8">Tidak ada data periode</p>';
    }
    @else
    // Monthly Trend Chart
    const monthlyData = @json($monthlyTrend);
    if (monthlyData && monthlyData.length > 0) {
        new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: {
            labels: monthlyData.map(d => d.month),
            datasets: [
                {
                    label: 'Submitted',
                    data: monthlyData.map(d => d.submitted),
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#8b5cf6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                },
                {
                    label: 'Approved',
                    data: monthlyData.map(d => d.approved),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: textColor, font: { size: 11 } },
                    grid: { color: gridColor, drawBorder: false }
                },
                x: {
                    ticks: { color: textColor, font: { size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });
    } else {
        console.warn('Monthly trend data is empty');
        document.getElementById('monthlyTrendChart').parentElement.innerHTML = '<p class="text-center text-gray-500 py-8">Tidak ada data trend bulanan</p>';
    }
    @endif

    // Level Distribution Chart
    const levelData = @json($levelDistribution);
    if (levelData && Object.keys(levelData).length > 0 && Object.values(levelData).some(v => v > 0)) {
        new Chart(document.getElementById('levelDistributionChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(levelData),
            datasets: [{
                data: Object.values(levelData),
                backgroundColor: ['#8b5cf6', '#3b82f6', '#10b981'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: textColor,
                        padding: 12,
                        font: { size: 10 },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            cutout: '65%'
        }
    });
    } else {
        console.warn('Level distribution data is empty');
        document.getElementById('levelDistributionChart').parentElement.innerHTML = '<p class="text-center text-gray-500 py-8">Tidak ada data distribusi tingkat</p>';
    }

    // Faculty Comparison Chart
    const facultyData = @json($facultyComparison);
    if (facultyData && facultyData.length > 0) {
        new Chart(document.getElementById('facultyComparisonChart'), {
        type: 'bar',
        data: {
            labels: facultyData.map(d => d.faculty),
            datasets: [
                {
                    label: 'Total',
                    data: facultyData.map(d => d.total),
                    backgroundColor: '#8b5cf6',
                    borderRadius: 6
                },
                {
                    label: 'Disetujui',
                    data: facultyData.map(d => d.approved),
                    backgroundColor: '#10b981',
                    borderRadius: 6
                },
                {
                    label: 'Menunggu',
                    data: facultyData.map(d => d.pending),
                    backgroundColor: '#f59e0b',
                    borderRadius: 6
                },
                {
                    label: 'Ditolak',
                    data: facultyData.map(d => d.rejected),
                    backgroundColor: '#ef4444',
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: textColor,
                        padding: 10,
                        font: { size: 11 },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: textColor, font: { size: 10 } },
                    grid: { color: gridColor, drawBorder: false }
                },
                x: {
                    ticks: { color: textColor, font: { size: 10 } },
                    grid: { display: false }
                }
            }
        }
    });
    } else {
        console.warn('Faculty comparison data is empty');
        document.getElementById('facultyComparisonChart').parentElement.innerHTML = '<p class="text-center text-gray-500 py-8">Tidak ada data perbandingan fakultas</p>';
    }

    // Category Distribution Chart
    const categoryData = @json($categoryDistribution);
    if (categoryData && categoryData.length > 0 && categoryData.some(d => d.total > 0)) {
        // Vibrant color palette for categories
        const categoryColors = [
            '#8b5cf6', // Purple
            '#3b82f6', // Blue
            '#10b981', // Green
            '#f59e0b', // Amber
            '#ef4444', // Red
            '#ec4899', // Pink
            '#06b6d4', // Cyan
            '#f97316', // Orange
            '#84cc16', // Lime
            '#6366f1', // Indigo
            '#14b8a6', // Teal
            '#a855f7', // Violet
        ];
        
        new Chart(document.getElementById('categoryDistributionChart'), {
        type: 'doughnut',
        data: {
            labels: categoryData.map(d => d.category),
            datasets: [{
                data: categoryData.map(d => d.total),
                backgroundColor: categoryColors,
                borderWidth: 2,
                borderColor: isDark ? '#1f2937' : '#ffffff',
                hoverOffset: 15,
                hoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: textColor,
                        padding: 15,
                        font: { size: 11, weight: '500' },
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8,
                        boxHeight: 8
                    }
                },
                tooltip: {
                    backgroundColor: isDark ? '#1f2937' : '#ffffff',
                    titleColor: isDark ? '#f9fafb' : '#111827',
                    bodyColor: isDark ? '#d1d5db' : '#374151',
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return ` ${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });
    } else {
        console.warn('Category distribution data is empty');
        document.getElementById('categoryDistributionChart').parentElement.innerHTML = '<p class="text-center text-gray-500 py-8">Tidak ada data distribusi kategori</p>';
    }
});
</script>
@endsection
