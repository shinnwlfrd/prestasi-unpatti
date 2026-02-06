@extends('layouts.admin')

@section('title', 'Prestasi Mahasiswa')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Prestasi Mahasiswa</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Kelola dan monitor semua prestasi mahasiswa</p>
        </div>
        <a href="{{ route('admin.submit.create') }}" 
            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Prestasi
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $achievements->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Disetujui Fakultas</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $achievements->whereIn('validation_status', ['faculty_approved', 'university_review'])->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Disetujui Universitas</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $achievements->whereIn('validation_status', ['university_approved', 'Disetujui'])->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $achievements->whereIn('validation_status', ['submitted', 'faculty_review', 'Menunggu'])->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Revisi</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $achievements->whereIn('validation_status', ['faculty_revision', 'Revisi'])->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <form method="GET" action="{{ route('admin.student-achievements') }}" class="space-y-4">
            <!-- SIGAP Cascade Filter -->
            <x-sigap-filter-simple 
                :faculties="$sigapFaculties"
                :departments="$sigapDepartments"
                :studyPrograms="$sigapStudyPrograms"
                :selectedFaculty="$selectedFaculty"
                :selectedDepartment="$selectedDepartment"
                :selectedStudyProgram="$selectedStudyProgram"
            />
            
            <!-- Search & Additional Filters -->
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Cari nama mahasiswa, NIM, atau event..." 
                            class="pl-10 w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                
                <!-- Filter Status -->
                <select name="status" onchange="this.form.submit()" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">Semua Status</option>
                    <optgroup label="Validasi Fakultas">
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Diajukan</option>
                        <option value="faculty_review" {{ request('status') == 'faculty_review' ? 'selected' : '' }}>Review Fakultas</option>
                        <option value="faculty_approved" {{ request('status') == 'faculty_approved' ? 'selected' : '' }}>Disetujui Fakultas</option>
                        <option value="faculty_rejected" {{ request('status') == 'faculty_rejected' ? 'selected' : '' }}>Ditolak Fakultas</option>
                        <option value="faculty_revision" {{ request('status') == 'faculty_revision' ? 'selected' : '' }}>Revisi Fakultas</option>
                    </optgroup>
                    <optgroup label="Validasi Universitas">
                        <option value="university_review" {{ request('status') == 'university_review' ? 'selected' : '' }}>Review Universitas</option>
                        <option value="university_approved" {{ request('status') == 'university_approved' ? 'selected' : '' }}>Disetujui Universitas</option>
                        <option value="university_rejected" {{ request('status') == 'university_rejected' ? 'selected' : '' }}>Ditolak Universitas</option>
                    </optgroup>
                    <optgroup label="Banding">
                        <option value="appeal_submitted" {{ request('status') == 'appeal_submitted' ? 'selected' : '' }}>Banding Diajukan</option>
                        <option value="appeal_approved" {{ request('status') == 'appeal_approved' ? 'selected' : '' }}>Banding Diterima</option>
                        <option value="appeal_rejected" {{ request('status') == 'appeal_rejected' ? 'selected' : '' }}>Banding Ditolak</option>
                    </optgroup>
                    <optgroup label="Legacy (Lama)">
                        <option value="Menunggu" {{ request('status') == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="Disetujui" {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="Revisi" {{ request('status') == 'Revisi' ? 'selected' : '' }}>Revisi</option>
                    </optgroup>
                </select>

                <!-- Filter Category -->
                <select name="category" onchange="this.form.submit()" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                
                <!-- Filter Level -->
                <select name="level" onchange="this.form.submit()" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">Semua Level</option>
                    <option value="Internasional" {{ request('level') == 'Internasional' ? 'selected' : '' }}>Internasional</option>
                    <option value="Nasional" {{ request('level') == 'Nasional' ? 'selected' : '' }}>Nasional</option>
                    <option value="Universitas" {{ request('level') == 'Universitas' ? 'selected' : '' }}>Universitas</option>
                </select>
                
                <!-- Per Page -->
                <select name="per_page" onchange="this.form.submit()" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 per halaman</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per halaman</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per halaman</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per halaman</option>
                </select>
                
                <!-- Buttons -->
                <div class="flex gap-2">
                    @if(request()->hasAny(['search', 'status', 'level', 'category', 'faculty_id', 'department_id', 'program_study_id']))
                        <a href="{{ route('admin.student-achievements') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reset
                        </a>
                    @endif
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Cari
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Mahasiswa
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Event
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Kategori
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Level
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Peringkat
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Tanggal
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($achievements as $achievement)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <!-- Mahasiswa -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-purple-600 dark:text-purple-400 font-semibold text-sm">
                                        {{ substr($achievement->student->name ?? 'M', 0, 1) }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 dark:text-white truncate">
                                        {{ $achievement->student->name ?? '-' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $achievement->student->student_id ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <!-- Event -->
                        <td class="px-4 py-3">
                            <div class="max-w-xs">
                                <p class="font-medium text-gray-900 dark:text-white truncate" title="{{ $achievement->event_name }}">
                                    {{ $achievement->event_name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate" title="{{ $achievement->organizer }}">
                                    {{ $achievement->organizer }}
                                </p>
                            </div>
                        </td>

                        <!-- Kategori -->
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                {{ $achievement->achievement->category->name ?? $achievement->category->name ?? '-' }}
                            </span>
                        </td>

                        <!-- Level -->
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-1 rounded text-xs font-medium
                                @if($achievement->level == 'Internasional') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400
                                @elseif($achievement->level == 'Nasional') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                @elseif($achievement->level == 'Universitas') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                @else bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
                                @endif">
                                {{ $achievement->level }}
                            </span>
                        </td>

                        <!-- Peringkat -->
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            @if($achievement->ranking)
                                @if($achievement->ranking <= 3)
                                    <span class="inline-flex items-center gap-1">
                                        @if($achievement->ranking == 1) <span class="text-lg">🥇</span>
                                        @elseif($achievement->ranking == 2) <span class="text-lg">🥈</span>
                                        @else <span class="text-lg">🥉</span>
                                        @endif
                                        {{ $achievement->ranking }}
                                    </span>
                                @else
                                    {{ $achievement->ranking }}
                                @endif
                            @else
                                -
                            @endif
                        </td>

                        <!-- Tanggal -->
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($achievement->event_date)->format('d M Y') }}
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3">
                            @php
                                $statusConfig = [
                                    // Two-stage validation statuses
                                    'submitted' => ['label' => 'Diajukan', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', 'icon' => '📝'],
                                    'faculty_review' => ['label' => 'Review Fakultas', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400', 'icon' => '🔍'],
                                    'faculty_approved' => ['label' => 'Disetujui Fakultas', 'class' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400', 'icon' => '✓'],
                                    'faculty_rejected' => ['label' => 'Ditolak Fakultas', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'icon' => '✗'],
                                    'faculty_revision' => ['label' => 'Revisi Fakultas', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', 'icon' => '↻'],
                                    'university_review' => ['label' => 'Review Universitas', 'class' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400', 'icon' => '🔍'],
                                    'university_approved' => ['label' => 'Disetujui Universitas', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400', 'icon' => '✓✓'],
                                    'university_rejected' => ['label' => 'Ditolak Universitas', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'icon' => '✗✗'],
                                    'appeal_submitted' => ['label' => 'Banding Diajukan', 'class' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400', 'icon' => '⚖️'],
                                    'appeal_approved' => ['label' => 'Banding Diterima', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400', 'icon' => '✓'],
                                    'appeal_rejected' => ['label' => 'Banding Ditolak', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'icon' => '✗'],
                                    // Legacy statuses
                                    'Disetujui' => ['label' => 'Disetujui', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400', 'icon' => '✓'],
                                    'Menunggu' => ['label' => 'Menunggu', 'class' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400', 'icon' => '⏳'],
                                    'Ditolak' => ['label' => 'Ditolak', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'icon' => '✗'],
                                    'Revisi' => ['label' => 'Revisi', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', 'icon' => '↻'],
                                ];
                                $status = $statusConfig[$achievement->validation_status] ?? ['label' => $achievement->validation_status, 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300', 'icon' => '?'];
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $status['class'] }}">
                                <span>{{ $status['icon'] }}</span>
                                {{ $status['label'] }}
                            </span>
                        </td>

                        <!-- Aksi -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if($achievement->sa_id)
                                    <!-- Dropdown Menu -->
                                    <div class="relative inline-block text-left" x-data="{ open: false }">
                                        <button @click="open = !open" @click.away="open = false" 
                                                class="text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 p-1 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors" 
                                                title="Opsi">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                            </svg>
                                        </button>
                                        
                                        <div x-show="open" 
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none border border-gray-200 dark:border-gray-700"
                                             style="display: none;">
                                            <div class="py-1">
                                                <a href="{{ route('admin.student-achievements.show', $achievement->sa_id) }}" 
                                                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    Lihat Detail Prestasi
                                                </a>
                                                <a href="{{ route('admin.validation-logs', ['sa_id' => $achievement->sa_id]) }}" 
                                                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Riwayat Validasi
                                                </a>
                                                
                                                @if(in_array($achievement->validation_status, ['faculty_approved', 'university_review']))
                                                    <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                                                    <a href="{{ route('admin.university.show', $achievement->sa_id) }}" 
                                                       class="flex items-center gap-2 px-4 py-2 text-sm text-purple-700 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        Validasi Universitas
                                                    </a>
                                                @endif
                                                
                                                @if($achievement->validation_status == 'appeal_submitted')
                                                    <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                                                    <a href="{{ route('admin.appeals.show', $achievement->latestAppeal->id ?? '#') }}" 
                                                       class="flex items-center gap-2 px-4 py-2 text-sm text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                        </svg>
                                                        Review Banding
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                @if(request()->hasAny(['search', 'status', 'level']))
                                    Tidak ada prestasi yang sesuai dengan filter
                                @else
                                    Belum ada prestasi mahasiswa
                                @endif
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                @if(request()->hasAny(['search', 'status', 'level']))
                                    Coba ubah filter pencarian Anda
                                @else
                                    Prestasi mahasiswa akan muncul di sini
                                @endif
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($achievements->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $achievements->links() }}
            </div>
        @endif
    </div>

    <!-- Info Footer -->
    <div class="text-sm text-gray-500 dark:text-gray-400 text-center">
        Menampilkan {{ $achievements->firstItem() ?? 0 }} - {{ $achievements->lastItem() ?? 0 }} dari {{ $achievements->total() }} prestasi
    </div>
</div>
@endsection
