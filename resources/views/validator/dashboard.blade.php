@extends('layouts.app')

@section('title', 'Dashboard Validator')
@section('subtitle', 'Panel Validator')

@php
    $userName = auth()->user()->name ?? 'Validator';
    $pendingCount = $pendingAchievements->count();
@endphp

@section('nav-links')
    @php
    $baseClass = 'px-4 py-2 rounded-lg text-sm font-medium transition-colors';
    $activeClass = 'bg-white dark:bg-gray-600 text-emerald-600 dark:text-emerald-400 shadow-sm';
    $inactiveClass = 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white';
@endphp

<div class="hidden md:flex items-center gap-1 bg-gray-100/50 dark:bg-gray-700/50 rounded-xl p-1">
    <a href="{{ route('validator.dashboard') }}"
       class="{{ $baseClass }} {{ request()->routeIs('validator.dashboard') ? $activeClass : $inactiveClass }}">
        Menunggu
        @if($pendingCount > 0)
            <span class="ml-1 px-1.5 py-0.5 text-xs rounded-md bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                {{ $pendingCount }}
            </span>
        @endif
    </a>

    <a href="{{ route('validator.history') }}"
       class="{{ $baseClass }} {{ request()->routeIs('validator.history') ? $activeClass : $inactiveClass }}">
        Riwayat
    </a>

    <a href="{{ route('validator.sk.index') }}"
       class="{{ $baseClass }} {{ request()->routeIs('validator.sk.*') ? $activeClass : $inactiveClass }}">
        Manajemen SK
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header with Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Validator</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Kelola dan validasi prestasi mahasiswa</p>
        </div>
        <a href="{{ route('validator.submit.form') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan Prestasi
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Menunggu Validasi</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $pendingAchievements->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Dokumen Pending</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $pendingAchievements->sum(fn($a) => $a->documents->where('status', 'pending')->count()) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Disetujui Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Prestasi</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $pendingAchievements->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header with Filters -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Menunggu Validasi</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Prestasi yang perlu divalidasi</p>
                </div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Total: <strong class="text-gray-900 dark:text-white">{{ $pendingAchievements->total() }}</strong> prestasi
                </span>
            </div>
            
            <!-- Filter Form -->
            <form method="GET" action="{{ route('validator.dashboard') }}" class="space-y-4" x-data="{ showFilters: {{ request()->hasAny(['level', 'category', 'date_from', 'date_to', 'submitted_by']) ? 'true' : 'false' }} }">
                <!-- Search Bar -->
                <div class="flex gap-3">
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Cari nama mahasiswa, event, atau penyelenggara..."
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                    <button type="button" @click="showFilters = !showFilters"
                        class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter
                        @if(request()->hasAny(['level', 'category', 'date_from', 'date_to', 'submitted_by']))
                            <span class="px-1.5 py-0.5 text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-full">
                                {{ collect(['level', 'category', 'date_from', 'date_to', 'submitted_by'])->filter(fn($f) => request()->filled($f))->count() }}
                            </span>
                        @endif
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors">
                        Cari
                    </button>
                    @if(request()->hasAny(['search', 'level', 'category', 'date_from', 'date_to', 'submitted_by']))
                        <a href="{{ route('validator.dashboard') }}"
                            class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
                
                <!-- Advanced Filters -->
                <div x-show="showFilters" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 pt-2"
                     style="display: none;">
                    <!-- Level Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tingkat</label>
                        <select name="level" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                            <option value="">Semua Tingkat</option>
                            @foreach($levels as $level)
                                <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Category Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                        <select name="category" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Date From -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Dari</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                    </div>
                    
                    <!-- Date To -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Sampai</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                    </div>
                    
                    <!-- Submitted By -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Diajukan Oleh</label>
                        <select name="submitted_by" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                            <option value="">Semua</option>
                            <option value="student" {{ request('submitted_by') == 'student' ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="validator" {{ request('submitted_by') == 'validator' ? 'selected' : '' }}>Validator</option>
                            <option value="admin" {{ request('submitted_by') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lomba</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tingkat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dokumen</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($pendingAchievements as $achievement)
                    @php
                        $docCount = $achievement->documents->count();
                        $approvedDocs = $achievement->documents->where('status', 'approved')->count();
                        $pendingDocs = $achievement->documents->where('status', 'pending')->count();
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">{{ substr($achievement->student?->name ?? 'N', 0, 1) }}</span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $achievement->student?->name ?? '-' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $achievement->student_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ Str::limit($achievement->event_name, 40) }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $achievement->organizer }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-medium {{ $achievement->level === 'Internasional' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : ($achievement->level === 'Nasional' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400') }}">
                                {{ $achievement->level }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $approvedDocs }}/{{ $docCount }}</span>
                                @if($pendingDocs > 0)
                                    <span class="px-1.5 py-0.5 text-xs bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 rounded">{{ $pendingDocs }} pending</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('validator.achievements.show', $achievement) }}" class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300" title="Detail & Validasi">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                </a>
                                <a href="{{ route('achievements.documents.index', $achievement) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Upload Dokumen">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-gray-800 dark:text-white font-medium">Semua sudah divalidasi!</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tidak ada prestasi yang menunggu validasi</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($pendingAchievements->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Menampilkan <span class="font-medium text-gray-900 dark:text-white">{{ $pendingAchievements->firstItem() }}</span>
                    sampai <span class="font-medium text-gray-900 dark:text-white">{{ $pendingAchievements->lastItem() }}</span>
                    dari <span class="font-medium text-gray-900 dark:text-white">{{ $pendingAchievements->total() }}</span> prestasi
                </div>
                
                <div class="flex items-center gap-2">
                    {{-- Previous Page Link --}}
                    @if ($pendingAchievements->onFirstPage())
                        <span class="px-3 py-2 text-sm text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-gray-800 rounded-lg cursor-not-allowed">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </span>
                    @else
                        <a href="{{ $pendingAchievements->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    <div class="flex items-center gap-1">
                        @foreach ($pendingAchievements->getUrlRange(1, $pendingAchievements->lastPage()) as $page => $url)
                            @if ($page == $pendingAchievements->currentPage())
                                <span class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    </div>

                    {{-- Next Page Link --}}
                    @if ($pendingAchievements->hasMorePages())
                        <a href="{{ $pendingAchievements->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @else
                        <span class="px-3 py-2 text-sm text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-gray-800 rounded-lg cursor-not-allowed">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection


@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush
