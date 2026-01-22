@extends('layouts.admin')

@section('title', 'Dashboard Monitoring Prestasi')

@section('content')
<div class="space-y-6">
    <!-- Header with Period Filter -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Monitoring Prestasi</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Pantau dan analisis prestasi mahasiswa secara real-time</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Period Filter -->
            <form method="GET" action="{{ route('admin.achievements.dashboard') }}" class="flex items-center gap-2">
                <select name="period" onchange="this.form.submit()" 
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                    <option value="all" {{ !$selectedPeriod ? 'selected' : '' }}>Semua Periode</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ $selectedPeriod && $selectedPeriod->id == $period->id ? 'selected' : '' }}>
                            {{ $period->name }} {{ $period->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>
            
            <a href="{{ route('admin.achievements.dashboard.export', ['format' => 'excel', 'period' => $selectedPeriod?->id]) }}" 
                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Data
            </a>
        </div>
    </div>

    <!-- Period Info Badge -->
    @if($selectedPeriod)
    <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-purple-100 dark:bg-purple-900/40 rounded-lg">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-purple-900 dark:text-purple-100">Menampilkan data untuk periode:</p>
                <p class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $selectedPeriod->name }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-xs text-purple-600 dark:text-purple-400">{{ $selectedPeriod->start_date->format('d M Y') }} - {{ $selectedPeriod->end_date->format('d M Y') }}</p>
            @if($selectedPeriod->is_active)
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs rounded-full mt-1">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    Periode Aktif
                </span>
            @endif
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Prestasi -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-white/20 backdrop-blur-sm rounded-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
            </div>
            <p class="text-purple-100 text-sm font-medium">Total Prestasi</p>
            <p class="text-4xl font-bold mt-2">{{ number_format($statistics['total']) }}</p>
            <p class="text-purple-100 text-xs mt-2">Semua prestasi yang tercatat</p>
        </div>

        <!-- Pending Review -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Menunggu Review</p>
            <p class="text-4xl font-bold text-yellow-600 dark:text-yellow-400 mt-2">{{ number_format($statistics['pending']) }}</p>
            <a href="{{ route('admin.achievements.validation.index', ['status' => 'pending']) }}" class="text-yellow-600 dark:text-yellow-400 text-xs mt-2 inline-flex items-center gap-1 hover:gap-2 transition-all">
                Lihat detail
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Approval Rate -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Tingkat Persetujuan</p>
            <p class="text-4xl font-bold text-green-600 dark:text-green-400 mt-2">{{ $statistics['approval_rate'] }}%</p>
            <div class="mt-3 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-green-600 dark:bg-green-400 h-2 rounded-full transition-all" style="width: {{ $statistics['approval_rate'] }}%"></div>
            </div>
        </div>

        <!-- Avg Time to Approve -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Rata-rata Waktu Review</p>
            <p class="text-4xl font-bold text-blue-600 dark:text-blue-400 mt-2">{{ $statistics['avg_time_to_approve'] }}</p>
            <p class="text-gray-500 dark:text-gray-400 text-xs mt-2">hari untuk approve</p>
        </div>
    </div>

    <!-- Charts Row -->
    @if($periodComparison)
    <!-- Period Comparison Chart (when "Semua Periode" selected) -->
    <div class="grid grid-cols-1 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Perbandingan Prestasi Per Periode</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Distribusi dan status prestasi di setiap periode akademik</p>
            </div>
            <div class="h-96">
                <canvas id="periodComparisonChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if(!$periodComparison)
        <!-- Monthly Trend Chart (only show when specific period selected) -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Trend Submission 
                        @if($selectedPeriod)
                            <span class="text-purple-600 dark:text-purple-400">{{ $selectedPeriod->semester }} {{ $selectedPeriod->year }}</span>
                        @else
                            Bulanan
                        @endif
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        @if($selectedPeriod)
                            {{ $selectedPeriod->start_date->format('M Y') }} - {{ $selectedPeriod->end_date->format('M Y') }}
                        @else
                            Perbandingan pengajuan dan persetujuan
                        @endif
                    </p>
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
            <div class="h-72">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>
        @endif

        <!-- Level Distribution Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Distribusi Tingkat Lomba</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Berdasarkan tingkat kompetisi</p>
            </div>
            <div class="h-72 flex items-center justify-center">
                <canvas id="levelDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- HIGH PRIORITY FEATURES -->

    <!-- Top Performers & Faculty Comparison -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Performers -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Top 10 Mahasiswa Berprestasi
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Mahasiswa dengan prestasi terbanyak</p>
                </div>
            </div>
            @if($topPerformers->isEmpty())
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Belum ada data</p>
                </div>
            @else
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($topPerformers as $index => $student)
                    <div class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex-shrink-0">
                            @if($index < 3)
                                <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $index === 0 ? 'bg-yellow-100 dark:bg-yellow-900/30' : ($index === 1 ? 'bg-gray-100 dark:bg-gray-700' : 'bg-orange-100 dark:bg-orange-900/30') }}">
                                    <span class="text-lg font-bold {{ $index === 0 ? 'text-yellow-600 dark:text-yellow-400' : ($index === 1 ? 'text-gray-600 dark:text-gray-400' : 'text-orange-600 dark:text-orange-400') }}">
                                        {{ $index + 1 }}
                                    </span>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                    <span class="text-sm font-semibold text-purple-600 dark:text-purple-400">{{ $index + 1 }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $student->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $student->student_id }} • {{ $student->faculty ?? 'N/A' }}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 text-sm font-bold rounded-full">
                                {{ $student->achievements_count }} prestasi
                            </span>
                        </div>
                    </div>
                    @endforeach
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

    <!-- Validator Performance & Category Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Validator Performance -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        Performa Validator
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Statistik validasi per validator</p>
                </div>
            </div>
            @if($validatorPerformance->isEmpty())
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Belum ada data validator</p>
                </div>
            @else
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($validatorPerformance as $validator)
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ substr($validator['name'], 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $validator['name'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $validator['faculty'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mt-3">
                            <div class="text-center p-2 bg-white dark:bg-gray-800 rounded-lg">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Total Validasi</p>
                                <p class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $validator['total_validated'] }}</p>
                            </div>
                            <div class="text-center p-2 bg-white dark:bg-gray-800 rounded-lg">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Avg Response</p>
                                <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $validator['avg_response_days'] }} hari</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
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
    // Check if dark mode is enabled
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#9ca3af' : '#6b7280';
    const gridColor = isDark ? '#374151' : '#e5e7eb';

    @if($periodComparison)
    // Period Comparison Chart (when "Semua Periode" selected)
    const periodData = @json($periodComparison);
    new Chart(document.getElementById('periodComparisonChart'), {
        type: 'bar',
        data: {
            labels: periodData.map(d => d.period),
            datasets: [
                {
                    label: 'Total',
                    data: periodData.map(d => d.total),
                    backgroundColor: '#8b5cf6',
                    borderRadius: 6,
                    barThickness: 40
                },
                {
                    label: 'Disetujui',
                    data: periodData.map(d => d.approved),
                    backgroundColor: '#10b981',
                    borderRadius: 6,
                    barThickness: 40
                },
                {
                    label: 'Menunggu',
                    data: periodData.map(d => d.pending),
                    backgroundColor: '#f59e0b',
                    borderRadius: 6,
                    barThickness: 40
                },
                {
                    label: 'Ditolak',
                    data: periodData.map(d => d.rejected),
                    backgroundColor: '#ef4444',
                    borderRadius: 6,
                    barThickness: 40
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
                        padding: 15,
                        font: {
                            size: 12
                        },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: isDark ? '#1f2937' : '#fff',
                    titleColor: isDark ? '#fff' : '#111827',
                    bodyColor: isDark ? '#d1d5db' : '#6b7280',
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    boxPadding: 6
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: textColor,
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: gridColor,
                        drawBorder: false
                    }
                },
                x: {
                    ticks: {
                        color: textColor,
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    @else
    // Monthly Trend Chart (only when specific period selected)
    const monthlyData = @json($monthlyTrend);
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
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: isDark ? '#1f2937' : '#fff',
                    titleColor: isDark ? '#fff' : '#111827',
                    bodyColor: isDark ? '#d1d5db' : '#6b7280',
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    boxPadding: 6
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: textColor,
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: gridColor,
                        drawBorder: false
                    }
                },
                x: {
                    ticks: {
                        color: textColor,
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    @endif

    // Level Distribution Chart
    const levelData = @json($levelDistribution);
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
                        padding: 15,
                        font: {
                            size: 12
                        },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: isDark ? '#1f2937' : '#fff',
                    titleColor: isDark ? '#fff' : '#111827',
                    bodyColor: isDark ? '#d1d5db' : '#6b7280',
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    boxPadding: 6,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });

    // Faculty Comparison Chart
    const facultyData = @json($facultyComparison);
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
                },
                tooltip: {
                    backgroundColor: isDark ? '#1f2937' : '#fff',
                    titleColor: isDark ? '#fff' : '#111827',
                    bodyColor: isDark ? '#d1d5db' : '#6b7280',
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                    borderWidth: 1,
                    padding: 12
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: textColor,
                        font: { size: 10 }
                    },
                    grid: {
                        color: gridColor,
                        drawBorder: false
                    }
                },
                x: {
                    ticks: {
                        color: textColor,
                        font: { size: 10 }
                    },
                    grid: { display: false }
                }
            }
        }
    });

    // Category Distribution Chart
    const categoryData = @json($categoryDistribution);
    new Chart(document.getElementById('categoryDistributionChart'), {
        type: 'doughnut',
        data: {
            labels: categoryData.map(d => d.category),
            datasets: [{
                data: categoryData.map(d => d.total),
                backgroundColor: [
                    '#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', 
                    '#ef4444', '#ec4899', '#06b6d4', '#84cc16'
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: textColor,
                        padding: 12,
                        font: { size: 11 },
                        usePointStyle: true,
                        pointStyle: 'circle',
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return {
                                        text: `${label}: ${value} (${percentage}%)`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    backgroundColor: isDark ? '#1f2937' : '#fff',
                    titleColor: isDark ? '#fff' : '#111827',
                    bodyColor: isDark ? '#d1d5db' : '#6b7280',
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
});
</script>
@endsection
