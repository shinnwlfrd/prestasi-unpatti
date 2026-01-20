@extends('layouts.app')

@section('title', 'Dashboard Validator')
@section('subtitle', 'Panel Validator')

@php
    $userName = auth()->user()->name ?? 'Validator';
    $pendingCount = $pendingAchievements->count();
@endphp

@section('nav-links')
    <div class="hidden md:flex items-center gap-1 bg-gray-100/50 dark:bg-gray-700/50 rounded-xl p-1">
        <a href="{{ route('validator.dashboard') }}"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('validator.dashboard') ? 'bg-white dark:bg-gray-600 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white' }}">
            Menunggu
            @if($pendingCount > 0)
                <span class="ml-1 px-1.5 py-0.5 text-xs rounded-md bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">{{ $pendingCount }}</span>
            @endif
        </a>
        <a href="{{ route('validator.history') }}"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('validator.history') ? 'bg-white dark:bg-gray-600 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white' }}">
            Riwayat
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6 animate-fade-in">

        <!-- Validator Profile Card -->
        <x-card>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ auth()->user()->name }}</h2>
                    <p class="text-emerald-600 dark:text-emerald-400 font-medium text-sm">{{ auth()->user()->role }}</p>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Kelola dan validasi prestasi mahasiswa</p>
                </div>
                <x-button href="{{ route('validator.submit.form') }}" variant="success">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajukan Prestasi
                </x-button>
            </div>
        </x-card>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            <x-stat-card title="Menunggu Validasi" :value="$pendingCount" icon="clock" color="yellow" />
            <x-stat-card title="Dokumen Pending" :value="$pendingAchievements->sum(fn($a) => $a->documents->where('status', 'pending')->count())" icon="document" color="blue" />
            <x-stat-card title="Total Prestasi" :value="$pendingAchievements->count()" icon="trophy" color="purple" />
        </div>

        <!-- Mobile Tabs -->
        <div class="flex md:hidden gap-2 p-1 bg-gray-100 dark:bg-gray-700 rounded-xl">
            <a href="{{ route('validator.dashboard') }}" class="flex-1 py-2 text-center rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('validator.dashboard') ? 'bg-white dark:bg-gray-600 text-emerald-600' : 'text-gray-600' }}">Menunggu</a>
            <a href="{{ route('validator.history') }}" class="flex-1 py-2 text-center rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('validator.history') ? 'bg-white dark:bg-gray-600 text-emerald-600' : 'text-gray-600' }}">Riwayat</a>
        </div>

        <!-- Pending Achievements Table -->
        <x-card title="Menunggu Validasi ({{ $pendingCount }})" :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mahasiswa</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Prestasi</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Level</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dokumen</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($pendingAchievements as $ach)
                            @php
                                $docCount = $ach->documents->count();
                                $approvedDocs = $ach->documents->where('status', 'approved')->count();
                                $pendingDocs = $ach->documents->where('status', 'pending')->count();
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                                            {{ strtoupper(substr($ach->student->name ?? 'M', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-800 dark:text-white">{{ $ach->student->name ?? '-' }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $ach->student->student_id ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 hidden sm:table-cell">
                                    <div class="text-gray-800 dark:text-white font-medium">{{ Str::limit($ach->event_name, 30) }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $ach->achievement->category ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400 hidden md:table-cell">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-700">{{ $ach->level }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $approvedDocs }}/{{ $docCount }}</span>
                                        @if($pendingDocs > 0)
                                            <span class="px-1.5 py-0.5 text-xs bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 rounded">{{ $pendingDocs }} pending</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('validator.achievements.show', $ach) }}"
                                            class="p-2 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/40 transition-colors"
                                            title="Detail & Validasi">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('validator.achievements.documents', $ach) }}"
                                            class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors"
                                            title="Lihat Dokumen">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-gray-800 dark:text-white font-medium">Semua sudah divalidasi!</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada prestasi yang menunggu.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection
