@extends('layouts.validator')

@section('title', $isPimpinan ? 'Dashboard Pimpinan' : 'Dashboard Validator')

@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@php
    // Set default values to prevent undefined variable errors
    $pendingAchievements = $pendingAchievements ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
    $categories = $categories ?? collect();
    $levels = $levels ?? [];
    $stats = $stats ?? [
        'pending' => 0,
        'approved_today' => 0,
        'revision_requested' => 0,
        'avg_review_time_hours' => 0,
    ];

    $userName = auth()->user()->name ?? ($isPimpinan ? 'Pimpinan' : 'Validator');
    $pendingCount = $pendingAchievements->count();
@endphp

@section('content')
    <div x-data="validatorDashboard()" class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex-1">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Selamat datang,
                        <span class="font-semibold text-gray-900 dark:text-white">
                            {{ $isPimpinan ? ($positionLabel . ' ' . ($scopeName ?? '')) : auth()->user()->name }}
                        </span>
                        @if($isPimpinan)
                            <span
                                class="ml-2 text-xs px-2 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full">
                                📊 Mode Read-Only
                            </span>
                        @endif
                    </p>

                    @if($isPimpinan)
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-gray-400 mr-1">Cetak Laporan:</span>
                            <div class="inline-flex rounded-lg shadow-sm" role="group">
                                <a href="{{ route('api.export.achievements', array_merge(request()->all(), ['format' => 'excel'])) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-l-lg hover:bg-emerald-100 hover:text-emerald-800 transition-colors dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-400 dark:hover:bg-emerald-900/40">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Excel
                                </a>
                                <a href="{{ route('api.export.achievements', array_merge(request()->all(), ['format' => 'csv'])) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 border border-l-0 border-blue-200 rounded-r-lg hover:bg-blue-100 hover:text-blue-800 transition-colors dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-400 dark:hover:bg-blue-900/40">
                                    CSV
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @unless($isPimpinan)
                <a href="{{ route('validator.submit.form') }}"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium flex items-center gap-2 transition-colors w-fit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajukan Prestasi
                </a>
            @endunless
        </div>

        <!-- @if($isPimpinan && !empty($positionLabel))
                                                                            <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-xl shadow-lg p-1">
                                                                                <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                                                                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                                                                        <div class="flex items-center gap-4">
                                                                                            <div
                                                                                                class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                                                                                                @if($level === 'university')
                                                                                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                                                                    </svg>
                                                                                                @elseif($level === 'faculty')
                                                                                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                                            d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                                                                                    </svg>
                                                                                                @elseif($level === 'department')
                                                                                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                                                                    </svg>
                                                                                                @elseif($level === 'program_study')
                                                                                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                                                                    </svg>
                                                                                                @else
                                                                                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                                                                    </svg>
                                                                                                @endif
                                                                                            </div>
                                                                                            <div>
                                                                                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                                                                    Tingkat Pimpinan</p>
                                                                                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $positionLabel }}</h3>
                                                                                                @if(!empty($scopeName))
                                                                                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5">{{ $scopeName }}</p>
                                                                                                @endif
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="flex flex-wrap items-center gap-2">
                                                                                            @php
                                                                                                $levelColors = [
                                                                                                    'university' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border-red-200 dark:border-red-800',
                                                                                                    'faculty' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200 dark:border-blue-800',
                                                                                                    'department' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                                                                                                    'program_study' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200 dark:border-amber-800',
                                                                                                    'graduate_program' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 border-purple-200 dark:border-purple-800',
                                                                                                ];
                                                                                                $levelLabels = [
                                                                                                    'university' => 'Tingkat Universitas',
                                                                                                    'faculty' => 'Tingkat Fakultas',
                                                                                                    'department' => 'Tingkat Jurusan',
                                                                                                    'program_study' => 'Tingkat Program Studi',
                                                                                                    'graduate_program' => 'Program Pascasarjana',
                                                                                                ];
                                                                                                $levelColor = $levelColors[$level] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600';
                                                                                                $levelLabel = $levelLabels[$level] ?? ucfirst(str_replace('_', ' ', $level ?? 'Unknown'));
                                                                                            @endphp
                                                                                            <span
                                                                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold border {{ $levelColor }}">
                                                                                                <span class="w-2 h-2 rounded-full bg-current opacity-60"></span>
                                                                                                {{ $levelLabel }}
                                                                                            </span>
                                                                                            <span
                                                                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                                                                </svg>
                                                                                                {{ number_format($totalStudents) }} Mahasiswa
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif -->


        <!-- Period Filter Bar -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-1">
            <form id="globalFilterForm" method="GET" action="{{ route($routePrefix . '.dashboard') }}"
                class="flex items-center">
                <!-- Preserve existing filters -->
                @foreach(request()->except('periods') as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $v)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                <div class="px-4 py-2 border-r border-gray-200 dark:border-gray-700 flex items-center gap-2 flex-shrink-0">
                    <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Periode:</span>
                </div>

                <div class="flex-1 flex items-center gap-2 overflow-x-auto px-4 py-2 no-scrollbar">
                    @foreach($allPeriods as $period)
                        <label class="relative flex-shrink-0 cursor-pointer">
                            <input type="checkbox" name="periods[]" value="{{ $period->id }}" {{ in_array($period->id, $selectedPeriods) ? 'checked' : '' }} class="peer sr-only" onchange="this.form.submit()">
                            <div
                                class="px-3 py-1.5 rounded-full border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 text-xs font-medium text-gray-600 dark:text-gray-400 peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600 transition-all hover:border-emerald-400 dark:hover:border-emerald-500 shadow-sm">
                                {{ $period->name }}
                                @if($period->is_active)
                                    <span
                                        class="ml-1 w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block peer-checked:bg-white animate-pulse"></span>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>

                @if(!empty($selectedPeriods) && !($selectedPeriods == [\App\Models\AcademicPeriod::where('is_active', true)->first()?->id]))
                    <div class="px-4 py-2 border-l border-gray-200 dark:border-gray-700">
                        <a href="{{ route($routePrefix . '.dashboard', request()->except('periods')) }}"
                            class="text-xs font-medium text-red-600 hover:text-red-700 dark:text-red-400 flex items-center gap-1 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Reset
                        </a>
                    </div>
                @endif


            </form>
        </div>

        @if($isPimpinan)
                <!-- Pimpinan Stats -->
                <div class="grid grid-cols-12 gap-6 mb-8">
                    <!-- Column 1: Total Achievements -->
                    <div
                        class="col-span-12 md:col-span-6 lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 transition-all hover:shadow-md">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl p-3">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest">Total
                                    Prestasi</p>
                                <p class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">
                                    {{ number_format($totalAchievements) }}
                                </p>
                                <span
                                    class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 dark:bg-emerald-900/10 text-emerald-600 dark:text-emerald-400">Terverifikasi
                                    Valid</span>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Performa Ratio KPI -->
                    <div
                        class="col-span-12 md:col-span-6 lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 transition-all hover:shadow-md">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900/30 rounded-xl p-3">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest">Performa
                                    Mhs</p>
                                <div class="flex items-baseline gap-1.5 mt-1">
                                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">
                                        {{ number_format($achievementRatio, 2) }}
                                    </p>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">pres/mhs</span>
                                </div>
                                <div class="mt-1 flex items-center gap-1.5">
                                    @if($achievementGrowth >= 0)
                                        <span
                                            class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">+{{ number_format($achievementGrowth, 1) }}%</span>
                                    @else
                                        <span
                                            class="text-[11px] font-bold text-red-600 dark:text-red-400">{{ number_format($achievementGrowth, 1) }}%</span>
                                    @endif
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">vs periode lalu</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 3: Level Split Percentage -->
                    <div
                        class="col-span-12 md:col-span-6 lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 transition-all hover:shadow-md">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl p-3">
                                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest">Split
                                    Tingkat</p>
                                <div class="mt-2 space-y-1.5">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="text-gray-500 dark:text-gray-400">Intl:
                                            {{ number_format($internationalPercentage, 0) }}%</span>
                                        <span class="text-gray-500 dark:text-gray-400">Nas:
                                            {{ number_format($nationalPercentage, 1) }}%</span>
                                    </div>
                                    <div class="w-full flex bg-gray-100 dark:bg-gray-700 rounded-full h-1 overflow-hidden">
                                        <div class="bg-indigo-500 h-1" style="width: {{ $internationalPercentage }}%"></div>
                                        <div class="bg-emerald-500 h-1" style="width: {{ $nationalPercentage }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 4: Active Units KPI -->
                    <div
                        class="col-span-12 md:col-span-6 lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 transition-all hover:shadow-md">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-amber-100 dark:bg-amber-900/30 rounded-xl p-3">
                                <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest">
                                    {{ $unitLabel }} Aktif
                                </p>
                                <div class="flex items-baseline gap-1.5 mt-1">
                                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $activeUnitsCount }}</p>
                                    <span class="text-[11px] text-gray-500">dari {{ $totalUnitsCount }}</span>
                                </div>
                                <div class="mt-2">
                                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1">
                                        <div class="bg-amber-500 h-1 rounded-full"
                                            style="width: {{ $totalUnitsCount > 0 ? ($activeUnitsCount / $totalUnitsCount) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">{{ $unitLabel }} berprestasi aktif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section - 12 Column Grid -->
            @if($isPimpinan)
                <div class="grid grid-cols-12 gap-6 mb-8">
                    @if(!empty($hierarchicalComparison) && isset($hierarchicalComparison['items']) && $hierarchicalComparison['items']->count() > 0)
                        <!-- Hierarchical Comparison Chart (col-span-6) -->
                        <div class="col-span-12 lg:col-span-6 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div class="mb-4">
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    <button id="backBtn"
                                        class="hidden items-center gap-1 px-2 py-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                        Kembali
                                    </button>
                                </div>
                                <h3 id="breadcrumb" class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $hierarchicalComparison['label'] }}
                                </h3>
                            </div>
                            <div class="relative h-64">
                                <canvas id="hierarchicalChart"></canvas>
                                <div id="chartLoading"
                                    class="hidden absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-800 bg-opacity-75">
                                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                            </circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Loading...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Efficiency Ratio Ranking (col-span-3) -->
                        <div class="col-span-12 lg:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Efisiensi Capaian Prestasi</h3>
                                <div class="group relative">
                                    <svg class="w-4 h-4 text-gray-400 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div
                                        class="absolute bottom-full right-0 mb-2 w-48 p-2 bg-gray-900 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                        Efisiensi: Jumlah prestasi dibagi populasi mahasiswa. Menunjukkan produktivitas unit.
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4 max-h-64 overflow-y-auto pr-1">
                                @forelse($efficiencyRanking as $rank)
                                    <div class="flex items-center justify-between">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate"
                                                title="{{ $rank['name'] }}">
                                                {{ $rank['name'] }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $rank['achievements'] }} Pres / {{ $rank['students'] }} Mhs
                                            </p>
                                        </div>
                                        <div class="ml-2 text-right">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                {{ number_format($rank['ratio'], 3) }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-8 text-center text-gray-500 dark:text-gray-400">
                                        <svg class="w-8 h-8 mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        <p class="text-xs">Data tidak tersedia</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Achievement Trend (col-span-3) -->
                        <div class="col-span-12 lg:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Analisis Tren Pertumbuhan</h3>
                            <div class="relative h-64">
                                <canvas id="achievementTrendChart"></canvas>
                            </div>
                        </div>
                    @else
                        <!-- No Data Message -->
                        <div class="col-span-12 lg:col-span-12 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Perbandingan Hierarkis</h3>
                            <div class="flex items-center justify-center h-64">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada data untuk perbandingan</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Row 3 - Specialized Distributions -->
                <div class="grid grid-cols-12 gap-6 mb-6">
                    <!-- Level Distribution Chart (col-span-4) -->
                    <div class="col-span-12 lg:col-span-4 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Komposisi Tingkat Capaian</h3>
                        <div class="relative h-64">
                            <canvas id="levelDistributionChart"></canvas>
                        </div>
                    </div>

                    <!-- Category Distribution Chart (col-span-4) -->
                    <div class="col-span-12 lg:col-span-4 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Komposisi Bidang Prestasi</h3>
                        <div class="relative h-64">
                            <canvas id="categoryDistributionChart"></canvas>
                        </div>
                    </div>

                    <!-- Indicator Risk & Insight List (col-span-4) -->
                    <div
                        class="col-span-12 lg:col-span-4 bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-amber-500 dark:border-amber-600">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pusat Kendali & Manajemen Risiko</h3>
                            <span
                                class="px-2 py-0.5 bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 text-xs font-bold rounded-full">
                                {{ count($riskIndicators) }} Poin
                            </span>
                        </div>
                        <div class="space-y-4">
                            @forelse($riskIndicators as $risk)
                                <div
                                    class="flex items-start gap-3 p-3 rounded-lg {{ $risk['type'] === 'danger' ? 'bg-red-50 dark:bg-red-900/10 text-red-700 dark:text-red-400' : 'bg-amber-50 dark:bg-amber-900/10 text-amber-700 dark:text-amber-400' }}">
                                    <div class="mt-0.5">
                                        @if($risk['icon'] === 'clock')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @elseif($risk['icon'] === 'trending-down')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                            </svg>
                                        @elseif($risk['icon'] === 'users')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <p class="text-xs font-medium leading-relaxed">{{ $risk['message'] }}</p>
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-12 text-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-sm">Semua metrik dalam batas aman</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif



            <!-- Top 3 IPK per Angkatan (Only for Kaprodi) - Single Row -->
            @if($level === 'program_study' && !empty($topGpaByAngkatan))
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top 3 IPK Tertinggi per Angkatan (3
                        Angkatan Terbaru)</h3>

                    <!-- Single Row with Horizontal Scroll -->
                    <div class="overflow-x-auto pb-4">
                        <div class="flex gap-6 min-w-max">
                            @foreach($topGpaByAngkatan as $angkatan => $students)
                                @if($students->count() > 0)
                                    <div
                                        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-[480px] flex-shrink-0 border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center justify-between mb-5 pb-4 border-b border-gray-200 dark:border-gray-700">
                                            <div>
                                                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Angkatan {{ $angkatan }}</h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Mahasiswa Berprestasi</p>
                                            </div>
                                            <span
                                                class="px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full text-sm font-semibold">
                                                {{ $students->count() }} mahasiswa
                                            </span>
                                        </div>
                                        <div class="space-y-3 max-h-[420px] overflow-y-auto pr-2">
                                            @php
                                                $currentRank = 0;
                                                $previousGpa = null;
                                                $displayRank = 0;
                                            @endphp
                                            @foreach($students as $index => $student)
                                                @php
                                                    // Increment actual position
                                                    $currentRank++;

                                                    // If GPA is different from previous, update display rank
                                                    if ($previousGpa === null || $student->gpa != $previousGpa) {
                                                        $displayRank = $currentRank;
                                                    }

                                                    $previousGpa = $student->gpa;

                                                    // Determine medal/badge style based on display rank
                                                    if ($displayRank === 1) {
                                                        $badgeBg = 'bg-yellow-100 dark:bg-yellow-900/30';
                                                        $badgeText = 'text-yellow-700 dark:text-yellow-400';
                                                        $medal = '🥇';
                                                    } elseif ($displayRank === 2) {
                                                        $badgeBg = 'bg-gray-200 dark:bg-gray-600';
                                                        $badgeText = 'text-gray-700 dark:text-gray-300';
                                                        $medal = '🥈';
                                                    } elseif ($displayRank === 3) {
                                                        $badgeBg = 'bg-orange-100 dark:bg-orange-900/30';
                                                        $badgeText = 'text-orange-700 dark:text-orange-400';
                                                        $medal = '🥉';
                                                    } else {
                                                        $badgeBg = 'bg-gray-100 dark:bg-gray-700';
                                                        $badgeText = 'text-gray-700 dark:text-gray-300';
                                                        $medal = '';
                                                    }
                                                @endphp
                                                <div
                                                    class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 hover:shadow-md">
                                                    <div
                                                        class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center {{ $badgeBg }} shadow-sm">
                                                        <span class="text-base font-bold {{ $badgeText }}">
                                                            {{ $medal ?: $displayRank }}
                                                        </span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-base font-semibold text-gray-900 dark:text-white mb-1"
                                                            title="{{ $student->name }}">
                                                            {{ $student->name }}
                                                        </p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $student->student_id }}</p>
                                                    </div>
                                                    <div class="flex items-center gap-3 flex-shrink-0">
                                                        <span
                                                            class="px-3.5 py-2 rounded-lg text-base font-bold shadow-sm
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        {{ $student->gpa >= 3.5 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                                                    ($student->gpa >= 3.0 ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' :
                                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300') }}">
                                                            {{ number_format($student->gpa, 2) }}
                                                        </span>
                                                        <a href="{{ route($routePrefix . '.students.show', $student->student_id) }}"
                                                            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 rounded-lg transition-colors shadow-sm">
                                                            Detail
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M9 5l7 7-7 7" />
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Scroll Hint -->
                    @if(count($topGpaByAngkatan) > 1)
                        <div class="flex items-center justify-center gap-2 mt-3">
                            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                                Geser untuk melihat angkatan lainnya
                            </p>
                            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    @endif
                </div>
            @endif
        @else
        <!-- Validator Pending Achievements Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Menunggu Validasi</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Klik prestasi untuk validasi</p>
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Total: <strong class="text-gray-900 dark:text-white">{{ $pendingAchievements->total() }}</strong>
                        prestasi
                    </span>
                </div>

                <!-- Filter Section -->
                <form method="GET" action="{{ route($routePrefix . '.dashboard') }}" class="space-y-3">
                    @foreach($selectedPeriods as $periodId)
                        <input type="hidden" name="periods[]" value="{{ $periodId }}">
                    @endforeach
                    <div class="flex flex-col md:flex-row gap-3">
                        <!-- Search -->
                        <div class="flex-1">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Cari nama mahasiswa, NIM, atau event..."
                                    class="pl-10 w-full py-3 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>

                        <!-- Filter Category -->
                        <select name="category" onchange="this.form.submit()"
                            class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Filter Level -->
                        <select name="level" onchange="this.form.submit()"
                            class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500">
                            <option value="">Semua Level</option>
                            @foreach($levels as $level)
                                <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                                    {{ $level }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Buttons -->
                        <div class="flex gap-2">
                            <button type="submit"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors">
                                Filter
                            </button>
                            @if(request()->hasAny(['search', 'category', 'level']))
                                <a href="{{ route($routePrefix . '.dashboard', ['periods' => $selectedPeriods]) }}"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Mahasiswa</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Prestasi</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Kategori</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tingkat</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tanggal Submit</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Dokumen</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($pendingAchievements as $achievement)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center">
                                                <span
                                                    class="text-emerald-600 dark:text-emerald-400 font-medium">{{ substr($achievement->student?->name ?? 'N', 0, 1) }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $achievement->student?->name }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $achievement->student_id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $achievement->event_name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $achievement->organizer }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-block px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                            {{ $achievement->achievement->category->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    {{ $achievement->level === 'Internasional' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' :
                            ($achievement->level === 'Nasional' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' :
                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400') }}">
                                            {{ $achievement->level }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $achievement->submitted_at?->format('d M Y') }}
                                        <div class="text-xs text-gray-400">{{ $achievement->submitted_at?->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $achievement->documents->count() }} dokumen
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('achievements.documents.index', $achievement) }}"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                                                title="Upload Dokumen">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                            </a>
                                            <button type="button" @click="openModal({{ $achievement->sa_id }})"
                                                class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300 font-medium"
                                                title="Validasi">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada prestasi yang perlu
                                        divalidasi</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($pendingAchievements->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $pendingAchievements->links() }}
                </div>
            @endif
        </div>
    @endif
    <!-- End of Pimpinan/Validator conditional -->


    @unless($isPimpinan)
        <!-- Modal Validasi (Only for Validator) -->
        <div x-show="showModal" x-cloak @click.self="closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div @click.away="closeModal()"
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">

                <!-- Loading State -->
                <div x-show="loading" class="p-12 text-center">
                    <svg class="animate-spin h-12 w-12 mx-auto text-emerald-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">Memuat data...</p>
                </div>

                <!-- Content -->
                <div x-show="!loading && achievement" style="display: none;">
                    <!-- Modal Header -->
                    <div
                        class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between z-10">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="achievement?.event_name">
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400"
                                    x-text="achievement?.student?.name + ' - ' + achievement?.student_id"></p>
                            </div>
                        </div>
                        <button @click="closeModal()"
                            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-6">
                        <!-- Student & Achievement Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Informasi Mahasiswa</h4>
                                <dl class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500 dark:text-gray-400">Nama:</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white"
                                            x-text="achievement?.student?.name"></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500 dark:text-gray-400">NIM:</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white" x-text="achievement?.student_id">
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500 dark:text-gray-400">Fakultas:</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white"
                                            x-text="achievement?.student?.faculty || '-'"></dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Informasi Prestasi</h4>
                                <dl class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500 dark:text-gray-400">Tingkat:</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white" x-text="achievement?.level">
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500 dark:text-gray-400">Penyelenggara:</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white" x-text="achievement?.organizer">
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500 dark:text-gray-400">Tanggal:</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white" x-text="achievement?.event_date">
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Documents -->
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Dokumen (<span
                                    x-text="achievement?.documents?.length || 0"></span>)</h4>
                            <div class="space-y-2">
                                <template x-for="doc in achievement?.documents" :key="doc.id">
                                    <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white"
                                                    x-text="doc.type_name"></p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="doc.file_name">
                                                </p>
                                            </div>
                                        </div>
                                        <a :href="'/achievements/documents/' + doc.id + '/preview'" target="_blank"
                                            class="px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40">
                                            Lihat
                                        </a>
                                    </div>
                                </template>
                                <div x-show="!achievement?.documents || achievement?.documents?.length === 0"
                                    class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
                                    Belum ada dokumen
                                </div>
                            </div>
                        </div>

                        <!-- Validation Form -->
                        <form :action="'/validator/pending/' + achievement?.sa_id + '/validate'" method="POST"
                            id="validationForm">
                            @csrf
                            <div class="space-y-4">
                                <!-- Pesan Instruksi -->
                                <div x-show="!validationAction"
                                    class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                    <p class="text-sm text-blue-700 dark:text-blue-400">
                                        <strong>Instruksi:</strong> Silakan pilih salah satu aksi validasi di bawah ini
                                        (Setujui, Tolak, atau Minta Revisi).
                                    </p>
                                </div>

                                <!-- Notes -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                                        Catatan Validasi <span class="text-gray-400 text-xs">(Opsional)</span>
                                    </label>
                                    <textarea name="notes" rows="3" placeholder="Tambahkan catatan untuk validasi ini..."
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"></textarea>
                                </div>

                                <!-- Dynamic Fields -->
                                <div x-show="validationAction === 'reject'" x-cloak
                                    class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                    <label class="block text-sm font-medium text-red-700 dark:text-red-400 mb-2">
                                        Alasan Penolakan <span class="text-red-500">*</span>
                                    </label>
                                    <textarea name="rejection_reason" rows="3" :required="validationAction === 'reject'"
                                        placeholder="Jelaskan alasan penolakan prestasi ini..."
                                        class="w-full px-4 py-2 border border-red-300 dark:border-red-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-2">* Wajib diisi</p>
                                </div>

                                <div x-show="validationAction === 'request_revision'" x-cloak
                                    class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                    <label class="block text-sm font-medium text-blue-700 dark:text-blue-400 mb-2">
                                        Alasan Permintaan Revisi <span class="text-blue-500">*</span>
                                    </label>
                                    <textarea name="revision_reason" rows="3"
                                        :required="validationAction === 'request_revision'"
                                        placeholder="Jelaskan dokumen apa yang perlu diperbaiki atau dilengkapi..."
                                        class="w-full px-4 py-2 border border-blue-300 dark:border-blue-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">* Wajib diisi</p>
                                </div>

                                <div x-show="validationAction === 'approve'" x-cloak class="space-y-3">
                                    <div
                                        class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg">
                                        <div class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div>
                                                <p class="text-sm font-medium text-emerald-700 dark:text-emerald-400">
                                                    Persetujuan Tingkat Fakultas
                                                </p>
                                                <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">
                                                    Prestasi yang Anda setujui akan dikirim ke Admin Universitas untuk
                                                    review
                                                    final dan penerbitan SK.
                                                    SK akan di-assign oleh Admin Universitas, bukan di tingkat fakultas.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Modal Footer -->
                    <div
                        class="sticky bottom-0 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 px-6 py-4 space-y-3">
                        <!-- Tombol Pilih Aksi (tampil jika belum ada aksi dipilih) -->
                        <div x-show="!validationAction" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <button type="button" @click="validationAction = 'approve'"
                                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Setujui
                            </button>
                            <button type="button" @click="validationAction = 'reject'"
                                class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Tolak
                            </button>
                            <button type="button" @click="validationAction = 'request_revision'"
                                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Minta Revisi
                            </button>
                        </div>

                        <!-- Tombol Submit & Kembali (tampil setelah aksi dipilih) -->
                        <div x-show="validationAction" class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <button type="button" @click="validationAction = ''"
                                            class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition-colors">
                                            ← Kembali
                                        </button>
                                        <button type="button" @click="submitValidation()"
                                            class="px-4 py-2.5 text-white rounded-lg font-medium transition-colors"
                                            :class="{
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            'bg-emerald-600 hover:bg-emerald-700': validationAction === 'approve',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            'bg-red-600 hover:bg-red-700': validationAction === 'reject',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            'bg-blue-600 hover:bg-blue-700': validationAction === 'request_revision'
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        }">
                                            <span x-show="validationAction === 'approve'">✓ Kirim Persetujuan</span>
                                            <span x-show="validationAction === 'reject'">✗ Kirim Penolakan</span>
                                            <span x-show="validationAction === 'request_revision'">↻ Kirim Permintaan Revisi</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Tombol Tutup -->
                                <button type="button" @click="closeModal()"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition-colors">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
    @endunless
        <!-- End Modal Validasi -->

        <!-- Modal Peserta Event (Pop-up Rincian Peserta Lomba) -->
        <div x-show="showEventModal" x-cloak @click.self="closeEventModal()"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div @click.away="closeEventModal()"
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden">

                <!-- Modal Header -->
                <div
                    class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="eventInfo.name"></h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400"
                                x-text="eventInfo.organizer + ' • ' + eventInfo.level"></p>
                        </div>
                    </div>
                    <button @click="closeEventModal()"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto">
                    <!-- Loading State -->
                    <div x-show="eventLoading" class="py-12 text-center">
                        <svg class="animate-spin h-10 w-10 mx-auto text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <p class="mt-4 text-gray-600 dark:text-gray-400">Memuat rincian peserta...</p>
                    </div>

                    <!-- Participants List -->
                    <div x-show="!eventLoading" class="space-y-4">
                        <div class="overflow-hidden border border-gray-200 dark:border-gray-700 rounded-xl">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Mahasiswa</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Prodi / Fakultas</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Tgl Submit</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="participant in eventParticipants" :key="participant.student_id">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div
                                                        class="flex-shrink-0 h-9 w-9 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                                        <span class="text-blue-600 dark:text-blue-400 font-bold text-sm"
                                                            x-text="participant.student_name.charAt(0)"></span>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-semibold text-gray-900 dark:text-white"
                                                            x-text="participant.student_name"></div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400"
                                                            x-text="participant.student_id"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900 dark:text-white"
                                                    x-text="participant.program_study"></div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400"
                                                    x-text="participant.faculty"></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                                x-text="participant.submitted_at"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <a :href="participant.details_url"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40">
                                                    Profil
                                                </a>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button @click="closeEventModal()"
                        class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
        <!-- End Modal Peserta Event -->
        </div>

        <script>
            function validatorDashboard() {
                return {
                    showModal: false,
                    loading: false,
                    achievement: null,
                    validationAction: '',
                    showCategoryHierarchy: false,
                    showLevelHierarchy: false,

                    // Event Modal State
                    showEventModal: false,
                    eventLoading: false,
                    eventParticipants: [],
                    eventInfo: { name: '', organizer: '', level: '' },

                    async openEventModal(eventName, organizer, level) {
                        this.showEventModal = true;
                        this.eventLoading = true;
                        this.eventInfo = { name: eventName, organizer: organizer, level: level };
                        this.eventParticipants = [];

                        // Add state to history so back button closes modal
                        if (!window.location.hash.includes('event-modal')) {
                            window.history.pushState({ modal: 'event' }, '', window.location.pathname + window.location.search + '#event-modal');
                        }

                        try {
                            const params = new URLSearchParams({
                                event_name: eventName,
                                organizer: organizer,
                                level: level
                            });
                            const response = await fetch("{{ route($routePrefix . '.api.event-participants') }}?" + params);
                            const data = await response.json();
                            this.eventParticipants = data.participants;
                        } catch (error) {
                            console.error('Error loading participants:', error);
                            alert('Gagal memuat rincian peserta');
                        } finally {
                            this.eventLoading = false;
                        }
                    },

                    closeEventModal() {
                        this.showEventModal = false;
                        if (window.location.hash.includes('event-modal')) {
                            window.history.back();
                        }
                    },

                    init() {
                        // Handle browser back button for modal and chart
                        window.addEventListener('popstate', (event) => {
                            if (this.showEventModal && !window.location.hash.includes('event-modal')) {
                                this.showEventModal = false;
                            }
                        });
                    },

                    async openModal(achievementId) {
                        this.showModal = true;
                        this.loading = true;
                        this.validationAction = '';

                        try {
                            const response = await fetch(`/api/validator/achievements/${achievementId}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin'
                            });

                            if (!response.ok) {
                                const errorText = await response.text();
                                console.error('Response error:', errorText);
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }

                            const data = await response.json();

                            if (data.error) {
                                throw new Error(data.error);
                            }

                            this.achievement = data;
                        } catch (error) {
                            console.error('Error loading achievement:', error);
                            alert('Gagal memuat data prestasi: ' + error.message);
                            this.closeModal();
                        } finally {
                            this.loading = false;
                        }
                    },

                    closeModal() {
                        this.showModal = false;
                        this.achievement = null;
                        this.validationAction = '';
                    },

                    submitValidation() {
                        const form = document.getElementById('validationForm');
                        const action = this.validationAction;

                        // Validasi berdasarkan aksi
                        if (action === 'reject') {
                            const rejectReason = form.querySelector('[name="rejection_reason"]');
                            if (!rejectReason || !rejectReason.value.trim()) {
                                // Scroll ke field alasan penolakan
                                rejectReason.focus();
                                rejectReason.classList.add('border-red-500');
                                setTimeout(() => rejectReason.classList.remove('border-red-500'), 2000);
                                return;
                            }
                        } else if (action === 'request_revision') {
                            const revisionReason = form.querySelector('[name="revision_reason"]');
                            if (!revisionReason || !revisionReason.value.trim()) {
                                // Scroll ke field alasan revisi
                                revisionReason.focus();
                                revisionReason.classList.add('border-red-500');
                                setTimeout(() => revisionReason.classList.remove('border-red-500'), 2000);
                                return;
                            }
                        }

                        // Tambahkan action ke form
                        const actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = action;
                        form.appendChild(actionInput);

                        // Submit form
                        form.submit();
                    }
                }
            }

            // Initialize Charts for Pimpinan Dashboard
            @if($isPimpinan)
                document.addEventListener('DOMContentLoaded', function () {
                    // Helper to get dynamic colors based on current theme
                    const getChartColors = () => {
                        const isDark = document.documentElement.classList.contains('dark');
                        return {
                            textColor: isDark ? '#f3f4f6' : '#374151',
                            gridColor: isDark ? '#374151' : '#e5e7eb',
                            tooltipBg: isDark ? '#1f2937' : '#ffffff',
                            tooltipTitle: isDark ? '#f9fafb' : '#111827',
                            tooltipBody: isDark ? '#d1d5db' : '#374151',
                            tooltipBorder: isDark ? '#374151' : '#e5e7eb'
                        };
                    };

                    let colors = getChartColors();
                    const chartInstances = [];

                    // Achievement Trend Chart
                    const trendData = @json($achievementTrend);
                    const trendCanvas = document.getElementById('achievementTrendChart');
                    if (trendCanvas && trendData && trendData.length > 0) {
                        const trendChart = new Chart(trendCanvas, {
                            type: 'line',
                            data: {
                                labels: trendData.map(d => d.label),
                                datasets: [
                                    {
                                        label: 'Bidang Akademik',
                                        data: trendData.map(d => d.academic),
                                        borderColor: '#3b82f6',
                                        backgroundColor: '#3b82f620',
                                        fill: true,
                                        tension: 0.4,
                                        borderWidth: 2,
                                        pointRadius: 3,
                                        pointBackgroundColor: '#3b82f6'
                                    },
                                    {
                                        label: 'Bidang Non-Akademik',
                                        data: trendData.map(d => d.non_academic),
                                        borderColor: '#10b981',
                                        backgroundColor: '#10b98120',
                                        fill: true,
                                        tension: 0.4,
                                        borderWidth: 2,
                                        pointRadius: 3,
                                        pointBackgroundColor: '#10b981'
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
                                            color: colors.textColor,
                                            boxWidth: 8,
                                            usePointStyle: true,
                                            pointStyle: 'circle',
                                            font: { size: 10 }
                                        }
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                        backgroundColor: colors.tooltipBg,
                                        titleColor: colors.tooltipTitle,
                                        bodyColor: colors.tooltipBody,
                                        borderColor: colors.tooltipBorder,
                                        borderWidth: 1
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: { display: false },
                                        ticks: { color: colors.textColor, font: { size: 10 } }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        grid: { color: colors.gridColor },
                                        ticks: { color: colors.textColor, font: { size: 10 }, stepSize: 1 }
                                    }
                                }
                            }
                        });
                        chartInstances.push(trendChart);
                    }

                    // Level Distribution Donut Chart
                    const levelData = @json($levelDistribution);
                    const levelCanvas = document.getElementById('levelDistributionChart');
                    if (levelCanvas && levelData && levelData.length > 0 && levelData.some(d => d.total > 0)) {
                        const levelChart = new Chart(levelCanvas, {
                            type: 'doughnut',
                            data: {
                                labels: levelData.map(d => d.name),
                                datasets: [{
                                    data: levelData.map(d => d.total),
                                    backgroundColor: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
                                    borderWidth: 2,
                                    borderColor: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff',
                                    hoverOffset: 15
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            color: colors.textColor,
                                            padding: 15,
                                            font: { size: 10 },
                                            usePointStyle: true,
                                            boxWidth: 8
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: colors.tooltipBg,
                                        titleColor: colors.tooltipTitle,
                                        bodyColor: colors.tooltipBody,
                                        borderColor: colors.tooltipBorder,
                                        borderWidth: 1,
                                        callbacks: {
                                            label: function (context) {
                                                const label = context.label || '';
                                                const value = context.parsed || 0;
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = ((value / total) * 100).toFixed(1);
                                                return ` ${label}: ${value} Capaian (${percentage}%)`;
                                            }
                                        }
                                    }
                                },
                                cutout: '65%'
                            }
                        });
                        chartInstances.push(levelChart);
                    } else if (levelCanvas) {
                        levelCanvas.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">Tidak ada data distribusi tingkat</div>';
                    }

                    // Category Distribution Donut Chart
                    const categoryData = @json($categoryDistribution);
                    const categoryCanvas = document.getElementById('categoryDistributionChart');
                    if (categoryCanvas && categoryData && categoryData.length > 0 && categoryData.some(d => d.total > 0)) {
                        const categoryColors = [
                            '#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444',
                            '#ec4899', '#06b6d4', '#f97316', '#84cc16', '#6366f1'
                        ];

                        const categoryChart = new Chart(categoryCanvas, {
                            type: 'doughnut',
                            data: {
                                labels: categoryData.map(d => d.name),
                                datasets: [{
                                    data: categoryData.map(d => d.total),
                                    backgroundColor: categoryColors,
                                    borderWidth: 2,
                                    borderColor: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff',
                                    hoverOffset: 15
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            color: colors.textColor,
                                            padding: 15,
                                            font: { size: 10 },
                                            usePointStyle: true,
                                            boxWidth: 8
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: colors.tooltipBg,
                                        titleColor: colors.tooltipTitle,
                                        bodyColor: colors.tooltipBody,
                                        borderColor: colors.tooltipBorder,
                                        borderWidth: 1,
                                        callbacks: {
                                            label: function (context) {
                                                const label = context.label || '';
                                                const value = context.parsed || 0;
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = ((value / total) * 100).toFixed(1);
                                                return ` ${label}: ${value} Capaian (${percentage}%)`;
                                            }
                                        }
                                    }
                                },
                                cutout: '65%'
                            }
                        });
                        chartInstances.push(categoryChart);
                    } else if (categoryCanvas) {
                        categoryCanvas.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">Tidak ada data distribusi kategori</div>';
                    }

                    // Hierarchical Chart functionality
                    let hierarchicalChart = null;
                    let currentDrillLevel = 'main';
                    let drillHistory = [];
                    let currentPeriods = @json($selectedPeriods);

                    // Initialize hierarchical chart
                    function initHierarchicalChart() {
                        const canvas = document.getElementById('hierarchicalChart');
                        if (!canvas) {
                            return;
                        }

                        loadHierarchicalData('main');
                    }

                    // Load hierarchical data via AJAX
                    function loadHierarchicalData(drillLevel, parentId = null, grandparentId = null) {
                        const loading = document.getElementById('chartLoading');
                        const backBtn = document.getElementById('backBtn');

                        if (loading) loading.classList.remove('hidden');

                        const params = new URLSearchParams({
                            drill_level: drillLevel
                        });

                        // Add periods as separate parameters
                        if (currentPeriods && currentPeriods.length > 0) {
                            currentPeriods.forEach(period => {
                                params.append('periods[]', period);
                            });
                        }

                        if (parentId) params.append('parent_id', parentId);
                        if (grandparentId) params.append('grandparent_id', grandparentId);

                        fetch(`{{ route($routePrefix . '.api.hierarchical-chart-data') }}?${params}`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (loading) loading.classList.add('hidden');
                                updateHierarchicalChart(data);
                                updateBreadcrumb(data.title);

                                // Show/hide back button
                                if (backBtn) {
                                    if (drillLevel !== 'main') {
                                        backBtn.classList.remove('hidden');
                                        backBtn.classList.add('flex');
                                    } else {
                                        backBtn.classList.add('hidden');
                                        backBtn.classList.remove('flex');
                                    }
                                }

                                currentDrillLevel = drillLevel;
                            })
                            .catch(error => {
                                console.error('Error loading hierarchical data:', error);
                                if (loading) loading.classList.add('hidden');

                                // Fallback: create static chart with existing data
                                @php
                                    $hasItems = !empty($hierarchicalComparison) && isset($hierarchicalComparison['items']) && $hierarchicalComparison['items']->count() > 0;
                                    $labelField = ($hierarchicalComparison['type'] ?? '') === 'faculty' ? 'faculty' :
                                        (($hierarchicalComparison['type'] ?? '') === 'department' ? 'department' :
                                            (($hierarchicalComparison['type'] ?? '') === 'angkatan' ? 'angkatan_label' : 'program_study'));
                                @endphp
                                const fallbackData = {
                                    labels: @json($hasItems ? $hierarchicalComparison['items']->pluck($labelField) : []),
                                    values: @json($hasItems ? $hierarchicalComparison['items']->pluck('achievements_count') : []),
                                    title: @json($hierarchicalComparison['label'] ?? 'Perbandingan Prestasi'),
                                    canDrillDown: false,
                                    drillData: @json($hasItems ? $hierarchicalComparison['items']->values()->map(function ($item) {
                                        return [
                                            'id' => $item->faculty_id ?? $item->department_id ?? $item->program_study_id ?? $item->angkatan,
                                            'name' => $item->faculty ?? $item->department ?? $item->program_study ?? $item->angkatan_label,
                                            'students_count' => $item->students_count ?? 0
                                        ];
                                    }) : [])
                                };

                                if (fallbackData.labels.length > 0) {
                                    updateHierarchicalChart(fallbackData);
                                    updateBreadcrumb(fallbackData.title);
                                } else {
                                    // Show error message
                                    const canvas = document.getElementById('hierarchicalChart');
                                    if (canvas) {
                                        canvas.parentElement.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400 py-8">Tidak ada data untuk ditampilkan</p>';
                                    }
                                }
                            });
                    }

                    // Update hierarchical chart
                    function updateHierarchicalChart(data) {
                        const canvas = document.getElementById('hierarchicalChart');
                        if (!canvas) return;

                        const ctx = canvas.getContext('2d');
                        const colors = getChartColors(); // Get latest colors

                        // Destroy existing chart
                        if (hierarchicalChart) {
                            hierarchicalChart.destroy();
                            // Also remove from chartInstances if it exists there
                            const idx = chartInstances.indexOf(hierarchicalChart);
                            if (idx > -1) chartInstances.splice(idx, 1);
                        }

                        // Generate colors
                        const chartBarColors = [
                            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                            '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#6366f1',
                            '#14b8a6', '#eab308'
                        ];

                        hierarchicalChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: data.labels,
                                datasets: [{
                                    label: 'Akumulasi Prestasi',
                                    data: data.values,
                                    backgroundColor: chartBarColors.slice(0, data.labels.length),
                                    borderColor: chartBarColors.slice(0, data.labels.length),
                                    borderWidth: 1,
                                    borderRadius: 6,
                                    borderSkipped: false
                                }]
                            },
                            options: {
                                indexAxis: 'y', // Better for long labels
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        backgroundColor: colors.tooltipBg,
                                        titleColor: colors.tooltipTitle,
                                        bodyColor: colors.tooltipBody,
                                        borderColor: colors.tooltipBorder,
                                        borderWidth: 1,
                                        padding: 12,
                                        callbacks: {
                                            label: function (context) {
                                                const value = context.parsed.x; // Use x for indexAxis: y
                                                const total = data.values.reduce((a, b) => a + b, 0);
                                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                                return `Total Capaian: ${value} (${percentage}%)`;
                                            },
                                            afterLabel: function (context) {
                                                const index = context.dataIndex;
                                                const students = (data.drillData && data.drillData[index]) ?
                                                    data.drillData[index].students_count : 0;
                                                let label = `Basis Populasi: ${students.toLocaleString()} Mahasiswa`;
                                                if (data.canDrillDown) {
                                                    label += '\n\nKlik untuk drill down';
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        beginAtZero: true,
                                        ticks: {
                                            color: colors.textColor,
                                            font: { size: 11 }
                                        },
                                        grid: {
                                            color: colors.gridColor
                                        }
                                    },
                                    y: {
                                        ticks: {
                                            color: colors.textColor,
                                            font: { size: 11, weight: '500' }
                                        },
                                        grid: {
                                            display: false
                                        }
                                    }
                                },
                                onClick: (event, elements) => {
                                    if (elements.length > 0 && data.canDrillDown) {
                                        const index = elements[0].index;
                                        const drillItem = data.drillData[index];

                                        // Save current state to history
                                        drillHistory.push({
                                            level: currentDrillLevel,
                                            data: data,
                                            title: data.title
                                        });

                                        // Determine next drill level
                                        let nextLevel = 'level1';
                                        if (currentDrillLevel === 'level1') {
                                            nextLevel = 'level2';
                                        }

                                        // Save state to browser history
                                        window.history.pushState({
                                            type: 'chart-drill',
                                            level: nextLevel,
                                            parentId: drillItem.id,
                                            title: drillItem.name
                                        }, '', window.location.pathname + window.location.search);

                                        // Load next level data
                                        loadHierarchicalData(nextLevel, drillItem.id);
                                    }
                                }
                            }
                        });
                        chartInstances.push(hierarchicalChart);
                    }

                    // Update breadcrumb
                    function updateBreadcrumb(title) {
                        const breadcrumb = document.getElementById('breadcrumb');
                        if (breadcrumb) {
                            breadcrumb.textContent = title;
                        }
                    }

                    // Back button functionality
                    const backBtn = document.getElementById('backBtn');
                    if (backBtn) {
                        backBtn.addEventListener('click', function () {
                            window.history.back();
                        });
                    }

                    // Change charts theme dynamically
                    function updateChartsTheme() {
                        const newColors = getChartColors();
                        const isDark = document.documentElement.classList.contains('dark');

                        chartInstances.forEach(chart => {
                            // Update global plugin options
                            if (chart.options.plugins.legend) {
                                chart.options.plugins.legend.labels.color = newColors.textColor;
                            }

                            // Update tooltips
                            if (chart.options.plugins.tooltip) {
                                chart.options.plugins.tooltip.backgroundColor = newColors.tooltipBg;
                                chart.options.plugins.tooltip.titleColor = newColors.tooltipTitle;
                                chart.options.plugins.tooltip.bodyColor = newColors.tooltipBody;
                                chart.options.plugins.tooltip.borderColor = newColors.tooltipBorder;
                            }

                            // Update scales for line/bar charts
                            if (chart.options.scales) {
                                if (chart.options.scales.x) {
                                    chart.options.scales.x.ticks.color = newColors.textColor;
                                    if (chart.options.scales.x.grid) {
                                        chart.options.scales.x.grid.color = newColors.gridColor;
                                    }
                                }
                                if (chart.options.scales.y) {
                                    chart.options.scales.y.ticks.color = newColors.textColor;
                                    if (chart.options.scales.y.grid) {
                                        chart.options.scales.y.grid.color = newColors.gridColor;
                                    }
                                }
                            }

                            // Update doughnut specific options
                            if (chart.config.type === 'doughnut') {
                                chart.data.datasets.forEach(dataset => {
                                    dataset.borderColor = isDark ? '#1f2937' : '#ffffff';
                                });
                            }

                            chart.update();
                        });
                    }

                    // Monitor class changes on html tag to detect dark mode toggle
                    const observer = new MutationObserver((mutations) => {
                        mutations.forEach((mutation) => {
                            if (mutation.attributeName === 'class') {
                                updateChartsTheme();
                            }
                        });
                    });

                    observer.observe(document.documentElement, { attributes: true });

                    // Handle drill-back via popstate
                    window.addEventListener('popstate', function (event) {
                        if (event.state && event.state.type === 'chart-drill') {
                            if (drillHistory.length > 0) {
                                const previousState = drillHistory.pop();
                                currentDrillLevel = previousState.level;
                                updateHierarchicalChart(previousState.data);
                                updateBreadcrumb(previousState.title);

                                if (drillHistory.length === 0 && backBtn) {
                                    backBtn.classList.add('hidden');
                                    backBtn.classList.remove('flex');
                                }
                            }
                        } else if (!event.state || event.state.type !== 'chart-drill') {
                            if (drillHistory.length > 0) {
                                const previousState = drillHistory.pop();
                                currentDrillLevel = previousState.level;
                                updateHierarchicalChart(previousState.data);
                                updateBreadcrumb(previousState.title);

                                if (drillHistory.length === 0 && backBtn) {
                                    backBtn.classList.add('hidden');
                                    backBtn.classList.remove('flex');
                                }
                            }
                        }
                    });

                    // Initialize hierarchical chart on page load
                    @if(!empty($hierarchicalComparison) && isset($hierarchicalComparison['items']) && $hierarchicalComparison['items']->count() > 0)
                        initHierarchicalChart();
                    @endif
                                            });
            @endif
        </script>
@endsection