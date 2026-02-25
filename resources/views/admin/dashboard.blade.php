@extends('layouts.admin')

@section('title', 'Dashboard Admin')

{{-- Bell Notification Icon for Navbar --}}
@section('anomaly_bell')
    @php
        $bellAnomalies = $anomalies ?? ['sla_breach' => 0, 'no_docs' => 0, 'duplicates' => 0, 'abandoned_drafts' => 0];
        $bellTotal = array_sum($bellAnomalies);
        $bellContext = $alertContext ?? 'active';
        $bellBadgeColor = match($bellContext) {
            'active'  => 'bg-red-500',
            'archive' => 'bg-blue-500',
            'global'  => 'bg-purple-500',
        };
        $bellRingColor = match($bellContext) {
            'active'  => 'ring-red-400/50',
            'archive' => 'ring-blue-400/50',
            'global'  => 'ring-purple-400/50',
        };
    @endphp
    <button onclick="openAnomalyModal()"
            class="relative p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-200 border border-gray-200 dark:border-gray-600 group"
            title="Peringatan Sistem ({{ $bellTotal }} masalah)"
            id="bellNotificationBtn">
        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300 transition-transform group-hover:rotate-12 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($bellTotal > 0)
            <span class="absolute -top-1 -right-1 flex items-center justify-center min-w-[20px] h-5 px-1 rounded-full text-[10px] font-black text-white {{ $bellBadgeColor }} ring-2 {{ $bellRingColor }} shadow-lg animate-pulse">
                {{ $bellTotal > 99 ? '99+' : $bellTotal }}
            </span>
        @endif
    </button>
@endsection

@section('content')
    @php
        // Set default values for all variables to prevent undefined errors
        $stats = $stats ?? ['students' => 0, 'achievements' => 0, 'validators' => 0, 'approved' => 0, 'pending' => 0, 'total_rejected' => 0];
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
        $anomalies = $anomalies ?? ['duplicates' => 0, 'no_docs' => 0, 'sla_breach' => 0, 'abandoned_drafts' => 0];
        $activeStats = $activeStats ?? ['daily_trend' => collect(), 'faculty_backlog' => collect(), 'critical_queue' => collect()];
        $totalDistinctStudents = $totalDistinctStudents ?? 0;
        $topStudentsGlobal = $topStudentsGlobal ?? collect();
        $masterData = $masterData ?? [
            'faculties_count' => 0,
            'prodis_count' => 0,
            'validators_count' => 0,
            'operators_count' => 0,
            'sync_status' => ['status' => 'Unknown', 'last_sync' => '-', 'percentage' => 0]
        ];

        $isGlobal = !$selectedPeriod;
        $isActivePeriod = $selectedPeriod && $selectedPeriod->is_active;
        $isInactivePeriod = $selectedPeriod && !$selectedPeriod->is_active;
    @endphp
    <style>
        /* --- PRINT & PDF ENHANCEMENTS --- */
        @media print {

            /* Hide UI Elements */
            aside,
            header,
            nav,
            .no-print,
            button,
            form,
            .toast-container,
            [role="button"] {
                display: none !important;
            }

            /* Reset Body & Main */
            body {
                background: white !important;
                color: black !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            main {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            /* Force Page Size & Margins */
            @page {
                size: A4;
                margin: 1.5cm;
            }

            /* Grid Layout Reset for Print */
            /* Hide responsive-only elements */
            .sm\:block,
            .md\:block,
            .lg\:block {
                display: none !important;
            }

            /* Force single column for print */
            .grid {
                display: block !important;
            }

            .grid>div {
                width: 100% !important;
                margin-bottom: 1rem !important;
            }

            /* Scale down fonts for print */
            h1 {
                font-size: 18pt !important;
            }

            h2 {
                font-size: 14pt !important;
            }

            h3 {
                font-size: 12pt !important;
            }

            p {
                font-size: 10pt !important;
            }

            /* Keep Backgrounds (Critical for KPIs and Charts) */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Typography */

            .text-gray-500,
            .text-gray-400 {
                color: #666 !important;
            }

            /* Charts Scaling */
            canvas {
                max-width: 100% !important;
                height: auto !important;
            }

            /* Print Header Branding */
            .print-report-header {
                display: block !important;
                border-bottom: 3px double #000;
                margin-bottom: 2rem;
                padding-bottom: 1rem;
                text-align: center;
            }

            .print-report-header img {
                height: 60px;
                margin: 0 auto 10px;
            }

            .print-report-header h1 {
                font-size: 1.5rem;
                margin: 0;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .print-report-header p {
                margin: 5px 0 0;
                font-size: 0.875rem;
                color: #444;
            }
        }

        .print-report-header {
            display: none;
        }
    </style>

    <!-- Professional Print Header -->
    <div class="print-report-header">
        <h1>Sistem Informasi Manajemen Prestasi Mahasiswa (SIMAPRES)</h1>
        <h2>Universitas Pattimura</h2>
        <p>Laporan Dashboard Admin - {{ $selectedPeriod ? $selectedPeriod->name : 'Semua Periode' }}</p>
        <p class="text-[10px] mt-2 italic text-gray-400">Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} oleh
            {{ auth()->user()->name }}
        </p>
    </div>

    <div class="space-y-6 lg:space-y-8">
        <!-- Header with Period Filter -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 px-1">
            <div>
                <h1 class="text-2xl lg:text-3xl xl:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                    Dashboard Overview</h1>
                <p class="text-sm lg:text-base text-gray-500 dark:text-gray-400 mt-1 font-medium">
                    Pantau statistik dan performa prestasi mahasiswa secara real-time.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <!-- Period Filter -->
                <form method="GET" action="{{ route('admin.dashboard') }}" class="relative group">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-purple-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <select name="period" onchange="this.form.submit()" class="w-full sm:w-64 pl-10 pr-4 py-2.5 bg-white dark:bg-gray-800 
                                border border-gray-200 dark:border-gray-700 rounded-xl 
                                text-sm font-bold text-gray-700 dark:text-gray-200 
                                focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 
                                shadow-sm transition-all cursor-pointer appearance-none">
                        <option value="all" {{ request('period') === 'all' || (!request('period') && !isset($selectedPeriod)) ? 'selected' : '' }}>
                            Semua Periode Akademik
                        </option>
                        @if(isset($periods))
                            @foreach($periods as $period)
                                <option value="{{ $period->id }}" {{ isset($selectedPeriod) && $selectedPeriod && $selectedPeriod->id == $period->id ? 'selected' : '' }}>
                                    {{ $period->name }} {{ $period->is_active ? '• Aktif' : '' }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </form>


            </div>
        </div>

        <!-- Active/Inactive Period Info -->
        @if($selectedPeriod)
            @if($isActivePeriod)
                <!-- Active Period Banner -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border-l-4 border-purple-600 shadow-sm">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-purple-600">
                                        Periode Aktif Saat Ini
                                    </span>
                                    <span class="px-2.5 py-0.5 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400 text-[9px] font-black rounded-full border border-green-200 dark:border-green-800/50 uppercase tracking-tighter animate-pulse">
                                        ● Live
                                    </span>
                                </div>
                                <h2 class="text-xl lg:text-2xl font-black text-gray-900 dark:text-white leading-tight uppercase tracking-tight">
                                    {{ $selectedPeriod->name }}
                                </h2>
                            </div>
                        </div>

                        <div class="flex flex-col lg:items-end gap-1.5 lg:pl-10 lg:border-l border-gray-100 dark:border-gray-700">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-0.5">Rentang Waktu Pelaporan</span>
                            <div class="flex items-center gap-3 font-bold text-gray-700 dark:text-gray-300">
                                <span class="px-3 py-1 bg-gray-50 dark:bg-gray-900 rounded-lg text-sm">{{ $selectedPeriod->start_date->format('d M Y') }}</span>
                                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                                <span class="px-3 py-1 bg-gray-50 dark:bg-gray-900 rounded-lg text-sm">{{ $selectedPeriod->end_date->format('d M Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Inactive Period Banner - Enhanced -->
                <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-6 border-2 border-slate-200 dark:border-gray-700 shadow-lg relative overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 opacity-5">
                        <div class="absolute inset-0" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, currentColor 10px, currentColor 11px);"></div>
                    </div>
                    
                    <div class="relative z-10">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                            <!-- Left Section -->
                            <div class="flex items-start gap-5">
                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center bg-slate-200 dark:bg-gray-700 text-slate-600 dark:text-gray-400 shadow-inner">
                                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-600 dark:text-gray-400">
                                            📦 Arsip Data Periode
                                        </span>
                                        <span class="px-3 py-1 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 text-[9px] font-black rounded-full border border-amber-200 dark:border-amber-800/50 uppercase tracking-tighter">
                                            🔒 Read Only
                                        </span>
                                    </div>
                                    <h2 class="text-2xl lg:text-3xl font-black text-slate-800 dark:text-gray-200 leading-tight uppercase tracking-tight mb-2">
                                        {{ $selectedPeriod->name }}
                                    </h2>
                                    <p class="text-sm text-slate-600 dark:text-gray-400 font-medium">
                                        Data historis periode yang telah berakhir - Tidak dapat diubah
                                    </p>
                                </div>
                            </div>

                            <!-- Right Section -->
                            <div class="flex flex-col gap-4 lg:pl-10 lg:border-l-2 border-slate-300 dark:border-gray-700">
                                <!-- Date Range -->
                                <div>
                                    <span class="text-[10px] font-black text-slate-500 dark:text-gray-500 uppercase tracking-widest block mb-2">
                                        📅 Rentang Waktu Periode
                                    </span>
                                    <div class="flex items-center gap-3 font-bold text-slate-700 dark:text-gray-300">
                                        <span class="px-4 py-2 bg-white dark:bg-gray-800 rounded-xl text-sm shadow-sm border border-slate-200 dark:border-gray-700">
                                            {{ $selectedPeriod->start_date->format('d M Y') }}
                                        </span>
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                        </svg>
                                        <span class="px-4 py-2 bg-white dark:bg-gray-800 rounded-xl text-sm shadow-sm border border-slate-200 dark:border-gray-700">
                                            {{ $selectedPeriod->end_date->format('d M Y') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Archive Info -->
                                <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-gray-400 bg-white/50 dark:bg-gray-800/50 px-3 py-2 rounded-lg border border-slate-200 dark:border-gray-700">
                                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-semibold">Data tersimpan untuk keperluan pelaporan dan analisis historis</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div
                class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-6 shadow-xl shadow-blue-500/20 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                <div class="relative z-10 flex items-center gap-6">
                    <div
                        class="w-16 h-16 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/30">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl lg:text-2xl font-black tracking-tight mb-1">Ringkasan Data Global</h3>
                        <p class="text-blue-100 text-sm font-medium">Menampilkan agregasi data dari seluruh periode akademik
                            yang tercatat dalam sistem.</p>
                    </div>
                </div>
            </div>
        @endif

        @if($isInactivePeriod)
            <!-- HEADER SECTION - ARCHIVED PERIOD SUMMARY -->
            <div class="relative mb-8 pt-4">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                </div>
                <div class="relative flex justify-start">
                    <span class="pr-6 bg-gray-50 dark:bg-gray-900 text-sm font-black text-gray-500 dark:text-gray-400 uppercase tracking-[0.3em] flex items-center gap-3">
                        <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                        Ringkasan Final Arsip
                    </span>
                </div>
            </div>

            <!-- BARIS 1 – RINGKASAN AKHIR PERIODE (4 CARD) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1 – Total Prestasi Final -->
                <div class="dash-card bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all group relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 text-emerald-500/10 transition-transform group-hover:scale-110 group-hover:-rotate-12 duration-500">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd"
                                d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Prestasi Final</p>
                        </div>
                        <p class="text-4xl font-black text-emerald-600 tracking-tight mb-2">{{ number_format($stats['achievements']) }}</p>
                        {{-- Period Comparison Indicator --}}
                        @if(isset($archivedStats['period_growth']) && $archivedStats['period_growth'] !== null)
                            @php $growth = $archivedStats['period_growth']; @endphp
                            @if($growth >= 0)
                                <div class="flex items-center gap-1.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-2.5 py-1.5 rounded-lg border border-emerald-100 dark:border-emerald-800/30 mb-2">
                                    <span>▲</span>
                                    <span>+{{ $growth }}% dari periode sebelumnya</span>
                                </div>
                            @else
                                <div class="flex items-center gap-1.5 text-[10px] font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-2.5 py-1.5 rounded-lg border border-red-100 dark:border-red-800/30 mb-2">
                                    <span>▼</span>
                                    <span>{{ $growth }}% dari periode sebelumnya</span>
                                </div>
                            @endif
                        @elseif(isset($archivedStats['previous_period']) && $archivedStats['previous_period'] === null)
                            <div class="flex items-center gap-1.5 text-[10px] font-bold text-gray-400 bg-gray-50 dark:bg-gray-700/30 px-2.5 py-1.5 rounded-lg border border-gray-100 dark:border-gray-700 mb-2">
                                <span>—</span>
                                <span>Tidak ada periode sebelumnya</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-2 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-tighter bg-emerald-50 dark:bg-emerald-900/20 px-2 py-1 rounded-lg">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                            Validasi Selesai & Disahkan
                        </div>
                    </div>
                </div>

                <!-- Card 2 – Persentase Nasional & Internasional -->
                <div class="dash-card bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all group relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 text-purple-500/10 transition-transform group-hover:scale-110 group-hover:-rotate-12 duration-500">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.083 9h1.946c.089-1.546.383-2.97.837-4.118A6.004 6.004 0 004.083 9zM10 2a8 8 0 100 16 8 8 0 000-16zm0 2c-.076 0-.232.032-.465.262-.238.234-.497.623-.737 1.182-.389.907-.673 2.142-.766 3.556h3.936c-.093-1.414-.377-2.649-.766-3.556-.24-.56-.5-.948-.737-1.182C10.232 4.032 10.076 4 10 4zm3.971 5c-.089-1.546-.383-2.97-.837-4.118A6.004 6.004 0 0115.917 9h-1.946zm-2.003 2H8.032c.093 1.414.377 2.649.766 3.556.24.56.5.948.737 1.182.233.23.389.262.465.262.076 0 .232-.032.465-.262.238-.234.498-.623.737-1.182.389-.907.673-2.142.766-3.556zm1.166 4.118c.454-1.147.748-2.572.837-4.118h1.946a6.004 6.004 0 01-2.783 4.118zm-6.268 0C6.412 13.97 6.118 12.546 6.03 11H4.083a6.004 6.004 0 002.783 4.118z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Skala Prestasi</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            @php 
                                $total = array_sum($levelDistribution);
                                $nasRatio = $total > 0 ? round(($levelDistribution['Nasional'] / $total) * 100) : 0;
                                $interRatio = $total > 0 ? round(($levelDistribution['Internasional'] / $total) * 100) : 0;
                            @endphp
                            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-3 border border-purple-100 dark:border-purple-800/30">
                                <p class="text-2xl font-black text-purple-600 dark:text-purple-400">{{ $nasRatio }}%</p>
                                <p class="text-[9px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-tighter mt-1">🇮🇩 Nasional</p>
                            </div>
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-3 border border-indigo-100 dark:border-indigo-800/30">
                                <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ $interRatio }}%</p>
                                <p class="text-[9px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-tighter mt-1">🌍 Internasional</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3 – Fakultas Aktif Berprestasi -->
                <div class="dash-card bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all group relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 text-blue-500/10 transition-transform group-hover:scale-110 group-hover:-rotate-12 duration-500">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16l7-2 7 2z" />
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                </svg>
                            </div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Partisipasi Fakultas</p>
                        </div>
                        <div class="flex items-baseline gap-2 mb-3">
                            <p class="text-4xl font-black text-blue-600 tracking-tight">{{ $archivedStats['active_faculties'] ?? 0 }}</p>
                            <p class="text-lg font-bold text-gray-400">/ {{ $archivedStats['total_faculties'] ?? 0 }}</p>
                        </div>
                        <div class="space-y-2">
                            <div class="w-full bg-gray-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden border border-gray-200 dark:border-gray-600">
                                @php
                                    $totalFac = $archivedStats['total_faculties'] ?? 1;
                                    $activeFac = $archivedStats['active_faculties'] ?? 0;
                                    $percentage = ($activeFac / $totalFac) * 100;
                                @endphp
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-full transition-all duration-1000 shadow-[0_0_8px_rgba(59,130,246,0.5)]" style="width: {{ $percentage }}%"></div>
                            </div>
                            <p class="text-[9px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-tighter">{{ round($percentage) }}% Fakultas Berkontribusi</p>
                        </div>
                    </div>
                </div>

                <!-- Card 4 – Rata-rata Waktu Validasi (Final) -->
                <div class="dash-card bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all group relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 text-amber-500/10 transition-transform group-hover:scale-110 group-hover:-rotate-12 duration-500">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kecepatan Proses</p>
                        </div>
                        <div class="flex items-baseline gap-2 mb-2">
                            <p class="text-4xl font-black text-amber-600 tracking-tight">{{ $statistics['avg_time_to_approve'] ?? 0 }}</p>
                            <p class="text-lg font-bold text-gray-400 uppercase">Hari</p>
                        </div>
                        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg px-3 py-2 border border-amber-100 dark:border-amber-800/30">
                            <p class="text-[9px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-tighter">⚡ Rata-rata Submission → Approval</p>
                        </div>
                    </div>
                </div>
            </div>

                            <!-- BARIS 2 – DISTRIBUSI HASIL (3 CARD) -->
                            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-12 gap-6 mt-6">
                                <!-- Card 5 – Distribusi Tingkat -->
                                <div class="lg:col-span-4 dash-card bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 lg:p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-lg transition-shadow">
                                    <h3 class="dash-chart-title text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Distribusi Tingkat</h3>
                                    <div class="dash-chart-container h-64 relative">
                                        <canvas id="archivedLevelChart"></canvas>
                                    </div>
                                </div>

                                <!-- Card 6 – Distribusi Kategori -->
                                <div class="lg:col-span-4 dash-card bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 lg:p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-lg transition-shadow">
                                    <h3 class="dash-chart-title text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Distribusi Kategori</h3>
                                    <div class="dash-chart-container h-64 relative">
                                        <canvas id="archivedCategoryChart"></canvas>
                                    </div>
                                </div>

                                <!-- Card 7 – Status Prestasi (Final) -->
                                <div class="lg:col-span-4 dash-card bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 lg:p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-lg transition-shadow">
                                    <h3 class="dash-chart-title text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Status Prestasi (Final)</h3>
                                    <div class="dash-chart-container h-64 relative">
                                        <canvas id="archivedStatusChart"></canvas>
                                    </div>
                                    <div class="mt-5 flex justify-center gap-6">
                                        <div class="text-center">
                                            <p class="dash-stat-sub text-[10px] text-gray-400 font-bold uppercase">Disetujui</p>
                                            <p class="text-xl lg:text-2xl font-black text-emerald-600">{{ $stats['approved'] }}</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="dash-stat-sub text-[10px] text-gray-400 font-bold uppercase">Ditolak</p>
                                            <p class="text-xl lg:text-2xl font-black text-red-600">{{ $stats['total_rejected'] }}</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="dash-stat-sub text-[10px] text-gray-400 font-bold uppercase">Dibatalkan</p>
                                            <p class="text-xl lg:text-2xl font-black text-amber-600">{{ $archivedStats['expired_count'] ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BARIS 3 – PERBANDINGAN INTERNAL (3 CARD) -->
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
                                <!-- Card 8 – Prestasi per Fakultas -->
                                <div class="lg:col-span-6 dash-card bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 lg:p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-lg transition-shadow">
                                    <h3 class="dash-chart-title text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Prestasi per Fakultas</h3>
                                    <div class="dash-chart-container-lg h-80 relative">
                                        <canvas id="archivedFacultyChart"></canvas>
                                    </div>
                                </div>

                                <!-- Card 9 – Prestasi per Program Studi -->
                                <div class="lg:col-span-3 dash-card bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 lg:p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-lg transition-shadow flex flex-col">
                                    <h3 class="dash-chart-title text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Top Program Studi</h3>
                                    <div class="space-y-4 lg:space-y-5 flex-1">
                                        @foreach($topProgramStudies->take(6) as $prodi)
                                            <div>
                                                <div class="flex justify-between items-center mb-1.5">
                                                    <span class="dash-rank-name text-xs font-bold text-gray-700 dark:text-gray-300 truncate w-40">{{ $prodi->prodi }}</span>
                                                    <span class="dash-rank-value text-xs font-black text-blue-600">{{ $prodi->total }}</span>
                                                </div>
                                                <div class="w-full bg-gray-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                                                    <div class="bg-blue-500 h-full" style="width: {{ ($prodi->total / $topProgramStudies->max('total')) * 100 }}%"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Card 10 – Rasio Prestasi per Mahasiswa -->
                                <div class="lg:col-span-3 dash-card bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 lg:p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-lg transition-shadow">
                                    <h3 class="dash-chart-title text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Rasio per Mahasiswa (%)</h3>
                                    <div class="dash-chart-container-lg h-80 relative">
                                        <canvas id="archivedRatioChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- BARIS 4 – EVALUASI & KUALITAS DATA (2 CARD) -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <!-- Card 11 – Temuan & Catatan Evaluasi -->
                                <div class="dash-card bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 lg:p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-lg transition-shadow">
                                    <h3 class="dash-chart-title text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                        Temuan & Catatan Evaluasi
                                    </h3>
                                    <ul class="space-y-3">
                                        @php
                                            $inactiveFaculties = $archivedStats['total_faculties'] - $archivedStats['active_faculties'];
                                        @endphp
                                        <li class="dash-insight-item flex items-start gap-3 p-3 bg-red-50 dark:bg-red-900/10 rounded-lg">
                                            <span class="dash-insight-badge p-1 bg-red-100 dark:bg-red-800 text-red-600 dark:text-red-300 rounded text-[10px] uppercase font-bold">Insight</span>
                                            <span class="dash-insight-text text-xs text-red-800 dark:text-red-300">Terdapat {{ $inactiveFaculties }} fakultas yang tidak mencatatkan prestasi pada periode ini.</span>
                                        </li>
                                        <li class="dash-insight-item flex items-start gap-3 p-3 bg-blue-50 dark:bg-blue-900/10 rounded-lg">
                                            <span class="dash-insight-badge p-1 bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-300 rounded text-[10px] uppercase font-bold">Insight</span>
                                            <span class="dash-insight-text text-xs text-blue-800 dark:text-blue-300">Kategori {{ $categoryDistribution->sortByDesc('total')->first()->category ?? 'Utama' }} mendominasi {{ round(($categoryDistribution->max('total') / ($stats['achievements'] ?: 1)) * 100) }}% capaian data.</span>
                                        </li>
                                        <li class="dash-insight-item flex items-start gap-3 p-3 bg-amber-50 dark:bg-amber-900/10 rounded-lg">
                                            <span class="dash-insight-badge p-1 bg-amber-100 dark:bg-amber-800 text-amber-600 dark:text-amber-300 rounded text-[10px] uppercase font-bold">Audit</span>
                                            <span class="dash-insight-text text-xs text-amber-800 dark:text-amber-300">Rasio penolakan data sebesar {{ round(($stats['total_rejected'] / ($stats['achievements'] ?: 1)) * 100) }}%, perlu review pedoman pengajuan.</span>
                                        </li>
                                    </ul>
                                    <div class="mt-6 p-4 lg:p-5 border-2 border-dashed border-gray-100 dark:border-gray-700 rounded-xl">
                                        <p class="dash-stat-sub text-[10px] text-gray-400 font-bold uppercase mb-2">Bahan Rapat Evaluasi</p>
                                        <p class="dash-insight-text text-xs text-gray-500 italic">"Prioritaskan peningkatan partisipasi pada fakultas non-aktif dan standarisasi dokumen pendukung untuk menekan angka penolakan."</p>
                                    </div>
                                </div>

                                <!-- Card 12 – Ringkasan Data Ditolak -->
                                <div class="dash-card bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 lg:p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-lg transition-shadow flex flex-col">
                                    <h3 class="dash-chart-title text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Ringkasan Data Ditolak</h3>
                                    <div class="flex-1 max-h-[400px] overflow-auto custom-scrollbar pr-2">
                                        <div class="overflow-x-auto custom-scrollbar">
                                            <table class="dash-table w-full text-left min-w-[600px]">
                                                <thead class="sticky top-0 bg-white dark:bg-gray-800 z-10">
                                                    <tr>
                                                        <th class="pb-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Fakultas</th>
                                                        <th class="pb-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Ditolak</th>
                                                        <th class="pb-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-right">Failure Rate</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                @foreach($facultyComparison->sortByDesc('rejected') as $f)
                                                    <tr>
                                                        <td class="py-3 text-xs font-bold text-gray-700 dark:text-gray-300">{{ $f->faculty }}</td>
                                                        <td class="py-3 text-xs font-black text-red-600 text-center">{{ $f->rejected }}</td>
                                                        <td class="py-3 text-right">
                                                            <span class="text-xs font-bold text-gray-500">{{ $f->total > 0 ? round(($f->rejected / $f->total) * 100) : 0 }}%</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BARIS 5 – ARSIP & LAPORAN -->
                            <div class="mt-6 lg:col-span-12">
                            <!-- BARIS 5 – ARSIP & LAPORAN -->
                            <div class="mt-8 lg:col-span-12">
                                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-2xl overflow-hidden relative group">
                                    <!-- Decorative Elements -->
                                    <div class="absolute top-0 right-0 w-96 h-96 bg-gray-50 dark:bg-gray-900/40 rounded-full -mr-48 -mt-48 transition-transform group-hover:scale-110 duration-700"></div>
                                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-emerald-50 dark:bg-emerald-900/10 rounded-full -ml-32 -mb-32 transition-transform group-hover:scale-110 duration-700"></div>

                                    <div class="relative z-10 flex flex-col lg:flex-row items-stretch">
                                        <!-- Left Side: Status / Seal -->
                                        <div class="lg:w-1/3 p-8 lg:p-12 bg-gray-50 dark:bg-gray-900/50 flex flex-col items-center justify-center text-center border-b lg:border-b-0 lg:border-r border-gray-100 dark:border-gray-700">
                                            <div class="relative mb-6">
                                                <div class="absolute inset-0 bg-emerald-500 rounded-full blur-2xl opacity-20 animate-pulse"></div>
                                                <div class="w-24 h-24 bg-white dark:bg-gray-800 rounded-full shadow-xl flex items-center justify-center border-4 border-emerald-500 relative">
                                                    <svg class="w-12 h-12 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <h4 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tighter mb-2">Data Terkunci</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-4">Seluruh data telah divalidasi dan disimpan dalam arsip permanen universitas.</p>
                                            <div class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 rounded-full border border-emerald-200 dark:border-emerald-800/50">
                                                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                                                <span class="text-[10px] font-black uppercase tracking-widest leading-none">Status: Final & Locked</span>
                                            </div>
                                        </div>

                                        <!-- Right Side: Details & Actions -->
                                        <div class="flex-1 p-8 lg:p-12 flex flex-col justify-between">
                                            <div>
                                                <h3 class="text-3xl lg:text-4xl font-black text-gray-900 dark:text-white tracking-tight mb-4">Ringkasan Arsip Periode</h3>
                                                <p class="text-gray-500 dark:text-gray-400 text-base font-medium max-w-xl mb-8">
                                                    Menampilkan data konsolidasi periode akademik <span class="text-gray-900 dark:text-white font-bold">{{ $selectedPeriod->name }}</span>. 
                                                    Laporan ini digunakan sebagai dasar evaluasi capaian IKU universitas.
                                                </p>

                                                <div class="grid grid-cols-2 md:grid-cols-3 gap-8 mb-10">
                                                    <div class="space-y-1">
                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Prestasi</p>
                                                        <p class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ number_format($stats['achievements']) }}</p>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Tgl Penutupan</p>
                                                        <p class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $selectedPeriod->end_date->format('d/m/Y') }}</p>
                                                    </div>
                                                    <div class="space-y-1 hidden md:block">
                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kualitas Data</p>
                                                        <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">100% Final</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex flex-col sm:flex-row items-center gap-4 border-t border-gray-100 dark:border-gray-700 pt-8" x-data="{ open: false }">
                                                <div class="relative flex-1 w-full sm:w-auto">
                                                    <button @click="open = !open" @click.away="open = false" 
                                                        class="w-full sm:w-auto px-8 py-4 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-black rounded-2xl hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-3 shadow-xl shadow-gray-900/20 dark:shadow-none group">
                                                        <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4h10z"></path>
                                                        </svg>
                                                        UNDUH LAPORAN ARSIP
                                                        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    </button>

                                                    <!-- Dropdown Menu -->
                                                    <div x-show="open" 
                                                        x-transition:enter="transition ease-out duration-200"
                                                        x-transition:enter-start="transform opacity-0 -translate-y-4"
                                                        x-transition:enter-end="transform opacity-100 translate-y-0"
                                                        x-transition:leave="transition ease-in duration-150"
                                                        x-transition:leave-start="transform opacity-100 translate-y-0"
                                                        x-transition:leave-end="transform opacity-0 -translate-y-4"
                                                        class="absolute bottom-full left-0 mb-4 w-72 rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.2)] bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-[50] overflow-hidden" 
                                                        x-cloak>
                                                        <div class="p-3 space-y-2">
                                                            <a href="{{ route('admin.export.achievements', ['period' => $selectedPeriod->id, 'format' => 'excel']) }}" 
                                                                class="flex items-center gap-4 px-5 py-4 text-sm text-gray-700 dark:text-gray-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-2xl transition-all group/item">
                                                                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center group-hover/item:scale-110 transition-transform">
                                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                </div>
                                                                <div class="flex flex-col">
                                                                    <span class="font-black">Microsoft Excel</span>
                                                                    <span class="text-[10px] text-gray-500 uppercase tracking-widest font-bold mt-0.5">Format .xlsx</span>
                                                                </div>
                                                            </a>
                                                            <a href="{{ route('admin.export.achievements', ['period' => $selectedPeriod->id, 'format' => 'csv']) }}" 
                                                                class="flex items-center gap-4 px-5 py-4 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-2xl transition-all group/item">
                                                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center group-hover/item:scale-110 transition-transform">
                                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                                                </div>
                                                                <div class="flex flex-col">
                                                                    <span class="font-black">CSV Data Stream</span>
                                                                    <span class="text-[10px] text-gray-500 uppercase tracking-widest font-bold mt-0.5">Format .csv</span>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-gray-400 font-medium italic sm:max-w-[200px]">
                                                    Laporan ini telah dienkripsi secara digital untuk integritas arsip.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
        @elseif($isActivePeriod)
                        <!-- BARIS 1 – STATUS SISTEM SAAT INI (5 CARD) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                            <!-- Card 1 – Total Pengajuan -->
                            <div class="dash-card bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all group relative overflow-hidden">
                                <div class="absolute -right-4 -bottom-4 text-gray-500/5 transition-transform group-hover:scale-110 duration-500">
                                    <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13H5v-2h14v2z" /></svg>
                                </div>
                                <div class="relative z-10">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Total Pengajuan</p>
                                    <div class="flex items-baseline gap-2">
                                        <p class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">{{ number_format($stats['achievements']) }}</p>
                                        <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <p class="text-[9px] font-black text-gray-400 uppercase mt-4 tracking-tighter">Diterima Periode Ini</p>
                                </div>
                            </div>

                            <!-- Card 2 – Menunggu Validasi -->
                            @php $isOverThreshold = $stats['pending'] > 20; @endphp
                            <div class="dash-card {{ $isOverThreshold ? 'bg-amber-50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-800' : 'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700' }} rounded-2xl p-6 border shadow-sm hover:shadow-xl transition-all group relative overflow-hidden">
                                <div class="absolute -right-4 -bottom-4 text-amber-500/5 transition-transform group-hover:scale-110 duration-500">
                                    <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" /></svg>
                                </div>
                                <div class="relative z-10">
                                    <p class="text-[10px] font-black {{ $isOverThreshold ? 'text-amber-600' : 'text-gray-400' }} uppercase tracking-widest mb-3">Menunggu Verifikasi</p>
                                    <div class="flex items-center gap-3">
                                        <p class="text-3xl font-black {{ $isOverThreshold ? 'text-amber-600' : 'text-gray-900 dark:text-white' }} tracking-tight">{{ number_format($stats['pending']) }}</p>
                                        @if($isOverThreshold)
                                            <div class="flex h-2 w-2 relative">
                                                <div class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></div>
                                                <div class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></div>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="text-[9px] font-black {{ $isOverThreshold ? 'text-amber-500' : 'text-gray-400' }} uppercase mt-4 tracking-tighter">
                                        {{ $isOverThreshold ? 'Prioritas Tinggi' : 'Beban Kerja Antrean' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Card 3 – Disetujui -->
                            <div class="dash-card bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all group relative overflow-hidden">
                                <div class="absolute -right-4 -bottom-4 text-emerald-500/5 transition-transform group-hover:scale-110 duration-500">
                                    <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" /></svg>
                                </div>
                                <div class="relative z-10">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Telah Disetujui</p>
                                    <p class="text-3xl font-black text-emerald-600 tracking-tight">{{ number_format($stats['approved']) }}</p>
                                    <p class="text-[9px] font-black text-gray-400 uppercase mt-4 tracking-tighter">Selesai Verifikasi</p>
                                </div>
                            </div>

                            <!-- Card 4 – Ditolak -->
                            <div class="dash-card bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all group relative overflow-hidden">
                                <div class="absolute -right-4 -bottom-4 text-red-500/5 transition-transform group-hover:scale-110 duration-500">
                                    <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" /></svg>
                                </div>
                                <div class="relative z-10">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Total Ditolak</p>
                                    <p class="text-3xl font-black text-red-600 tracking-tight">{{ number_format($stats['total_rejected']) }}</p>
                                    <p class="text-[9px] font-black text-gray-400 uppercase mt-4 tracking-tighter">Butuh Revisi</p>
                                </div>
                            </div>

                            <!-- Card 5 – Rata-rata Waktu Validasi -->
                            <div class="dash-card bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all group relative overflow-hidden">
                                <div class="absolute -right-4 -bottom-4 text-purple-500/5 transition-transform group-hover:scale-110 duration-500">
                                    <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M11 9.16V2a1 1 0 0 0-2 0v7.26l-3.38 3.38a1 1 0 1 0 1.41 1.41L10 11.41l2.97 2.97a1 1 0 0 0 1.41-1.41L11 9.16z" /></svg>
                                </div>
                                <div class="relative z-10">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Speed Verifikasi</p>
                                    <div class="flex items-baseline gap-2">
                                        <p class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">{{ $statistics['avg_time_to_approve'] }}</p>
                                        <p class="text-lg font-bold text-gray-400 uppercase">Hari</p>
                                    </div>
                                    <p class="text-[9px] font-black text-gray-400 uppercase mt-4 tracking-tighter text-right">Target &lt; 3 Hari</p>
                                </div>
                            </div>
                        </div>

                        <!-- BARIS 2 – DISTRIBUSI PROSES (3 CARD) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-6 mt-8">
                            <!-- Card 6 – Status Pengajuan -->
                            <div class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                                <div class="flex items-center justify-between mb-8">
                                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Status Pengajuan</h3>
                                    <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-900 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path></svg>
                                    </div>
                                </div>
                                <div class="h-64 relative">
                                    <canvas id="activeStatusChart"></canvas>
                                </div>
                            </div>

                            <!-- Card 7 – Distribusi Tingkat -->
                            <div class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                                <div class="flex items-center justify-between mb-8">
                                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Tingkat Prestasi</h3>
                                    <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-900 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                    </div>
                                </div>
                                <div class="h-64 relative">
                                    <canvas id="activeLevelChart"></canvas>
                                </div>
                            </div>

                            <!-- Card 8 – Distribusi Kategori -->
                            <div class="sm:col-span-2 lg:col-span-4 bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                                <div class="flex items-center justify-between mb-8">
                                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Top Kategori</h3>
                                    <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-900 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                    </div>
                                </div>
                                <div class="h-64 relative">
                                    <canvas id="activeCategoryChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- BARIS 3 – MONITORING UNIT (3 CARD) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-6 mt-8">
                            <!-- Card 9 – Backlog Validasi per Fakultas -->
                            <div class="md:col-span-2 xl:col-span-7 bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                                <div class="flex items-center justify-between mb-8 px-1">
                                    <div>
                                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Backlog per Fakultas</h3>
                                        <p class="text-[10px] text-gray-500 font-medium">Distribusi antrean yang membutuhkan tindakan segera.</p>
                                    </div>
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    </div>
                                </div>
                                <div class="h-72 relative">
                                    <canvas id="facultyBacklogChart"></canvas>
                                </div>
                            </div>

                            <!-- Card 11 – Fakultas Paling Aktif -->
                            <div class="xl:col-span-5 bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Fakultas Teraktif</h3>
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16l7-2 7 2z" /></svg>
                                    </div>
                                </div>
                                <div class="space-y-3.5">
                                    @php $maxFacultyTotal = $facultyComparison->max('total') ?: 1; @endphp
                                    @foreach($facultyComparison->take(6) as $index => $f)
                                        <div class="relative group/item">
                                            <div class="flex items-center justify-between mb-1.5 relative z-10">
                                                <div class="flex items-center gap-3">
                                                    <span class="text-[10px] font-black w-5 text-gray-400">#{{ $index + 1 }}</span>
                                                    <span class="text-[11px] lg:text-xs font-bold text-gray-700 dark:text-gray-200">{{ $f->faculty }}</span>
                                                </div>
                                                <span class="text-xs font-black text-blue-600 dark:text-blue-400">{{ number_format($f->total) }}</span>
                                            </div>
                                            <div class="h-1.5 w-full bg-gray-50 dark:bg-gray-700/50 rounded-full overflow-hidden border border-gray-100 dark:border-gray-700">
                                                <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full shadow-[0_0_8px_rgba(59,130,246,0.3)]" style="width: {{ ($f->total / $maxFacultyTotal) * 100 }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- BARIS 4 – RISIKO & KUALITAS DATA -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mt-6">
                            <!-- Card 12 – Peringatan Sistem -->
                            <div class="dash-card bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 lg:p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-lg transition-shadow">
                                <h3 class="dash-chart-title text-xs font-bold text-gray-900 dark:text-white uppercase mb-6 tracking-widest">Peringatan Sistem</h3>
                                <div class="space-y-3">
                                    @if($anomalies['sla_breach'] > 0)
                                        <div class="flex items-center gap-3 p-3 bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/20 rounded-xl">
                                            <div class="p-2 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="dash-alert-title text-[11px] font-black text-red-800 dark:text-red-300">SLA BREACH ALERT</p>
                                                <p class="dash-alert-text text-[10px] text-red-600 dark:text-red-400">{{ $anomalies['sla_breach'] }} pengajuan menunggu validasi lebih dari 7 hari.</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($anomalies['abandoned_drafts'] > 0)
                                        <div class="flex items-center gap-3 p-3 bg-orange-50 dark:bg-orange-900/10 border border-orange-100 dark:border-orange-900/20 rounded-xl">
                                            <div class="p-2 bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-lg">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="dash-alert-title text-[11px] font-black text-orange-800 dark:text-orange-300">DRAFT TERBENGKALAI</p>
                                                <p class="dash-alert-text text-[10px] text-orange-600 dark:text-orange-400">{{ $anomalies['abandoned_drafts'] }} draft tidak diupdate lebih dari 30 hari.</p>
                                            </div>
                                            <a href="{{ route('admin.student-achievements', ['status' => 'draft', 'abandoned' => 1]) }}" 
                                               class="px-2.5 py-1 bg-orange-100 dark:bg-orange-800 hover:bg-orange-200 dark:hover:bg-orange-700 text-orange-700 dark:text-orange-200 text-[9px] font-bold rounded-lg transition-colors whitespace-nowrap">
                                                Lihat Data
                                            </a>
                                        </div>
                                    @endif

                                    @php $inactiveFaculties = $facultyComparison->where('total', 0)->count(); @endphp
                                    @if($inactiveFaculties > 0)
                                        <div class="flex items-center gap-3 p-3 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/20 rounded-xl">
                                            <div class="p-2 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-lg">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="dash-alert-title text-[11px] font-black text-amber-800 dark:text-amber-300">PARTISIPASI RENDAH</p>
                                                <p class="dash-alert-text text-[10px] text-amber-600 dark:text-amber-400">{{ $inactiveFaculties }} fakultas belum mencatat prestasi periode ini.</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($anomalies['sla_breach'] == 0 && $anomalies['abandoned_drafts'] == 0 && $inactiveFaculties == 0)
                                        <div class="flex items-center gap-3 p-3 bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/20 rounded-xl">
                                            <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="dash-alert-title text-[11px] font-black text-emerald-800 dark:text-emerald-300">SISTEM SEHAT</p>
                                                <p class="dash-alert-text text-[10px] text-emerald-600 dark:text-emerald-400">Tidak ada anomali atau data bermasalah terdeteksi.</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Card 13 – Antrean Pengajuan Kritis -->
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                                <div class="flex items-center justify-between mb-8">
                                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Antrean Terlama</h3>
                                    <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/30 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                </div>

                                <!-- Mobile: Card-based layout (< 640px) -->
                                <div class="sm:hidden space-y-3">
                                    @forelse($activeStats['critical_queue'] as $ach)
                                        @php $days = $ach->waiting_days; @endphp
                                        <div class="flex items-center gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-black text-gray-900 dark:text-white truncate">{{ $ach->student->name }}</p>
                                                <p class="text-[10px] text-gray-500 font-medium truncate mt-0.5">{{ $ach->event_name }}</p>
                                                <span class="inline-block mt-2 text-[9px] font-black text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 px-2 py-0.5 rounded-full border border-gray-100 dark:border-gray-700">{{ $ach->student->faculty }}</span>
                                            </div>
                                            <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                                                <span class="px-3 py-1 {{ $days > 7 ? 'bg-red-50 text-red-600 border-red-100' : 'bg-amber-50 text-amber-600 border-amber-100' }} border text-[10px] font-black rounded-lg">
                                                    {{ $days }} Hari
                                                </span>
                                                <a href="{{ route('admin.university.show', $ach->sa_id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-[9px] font-bold rounded-lg transition-colors shadow-sm">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                                    Proses
                                                </a>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="py-12 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Antrean Bersih</div>
                                    @endforelse
                                </div>

                                <!-- Table layout for non-mobile -->
                                <div class="hidden sm:block flex-1 overflow-x-auto custom-scrollbar">
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr>
                                                <th class="pb-4 text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Mahasiswa</th>
                                                <th class="pb-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Fakultas</th>
                                                <th class="pb-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Masa Tunggu</th>
                                                <th class="pb-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right pr-1">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                                            @forelse($activeStats['critical_queue'] as $ach)
                                                @php $days = $ach->waiting_days; @endphp
                                                <tr class="group/tr hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors">
                                                    <td class="py-4 pl-1">
                                                        <p class="text-[13px] font-black text-gray-900 dark:text-white tracking-tight">{{ $ach->student->name }}</p>
                                                        <p class="text-[11px] text-gray-500 font-medium line-clamp-1 mt-0.5">{{ $ach->event_name }}</p>
                                                    </td>
                                                    <td class="py-4">
                                                        <span class="text-[10px] font-black text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 px-2.5 py-1 rounded-lg border border-gray-100 dark:border-gray-600 uppercase tracking-tighter">{{ $ach->student->faculty }}</span>
                                                    </td>
                                                    <td class="py-4 text-center">
                                                        <span class="inline-flex items-center px-3 py-1.5 {{ $days > 7 ? 'bg-red-50 text-red-600 border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800/30' : 'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/30' }} border text-[11px] font-black rounded-xl">
                                                            {{ $days }} Hari
                                                        </span>
                                                    </td>
                                                    <td class="py-4 text-right pr-1">
                                                        <a href="{{ route('admin.university.show', $ach->sa_id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold rounded-xl hover:scale-105 transition-all shadow-sm" title="Proses Sekarang">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                                            Proses Sekarang
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="py-16 text-center text-xs font-black text-gray-300 uppercase tracking-widest">Semua Pengajuan Telah Ditangani</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        </div>



                            <!-- Row 6: ACTIVE PERIOD REPORT EXPORT -->
                            <div class="mt-8">
                                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-2xl overflow-hidden relative group">
                                    <!-- Decorative Elements -->
                                    <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-50 dark:bg-emerald-900/10 rounded-full -mr-48 -mt-48 transition-transform group-hover:scale-110 duration-700"></div>
                                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-50 dark:bg-blue-900/10 rounded-full -ml-32 -mb-32 transition-transform group-hover:scale-110 duration-700"></div>

                                    <div class="relative z-10 flex flex-col lg:flex-row items-stretch">
                                        <!-- Left Side: Status -->
                                        <div class="lg:w-1/3 p-8 lg:p-12 bg-gray-50 dark:bg-gray-900/50 flex flex-col items-center justify-center text-center border-b lg:border-b-0 lg:border-r border-gray-100 dark:border-gray-700">
                                            <div class="relative mb-6">
                                                <div class="absolute inset-0 bg-emerald-500 rounded-full blur-2xl opacity-20 animate-pulse"></div>
                                                <div class="w-24 h-24 bg-white dark:bg-gray-800 rounded-full shadow-xl flex items-center justify-center border-4 border-emerald-500 relative">
                                                    <svg class="w-12 h-12 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <h4 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tighter mb-2">Laporan Berjalan</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-4">Data sedang dalam periode aktif. Angka dapat berubah sewaktu-waktu.</p>
                                            <div class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 rounded-full border border-emerald-200 dark:border-emerald-800/50">
                                                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                                                <span class="text-[10px] font-black uppercase tracking-widest leading-none">Status: Live & Operational</span>
                                            </div>
                                        </div>

                                        <!-- Right Side: Details & Actions -->
                                        <div class="flex-1 p-8 lg:p-12 flex flex-col justify-between">
                                            <div>
                                                <h3 class="text-3xl lg:text-4xl font-black text-gray-900 dark:text-white tracking-tight mb-4">Ringkasan Laporan Operasional</h3>
                                                <p class="text-gray-500 dark:text-gray-400 text-base font-medium max-w-xl mb-8">
                                                    Unduh data capaian prestasi untuk periode akademik <span class="text-gray-900 dark:text-white font-bold">{{ $selectedPeriod->name }}</span>. 
                                                    Gunakan laporan ini untuk monitoring harian proses verifikasi.
                                                </p>

                                                <div class="grid grid-cols-2 md:grid-cols-3 gap-8 mb-10">
                                                    <div class="space-y-1">
                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Prestasi Masuk</p>
                                                        <p class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ number_format($stats['achievements']) }}</p>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pending Verif</p>
                                                        <p class="text-2xl font-black text-amber-600 tracking-tight">{{ number_format($stats['pending']) }}</p>
                                                    </div>
                                                    <div class="space-y-1 hidden md:block">
                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Target Selesai</p>
                                                        <p class="text-2xl font-black text-blue-600 dark:text-blue-400 tracking-tight">{{ $selectedPeriod->end_date->format('d/m/y') }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex flex-col sm:flex-row items-center gap-4 border-t border-gray-100 dark:border-gray-700 pt-8" x-data="{ open: false }">
                                                <div class="relative flex-1 w-full sm:w-auto">
                                                    <button @click="open = !open" @click.away="open = false" 
                                                        class="w-full sm:w-auto px-8 py-4 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-black rounded-2xl hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-3 shadow-xl shadow-gray-900/20 dark:shadow-none group">
                                                        <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        UNDUH DATA PERIODE INI
                                                        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    </button>

                                                    <!-- Dropdown Menu -->
                                                    <div x-show="open" 
                                                        x-transition:enter="transition ease-out duration-200"
                                                        x-transition:enter-start="transform opacity-0 -translate-y-4"
                                                        x-transition:enter-end="transform opacity-100 translate-y-0"
                                                        x-transition:leave="transition ease-in duration-150"
                                                        x-transition:leave-start="transform opacity-100 translate-y-0"
                                                        x-transition:leave-end="transform opacity-0 -translate-y-4"
                                                        class="absolute bottom-full left-0 mb-4 w-72 rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.2)] bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-[50] overflow-hidden" 
                                                        x-cloak>
                                                        <div class="p-3 space-y-2">
                                                            <a href="{{ route('admin.export.achievements', ['format' => 'excel', 'period' => $selectedPeriod->id]) }}" 
                                                                class="flex items-center gap-4 px-5 py-4 text-sm text-gray-700 dark:text-gray-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-2xl transition-all group/item">
                                                                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center group-hover/item:scale-110 transition-transform">
                                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                </div>
                                                                <div class="flex flex-col">
                                                                    <span class="font-black">Microsoft Excel</span>
                                                                    <span class="text-[10px] text-gray-500 uppercase tracking-widest font-bold mt-0.5">Format .xlsx</span>
                                                                </div>
                                                            </a>
                                                            <a href="{{ route('admin.export.achievements', ['format' => 'csv', 'period' => $selectedPeriod->id]) }}" 
                                                                class="flex items-center gap-4 px-5 py-4 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-2xl transition-all group/item">
                                                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center group-hover/item:scale-110 transition-transform">
                                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                                                </div>
                                                                <div class="flex flex-col">
                                                                    <span class="font-black">CSV Data Stream</span>
                                                                    <span class="text-[10px] text-gray-500 uppercase tracking-widest font-bold mt-0.5">Format .csv</span>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-gray-400 font-medium italic sm:max-w-[200px]">
                                                    Laporan operasional diperbarui secara otomatis setiap ada validasi baru.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- RINGKASAN DATA GLOBAL -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 sm:gap-6 mt-6">
                            <!-- Total Achievements (Static) - Col 1-2 -->
                            <div
                                class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 lg:p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                        </svg>
                                    </div>
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">HISTORIS</span>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Prestasi (Semua Periode)</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                                    {{ number_format($stats['total_achievements'] ?? 0) }}
                                </p>
                                <a href="{{ route('admin.student-achievements') }}"
                                    class="text-blue-600 dark:text-blue-400 text-xs mt-2 inline-flex items-center gap-1 hover:underline">
                                    Lihat Semua Data
                                </a>
                            </div>

                            <!-- Total Disetujui - Col 3-4 -->
                                <div
                                    class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 lg:p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                                            <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 uppercase">
                                            Approved
                                        </span>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">
                                        Total Disetujui (Semua Periode)
                                    </p>
                                    <div class="flex items-baseline gap-2 mt-1">
                                        <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($stats['approved']) }}
                                        </p>
                                        @php
                                            $approvalPercentage = $stats['total_achievements'] > 0 ? round(($stats['approved'] / $stats['total_achievements']) * 100, 1) : 0;
                                        @endphp
                                        <p class="text-xs font-medium text-gray-500">{{ $approvalPercentage }}% dari total</p>
                                    </div>
                                </div>

                                <!-- Backlog - Col 5-6 -->
                                <div
                                    class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 lg:p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="p-3 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                                            <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 uppercase">Antrean</span>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Menunggu Verifikasi</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                                        {{ number_format($stats['total_pending'] ?? 0) }}
                                    </p>
                                </div>

                                <!-- Rejected - Col 7-8 -->
                                <div
                                    class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 lg:p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                                            <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 uppercase">Ditolak</span>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Ditolak</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                                        {{ number_format($stats['total_rejected'] ?? 0) }}
                                    </p>
                                </div>

                                <!-- Total Mahasiswa Berprestasi - Col 9-12 -->
                                <div
                                    class="sm:col-span-2 lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-5 lg:p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                                    <div class="flex items-center justify-between mb-4 relative z-10">
                                        <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                            <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <div class="text-right">
                                            <span
                                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 uppercase tracking-wider">
                                                Achievers
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-end justify-between relative z-10">
                                        <div>
                                            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Mahasiswa Berprestasi</p>
                                            <div class="flex items-baseline gap-2 mt-1">
                                                <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ number_format($totalDistinctStudents) }}</p>
                                                <p class="text-lg font-semibold text-gray-500 dark:text-gray-400">Orang</p>
                                            </div>
                                            <p class="text-xs text-purple-600 dark:text-purple-400 mt-2 font-medium">Mahasiswa unik dengan prestasi valid</p>
                                        </div>

                                        <!-- Decorative Icon -->
                                        <div class="flex items-end mb-1 opacity-10">
                                            <svg class="w-16 h-16 text-purple-500" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Decorative Background Gradient -->
                                    <div
                                        class="absolute bottom-0 right-0 w-1/3 h-24 bg-gradient-to-t from-purple-500/5 to-transparent pointer-events-none">
                                    </div>
                                </div>
                            </div>

                            <!-- Two-Stage Validation Queues -->
                            @if($isActivePeriod)
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                                    <!-- University Validation Queue -->
                                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 lg:p-6 shadow-sm">
                                        <div class="flex items-center justify-between mb-6">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                                                    </svg>
                                                    Antrean Verifikasi Universitas
                                                </h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Disetujui fakultas, menunggu verifikasi
                                                    universitas</p>
                                            </div>
                                            @if(isset($universityPending) && $universityPending->isNotEmpty())
                                                <span
                                                    class="px-2 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 text-xs font-bold rounded-full">
                                                    {{ $universityPending->count() }}
                                                </span>
                                            @endif
                                        </div>
                                        @if(!isset($universityPending) || $universityPending->isEmpty())
                                            <div class="text-center py-8">
                                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Tidak ada prestasi pending</p>
                                            </div>
                                        @else
                                            <div class="space-y-3 max-h-[400px] overflow-y-auto">
                                                @foreach($universityPending as $achievement)
                                                    <div
                                                        class="flex items-center justify-between p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                                {{ $achievement->event_name }}
                                                            </p>
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $achievement->student?->name }}</p>
                                                                <span class="text-xs text-gray-400">•</span>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                    {{ $achievement->student?->faculty_name }}
                                                                </p>
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
                                                    Lihat Semua Antrean Universitas
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            @endif

                            <!-- Recent Activity & Achievements Grid -->
                            @if($isActivePeriod)
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                                    <!-- Urgent Pending Alerts -->
                                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 lg:p-6 shadow-sm">
                                        <div class="flex items-center justify-between mb-6">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    Perlu Perhatian Segera
                                                </h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pending > 7 hari</p>
                                            </div>
                                            @if($urgentPending->isNotEmpty())
                                                <span
                                                    class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-xs font-bold rounded-full">
                                                    {{ $urgentPending->count() }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($urgentPending->isEmpty())
                                            <div class="text-center py-8">
                                                <svg class="w-12 h-12 mx-auto text-green-300 dark:text-green-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Tidak ada prestasi yang urgent</p>
                                            </div>
                                        @else
                                            <div class="space-y-3 max-h-[400px] overflow-y-auto">
                                                @foreach($urgentPending as $alert)
                                                    <div
                                                        class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $alert->event_name }}
                                                            </p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $alert->student?->name }} •
                                                                {{ $alert->submitted_at->diffInDays() }} hari
                                                            </p>
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
                                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 lg:p-6 shadow-sm">
                                        <div class="flex items-center justify-between mb-6">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    Aktivitas & Prestasi Terbaru
                                                </h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">5 aktivitas terakhir</p>
                                            </div>
                                            <a href="{{ route('admin.student-achievements') }}"
                                                class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400 font-medium flex items-center gap-1">
                                                Lihat Semua
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        </div>
                                        @if($recentValidations->isEmpty() && $recentAchievements->isEmpty())
                                            <div class="text-center py-8">
                                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Belum ada aktivitas</p>
                                            </div>
                                        @else
                                            <div class="space-y-3 max-h-[400px] overflow-y-auto">
                                                @php
                                                    // Combine validations and achievements, then sort by date and take 5
                                                    $combinedActivities = collect();

                                                    // Add validations with type marker
                                                    foreach ($recentValidations as $log) {
                                                        $combinedActivities->push([
                                                            'type' => 'validation',
                                                            'data' => $log,
                                                            'date' => $log->validated_at
                                                        ]);
                                                    }

                                                    // Add achievements with type marker
                                                    foreach ($recentAchievements as $achievement) {
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
                                                        <div
                                                            class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900/70 transition-colors">
                                                            <div
                                                                class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $log->new_status === 'Disetujui' ? 'bg-green-100 dark:bg-green-900/30' : ($log->new_status === 'Ditolak' ? 'bg-red-100 dark:bg-red-900/30' : 'bg-blue-100 dark:bg-blue-900/30') }}">
                                                                @if($log->new_status === 'Disetujui')
                                                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd"
                                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                            clip-rule="evenodd" />
                                                                    </svg>
                                                                @elseif($log->new_status === 'Ditolak')
                                                                    <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd"
                                                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                                            clip-rule="evenodd" />
                                                                    </svg>
                                                                @else
                                                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd"
                                                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                                            clip-rule="evenodd" />
                                                                    </svg>
                                                                @endif
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <div class="flex items-center gap-2">
                                                                    <span
                                                                        class="px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                                        Verifikasi
                                                                    </span>
                                                                    <span
                                                                        class="px-2 py-0.5 rounded-full text-xs font-medium {{ $log->new_status === 'Disetujui' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($log->new_status === 'Ditolak' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400') }}">
                                                                        {{ $log->new_status === 'Disetujui' ? 'Selesai Diverifikasi' : ($log->new_status === 'Ditolak' ? 'Ditolak' : $log->new_status) }}
                                                                    </span>
                                                                </div>
                                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate mt-1">
                                                                    {{ $log->studentAchievement->event_name }}
                                                                </p>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                    {{ $log->validator->name }} • {{ $log->validated_at->diffForHumans() }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @else
                                                        @php $achievement = $activity['data']; @endphp
                                                        <div
                                                            class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900/70 transition-colors">
                                                            <div class="flex items-start justify-between gap-3">
                                                                <div class="flex items-start gap-3 flex-1 min-w-0">
                                                                    <div
                                                                        class="flex-shrink-0 h-8 w-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                                                        <span
                                                                            class="text-purple-600 dark:text-purple-400 font-medium text-xs">{{ substr($achievement->student?->name ?? 'N', 0, 1) }}</span>
                                                                    </div>
                                                                    <div class="flex-1 min-w-0">
                                                                        <div class="flex items-center gap-2">
                                                                            <span
                                                                                class="px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                                                                Pengajuan
                                                                            </span>
                                                                            @php
                                                                                $statusColor = match ($achievement->validation_status) {
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
                                                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate mt-1">
                                                                            {{ $achievement->event_name }}
                                                                        </p>
                                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                            {{ $achievement->student?->name ?? '-' }} •
                                                                            {{ $achievement->created_at->diffForHumans() }}
                                                                        </p>
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
                                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 lg:p-6 shadow-sm">
                                        <div class="flex items-center justify-between mb-6">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                    Top 5 Mahasiswa Berprestasi
                                                </h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Berdasarkan jumlah prestasi disetujui</p>
                                            </div>
                                        </div>
                                        @if($topPerformers->isEmpty())
                                            <div class="text-center py-8">
                                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
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
                                                                    <div
                                                                        class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center shadow-lg">
                                                                        <span class="text-white font-bold text-lg">1</span>
                                                                    </div>
                                                                @elseif($index === 1)
                                                                    <div
                                                                        class="w-12 h-12 bg-gradient-to-br from-gray-300 to-gray-500 rounded-full flex items-center justify-center shadow-lg">
                                                                        <span class="text-white font-bold text-lg">2</span>
                                                                    </div>
                                                                @elseif($index === 2)
                                                                    <div
                                                                        class="w-12 h-12 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full flex items-center justify-center shadow-lg">
                                                                        <span class="text-white font-bold text-lg">3</span>
                                                                    </div>
                                                                @else
                                                                    <div
                                                                        class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                                                        <span
                                                                            class="text-purple-600 dark:text-purple-400 font-bold text-lg">{{ $index + 1 }}</span>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $student->name }}
                                                                </p>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $student->student_id }}</p>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $student->faculty }}</p>
                                                            </div>

                                                            <div class="text-right flex-shrink-0">
                                                                <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-purple-600 dark:text-purple-400">
                                                                    {{ $student->achievements_count }}
                                                                </p>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">Prestasi</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Global Insights Row -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 sm:gap-6 mt-6">
                                <!-- Global Status Doughnut - Col 1-4 -->
                                <div
                                    class="sm:col-span-2 lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 lg:p-6 shadow-sm">
                                    <div class="mb-6">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status Prestasi Global</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Distribusi status dari seluruh periode tercatat
                                        </p>
                                    </div>
                                    <div class="h-64 relative">
                                        <canvas id="globalStatusChart"></canvas>
                                    </div>
                                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <div class="text-center">
                                            <p class="text-[10px] text-gray-500 uppercase font-bold">Menunggu</p>
                                            <p class="text-sm font-bold text-amber-600">
                                                {{ number_format($stats['global_status_stats']['menunggu']) }}
                                            </p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-[10px] text-gray-500 uppercase font-bold">Disetujui</p>
                                            <p class="text-sm font-bold text-emerald-600">
                                                {{ number_format($stats['global_status_stats']['disetujui']) }}
                                            </p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-[10px] text-gray-500 uppercase font-bold">Ditolak</p>
                                            <p class="text-sm font-bold text-red-600">
                                                {{ number_format($stats['global_status_stats']['ditolak']) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Distribusi Tingkat Prestasi - Col 5-8 (col-span-4) -->
                                <div
                                    class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 lg:p-6 shadow-sm">
                                    <div class="mb-6">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Distribusi Tingkat Prestasi</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tingkat capaian prestasi semua periode</p>
                                    </div>
                                    <div class="h-64 relative">
                                        <canvas id="globalLevelChart"></canvas>
                                    </div>
                                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <div class="text-center">
                                            <p class="text-[10px] text-gray-500 uppercase font-bold">Universitas</p>
                                            <p class="text-sm font-bold text-blue-600">
                                                {{ number_format($stats['global_level_distribution']['Universitas']) }}
                                            </p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-[10px] text-gray-500 uppercase font-bold">Nasional</p>
                                            <p class="text-sm font-bold text-purple-600">
                                                {{ number_format($stats['global_level_distribution']['Nasional']) }}
                                            </p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-[10px] text-gray-500 uppercase font-bold">Internasional</p>
                                            <p class="text-sm font-bold text-indigo-600">
                                                {{ number_format($stats['global_level_distribution']['Internasional']) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Global Category Doughnut - Col 9-12 (col-span-4) -->
                                <div
                                    class="sm:col-span-2 lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 lg:p-6 shadow-sm">
                                    <div class="mb-6">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Distribusi Kategori Prestasi</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pembagian kategori prestasi semua periode</p>
                                    </div>
                                    <div class="h-64 relative">
                                        <canvas id="globalCategoryChart"></canvas>
                                    </div>
                                    <div class="mt-4 flex flex-wrap justify-center gap-x-4 gap-y-1">
                                        @foreach($stats['global_category_distribution']->take(4) as $cat)
                                            <div class="flex items-center gap-1.5">
                                                <div class="w-2 h-2 rounded-full"
                                                    style="background-color: {{ ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#ec4899'][$loop->index] }}">
                                                </div>
                                                <p class="text-[10px] text-gray-600 dark:text-gray-400 font-medium whitespace-nowrap">
                                                    {{ $cat->category }}
                                                </p>
                                            </div>
                                        @endforeach
                                        @if($stats['global_category_distribution']->count() > 4)
                                            <p class="text-[10px] text-gray-400">...</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Row 3: Top Rankings & Unit Breakdown -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 sm:gap-6 mt-6">
                                <!-- Top Mahasiswa Berprestasi - Col 1-4 (col-span-4) -->
                                <div
                                    class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 lg:p-6 shadow-sm flex flex-col">
                                    <div class="mb-5">
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                            <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            Top Mahasiswa Berprestasi
                                        </h3>
                                        <p class="text-[10px] text-gray-500 mt-1">Peringkat berdasarkan jumlah prestasi disetujui sepanjang masa</p>
                                    </div>

                                    <div class="space-y-4 flex-1">
                                        @forelse($topStudentsGlobal->take(5) as $index => $student)
                                            <div class="group">
                                                <div class="flex justify-between items-center mb-1.5">
                                                    <div class="flex items-center gap-3 min-w-0">
                                                        <span class="text-[10px] font-black w-5 text-gray-400 flex-shrink-0">#{{ $index + 1 }}</span>
                                                        <div class="flex flex-col min-w-0">
                                                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate"
                                                                title="{{ $student->name }}">{{ $student->name }}</span>
                                                            <span class="text-[9px] text-gray-500 dark:text-gray-400 truncate">{{ $student->program_study ?? $student->faculty ?? '-' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col items-end flex-shrink-0 ml-2">
                                                        <span class="text-xs font-black text-purple-600 dark:text-purple-400">{{ $student->approved_count }}</span>
                                                        <span class="text-[8px] text-gray-500 font-bold">Prestasi</span>
                                                    </div>
                                                </div>
                                                <div class="w-full bg-gray-100 dark:bg-gray-700/50 rounded-full h-1.5 overflow-hidden">
                                                    @php
                                                        $maxStudent = $topStudentsGlobal->max('approved_count') ?: 1;
                                                        $pct = ($student->approved_count / $maxStudent) * 100;
                                                    @endphp
                                                    <div class="bg-gradient-to-r from-purple-500 to-indigo-500 h-1.5 rounded-full shadow-[0_0_8px_rgba(139,92,246,0.3)] transition-all duration-700" style="width: {{ $pct }}%"></div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-12">
                                                <div class="mx-auto w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-3">
                                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                                    </svg>
                                                </div>
                                                <p class="text-xs text-gray-400 italic">Belum ada data mahasiswa berprestasi</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Prestasi per Fakultas - Col 5-8 (col-span-4) -->
                                <div
                                    class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 lg:p-6 shadow-sm">
                                    <div class="mb-4">
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Prestasi per Fakultas</h3>
                                        <p class="text-[10px] text-gray-500 mt-0.5">Total akumulasi prestasi per unit</p>
                                    </div>

                                    <div class="h-64 relative">
                                        <canvas id="facultyComparisonChart"></canvas>
                                    </div>

                                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                                        <p class="text-[9px] text-gray-400 text-center italic">Grafik akumulasi prestasi per fakultas</p>
                                    </div>
                                </div>

                                <!-- Top Program Studi - Col 9-12 (col-span-4) -->
                                <div
                                    class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 lg:p-6 shadow-sm flex flex-col">
                                    <div class="mb-5">
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Top Program Studi</h3>
                                        <p class="text-[10px] text-gray-500 mt-0.5">Peringkat berdasarkan total prestasi</p>
                                    </div>

                                    <div class="space-y-4 flex-1">
                                        @forelse($topProgramStudies->take(5) as $prodi)
                                            <div class="group">
                                                <div class="flex justify-between items-center mb-1.5">
                                                    <div class="flex flex-col min-w-0">
                                                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate"
                                                            title="{{ $prodi->prodi }}">
                                                            {{ $prodi->prodi }}
                                                        </span>
                                                        <span class="text-[9px] text-gray-500 truncate">{{ $prodi->faculty }}</span>
                                                    </div>
                                                    <div class="flex flex-col items-end flex-shrink-0 ml-2">
                                                        <span class="text-xs font-black text-blue-600 dark:text-blue-400">{{ $prodi->total }}</span>
                                                        <span class="text-[8px] text-emerald-600 font-bold">{{ $prodi->approved }} OK</span>
                                                    </div>
                                                </div>
                                                <div class="w-full bg-gray-100 dark:bg-gray-700/50 rounded-full h-1.5 overflow-hidden">
                                                    @php
                                                        $maxProdi = $topProgramStudies->max('total') ?: 1;
                                                        $percentage = ($prodi->total / $maxProdi) * 100;
                                                        $approvedPercentage = $prodi->total > 0 ? ($prodi->approved / $prodi->total) * 100 : 0;
                                                    @endphp
                                                    <div class="bg-blue-500 h-1.5 rounded-full relative" style="width: {{ $percentage }}%">
                                                        <div class="absolute inset-0 bg-emerald-400 opacity-40"
                                                            style="width: {{ $approvedPercentage }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-12">
                                                <div
                                                    class="mx-auto w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-3">
                                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                    </svg>
                                                </div>
                                                <p class="text-xs text-gray-400 italic">Data prodi belum tersedia</p>
                                            </div>
                                        @endforelse
                                    </div>

                                    <div class="mt-6 pt-4 border-t border-gray-50 dark:border-gray-700">
                                        <button type="button" onclick="openUnitModal()"
                                            class="w-full py-2 bg-gray-50 dark:bg-gray-700/50 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-[10px] font-bold text-blue-600 dark:text-blue-400 rounded-lg transition-colors flex items-center justify-center gap-2 uppercase tracking-tighter">
                                            Lihat Sebaran Unit Lengkap
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 4: Achievement Trend & Data Quality -->
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 mt-6">
                                <!-- Multi-Period Achievement Trend - Col 1-8 (col-span-8) -->
                                <div
                                    class="lg:col-span-8 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 lg:p-6 shadow-sm">
                                    <div class="flex items-center justify-between mb-6">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tren Prestasi Multi-Periode</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Perkembangan total prestasi lintas semester</p>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <span class="flex items-center gap-1.5 text-xs text-gray-500 font-medium">
                                                <span class="w-2.5 h-2.5 bg-blue-500 rounded-full"></span>
                                                Approved
                                            </span>
                                            <span class="flex items-center gap-1.5 text-xs text-gray-500 font-medium">
                                                <span class="w-2.5 h-2.5 bg-gray-300 dark:bg-gray-600 rounded-full"></span>
                                                Total
                                            </span>
                                        </div>
                                    </div>
                                    <div class="h-80 relative">
                                        <canvas id="multiPeriodTrendChart"></canvas>
                                    </div>
                                </div>

                                <!-- Anomali & Data Bermasalah - Col 9-12 (col-span-4) -->
                                <div
                                    class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 lg:p-6 shadow-sm">
                                    <div class="mb-6">
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Kualitas Data</h3>
                                        <p class="text-[10px] text-gray-500 mt-1">Audit anomali dan keaslian data</p>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-1 gap-4">
                                        <!-- Duplikasi -->
                                        <div class="flex flex-col p-4 rounded-lg bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-800/20 hover:shadow-md transition-shadow">
                                            <div class="flex items-center justify-between mb-3">
                                                <span class="text-xs font-semibold text-red-800 dark:text-red-300">Duplikasi</span>
                                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div class="text-2xl font-black text-red-700 dark:text-red-300 mb-2">{{ $anomalies['duplicates'] }}</div>
                                            <button onclick="openAnomalyModal('duplicates')" 
                                               class="inline-flex items-center gap-1 text-[10px] font-bold text-red-600 dark:text-red-400 hover:underline uppercase tracking-wider">
                                                Detail
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Tanpa Dokumen -->
                                        <div class="flex flex-col p-4 rounded-lg bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/20 hover:shadow-md transition-shadow">
                                            <div class="flex items-center justify-between mb-3">
                                                <span class="text-xs font-semibold text-amber-800 dark:text-amber-300">Tanpa Dokumen</span>
                                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                            <div class="text-2xl font-black text-amber-700 dark:text-amber-300 mb-2">{{ $anomalies['no_docs'] }}</div>
                                            <button onclick="openAnomalyModal('no_docs')" 
                                               class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-600 dark:text-amber-400 hover:underline uppercase tracking-wider">
                                                Detail
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Draft -->
                                        <div class="flex flex-col p-4 rounded-lg bg-purple-50 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-800/20 hover:shadow-md transition-shadow">
                                            <div class="flex items-center justify-between mb-3">
                                                <span class="text-xs font-semibold text-purple-800 dark:text-purple-300">Draft</span>
                                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <div class="text-2xl font-black text-purple-700 dark:text-purple-300 mb-2">{{ $anomalies['abandoned_drafts'] }}</div>
                                            <button onclick="openAnomalyModal('abandoned_drafts')" 
                                               class="inline-flex items-center gap-1 text-[10px] font-bold text-purple-600 dark:text-purple-400 hover:underline uppercase tracking-wider">
                                                Detail
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 5: Master Data Summary -->
                            <div class="grid grid-cols-1 gap-6 mt-6">
                                <div
                                    class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 lg:p-6 shadow-sm overflow-hidden relative">
                                    <!-- Decorative Background Element -->
                                    <div
                                        class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-gray-50 dark:bg-gray-700/30 rounded-full blur-3xl opacity-50">
                                    </div>

                                    <div class="flex items-center justify-between mb-8 relative z-10">
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                                <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                                Ringkasan Master Data
                                            </h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Status entitas pendukung sistem aplikasi
                                            </p>
                                        </div>
                                        <div
                                            class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-bold rounded-full border border-green-200 dark:border-green-800 flex items-center gap-2">
                                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                            Sistem Operasional
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-8 relative z-10">
                                        <!-- Faculty Count -->
                                        <div
                                            class="flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                            <div
                                                class="p-3 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg group-hover:scale-110 transition-transform">
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-widest">
                                                    Fakultas</p>
                                                <p class="text-xl sm:text-2xl lg:text-3xl font-black text-gray-900 dark:text-white leading-tight">
                                                    {{ $masterData['faculties_count'] }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Prodi Count -->
                                        <div
                                            class="flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                            <div
                                                class="p-3 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-lg group-hover:scale-110 transition-transform">
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.582 0.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332 0.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332 0.477-4.5 1.253" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-widest">
                                                    Program Studi</p>
                                                <p class="text-xl sm:text-2xl lg:text-3xl font-black text-gray-900 dark:text-white leading-tight">
                                                    {{ $masterData['prodis_count'] }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Users Count -->
                                        <div
                                            class="flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                            <div
                                                class="p-3 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg group-hover:scale-110 transition-transform">
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-widest">
                                                    Operator & Validator</p>
                                                <p class="text-xl sm:text-2xl lg:text-3xl font-black text-gray-900 dark:text-white leading-tight">
                                                    {{ $masterData['validators_count'] + $masterData['operators_count'] }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Sync Status -->
                                        <div
                                            class="flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                            <div
                                                class="p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg group-hover:scale-110 transition-transform">
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-widest">
                                                    Sinkronisasi SIMAPRES</p>
                                                <div class="flex items-baseline gap-2">
                                                    <p class="text-xl sm:text-2xl lg:text-3xl font-black text-emerald-600 dark:text-emerald-400 leading-tight">
                                                        {{ $masterData['sync_status']['percentage'] }}%
                                                    </p>
                                                    <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">at
                                                        {{ $masterData['sync_status']['last_sync'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 6: GLOBAL REPORT EXPORT -->
                            <div class="mt-8">
                                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-2xl overflow-hidden relative group">
                                    <!-- Decorative Elements -->
                                    <div class="absolute top-0 right-0 w-96 h-96 bg-blue-50 dark:bg-blue-900/10 rounded-full -mr-48 -mt-48 transition-transform group-hover:scale-110 duration-700"></div>
                                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-indigo-50 dark:bg-indigo-900/10 rounded-full -ml-32 -mb-32 transition-transform group-hover:scale-110 duration-700"></div>

                                    <div class="relative z-10 flex flex-col lg:flex-row items-stretch">
                                        <!-- Left Side: Global Status -->
                                        <div class="lg:w-1/3 p-8 lg:p-12 bg-gray-50 dark:bg-gray-900/50 flex flex-col items-center justify-center text-center border-b lg:border-b-0 lg:border-r border-gray-100 dark:border-gray-700">
                                            <div class="relative mb-6">
                                                <div class="absolute inset-0 bg-blue-500 rounded-full blur-2xl opacity-20 animate-pulse"></div>
                                                <div class="w-24 h-24 bg-white dark:bg-gray-800 rounded-full shadow-xl flex items-center justify-center border-4 border-blue-500 relative">
                                                    <svg class="w-12 h-12 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a2 2 0 012-2v11a2 2 0 01-2 2H3a1 1 0 01-1-1v-1a1 1 0 011-1h1V7zM17 7a2 2 0 012 2v10a1 1 0 01-1 1h-1a1 1 0 01-1-1v-1a2 2 0 01-2-2V5a2 2 0 012-2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <h4 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tighter mb-2">Laporan Global</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-4">Akumulasi seluruh data prestasi dari berbagai periode akademik.</p>
                                            <div class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 rounded-full border border-blue-200 dark:border-blue-800/50">
                                                <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                                                <span class="text-[10px] font-black uppercase tracking-widest leading-none">Status: Consolidated</span>
                                            </div>
                                        </div>

                                        <!-- Right Side: Details & Actions -->
                                        <div class="flex-1 p-8 lg:p-12 flex flex-col justify-between">
                                            <div>
                                                <h3 class="text-3xl lg:text-4xl font-black text-gray-900 dark:text-white tracking-tight mb-4">Ringkasan Laporan Konsolidasi</h3>
                                                <p class="text-gray-500 dark:text-gray-400 text-base font-medium max-w-xl mb-8">
                                                    Ekspor data agregat untuk kebutuhan akreditasi dan pelaporan tahunan universitas. 
                                                    Data mencakup seluruh prestasi yang telah divalidasi hingga saat ini.
                                                </p>

                                                <div class="grid grid-cols-2 md:grid-cols-3 gap-8 mb-10">
                                                    <div class="space-y-1">
                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Akumulasi</p>
                                                        <p class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ number_format($stats['total_achievements'] ?? 0) }}</p>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Fakultas Aktif</p>
                                                        <p class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $masterData['faculties_count'] }}</p>
                                                    </div>
                                                    <div class="space-y-1 hidden md:block">
                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Update Terakhir</p>
                                                        <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">Real-time</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex flex-col sm:flex-row items-center gap-4 border-t border-gray-100 dark:border-gray-700 pt-8" x-data="{ open: false }">
                                                <div class="relative flex-1 w-full sm:w-auto">
                                                    <button @click="open = !open" @click.away="open = false" 
                                                        class="w-full sm:w-auto px-8 py-4 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-black rounded-2xl hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-3 shadow-xl shadow-gray-900/20 dark:shadow-none group">
                                                        <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                        </svg>
                                                        UNDUH LAPORAN GLOBAL
                                                        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    </button>

                                                    <!-- Dropdown Menu -->
                                                    <div x-show="open" 
                                                        x-transition:enter="transition ease-out duration-200"
                                                        x-transition:enter-start="transform opacity-0 -translate-y-4"
                                                        x-transition:enter-end="transform opacity-100 translate-y-0"
                                                        x-transition:leave="transition ease-in duration-150"
                                                        x-transition:leave-start="transform opacity-100 translate-y-0"
                                                        x-transition:leave-end="transform opacity-0 -translate-y-4"
                                                        class="absolute bottom-full left-0 mb-4 w-72 rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.2)] bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-[50] overflow-hidden" 
                                                        x-cloak>
                                                        <div class="p-3 space-y-2">
                                                            <a href="{{ route('admin.export.achievements', ['format' => 'excel', 'period' => 'all']) }}" 
                                                                class="flex items-center gap-4 px-5 py-4 text-sm text-gray-700 dark:text-gray-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-2xl transition-all group/item">
                                                                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center group-hover/item:scale-110 transition-transform">
                                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                </div>
                                                                <div class="flex flex-col">
                                                                    <span class="font-black">Microsoft Excel</span>
                                                                    <span class="text-[10px] text-gray-500 uppercase tracking-widest font-bold mt-0.5">Format .xlsx</span>
                                                                </div>
                                                            </a>
                                                            <a href="{{ route('admin.export.achievements', ['format' => 'csv', 'period' => 'all']) }}" 
                                                                class="flex items-center gap-4 px-5 py-4 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-2xl transition-all group/item">
                                                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center group-hover/item:scale-110 transition-transform">
                                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                                                </div>
                                                                <div class="flex flex-col">
                                                                    <span class="font-black">CSV Data Stream</span>
                                                                    <span class="text-[10px] text-gray-500 uppercase tracking-widest font-bold mt-0.5">Format .csv</span>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-gray-400 font-medium italic sm:max-w-[200px]">
                                                    Data global mencakup arsip dan data periode berjalan.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endif

                        <!-- Unit Distribution Modal will be loaded dynamically via JavaScript below -->
                            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const isDark = document.documentElement.classList.contains('dark');
                                    const textColor = isDark ? '#9ca3af' : '#6b7280';
                                    const gridColor = isDark ? '#374151' : '#e5e7eb';


                                    @if($isInactivePeriod)
                                        // --- ARCHIVED CHARTS ---

                                        // Archived Level Distribution Chart
                                        const archLevelData = @json($levelDistribution);
                                        new Chart(document.getElementById('archivedLevelChart'), {
                                            type: 'doughnut',
                                            data: {
                                                labels: ['Universitas', 'Nasional', 'Internasional'],
                                                datasets: [{
                                                    data: [archLevelData.Universitas, archLevelData.Nasional, archLevelData.Internasional],
                                                    backgroundColor: ['#3b82f6', '#8b5cf6', '#6366f1'],
                                                    borderWidth: 2,
                                                    borderColor: isDark ? '#1f2937' : '#ffffff',
                                                }]
                                            },
                                                options: {
                                                    responsive: true,
                                                    maintainAspectRatio: false,
                                                    plugins: { legend: { position: 'bottom', labels: { color: textColor, font: { size: 10 }, usePointStyle: true } } },
                                                    cutout: '70%'
                                                }
                                        });

                                        // Archived Category Distribution Chart
                                        const archCategoryData = @json($categoryDistribution);
                                        new Chart(document.getElementById('archivedCategoryChart'), {
                                            type: 'doughnut',
                                            data: {
                                                labels: archCategoryData.map(d => d.category),
                                                datasets: [{
                                                    data: archCategoryData.map(d => d.total),
                                                    backgroundColor: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#ec4899'],
                                                    borderWidth: 2,
                                                    borderColor: isDark ? '#1f2937' : '#ffffff',
                                                }]
                                            },
                                                options: {
                                                    responsive: true,
                                                    maintainAspectRatio: false,
                                                    plugins: { legend: { position: 'bottom', labels: { color: textColor, font: { size: 9 }, usePointStyle: true, boxWidth: 6 } } },
                                                    cutout: '70%'
                                                }
                                        });

                                        // Archived Status Distribution Chart (Disetujui + Ditolak + Dibatalkan/Expired)
                                        new Chart(document.getElementById('archivedStatusChart'), {
                                            type: 'doughnut',
                                            data: {
                                                labels: ['Disetujui', 'Ditolak', 'Dibatalkan (Expired)'],
                                                datasets: [{
                                                    data: [@json($stats['approved']), @json($stats['total_rejected']), @json($archivedStats['expired_count'] ?? 0)],
                                                    backgroundColor: ['#10b981', '#ef4444', '#f59e0b'],
                                                    borderWidth: 2,
                                                    borderColor: isDark ? '#1f2937' : '#ffffff',
                                                }]
                                            },
                                                options: {
                                                    responsive: true,
                                                    maintainAspectRatio: false,
                                                    plugins: { legend: { display: false } },
                                                    cutout: '75%'
                                                }
                                        });

                                        // Archived Faculty Comparison Chart
                                        const archFacultyData = @json($facultyComparison);
                                        new Chart(document.getElementById('archivedFacultyChart'), {
                                            type: 'bar',
                                            data: {
                                                labels: archFacultyData.map(d => d.faculty),
                                                datasets: [{
                                                    label: 'Total Prestasi',
                                                    data: archFacultyData.map(d => d.total),
                                                    backgroundColor: '#3b82f6',
                                                    borderRadius: 6
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: { legend: { display: false } },
                                                scales: {
                                                    y: { beginAtZero: true, ticks: { color: textColor }, grid: { color: gridColor } },
                                                    x: { ticks: { color: textColor }, grid: { display: false } }
                                                }
                                            }
                                        });

                                        // Archived Ratio Chart
                                        const archRatioData = @json($archivedStats['faculty_ratios'] ?? []);
                                        new Chart(document.getElementById('archivedRatioChart'), {
                                            type: 'bar',
                                            data: {
                                                labels: archRatioData.map(d => d.faculty),
                                                datasets: [{
                                                    label: 'Rasio (%)',
                                                    data: archRatioData.map(d => d.ratio),
                                                    backgroundColor: '#8b5cf6',
                                                    borderRadius: 6
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                indexAxis: 'y',
                                                plugins: { legend: { display: false } },
                                                scales: {
                                                    x: { beginAtZero: true, ticks: { color: textColor }, grid: { color: gridColor } },
                                                    y: { ticks: { color: textColor }, grid: { display: false } }
                                                }
                                            }
                                        });
                                    @endif

                                    @if($isActivePeriod)
                                        // --- ACTIVE CHARTS ---

                                        // Active Status Chart
                                        new Chart(document.getElementById('activeStatusChart'), {
                                            type: 'doughnut',
                                            data: {
                                                labels: ['Menunggu Verifikasi', 'Selesai Diverifikasi', 'Ditolak'],
                                                datasets: [{
                                                    data: [@json($stats['pending']), @json($stats['approved']), @json($stats['total_rejected'])],
                                                    backgroundColor: ['#f59e0b', '#10b981', '#ef4444'],
                                                    borderWidth: 2,
                                                    borderColor: isDark ? '#1f2937' : '#ffffff',
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: { legend: { position: 'bottom', labels: { color: textColor, font: { size: 10 }, usePointStyle: true } } },
                                                cutout: '70%'
                                            }
                                        });

                                        // Active Level Chart
                                        const actLevelData = @json($levelDistribution);
                                        new Chart(document.getElementById('activeLevelChart'), {
                                            type: 'doughnut',
                                            data: {
                                                labels: ['Universitas', 'Nasional', 'Internasional'],
                                                datasets: [{
                                                    data: [actLevelData.Universitas, actLevelData.Nasional, actLevelData.Internasional],
                                                    backgroundColor: ['#3b82f6', '#8b5cf6', '#6366f1'],
                                                    borderWidth: 2,
                                                    borderColor: isDark ? '#1f2937' : '#ffffff',
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: { legend: { position: 'bottom', labels: { color: textColor, font: { size: 10 }, usePointStyle: true } } },
                                                cutout: '70%'
                                            }
                                        });

                                        // Active Category Chart
                                        const actCatData = @json($categoryDistribution);
                                        new Chart(document.getElementById('activeCategoryChart'), {
                                            type: 'doughnut',
                                            data: {
                                                labels: actCatData.map(d => d.category),
                                                datasets: [{
                                                    data: actCatData.map(d => d.total),
                                                    backgroundColor: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#ec4899'],
                                                    borderWidth: 2,
                                                    borderColor: isDark ? '#1f2937' : '#ffffff',
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: { legend: { position: 'bottom', labels: { color: textColor, font: { size: 9 }, usePointStyle: true, boxWidth: 6 } } },
                                                cutout: '70%'
                                            }
                                        });

                                        // Faculty Backlog Chart
                                        const backData = @json($activeStats['faculty_backlog'] ?? []);
                                        new Chart(document.getElementById('facultyBacklogChart'), {
                                            type: 'bar',
                                            data: {
                                                labels: backData.map(d => d.faculty),
                                                datasets: [{
                                                    label: 'Pending',
                                                    data: backData.map(d => d.count),
                                                    backgroundColor: '#f59e0b',
                                                    borderRadius: 6
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: { legend: { display: false } },
                                                scales: {
                                                    y: { beginAtZero: true, ticks: { color: textColor, font: { size: 10 } }, grid: { color: gridColor } },
                                                    x: { ticks: { color: textColor, font: { size: 9 }, maxRotation: 0, maxTicksLimit: 8 }, grid: { display: false } }
                                                }
                                            }
                                        });

                                        // Daily Activity Chart
                                        const dData = @json($activeStats['daily_trend'] ?? []);
                                        new Chart(document.getElementById('dailyActivityChart'), {
                                            type: 'line',
                                            data: {
                                                labels: dData.map(d => d.date),
                                                datasets: [{
                                                    label: 'Verifikasi',
                                                    data: dData.map(d => d.count),
                                                    borderColor: '#10b981',
                                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                                    fill: true,
                                                    tension: 0.4,
                                                    borderWidth: 3,
                                                    pointRadius: 4,
                                                    pointBackgroundColor: '#10b981'
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: { legend: { display: false } },
                                                scales: {
                                                    y: { beginAtZero: true, ticks: { color: textColor, font: { size: 10 } }, grid: { color: gridColor, drawBorder: false } },
                                                    x: { ticks: { color: textColor, font: { size: 10 } }, grid: { display: false } }
                                                }
                                            }
                                        });
                                    @endif

                                    // Global Status Chart
                                    const globalStatusData = @json($stats['global_status_stats']);
                                    const globalStatusEl = document.getElementById('globalStatusChart');
                                    if (globalStatusData && globalStatusEl) {
                                        new Chart(globalStatusEl, {
                                            type: 'doughnut',
                                            data: {
                                                labels: ['Menunggu Verifikasi', 'Selesai Diverifikasi', 'Ditolak'],
                                                datasets: [{
                                                    data: [globalStatusData.menunggu, globalStatusData.disetujui, globalStatusData.ditolak],
                                                    backgroundColor: ['#f59e0b', '#10b981', '#ef4444'],
                                                    borderWidth: 2,
                                                    borderColor: isDark ? '#1f2937' : '#ffffff',
                                                    hoverOffset: 12
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: {
                                                    legend: {
                                                        display: false
                                                    },
                                                    tooltip: {
                                                        backgroundColor: isDark ? '#1f2937' : '#ffffff',
                                                        titleColor: isDark ? '#f9fafb' : '#111827',
                                                        bodyColor: isDark ? '#d1d5db' : '#374151',
                                                        borderColor: isDark ? '#374151' : '#e5e7eb',
                                                        borderWidth: 1,
                                                        padding: 10
                                                    }
                                                },
                                                cutout: '70%'
                                            }
                                        });
                                    }

                                    // Global Level Chart
                                    const globalLevelData = @json($stats['global_level_distribution']);
                                    const globalLevelEl = document.getElementById('globalLevelChart');
                                    if (globalLevelData && globalLevelEl) {
                                        new Chart(globalLevelEl, {
                                            type: 'doughnut',
                                            data: {
                                                labels: ['Universitas', 'Nasional', 'Internasional'],
                                                datasets: [{
                                                    data: [globalLevelData.Universitas, globalLevelData.Nasional, globalLevelData.Internasional],
                                                    backgroundColor: ['#3b82f6', '#8b5cf6', '#6366f1'],
                                                    borderWidth: 2,
                                                    borderColor: isDark ? '#1f2937' : '#ffffff',
                                                    hoverOffset: 12
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: {
                                                    legend: {
                                                        display: false
                                                    },
                                                    tooltip: {
                                                        backgroundColor: isDark ? '#1f2937' : '#ffffff',
                                                        titleColor: isDark ? '#f9fafb' : '#111827',
                                                        bodyColor: isDark ? '#d1d5db' : '#374151',
                                                        borderColor: isDark ? '#374151' : '#e5e7eb',
                                                        borderWidth: 1,
                                                        padding: 10
                                                    }
                                                },
                                                cutout: '70%'
                                            }
                                        });
                                    }

                                    // Global Category Chart
                                    const globalCategoryData = @json($stats['global_category_distribution']);
                                    const globalCategoryEl = document.getElementById('globalCategoryChart');
                                    if (globalCategoryData && globalCategoryData.length > 0 && globalCategoryEl) {
                                        new Chart(globalCategoryEl, {
                                            type: 'doughnut',
                                            data: {
                                                labels: globalCategoryData.map(d => d.category),
                                                datasets: [{
                                                    data: globalCategoryData.map(d => d.total),
                                                    backgroundColor: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#06b6d4', '#f97316'],
                                                    borderWidth: 2,
                                                    borderColor: isDark ? '#1f2937' : '#ffffff',
                                                    hoverOffset: 12
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: {
                                                    legend: { display: false },
                                                    tooltip: {
                                                        backgroundColor: isDark ? '#1f2937' : '#ffffff',
                                                        titleColor: isDark ? '#f9fafb' : '#111827',
                                                        bodyColor: isDark ? '#d1d5db' : '#374151',
                                                        borderColor: isDark ? '#374151' : '#e5e7eb',
                                                        borderWidth: 1,
                                                        padding: 10
                                                    }
                                                },
                                                cutout: '70%'
                                            }
                                        });
                                    }

                                    // Multi-Period Trend Chart (Area with Gradient)
                                    const trendData = @json($globalTrend ?? []);
                                    const trendEl = document.getElementById('multiPeriodTrendChart');
                                    if (trendData && trendData.length > 0 && trendEl) {
                                        // Reverse for chronological order
                                        const sortedTrend = [...trendData].reverse();
                                        
                                        // Create gradient for Approved line
                                        const ctx = trendEl.getContext('2d');
                                        const approvedGradient = ctx.createLinearGradient(0, 0, 0, 320);
                                        approvedGradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)'); // Emerald with 20% opacity
                                        approvedGradient.addColorStop(1, 'rgba(16, 185, 129, 0)');   // Fade to transparent
                                        
                                        new Chart(trendEl, {
                                            type: 'line',
                                            data: {
                                                labels: sortedTrend.map(d => d.period),
                                                datasets: [
                                                    {
                                                        label: 'Total Prestasi',
                                                        data: sortedTrend.map(d => d.total),
                                                        borderColor: '#64748b', // Slate gray
                                                        backgroundColor: 'transparent',
                                                        borderWidth: 2,
                                                        borderDash: [8, 4], // Dashed line
                                                        tension: 0.4,
                                                        pointRadius: 4,
                                                        pointHoverRadius: 6,
                                                        pointBackgroundColor: '#64748b',
                                                        pointBorderColor: '#fff',
                                                        pointBorderWidth: 2,
                                                        pointHoverBackgroundColor: '#64748b',
                                                        pointHoverBorderColor: '#fff',
                                                        pointHoverBorderWidth: 2,
                                                        fill: false
                                                    },
                                                    {
                                                        label: 'Disetujui',
                                                        data: sortedTrend.map(d => d.approved),
                                                        borderColor: '#10b981', // Emerald green
                                                        backgroundColor: approvedGradient,
                                                        fill: true, // Enable area fill
                                                        tension: 0.4,
                                                        borderWidth: 3,
                                                        pointRadius: 4,
                                                        pointHoverRadius: 6,
                                                        pointBackgroundColor: '#10b981',
                                                        pointBorderColor: '#fff',
                                                        pointBorderWidth: 2,
                                                        pointHoverBackgroundColor: '#10b981',
                                                        pointHoverBorderColor: '#fff',
                                                        pointHoverBorderWidth: 2
                                                    }
                                                ]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                interaction: {
                                                    mode: 'index',
                                                    intersect: false
                                                },
                                                plugins: {
                                                    legend: {
                                                        display: true,
                                                        position: 'top',
                                                        align: 'end',
                                                        labels: {
                                                            color: isDark ? '#94a3b8' : '#64748b',
                                                            font: {
                                                                size: 12,
                                                                weight: '600'
                                                            },
                                                            usePointStyle: true,
                                                            pointStyle: 'circle',
                                                            padding: 15,
                                                            boxWidth: 8,
                                                            boxHeight: 8
                                                        }
                                                    },
                                                    tooltip: {
                                                        enabled: true,
                                                        backgroundColor: isDark ? 'rgba(30, 41, 59, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                                                        titleColor: isDark ? '#f1f5f9' : '#1e293b',
                                                        bodyColor: isDark ? '#cbd5e1' : '#475569',
                                                        borderColor: isDark ? '#475569' : '#e2e8f0',
                                                        borderWidth: 1,
                                                        padding: 12,
                                                        displayColors: true,
                                                        boxWidth: 10,
                                                        boxHeight: 10,
                                                        boxPadding: 6,
                                                        usePointStyle: true,
                                                        titleFont: {
                                                            size: 13,
                                                            weight: 'bold'
                                                        },
                                                        bodyFont: {
                                                            size: 12
                                                        },
                                                        callbacks: {
                                                            label: function(context) {
                                                                let label = context.dataset.label || '';
                                                                if (label) {
                                                                    label += ': ';
                                                                }
                                                                label += context.parsed.y.toLocaleString('id-ID');
                                                                return label;
                                                            }
                                                        }
                                                    }
                                                },
                                                scales: {
                                                    y: {
                                                        beginAtZero: true,
                                                        ticks: {
                                                            color: '#94a3b8', // Light gray for better contrast
                                                            font: { size: 11, weight: '500' },
                                                            padding: 8,
                                                            callback: function(value) {
                                                                return value.toLocaleString('id-ID');
                                                            }
                                                        },
                                                        grid: {
                                                            color: isDark ? 'rgba(51, 65, 85, 0.5)' : 'rgba(226, 232, 240, 0.8)', // #334155 with opacity
                                                            drawBorder: false,
                                                            lineWidth: 1,
                                                            drawTicks: false,
                                                            tickLength: 0,
                                                            borderDash: [4, 4] // Dashed grid lines
                                                        },
                                                        border: {
                                                            display: false
                                                        }
                                                    },
                                                    x: {
                                                        ticks: {
                                                            color: '#94a3b8', // Light gray for better contrast
                                                            font: { size: 11, weight: '500' },
                                                            padding: 8,
                                                            maxRotation: 45,
                                                            minRotation: 0
                                                        },
                                                        grid: {
                                                            display: false,
                                                            drawBorder: false
                                                        },
                                                        border: {
                                                            display: false
                                                        }
                                                    }
                                                }
                                            }
                                        });
                                    }

                                    // Monthly Trend Chart - Not used in this view layout
                                    // Replaced by multiPeriodTrendChart
                                    // Faculty Comparison Chart
                                    const facultyData = @json($facultyComparison);
                                    const facultyEl = document.getElementById('facultyComparisonChart');
                                    if (facultyData && facultyData.length > 0 && facultyEl) {
                                        new Chart(facultyEl, {
                                            type: 'bar',
                                            data: {
                                                labels: facultyData.map(d => d.faculty),
                                                datasets: [
                                                    {
                                                        label: 'Total Prestasi',
                                                        data: facultyData.map(d => d.total),
                                                        backgroundColor: '#3b82f6', // Bright Blue
                                                        borderRadius: 4,
                                                        barThickness: 20
                                                    }
                                                ]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                indexAxis: 'y',
                                                plugins: {
                                                    legend: { display: false },
                                                    tooltip: {
                                                        enabled: true,
                                                        backgroundColor: isDark ? '#1f2937' : '#ffffff',
                                                        titleColor: isDark ? '#f9fafb' : '#111827',
                                                        bodyColor: isDark ? '#d1d5db' : '#374151',
                                                        borderColor: isDark ? '#374151' : '#e5e7eb',
                                                        borderWidth: 1,
                                                        callbacks: {
                                                            title: function (context) {
                                                                const index = context[0].dataIndex;
                                                                return facultyData[index].full_name || facultyData[index].faculty;
                                                            }
                                                        }
                                                    }
                                                },
                                                scales: {
                                                    y: {
                                                        ticks: { color: textColor, font: { size: 9, weight: 'bold' } },
                                                        grid: { display: false, drawBorder: false }
                                                    },
                                                    x: {
                                                        beginAtZero: true,
                                                        ticks: { color: textColor, font: { size: 8 } },
                                                        grid: { color: gridColor, drawBorder: false }
                                                    }
                                                }
                                            }
                                        });
                                    } else if (facultyEl) {
                                        facultyEl.parentElement.innerHTML = '<p class="text-center text-gray-500 py-8">Tidak ada data perbandingan fakultas</p>';
                                    }

                                });
                            </script>

    <!-- Anomaly Detail Modal -->
    <div id="anomalyModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-6 border w-11/12 max-w-6xl shadow-2xl rounded-2xl bg-white dark:bg-gray-800 mb-10">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-5 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h3 id="modalTitle" class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Detail Anomali Data</h3>
                    <p id="modalSubtitle" class="text-sm text-gray-500 dark:text-gray-400 mt-1"></p>
                </div>
                <button onclick="closeAnomalyModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg p-2 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="mt-6">
                <div id="modalLoading" class="text-center py-12">
                    <svg class="animate-spin h-12 w-12 mx-auto text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 font-medium">Memuat data...</p>
                </div>

                <div id="modalContent" class="hidden">
                    <!-- Content for duplicates (grouped) -->
                    <div id="duplicatesContent" class="hidden space-y-6">
                        <!-- Groups will be inserted here -->
                    </div>

                    <!-- Content for other anomalies (regular table) -->
                    <div id="regularContent" class="hidden overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">NIM</th>
                                    <th class="px-4 py-3 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Nama Mahasiswa</th>
                                    <th class="px-4 py-3 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Prestasi</th>
                                    <th class="px-4 py-3 text-center text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Tanggal Input</th>
                                    <th class="px-4 py-3 text-center text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="modalTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Data will be inserted here -->
                            </tbody>
                        </table>
                    </div>

                    <div id="modalEmpty" class="hidden text-center py-12">
                        <div class="mx-auto w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-lg font-semibold text-gray-500 dark:text-gray-400 mb-1">Tidak Ada Anomali Data</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500">Seluruh data prestasi telah memenuhi standar kualitas</p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end pt-5 border-t border-gray-200 dark:border-gray-700 mt-6">
                <button onclick="closeAnomalyModal()" class="px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-bold transition-colors shadow-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function openAnomalyModal(type) {
            const modal = document.getElementById('anomalyModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalSubtitle = document.getElementById('modalSubtitle');
            const modalLoading = document.getElementById('modalLoading');
            const modalContent = document.getElementById('modalContent');
            const duplicatesContent = document.getElementById('duplicatesContent');
            const regularContent = document.getElementById('regularContent');
            const modalTableBody = document.getElementById('modalTableBody');
            const modalEmpty = document.getElementById('modalEmpty');

            // Set title based on type
            const titles = {
                'duplicates': 'Anomali Duplikasi Data',
                'no_docs': 'Dokumen Tidak Lengkap',
                'abandoned_drafts': 'Draft Kadaluwarsa'
            };
            const subtitles = {
                'duplicates': 'Daftar prestasi yang terdeteksi diinput lebih dari satu kali oleh mahasiswa yang sama',
                'no_docs': 'Daftar prestasi yang belum melampirkan dokumen pendukung wajib',
                'abandoned_drafts': 'Draft prestasi yang tidak diperbarui oleh mahasiswa lebih dari 30 hari'
            };
            modalTitle.textContent = titles[type] || 'Detail Kualitas Data';
            modalSubtitle.textContent = subtitles[type] || '';
            
            // Store current type for reload after deletion
            modalTitle.dataset.currentType = type;

            // Show modal and loading
            modal.classList.remove('hidden');
            modalLoading.classList.remove('hidden');
            modalContent.classList.add('hidden');
            duplicatesContent.classList.add('hidden');
            regularContent.classList.add('hidden');

            // Get current period from URL or form
            const periodSelect = document.querySelector('select[name="period"]');
            const periodParam = periodSelect ? `?period=${periodSelect.value}` : '';

            // Fetch data
            fetch(`/admin/api/anomalies/${type}${periodParam}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Received data:', data);
                    modalLoading.classList.add('hidden');
                    modalContent.classList.remove('hidden');

                    if (!data || data.length === 0) {
                        modalEmpty.classList.remove('hidden');
                    } else {
                        modalEmpty.classList.add('hidden');
                        
                        if (type === 'duplicates') {
                            // Show grouped duplicates
                            duplicatesContent.classList.remove('hidden');
                            renderDuplicateGroups(data, duplicatesContent);
                        } else {
                            // Show regular table
                            regularContent.classList.remove('hidden');
                            renderRegularTable(data, modalTableBody, type);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching anomaly data:', error);
                    modalLoading.classList.add('hidden');
                    modalContent.classList.remove('hidden');
                    modalEmpty.classList.add('hidden');
                    regularContent.classList.remove('hidden');
                    modalTableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center">
                                <div class="text-red-600 dark:text-red-400 font-semibold mb-2">Gagal memuat data</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-3">${error.message}</div>
                                <button onclick="openAnomalyModal('${type}')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold transition-colors">
                                    Coba Lagi
                                </button>
                            </td>
                        </tr>
                    `;
                });
        }

        function renderDuplicateGroups(groups, container) {
            container.innerHTML = groups.map((group, groupIndex) => {
                const bgColors = ['bg-red-50 dark:bg-red-900/10', 'bg-orange-50 dark:bg-orange-900/10'];
                const bgColor = bgColors[groupIndex % bgColors.length];
                
                return `
                    <div class="border-2 border-red-200 dark:border-red-800 rounded-xl overflow-hidden ${bgColor}">
                        <!-- Group Header -->
                        <div class="bg-red-100 dark:bg-red-900/30 px-5 py-3 border-b-2 border-red-200 dark:border-red-800">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-red-600 text-white rounded-full text-sm font-black">
                                        ${groupIndex + 1}
                                    </span>
                                    <div>
                                        <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase">
                                            ${group.student_name} (${group.student_id})
                                        </h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                            ${group.event_name}
                                        </p>
                                    </div>
                                </div>
                                <span class="px-3 py-1.5 bg-red-600 text-white rounded-full text-xs font-black">
                                    ${group.duplicate_count}x Duplikasi
                                </span>
                            </div>
                        </div>

                        <!-- Group Records -->
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-100 dark:bg-gray-800/50">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase">No</th>
                                        <th class="px-4 py-2.5 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase">Status</th>
                                        <th class="px-4 py-2.5 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase">Tanggal Input</th>
                                        <th class="px-4 py-2.5 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase">Keterangan</th>
                                        <th class="px-4 py-2.5 text-center text-xs font-black text-gray-700 dark:text-gray-300 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    ${group.records.map((record, index) => `
                                        <tr class="hover:bg-white/50 dark:hover:bg-gray-800/50 transition-colors">
                                            <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-gray-100">
                                                ${index + 1}
                                            </td>
                                            <td class="px-4 py-3">
                                                ${record.is_oldest 
                                                    ? '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400"><svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/></svg>Data Lama</span>' 
                                                    : record.is_newest 
                                                        ? '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400"><svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>Data Baru</span>'
                                                        : '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">Data Tengah</span>'
                                                }
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 font-medium">
                                                ${record.created_at}
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-xs px-2 py-1 rounded-full ${getStatusBadgeClass(record.validation_status)}">
                                                    ${record.validation_status}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-center gap-2">
                                                    <a href="/admin/student-achievements/${record.id}" 
                                                       target="_blank"
                                                       class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors inline-flex items-center gap-1 shadow-sm">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                        Lihat Detail
                                                    </a>
                                                    <button onclick="deleteRecord(${record.id})" 
                                                            class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition-colors inline-flex items-center gap-1 shadow-sm">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function renderRegularTable(data, tbody, type) {
            tbody.innerHTML = data.map((item, index) => `
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-gray-100">${index + 1}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">${item.nim || '-'}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">${item.student_name || '-'}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                        <div class="max-w-md truncate" title="${item.achievement_name || '-'}">
                            ${item.achievement_name || '-'}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-gray-100">
                        ${item.created_at || '-'}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="/admin/student-achievements/${item.id}" 
                           class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Lihat
                        </a>
                    </td>
                </tr>
            `).join('');
        }

        function getStatusBadgeClass(status) {
            const statusClasses = {
                'Menunggu': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
                'submitted': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
                'Disetujui': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                'university_approved': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                'Ditolak': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                'draft': 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
            };
            return statusClasses[status] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300';
        }

        function deleteRecord(recordId) {
            if (!confirm('Apakah Anda yakin ingin menghapus data prestasi ini? Tindakan ini tidak dapat dibatalkan.')) {
                return;
            }

            // Show loading state
            const deleteButtons = document.querySelectorAll(`button[onclick="deleteRecord(${recordId})"]`);
            deleteButtons.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '<svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menghapus...';
            });

            // Get CSRF token
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // Send delete request
            fetch(`/admin/api/achievements/${recordId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success notification
                    showNotification('success', data.message || 'Data berhasil dihapus');
                    
                    // Reload the modal data to reflect changes
                    const currentType = document.getElementById('anomalyModalTitle')?.dataset.currentType;
                    if (currentType) {
                        setTimeout(() => {
                            openAnomalyModal(currentType);
                        }, 1000);
                    }
                } else {
                    showNotification('error', data.message || 'Gagal menghapus data');
                    // Re-enable buttons on error
                    deleteButtons.forEach(btn => {
                        btn.disabled = false;
                        btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Hapus';
                    });
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                showNotification('error', 'Terjadi kesalahan saat menghapus data');
                // Re-enable buttons on error
                deleteButtons.forEach(btn => {
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Hapus';
                });
            });
        }

        function showNotification(type, message) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-[9999] px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 ${
                type === 'success' 
                    ? 'bg-green-500 text-white' 
                    : 'bg-red-500 text-white'
            }`;
            notification.innerHTML = `
                <div class="flex items-center gap-3">
                    ${type === 'success' 
                        ? '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                        : '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                    }
                    <span class="font-bold">${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.transform = 'translateX(400px)';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        function closeAnomalyModal() {
            document.getElementById('anomalyModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('anomalyModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAnomalyModal();
            }
        });

        // Unit Distribution Modal Functions
        function openUnitModal() {
            const modal = document.getElementById('unitDistributionModal');
            const modalLoading = document.getElementById('unitModalLoading');
            const modalContent = document.getElementById('unitModalContent');
            const modalTableBody = document.getElementById('unitTableBody');
            const modalEmpty = document.getElementById('unitModalEmpty');

            modal.classList.remove('hidden');
            modalLoading.classList.remove('hidden');
            modalContent.classList.add('hidden');

            // Get current period parameter
            const urlParams = new URLSearchParams(window.location.search);
            const period = urlParams.get('period') || 'all';
            const periodParam = period !== 'all' ? `?period=${period}` : '';

            // Fetch unit distribution data
            fetch(`/admin/api/unit-distribution${periodParam}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    modalLoading.classList.add('hidden');
                    modalContent.classList.remove('hidden');

                    if (!data || data.length === 0) {
                        modalTableBody.innerHTML = '';
                        modalEmpty.classList.remove('hidden');
                    } else {
                        modalEmpty.classList.add('hidden');
                        const maxTotal = Math.max(...data.map(item => item.total));
                        
                        modalTableBody.innerHTML = data.map((item, index) => {
                            const percentage = maxTotal > 0 ? (item.total / maxTotal) * 100 : 0;
                            const approvedPercentage = item.total > 0 ? (item.approved / item.total) * 100 : 0;
                            
                            return `
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-4 py-3 text-sm font-bold text-gray-500 dark:text-gray-400">${index + 1}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100">${item.prodi}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">${item.faculty}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1">
                                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                                <div class="bg-blue-500 h-2 rounded-full relative transition-all duration-500" style="width: ${percentage}%">
                                                    <div class="absolute inset-0 bg-emerald-400 opacity-40" style="width: ${approvedPercentage}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-sm font-black text-blue-600 dark:text-blue-400 min-w-[3rem] text-right">${item.total}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                        ${item.approved}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                        ${item.pending}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                        ${item.rejected}
                                    </span>
                                </td>
                            </tr>
                        `}).join('');
                    }
                })
                .catch(error => {
                    console.error('Error fetching unit distribution:', error);
                    modalLoading.classList.add('hidden');
                    modalContent.classList.remove('hidden');
                    modalEmpty.classList.add('hidden');
                    modalTableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center">
                                <div class="text-red-600 dark:text-red-400 font-semibold mb-2">Gagal memuat data</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">${error.message}</div>
                                <button onclick="openUnitModal()" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors">
                                    Coba Lagi
                                </button>
                            </td>
                        </tr>
                    `;
                });
        }

        function closeUnitModal() {
            document.getElementById('unitDistributionModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('unitDistributionModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeUnitModal();
            }
        });
    </script>

    <!-- Unit Distribution Modal -->
    <div id="unitDistributionModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                        Sebaran Prestasi per Program Studi
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Daftar lengkap distribusi prestasi mahasiswa berdasarkan program studi
                    </p>
                </div>
                <button onclick="closeUnitModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto p-6">
                <!-- Loading State -->
                <div id="unitModalLoading" class="flex flex-col items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Memuat data...</p>
                </div>

                <!-- Content -->
                <div id="unitModalContent" class="hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                        #
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                        Program Studi
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                        Total Prestasi
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                        Disetujui
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                        Menunggu
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                        Ditolak
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="unitTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Data will be inserted here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div id="unitModalEmpty" class="hidden text-center py-12">
                        <div class="mx-auto w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada data program studi</p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 dark:border-gray-700">
                <button onclick="closeUnitModal()" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-bold transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- Context-Aware Alert System (Toast + Modal) --}}
    <x-anomaly-toast :anomalies="$anomalies" :context="$alertContext" />
    <x-anomaly-notification-modal :anomalies="$anomalies" :context="$alertContext" :globalBreakdown="$globalBreakdown" />
@endsection