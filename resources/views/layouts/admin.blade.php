<!DOCTYPE html>
<html lang="id"
    x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarOpen: false, mobileSidebarOpen: false }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
    :class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8" />
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - SIMAPRES</title>
    <!-- Tailwind CSS CDN - For development only. Consider installing via npm for production -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Mobile auto-hide navbar */
        @media (max-width: 1023px) {
            .mobile-navbar {
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                will-change: transform;
            }
            .mobile-navbar.navbar-hidden {
                transform: translateY(-100%);
            }
            .mobile-navbar.navbar-visible {
                transform: translateY(0);
            }
        }

        /* Desktop Optimizations */
        @media (min-width: 1024px) {
            /* Enhanced hover effects */
            .desktop-card-hover {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .desktop-card-hover:hover {
                transform: translateY(-4px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }

            /* Table row hover */
            .desktop-table-row:hover {
                background-color: rgba(139, 92, 246, 0.05);
            }

            /* Smooth scrollbar */
            .custom-scrollbar::-webkit-scrollbar {
                width: 10px;
                height: 10px;
            }
            .custom-scrollbar::-webkit-scrollbar-track {
                background: rgba(0, 0, 0, 0.05);
                border-radius: 5px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: rgba(139, 92, 246, 0.3);
                border-radius: 5px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: rgba(139, 92, 246, 0.5);
            }

            /* Dark mode scrollbar */
            .dark .custom-scrollbar::-webkit-scrollbar-track {
                background: rgba(255, 255, 255, 0.05);
            }
            .dark .custom-scrollbar::-webkit-scrollbar-thumb {
                background: rgba(139, 92, 246, 0.4);
            }
            .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: rgba(139, 92, 246, 0.6);
            }
        }

        /* Enhanced Typography */
        @media (min-width: 1280px) {
            .desktop-heading-xl {
                font-size: 2.5rem;
                line-height: 1.2;
            }
            .desktop-heading-lg {
                font-size: 2rem;
                line-height: 1.3;
            }
        }

        /* ===================================
           DASHBOARD CARD DESKTOP SCALING
           =================================== */

        /* Desktop (≥1024px) — Scale up dashboard cards */
        @media (min-width: 1024px) {
            /* Dashboard stat cards — bigger padding */
            .dash-card {
                padding: 1.75rem !important;
                border-radius: 1rem;
            }

            /* Dashboard KPI stat value — much bigger numbers */
            .dash-stat-value {
                font-size: 2.75rem !important;
                line-height: 1.1;
                letter-spacing: -0.02em;
            }

            /* Dashboard stat label — cleaner & bigger */
            .dash-stat-label {
                font-size: 0.8125rem !important;
                letter-spacing: 0.1em;
                color: #6B7280; /* text-gray-500 */
                font-weight: 700 !important;
            }
            .dark .dash-stat-label {
                color: #9CA3AF; /* text-gray-400 */
            }

            /* Dashboard stat sublabel / helper text */
            .dash-stat-sub {
                font-size: 0.8125rem !important;
                color: #6B7280; /* text-gray-500 */
                font-weight: 500;
            }
            .dark .dash-stat-sub {
                color: #9CA3AF; /* text-gray-400 */
            }

            /* Chart section headings */
            .dash-chart-title {
                font-size: 0.9375rem !important;
                margin-bottom: 1.25rem !important;
                color: #111827; /* text-gray-900 */
                font-weight: 800 !important;
            }
            .dark .dash-chart-title {
                color: #F3F4F6; /* text-gray-100 */
            }

            /* Chart containers — taller */
            .dash-chart-container {
                height: 20rem !important;
                min-height: 20rem;
            }

            /* Chart containers large variant */
            .dash-chart-container-lg {
                height: 24rem !important;
                min-height: 24rem;
            }

            /* Warning/alert cards — scale text */
            .dash-alert-title {
                font-size: 0.875rem !important;
            }
            .dash-alert-text {
                font-size: 0.8125rem !important;
            }

            /* Table text inside dashboard */
            .dash-table th {
                font-size: 0.8125rem !important;
                padding-top: 0.875rem !important;
                padding-bottom: 0.875rem !important;
            }
            .dash-table td {
                font-size: 0.875rem !important;
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }

            /* Ranking list items */
            .dash-rank-item {
                padding: 0.625rem 0 !important;
            }
            .dash-rank-name {
                font-size: 0.875rem !important;
                color: #374151; /* text-gray-700 */
            }
            .dark .dash-rank-name {
                color: #D1D5DB; /* text-gray-300 */
            }
            .dash-rank-value {
                font-size: 0.875rem !important;
            }

            /* Activity log items */
            .dash-activity-item {
                padding: 1.25rem 1.5rem !important;
            }
            .dash-activity-user {
                font-size: 0.9375rem !important;
            }
            .dash-activity-desc {
                font-size: 0.875rem !important;
                color: #4B5563; /* text-gray-600 */
            }
            .dark .dash-activity-desc {
                color: #9CA3AF; /* text-gray-400 */
            }
            .dash-activity-time {
                font-size: 0.8125rem !important;
            }

            /* Insight cards */
            .dash-insight-item {
                padding: 1.25rem !important;
            }
            .dash-insight-badge {
                font-size: 0.75rem !important;
                padding: 0.35rem 0.65rem !important;
            }
            .dash-insight-text {
                font-size: 0.875rem !important;
            }
        }

        /* XL Desktop (≥1280px) — Even larger */
        @media (min-width: 1280px) {
            .dash-card {
                padding: 2rem !important;
                border-radius: 1.25rem;
            }

            .dash-stat-value {
                font-size: 3.25rem !important;
            }

            .dash-stat-label {
                font-size: 0.875rem !important;
            }

            .dash-stat-sub {
                font-size: 0.875rem !important;
            }

            .dash-chart-title {
                font-size: 1.0625rem !important;
                margin-bottom: 1.5rem !important;
            }

            .dash-chart-container {
                height: 22rem !important;
                min-height: 22rem;
            }

            .dash-chart-container-lg {
                height: 26rem !important;
                min-height: 26rem;
            }

            .dash-alert-title {
                font-size: 0.9375rem !important;
            }
            .dash-alert-text {
                font-size: 0.875rem !important;
            }

            .dash-table th {
                font-size: 0.875rem !important;
            }
            .dash-table td {
                font-size: 0.9375rem !important;
            }

            .dash-rank-name {
                font-size: 0.9375rem !important;
            }
            .dash-rank-value {
                font-size: 0.9375rem !important;
            }

            .dash-activity-item {
                padding: 1.5rem 2rem !important;
            }
            .dash-activity-user {
                font-size: 1.0625rem !important;
            }
            .dash-activity-desc {
                font-size: 0.9375rem !important;
            }

            .dash-insight-item {
                padding: 1.5rem !important;
            }
            .dash-insight-text {
                font-size: 0.9375rem !important;
                line-height: 1.6;
            }
        }

        /* 2XL Desktop (≥1536px) — Max scaling */
        @media (min-width: 1536px) {
            .dash-card {
                padding: 2.5rem !important;
                border-radius: 1.5rem;
            }

            .dash-stat-value {
                font-size: 3.75rem !important;
            }

            .dash-stat-label {
                font-size: 0.875rem !important;
            }

            .dash-stat-sub {
                font-size: 0.875rem !important;
            }

            .dash-chart-title {
                font-size: 1.125rem !important;
                margin-bottom: 1.75rem !important;
            }

            .dash-chart-container {
                height: 24rem !important;
                min-height: 24rem;
            }

            .dash-chart-container-lg {
                height: 28rem !important;
                min-height: 28rem;
            }

            .dash-rank-name {
                font-size: 1rem !important;
            }
            .dash-rank-value {
                font-size: 1rem !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 min-h-screen overflow-x-hidden
             text-sm md:text-base lg:text-[15px] 2xl:text-[17px]">
    <div class="min-h-screen">
        @php
            $user = auth()->user();
            $currentRole = $user ? $user->getCurrentRole() : null;
            $isPimpinan = $currentRole && $currentRole->role === 'pimpinan';
            $routePrefix = $isPimpinan ? 'pimpinan' : 'validator';
        @endphp
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="mobileSidebarOpen" x-cloak @click="mobileSidebarOpen = false"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

        <!-- Sidebar -->
        <aside
            :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed top-0 left-0 h-screen 
                    w-56 sm:w-60 md:w-64 lg:w-64 xl:w-72 2xl:w-80 
                    flex flex-col bg-white dark:bg-gray-800 
                    border-r border-gray-200 dark:border-gray-700 
                    transition-all duration-300 z-50">

            <div class="p-3.5 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 bg-gradient-to-tr from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-purple-500/20 ring-4 ring-purple-500/10">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-black text-xl text-gray-900 dark:text-white leading-none tracking-tight">SIMAPRES</span>
                        <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-widest mt-1">Admin Panel</span>
                    </div>
                </div>
                <button @click="mobileSidebarOpen = false"
                    class="lg:hidden p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto custom-scrollbar">
                <a href="{{ route('admin.dashboard') }}"
                    class="group flex items-center gap-3.5 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-purple-600 dark:hover:text-purple-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-sm lg:text-[20px]">Dashboard</span>
                </a>

                <!-- Validation System -->
                <div class="pt-6 pb-2">
                    <p class="px-4 text-[15px] font-black text-gray-400 uppercase tracking-[0.2em]">Verifikasi</p>
                </div>
                <a href="{{ route('admin.university.index') }}"
                    class="group flex items-center gap-3.5 px-4 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.university.*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-purple-600 dark:hover:text-purple-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm lg:text-[20px]">Verifikasi Universitas</span>
                </a>

                <!-- Data Management -->
                <div class="pt-6 pb-2">
                    <p class="px-4 text-[15px] font-black text-gray-400 uppercase tracking-[0.2em]">Data</p>
                </div>
                <a href="{{ route('admin.students') }}"
                    class="group flex items-center gap-3.5 px-4 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.students') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-purple-600 dark:hover:text-purple-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-sm lg:text-[20px]">Mahasiswa</span>
                </a>
                <a href="{{ route('admin.student-achievements') }}"
                    class="group flex items-center gap-3.5 px-4 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.student-achievements') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-purple-600 dark:hover:text-purple-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span class="text-sm lg:text-[20px]">Prestasi</span>
                </a>
                <a href="{{ route('admin.validation-logs') }}"
                    class="group flex items-center gap-3.5 px-4 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.validation-logs') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-purple-600 dark:hover:text-purple-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-sm lg:text-[20px]">Log Verifikasi</span>
                </a>
                <a href="{{ route('admin.users') }}"
                    class="group flex items-center gap-3.5 px-4 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.users') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-purple-600 dark:hover:text-purple-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-sm lg:text-[20px]">Users</span>
                </a>

                <!-- Master Data -->
                <div class="pt-6 pb-2">
                    <p class="px-4 text-[15px] font-black text-gray-400 uppercase tracking-[0.2em]">Master Data</p>
                </div>
                <a href="{{ route('admin.categories.index') }}"
                    class="group flex items-center gap-3.5 px-4 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.categories.*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-purple-600 dark:hover:text-purple-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span class="text-sm lg:text-[20px]">Kategori</span>
                </a>
                <a href="{{ route('admin.levels.index') }}"
                    class="group flex items-center gap-3.5 px-4 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.levels.*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-purple-600 dark:hover:text-purple-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span class="text-sm lg:text-[20px]">Level</span>
                </a>
                <a href="{{ route('admin.periods.index') }}"
                    class="group flex items-center gap-3.5 px-4 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.periods.*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-purple-600 dark:hover:text-purple-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-sm lg:text-[20px]">Periode</span>
                </a>
                <a href="{{ route('admin.sk.index') }}"
                    class="group flex items-center gap-3.5 px-4 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.sk.*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-purple-600 dark:hover:text-purple-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-sm lg:text-[20px]">Manajemen SK</span>
                </a>

                <!-- Actions -->
                <div class="pt-6 pb-2">
                    <p class="px-4 text-[15px] font-black text-gray-400 uppercase tracking-[0.2em]">Aksi</p>
                </div>
                <a href="{{ route('admin.submit.create') }}"
                    class="group flex items-center gap-3.5 px-4 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.submit.*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-purple-600 dark:hover:text-purple-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="text-sm lg:text-[20px]">Ajukan Prestasi</span>
                </a>

                <!-- Profile -->
                <div class="pt-6 pb-2">
                    <p class="px-4 text-[15px] font-black text-gray-400 uppercase tracking-[0.2em]">Akun</p>
                </div>
                <a href="{{ route('profile') }}"
                    class="group flex items-center gap-3.5 px-4 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('profile') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-purple-600 dark:hover:text-purple-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm lg:text-[20px]">Profil Saya</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 min-w-0 flex flex-col
            ml-0 
            lg:ml-64
            xl:ml-72 
            2xl:ml-80
            min-h-screen 
            overflow-y-auto
            bg-gray-50 dark:bg-gray-900
            transition-all duration-300">

            <!-- Top Bar -->
            <header id="mobileNavbar"
                class="mobile-navbar bg-white/80 dark:bg-gray-800/80 backdrop-blur-md border-b border-gray-100 dark:border-gray-700 py-3.5 flex justify-between items-center sticky top-0 z-30 ring-1 ring-black/5 dark:ring-white/5 shadow-sm">
                <div class="w-full px-4 md:px-6 lg:px-8 xl:px-10 flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <button @click="mobileSidebarOpen = !mobileSidebarOpen"
                            class="lg:hidden p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-lg lg:text-xl font-black text-gray-900 dark:text-white tracking-tight">
                            @yield('title', 'Dashboard')</h1>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                    <button @click="darkMode = !darkMode"
                        class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-200 border border-gray-200 dark:border-gray-600">
                        <svg x-show="!darkMode" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="darkMode" x-cloak class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>

                    <!-- Role Switcher (Inline for top bar) -->
                    @php
                        $user = auth()->user();
                        $activeRoles = $user ? $user->activeRoles()->get() : collect();
                        $currentRoleId = session('active_role_id');
                        $currentRole = $currentRoleId ? $activeRoles->firstWhere('id', $currentRoleId) : $activeRoles->first();
                    @endphp
                    @if($activeRoles->count() > 1)
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" type="button"
                                class="flex items-center gap-2 px-3 py-2.5 text-xs font-bold bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 rounded-xl hover:bg-purple-100 dark:hover:bg-purple-900/40 transition-colors border border-purple-100 dark:border-purple-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span
                                    class="hidden sm:inline uppercase tracking-wider">{{ $currentRole ? $currentRole->getRoleDisplayName() : 'Role' }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
                                class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 py-1.5 z-[60] overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-50 dark:border-gray-700">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.15em]">Ganti Peran</p>
                                </div>
                                <div class="max-h-64 overflow-y-auto custom-scrollbar">
                                    @foreach($activeRoles as $role)
                                        @if($role->id !== $currentRoleId)
                                            <form method="POST" action="{{ route('role.switch') }}" class="block">
                                                @csrf
                                                <input type="hidden" name="role_id" value="{{ $role->id }}">
                                                <button type="submit"
                                                    class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                                    <div class="flex items-center space-x-3">
                                                        <div
                                                            class="w-9 h-9 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm transition-transform group-hover:scale-110">
                                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                            </svg>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $role->getRoleDisplayName() }}</p>
                                                            @if($role->level !== 'university')
                                                                <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                                                    {{ $role->getScopeDescription() }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </button>
                                            </form>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="hidden sm:flex items-center gap-3 px-3 py-1.5 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600">
                        <div class="flex flex-col items-end">
                            <span class="text-xs font-bold text-gray-900 dark:text-white leading-none">{{ auth()->user()->name }}</span>
                            @if(auth()->user()->last_login_method === 'sso')
                                <span class="text-[9px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-tighter mt-1">Verified SSO</span>
                            @endif
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 font-bold text-xs ring-2 ring-white dark:ring-gray-800">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>

                    <form
                        action="{{ auth()->user()->last_login_method === 'sso' ? route('sso.logout') : route('logout') }}"
                        method="POST">
                        @csrf
                        <button type="submit"
                            class="p-2.5 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/40 transition-all duration-200 hover:scale-105 border border-red-100 dark:border-red-900/30 group"
                            title="Logout{{ auth()->user()->last_login_method === 'sso' ? ' (SSO)' : '' }}">
                            <svg class="w-5 h-5 transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 w-full p-4 md:p-6 lg:p-8 xl:p-10 2xl:p-12">

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Toast Notifications -->
    <x-toast-notification />

    <!-- Show session notifications -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                showToast('success', {!! json_encode(session('success')) !!}, 'Berhasil!');
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                showToast('error', {!! json_encode(session('error')) !!}, 'Terjadi Kesalahan!');
            });
        </script>
    @endif

    @if(session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                showToast('warning', {!! json_encode(session('warning')) !!}, 'Peringatan!');
            });
        </script>
    @endif

    @if(session('info'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                showToast('info', {!! json_encode(session('info')) !!}, 'Informasi');
            });
        </script>
    @endif

    @php /** @var \Illuminate\Support\ViewErrorBag $errors */ @endphp
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @foreach($errors->all() as $error)
                    showToast('error', {!! json_encode($error) !!}, 'Validasi Gagal');
                @endforeach
            });
        </script>
    @endif

    {{-- Disabled: instant.page prefetch can cause session race conditions on search/filter --}}
    {{-- <script src="https://instant.page/5.2.0" type="module"
        integrity="sha384-jnZyxPjiipYXnSU0ygqeac2q7CVYMbh84q0uHVRRxEtvFPiQYbXWUorga2aqZJ0z"></script> --}}

    {{-- Mobile auto-hide navbar script --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.getElementById('mobileNavbar');
        if (!navbar) return;

        let lastScrollY = window.scrollY;
        let ticking = false;
        let tapTimer = null;
        const SCROLL_THRESHOLD = 5;
        const TOP_ZONE = 100;
        const TAP_SHOW_DURATION = 3000; // ms to keep navbar visible after tap

        function isMobile() {
            return window.innerWidth < 1024;
        }

        function showNavbar() {
            navbar.classList.remove('navbar-hidden');
            navbar.classList.add('navbar-visible');
        }

        function hideNavbar() {
            // Don't hide if sidebar is open
            if (document.querySelector('[x-data]') && 
                document.querySelector('[x-data]').__x &&
                document.querySelector('[x-data]').__x.$data.mobileSidebarOpen) {
                return;
            }
            navbar.classList.remove('navbar-visible');
            navbar.classList.add('navbar-hidden');
        }

        function onScroll() {
            if (!isMobile()) {
                // On desktop, always show and remove mobile classes
                navbar.classList.remove('navbar-hidden', 'navbar-visible');
                return;
            }

            const currentScrollY = window.scrollY;
            const delta = currentScrollY - lastScrollY;

            // Always show at top of page
            if (currentScrollY <= TOP_ZONE) {
                showNavbar();
                lastScrollY = currentScrollY;
                return;
            }

            // Only trigger if scroll distance exceeds threshold
            if (Math.abs(delta) < SCROLL_THRESHOLD) return;

            if (delta < 0) {
                // Scrolling UP → show navbar
                showNavbar();
            } else {
                // Scrolling DOWN → hide navbar
                hideNavbar();
            }

            lastScrollY = currentScrollY;
        }

        // Scroll handler with requestAnimationFrame for performance
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    onScroll();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        // Tap/touch handler — show navbar on screen tap
        document.addEventListener('touchstart', function(e) {
            if (!isMobile()) return;

            // Don't interfere with interactive elements
            const tag = e.target.tagName.toLowerCase();
            const isInteractive = tag === 'a' || tag === 'button' || tag === 'input' || 
                                  tag === 'select' || tag === 'textarea' ||
                                  e.target.closest('a') || e.target.closest('button') || 
                                  e.target.closest('form');

            showNavbar();

            // If tapping on non-interactive area, auto-hide after delay
            if (!isInteractive) {
                clearTimeout(tapTimer);
                tapTimer = setTimeout(function() {
                    if (window.scrollY > TOP_ZONE) {
                        hideNavbar();
                    }
                }, TAP_SHOW_DURATION);
            }
        }, { passive: true });

        // Reset on resize (e.g. rotating device)
        window.addEventListener('resize', function() {
            if (!isMobile()) {
                navbar.classList.remove('navbar-hidden', 'navbar-visible');
            }
        });
    });
    </script>
    
    @stack('scripts')
</body>

</html>