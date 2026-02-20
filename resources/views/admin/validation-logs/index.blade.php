@extends('layouts.admin')

@section('title', 'Log Validasi')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl font-bold text-gray-900 dark:text-white">Log Validasi</h2>
                <p class="text-sm lg:text-base text-gray-500 dark:text-gray-400 mt-1">Riwayat lengkap aktivitas validasi prestasi mahasiswa oleh validator dan admin.</p>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 lg:gap-6 xl:gap-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl border border-gray-200 dark:border-gray-700 p-5 lg:p-6 xl:p-8 desktop-card-hover">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd"
                                d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl border border-gray-200 dark:border-gray-700 p-5 lg:p-6 xl:p-8 desktop-card-hover">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Disetujui</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ number_format($stats['approved']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl border border-gray-200 dark:border-gray-700 p-5 lg:p-6 xl:p-8 desktop-card-hover">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Ditolak</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($stats['rejected']) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl border border-gray-200 dark:border-gray-700 p-5 lg:p-6 xl:p-8 desktop-card-hover">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Revisi</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ number_format($stats['revision']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl border border-gray-200 dark:border-gray-700 p-5 lg:p-6 xl:p-8 desktop-card-hover">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Hari Ini</p>
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                            {{ number_format($stats['today']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl border border-gray-200 dark:border-gray-700 p-5 lg:p-6 xl:p-8 shadow-sm">
            <form method="GET" action="{{ url()->current() }}" class="space-y-4">
                <!-- Main Filters Section -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
                    <!-- Search (Span 6) -->
                    <div class="lg:col-span-6">
                        <label
                            class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Pencarian</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-purple-500 transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari nama mahasiswa, NIM, atau nama event..."
                                class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white dark:focus:bg-gray-700 transition-all text-sm">
                        </div>
                    </div>

                    <!-- Decision (Span 3) -->
                    <div class="lg:col-span-3">
                        <label
                            class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Status</label>
                        <div class="relative">
                            <select name="decision" onchange="this.form.submit()"
                                class="w-full pl-4 pr-10 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white dark:focus:bg-gray-700 transition-all text-sm appearance-none cursor-pointer">
                                <option value="">Semua Status</option>
                                <option value="Disetujui" {{ request('decision') == 'Disetujui' ? 'selected' : '' }}>Disetujui
                                </option>
                                <option value="Ditolak" {{ request('decision') == 'Ditolak' ? 'selected' : '' }}>Ditolak
                                </option>
                                <option value="Revisi" {{ request('decision') == 'Revisi' ? 'selected' : '' }}>Revisi</option>
                            </select>
                            <div
                                class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Validator (Span 3) -->
                    <div class="lg:col-span-3">
                        <label
                            class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Validator</label>
                        <div class="relative">
                            <select name="validator" onchange="this.form.submit()"
                                class="w-full pl-4 pr-10 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white dark:focus:bg-gray-700 transition-all text-sm appearance-none cursor-pointer">
                                <option value="">Semua Validator</option>
                                @foreach($validators as $validator)
                                    <option value="{{ $validator->id }}" {{ request('validator') == $validator->id ? 'selected' : '' }}>
                                        {{ $validator->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div
                                class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Academic Filters (Optional / Collapsible logic or visual separation) -->
                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 mb-6 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Filter
                            Akademik</h3>
                    </div>
                    <div class="academic-filter-wrapper">
                        <x-sigap-filter-simple :faculties="$sigapFaculties" :departments="$sigapDepartments"
                            :studyPrograms="$sigapStudyPrograms" :selectedFaculty="$selectedFaculty"
                            :selectedDepartment="$selectedDepartment" :selectedStudyProgram="$selectedStudyProgram" />
                    </div>
                </div>

                <!-- Date Range & Tools -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
                    <!-- Date From -->
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Dari
                            Tanggal</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 text-sm">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Sampai
                            Tanggal</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 text-sm">
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        @if(request()->hasAny(['search', 'decision', 'validator', 'date_from', 'date_to', 'faculty_id', 'department_id', 'program_study_id']))
                            <a href="{{ url()->current() }}"
                                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2"
                                title="Reset Filter">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </a>
                        @endif
                        <button type="submit"
                            class="flex-1 px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition-all shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->

        <!-- Mobile View -->
        <div class="md:hidden space-y-4">
            @forelse($logs as $log)
                @php
                    $statusConfig = [/* PASTE ARRAY STATUS CONFIG YANG SUDAH ADA */];
                    $status = $statusConfig[$log->new_status] ?? [
                        'label' => $log->new_status,
                        'sublabel' => 'Status',
                        'bg' => 'bg-gray-50 dark:bg-gray-700',
                        'border' => 'border-gray-200 dark:border-gray-600',
                        'text' => 'text-gray-700 dark:text-gray-300',
                        'icon' => ''
                    ];
                @endphp

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 space-y-4">

                    <!-- Header: Mahasiswa -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                            <span class="text-purple-600 dark:text-purple-400 font-semibold text-sm">
                                {{ substr($log->studentAchievement->student->name ?? 'M', 0, 1) }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 dark:text-white truncate">
                                {{ $log->studentAchievement->student->name ?? '-' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $log->studentAchievement->student->student_id ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Event -->
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $log->studentAchievement->event_name ?? '-' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $log->studentAchievement->level ?? '-' }}
                        </p>
                    </div>

                    <!-- Meta Grid -->
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $log->validated_at->format('d M Y') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Jam</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $log->validated_at->format('H:i') }}
                            </p>
                        </div>

                        <div class="col-span-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Validator</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $log->validator->name ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border {{ $status['bg'] }} {{ $status['border'] }} {{ $status['text'] }}">
                            {!! $status['icon'] !!}
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold leading-tight">
                                    {{ $status['label'] }}
                                </span>
                                <span class="text-xs opacity-75 leading-tight">
                                    {{ $status['sublabel'] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan -->
                    @if($log->notes)
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Catatan</p>
                            <p class="leading-relaxed">
                                {{ $log->notes }}
                            </p>
                        </div>
                    @endif

                    <!-- Action -->
                    @if($log->studentAchievement)
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('admin.student-achievements.show', $log->studentAchievement->sa_id) }}"
                                class="text-sm text-purple-600 dark:text-purple-400 font-medium">
                                Lihat Detail →
                            </a>
                        </div>
                    @endif

                </div>
            @empty
                <div class="text-center py-10 text-sm text-gray-500 dark:text-gray-400">
                    Tidak ada log validasi
                </div>
            @endforelse
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-sm lg:text-base">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th
                                class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                Waktu
                            </th>
                            <th
                                class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                Mahasiswa
                            </th>
                            <th
                                class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                Event
                            </th>
                            <th
                                class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                Validator
                            </th>
                            <th
                                class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                Keputusan
                            </th>
                            <th
                                class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                Catatan
                            </th>
                            <th
                                class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($logs as $log)
                            <tr class="desktop-table-row transition-colors">
                                <!-- Waktu -->
                                <td class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5 whitespace-nowrap">
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $log->validated_at->format('d M Y') }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $log->validated_at->format('H:i') }}
                                        </p>
                                    </div>
                                </td>

                                <!-- Mahasiswa -->
                                <td class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-purple-600 dark:text-purple-400 font-semibold text-xs">
                                                {{ substr($log->studentAchievement->student->name ?? 'M', 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 dark:text-white truncate">
                                                {{ $log->studentAchievement->student->name ?? '-' }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $log->studentAchievement->student->student_id ?? '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Event -->
                                <td class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5">
                                    <div class="max-w-xs">
                                        <p class="font-medium text-gray-900 dark:text-white truncate"
                                            title="{{ $log->studentAchievement->event_name ?? '-' }}">
                                            {{ $log->studentAchievement->event_name ?? '-' }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $log->studentAchievement->level ?? '-' }}
                                        </p>
                                    </div>
                                </td>

                                <!-- Validator -->
                                <td class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-blue-600 dark:text-blue-400 font-semibold text-xs">
                                                {{ substr($log->validator->name ?? 'V', 0, 1) }}
                                            </span>
                                        </div>
                                        <span class="text-sm text-gray-900 dark:text-white">
                                            {{ $log->validator->name ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Keputusan -->
                                <td class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5">
                                    @php
                                        $statusConfig = [
                                            // Two-stage validation statuses
                                            'faculty_approved' => [
                                                'label' => 'Disetujui',
                                                'sublabel' => 'Fakultas',
                                                'bg' => 'bg-indigo-50 dark:bg-indigo-900/20',
                                                'border' => 'border-indigo-200 dark:border-indigo-800',
                                                'text' => 'text-indigo-700 dark:text-indigo-300',
                                                'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
                                            ],
                                            'faculty_rejected' => [
                                                'label' => 'Ditolak',
                                                'sublabel' => 'Fakultas',
                                                'bg' => 'bg-red-50 dark:bg-red-900/20',
                                                'border' => 'border-red-200 dark:border-red-800',
                                                'text' => 'text-red-700 dark:text-red-300',
                                                'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>'
                                            ],
                                            'faculty_revision' => [
                                                'label' => 'Perlu Revisi',
                                                'sublabel' => 'Fakultas',
                                                'bg' => 'bg-blue-50 dark:bg-blue-900/20',
                                                'border' => 'border-blue-200 dark:border-blue-800',
                                                'text' => 'text-blue-700 dark:text-blue-300',
                                                'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>'
                                            ],
                                            'university_approved' => [
                                                'label' => 'Disetujui',
                                                'sublabel' => 'Universitas',
                                                'bg' => 'bg-green-50 dark:bg-green-900/20',
                                                'border' => 'border-green-200 dark:border-green-800',
                                                'text' => 'text-green-700 dark:text-green-300',
                                                'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
                                            ],
                                            'university_rejected' => [
                                                'label' => 'Ditolak',
                                                'sublabel' => 'Universitas',
                                                'bg' => 'bg-red-50 dark:bg-red-900/20',
                                                'border' => 'border-red-200 dark:border-red-800',
                                                'text' => 'text-red-700 dark:text-red-300',
                                                'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>'
                                            ],
                                            // Legacy statuses
                                            'Disetujui' => [
                                                'label' => 'Disetujui',
                                                'sublabel' => 'Lengkap',
                                                'bg' => 'bg-green-50 dark:bg-green-900/20',
                                                'border' => 'border-green-200 dark:border-green-800',
                                                'text' => 'text-green-700 dark:text-green-300',
                                                'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
                                            ],
                                            'Ditolak' => [
                                                'label' => 'Ditolak',
                                                'sublabel' => 'Tidak Memenuhi',
                                                'bg' => 'bg-red-50 dark:bg-red-900/20',
                                                'border' => 'border-red-200 dark:border-red-800',
                                                'text' => 'text-red-700 dark:text-red-300',
                                                'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>'
                                            ],
                                            'Revisi' => [
                                                'label' => 'Perlu Revisi',
                                                'sublabel' => 'Perbaikan',
                                                'bg' => 'bg-blue-50 dark:bg-blue-900/20',
                                                'border' => 'border-blue-200 dark:border-blue-800',
                                                'text' => 'text-blue-700 dark:text-blue-300',
                                                'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>'
                                            ],
                                        ];
                                        $status = $statusConfig[$log->new_status] ?? [
                                            'label' => $log->new_status,
                                            'sublabel' => 'Status',
                                            'bg' => 'bg-gray-50 dark:bg-gray-700',
                                            'border' => 'border-gray-200 dark:border-gray-600',
                                            'text' => 'text-gray-700 dark:text-gray-300',
                                            'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
                                        ];
                                    @endphp
                                    <div
                                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border {{ $status['bg'] }} {{ $status['border'] }} {{ $status['text'] }}">
                                        <div class="flex-shrink-0">
                                            {!! $status['icon'] !!}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold leading-tight">{{ $status['label'] }}</span>
                                            <span class="text-xs opacity-75 leading-tight">{{ $status['sublabel'] }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Catatan -->
                                <td class="px-4 py-3">
                                    @if($log->notes)
                                        <div class="max-w-xs">
                                            <p class="text-sm text-gray-600 dark:text-gray-400 truncate" title="{{ $log->notes }}">
                                                {{ $log->notes }}
                                            </p>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">-</span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="px-4 py-3">
                                    @if($log->studentAchievement)
                                        <div class="relative inline-block text-left" x-data="{ open: false }">
                                            <button @click="open = !open" @click.away="open = false"
                                                class="text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 p-1 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors"
                                                title="Opsi">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                                </svg>
                                            </button>

                                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="transform opacity-0 scale-95"
                                                x-transition:enter-end="transform opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="transform opacity-100 scale-100"
                                                x-transition:leave-end="transform opacity-0 scale-95"
                                                class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none border border-gray-200 dark:border-gray-700"
                                                style="display: none;">
                                                <div class="py-1">
                                                    <a href="{{ route('admin.student-achievements.show', $log->studentAchievement->sa_id) }}"
                                                        class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        Lihat Detail Prestasi
                                                    </a>
                                                    <a href="{{ route('admin.validation-logs', ['sa_id' => $log->studentAchievement->sa_id]) }}"
                                                        class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Lihat Riwayat Lengkap
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                        @if(request()->hasAny(['search', 'decision', 'validator', 'date_from', 'date_to']))
                                            Tidak ada log yang sesuai dengan filter
                                        @else
                                            Belum ada log validasi
                                        @endif
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        @if(request()->hasAny(['search', 'decision', 'validator', 'date_from', 'date_to']))
                                            Coba ubah filter pencarian Anda
                                        @else
                                            Log validasi akan muncul di sini
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($logs->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

        <!-- Info Footer -->
        <div class="text-sm text-gray-500 dark:text-gray-400 text-center">
            Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} log validasi
        </div>
    </div>
@endsection