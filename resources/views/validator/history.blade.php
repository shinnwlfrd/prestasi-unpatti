@extends('layouts.validator')

@section('title', 'Riwayat Validasi')

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Total -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Validasi</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $logs->total() }}</p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Approved -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Disetujui</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
                        {{ $stats['approved'] ?? 0 }}
                    </p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Rejected -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ditolak</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
                        {{ $stats['rejected'] ?? 0 }}
                    </p>
                </div>
                <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Revision -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Revisi</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">
                        {{ $stats['revision'] ?? 0 }}
                    </p>
                </div>
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <form method="GET" action="{{ route('validator.history') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Nama mahasiswa, NIM, atau prestasi..."
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select name="status" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Status</option>
                        @php
                            $statusLabels = [
                                'pending' => 'Pending',
                                'submitted' => 'Diajukan',
                                'faculty_review' => 'Review Fakultas',
                                'faculty_approved' => 'Disetujui Fakultas',
                                'faculty_rejected' => 'Ditolak Fakultas',
                                'university_review' => 'Review Universitas',
                                'university_approved' => 'Disetujui Universitas',
                                'university_rejected' => 'Ditolak Universitas',
                                'rejected' => 'Ditolak',
                                'revision_requested' => 'Perlu Revisi',
                                'appeal_submitted' => 'Banding Diajukan',
                                'appeal_approved' => 'Banding Diterima',
                                'appeal_rejected' => 'Banding Ditolak',
                            ];
                        @endphp
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kategori</label>
                    <select name="category" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Level Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tingkat</label>
                    <select name="level" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Tingkat</option>
                        @foreach($levels as $level)
                            <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                                {{ $level }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'category', 'level', 'date_from', 'date_to']))
                    <a href="{{ route('validator.history') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium">
                        Reset
                    </a>
                @endif
                <div class="ml-auto flex items-center gap-2">
                    <label class="text-sm text-gray-600 dark:text-gray-400">Per halaman:</label>
                    <select name="per_page" onchange="this.form.submit()" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- History Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Validasi</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        @if(request()->hasAny(['search', 'status', 'category', 'level', 'date_from', 'date_to']))
                            Hasil filter
                        @else
                            Histori prestasi yang sudah divalidasi
                        @endif
                    </p>
                </div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Total: <strong class="text-gray-900 dark:text-white">{{ $logs->total() }}</strong> riwayat
                </span>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Prestasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">SK</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">{{ substr($log->studentAchievement->student->name ?? 'N', 0, 1) }}</span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $log->studentAchievement->student->name ?? '-' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $log->studentAchievement->student->student_id ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($log->studentAchievement->event_name ?? '-', 40) }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $log->studentAchievement->achievement->category->name ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $log->validated_at ? \Carbon\Carbon::parse($log->validated_at)->format('d M Y H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusConfig = [
                                    'faculty_approved' => [
                                        'bg' => 'bg-blue-100 dark:bg-blue-900/30',
                                        'text' => 'text-blue-800 dark:text-blue-300',
                                        'border' => 'border-blue-200 dark:border-blue-800',
                                        'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
                                        'label' => 'Disetujui Fakultas',
                                        'sublabel' => 'Menunggu review universitas'
                                    ],
                                    'university_approved' => [
                                        'bg' => 'bg-green-100 dark:bg-green-900/30',
                                        'text' => 'text-green-800 dark:text-green-300',
                                        'border' => 'border-green-200 dark:border-green-800',
                                        'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
                                        'label' => 'Disetujui Universitas',
                                        'sublabel' => 'Prestasi telah divalidasi'
                                    ],
                                    'faculty_rejected' => [
                                        'bg' => 'bg-red-100 dark:bg-red-900/30',
                                        'text' => 'text-red-800 dark:text-red-300',
                                        'border' => 'border-red-200 dark:border-red-800',
                                        'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
                                        'label' => 'Ditolak Fakultas',
                                        'sublabel' => 'Tidak memenuhi kriteria'
                                    ],
                                    'university_rejected' => [
                                        'bg' => 'bg-red-100 dark:bg-red-900/30',
                                        'text' => 'text-red-800 dark:text-red-300',
                                        'border' => 'border-red-200 dark:border-red-800',
                                        'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
                                        'label' => 'Ditolak Universitas',
                                        'sublabel' => 'Tidak memenuhi kriteria'
                                    ],
                                    'rejected' => [
                                        'bg' => 'bg-red-100 dark:bg-red-900/30',
                                        'text' => 'text-red-800 dark:text-red-300',
                                        'border' => 'border-red-200 dark:border-red-800',
                                        'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
                                        'label' => 'Ditolak',
                                        'sublabel' => 'Tidak memenuhi kriteria'
                                    ],
                                    'revision_requested' => [
                                        'bg' => 'bg-amber-100 dark:bg-amber-900/30',
                                        'text' => 'text-amber-800 dark:text-amber-300',
                                        'border' => 'border-amber-200 dark:border-amber-800',
                                        'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
                                        'label' => 'Perlu Revisi',
                                        'sublabel' => 'Dokumen perlu diperbaiki'
                                    ],
                                    'appeal_submitted' => [
                                        'bg' => 'bg-purple-100 dark:bg-purple-900/30',
                                        'text' => 'text-purple-800 dark:text-purple-300',
                                        'border' => 'border-purple-200 dark:border-purple-800',
                                        'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>',
                                        'label' => 'Banding Diajukan',
                                        'sublabel' => 'Menunggu review banding'
                                    ],
                                    'appeal_approved' => [
                                        'bg' => 'bg-indigo-100 dark:bg-indigo-900/30',
                                        'text' => 'text-indigo-800 dark:text-indigo-300',
                                        'border' => 'border-indigo-200 dark:border-indigo-800',
                                        'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
                                        'label' => 'Banding Diterima',
                                        'sublabel' => 'Prestasi disetujui'
                                    ],
                                    'appeal_rejected' => [
                                        'bg' => 'bg-rose-100 dark:bg-rose-900/30',
                                        'text' => 'text-rose-800 dark:text-rose-300',
                                        'border' => 'border-rose-200 dark:border-rose-800',
                                        'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
                                        'label' => 'Banding Ditolak',
                                        'sublabel' => 'Keputusan final'
                                    ],
                                ];
                                
                                $config = $statusConfig[$log->new_status] ?? [
                                    'bg' => 'bg-gray-100 dark:bg-gray-700',
                                    'text' => 'text-gray-800 dark:text-gray-300',
                                    'border' => 'border-gray-200 dark:border-gray-600',
                                    'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>',
                                    'label' => ucfirst(str_replace('_', ' ', $log->new_status)),
                                    'sublabel' => ''
                                ];
                            @endphp
                            <div class="inline-flex flex-col gap-1">
                                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border {{ $config['bg'] }} {{ $config['text'] }} {{ $config['border'] }}">
                                    <div class="flex-shrink-0">
                                        {!! $config['icon'] !!}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold leading-tight">{{ $config['label'] }}</span>
                                        @if(!empty($config['sublabel']))
                                        <span class="text-[10px] opacity-75 leading-tight">{{ $config['sublabel'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $skDocument = $log->sk_document;
                                if (!$skDocument && $log->new_status === 'Disetujui') {
                                    $skDoc = $log->studentAchievement?->documents()
                                        ->where('document_type', 'sk_resmi')
                                        ->where('status', 'approved')
                                        ->latest()
                                        ->first();
                                    $skDocument = $skDoc?->file_path;
                                }
                            @endphp
                            
                            @if($skDocument)
                                <a href="{{ route('validation.sk.preview', $log) }}" target="_blank"
                                    class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 text-sm font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Lihat SK
                                </a>
                            @else
                                <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600 dark:text-gray-400 truncate max-w-[200px] block" title="{{ $log->notes }}">
                                {{ $log->notes ?? '-' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-gray-800 dark:text-white font-medium">Belum ada riwayat</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Validasi prestasi akan muncul di sini</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
