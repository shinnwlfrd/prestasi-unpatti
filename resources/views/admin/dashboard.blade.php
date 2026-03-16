@extends('layouts.admin')

@section('title', 'Dashboard Admin')

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
        $systemActivities = $systemActivities ?? collect();
        $anomalies = $anomalies ?? ['duplicates' => 0, 'no_docs' => 0, 'sla_breach' => 0, 'sync_issue' => 0];
        $activeStats = $activeStats ?? ['daily_trend' => collect(), 'faculty_backlog' => collect(), 'critical_queue' => collect()];
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
            .grid {
                display: block !important;
            }

            .grid>div {
                width: 100% !important;
                margin-bottom: 1.5rem !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            /* Keep Backgrounds (Critical for KPIs and Charts) */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Typography */
            h1,
            h2,
            h3 {
                color: black !important;
            }

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
            {{ auth()->user()->name }}</p>
    </div>

    <div class="space-y-6">
        <!-- Header with Period Filter -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Overview</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Pantau statistik dan performa prestasi mahasiswa.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <!-- Period Filter -->
                        <form method="GET" action="{{ route('admin.dashboard') }}" class="w-full sm:w-auto">
                            <select name="period" onchange="this.form.submit()"
                                class="w-full sm:w-auto px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 shadow-sm transition-all cursor-pointer">
                                <option value="all" {{ request('period') === 'all' || (!request('period') && !isset($selectedPeriod)) ? 'selected' : '' }}>
                                    Semua Periode
                                </option>
                                @if(isset($periods))
                                    @foreach($periods as $period)
                                        <option value="{{ $period->id }}" {{ isset($selectedPeriod) && $selectedPeriod && $selectedPeriod->id == $period->id ? 'selected' : '' }}>
                                            {{ $period->name }} {{ $period->is_active ? '(Aktif)' : '' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </form>

                        <!-- Export Buttons -->
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <a href="{{ route('admin.export.achievements', ['format' => 'excel', 'period' => request('period', 'all')]) }}" 
                               class="flex-1 sm:flex-none justify-center flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors shadow-sm text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Excel
                            </a>
                            <a href="{{ route('admin.export.achievements', ['format' => 'csv', 'period' => request('period', 'all')]) }}" 
                               class="flex-1 sm:flex-none justify-center flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors shadow-sm text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 2v-6m0 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                CSV
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Active/Inactive Period Info -->
                @if($selectedPeriod)
                    <div
                        class="{{ $isActivePeriod ? 'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800' : 'bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700' }} border rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="p-2 {{ $isActivePeriod ? 'bg-purple-100 dark:bg-purple-900/40' : 'bg-gray-200 dark:bg-gray-700' }} rounded-lg">
                                <svg class="w-5 h-5 {{ $isActivePeriod ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p
                                    class="text-sm font-medium {{ $isActivePeriod ? 'text-purple-900 dark:text-purple-100' : 'text-gray-900 dark:text-gray-100' }}">
                                    Periode Akademik {{ $isActivePeriod ? 'Aktif' : 'Arsip/Tidak Aktif' }}:
                                </p>
                                <p
                                    class="text-lg font-bold {{ $isActivePeriod ? 'text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400' }}">
                                    {{ $selectedPeriod->name }}
                                </p>
                            </div>
                            @if($isInactivePeriod)
                                <div
                                    class="px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-bold rounded-full ml-4">
                                    MODE BACA SAJA
                                </div>
                            @endif
                            <div class="ml-auto text-right">
                                <p class="text-xs {{ $isActivePeriod ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500' }}">
                                    {{ $selectedPeriod->start_date->format('d M Y') }} -
                                    {{ $selectedPeriod->end_date->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/40 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-900 dark:text-blue-100">Ringkasan Data Global</p>
                                <p class="text-xs text-blue-600 dark:text-blue-400 font-bold uppercase">Seluruh Periode Tercatat</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($isInactivePeriod)
                    <!-- BARIS 1 – RINGKASAN AKHIR PERIODE (4 CARD) -->
                    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-12 gap-6">
                        <!-- Card 1 – Total Prestasi Final -->
                        <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-3 opacity-10">
                                <svg class="w-16 h-16 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Prestasi Final</p>
                            <p class="text-4xl font-black text-emerald-600 mt-2">{{ number_format($stats['achievements']) }}</p>
                            <p class="text-[10px] text-gray-400 mt-1 uppercase">Validasi Selesai & Disahkan</p>
                        </div>

                        <!-- Card 2 – Persentase Nasional & Internasional -->
                        <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Skala Nasional & Inter</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    @php 
                                                                                                                                $total = array_sum($levelDistribution);
                                        $nasRatio = $total > 0 ? round(($levelDistribution['Nasional'] / $total) * 100) : 0;
                                        $interRatio = $total > 0 ? round(($levelDistribution['Internasional'] / $total) * 100) : 0;
                                    @endphp
                                    <p class="text-2xl font-black text-purple-600">{{ $nasRatio }}%</p>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase">Nasional</p>
                                </div>
                                <div>
                                    <p class="text-2xl font-black text-indigo-600">{{ $interRatio }}%</p>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase">Internasional</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3 – Fakultas Aktif Berprestasi -->
                        <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col justify-between">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Fakultas Aktif</p>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <p class="text-3xl font-black text-blue-600">{{ $archivedStats['active_faculties'] }}</p>
                                    <p class="text-xs text-gray-400 font-bold">/ {{ $archivedStats['total_faculties'] }}</p>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full mt-4 overflow-hidden">
                                <div class="bg-blue-500 h-full transition-all duration-1000" style="width: {{ ($archivedStats['active_faculties'] / $archivedStats['total_faculties']) * 100 }}%"></div>
                            </div>
                        </div>

                        <!-- Card 4 – Rata-rata Waktu Validasi (Final) -->
                        <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden relative">
                            <div class="absolute -right-4 -bottom-4 opacity-5">
                               <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                            </div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Efficiency Final</p>
                            <p class="text-4xl font-black text-amber-600 mt-2">{{ $statistics['avg_time_to_approve'] }} <span class="text-sm">Hari</span></p>
                            <p class="text-[10px] text-gray-400 mt-1 uppercase">Submit &rarr; Pengesahan</p>
                        </div>
                    </div>

                    <!-- BARIS 2 – DISTRIBUSI HASIL (3 CARD) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-12 gap-6 mt-6">
                        <!-- Card 5 – Distribusi Tingkat -->
                        <div class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Distribusi Tingkat</h3>
                            <div class="h-64 relative">
                                <canvas id="archivedLevelChart"></canvas>
                            </div>
                        </div>

                        <!-- Card 6 – Distribusi Kategori -->
                        <div class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Distribusi Kategori</h3>
                            <div class="h-64 relative">
                                <canvas id="archivedCategoryChart"></canvas>
                            </div>
                        </div>

                        <!-- Card 7 – Status Prestasi (Final) -->
                        <div class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Status Prestasi (Final)</h3>
                            <div class="h-64 relative">
                                <canvas id="archivedStatusChart"></canvas>
                            </div>
                            <div class="mt-4 flex justify-center gap-6">
                                <div class="text-center">
                                    <p class="text-[10px] text-gray-400 font-bold uppercase">Disetujui</p>
                                    <p class="text-lg font-black text-emerald-600">{{ $stats['approved'] }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] text-gray-400 font-bold uppercase">Ditolak</p>
                                    <p class="text-lg font-black text-red-600">{{ $stats['total_rejected'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BARIS 3 – PERBANDINGAN INTERNAL (3 CARD) -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
                        <!-- Card 8 – Prestasi per Fakultas -->
                        <div class="lg:col-span-6 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Prestasi per Fakultas</h3>
                            <div class="h-80 relative">
                                <canvas id="archivedFacultyChart"></canvas>
                            </div>
                        </div>

                        <!-- Card 9 – Prestasi per Program Studi -->
                        <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Top Program Studi</h3>
                            <div class="space-y-4 flex-1">
                                @foreach($topProgramStudies->take(6) as $prodi)
                                    <div>
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 truncate w-32">{{ $prodi->prodi }}</span>
                                            <span class="text-xs font-black text-blue-600">{{ $prodi->total }}</span>
                                        </div>
                                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                                            <div class="bg-blue-500 h-full" style="width: {{ ($prodi->total / $topProgramStudies->max('total')) * 100 }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Card 10 – Rasio Prestasi per Mahasiswa -->
                        <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Rasio per Mahasiswa (%)</h3>
                            <div class="h-80 relative">
                                <canvas id="archivedRatioChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- BARIS 4 – EVALUASI & KUALITAS DATA (2 CARD) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <!-- Card 11 – Temuan & Catatan Evaluasi -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                Temuan & Catatan Evaluasi
                            </h3>
                            <ul class="space-y-3">
                                @php
                                    $inactiveFaculties = $archivedStats['total_faculties'] - $archivedStats['active_faculties'];
                                @endphp
                                <li class="flex items-start gap-3 p-3 bg-red-50 dark:bg-red-900/10 rounded-lg">
                                    <span class="p-1 bg-red-100 dark:bg-red-800 text-red-600 dark:text-red-300 rounded text-[10px] uppercase font-bold">Insight</span>
                                    <span class="text-xs text-red-800 dark:text-red-300">Terdapat {{ $inactiveFaculties }} fakultas yang tidak mencatatkan prestasi pada periode ini.</span>
                                </li>
                                <li class="flex items-start gap-3 p-3 bg-blue-50 dark:bg-blue-900/10 rounded-lg">
                                    <span class="p-1 bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-300 rounded text-[10px] uppercase font-bold">Insight</span>
                                    <span class="text-xs text-blue-800 dark:text-blue-300">Kategori {{ $categoryDistribution->sortByDesc('total')->first()->category ?? 'Utama' }} mendominasi {{ round(($categoryDistribution->max('total') / ($stats['achievements'] ?: 1)) * 100) }}% capaian data.</span>
                                </li>
                                <li class="flex items-start gap-3 p-3 bg-amber-50 dark:bg-amber-900/10 rounded-lg">
                                    <span class="p-1 bg-amber-100 dark:bg-amber-800 text-amber-600 dark:text-amber-300 rounded text-[10px] uppercase font-bold">Audit</span>
                                    <span class="text-xs text-amber-800 dark:text-amber-300">Rasio penolakan data sebesar {{ round(($stats['total_rejected'] / ($stats['achievements'] ?: 1)) * 100) }}%, perlu review pedoman pengajuan.</span>
                                </li>
                            </ul>
                            <div class="mt-6 p-4 border-2 border-dashed border-gray-100 dark:border-gray-700 rounded-xl">
                                <p class="text-[10px] text-gray-400 font-bold uppercase mb-2">Bahan Rapat Evaluasi</p>
                                <p class="text-xs text-gray-500 italic italic">"Prioritaskan peningkatan partisipasi pada fakultas non-aktif dan standarisasi dokumen pendukung untuk menekan angka penolakan."</p>
                            </div>
                        </div>

                        <!-- Card 12 – Ringkasan Data Ditolak -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Ringkasan Data Ditolak</h3>
                            <div class="flex-1 max-h-[300px] overflow-auto custom-scrollbar pr-2">
                                <table class="w-full text-left">
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

                    <!-- BARIS 5 – ARSIP & LAPORAN -->
                    <div class="mt-6 lg:col-span-12">
                        <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-2xl p-8 text-white relative shadow-xl focus-within:z-40">
                            <!-- Background Decoration -->
                            <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none">
                                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                                <div class="absolute bottom-0 left-0 w-64 h-64 bg-emerald-500/5 rounded-full -ml-32 -mb-32 blur-3xl"></div>
                            </div>
                            <div class="flex flex-col md:flex-row items-center justify-between gap-8 relative z-10">
                                <div class="flex-1">
                                    <h2 class="text-2xl font-black mb-2">Ringkasan Arsip Periode</h2>
                                    <p class="text-gray-400 text-sm max-w-xl">Seluruh data pada periode akademik ini telah divalidasi dan dikunci dalam sistem arsip universitas. Laporan ini bersifat final.</p>

                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-8">
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-tighter mb-1">Periode</p>
                                            <p class="text-lg font-bold">{{ $selectedPeriod->name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-tighter mb-1">Total Prestasi Final</p>
                                            <p class="text-lg font-bold text-emerald-400">{{ $stats['achievements'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-tighter mb-1">Tanggal Penutupan</p>
                                            <p class="text-lg font-bold">{{ $selectedPeriod->end_date->format('d M Y') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-tighter mb-1">Status Arsip</p>
                                            <p class="text-lg font-bold flex items-center gap-2">
                                                <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                                LOCKED
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-shrink-0" x-data="{ open: false }">
                                    <div class="relative">
                                        <button @click="open = !open" @click.away="open = false" 
                                            class="px-8 py-3 bg-white text-gray-900 font-black rounded-xl hover:bg-gray-100 transition-all flex items-center gap-3 shadow-lg group">
                                            <svg class="w-5 h-5 text-gray-900 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4h10z"></path></svg>
                                            CETAK LAPORAN FINAL
                                            <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </button>

                                        <!-- Dropdown Menu -->
                                        <div x-show="open" 
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute right-0 bottom-full mb-3 w-64 rounded-xl shadow-2xl bg-white ring-1 ring-black ring-opacity-5 z-[100] overflow-hidden" 
                                            x-cloak>
                                            <div class="py-1">
                                                <a href="{{ route('api.export.achievements', ['period_id' => $selectedPeriod->id, 'format' => 'excel']) }}" 
                                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                                                    <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <span class="font-bold">Export Excel</span>
                                                        <span class="text-[10px] text-gray-500">Format .xls untuk laporan</span>
                                                    </div>
                                                </a>
                                                <a href="{{ route('api.export.achievements', ['period_id' => $selectedPeriod->id, 'format' => 'csv']) }}" 
                                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <span class="font-bold">Export CSV</span>
                                                        <span class="text-[10px] text-gray-500">Format teks untuk data</span>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($isActivePeriod)
                    <!-- BARIS 1 – STATUS SISTEM SAAT INI (5 CARD) -->
                    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-12 gap-6">
                        <!-- Card 1 – Total Pengajuan -->
                        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Pengajuan</p>
                            <div class="flex items-baseline gap-2">
                                <p class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($stats['achievements']) }}</p>
                                <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-2">Diterima periode ini</p>
                        </div>

                        <!-- Card 2 – Menunggu Validasi -->
                        @php $isOverThreshold = $stats['pending'] > 20; @endphp
                        <div class="lg:col-span-2 {{ $isOverThreshold ? 'bg-amber-50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-800' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700' }} rounded-xl p-6 border shadow-sm group">
                            <p class="text-[10px] font-bold {{ $isOverThreshold ? 'text-amber-600' : 'text-gray-400' }} uppercase tracking-widest mb-1">Menunggu Validasi</p>
                            <div class="flex items-baseline gap-2">
                                <p class="text-3xl font-black {{ $isOverThreshold ? 'text-amber-600' : 'text-gray-900 dark:text-white' }}">{{ number_format($stats['pending']) }}</p>
                                @if($isOverThreshold)
                                    <span class="flex h-2 w-2 relative">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-[10px] {{ $isOverThreshold ? 'text-amber-500 font-bold' : 'text-gray-500' }} mt-2">
                                    {{ $isOverThreshold ? 'BACKLOG TINGGI' : 'Beban Kerja Antrian' }}
                                </p>
                                <a href="{{ route('admin.student-achievements') }}?status=Menunggu" class="text-[10px] text-blue-600 font-bold hover:underline mt-2">LIHAT ANTRIAN →</a>
                            </div>
                        </div>

                        <!-- Card 3 – Disetujui -->
                        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Disetujui</p>
                            <p class="text-3xl font-black text-emerald-600">{{ number_format($stats['approved']) }}</p>
                            <p class="text-[10px] text-gray-500 mt-2">Lolos validasi periode ini</p>
                        </div>

                        <!-- Card 4 – Ditolak -->
                        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Ditolak</p>
                            <p class="text-3xl font-black text-red-600">{{ number_format($stats['total_rejected']) }}</p>
                            <p class="text-[10px] text-gray-500 mt-2">Butuh revisi/tidak valid</p>
                        </div>

                        <!-- Card 5 – Rata-rata Waktu Validasi -->
                        <div class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm relative overflow-hidden">
                            <div class="relative z-10">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Speed Validasi</p>
                                <div class="flex items-baseline gap-2">
                                    <p class="text-4xl font-black text-gray-900 dark:text-white">{{ $statistics['avg_time_to_approve'] }}</p>
                                    <p class="text-sm font-bold text-gray-500">Hari</p>
                                </div>
                                <p class="text-[10px] text-emerald-600 font-bold mt-2 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7l-1.204-1.204-5.914 5.914L12 18.828l1.204-1.204L8.414 13.5H19v-2.172H8.414l4.79-4.79-1.204-1.204L5 13.5z" clip-rule="evenodd" /></svg>
                                    SLA Performance
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- BARIS 2 – DISTRIBUSI PROSES (3 CARD) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-12 gap-6 mt-6">
                        <!-- Card 6 – Status Pengajuan -->
                        <div class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase mb-6 tracking-widest">Status Pengajuan</h3>
                            <div class="h-64 relative">
                                <canvas id="activeStatusChart"></canvas>
                            </div>
                        </div>

                        <!-- Card 7 – Distribusi Tingkat -->
                        <div class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase mb-6 tracking-widest">Tingkat Prestasi</h3>
                            <div class="h-64 relative">
                                <canvas id="activeLevelChart"></canvas>
                            </div>
                        </div>

                        <!-- Card 8 – Distribusi Kategori -->
                        <div class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase mb-6 tracking-widest">Top Kategori</h3>
                            <div class="h-64 relative">
                                <canvas id="activeCategoryChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- BARIS 3 – MONITORING UNIT (3 CARD) -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
                        <!-- Card 9 – Backlog Validasi per Fakultas -->
                        <div class="lg:col-span-6 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase mb-6 tracking-widest">Backlog per Fakultas</h3>
                            <div class="h-80 relative">
                                <canvas id="facultyBacklogChart"></canvas>
                            </div>
                        </div>

                        <!-- Card 10 – Aktivitas Validasi Harian -->
                        <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase mb-6 tracking-widest">Aktivitas 14 Hari</h3>
                            <div class="h-80 relative">
                                <canvas id="dailyActivityChart"></canvas>
                            </div>
                        </div>

                        <!-- Card 11 – Fakultas Paling Aktif -->
                        <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase mb-6 tracking-widest">Fakultas Teraktif</h3>
                            <div class="space-y-4 pr-1">
                                @foreach($facultyComparison->take(6) as $index => $f)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="text-[10px] font-black w-4 text-gray-400">#{{ $index + 1 }}</span>
                                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $f->faculty }}</span>
                                        </div>
                                        <span class="text-xs font-black text-blue-600">{{ $f->total }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- BARIS 4 – RISIKO & KUALITAS DATA (2 CARD) -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                        <!-- Card 12 – Peringatan Sistem -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase mb-6 tracking-widest">Peringatan Sistem</h3>
                            <div class="space-y-3">
                                @if($anomalies['sla_breach'] > 0)
                                    <div class="flex items-center gap-3 p-3 bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/20 rounded-xl">
                                        <div class="p-2 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-[11px] font-black text-red-800 dark:text-red-300">SLA BREACH ALERT</p>
                                            <p class="text-[10px] text-red-600 dark:text-red-400">{{ $anomalies['sla_breach'] }} pengajuan menunggu validasi lebih dari 7 hari.</p>
                                        </div>
                                    </div>
                                @endif

                                @php $inactiveFaculties = $facultyComparison->where('total', 0)->count(); @endphp
                                @if($inactiveFaculties > 0)
                                    <div class="flex items-center gap-3 p-3 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/20 rounded-xl">
                                        <div class="p-2 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-lg">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-[11px] font-black text-amber-800 dark:text-amber-300">PARTISIPASI RENDAH</p>
                                            <p class="text-[10px] text-amber-600 dark:text-amber-400">{{ $inactiveFaculties }} fakultas belum mencatat prestasi periode ini.</p>
                                        </div>
                                    </div>
                                @endif

                                <div class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/20 rounded-xl opacity-60">
                                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-[11px] font-black text-blue-800 dark:text-blue-300">SYSTEM STABILITY</p>
                                        <p class="text-[10px] text-blue-600 dark:text-blue-400">Sinkronisasi SIGAP berjalan normal tanpa delay signifikan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 13 – Antrian Pengajuan Kritis -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase mb-6 tracking-widest">Antrian Terlama</h3>
                            <div class="flex-1 overflow-x-auto custom-scrollbar">
                                <table class="w-full text-left">
                                    <thead class="border-b border-gray-100 dark:border-gray-700">
                                        <tr>
                                            <th class="pb-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Mahasiswa</th>
                                            <th class="pb-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Fakultas</th>
                                            <th class="pb-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-right">Menunggu</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                                        @forelse($activeStats['critical_queue'] as $ach)
                                            <tr>
                                                <td class="py-3">
                                                    <p class="text-xs font-bold text-gray-900 dark:text-white truncate w-32 tracking-tight">{{ $ach->student->name }}</p>
                                                    <p class="text-[9px] text-gray-500 line-clamp-1 mt-0.5">{{ $ach->event_name }}</p>
                                                </td>
                                                <td class="py-3">
                                                    <span class="text-[10px] font-bold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-md">{{ $ach->student->faculty }}</span>
                                                </td>
                                                <td class="py-3 text-right">
                                                    @php $days = now()->diffInDays($ach->submitted_at); @endphp
                                                    <a href="{{ route('admin.student-achievements') }}?search={{ $ach->student->student_id }}" class="px-2 py-1 {{ $days > 7 ? 'bg-red-500 text-white' : 'bg-amber-100 text-amber-700' }} text-[9px] font-black rounded-lg hover:opacity-80 transition-opacity">
                                                        {{ $days }} HRI
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="py-8 text-center text-xs italic text-gray-400">Belum ada antrian</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- BARIS 5 – AKTIVITAS SISTEM -->
                    <div class="mt-6">
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                            <!-- Header -->
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Log Aktivitas Sistem</h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Aktivitas terbaru dalam sistem</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('admin.validation-logs') }}" class="text-xs font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 flex items-center gap-1 transition-colors">
                                        Lihat Semua
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <!-- Activity List -->
                            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($systemActivities->take(6) as $log)
                                    @php
                                        $rawStatus = null;
                                        if ($log->type === 'validation' && preg_match('/→ (.*)$/', $log->description, $matches)) {
                                            $rawStatus = trim($matches[1]);
                                        }

                                        // Determine colors and icons based on activity type and status
                                        $itemColor = $log->color ?? 'gray';
                                        $iconBg = 'bg-gray-100 dark:bg-gray-700';
                                        $iconColor = 'text-gray-600 dark:text-gray-400';
                                        $icon = 'info';

                                        if ($rawStatus === 'university_rejected' || $rawStatus === 'faculty_rejected' || $rawStatus === 'Ditolak') {
                                            $itemColor = 'red';
                                            $iconBg = 'bg-red-100 dark:bg-red-900/30';
                                            $iconColor = 'text-red-600 dark:text-red-400';
                                            $icon = 'rejected';
                                        } elseif ($rawStatus === 'faculty_approved') {
                                            $itemColor = 'indigo';
                                            $iconBg = 'bg-indigo-100 dark:bg-indigo-900/30';
                                            $iconColor = 'text-indigo-600 dark:text-indigo-400';
                                            $icon = 'approved';
                                        } elseif ($rawStatus === 'university_approved' || $rawStatus === 'Disetujui') {
                                            $itemColor = 'emerald';
                                            $iconBg = 'bg-emerald-100 dark:bg-emerald-900/30';
                                            $iconColor = 'text-emerald-600 dark:text-emerald-400';
                                            $icon = 'verified';
                                        } elseif ($log->type === 'auth') {
                                            $iconBg = 'bg-blue-100 dark:bg-blue-900/30';
                                            $iconColor = 'text-blue-600 dark:text-blue-400';
                                            $icon = 'auth';
                                        } elseif (str_contains($log->description, 'Revisi') || str_contains($log->description, 'revision')) {
                                            $iconBg = 'bg-amber-100 dark:bg-amber-900/30';
                                            $iconColor = 'text-amber-600 dark:text-amber-400';
                                            $icon = 'revision';
                                        }

                                        // Translate status labels
                                        $displayDescription = $log->description;
                                        $statusMap = [
                                            'submitted' => 'Diajukan',
                                            'faculty_review' => 'Review Fakultas',
                                            'faculty_approved' => 'Disetujui Fakultas',
                                            'faculty_rejected' => 'Ditolak Fakultas',
                                            'faculty_revision' => 'Revisi Fakultas',
                                            'university_review' => 'Review Universitas',
                                            'university_approved' => 'Disetujui Universitas',
                                            'university_rejected' => 'Ditolak Universitas',
                                            'appeal_submitted' => 'Banding Diajukan',
                                            'appeal_approved' => 'Banding Diterima',
                                            'appeal_rejected' => 'Banding Ditolak',
                                        ];
                                        foreach ($statusMap as $raw => $label) {
                                            $displayDescription = str_replace($raw, $label, $displayDescription);
                                        }
                                    @endphp
                                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <div class="flex items-start gap-4">
                                            <!-- Icon -->
                                            <div class="flex-shrink-0 {{ $iconBg }} rounded-lg p-2">
                                                @if($icon === 'auth')
                                                    <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                                    </svg>
                                                @elseif($icon === 'rejected')
                                                    <svg class="w-5 h-5 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                    </svg>
                                                @elseif($icon === 'approved')
                                                    <svg class="w-5 h-5 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                @elseif($icon === 'verified')
                                                    <svg class="w-5 h-5 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                @elseif($icon === 'revision')
                                                    <svg class="w-5 h-5 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @endif
                                            </div>

                                            <!-- Content -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                            {{ $log->user }}
                                                        </p>
                                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5 line-clamp-2">
                                                            {{ $displayDescription }}
                                                        </p>
                                                    </div>
                                                    <div class="flex-shrink-0 text-right">
                                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                            {{ $log->timestamp->format('H:i') }}
                                                        </p>
                                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                                            {{ $log->timestamp->format('d M') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-6 py-12 text-center">
                                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada aktivitas sistem</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @else
                    <!-- RINGKASAN DATA GLOBAL -->
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-12 gap-6 mt-6">
                        <!-- Total Achievements (Static) - Col 1-2 -->
                        <div
                            class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
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

                        <!-- Period Achievement - Col 3-4 -->
                            <div
                                class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                    </div>
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 uppercase">
                                        {{ $isGlobal ? 'Global' : 'Periode' }}
                                    </span>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">
                                    {{ $isGlobal ? 'Total Prestasi' : 'Prestasi Terdata' }}
                                </p>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['achievements']) }}
                                    </p>
                                    <p class="text-xs font-medium text-green-600">{{ $statistics['approval_rate'] }}% Rate</p>
                                </div>
                            </div>

                            <!-- Backlog - Col 5-6 -->
                            <div
                                class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="p-3 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                                        <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 uppercase">Backlog</span>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Menunggu Validasi</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                                    {{ number_format($stats['total_pending'] ?? 0) }}
                                </p>
                            </div>

                            <!-- Rejected - Col 7-8 -->
                            <div
                                class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
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

                            <!-- Avg Validation Time - Col 9-12 -->
                            <div
                                class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                                <div class="flex items-center justify-between mb-4 relative z-10">
                                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                                        <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="text-right">
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 uppercase tracking-wider">
                                            Validation SLA
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-end justify-between relative z-10">
                                    <div>
                                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Rata-rata Waktu Validasi</p>
                                        <div class="flex items-baseline gap-2 mt-1">
                                            <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ $stats['total_avg_time'] }}</p>
                                            <p class="text-lg font-semibold text-gray-500 dark:text-gray-400">Hari</p>
                                        </div>
                                        <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2 font-medium">Dari pengajuan hingga keputusan final</p>
                                    </div>

                                    <!-- Simple Sparkline Trend Placeholder (CSS based) -->
                                    <div class="flex items-end gap-1 h-12 mb-1">
                                        @foreach($monthlyTrend->take(5) as $trend)
                                            <div class="w-2 bg-emerald-500/20 rounded-t-sm" style="height: {{ rand(30, 100) }}%"></div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Decorative Background Chart Gradient -->
                                <div
                                    class="absolute bottom-0 right-0 w-1/3 h-24 bg-gradient-to-t from-emerald-500/5 to-transparent pointer-events-none">
                                </div>
                            </div>
                        </div>

                        <!-- Two-Stage Validation Queues -->
                        @if($isActivePeriod)
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- University Validation Queue -->
                                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                                    <div class="flex items-center justify-between mb-6">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                                <svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                                                </svg>
                                                Antrian Validasi Universitas
                                            </h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Disetujui fakultas, menunggu validasi
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
                                                Lihat Semua Antrian Universitas
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
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- Urgent Pending Alerts -->
                                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
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
                                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
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
                                                                    Validasi
                                                                </span>
                                                                <span
                                                                    class="px-2 py-0.5 rounded-full text-xs font-medium {{ $log->new_status === 'Disetujui' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($log->new_status === 'Ditolak' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400') }}">
                                                                    {{ $log->new_status }}
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
                                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
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
                                                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
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
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
                            <!-- Global Status Doughnut - Col 1-4 -->
                            <div
                                class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
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
                                class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
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
                                class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
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

                        <!-- Row 3: Multi-Period Trend, Faculty Bar Chart, and Analysis -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
                            <!-- Multi-Period Achievement Trend - Col 1-6 -->
                            <div
                                class="lg:col-span-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tren Prestasi Multi-Periode</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Perkembangan total prestasi lintas semester
                                        </p>
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

                            <!-- Prestasi per Fakultas - Col 7-9 (col-span-3) -->
                            <div
                                class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
                                <div class="mb-4">
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Prestasi per
                                        Fakultas</h3>
                                    <p class="text-[10px] text-gray-500 mt-0.5">Total akumulasi prestasi per unit</p>
                                </div>

                                <div class="h-64 relative">
                                    <canvas id="facultyComparisonChart"></canvas>
                                </div>

                                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                                    <p class="text-[9px] text-gray-400 text-center italic">Grafik akumulasi prestasi per fakultas</p>
                                </div>
                            </div>

                            <!-- Prestasi per Prodi - Col 10-12 (col-span-3) -->
                            <div
                                class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm flex flex-col">
                                <div class="mb-5">
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Top Program Studi
                                    </h3>
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

                        <!-- Row 4: Anomaly & System Activity -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
                            <!-- Anomali & Data Bermasalah - Col 1-6 (col-span-6) -->
                            <div
                                class="lg:col-span-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                                <div class="mb-6">
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Kualitas Data</h3>
                                    <p class="text-[10px] text-gray-500 mt-1">Identifikasi anomali data</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div
                                        class="flex items-center justify-between p-3 rounded-lg bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-800/20">
                                        <span class="text-xs font-semibold text-red-800 dark:text-red-300">Duplikasi</span>
                                        <span
                                            class="px-2 py-0.5 bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-200 text-[10px] font-bold rounded-full">{{ $anomalies['duplicates'] }}</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-3 rounded-lg bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/20">
                                        <span class="text-xs font-semibold text-amber-800 dark:text-amber-300">Tanpa Dokumen</span>
                                        <span
                                            class="px-2 py-0.5 bg-amber-100 dark:bg-amber-800 text-amber-700 dark:text-amber-200 text-[10px] font-bold rounded-full">{{ $anomalies['no_docs'] }}</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-3 rounded-lg bg-orange-50 dark:bg-orange-900/10 border border-orange-100 dark:border-orange-800/20">
                                        <span class="text-xs font-semibold text-orange-800 dark:text-orange-300">SLA Breach</span>
                                        <span
                                            class="px-2 py-0.5 bg-orange-100 dark:bg-orange-800 text-orange-700 dark:text-orange-200 text-[10px] font-bold rounded-full">{{ $anomalies['sla_breach'] }}</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-3 rounded-lg bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800/20">
                                        <span class="text-xs font-semibold text-blue-800 dark:text-blue-300">Sync Issue</span>
                                        <span
                                            class="px-2 py-0.5 bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-200 text-[10px] font-bold rounded-full">{{ $anomalies['sync_issue'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Aktivitas Sistem - Col 7-12 (col-span-6) -->
                            <div
                                class="lg:col-span-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                                <div class="mb-5">
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Aktivitas Sistem
                                    </h3>
                                    <p class="text-[10px] text-gray-500 mt-1">Log aktivitas terbaru</p>
                                </div>

                                <div class="flow-root">
                                    <ul role="list" class="-mb-8">
                                        @forelse($systemActivities->take(4) as $index => $activity)
                                            <li>
                                                <div class="relative pb-5">
                                                    @if($index !== 3)
                                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-100 dark:bg-gray-700"
                                                            aria-hidden="true"></span>
                                                    @endif
                                                    <div class="relative flex space-x-3">
                                                        <div>
                                                            <span
                                                                class="h-8 w-8 rounded-full bg-{{ $activity->color }}-100 dark:bg-{{ $activity->color }}-900/30 flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
                                                                @if($activity->type === 'auth')
                                                                    <svg class="h-4 w-4 text-{{ $activity->color }}-600 dark:text-{{ $activity->color }}-400"
                                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                                                    </svg>
                                                                @else
                                                                    <svg class="h-4 w-4 text-{{ $activity->color }}-600 dark:text-{{ $activity->color }}-400"
                                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex justify-between items-start">
                                                                <p class="text-[11px] font-bold text-gray-900 dark:text-white truncate w-32">
                                                                    {{ $activity->user }}
                                                                </p>
                                                                <span class="whitespace-nowrap text-[10px] text-gray-500">
                                                                    {{ $activity->timestamp->diffForHumans() }}
                                                                </span>
                                                            </div>
                                                            <p class="mt-0.5 text-[10px] text-gray-500 dark:text-gray-400 line-clamp-1">
                                                                {{ $activity->description }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="text-center py-4">
                                                <p class="text-[10px] text-gray-400 italic">Tidak ada aktivitas terbaru</p>
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Row 5: Master Data Summary -->
                        <div class="grid grid-cols-1 gap-6 mt-6">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm overflow-hidden relative">
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

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 relative z-10">
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
                                            <p class="text-2xl font-black text-gray-900 dark:text-white leading-tight">
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
                                            <p class="text-2xl font-black text-gray-900 dark:text-white leading-tight">
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
                                            <p class="text-2xl font-black text-gray-900 dark:text-white leading-tight">
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
                                                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 leading-tight">
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

                @endif

                    <!-- Unit Distribution Modal -->
                        <div id="unitModal" class="fixed inset-0 z-[60] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
                            aria-modal="true">
                            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" aria-hidden="true"
                                    onclick="closeUnitModal()"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div
                                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-200 dark:border-gray-700">
                                    <div
                                        class="bg-white dark:bg-gray-800 px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="modal-title">Sebaran Prestasi
                                                per Unit</h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 uppercase tracking-wider font-semibold">
                                                Daftar lengkap prestasi per Program Studi dan Fakultas</p>
                                        </div>
                                        <button type="button" onclick="closeUnitModal()"
                                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-all">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="px-6 py-4 max-h-[65vh] overflow-y-auto custom-scrollbar">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead
                                                class="bg-gray-50 dark:bg-gray-900/50 sticky top-0 uppercase tracking-widest text-[10px] font-bold z-10">
                                                <tr>
                                                    <th scope="col" class="px-4 py-3 text-left text-gray-500">Program Studi</th>
                                                    <th scope="col" class="px-4 py-3 text-left text-gray-500">Fakultas</th>
                                                    <th scope="col" class="px-4 py-3 text-center text-gray-500">Total</th>
                                                    <th scope="col" class="px-4 py-3 text-center text-gray-500">Approved</th>
                                                    <th scope="col" class="px-4 py-3 text-center text-gray-500">Success Ratio</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                @foreach($topProgramStudies as $prodi)
                                                    <tr class="hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors group">
                                                        <td class="px-4 py-4">
                                                            <div
                                                                class="text-xs font-black text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                                {{ $prodi->prodi }}
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-4 text-xs text-gray-500 dark:text-gray-400 font-medium">
                                                            {{ $prodi->faculty }}
                                                        </td>
                                                        <td class="px-4 py-4 text-center">
                                                            <span
                                                                class="text-xs font-black text-gray-900 dark:text-white">{{ $prodi->total }}</span>
                                                        </td>
                                                        <td class="px-4 py-4 text-center">
                                                            <span
                                                                class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-lg text-[10px] font-black">
                                                                {{ $prodi->approved }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-4">
                                                            @php $ratio = $prodi->total > 0 ? round(($prodi->approved / $prodi->total) * 100) : 0; @endphp
                                                            <div class="flex items-center gap-3 justify-center">
                                                                <div
                                                                    class="w-16 bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $ratio }}%">
                                                                    </div>
                                                                </div>
                                                                <span
                                                                    class="text-[10px] font-bold text-gray-600 dark:text-gray-300 w-8">{{ $ratio }}%</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div
                                        class="bg-gray-50 dark:bg-gray-900/30 px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                                        <button type="button" onclick="closeUnitModal()"
                                            class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-sm font-bold text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                                            Tutup
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <style>
                            .custom-scrollbar::-webkit-scrollbar {
                                width: 6px;
                            }

                            .custom-scrollbar::-webkit-scrollbar-track {
                                background: transparent;
                            }

                            .custom-scrollbar::-webkit-scrollbar-thumb {
                                background: #e5e7eb;
                                border-radius: 10px;
                            }

                            .dark .custom-scrollbar::-webkit-scrollbar-thumb {
                                background: #374151;
                            }
                        </style>

                        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                        <script>
                            function openUnitModal() {
                                document.getElementById('unitModal').classList.remove('hidden');
                                document.body.style.overflow = 'hidden';
                            }

                            function closeUnitModal() {
                                document.getElementById('unitModal').classList.add('hidden');
                                document.body.style.overflow = '';
                            }

                            document.addEventListener('keydown', function (event) {
                                if (event.key === "Escape") {
                                    closeUnitModal();
                                }
                            });
                        </script>
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

                                    // Archived Status Distribution Chart
                                    new Chart(document.getElementById('archivedStatusChart'), {
                                        type: 'doughnut',
                                        data: {
                                            labels: ['Disetujui', 'Ditolak'],
                                            datasets: [{
                                                data: [@json($stats['approved']), @json($stats['total_rejected'])],
                                                backgroundColor: ['#10b981', '#ef4444'],
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
                                            labels: ['Menunggu', 'Disetujui', 'Ditolak'],
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
                                                y: { beginAtZero: true, ticks: { color: textColor }, grid: { color: gridColor } },
                                                x: { ticks: { color: textColor }, grid: { display: false } }
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
                                                label: 'Validasi',
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
                                            labels: ['Menunggu', 'Disetujui', 'Ditolak'],
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

                                // Multi-Period Trend Chart (Line)
                                const trendData = @json($globalTrend ?? []);
                                const trendEl = document.getElementById('multiPeriodTrendChart');
                                if (trendData && trendData.length > 0 && trendEl) {
                                    // Reverse for chronological order if needed (assuming periodComparison is desc)
                                    const sortedTrend = [...trendData].reverse();
                                    new Chart(trendEl, {
                                        type: 'line',
                                        data: {
                                            labels: sortedTrend.map(d => d.period),
                                            datasets: [
                                                {
                                                    label: 'Total',
                                                    data: sortedTrend.map(d => d.total),
                                                    borderColor: isDark ? '#4b5563' : '#d1d5db',
                                                    backgroundColor: 'transparent',
                                                    borderWidth: 2,
                                                    borderDash: [5, 5],
                                                    tension: 0.4,
                                                    pointRadius: 0
                                                },
                                                {
                                                    label: 'Approved',
                                                    data: sortedTrend.map(d => d.approved),
                                                    borderColor: '#3b82f6',
                                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                                    fill: true,
                                                    tension: 0.4,
                                                    borderWidth: 3,
                                                    pointRadius: 4,
                                                    pointBackgroundColor: '#3b82f6'
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
@endsection